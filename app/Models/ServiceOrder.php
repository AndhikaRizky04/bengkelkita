<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'queue_id',
        'customer_id',
        'vehicle_id',
        'mechanic_id',
        'status',
        'complaint',
        'diagnosis',
        'mechanic_notes',
        'kilometer_in',
        'kilometer_out',
        'started_at',
        'completed_at',
        'total_service_cost',
        'total_sparepart_cost',
        'total_oil_cost',
        'total_wash_cost',
        'discount',
        'grand_total',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_service_cost' => 'float',
        'total_sparepart_cost' => 'float',
        'total_oil_cost' => 'float',
        'total_wash_cost' => 'float',
        'discount' => 'float',
        'grand_total' => 'float',
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function mechanic()
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }

    public function serviceOrderItems()
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function serviceOrderSpareparts()
    {
        return $this->hasMany(ServiceOrderSparepart::class);
    }

    public function serviceOrderOils()
    {
        return $this->hasMany(ServiceOrderOil::class);
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function serviceRecommendations()
    {
        return $this->hasMany(ServiceRecommendation::class);
    }

    public function statusLogs()
    {
        return $this->morphMany(StatusLog::class, 'loggable');
    }

    public function washOrder()
    {
        return $this->hasOne(WashOrder::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function calculateTotals()
    {
        $this->total_service_cost = $this->serviceOrderItems()->sum('subtotal');
        $this->total_sparepart_cost = $this->serviceOrderSpareparts()->sum('subtotal');
        $this->total_oil_cost = $this->serviceOrderOils()->sum('subtotal');
        $washOrder = $this->washOrder;
        $this->total_wash_cost = $washOrder ? $washOrder->price : 0;
        $this->grand_total = $this->total_service_cost + $this->total_sparepart_cost + $this->total_oil_cost + $this->total_wash_cost - $this->discount;
        $this->save();
    }

    public static function generateOrderNumber()
    {
        $last = static::orderBy('id', 'desc')->first();
        $number = $last ? intval(substr($last->order_number, 3)) + 1 : 1001;
        return 'WO-' . $number;
    }
}
