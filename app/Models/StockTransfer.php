<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockTransfer extends Model
{
    use HasFactory;

    protected $table = 'stock_transfers';

    protected $fillable = [
        'transfer_date',
        'grade_company_id',
        'from_location_id',
        'to_location_id',
        'weight_grams',
        'notes',
        'created_by'
    ];

    public function gradeCompany()
    {
        return $this->belongsTo(GradeCompany::class);
    }

    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
