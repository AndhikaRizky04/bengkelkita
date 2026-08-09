<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OilProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'name',
        'viscosity',
        'type',
        'price',
        'stock',
        'unit',
        'minimum_stock',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
    ];

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'minimum_stock');
    }
}
