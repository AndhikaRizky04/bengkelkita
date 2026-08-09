<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'customer_id',
        'service_type',
        'next_service_date',
        'next_service_kilometer',
        'is_sent',
        'sent_at',
        'notes',
    ];

    protected $casts = [
        'next_service_date' => 'date',
        'sent_at' => 'datetime',
        'is_sent' => 'boolean',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
