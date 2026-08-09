<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Queue;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = [];

        // Search Queues by Queue Number
        $queues = Queue::with(['customer', 'vehicle.vehicleModel'])
            ->where('queue_number', 'like', "%{$q}%")
            ->latest()
            ->take(5)
            ->get();

        foreach ($queues as $queue) {
            $results[] = [
                'type' => 'Antrean',
                'title' => "Antrean {$queue->queue_number}",
                'subtitle' => "{$queue->vehicle->full_name} ({$queue->customer->name}) - Status: {$queue->status}",
                'url' => route('admin.handover.index', ['queue_number' => $queue->queue_number]),
            ];
        }

        // Search Vehicles by License Plate
        $vehicles = Vehicle::with(['customer', 'vehicleModel'])
            ->where('license_plate', 'like', "%{$q}%")
            ->take(5)
            ->get();

        foreach ($vehicles as $vehicle) {
            $results[] = [
                'type' => 'Kendaraan',
                'title' => $vehicle->license_plate,
                'subtitle' => "{$vehicle->full_name} - Pemilik: {$vehicle->customer->name}",
                'url' => route('admin.vehicles.show', $vehicle),
            ];
        }

        // Search Customers by Name or Phone
        $customers = Customer::where('name', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
            ->take(5)
            ->get();

        foreach ($customers as $customer) {
            $results[] = [
                'type' => 'Pelanggan',
                'title' => $customer->name,
                'subtitle' => "WA: {$customer->phone}",
                'url' => route('admin.customers.show', $customer),
            ];
        }

        // Search Service Orders by Order Number
        $orders = ServiceOrder::with(['customer', 'vehicle.vehicleModel'])
            ->where('order_number', 'like', "%{$q}%")
            ->take(5)
            ->get();

        foreach ($orders as $order) {
            $results[] = [
                'type' => 'Order Servis',
                'title' => $order->order_number,
                'subtitle' => "{$order->vehicle->full_name} ({$order->customer->name})",
                'url' => route('admin.service_orders.show', $order),
            ];
        }

        return response()->json($results);
    }
}
