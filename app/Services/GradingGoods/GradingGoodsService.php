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
    public function getAllGrading()
    {
        return SortingResult::select(
            'sorting_results.id',
            'sorting_results.grading_date',
            'sorting_results.weight_grams',
            'sorting_results.quantity',
            'sorting_results.percentage_difference',
            'sorting_results.notes',
            'grades_supplier.name as grade_supplier_name',
            'grades_company.name as grade_company_name',
            'purchase_receipts.receipt_date', // Ini adalah 'tgl_datang'
            'receipt_items.warehouse_weight_grams as warehouse_weight_grams' // Ini adalah 'berat_gudang'
        )
        ->join('receipt_items', 'sorting_results.receipt_item_id', '=', 'receipt_items.id')
        ->join('purchase_receipts', 'receipt_items.purchase_receipt_id', '=', 'purchase_receipts.id')
        ->leftJoin('grades_supplier', 'receipt_items.grade_supplier_id', '=', 'grades_supplier.id')
        ->leftJoin('grades_company', 'sorting_results.grade_company_id', '=', 'grades_company.id')
        ->orderBy('sorting_results.grading_date', 'desc')
        ->get();
    }

    // Get receipt items filtered by grade supplier name (used in step1)
    public function getReceiptItemsByGradeSupplierName($name = null)
    {
        // Fungsi ini sesuai flow Step 1:
        // Mencari item (beserta tgl_datang dan berat_gudang)
        // berdasarkan 'nama dari grade supplier' ($name)
        $query = ReceiptItem::select(
            'receipt_items.id',
            'receipt_items.warehouse_weight_grams',
            'receipt_items.supplier_weight_grams',
            'grades_supplier.name as grade_supplier_name',
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

    // Create sorting_result for step 1
    public function createSortingResultStep1($gradingDate, $receiptItemId)
    {
        // Fungsi ini sesuai flow Step 1:
        // 1. Menginputkan tgl grading
        // 2. Menyimpan relasi ke receipt_item_id (yang dipilih berdasarkan grade_supplier_name)
        // 'tgl_datang' & 'berat_gudang' tidak perlu di-copy, karena sudah terhubung via receipt_item_id
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

    // Fetch sorting result with relations for step 2 view
    public function getSortingResultWithRelations($id)
    {
        return SortingResult::with(['receiptItem.purchaseReceipt', 'receiptItem.gradeSupplier', 'gradeCompany'])
            ->find($id);
    }

    // Update sorting_result for step 2
    public function updateSortingResultStep2($sortingResultId, $quantity, $gradeCompanyName, $weightGrams, $notes = null)
    {
        // Fungsi ini sesuai flow Step 2:
        $sorting = SortingResult::findOrFail($sortingResultId);

        $receiptItem = $sorting->receiptItem;
        if (! $receiptItem) {
            throw new Exception('Receipt item tidak ditemukan untuk sorting result ini.');
        }

        // 1. Menginputkan nama grade perusahaan (find or create)
        $gradeCompany = GradeCompany::firstOrCreate(
            ['name' => $gradeCompanyName],
            ['image_url' => null, 'description' => null]
        );

        // 2. Mengambil 'berat_gudang' dari data Step 1 (via relasi)
        $warehouseWeight = floatval($receiptItem->warehouse_weight_grams);
        // 3. Mengambil 'berat_grading' dari input Step 2
        $gradingWeight = floatval($weightGrams);

        // 4. Menghitung '% selisih'
        $percentage = null;
        if ($warehouseWeight > 0) {
            $percentage = round((($warehouseWeight - $gradingWeight) / $warehouseWeight) * 100, 2);
        }

        // 5. Menyimpan semua data Step 2
        $sorting->quantity = $quantity; // Input 'jumlah item'
        $sorting->grade_company_id = $gradeCompany->id;
        $sorting->weight_grams = $gradingWeight; // Input 'berat setelah grading'
        $sorting->notes = $notes; // Input 'catatan'
        $sorting->percentage_difference = $percentage; // Menyimpan '% selisih'
        $sorting->save();

        return $sorting;
    }

    public function updateFullGrading($sortingResultId, array $data)
    {
        $sorting = SortingResult::findOrFail($sortingResultId);

        // 1. Dapatkan ReceiptItem yang baru (atau lama)
        // Ini penting untuk menghitung ulang 'berat gudang' dan '% selisih'
        $receiptItem = ReceiptItem::findOrFail($data['receipt_item_id']);

        // 2. Cari atau buat GradeCompany
        $gradeCompany = GradeCompany::firstOrCreate(
            ['name' => $data['grade_company_name']],
            ['image_url' => null, 'description' => null]
        );

        // 3. Ambil 'berat_gudang' baru dan 'berat_grading' baru
        $warehouseWeight = floatval($receiptItem->warehouse_weight_grams);
        $gradingWeight = floatval($data['weight_grams']);

        // 4. Hitung ulang '% selisih'
        $percentage = null;
        if ($warehouseWeight > 0) {
            $percentage = round((($warehouseWeight - $gradingWeight) / $warehouseWeight) * 100, 2);
        }

        // 5. Update semua field di SortingResult
        $sorting->grading_date = $data['grading_date'];
        $sorting->receipt_item_id = $data['receipt_item_id']; // Meng-update relasi item
        $sorting->quantity = $data['quantity'];
        $sorting->grade_company_id = $gradeCompany->id;
        $sorting->weight_grams = $gradingWeight;
        $sorting->notes = $data['notes'];
        $sorting->percentage_difference = $percentage; // Menyimpan selisih baru

        $sorting->save();

        return $sorting;
    }

    /**
     * BARU: Menghapus data grading.
     */
    public function deleteGrading($sortingResultId)
    {
        $sorting = SortingResult::findOrFail($sortingResultId);
        $sorting->delete();

        return true;
    }
}
