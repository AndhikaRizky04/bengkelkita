<?php

namespace App\Http\Controllers\Washer;

use App\Http\Controllers\Controller;
use App\Models\WashOrder;
use App\Services\WashService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $activeWashOrders = WashOrder::with(['customer', 'vehicle.vehicleBrand', 'vehicle.vehicleModel', 'washPackage', 'queue'])
            ->whereIn('status', ['menunggu', 'antri_cuci', 'sedang_dicuci', 'pengeringan', 'finishing', 'quality_check'])
            ->orderByRaw("FIELD(status, 'sedang_dicuci', 'pengeringan', 'finishing', 'antri_cuci', 'menunggu', 'quality_check')")
            ->get();

        $completedToday = WashOrder::with(['customer', 'vehicle.vehicleModel', 'washPackage'])
            ->where('status', 'selesai')
            ->whereDate('completed_at', $today)
            ->get();

        return view('washer.dashboard', compact('activeWashOrders', 'completedToday'));
    }

    public function updateStatus(Request $request, WashOrder $washOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:menunggu,antri_cuci,sedang_dicuci,pengeringan,finishing,quality_check,selesai',
        ]);

        // Auto assign washer to self
        if (!$washOrder->washer_id) {
            $washOrder->update(['washer_id' => auth()->id()]);
        }

        app(WashService::class)->updateStatus(
            $washOrder,
            $validated['status'],
            auth()->id(),
            "Status cuci diubah ke {$validated['status']}"
        );

        return back()->with('success', "Status pencucian kendaraan berhasil diperbarui ke: " . strtoupper($validated['status']));
    }
}
