<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrderOil extends Model
{
    use HasFactory;

    protected $table = 'service_order_oils';

    protected $fillable = [
        'service_order_id',
        'oil_product_id',
        'quantity',
        'price',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'price' => 'float',
        'subtotal' => 'float',
    ];

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function oilProduct()
    {
        return $this->belongsTo(OilProduct::class);
    }
}
