<?php

namespace App\Services\IncomingGoods;

use App\Exports\IncomingGoodsExport;
use App\Models\PurchaseReceipt;
use App\Models\ReceiptItem;
use App\Models\Supplier;
use App\Models\GradeSupplier;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class IncomingGoodsService
{
    public function getAllReceipts($filters = [])
    {
        $query = PurchaseReceipt::with(['supplier', 'receiptItems.gradeSupplier']);

        // Apply date filters
        if (!empty($filters['month'])) {
            $query->whereMonth('receipt_date', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('receipt_date', $filters['year']);
        }

        return $query->latest('receipt_date')->paginate(10);
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
     * Get receipt by ID with relationships
     */
    public function getReceiptById($id)
    {
        return PurchaseReceipt::with(['supplier', 'receiptItems.gradeSupplier'])
            ->findOrFail($id);
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
                    $selisih = $beratAkhir - $beratAwal;

                    // Flag if there's any difference
                    $isFlagged = $selisih != 0;

                    ReceiptItem::create([
                        'purchase_receipt_id' => $receipt->id,
                        'grade_supplier_id' => $gradeId,
                        'supplier_weight_grams' => $beratAwal,
                        'warehouse_weight_grams' => $beratAkhir,
                        'difference_grams' => $selisih,
                        'moisture_percentage' => $kadarAir,
                        'is_flagged_red' => $isFlagged,
                        'status' => ReceiptItem::STATUS_MENTAH,
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
     * Update purchase receipt and items
     */
    public function updateReceipt($id, array $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $receipt = PurchaseReceipt::findOrFail($id);

                // Update receipt basic info
                $receipt->update([
                    'supplier_id' => $data['supplier_id'],
                    'receipt_date' => $data['receipt_date'],
                    'unloading_date' => $data['unloading_date'],
                    'notes' => $data['notes'] ?? null,
                    'updated_by' => auth()->id(),
                ]);

                // Delete existing items
                $receipt->receiptItems()->delete();

                // Create new items
                foreach ($data['items'] as $itemData) {
                    $supplierWeight = $itemData['supplier_weight_grams'];
                    $warehouseWeight = $itemData['warehouse_weight_grams'];
                    $difference = $warehouseWeight - $supplierWeight;

                    ReceiptItem::create([
                        'purchase_receipt_id' => $receipt->id,
                        'grade_supplier_id' => $itemData['grade_supplier_id'],
                        'supplier_weight_grams' => $supplierWeight,
                        'warehouse_weight_grams' => $warehouseWeight,
                        'difference_grams' => $difference,
                        'moisture_percentage' => $itemData['moisture_percentage'] ?? null,
                        'is_flagged_red' => $difference != 0,
                        'status' => ReceiptItem::STATUS_MENTAH,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);
                }

                return $receipt->load(['supplier', 'receiptItems.gradeSupplier']);
            });
        } catch (Exception $e) {
            throw new Exception('Gagal mengupdate data: ' . $e->getMessage());
        }
    }

    /**
     * Delete purchase receipt and its items
     */
    public function deleteReceipt($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $receipt = PurchaseReceipt::findOrFail($id);

                // delete related items (if cascade not configured)
                $receipt->receiptItems()->delete();

                $receipt->delete();

                return true;
            });
        } catch (Exception $e) {
            throw new Exception('Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Clear wizard session data
     */
    public function clearWizardSession()
    {
        session()->forget(['step1_data', 'step2_data']);
    }

    /**
     * Export Location to Excel
     *
     */
    public function exportToExcel($filters = [])
    {
        try {
            // Generate filename with filter info
            $filename = 'incoming-goods';
            
            if (!empty($filters['month']) && !empty($filters['year'])) {
                $monthName = date('F', mktime(0, 0, 0, $filters['month'], 1));
                $filename .= '-' . $monthName . '-' . $filters['year'];
            } elseif (!empty($filters['year'])) {
                $filename .= '-' . $filters['year'];
            } elseif (!empty($filters['month'])) {
                $monthName = date('F', mktime(0, 0, 0, $filters['month'], 1));
                $filename .= '-' . $monthName;
            } else {
                $filename .= '-' . date('Y-m-d');
            }

            return Excel::download(new IncomingGoodsExport($filters), $filename . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Export failed: ' . $e->getMessage());
            throw new Exception('Gagal mengekspor data: ' . $e->getMessage());
        }
    }
}
