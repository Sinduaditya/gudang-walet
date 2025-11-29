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
            'year' => $request->get('year'),
        ];

        $gradings = $this->gradingGoodsService->getAllGrading($filters);

        return view('admin.grading-goods.index', compact('gradings'));
    }

    public function show($receiptItemId)
    {
        $allGradingResults = $this->gradingGoodsService->getSortingResultsByReceiptItem($receiptItemId);

        if ($allGradingResults->isEmpty()) {
            return abort(404, 'Grading not found');
        }

        $grading = $allGradingResults->first();

        return view('admin.grading-goods.show', compact('grading', 'allGradingResults'));
    }

    public function createStep1(Request $request)
    {
        $q = $request->query('q');
        $receiptItems = $this->gradingGoodsService->getReceiptItemsByGradeSupplierName($q);
        return view('admin.grading-goods.step1', compact('receiptItems', 'q'));
    }

    public function storeStep1(Step1Request $request)
    {
        $sortingResult = $this->gradingGoodsService->createSortingResultStep1($request->input('grading_date'), $request->input('receipt_item_id'));

        return redirect()
            ->route('grading-goods.step2', ['id' => $sortingResult->id])
            ->with('success', 'Step 1 tersimpan. Lanjutkan ke Step 2.');
    }

    public function createStep2($id)
    {
        $sortingResult = $this->gradingGoodsService->getSortingResultWithRelations($id);
        if (!$sortingResult) {
            return redirect()->route('grading-goods.index')->with('error', 'Data grading tidak ditemukan.');
        }

        $allGradeCompanies = $this->gradingGoodsService->getAllGradeCompanies();

        return view('admin.grading-goods.step2', compact('sortingResult', 'allGradeCompanies'));
    }

    public function storeStep2(Step2Request $request, $id)
    {
        try {
            $grades = $request->input('grades');
            $globalNotes = $request->input('global_notes');

            $results = $this->gradingGoodsService->updateSortingResultStep2Multiple($id, $grades, $globalNotes);

            $gradesCount = count($results);
            $totalWeight = collect($grades)->sum('weight_grams');

            return redirect()
                ->route('grading-goods.index')
                ->with('success', "Grading berhasil disimpan! Menghasilkan {$gradesCount} grade dengan total berat {$totalWeight} gram.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        // Get filters if any
        $filters = [
            'month' => $request->get('month'),
            'year' => $request->get('year'),
        ];

        // Create filename with date range
        $fileName = 'laporan_grading_barang';

        if (!empty($filters['month']) || !empty($filters['year'])) {
            $fileName .= '_';
            if (!empty($filters['month'])) {
                $fileName .= 'bulan_' . $filters['month'];
            }
            if (!empty($filters['year'])) {
                $fileName .= '_tahun_' . $filters['year'];
            }
        }

        $fileName .= '_' . date('Y-m-d') . '.xlsx';

        // Pass filters to export
        $export = new GradingGoodsExport($this->gradingGoodsService, $filters);
        return Excel::download($export, $fileName);
    }

    // ✅ FIX: Edit berdasarkan receipt_item_id untuk edit semua grading
    public function edit($receiptItemId)
    {
        // ✅ Ambil semua sorting results untuk receipt item ini
        $allGradingResults = $this->gradingGoodsService->getSortingResultsByReceiptItem($receiptItemId);

        if ($allGradingResults->isEmpty()) {
            return redirect()->route('grading-goods.index')->with('error', 'Data grading tidak ditemukan.');
        }

        $receiptItem = $allGradingResults->first()->receiptItem;
        $allGradeCompanies = $this->gradingGoodsService->getAllGradeCompanies();

        return view('admin.grading-goods.edit', compact('allGradingResults', 'receiptItem', 'allGradeCompanies'));
    }

    // ✅ FIX: Update semua grading untuk receipt item
    public function update(Request $request, $receiptItemId)
    {
        // ✅ FIX: Validasi yang lebih fleksibel untuk integer
        $request->validate([
            'grades.*.grading_date' => 'required|date',
            'grades.*.grade_company_name' => 'required|string|max:255',
            'grades.*.quantity' => 'required|numeric|min:0', // ✅ numeric instead of integer
            'grades.*.weight_grams' => 'required|numeric|min:0', // ✅ numeric instead of integer
            'grades.*.notes' => 'nullable|string',
            'global_notes' => 'nullable|string',
        ]);

        try {
            $grades = $request->input('grades');
            $globalNotes = $request->input('global_notes');

            // ✅ FIX: Convert string ke integer sebelum disimpan
            $processedGrades = [];
            foreach ($grades as $grade) {
                $processedGrades[] = [
                    'grading_date' => $grade['grading_date'],
                    'grade_company_name' => $grade['grade_company_name'],
                    'quantity' => (int) $grade['quantity'], // ✅ Cast ke integer
                    'weight_grams' => (int) $grade['weight_grams'], // ✅ Cast ke integer
                    'notes' => $grade['notes'] ?? null,
                ];
            }

            $this->gradingGoodsService->updateMultipleSortingResults($receiptItemId, $processedGrades, $globalNotes);

            return redirect()->route('grading-goods.index')->with('success', 'Data grading berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ✅ FIX: Delete berdasarkan receipt_item_id
    public function destroy($receiptItemId)
    {
        try {
            $this->gradingGoodsService->deleteGrading($receiptItemId);
            return redirect()->route('grading-goods.index')->with('success', 'Data grading berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
