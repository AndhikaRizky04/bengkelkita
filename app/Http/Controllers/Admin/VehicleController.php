<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\VehicleBrand;
use App\Models\ServiceHistory;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $vehicles = Vehicle::with(['customer', 'vehicleBrand', 'vehicleModel'])
            ->when($search, function ($query, $search) {
                $query->where('license_plate', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('vehicleModel', fn($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(15);

        return view('admin.vehicles.index', compact('vehicles', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_brand_id' => 'required|exists:vehicle_brands,id',
            'vehicle_model_id' => 'required|exists:vehicle_models,id',
            'license_plate' => 'required|string|unique:vehicles,license_plate|max:20',
            'year' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'last_kilometer' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ], [
            'license_plate.required' => 'Nomor polisi kendaraan wajib diisi.',
            'license_plate.unique' => 'Nomor polisi kendaraan sudah terdaftar.',
        ]);

        $validated['license_plate'] = strtoupper(preg_replace('/\s+/', ' ', trim($validated['license_plate'])));

        $vehicle = Vehicle::create($validated);

        return back()->with('success', "Kendaraan {$vehicle->license_plate} ({$vehicle->full_name}) berhasil ditambahkan.");
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['customer', 'vehicleBrand', 'vehicleModel', 'serviceOrders.mechanic', 'serviceOrders.serviceOrderItems.service', 'washOrders.washPackage']);

        $serviceHistories = ServiceHistory::where('vehicle_id', $vehicle->id)
            ->latest('service_date')
            ->get();

        return view('admin.vehicles.show', compact('vehicle', 'serviceHistories'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'vehicle_brand_id' => 'required|exists:vehicle_brands,id',
            'vehicle_model_id' => 'required|exists:vehicle_models,id',
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate,' . $vehicle->id,
            'year' => 'nullable|integer',
            'color' => 'nullable|string',
            'last_kilometer' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $validated['license_plate'] = strtoupper(preg_replace('/\s+/', ' ', trim($validated['license_plate'])));

        $vehicle->update($validated);

        return back()->with('success', 'Data kendaraan berhasil diperbarui.');
    }
}
