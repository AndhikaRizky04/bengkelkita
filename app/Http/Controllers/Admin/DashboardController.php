<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\ServiceOrder;
use App\Models\WashOrder;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Sparepart;
use App\Models\OilProduct;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        // Key stats
        $todayRevenue = Payment::whereDate('payment_date', $today)
            ->where('status', 'completed')
            ->sum('amount');

        $vehiclesEntered = Queue::whereDate('queue_date', $today)->count();

        $inProgress = ServiceOrder::whereIn('status', ['pending', 'inspection', 'in_progress', 'waiting_parts', 'final_check'])
            ->count();

        $readyForPickup = Queue::whereDate('queue_date', $today)
            ->where('status', 'siap_diambil')
            ->count();

        $washQueueCount = WashOrder::whereIn('status', ['menunggu', 'antri_cuci', 'sedang_dicuci', 'pengeringan', 'finishing'])
            ->count();

        // Active queues table today
        $activeQueues = Queue::with(['customer', 'vehicle.vehicleBrand', 'vehicle.vehicleModel', 'serviceOrder', 'washOrder'])
            ->whereDate('queue_date', $today)
            ->orderByRaw("FIELD(status, 'menunggu', 'dipanggil', 'pemeriksaan', 'pengerjaan', 'menunggu_sparepart', 'menunggu_pembayaran', 'siap_diambil', 'selesai', 'dibatalkan')")
            ->orderBy('queue_number')
            ->get();

        // Active Wash Orders table
        $activeWashOrders = WashOrder::with(['customer', 'vehicle.vehicleModel', 'washPackage', 'washer'])
            ->whereIn('status', ['menunggu', 'antri_cuci', 'sedang_dicuci', 'pengeringan', 'finishing', 'quality_check'])
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        // Low stock alerts
        $lowStockSpareparts = Sparepart::whereColumn('stock', '<=', 'minimum_stock')->get();
        $lowStockOils = OilProduct::whereColumn('stock', '<=', 'minimum_stock')->get();

        // Payment recap: distinguish online (customer self-service) vs cashier payments.
        // Online payments are tagged with reference_number starting "ONLINE-".
        $todayPayments = Payment::with(['invoice.customer', 'receivedBy'])
            ->whereDate('payment_date', $today)
            ->where('status', 'completed')
            ->latest('payment_date')
            ->get();

        $onlineTodayTotal = $todayPayments
            ->filter(fn ($p) => str_starts_with((string) $p->reference_number, 'ONLINE-'))
            ->sum('amount');

        $cashierTodayTotal = $todayRevenue - $onlineTodayTotal;

        $recentTransactions = $todayPayments->take(8);

        return view('admin.dashboard', compact(
            'todayRevenue',
            'vehiclesEntered',
            'inProgress',
            'readyForPickup',
            'washQueueCount',
            'activeQueues',
            'activeWashOrders',
            'lowStockSpareparts',
            'lowStockOils',
            'onlineTodayTotal',
            'cashierTodayTotal',
            'recentTransactions'
        ));
    }
}
