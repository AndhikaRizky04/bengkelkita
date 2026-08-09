<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'brand',
        'sparepart_category_id',
        'purchase_price',
        'selling_price',
        'stock',
        'minimum_stock',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'float',
        'selling_price' => 'float',
        'is_active' => 'boolean',
    ];

    public function sparepartCategory()
    {
        return $this->belongsTo(SparepartCategory::class);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'minimum_stock');
    }
}
