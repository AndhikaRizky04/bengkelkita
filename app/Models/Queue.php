<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_number',
        'queue_date',
        'customer_id',
        'vehicle_id',
        'service_type',
        'status',
        'complaint',
        'registered_at',
        'called_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'queue_date' => 'date',
        'registered_at' => 'datetime',
        'called_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function serviceOrder()
    {
        return $this->hasOne(ServiceOrder::class);
    }

    public function washOrder()
    {
        return $this->hasOne(WashOrder::class);
    }

    public function statusLogs()
    {
        return $this->morphMany(StatusLog::class, 'loggable');
    }

    public static function generateQueueNumber($date = null)
    {
        $date = $date ?: now()->toDateString();
        $lastQueue = static::where('queue_date', $date)->orderBy('queue_number', 'desc')->first();
        if (!$lastQueue) return 'A-001';
        $lastNumber = intval(substr($lastQueue->queue_number, 2));
        return 'A-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }
}
