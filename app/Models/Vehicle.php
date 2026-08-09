<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'vehicle_brand_id',
        'vehicle_model_id',
        'license_plate',
        'year',
        'color',
        'last_kilometer',
        'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicleBrand()
    {
        return $this->belongsTo(VehicleBrand::class);
    }

    public function vehicleModel()
    {
        return $this->belongsTo(VehicleModel::class);
    }

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function washOrders()
    {
        return $this->hasMany(WashOrder::class);
    }

    public function serviceHistories()
    {
        return $this->hasMany(ServiceHistory::class);
    }

    public function maintenanceReminders()
    {
        return $this->hasMany(MaintenanceReminder::class);
    }

    public function getFullNameAttribute()
    {
        $brand = $this->vehicleBrand ? $this->vehicleBrand->name : '';
        $model = $this->vehicleModel ? $this->vehicleModel->name : '';
        return trim("{$brand} {$model}");
    }
}
