<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GradeSupplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_url',
        'description'];

    public function receiptItems()
    {
        return $this->hasMany(ReceiptItem::class);
    }
}
