<?php

namespace App\Services\GradingGoods;

use Exception;
use App\Models\ReceiptItem;
use App\Models\GradeCompany;
use App\Models\SortingResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GradingGoodsService
{
    public function getAllGradeCompanies()
    {
        return GradeCompany::orderBy('name')->get();
    }

    public function getAllGrading($filters = [], $perPage = 15)
    {
        $query = SortingResult::select(['sorting_results.id', 'sorting_results.grading_date', 'sorting_results.quantity', 'sorting_results.weight_grams', 'sorting_results.percentage_difference', 'sorting_results.notes', 'grades_company.name as grade_company_name', 'grades_supplier.name as grade_supplier_name', 'purchase_receipts.receipt_date', 'suppliers.name as supplier_name'])
            ->leftJoin('grades_company', 'sorting_results.grade_company_id', '=', 'grades_company.id')
            ->leftJoin('receipt_items', 'sorting_results.receipt_item_id', '=', 'receipt_items.id')
            ->leftJoin('grades_supplier', 'receipt_items.grade_supplier_id', '=', 'grades_supplier.id')
            ->leftJoin('purchase_receipts', 'receipt_items.purchase_receipt_id', '=', 'purchase_receipts.id')
            ->leftJoin('suppliers', 'purchase_receipts.supplier_id', '=', 'suppliers.id')
            ->orderBy('sorting_results.grading_date', 'desc')
            ->orderBy('suppliers.name', 'asc')
            ->orderBy('grades_company.name', 'asc');

        // Apply filters
        if (!empty($filters['month'])) {
            $query->whereMonth('sorting_results.grading_date', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('sorting_results.grading_date', $filters['year']);
        }

        // Return paginated results
        return $query->paginate($perPage)->appends(request()->query());
    }

    public function getReceiptItemsByGradeSupplierName($name = null)
    {
        $query = ReceiptItem::select(
            'receipt_items.id',
            'receipt_items.warehouse_weight_grams',
            'receipt_items.supplier_weight_grams',
            'grades_supplier.name as grade_supplier_name',
            'grades_supplier.image_url as grade_supplier_image_url',
            'purchase_receipts.id as purchase_receipt_id',
            'purchase_receipts.receipt_date',
            'suppliers.name as supplier_name', // Tambahkan ini
        )
            ->join('purchase_receipts', 'receipt_items.purchase_receipt_id', '=', 'purchase_receipts.id')
            ->leftJoin('grades_supplier', 'receipt_items.grade_supplier_id', '=', 'grades_supplier.id')
            ->leftJoin('suppliers', 'purchase_receipts.supplier_id', '=', 'suppliers.id') // Tambahkan JOIN ini
            ->where('receipt_items.status', ReceiptItem::STATUS_MENTAH)
            ->whereDoesntHave('sortingResults') // Belum di-grading
            ->orderBy('purchase_receipts.receipt_date', 'desc');

        if (!empty($name)) {
            $query->where('grades_supplier.name', 'like', '%' . $name . '%');
        }

        return $query->get();
    }

    public function createSortingResultStep1($gradingDate, $receiptItemId)
    {
        $data = [
            'grading_date' => $gradingDate,
            'receipt_item_id' => $receiptItemId,
            'grade_company_id' => null,
            'weight_grams' => null,
            'quantity' => null,
            'percentage_difference' => null,
            'notes' => null,
            'created_by' => Auth::id(),
        ];

        return SortingResult::create($data);
    }

    public function getSortingResultWithRelations($id)
    {
        return SortingResult::with(['receiptItem.purchaseReceipt', 'receiptItem.gradeSupplier', 'gradeCompany'])->find($id);
    }

    public function updateSortingResultStep2Multiple($sortingResultId, array $grades, $globalNotes = null)
    {
        try {
            return DB::transaction(function () use ($sortingResultId, $grades, $globalNotes) {
                // Ambil data original SEBELUM dihapus (tanpa withTrashed)
                $originalSortingResult = SortingResult::with(['receiptItem', 'receiptItem.purchaseReceipt.supplier'])->find($sortingResultId);

                if (!$originalSortingResult) {
                    throw new Exception('Data grading tidak ditemukan');
                }

                // Simpan data yang diperlukan sebelum dihapus
                $gradingDate = $originalSortingResult->grading_date;
                $receiptItemId = $originalSortingResult->receipt_item_id;
                $receiptItem = $originalSortingResult->receiptItem;
                $originalWeight = $receiptItem->warehouse_weight_grams;

                // Hapus sorting result yang existing
                SortingResult::where('id', $sortingResultId)->delete();

                $createdResults = [];

                // Buat sorting result untuk setiap grade
                foreach ($grades as $index => $gradeData) {
                    // Cari atau buat grade company
                    $gradeCompany = GradeCompany::firstOrCreate(['name' => $gradeData['grade_company_name']], ['name' => $gradeData['grade_company_name']]);

                    // Hitung persentase difference berdasarkan berat asal
                    $percentageDifference = $originalWeight > 0 ? (($gradeData['weight_grams'] - $originalWeight) / $originalWeight) * 100 : 0;

                    // Gabungkan catatan
                    $notes = collect([$globalNotes, $gradeData['notes'] ?? null, $index > 0 ? 'Grade ke-' . ($index + 1) . ' dari grading berganda' : null])
                        ->filter()
                        ->implode('. ');

                    // Buat sorting result baru
                    $sortingResult = SortingResult::create([
                        'grading_date' => $gradingDate,
                        'receipt_item_id' => $receiptItemId,
                        'quantity' => $gradeData['quantity'],
                        'grade_company_id' => $gradeCompany->id,
                        'weight_grams' => $gradeData['weight_grams'],
                        'percentage_difference' => round($percentageDifference, 2),
                        'notes' => $notes,
                        'created_by' => auth()->id(),
                    ]);

                    $createdResults[] = $sortingResult;
                }

                // Update status receipt item menjadi selesai disortir
                $receiptItem->update(['status' => ReceiptItem::STATUS_SELESAI_DISORTIR]);

                Log::info('Multiple grades grading completed', [
                    'receipt_item_id' => $receiptItem->id,
                    'grades_count' => count($grades),
                    'total_weight' => collect($grades)->sum('weight_grams'),
                    'original_weight' => $originalWeight,
                ]);

                return $createdResults;
            });
        } catch (Exception $e) {
            Log::error('Gagal melakukan grading berganda: ' . $e->getMessage(), [
                'sorting_result_id' => $sortingResultId,
                'grades_count' => count($grades),
                'user_id' => auth()->id(),
                'error_trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function updateFullGrading($sortingResultId, array $data)
    {
        $sorting = SortingResult::findOrFail($sortingResultId);

        $receiptItem = ReceiptItem::findOrFail($data['receipt_item_id']);

        $gradeCompany = GradeCompany::firstOrCreate(['name' => $data['grade_company_name']], ['image_url' => null, 'description' => null]);

        $warehouseWeight = floatval($receiptItem->warehouse_weight_grams);
        $gradingWeight = floatval($data['weight_grams']);

        $percentage = null;
        if ($warehouseWeight > 0) {
            $percentage = round((($warehouseWeight - $gradingWeight) / $warehouseWeight) * 100, 2);
        }

        $sorting->grading_date = $data['grading_date'];
        $sorting->receipt_item_id = $data['receipt_item_id'];
        $sorting->quantity = $data['quantity'];
        $sorting->grade_company_id = $gradeCompany->id;
        $sorting->weight_grams = $gradingWeight;
        $sorting->notes = $data['notes'];
        $sorting->percentage_difference = $percentage;

        $sorting->save();

        return $sorting;
    }

    public function deleteGrading($sortingResultId)
    {
        $sorting = SortingResult::findOrFail($sortingResultId);
        $sorting->delete();

        return true;
    }
}
