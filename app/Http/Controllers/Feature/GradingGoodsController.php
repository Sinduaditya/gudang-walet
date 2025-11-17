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

    public function index(Request $request)
    {
        $filters = [
            'month' => $request->get('month'),
            'year'  => $request->get('year'),
        ];

        $gradings = $this->gradingGoodsService->getAllGrading($filters);

        return view('admin.grading-goods.index', compact('gradings'));
    }

    public function show($id)
    {
        $grading = $this->gradingGoodsService->getSortingResultWithRelations($id);

        if (!$grading) {
            return abort(404, 'Grading not found');
        }

        return view('admin.grading-goods.show', compact('grading'));
    }

    public function createStep1(Request $request)
    {
        $q = $request->query('q');
        $receiptItems = $this->gradingGoodsService->getReceiptItemsByGradeSupplierName($q);
        return view('admin.grading-goods.step1', compact('receiptItems', 'q'));
    }

    public function storeStep1(Step1Request $request)
    {
        $sortingResult = $this->gradingGoodsService->createSortingResultStep1(
            $request->input('grading_date'),
            $request->input('receipt_item_id')
        );

        return redirect()->route('grading-goods.step2', ['id' => $sortingResult->id])
            ->with('success', 'Step 1 tersimpan. Lanjutkan ke Step 2.');
    }

    public function createStep2($id)
    {
        $sortingResult = $this->gradingGoodsService->getSortingResultWithRelations($id);
        if (! $sortingResult) {
            return redirect()->route('grading-goods.index')->with('error', 'Data grading tidak ditemukan.');
        }

        $allGradeCompanies = $this->gradingGoodsService->getAllGradeCompanies();

        return view('admin.grading-goods.step2', compact('sortingResult', 'allGradeCompanies'));
    }

    public function storeStep2(Step2Request $request, $id)
    {
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
        $fileName = 'data_grading_barang_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new GradingGoodsExport($this->gradingGoodsService), $fileName);
    }

    public function edit($id)
    {
        $sortingResult = $this->gradingGoodsService->getSortingResultWithRelations($id);
        if (!$sortingResult) {
            return redirect()->route('grading-goods.index')->with('error', 'Data grading tidak ditemukan.');
        }

        $allReceiptItems = $this->gradingGoodsService->getReceiptItemsByGradeSupplierName(null);

        $allGradeCompanies = $this->gradingGoodsService->getAllGradeCompanies();

        return view('admin.grading-goods.edit', compact('sortingResult', 'allReceiptItems', 'allGradeCompanies'));
    }

    public function update(UpdateGradingRequest $request, $id)
    {
        try {
            $this->gradingGoodsService->updateFullGrading($id, $request->validated());

            return redirect()->route('grading-goods.index')->with('success', 'Data grading berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

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
