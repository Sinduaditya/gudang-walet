<?php

namespace App\Services\Idm;

use App\Models\IdmTransfer;
use App\Models\IdmTransferDetail;
use App\Models\IdmDetail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransferIdmService
{
    public function getTransfers($filters = [])
    {
        $query = IdmTransfer::withCount('details');

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('transfer_date', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters['search'])) {
            $query->where('transfer_code', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate(10);
    }

    public function getAvailableIdmDetails($filters = [])
    {
        $query = IdmDetail::with(['idmManagement.supplier', 'idmManagement.gradeCompany'])
            ->whereDoesntHave('transferDetails'); // Ensure not already transferred

        // Filter: Grading Date (from IdmManagement)
        if (!empty($filters['grading_date'])) {
            $query->whereHas('idmManagement', function ($q) use ($filters) {
                $q->whereDate('grading_date', $filters['grading_date']);
            });
        }

        // Filter: Supplier
        if (!empty($filters['supplier_id'])) {
            $query->whereHas('idmManagement', function ($q) use ($filters) {
                $q->where('supplier_id', $filters['supplier_id']);
            });
        }

        // Filter: Grade Company
        if (!empty($filters['grade_company_id'])) {
            $query->whereHas('idmManagement', function ($q) use ($filters) {
                $q->where('grade_company_id', $filters['grade_company_id']);
            });
        }

        // Filter: Grade IDM
        if (!empty($filters['grade_idm_name'])) {
            $query->where('grade_idm_name', 'like', '%' . $filters['grade_idm_name'] . '%');
        }

        return $query->get();
    }

    public function generateTransferCode($date)
    {
        // Format: month-dayyear e.g. jan-1025
        $carbonDate = Carbon::parse($date);
        $month = strtolower($carbonDate->format('M')); // jan
        $dayYear = $carbonDate->format('dy'); // 1025 (day 10, year 25)
        
        $baseCode = "{$month}-{$dayYear}";
        $code = $baseCode;
        $counter = 1;

        // Verify uniqueness
        while (IdmTransfer::where('transfer_code', $code)->exists()) {
            $code = "{$baseCode}-{$counter}";
            $counter++;
        }

        return $code;
    }

    public function storeTransfer($data)
    {
        return DB::transaction(function () use ($data) {
            $transfer = IdmTransfer::create([
                'transfer_date' => $data['transfer_date'],
                'transfer_code' => $this->generateTransferCode($data['transfer_date']),
                'sum_goods' => count($data['items']),
                'price_transfer' => $data['total_price'],
                'average_idm_price' => $data['average_idm_price'],
                'total_non_idm_price' => $data['total_non_idm_price'],
                'total_idm_price' => $data['total_idm_price'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                // Determine item name (fallback to grade name if not provided? logic for "Nama Barang")
                // Assuming we pass item details
                IdmTransferDetail::create([
                    'idm_transfer_id' => $transfer->id,
                    'idm_detail_id' => $item['id'],
                    'item_name' => $item['grade_idm_name'] ?? 'Unknown', // Or fetch from somewhere else if needed
                    'grade_idm_name' => $item['grade_idm_name'],
                    'weight' => $item['weight'],
                    'price' => $item['price'],
                    'total_price' => $item['total_price'],
                ]);
            }

            return $transfer;
        });
    }

    public function updateTransfer($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $transfer = IdmTransfer::findOrFail($id);
            
            // Check if date changed to regenerate code
            if ($transfer->transfer_date != $data['transfer_date']) {
                $transfer->transfer_code = $this->generateTransferCode($data['transfer_date']);
            }

            $transfer->transfer_date = $data['transfer_date'];
            $transfer->sum_goods = count($data['items']);
            $transfer->price_transfer = $data['total_price'];
            $transfer->average_idm_price = $data['average_idm_price'];
            $transfer->total_non_idm_price = $data['total_non_idm_price'];
            $transfer->total_idm_price = $data['total_idm_price'];
            $transfer->notes = $data['notes'] ?? null;
            $transfer->save();

            // Sync items: Delete all existing details and recreate
            // This is safe because we are passing the "full state" of desired items
            $transfer->details()->delete();

            foreach ($data['items'] as $item) {
                IdmTransferDetail::create([
                    'idm_transfer_id' => $transfer->id,
                    'idm_detail_id' => $item['id'], // Ensure this maps to idm_detail_id
                    'item_name' => $item['grade_idm_name'] ?? 'Unknown',
                    'grade_idm_name' => $item['grade_idm_name'],
                    'weight' => $item['weight'],
                    'price' => $item['price'],
                    'total_price' => $item['total_price'],
                ]);
            }

            return $transfer;
        });
    }

    public function getTransferById($id)
    {
        return IdmTransfer::with(['details.idmDetail.idmManagement.supplier'])->findOrFail($id);
    }
}
