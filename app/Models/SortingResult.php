<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SortingResult extends Model
{
    use HasFactory;

    protected $table = 'sorting_results';

    protected $fillable = [
        'grading_date',
        'receipt_item_id',
        'grade_company_id',
        'weight_grams',
        'quantity',
        'percentage_difference',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'grading_date' => 'date',
        'weight_grams' => 'decimal:2',
        'percentage_difference' => 'decimal:2',
    ];

    public function receiptItem()
    {
        return $this->belongsTo(ReceiptItem::class);
    }

    public function gradeCompany()
    {
        return $this->belongsTo(GradeCompany::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purchaseReceipt()
    {
        return $this->hasOneThrough(
            PurchaseReceipt::class,
            ReceiptItem::class,
            'id',
            'id', 
            'receipt_item_id',
            'purchase_receipt_id'
        );
    }

    public function supplier()
    {
        return $this->hasOneThrough(
            Supplier::class,
            PurchaseReceipt::class,
            'id',
            'id',
            'receipt_item_id',
            'supplier_id'
        )->through('receiptItem');
    }
}