<?php

namespace App\Http\Controllers\Feature;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\GradeCompany;
use App\Services\BarangKeluar\BarangKeluarService;
use App\Http\Requests\BarangKeluar\ExternalTransferRequest;
use Illuminate\Http\Request;

class TransferExternalController extends Controller
{
    protected BarangKeluarService $service;

    public function __construct(BarangKeluarService $service)
    {
        $this->service = $service;
    }

    /**
     * Step 1: Form transfer external + riwayat
     */
    public function externalTransferStep1(Request $request)
    {
        $gudangUtama = Location::where('name', 'Gudang Utama')->first();
        if (!$gudangUtama) {
            return redirect()->back()->with('error', 'Lokasi "Gudang Utama" tidak ditemukan.');
        }

        $grades = GradeCompany::orderBy('name')->get();

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

        $jasaCuciLocations = Location::where('name', 'NOT LIKE', '%IDM%')
            ->where('name', 'NOT LIKE', '%DMK%')
            ->where('name', '!=', 'Gudang Utama')
            ->orderBy('name')
            ->get();

        $query = InventoryTransaction::where('transaction_type', 'EXTERNAL_TRANSFER_OUT')
            ->with(['gradeCompany', 'location', 'stockTransfer.toLocation'])
            ->where('location_id', $gudangUtama->id); 

        if ($request->filled('grade_id')) {
            $query->where('grade_company_id', $request->grade_id);
        }

        if ($request->filled('location_id')) {
            $query->whereHas('stockTransfer', function($q) use ($request) {
                $q->where('to_location_id', $request->location_id);
            });
        }

        $transferExternalTransactions = $query->latest('transaction_date')
            ->latest('id')
            ->paginate(10);

        return view('admin.barang-keluar.external-transfer-step1', compact(
            'grades',
            'gradesWithStock', 
            'gudangUtama',
            'jasaCuciLocations', 
            'transferExternalTransactions'
        ));
    }

    /**
     * Store External Transfer Step 1 data to session
     */
    public function storeExternalTransferStep1(Request $request)
    {
        $validated = $request->validate([
            'grade_company_id' => 'required|exists:grades_company,id',
            'to_location_id' => 'required|exists:locations,id',
            'weight_grams' => 'required|numeric|min:0.01',
            'transfer_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ], [
            'grade_company_id.required' => 'Grade harus dipilih',
            'to_location_id.required' => 'Lokasi tujuan (Jasa Cuci) harus dipilih',
            'weight_grams.required' => 'Berat harus diisi',
            'weight_grams.min' => 'Berat minimal 0.01 gram',
        ]);

        $gudangUtama = Location::where('name', 'Gudang Utama')->first();
        $validated['from_location_id'] = $gudangUtama->id;

        $hasEnoughStock = $this->service->hasEnoughStock(
            $validated['grade_company_id'], 
            $validated['from_location_id'], // Gudang Utama
            $validated['weight_grams']
        );

        if (!$hasEnoughStock) {
            $availableStock = $this->service->getAvailableStock(
                $validated['grade_company_id'], 
                $validated['from_location_id']
            );
            
            return redirect()->back()
                ->withInput()
                ->with('error', "Stok di Gudang Utama tidak mencukupi! Hanya tersedia " . number_format($availableStock, 2) . " gram.");
        }

        $request->session()->put('external_transfer_step1', $validated);

        return redirect()->route('barang.keluar.external-transfer.step2');
    }

    /**
     * External Transfer Step 2 - Show confirmation
     */
    public function externalTransferStep2()
    {
        $step1Data = session('external_transfer_step1');

        if (!$step1Data) {
            return redirect()->route('barang.keluar.external-transfer.step1')
                ->with('error', 'Silakan lengkapi data transfer terlebih dahulu');
        }

        $grade = GradeCompany::findOrFail($step1Data['grade_company_id']);
        $fromLocation = Location::findOrFail($step1Data['from_location_id']);
        $toLocation = Location::findOrFail($step1Data['to_location_id']);

        return view('admin.barang-keluar.external-transfer-step2', compact(
            'step1Data',
            'grade',
            'fromLocation',
            'toLocation'
        ));
    }

    /**
     * Process external transfer
     */
    public function externalTransfer(ExternalTransferRequest $request)
    {
        $this->service->externalTransfer($request->validated());

        session()->forget('external_transfer_step1');

        return redirect()
            ->route('barang.keluar.external-transfer.step1')
            ->with('success', 'Transfer eksternal berhasil dicatat dan stok diperbarui.');
    }
}
