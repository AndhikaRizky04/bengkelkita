<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use App\Services\QueueService;
use App\Services\ServiceOrderService;
use App\Services\WashService;
use App\Models\WashPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $status = $request->query('status');

        $query = Queue::with(['customer', 'vehicle.vehicleBrand', 'vehicle.vehicleModel', 'serviceOrder', 'washOrder'])
            ->whereDate('queue_date', $date);

        if ($status) {
            $query->where('status', $status);
        }

        $queues = $query->orderBy('queue_number')->paginate(20);

        return view('admin.queues.index', compact('queues', 'date', 'status'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $brands = VehicleBrand::where('is_active', true)->with('vehicleModels')->get();
        $washPackages = WashPackage::where('is_active', true)->get();

        return view('admin.queues.create', compact('customers', 'brands', 'washPackages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_type' => 'required|in:servis,cuci,servis_cuci',
            'wash_package_id' => 'required_if:service_type,cuci,servis_cuci|nullable|exists:wash_packages,id',
            'complaint' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $queue = app(QueueService::class)->createQueue([
                'customer_id' => $validated['customer_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'service_type' => $validated['service_type'],
                'complaint' => $validated['complaint'],
                'notes' => $validated['notes'] ?? null,
            ]);

            if (in_array($validated['service_type'], ['servis', 'servis_cuci'])) {
                app(ServiceOrderService::class)->createFromQueue($queue);
            }

            if (in_array($validated['service_type'], ['cuci', 'servis_cuci']) && !empty($validated['wash_package_id'])) {
                $package = WashPackage::find($validated['wash_package_id']);
                app(WashService::class)->createWashOrder([
                    'queue_id' => $queue->id,
                    'customer_id' => $validated['customer_id'],
                    'vehicle_id' => $validated['vehicle_id'],
                    'wash_package_id' => $package->id,
                    'price' => $package->price,
                ]);
            }

            return redirect()->route('admin.queues.index')->with('success', "Antrean {$queue->queue_number} berhasil dibuat.");
        });
    }

    public function updateStatus(Request $request, Queue $queue)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        app(QueueService::class)->updateStatus($queue, $validated['status'], auth()->id(), $validated['notes'] ?? null);

        return back()->with('success', "Status antrean {$queue->queue_number} diperbarui menjadi {$validated['status']}.");
    }

    public function callNext()
    {
        $queue = app(QueueService::class)->callNext();

        if ($queue) {
            return back()->with('success', "Memanggil antrean {$queue->queue_number} - {$queue->vehicle->full_name}.");
        }

        return back()->with('info', 'Tidak ada antrean yang menunggu.');
    }
}
