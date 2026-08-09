<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WashOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id',
        'service_order_id',
        'customer_id',
        'vehicle_id',
        'wash_package_id',
        'washer_id',
        'status',
        'price',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'price' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function washPackage()
    {
        return $this->belongsTo(WashPackage::class);
    }

    public function washer()
    {
        return $this->belongsTo(User::class, 'washer_id');
    }

    public function statusLogs()
    {
        return $this->morphMany(StatusLog::class, 'loggable');
    }
}
