<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReceiptItem extends Model
{
    use HasFactory;

    protected $table = 'receipt_items';

    // Status constants untuk alur kerja
    const STATUS_MENTAH = 'mentah';
    const STATUS_SELESAI_DISORTIR = 'selesai_disortir';

    protected $fillable = ['purchase_receipt_id', 'grade_supplier_id', 'supplier_weight_grams', 'warehouse_weight_grams', 'difference_grams', 'moisture_percentage', 'is_flagged_red', 'status', 'created_by', 'updated_by'];

    protected $casts = [
        'supplier_weight_grams' => 'integer',
        'warehouse_weight_grams' => 'integer',
        'difference_grams' => 'integer',
        'moisture_percentage' => 'float',
        'is_flagged_red' => 'boolean',
    ];

    public function purchaseReceipt()
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function gradeSupplier()
    {
        return $this->belongsTo(GradeSupplier::class, 'grade_supplier_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Helper methods untuk status
    public function isMentah()
    {
        return $this->status === self::STATUS_MENTAH;
    }

    public function isSelesaiDisortir()
    {
        return $this->status === self::STATUS_SELESAI_DISORTIR;
    }

    // Scope untuk query berdasarkan status
    public function scopeMentah($query)
    {
        return $query->where('status', self::STATUS_MENTAH);
    }

    public function scopeSelesaiDisortir($query)
    {
        return $query->where('status', self::STATUS_SELESAI_DISORTIR);
    }

    public function sortingResults()
    {
        return $this->hasMany(SortingResult::class, 'receipt_item_id');
    }

    public function hasSortingResults()
    {
        return $this->sortingResults()->exists();
    }

    public function canBeGraded()
    {
        return $this->isMentah() && !$this->hasSortingResults();
    }

    // Method untuk mendapatkan total weight dari hasil grading
    public function getTotalGradedWeight()
    {
        return $this->sortingResults()->sum('weight_grams');
    }
}
