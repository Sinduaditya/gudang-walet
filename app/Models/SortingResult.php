<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SortingResult extends Model
{
    use HasFactory;

    protected $table = 'sorting_results';

    protected $fillable = [
        'receipt_item_id',
        'grade_company_id',
        'weight_grams',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
