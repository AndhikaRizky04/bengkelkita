<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Models\WashOrder;
use App\Models\Sparepart;
use App\Models\ServiceOrderItem;
use App\Models\ServiceOrderSparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'monthly'); // daily, weekly, monthly
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());

        // 1. Revenue Report
        $payments = Payment::whereBetween(DB::raw('DATE(payment_date)'), [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();

        $totalRevenue = $payments->sum('amount');
        $cashRevenue = $payments->where('payment_method', 'tunai')->sum('amount');
        $transferRevenue = $payments->where('payment_method', 'transfer')->sum('amount');
        $qrisRevenue = $payments->where('payment_method', 'qris')->sum('amount');

        // 2. Service Report
        $completedServices = ServiceOrder::whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->whereIn('status', ['ready', 'completed'])
            ->get();

        $topServices = ServiceOrderItem::select('service_id', DB::raw('COUNT(*) as total_count'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('serviceOrder', fn($q) => $q->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]))
            ->with('service')
            ->groupBy('service_id')
            ->orderByDesc('total_count')
            ->take(5)
            ->get();

        // 3. Wash Report
        $completedWashes = WashOrder::whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->where('status', 'selesai')
            ->get();

        $topWashPackages = WashOrder::select('wash_package_id', DB::raw('COUNT(*) as total_count'), DB::raw('SUM(price) as total_revenue'))
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->where('status', 'selesai')
            ->with('washPackage')
            ->groupBy('wash_package_id')
            ->orderByDesc('total_count')
            ->get();

        // 4. Sparepart Usage Report
        $topSpareparts = ServiceOrderSparepart::select('sparepart_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('serviceOrder', fn($q) => $q->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]))
            ->with('sparepart')
            ->groupBy('sparepart_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $inventoryValue = Sparepart::select(DB::raw('SUM(purchase_price * stock) as total_val'))->value('total_val');

        return view('admin.reports.index', compact(
            'period',
            'startDate',
            'endDate',
            'totalRevenue',
            'cashRevenue',
            'transferRevenue',
            'qrisRevenue',
            'completedServices',
            'topServices',
            'completedWashes',
            'topWashPackages',
            'topSpareparts',
            'inventoryValue'
        ));
    }
}
