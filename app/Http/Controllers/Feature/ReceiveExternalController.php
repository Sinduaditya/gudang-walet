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
        // ✅ FIX: Ambil semua grades (untuk penerimaan tidak perlu cek stok)
        $grades = GradeCompany::all();
        
        // ✅ Hanya lokasi Jasa Cuci (selain IDM/DMK) sebagai asal
        $locations = Location::where('name', 'NOT LIKE', '%IDM%')
            ->where('name', 'NOT LIKE', '%DMK%')
            ->where('name', 'NOT LIKE', '%Gudang Utama%')
            ->get();

        // Riwayat penerimaan eksternal
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
        ]);

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