<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'service_order_id',
        'wash_order_id',
        'service_date',
        'kilometer',
        'description',
        'services_performed',
        'spareparts_used',
        'oil_used',
        'mechanic_name',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'service_date' => 'date',
        'total_cost' => 'float',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function washOrder()
    {
        return $this->belongsTo(WashOrder::class);
    }
}
