<?php

namespace App\Services\Stock;

use App\Models\GradeCompany;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TrackingStockService
{
    // public function getGradeCompany(?string $search = null): LengthAwarePaginator
    // {
    //     $query = GradeCompany::query();

    //     if ($search) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('name', 'like', "%{$search}%")
    //               ->orWhere('description', 'like', "%{$search}%");
    //         });
    //     }

    //     return $query->latest()->paginate(15)->withQueryString();
    // }

    public function getGradeCompany(?string $search = null): LengthAwarePaginator
    {
        return GradeCompany::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function getAllGradeCompany()
    {
        return GradeCompany::orderBy('name')->get();
    }

    public function getGradeById(int $id): GradeCompany
    {
        return GradeCompany::findOrFail($id);
    }

    public function calculateGlobalStock(int $gradeId): int
    {
        return (int) InventoryTransaction::where('grade_company_id', $gradeId)
            ->sum('quantity_change_grams');
    }

    public function getStockPerLocation(int $gradeId, ?string $search = null): Collection
    {
        return InventoryTransaction::query()
            ->selectRaw('location_id, SUM(quantity_change_grams) as total_stock')
            ->where('grade_company_id', $gradeId)
            // Relasi ke lokasi agar kita bisa ambil namanya
            ->with('location')
            // Filter Search (Mencari nama lokasi, bukan nama grade)
            ->when($search, function ($q) use ($search) {
                $q->whereHas('location', function ($loc) use ($search) {
                    $loc->where('name', 'like', "%{$search}%");
                });
            })
            // Grouping wajib untuk memecah stok per lokasi
            ->groupBy('location_id')
            // Logic: Hanya tampilkan lokasi yang barangnya masih ada ( > 0 )
            ->having('total_stock', '>', 0)
            ->get();
    }
}
