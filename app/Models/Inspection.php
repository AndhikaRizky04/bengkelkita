<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_order_id',
        'mechanic_id',
        'inspected_at',
        'notes',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
    ];

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function mechanic()
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }

    public function inspectionItems()
    {
        return $this->hasMany(InspectionItem::class);
    }
}
