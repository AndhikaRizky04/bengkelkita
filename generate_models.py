import os

path = r"C:\Users\ASUS\.gemini\antigravity\scratch\bengkelkita\app\Models"
os.makedirs(path, exist_ok=True)

models = {
    "Role.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
""",
    "User.php": r"""<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isMechanic()
    {
        return $this->hasRole('mechanic');
    }

    public function isKasir()
    {
        return $this->hasRole('kasir');
    }

    public function isCuci()
    {
        return $this->hasRole('cuci');
    }

    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }
}
""",
    "Customer.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'notes',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
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

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                     ->orWhere('phone', 'like', "%{$search}%")
                     ->orWhere('email', 'like', "%{$search}%");
    }
}
""",
    "VehicleBrand.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function vehicleModels()
    {
        return $this->hasMany(VehicleModel::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
""",
    "VehicleModel.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_brand_id',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function vehicleBrand()
    {
        return $this->belongsTo(VehicleBrand::class);
    }
}
""",
    "Vehicle.php": r"""<?php

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
""",
    "ServiceCategory.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
""",
    "Service.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_category_id',
        'name',
        'description',
        'estimated_duration_minutes',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }
}
""",
    "WashPackage.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WashPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'estimated_duration_minutes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
    ];
}
""",
    "SparepartCategory.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparepartCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function spareparts()
    {
        return $this->hasMany(Sparepart::class);
    }
}
""",
    "Sparepart.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'brand',
        'sparepart_category_id',
        'purchase_price',
        'selling_price',
        'stock',
        'minimum_stock',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'float',
        'selling_price' => 'float',
        'is_active' => 'boolean',
    ];

    public function sparepartCategory()
    {
        return $this->belongsTo(SparepartCategory::class);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'minimum_stock');
    }
}
""",
    "OilProduct.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OilProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'name',
        'viscosity',
        'type',
        'price',
        'stock',
        'unit',
        'minimum_stock',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
    ];

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'minimum_stock');
    }
}
""",
    "Queue.php": r"""<?php

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
""",
    "ServiceOrder.php": r"""<?php

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
""",
    "ServiceOrderItem.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_order_id',
        'service_id',
        'price',
        'quantity',
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

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
""",
    "ServiceOrderSparepart.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrderSparepart extends Model
{
    use HasFactory;

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
""",
    "ServiceOrderOil.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrderOil extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_order_id',
        'oil_product_id',
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

    public function oilProduct()
    {
        return $this->belongsTo(OilProduct::class);
    }
}
""",
    "InspectionCategory.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    public function inspectionTemplates()
    {
        return $this->hasMany(InspectionTemplate::class);
    }
}
""",
    "InspectionTemplate.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_category_id',
        'name',
        'sort_order',
    ];

    public function inspectionCategory()
    {
        return $this->belongsTo(InspectionCategory::class);
    }
}
""",
    "Inspection.php": r"""<?php

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
""",
    "InspectionItem.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'inspection_template_id',
        'name',
        'category',
        'condition',
        'notes',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function inspectionTemplate()
    {
        return $this->belongsTo(InspectionTemplate::class);
    }
}
""",
    "ServiceRecommendation.php": r"""<?php

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
""",
    "WashOrder.php": r"""<?php

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
""",
    "Invoice.php": r"""<?php

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
""",
    "InvoiceItem.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item_type',
        'item_name',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'subtotal' => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
""",
    "Payment.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_method',
        'amount',
        'payment_date',
        'reference_number',
        'status',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
""",
    "InventoryTransaction.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_type',
        'item_id',
        'transaction_type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
""",
    "ServiceHistory.php": r"""<?php

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
""",
    "MaintenanceReminder.php": r"""<?php

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
""",
    "AuditLog.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log($action, $description, $model = null, $oldValues = null, $newValues = null)
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);
    }
}
""",
    "Setting.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value)
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->save();
        return $setting;
    }
}
""",
    "StatusLog.php": r"""<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'from_status',
        'to_status',
        'changed_by',
        'notes',
    ];

    public function loggable()
    {
        return $this->morphTo();
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
"""
}

for filename, content in models.items():
    file_path = os.path.join(path, filename)
    
    if filename != "User.php" and os.path.exists(file_path):
        continue
        
    with open(file_path, "w", encoding="utf-8") as f:
        f.write(content)

print("Models generated successfully.")
