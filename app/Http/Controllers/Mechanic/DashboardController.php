<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Models\Inspection;
use App\Models\InspectionCategory;
use App\Models\InspectionItem;
use App\Models\InspectionTemplate;
use App\Models\Sparepart;
use App\Models\ServiceRecommendation;
use App\Services\ServiceOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $mechanicId = auth()->id();

        // My active jobs today
        $assignedJobs = ServiceOrder::with(['customer', 'vehicle.vehicleBrand', 'vehicle.vehicleModel', 'queue'])
            ->where(function ($q) use ($mechanicId) {
                $q->where('mechanic_id', $mechanicId)
                    ->orWhereNull('mechanic_id');
            })
            ->whereIn('status', ['pending', 'inspection', 'in_progress', 'waiting_parts', 'final_check'])
            ->orderBy('id')
            ->get();

        // Completed by me today
        $completedToday = ServiceOrder::with(['customer', 'vehicle.vehicleModel'])
            ->where('mechanic_id', $mechanicId)
            ->whereDate('completed_at', now()->toDateString())
            ->get();

        return view('mechanic.dashboard', compact('assignedJobs', 'completedToday'));
    }

    public function showJob(ServiceOrder $serviceOrder)
    {
        $serviceOrder->load([
            'customer',
            'vehicle.vehicleBrand',
            'vehicle.vehicleModel',
            'queue',
            'serviceOrderItems.service',
            'serviceOrderSpareparts.sparepart',
            'serviceOrderOils.oilProduct',
            'inspections.inspectionItems',
            'serviceRecommendations.sparepart'
        ]);

        $inspectionCategories = InspectionCategory::with('inspectionTemplates')->get();
        $spareparts = Sparepart::where('is_active', true)->where('stock', '>', 0)->get();

        return view('mechanic.job_detail', compact('serviceOrder', 'inspectionCategories', 'spareparts'));
    }

    public function startInspection(ServiceOrder $serviceOrder)
    {
        // Auto assign to self if unassigned
        if (!$serviceOrder->mechanic_id) {
            $serviceOrder->update(['mechanic_id' => auth()->id()]);
        }

        app(ServiceOrderService::class)->updateStatus(
            $serviceOrder,
            'inspection',
            auth()->id(),
            'Mulai pemeriksaan kendaraan'
        );

        return back()->with('success', 'Status pengerjaan diperbarui ke PEMERIKSAAN. Silakan isi checklist.');
    }

    public function storeInspection(Request $request, ServiceOrder $serviceOrder)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.category' => 'required|string',
            'items.*.condition' => 'required|in:baik,perlu_diperiksa,perlu_diganti',
            'items.*.notes' => 'nullable|string',
            'general_notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($serviceOrder, $validated) {
            $inspection = Inspection::create([
                'service_order_id' => $serviceOrder->id,
                'mechanic_id' => auth()->id(),
                'inspected_at' => now(),
                'notes' => $validated['general_notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                $inspItem = InspectionItem::create([
                    'inspection_id' => $inspection->id,
                    'name' => $itemData['name'],
                    'category' => $itemData['category'],
                    'condition' => $itemData['condition'],
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // If condition is perlu_diganti or perlu_diperiksa, auto-suggest recommendation draft
                if ($itemData['condition'] === 'perlu_diganti') {
                    ServiceRecommendation::create([
                        'service_order_id' => $serviceOrder->id,
                        'inspection_item_id' => $inspItem->id,
                        'description' => "Perbaikan/Penggantian: {$itemData['name']}",
                        'status' => 'pending',
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }
            }

            return back()->with('success', 'Hasil pemeriksaan kendaraan berhasil disimpan.');
        });
    }

    public function addRecommendation(Request $request, ServiceOrder $serviceOrder)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'sparepart_id' => 'nullable|exists:spareparts,id',
            'sparepart_price' => 'nullable|numeric|min:0',
            'service_price' => 'nullable|numeric|min:0',
        ]);

        $sparepartPrice = $validated['sparepart_price'] ?? 0;
        $servicePrice = $validated['service_price'] ?? 0;

        if (!empty($validated['sparepart_id']) && $sparepartPrice == 0) {
            $sp = Sparepart::find($validated['sparepart_id']);
            $sparepartPrice = $sp->selling_price;
        }

        ServiceRecommendation::create([
            'service_order_id' => $serviceOrder->id,
            'description' => $validated['description'],
            'sparepart_id' => $validated['sparepart_id'] ?? null,
            'sparepart_price' => $sparepartPrice,
            'service_price' => $servicePrice,
            'total_price' => $sparepartPrice + $servicePrice,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Rekomendasi servis ditambahkan dan menunggu persetujuan pelanggan/admin.');
    }

    public function startWork(ServiceOrder $serviceOrder)
    {
        app(ServiceOrderService::class)->updateStatus(
            $serviceOrder,
            'in_progress',
            auth()->id(),
            'Mulai pengerjaan servis'
        );

        return back()->with('success', 'Status pengerjaan diperbarui ke PENGERJAAN.');
    }

    public function completeWork(ServiceOrder $serviceOrder, Request $request)
    {
        $validated = $request->validate([
            'mechanic_notes' => 'nullable|string',
            'kilometer_out' => 'nullable|integer',
        ]);

        if ($validated['mechanic_notes'] ?? false) {
            $serviceOrder->update(['mechanic_notes' => $validated['mechanic_notes']]);
        }

        app(ServiceOrderService::class)->updateStatus(
            $serviceOrder,
            'final_check',
            auth()->id(),
            'Pengerjaan servis selesai, dalam pemeriksaan akhir'
        );

        // If wash is requested as part of queue, assign to wash queue
        if ($serviceOrder->queue && in_array($serviceOrder->queue->service_type, ['servis_cuci'])) {
            if ($serviceOrder->washOrder) {
                app(\App\Services\WashService::class)->updateStatus(
                    $serviceOrder->washOrder,
                    'antri_cuci',
                    auth()->id(),
                    'Servis selesai, kendaraan masuk antrean cuci'
                );
            }
        }

        return back()->with('success', 'Pekerjaan berhasil diselesaikan.');
    }
}
