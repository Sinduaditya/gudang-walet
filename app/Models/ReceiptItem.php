<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_id',
        'grade_supplier_id',
        'supplier_weight_grams',
        'warehouse_weight_grams',
        'difference_grams',
        'moisture_percent',
        'is_flagged_red',
        'status',
        'created_by',
        'updated_by',
    ];

    public function receipt()
    {
        return $this->belongsTo(PurchaseReceipt::class, 'receipt_id');
    }

    public function gradeSupplier()
    {
        return $this->belongsTo(GradeSupplier::class, 'grade_supplier_id');
    }

    public function sortingResults()
    {
        return $this->hasMany(SortingResult::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
