<?php

namespace App\Http\Controllers\Feature;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\GradeCompany;
use App\Models\Location;
use App\Services\BarangKeluar\BarangKeluarService;
use App\Http\Requests\BarangKeluar\SellRequest;
use Illuminate\Http\Request;

class PenjualanController extends Controller
{
    protected BarangKeluarService $service;

    public function __construct(BarangKeluarService $service)
    {
        $this->service = $service;
    }

    /**
     * Tampilkan form penjualan + riwayat penjualan
     */
    public function sellForm(Request $request)
    {
        $grades = GradeCompany::all();
        
        // Dapatkan lokasi "Gudang Utama" sebagai default
        $defaultLocation = Location::where('name', 'Gudang Utama')->first();

        // Jika tidak ada lokasi "Gudang Utama", kembalikan error atau redirect
        if (!$defaultLocation) {
            return redirect()->back()->with('error', 'Lokasi "Gudang Utama" tidak ditemukan.');
        }

        // Riwayat khusus penjualan (SALE_OUT) dari Gudang Utama
        $query = InventoryTransaction::where('transaction_type', 'SALE_OUT')
            ->where('location_id', $defaultLocation->id)
            ->with(['gradeCompany', 'location']);

        // Filter berdasarkan grade jika ada
        if ($request->filled('grade_id')) {
            $query->where('grade_company_id', $request->grade_id);
        }

        $penjualanTransactions = $query->latest('transaction_date')
            ->latest('id')
            ->paginate(10);

        return view('admin.barang-keluar.sell', compact(
            'grades',
            'defaultLocation',
            'penjualanTransactions'
        ));
    }

    /**
     * Simpan data penjualan
     */
    public function sell(SellRequest $request)
    {
        // Pastikan location_id adalah Gudang Utama
        $defaultLocation = Location::where('name', 'Gudang Utama')->first();
        
        $data = $request->validated();
        $data['location_id'] = $defaultLocation->id;

        $this->service->sell($data);

        return redirect()
            ->route('barang.keluar.sell.form')
            ->with('success', 'Penjualan berhasil dicatat dan stok diperbarui.');
    }
}