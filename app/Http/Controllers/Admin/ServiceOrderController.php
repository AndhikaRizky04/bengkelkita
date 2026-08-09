<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Models\Service;
use App\Models\Sparepart;
use App\Models\OilProduct;
use App\Models\User;
use App\Services\ServiceOrderService;
use App\Services\InvoiceService;
use App\Models\ServiceRecommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = ServiceOrder::with(['customer', 'vehicle.vehicleModel', 'mechanic', 'queue'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $serviceOrders = $query->paginate(15);

        return view('admin.service_orders.index', compact('serviceOrders', 'status'));
    }

    public function show(ServiceOrder $serviceOrder)
    {
        $serviceOrder->load([
            'customer',
            'vehicle.vehicleBrand',
            'vehicle.vehicleModel',
            'mechanic',
            'queue',
            'serviceOrderItems.service',
            'serviceOrderSpareparts.sparepart',
            'serviceOrderOils.oilProduct',
            'inspections.inspectionItems',
            'serviceRecommendations.sparepart',
            'invoice.payments',
            'statusLogs.changedBy'
        ]);

        $services = Service::where('is_active', true)->get();
        $spareparts = Sparepart::where('is_active', true)->where('stock', '>', 0)->get();
        $oils = OilProduct::where('is_active', true)->where('stock', '>', 0)->get();
        $mechanics = User::whereHas('role', fn($q) => $q->where('name', 'mekanik'))->get();

        return view('admin.service_orders.show', compact('serviceOrder', 'services', 'spareparts', 'oils', 'mechanics'));
    }

    public function updateStatus(Request $request, ServiceOrder $serviceOrder)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        app(ServiceOrderService::class)->updateStatus(
            $serviceOrder,
            $validated['status'],
            auth()->id(),
            $validated['notes'] ?? null
        );

        return back()->with('success', 'Status pengerjaan servis berhasil diperbarui.');
    }

    public function assignMechanic(Request $request, ServiceOrder $serviceOrder)
    {
        $validated = $request->validate([
            'mechanic_id' => 'required|exists:users,id',
        ]);

        app(ServiceOrderService::class)->assignMechanic($serviceOrder, $validated['mechanic_id']);

        return back()->with('success', 'Mekanik berhasil ditugaskan.');
    }

    public function addService(Request $request, ServiceOrder $serviceOrder)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        $service = Service::findOrFail($validated['service_id']);

        app(ServiceOrderService::class)->addServiceItem(
            $serviceOrder,
            $service->id,
            $service->price
        );

        return back()->with('success', "Layanan {$service->name} ditambahkan.");
    }

    public function addSparepart(Request $request, ServiceOrder $serviceOrder)
    {
        $validated = $request->validate([
            'sparepart_id' => 'required|exists:spareparts,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $sparepart = Sparepart::findOrFail($validated['sparepart_id']);

            app(ServiceOrderService::class)->addSparepart(
                $serviceOrder,
                $sparepart->id,
                $sparepart->selling_price,
                $validated['quantity']
            );

            return back()->with('success', "Sparepart {$sparepart->name} ditambahkan & stok diperbarui.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function addOil(Request $request, ServiceOrder $serviceOrder)
    {
        $validated = $request->validate([
            'oil_product_id' => 'required|exists:oil_products,id',
            'quantity' => 'required|numeric|min:0.1',
        ]);

        try {
            $oil = OilProduct::findOrFail($validated['oil_product_id']);

            app(ServiceOrderService::class)->addOil(
                $serviceOrder,
                $oil->id,
                $oil->price,
                $validated['quantity']
            );

            return back()->with('success', "Oli {$oil->name} ditambahkan & stok diperbarui.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateRecommendation(Request $request, ServiceRecommendation $recommendation)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        return DB::transaction(function () use ($recommendation, $validated) {
            $recommendation->update([
                'status' => $validated['status'],
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            if ($validated['status'] === 'approved') {
                $order = $recommendation->serviceOrder;

                // Add sparepart to order if present
                if ($recommendation->sparepart_id) {
                    app(ServiceOrderService::class)->addSparepart(
                        $order,
                        $recommendation->sparepart_id,
                        $recommendation->sparepart_price
                    );
                }

                // Add service fee if present
                if ($recommendation->service_price > 0) {
                    // Create ad-hoc item or service item
                    $order->calculateTotals();
                }
            }

            return back()->with('success', 'Status rekomendasi disetujui/ditolak.');
        });
    }

    public function createInvoice(ServiceOrder $serviceOrder)
    {
        if ($serviceOrder->invoice) {
            return redirect()->route('admin.invoices.show', $serviceOrder->invoice);
        }

        $invoice = app(InvoiceService::class)->createFromServiceOrder($serviceOrder, auth()->id());

        // Update queue status to waiting payment
        if ($serviceOrder->queue) {
            app(\App\Services\QueueService::class)->updateStatus(
                $serviceOrder->queue,
                'menunggu_pembayaran',
                auth()->id(),
                'Faktur diterbitkan'
            );
        }

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} berhasil dibuat.");
    }
}
