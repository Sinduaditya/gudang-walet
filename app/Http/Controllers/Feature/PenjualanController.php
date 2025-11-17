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
        $locations = Location::all();

        // Riwayat khusus penjualan (SALE_OUT)
        $query = InventoryTransaction::where('transaction_type', 'SALE_OUT')
            ->with(['gradeCompany', 'location']);

        // Filter berdasarkan grade jika ada
        if ($request->filled('grade_id')) {
            $query->where('grade_company_id', $request->grade_id);
        }

        // Filter berdasarkan lokasi jika ada
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $penjualanTransactions = $query->latest('transaction_date')
            ->latest('id')
            ->paginate(10);

        return view('admin.barang-keluar.sell', compact(
            'grades',
            'locations',
            'penjualanTransactions'
        ));
    }

    /**
     * Simpan data penjualan
     */
    public function sell(SellRequest $request)
    {
        $this->service->sell($request->validated());

        return redirect()
            ->route('barang.keluar.sell.form')
            ->with('success', 'Penjualan berhasil dicatat dan stok diperbarui.');
    }
}
