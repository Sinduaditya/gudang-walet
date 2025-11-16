<?php

namespace App\Http\Controllers\Feature;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\GradingGoods\Step1Request;
use App\Http\Requests\GradingGoods\Step2Request;
use App\Services\GradingGoods\GradingGoodsService;
use App\Http\Requests\GradingGoods\UpdateGradingRequest;
use App\Exports\GradingGoodsExport;
use Maatwebsite\Excel\Facades\Excel;

class GradingGoodsController extends Controller
{
    protected $gradingGoodsService;

    public function __construct(GradingGoodsService $gradingGoodsService)
    {
        $this->gradingGoodsService = $gradingGoodsService;
    }

    public function index()
    {
        $gradings = $this->gradingGoodsService->getAllGrading();
        return view('admin.grading-goods.index', compact('gradings'));
    }

    // Step 1 - show form (optional filter q = grade supplier name)
    public function createStep1(Request $request)
    {
        // Sesuai flow Step 1: Dapatkan 'q' (nama grade supplier) dari query
        $q = $request->query('q');
        // Sesuai flow Step 1: Ambil data item berdasarkan 'q'
        $receiptItems = $this->gradingGoodsService->getReceiptItemsByGradeSupplierName($q);
        return view('admin.grading-goods.step1', compact('receiptItems', 'q'));
    }

    // Step 1 - store grading_date + create SortingResult with receipt_item_id
    public function storeStep1(Step1Request $request)
    {
        // Sesuai flow Step 1: Simpan data dari form
        $sortingResult = $this->gradingGoodsService->createSortingResultStep1(
            $request->input('grading_date'),
            $request->input('receipt_item_id')
        );

        return redirect()->route('grading-goods.step2', ['id' => $sortingResult->id])
            ->with('success', 'Step 1 tersimpan. Lanjutkan ke Step 2.');
    }

    // Step 2 - show form to complete grading
    public function createStep2($id)
    {
        $sortingResult = $this->gradingGoodsService->getSortingResultWithRelations($id);
        if (! $sortingResult) {
            return redirect()->route('grading-goods.index')->with('error', 'Data grading tidak ditemukan.');
        }

        // Data dari Step 1 (tgl_datang, berat_gudang) diambil via relasi $sortingResult
        return view('admin.grading-goods.step2', compact('sortingResult'));
    }

    // Step 2 - update sorting result
    public function storeStep2(Step2Request $request, $id)
    {
        // Sesuai flow Step 2: Simpan semua data
        $this->gradingGoodsService->updateSortingResultStep2(
            $id,
            $request->input('quantity'),
            $request->input('grade_company_name'),
            $request->input('weight_grams'),
            $request->input('notes')
        );

        return redirect()->route('grading-goods.index')->with('success', 'Step 2 selesai. Grading disimpan.');
    }

    public function export()
    {
        // Nama file saat di-download
        $fileName = 'data_grading_barang_' . date('Y-m-d') . '.xlsx';

        // Panggil export class.
        // Kita HARUS passing $this->gradingGoodsService ke constructor
        // karena class export kita membutuhkannya.
        return Excel::download(new GradingGoodsExport($this->gradingGoodsService), $fileName);
    }

    /**
     * BARU: Menampilkan form edit.
     */
    public function edit($id)
    {
        // 1. Dapatkan data grading yang ingin di-edit
        $sortingResult = $this->gradingGoodsService->getSortingResultWithRelations($id);
        if (!$sortingResult) {
            return redirect()->route('grading-goods.index')->with('error', 'Data grading tidak ditemukan.');
        }

        // 2. Dapatkan SEMUA item penerimaan untuk dropdown
        // (agar user bisa mengganti grade supplier)
        $allReceiptItems = $this->gradingGoodsService->getReceiptItemsByGradeSupplierName(null);

        return view('admin.grading-goods.edit', compact('sortingResult', 'allReceiptItems'));
    }

    /**
     * BARU: Menyimpan perubahan dari form edit.
     */
    public function update(UpdateGradingRequest $request, $id)
    {
        try {
            $this->gradingGoodsService->updateFullGrading($id, $request->validated());

            return redirect()->route('grading-goods.index')->with('success', 'Data grading berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * BARU: Menghapus data grading.
     */
    public function destroy($id)
    {
        try {
            $this->gradingGoodsService->deleteGrading($id);
            return redirect()->route('grading-goods.index')->with('success', 'Data grading berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
