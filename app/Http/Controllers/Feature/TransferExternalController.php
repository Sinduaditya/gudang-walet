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
        $grades = GradeCompany::all();
        $locations = Location::all();

        // Riwayat khusus transfer external (EXTERNAL_TRANSFER_IN)
        $query = InventoryTransaction::where('transaction_type', 'EXTERNAL_TRANSFER_IN')
            ->with(['gradeCompany', 'location', 'stockTransfer.fromLocation']);

        // Filter berdasarkan grade jika ada
        if ($request->filled('grade_id')) {
            $query->where('grade_company_id', $request->grade_id);
        }

        // Filter berdasarkan lokasi tujuan jika ada
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $transferExternalTransactions = $query->latest('transaction_date')
            ->latest('id')
            ->paginate(10);

        return view('admin.barang-keluar.external-transfer-step1', compact(
            'grades',
            'locations',
            'transferExternalTransactions'
        ));
    }

    /**
     * Store External Transfer Step 1 data to session
     */
    public function storeExternalTransferStep1(Request $request)
    {
        $validated = $request->validate([
            'from_location_id' => 'required|exists:locations,id',
            'grade_company_id' => 'required|exists:grades_company,id',
            'weight_grams' => 'required|numeric|min:0.01',
            'transfer_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ], [
            'from_location_id.required' => 'Lokasi asal eksternal harus dipilih',
            'from_location_id.exists' => 'Lokasi asal tidak valid',
            'grade_company_id.required' => 'Grade harus dipilih',
            'grade_company_id.exists' => 'Grade tidak valid',
            // 'to_location_id.required' => 'Lokasi tujuan harus dipilih',
            // 'to_location_id.exists' => 'Lokasi tujuan tidak valid',
            'weight_grams.required' => 'Berat harus diisi',
            'weight_grams.min' => 'Berat minimal 0.01 gram',
            'transfer_date.date' => 'Format tanggal tidak valid',
            'notes.max' => 'Catatan maksimal 500 karakter',
        ]);

         $validated['to_location_id'] = Location::where('name', 'Gudang Utama')->first()->id;

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
