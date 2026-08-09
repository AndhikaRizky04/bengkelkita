<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use App\Models\Service;
use App\Models\WashPackage;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Queue;
use App\Services\QueueService;
use App\Services\ServiceOrderService;
use App\Services\WashService;
use Illuminate\Support\Facades\DB;

class LandingController extends Controller
{
    public function index()
    {
        $brands = VehicleBrand::where('is_active', true)->with('vehicleModels')->get();
        $services = Service::where('is_active', true)->take(6)->get();
        $washPackages = WashPackage::where('is_active', true)->orderBy('sort_order')->get();

        return view('public.landing', compact('brands', 'services', 'washPackages'));
    }

    public function getModels(VehicleBrand $brand)
    {
        return response()->json(
            VehicleModel::where('vehicle_brand_id', $brand->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
        );
    }

    public function registerService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'license_plate' => 'required|string|max:20',
            'vehicle_brand_id' => 'required|exists:vehicle_brands,id',
            'vehicle_model_id' => 'required|exists:vehicle_models,id',
            'year' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'kilometer' => 'required|integer|min:0',
            'service_type' => 'required|in:servis,cuci,servis_cuci',
            'wash_package_id' => 'required_if:service_type,cuci,servis_cuci|nullable|exists:wash_packages,id',
            'complaint' => 'required|string|max:1000',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'license_plate.required' => 'Nomor polisi kendaraan wajib diisi.',
            'vehicle_brand_id.required' => 'Merk motor wajib dipilih.',
            'vehicle_model_id.required' => 'Tipe motor wajib dipilih.',
            'kilometer.required' => 'Kilometer kendaraan wajib diisi.',
            'service_type.required' => 'Pilih jenis layanan.',
            'complaint.required' => 'Keluhan kendaraan wajib diisi.',
        ]);

        return DB::transaction(function () use ($validated) {
            // Clean license plate
            $plate = strtoupper(preg_replace('/\s+/', ' ', trim($validated['license_plate'])));

            // 1. Find or create customer
            $customer = Customer::firstOrCreate(
                ['phone' => $validated['phone']],
                ['name' => $validated['name']]
            );

            if ($customer->name !== $validated['name']) {
                $customer->update(['name' => $validated['name']]);
            }

            // 2. Find or create vehicle
            $vehicle = Vehicle::where('license_plate', $plate)->first();
            if (!$vehicle) {
                $vehicle = Vehicle::create([
                    'customer_id' => $customer->id,
                    'vehicle_brand_id' => $validated['vehicle_brand_id'],
                    'vehicle_model_id' => $validated['vehicle_model_id'],
                    'license_plate' => $plate,
                    'year' => $validated['year'] ?? date('Y'),
                    'last_kilometer' => $validated['kilometer'],
                ]);
            } else {
                $vehicle->update(['last_kilometer' => max($vehicle->last_kilometer, $validated['kilometer'])]);
            }

            // 3. Create Queue
            $queueService = app(QueueService::class);
            $queue = $queueService->createQueue([
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'service_type' => $validated['service_type'],
                'complaint' => $validated['complaint'],
            ]);

            // 4. Create ServiceOrder if service requested
            if (in_array($validated['service_type'], ['servis', 'servis_cuci'])) {
                app(ServiceOrderService::class)->createFromQueue($queue);
            }

            // 5. Create WashOrder if wash requested
            if (in_array($validated['service_type'], ['cuci', 'servis_cuci']) && !empty($validated['wash_package_id'])) {
                $package = WashPackage::find($validated['wash_package_id']);
                app(WashService::class)->createWashOrder([
                    'queue_id' => $queue->id,
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'wash_package_id' => $package->id,
                    'price' => $package->price,
                ]);
            }

            return redirect()->route('public.status', ['queue' => $queue->queue_number])
                ->with('success_queue', "Pendaftaran berhasil! Nomor antrean Anda: {$queue->queue_number}");
        });
    }
}
