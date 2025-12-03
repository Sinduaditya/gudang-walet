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
        $defaultLocation = Location::where('name', 'Gudang Utama')->first();
        if (!$defaultLocation) {
            return redirect()->back()->with('error', 'Lokasi "Gudang Utama" tidak ditemukan.');
        }

        $grades = GradeCompany::all();

        $gradesWithStock = $grades->map(function ($grade) use ($defaultLocation) {
            $available = (int) $this->service->getAvailableStock($grade->id, $defaultLocation->id);
            return [
                'id' => $grade->id,
                'name' => $grade->name ?? '',
                'total_stock_grams' => $available,
            ];
        });

        $query = InventoryTransaction::where('transaction_type', 'SALE_OUT')
            ->where('location_id', $defaultLocation->id)
            ->with(['gradeCompany', 'location']);

        if ($request->filled('grade_id')) {
            $query->where('grade_company_id', $request->grade_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $penjualanTransactions = $query->latest('transaction_date')->paginate(10);

        return view('admin.barang-keluar.sell', compact(
            'gradesWithStock',
            'defaultLocation',
            'penjualanTransactions'
        ));
    }

    public function checkStock(Request $request)
    {
        $gradeId = (int) $request->query('grade_company_id');
        $locationId = (int) $request->query('location_id', 1);

        if (!$gradeId) {
            return response()->json(['ok' => false, 'message' => 'grade_company_id required'], 400);
        }

        $available = $this->service->getAvailableStock($gradeId, $locationId);
        return response()->json(['ok' => true, 'available_grams' => (int)$available]);
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
        if (!$this->service->hasEnoughStock($data['grade_company_id'], $data['location_id'], $data['weight_grams'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Stok tidak mencukupi. Gunakan tombol "Cek Stok" untuk melihat sisa stok.');
        }

        $this->service->sell($data);

        return redirect()
            ->route('barang.keluar.sell.form')
            ->with('success', 'Penjualan berhasil dicatat dan stok diperbarui.');
    }

    public function edit($id)
    {
        $tx = InventoryTransaction::findOrFail($id);
        return view('admin.barang-keluar.sell-edit', compact('tx'));
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $tx = InventoryTransaction::findOrFail($id);
        $request->validate(['weight_grams' => 'required|numeric|min:0.01']);
        $tx->update(['quantity_change_grams' => -abs($request->input('weight_grams'))]);
        return redirect()->route('barang.keluar.sell.form')->with('success', 'Transaksi diperbarui.');
    }

    public function destroy($id)
    {
        $tx = InventoryTransaction::findOrFail($id);
        $tx->delete();
        return redirect()->route('barang.keluar.sell.form')->with('success', 'Transaksi penjualan dihapus.');
    }
}