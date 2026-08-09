<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WashOrder;
use App\Models\WashPackage;
use App\Models\User;
use App\Services\WashService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class WashOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = WashOrder::with(['customer', 'vehicle.vehicleModel', 'washPackage', 'washer', 'queue'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $washOrders = $query->paginate(15);

        return view('admin.wash_orders.index', compact('washOrders', 'status'));
    }

    public function updateStatus(Request $request, WashOrder $washOrder)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        app(WashService::class)->updateStatus(
            $washOrder,
            $validated['status'],
            auth()->id(),
            $validated['notes'] ?? null
        );

        return back()->with('success', 'Status cuci motor berhasil diperbarui.');
    }

    public function assignWasher(Request $request, WashOrder $washOrder)
    {
        $validated = $request->validate([
            'washer_id' => 'required|exists:users,id',
        ]);

        app(WashService::class)->assignWasher($washOrder, $validated['washer_id']);

        return back()->with('success', 'Petugas cuci berhasil ditugaskan.');
    }

    public function createInvoice(WashOrder $washOrder)
    {
        if ($washOrder->invoice) {
            return redirect()->route('admin.invoices.show', $washOrder->invoice);
        }

        $invoice = app(InvoiceService::class)->createFromWashOrder($washOrder, auth()->id());

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', "Invoice cuci {$invoice->invoice_number} berhasil dibuat.");
    }
}
