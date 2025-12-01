<?php

namespace App\Http\Controllers\Feature;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\GradeCompany;
use App\Services\BarangKeluar\BarangKeluarService;
use Illuminate\Http\Request;

class ReceiveExternalController extends Controller
{
    protected BarangKeluarService $service;

    public function __construct(BarangKeluarService $service)
    {
        $this->service = $service;
    }

    /**
     * Step 1: Form terima barang eksternal (dari Jasa Cuci)
     */
    public function receiveExternalStep1(Request $request)
    {
        $grades = GradeCompany::all();
        
        $locations = Location::where('name', 'NOT LIKE', '%IDM%')
            ->where('name', 'NOT LIKE', '%DMK%')
            ->where('name', 'NOT LIKE', '%Gudang Utama%')
            ->get();

        $query = InventoryTransaction::where('transaction_type', 'RECEIVE_EXTERNAL_IN')
            ->with(['gradeCompany', 'location', 'stockTransfer.fromLocation'])
            ->whereHas('stockTransfer.fromLocation', function($q) {
                $q->where('name', 'NOT LIKE', '%IDM%')
                  ->where('name', 'NOT LIKE', '%DMK%');
            });

        if ($request->filled('grade_id')) {
            $query->where('grade_company_id', $request->grade_id);
        }

        $receiveExternalTransactions = $query->latest('transaction_date')
            ->latest('id')
            ->paginate(10);

        return view('admin.barang-keluar.receive-external-step1', compact(
            'grades',
            'locations',
            'receiveExternalTransactions'
        ));
    }

    /**
     * AJAX endpoint untuk cek stok yang dikirim ke jasa cuci
     */
    public function checkExternalStock(Request $request)
    {
        $gradeCompanyId = $request->get('grade_company_id');
        $fromLocationId = $request->get('from_location_id');

        if (!$gradeCompanyId || !$fromLocationId) {
            return response()->json([
                'success' => false,
                'message' => 'Grade dan lokasi asal harus dipilih'
            ]);
        }

        $sentStock = $this->getSentStockToLocation($gradeCompanyId, $fromLocationId);
        $receivedStock = $this->getReceivedStockFromLocation($gradeCompanyId, $fromLocationId);
        $pendingStock = $sentStock - $receivedStock;

        $grade = GradeCompany::find($gradeCompanyId);
        $location = Location::find($fromLocationId);

        return response()->json([
            'success' => true,
            'grade_name' => $grade ? $grade->name : 'Unknown',
            'location_name' => $location ? $location->name : 'Unknown',
            'sent_stock_grams' => $sentStock,
            'received_stock_grams' => $receivedStock,
            'pending_stock_grams' => $pendingStock,
            'formatted_sent_stock' => number_format($sentStock, 0, ',', '.') . ' gr',
            'formatted_received_stock' => number_format($receivedStock, 0, ',', '.') . ' gr',
            'formatted_pending_stock' => number_format($pendingStock, 0, ',', '.') . ' gr',
            'has_pending_stock' => $pendingStock > 0,
        ]);
    }

    /**
     * Get total stok yang pernah dikirim ke lokasi external
     */
    private function getSentStockToLocation(int $gradeCompanyId, int $locationId): float
    {
        return InventoryTransaction::where('grade_company_id', $gradeCompanyId)
            ->where('transaction_type', 'EXTERNAL_TRANSFER_OUT')
            ->whereHas('stockTransfer', function($q) use ($locationId) {
                $q->where('to_location_id', $locationId);
            })
            ->sum('quantity_change_grams'); // Sudah negatif, jadi hasil negatif
    }

    /**
     * Get total stok yang sudah diterima dari lokasi external
     */
    private function getReceivedStockFromLocation(int $gradeCompanyId, int $locationId): float
    {
        return InventoryTransaction::where('grade_company_id', $gradeCompanyId)
            ->where('transaction_type', 'RECEIVE_EXTERNAL_IN')
            ->whereHas('stockTransfer', function($q) use ($locationId) {
                $q->where('from_location_id', $locationId);
            })
            ->sum('quantity_change_grams'); // Positif
    }

    /**
     * Store Step 1 data to session
     */
    public function storeReceiveExternalStep1(Request $request)
    {
        $validated = $request->validate([
            'grade_company_id' => 'required|exists:grades_company,id',
            'from_location_id' => 'required|exists:locations,id',
            'weight_grams' => 'required|numeric|min:0.01',
            'transfer_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ], [
            'grade_company_id.required' => 'Grade harus dipilih',
            'from_location_id.required' => 'Lokasi asal harus dipilih',
            'weight_grams.required' => 'Berat harus diisi',
            'weight_grams.min' => 'Berat minimal 0.01 gram',
        ]);

        $sentStock = abs($this->getSentStockToLocation($validated['grade_company_id'], $validated['from_location_id']));
        $receivedStock = $this->getReceivedStockFromLocation($validated['grade_company_id'], $validated['from_location_id']);
        $pendingStock = $sentStock - $receivedStock;

        if ($validated['weight_grams'] > $pendingStock) {
            return back()
                ->withInput()
                ->withErrors([
                    'weight_grams' => "Berat melebihi stok yang pending. Maksimal: " . number_format($pendingStock, 2) . " gram"
                ]);
        }

        // Set to_location_id ke Gudang Utama
        $gudangUtama = Location::where('name', 'Gudang Utama')->first();
        $validated['to_location_id'] = $gudangUtama->id;

        $request->session()->put('receive_external_step1', $validated);

        return redirect()->route('barang.keluar.receive-external.step2');
    }

    /**
     * Step 2 - Confirmation
     */
    public function receiveExternalStep2()
    {
        $step1Data = session('receive_external_step1');

        if (!$step1Data) {
            return redirect()->route('barang.keluar.receive-external.step1')
                ->with('error', 'Silakan lengkapi data terlebih dahulu');
        }

        $grade = GradeCompany::findOrFail($step1Data['grade_company_id']);
        $fromLocation = Location::findOrFail($step1Data['from_location_id']);
        $toLocation = Location::findOrFail($step1Data['to_location_id']);

        return view('admin.barang-keluar.receive-external-step2', compact(
            'step1Data',
            'grade',
            'fromLocation',
            'toLocation'
        ));
    }

    /**
     * Process receive external
     */
    public function receiveExternal(Request $request)
    {
        $step1Data = session('receive_external_step1');
        if (!$step1Data) {
            return redirect()->route('barang.keluar.receive-external.step1')
                ->with('error', 'Data tidak ditemukan');
        }

        $this->service->receiveExternal($step1Data);

        session()->forget('receive_external_step1');

        return redirect()
            ->route('barang.keluar.receive-external.step1')
            ->with('success', 'Penerimaan barang eksternal berhasil dicatat dan stok diperbarui.');
    }
}