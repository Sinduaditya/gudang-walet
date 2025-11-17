<?php

namespace App\Services\GradingGoods;

use Exception;
use App\Models\Supplier;
use App\Models\ReceiptItem;
use App\Models\GradeCompany;
use App\Models\GradeSupplier;
use App\Models\SortingResult;
use App\Models\PurchaseReceipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GradingGoodsService
{
    public function getAllGradeCompanies()
    {
        return GradeCompany::orderBy('name')->get();
    }

    public function getAllGrading($filters = [])
    {
        $query = SortingResult::select(
                'sorting_results.id',
                'sorting_results.grading_date',
                'sorting_results.weight_grams',
                'sorting_results.quantity',
                'sorting_results.percentage_difference',
                'sorting_results.notes',
                'grades_supplier.name as grade_supplier_name',
                'grades_company.name as grade_company_name',
                'purchase_receipts.receipt_date',
                'receipt_items.warehouse_weight_grams as warehouse_weight_grams'
            )
            ->join('receipt_items', 'sorting_results.receipt_item_id', '=', 'receipt_items.id')
            ->join('purchase_receipts', 'receipt_items.purchase_receipt_id', '=', 'purchase_receipts.id')
            ->leftJoin('grades_supplier', 'receipt_items.grade_supplier_id', '=', 'grades_supplier.id')
            ->leftJoin('grades_company', 'sorting_results.grade_company_id', '=', 'grades_company.id');

        if (!empty($filters['month'])) {
            $query->whereMonth('sorting_results.grading_date', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('sorting_results.grading_date', $filters['year']);
        }

        return $query->orderBy('sorting_results.grading_date', 'desc')->paginate(10);
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
            'purchase_receipts.receipt_date'
        )
        ->join('purchase_receipts', 'receipt_items.purchase_receipt_id', '=', 'purchase_receipts.id')
        ->leftJoin('grades_supplier', 'receipt_items.grade_supplier_id', '=', 'grades_supplier.id')
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
        return SortingResult::with(['receiptItem.purchaseReceipt', 'receiptItem.gradeSupplier', 'gradeCompany'])
            ->find($id);
    }

    public function updateSortingResultStep2($sortingResultId, $quantity, $gradeCompanyName, $weightGrams, $notes = null)
    {
        $sorting = SortingResult::findOrFail($sortingResultId);

        $receiptItem = $sorting->receiptItem;
        if (! $receiptItem) {
            throw new Exception('Receipt item tidak ditemukan untuk sorting result ini.');
        }

        $gradeCompany = GradeCompany::firstOrCreate(
            ['name' => $gradeCompanyName],
            ['image_url' => null, 'description' => null]
        );

        $warehouseWeight = floatval($receiptItem->warehouse_weight_grams);
        $gradingWeight = floatval($weightGrams);

        $percentage = null;
        if ($warehouseWeight > 0) {
            $percentage = round((($warehouseWeight - $gradingWeight) / $warehouseWeight) * 100, 2);
        }

        $sorting->quantity = $quantity;
        $sorting->grade_company_id = $gradeCompany->id;
        $sorting->weight_grams = $gradingWeight;
        $sorting->notes = $notes;
        $sorting->percentage_difference = $percentage;
        $sorting->save();

        return $sorting;
    }

    public function updateFullGrading($sortingResultId, array $data)
    {
        $sorting = SortingResult::findOrFail($sortingResultId);

        $receiptItem = ReceiptItem::findOrFail($data['receipt_item_id']);

        $gradeCompany = GradeCompany::firstOrCreate(
            ['name' => $data['grade_company_name']],
            ['image_url' => null, 'description' => null]
        );

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
