<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'service_order_id',
        'wash_order_id',
        'customer_id',
        'total_amount',
        'discount',
        'tax',
        'grand_total',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'discount' => 'float',
        'tax' => 'float',
        'grand_total' => 'float',
    ];

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function washOrder()
    {
        return $this->belongsTo(WashOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public static function generateInvoiceNumber()
    {
        $last = static::orderBy('id', 'desc')->first();
        $number = $last ? intval(substr($last->invoice_number, 4)) + 1 : 1001;
        return 'INV-' . $number;
    }
}
