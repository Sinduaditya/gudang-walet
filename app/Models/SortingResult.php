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

    public function receiptItem()
    {
        return $this->belongsTo(ReceiptItem::class);
    }

    public function gradeCompany()
    {
        return $this->belongsTo(GradeCompany::class);
    }

    public function gradeSupplier()
    {
        return $this->belongsTo(GradeSupplier::class, 'grade_company_id');
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
            'id', // Foreign key on ReceiptItem table that SortingResult references (receipt_item_id)
            'id', // Foreign key on PurchaseReceipt table that ReceiptItem references (purchase_receipt_id)
            'receipt_item_id', // Local key on SortingResult
            'purchase_receipt_id' // Local key on ReceiptItem
        );
    }
}
