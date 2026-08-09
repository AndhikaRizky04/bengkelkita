<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_order_id',
        'inspection_item_id',
        'description',
        'sparepart_id',
        'sparepart_price',
        'service_price',
        'total_price',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'sparepart_price' => 'float',
        'service_price' => 'float',
        'total_price' => 'float',
        'approved_at' => 'datetime',
    ];

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function inspectionItem()
    {
        return $this->belongsTo(InspectionItem::class);
    }

    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
