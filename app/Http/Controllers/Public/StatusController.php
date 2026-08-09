<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Queue;

class StatusController extends Controller
{
    public function index(Request $request)
    {
        $queueNumber = strtoupper(trim($request->query('queue', '')));
        $queue = null;

        if ($queueNumber) {
            $queue = Queue::with([
                'vehicle.vehicleBrand',
                'vehicle.vehicleModel',
                'serviceOrder.serviceOrderItems.service',
                'serviceOrder.serviceOrderSpareparts.sparepart',
                'serviceOrder.serviceOrderOils.oilProduct',
                'serviceOrder.statusLogs',
                'washOrder.washPackage',
                'washOrder.statusLogs'
            ])
            ->where('queue_number', $queueNumber)
            ->whereDate('queue_date', '>=', now()->subDays(7)) // allow checking within 7 days
            ->latest()
            ->first();
        }

        return view('public.status', compact('queue', 'queueNumber'));
    }
}
