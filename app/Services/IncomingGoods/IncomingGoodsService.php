<?php

namespace App\Services\IncomingGoods;

use App\Models\PurchaseReceipt;
use App\Models\ReceiptItem;
use App\Models\Supplier;
use App\Models\GradeSupplier;
use Illuminate\Support\Facades\DB;
use Exception;

class IncomingGoodsService
{
    public function getAllReceipts()
    {
        return PurchaseReceipt::with(['supplier', 'receiptItems'])
            ->latest('receipt_date')
            ->paginate(15);
    }

    /**
     * Get all suppliers for dropdown
     */
    public function getSuppliers()
    {
        return Supplier::orderBy('name')->get();
    }

    /**
     * Get all grade suppliers for card checkbox
     */
    public function getGradeSuppliers()
    {
        return GradeSupplier::orderBy('name')->get();
    }

    /**
     * Get selected grade suppliers by IDs
     */
    public function getSelectedGrades(array $gradeIds)
    {
        return GradeSupplier::whereIn('id', $gradeIds)->orderBy('name')->get();
    }

    /**
     * Get supplier by ID
     */
    public function getSupplierById($supplierId)
    {
        return Supplier::findOrFail($supplierId);
    }

    /**
     * Create purchase receipt and items (Final Step)
     */
    public function createPurchaseReceipt(array $step1Data, array $step2Data, array $step3Data)
    {
        try {
            return DB::transaction(function () use ($step1Data, $step2Data, $step3Data) {
                // Create parent record (Purchase Receipt)
                $receipt = PurchaseReceipt::create([
                    'supplier_id' => $step1Data['supplier_id'],
                    'receipt_date' => $step1Data['receipt_date'],
                    'unloading_date' => $step1Data['unloading_date'],
                    'notes' => $step1Data['notes'] ?? null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                // Create child records (Receipt Items)
                foreach ($step1Data['grade_ids'] as $gradeId) {
                    $beratAwal = $step2Data['berat_awal'][$gradeId] ?? 0;
                    $kadarAir = $step2Data['kadar_air'][$gradeId] ?? 0;
                    $beratAkhir = $step3Data['berat_akhir'][$gradeId] ?? 0;

                    // Calculate difference
                    $selisih = $beratAwal - $beratAkhir;
                    
                    // Flag if there's any difference
                    $isFlagged = ($selisih != 0);

                    ReceiptItem::create([
                        'receipt_id' => $receipt->id,
                        'grade_supplier_id' => $gradeId,
                        'supplier_weight_grams' => $beratAwal,
                        'warehouse_weight_grams' => $beratAkhir,
                        'difference_grams' => $selisih,
                        'moisture_percent' => $kadarAir,
                        'is_flagged_red' => $isFlagged,
                        'status' => 'received',
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);
                }

                return $receipt->load(['supplier', 'receiptItems.gradeSupplier']);
            });
        } catch (Exception $e) {
            throw new Exception('Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Clear wizard session data
     */
    public function clearWizardSession()
    {
        session()->forget(['step1_data', 'step2_data']);
    }
}