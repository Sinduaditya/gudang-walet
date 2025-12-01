<?php

namespace App\Services\BarangKeluar;

use App\Models\InventoryTransaction;
use App\Models\StockTransfer;
use App\Models\GradeCompany;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BarangKeluarService
{
    /**
     * Proses penjualan langsung:
     * - Catat transaksi keluar (SALE_OUT)
     *
     * @param array $data
     * @return InventoryTransaction
     */
    public function sell(array $data): InventoryTransaction
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();

            return InventoryTransaction::create([
                'transaction_date'       => $data['transaction_date'] ?? now(),
                'grade_company_id'       => $data['grade_company_id'],
                'location_id'            => $data['location_id'],
                'quantity_change_grams'  => -abs($data['weight_grams']),
                'transaction_type'       => 'SALE_OUT',
                'reference_id'           => null,
                'created_by'             => $userId,
            ]);
        });
    }

    /**
     * Proses transfer internal antar lokasi:
     * - Insert ke stock_transfers
     * - Buat dua baris inventory_transactions (TRANSFER_OUT & TRANSFER_IN)
     *
     * @param array $data
     * @return StockTransfer
     */
    public function transfer(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();

            // Buat record utama untuk transfer
            $transfer = StockTransfer::create([
                'transfer_date'     => $data['transfer_date'] ?? now(),
                'grade_company_id'  => $data['grade_company_id'],
                'from_location_id'  => $data['from_location_id'],
                'to_location_id'    => $data['to_location_id'],
                'weight_grams'      => $data['weight_grams'],
                'notes'             => $data['notes'] ?? null,
                'created_by'        => $userId,
            ]);

            // Buat dua transaksi inventory (OUT & IN)
            $this->createTransferTransactions($transfer, $data, $userId);

            return $transfer;
        });
    }

    /**
     * Buat dua transaksi inventory untuk transfer internal
     *
     * @param StockTransfer $transfer
     * @param array $data
     * @param int $userId
     * @return void
     */
    protected function createTransferTransactions(StockTransfer $transfer, array $data, int $userId): void
    {
        // TRANSFER_OUT dari lokasi asal (quantity negatif)
        InventoryTransaction::create([
            'transaction_date'       => $data['transfer_date'] ?? now(),
            'grade_company_id'       => $data['grade_company_id'],
            'location_id'            => $data['from_location_id'],
            'quantity_change_grams'  => -abs($data['weight_grams']),
            'transaction_type'       => 'TRANSFER_OUT',
            'reference_id'           => $transfer->id,
            'created_by'             => $userId,
        ]);

        // TRANSFER_IN ke lokasi tujuan (quantity positif)
        InventoryTransaction::create([
            'transaction_date'       => $data['transfer_date'] ?? now(),
            'grade_company_id'       => $data['grade_company_id'],
            'location_id'            => $data['to_location_id'],
            'quantity_change_grams'  => abs($data['weight_grams']),
            'transaction_type'       => 'TRANSFER_IN',
            'reference_id'           => $transfer->id,
            'created_by'             => $userId,
        ]);
    }

    /**
     * Proses transfer eksternal (dari supplier/partner ke internal):
     * - Insert ke stock_transfers
     * - Buat satu transaksi EXTERNAL_TRANSFER_IN (positif)
     *
     * @param array $data
     * @return StockTransfer
     */
    public function externalTransfer(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();

            $transfer = StockTransfer::create([
                'transfer_date'     => $data['transfer_date'] ?? now(),
                'grade_company_id'  => $data['grade_company_id'],
                'from_location_id'  => $data['from_location_id'], // Gudang Utama
                'to_location_id'    => $data['to_location_id'],   // Jasa Cuci
                'weight_grams'      => $data['weight_grams'],
                'notes'             => $data['notes'] ?? null,
                'created_by'        => $userId,
            ]);

            // EXTERNAL_TRANSFER_OUT (negatif) di Gudang Utama
            InventoryTransaction::create([
                'transaction_date'       => $data['transfer_date'] ?? now(),
                'grade_company_id'       => $data['grade_company_id'],
                'location_id'            => $data['from_location_id'], // Gudang Utama
                'quantity_change_grams'  => -abs($data['weight_grams']),
                'transaction_type'       => 'EXTERNAL_TRANSFER_OUT',
                'reference_id'           => $transfer->id,
                'created_by'             => $userId,
            ]);

            return $transfer;
        });
    }

    public function receiveInternal(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();

            $transfer = StockTransfer::create([
                'transfer_date'     => $data['transfer_date'] ?? now(),
                'grade_company_id'  => $data['grade_company_id'],
                'from_location_id'  => $data['from_location_id'], // IDM/DMK
                'to_location_id'    => $data['to_location_id'],   // Gudang Utama
                'weight_grams'      => $data['weight_grams'],
                'notes'             => $data['notes'] ?? null,
                'created_by'        => $userId,
            ]);

            // RECEIVE_INTERNAL_IN (positif) di Gudang Utama
            InventoryTransaction::create([
                'transaction_date'       => $data['transfer_date'] ?? now(),
                'grade_company_id'       => $data['grade_company_id'],
                'location_id'            => $data['to_location_id'], // Gudang Utama
                'quantity_change_grams'  => abs($data['weight_grams']),
                'transaction_type'       => 'RECEIVE_INTERNAL_IN',
                'reference_id'           => $transfer->id,
                'created_by'             => $userId,
            ]);

            return $transfer;
        });
    }

    public function receiveExternal(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();

            $transfer = StockTransfer::create([
                'transfer_date'     => $data['transfer_date'] ?? now(),
                'grade_company_id'  => $data['grade_company_id'],
                'from_location_id'  => $data['from_location_id'], // Jasa Cuci
                'to_location_id'    => $data['to_location_id'],   // Gudang Utama
                'weight_grams'      => $data['weight_grams'],
                'notes'             => $data['notes'] ?? null,
                'created_by'        => $userId,
            ]);

            // RECEIVE_EXTERNAL_IN (positif) di Gudang Utama
            InventoryTransaction::create([
                'transaction_date'       => $data['transfer_date'] ?? now(),
                'grade_company_id'       => $data['grade_company_id'],
                'location_id'            => $data['to_location_id'], // Gudang Utama
                'quantity_change_grams'  => abs($data['weight_grams']),
                'transaction_type'       => 'RECEIVE_EXTERNAL_IN',
                'reference_id'           => $transfer->id,
                'created_by'             => $userId,
            ]);

            return $transfer;
        });
    }

    /**
     * Ambil stok per lokasi dengan grade dan lokasi relation
     *
     * @param int|null $gradeCompanyId Filter berdasarkan grade tertentu
     * @param int|null $locationId Filter berdasarkan lokasi tertentu
     * @return \Illuminate\Support\Collection
     */
    public function getStockPerLocation(?int $gradeCompanyId = null, ?int $locationId = null)
    {
        $query = InventoryTransaction::selectRaw(
            'grade_company_id, location_id, SUM(quantity_change_grams) AS current_stock_grams'
        );

        // Filter by grade jika diberikan
        if ($gradeCompanyId) {
            $query->where('grade_company_id', $gradeCompanyId);
        }

        // Filter by location jika diberikan
        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        $rows = $query->groupBy('grade_company_id', 'location_id')
                     ->having('current_stock_grams', '>', 0) // Hanya stok > 0
                     ->get();

        // Attach relasi grade & lokasi untuk kemudahan akses
        $rows->load(['gradeCompany', 'location']);

        return $rows;
    }

    /**
     * Ambil stok tersedia untuk grade tertentu di lokasi tertentu
     *
     * @param int $gradeCompanyId
     * @param int $locationId
     * @return float Stok dalam gram
     */
    public function getAvailableStock(int $gradeCompanyId, int $locationId): float
    {
        $stock = InventoryTransaction::where('grade_company_id', $gradeCompanyId)
            ->where('location_id', $locationId)
            ->sum('quantity_change_grams');

        return max(0, $stock); // Tidak boleh negatif
    }

    /**
     * Validasi apakah stok mencukupi untuk transaksi
     *
     * @param int $gradeCompanyId
     * @param int $locationId
     * @param float $requiredGrams
     * @return bool
     */
    public function hasEnoughStock(int $gradeCompanyId, int $locationId, float $requiredGrams): bool
    {
        $availableStock = $this->getAvailableStock($gradeCompanyId, $locationId);
        return $availableStock >= $requiredGrams;
    }

    /**
     * Get ringkasan stok per grade (semua lokasi)
     *
     * @return \Illuminate\Support\Collection
     */
    public function getStockSummaryByGrade()
    {
        return InventoryTransaction::selectRaw(
            'grade_company_id, SUM(quantity_change_grams) AS total_stock_grams'
        )
            ->groupBy('grade_company_id')
            ->having('total_stock_grams', '>', 0)
            ->with('gradeCompany')
            ->get();
    }
}
