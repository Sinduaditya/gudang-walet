<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $table = 'inventory_transactions';

    protected $fillable = [
        'transaction_date',
        'grade_company_id',
        'location_id',
        'quantity_change_grams',
        'transaction_type',
        'reference_id',
        'created_by'
    ];

    public function gradeCompany()
    {
        return $this->belongsTo(GradeCompany::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
