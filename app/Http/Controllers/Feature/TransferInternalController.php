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
        $grades = GradeCompany::all();
        $locations = Location::all();

        // Riwayat khusus transfer internal
        $query = InventoryTransaction::whereIn('transaction_type', ['TRANSFER_OUT', 'TRANSFER_IN'])
            ->with(['gradeCompany', 'location', 'stockTransfer.fromLocation', 'stockTransfer.toLocation']);

        // Filter berdasarkan grade jika ada
        if ($request->filled('grade_id')) {
            $query->where('grade_company_id', $request->grade_id);
        }

        // Filter berdasarkan lokasi jika ada
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // Group by reference_id untuk menampilkan transfer sebagai satu kesatuan
        $transferInternalTransactions = $query->latest('transaction_date')
            ->latest('id')
            ->paginate(10);

        return view('admin.barang-keluar.transfer-step1', compact(
            'grades',
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
}
