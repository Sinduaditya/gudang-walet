<?php

namespace App\Http\Controllers\Feature;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\GradeCompany;
use App\Services\BarangKeluar\BarangKeluarService;
use App\Http\Requests\BarangKeluar\TransferRequest;
use Illuminate\Http\Request;

class TransferInternalController extends Controller
{
    protected BarangKeluarService $service;

    public function __construct(BarangKeluarService $service)
    {
        $this->service = $service;
    }

    /**
     * Step 1: Form transfer internal + riwayat
     */
    public function transferStep1(Request $request)
    {
        $gudangUtama = Location::where('name', 'Gudang Utama')->first();
        if (!$gudangUtama) {
            return redirect()->back()->with('error', 'Lokasi "Gudang Utama" tidak ditemukan.');
        }

        $stockSummary = $this->service->getStockPerLocation(null, $gudangUtama->id);

        $gradesWithStock = $stockSummary->map(function ($stock) {
            return [
                'id' => $stock->grade_company_id,
                'name' => $stock->gradeCompany->name ?? 'Unknown',
                'total_stock_grams' => $stock->current_stock_grams,
            ];
        })->filter(function ($grade) {
            return $grade['total_stock_grams'] > 0;
        });

        $locations = Location::where('name', 'LIKE', '%IDM%')
            ->orWhere('name', 'LIKE', '%DMK%')
            ->get();

        $query = \App\Models\StockTransfer::with([
            'gradeCompany',
            'fromLocation',
            'toLocation',
        ])->whereHas('toLocation', function($q) {
            $q->where('name', 'LIKE', '%IDM%')
              ->orWhere('name', 'LIKE', '%DMK%');
        });

        if ($request->filled('grade_id')) {
            $query->where('grade_company_id', $request->grade_id);
        }

        if ($request->filled('location_id')) {
            $query->where('to_location_id', $request->location_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('transfer_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transfer_date', '<=', $request->end_date);
        }

        $transferInternalTransactions = $query->latest('transfer_date')
            ->latest('id')
            ->paginate(10);

        return view('admin.barang-keluar.transfer-step1', compact(
            'gradesWithStock',
            'gudangUtama',
            'locations',
            'transferInternalTransactions'
        ));
    }

    /**
     * Store Transfer Step 1 data to session
     */
    public function storeTransferStep1(Request $request)
    {
        $validated = $request->validate([
            'grade_company_id' => 'required|exists:grades_company,id',
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id' => 'required|exists:locations,id|different:from_location_id',
            'weight_grams' => 'required|numeric|min:0.01',
            'susut_grams' => 'nullable|numeric|min:0',
            'transfer_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ], [
            'grade_company_id.required' => 'Grade harus dipilih',
            'grade_company_id.exists' => 'Grade tidak valid',
            'from_location_id.required' => 'Lokasi asal harus dipilih',
            'from_location_id.exists' => 'Lokasi asal tidak valid',
            'to_location_id.required' => 'Lokasi tujuan harus dipilih',
            'to_location_id.exists' => 'Lokasi tujuan tidak valid',
            'to_location_id.different' => 'Lokasi tujuan harus berbeda dengan lokasi asal',
            'weight_grams.required' => 'Berat harus diisi',
            'weight_grams.min' => 'Berat minimal 0.01 gram',
            'transfer_date.date' => 'Format tanggal tidak valid',
            'notes.max' => 'Catatan maksimal 500 karakter',
        ]);

        // PENTING: Paksa from_location_id selalu Gudang Utama untuk keamanan
        $gudangUtama = Location::where('name', 'Gudang Utama')->first()
                    ?? Location::where('id', 1)->first();

        if ($gudangUtama) {
            $validated['from_location_id'] = $gudangUtama->id;
        }

        // Calculate total weight to be deducted (transfer weight + shrinkage)
        $totalWeight = $validated['weight_grams'] + ($validated['susut_grams'] ?? 0);

        $hasEnoughStock = $this->service->hasEnoughStock(
            $validated['grade_company_id'],
            $validated['from_location_id'],
            $totalWeight
        );

        if (!$hasEnoughStock) {
            $availableStock = $this->service->getAvailableStock(
                $validated['grade_company_id'],
                $validated['from_location_id']
            );

            return redirect()->back()
                ->withInput()
                ->with('error', "Stok tidak mencukupi! Total yang dibutuhkan (Transfer + Susut): " . number_format($totalWeight, 2) . " gram. Tersedia: " . number_format($availableStock, 2) . " gram.");
        }

        // Store in session
        $request->session()->put('transfer_step1', $validated);

        return redirect()->route('barang.keluar.transfer.step2');
    }

    /**
     * Transfer Step 2 - Show confirmation page
     */
    public function transferStep2()
    {
        $step1Data = session('transfer_step1');

        // If no step 1 data, redirect back to step 1
        if (!$step1Data) {
            return redirect()->route('barang.keluar.transfer.step1')
                ->with('error', 'Silakan lengkapi data transfer terlebih dahulu');
        }

        // Get related models for display
        $grade = GradeCompany::findOrFail($step1Data['grade_company_id']);
        $fromLocation = Location::findOrFail($step1Data['from_location_id']);
        $toLocation = Location::findOrFail($step1Data['to_location_id']);

        return view('admin.barang-keluar.transfer-step2', compact(
            'step1Data',
            'grade',
            'fromLocation',
            'toLocation'
        ));
    }

    /**
     * Process transfer (final submission)
     */
    public function transfer(TransferRequest $request)
    {
        $this->service->transfer($request->validated());

        // Clear session after successful transfer
        session()->forget('transfer_step1');

        return redirect()
            ->route('barang.keluar.transfer.step1')
            ->with('success', 'Transfer internal berhasil dicatat dan stok diperbarui.');
    }

    public function checkStock(Request $request)
    {
        $gradeId = (int) $request->query('grade_company_id');
        $locationId = (int) $request->query('location_id');

        if (!$gradeId || !$locationId) {
            return response()->json(['ok' => false, 'message' => 'grade_company_id dan location_id required'], 400);
        }

        $available = $this->service->getAvailableStock($gradeId, $locationId);

        return response()->json([
            'ok' => true,
            'available_grams' => $available
        ]);
    }

    public function edit($id)
    {
        $transfer = \App\Models\StockTransfer::findOrFail($id);
        $grades = \App\Models\GradeCompany::orderBy('name')->get();
        $locations = \App\Models\Location::where('name', 'NOT LIKE', '%Jasa Cuci%')
            ->where('name', '!=', 'Gudang Utama')
            ->orderBy('name')
            ->get();
        
        // Get stock for the current location and grade to show available
        $availableStock = $this->service->getAvailableStock($transfer->grade_company_id, $transfer->from_location_id);
        // Add back the current transfer weight because we are editing it
        $availableStock += $transfer->weight_grams + ($transfer->susut_grams ?? 0);

        return view('admin.barang-keluar.transfer-edit', compact('transfer', 'grades', 'locations', 'availableStock'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'grade_company_id' => 'required|exists:grades_company,id',
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id' => 'required|exists:locations,id|different:from_location_id',
            'weight_grams' => 'required|numeric|min:0.01',
            'susut_grams' => 'nullable|numeric|min:0',
            'transfer_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check stock availability (excluding current transaction)
        $totalWeight = $validated['weight_grams'] + ($validated['susut_grams'] ?? 0);
        $availableStock = $this->service->getAvailableStock($validated['grade_company_id'], $validated['from_location_id']);
        
        // If editing same location/grade, we need to add back the old weight to available stock check
        $oldTransfer = \App\Models\StockTransfer::findOrFail($id);
        if ($oldTransfer->grade_company_id == $validated['grade_company_id'] && 
            $oldTransfer->from_location_id == $validated['from_location_id']) {
            $availableStock += $oldTransfer->weight_grams + ($oldTransfer->susut_grams ?? 0);
        }

        if ($availableStock < $totalWeight) {
             return back()->with('error', "Stok tidak mencukupi! Dibutuhkan: " . number_format($totalWeight, 2) . " gr. Tersedia: " . number_format($availableStock, 2) . " gr.");
        }

        $this->service->updateTransferInternal($id, $validated);

        return redirect()->route('barang.keluar.transfer.step1')
            ->with('success', 'Transfer internal berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $transfer = \App\Models\StockTransfer::findOrFail($id);

        // Delete associated inventory transactions
        $transfer->transactions()->delete();

        // Delete the transfer record
        $transfer->delete();

        return redirect()->route('barang.keluar.transfer.step1')
            ->with('success', 'Transfer internal berhasil dihapus dan stok dikembalikan.');
    }
}
