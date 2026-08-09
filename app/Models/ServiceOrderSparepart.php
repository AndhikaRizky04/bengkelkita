<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrderSparepart extends Model
{
    use HasFactory;

    protected $table = 'service_order_spareparts';

    protected $fillable = [
        'service_order_id',
        'sparepart_id',
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

    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class);
    }
}
