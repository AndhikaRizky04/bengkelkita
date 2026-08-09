<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Invoice::with(['customer', 'serviceOrder.vehicle.vehicleModel', 'washOrder.vehicle.vehicleModel', 'payments'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $invoices = $query->paginate(15);

        return view('admin.invoices.index', compact('invoices', 'status'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'customer',
            'serviceOrder.vehicle.vehicleBrand',
            'serviceOrder.vehicle.vehicleModel',
            'serviceOrder.queue',
            'washOrder.vehicle.vehicleModel',
            'washOrder.queue',
            'invoiceItems',
            'payments.receivedBy',
            'createdBy'
        ]);

        return view('admin.invoices.show', compact('invoice'));
    }

    public function processPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:tunai,transfer,qris',
            'amount' => 'required|numeric|min:1',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ], [
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'amount.required' => 'Jumlah pembayaran wajib diisi.',
        ]);

        try {
            $payment = app(PaymentService::class)->processPayment(
                $invoice,
                $validated['payment_method'],
                $validated['amount'],
                $validated['reference_number'] ?? null,
                auth()->id(),
                $validated['notes'] ?? null
            );

            return back()->with('success', "Pembayaran sebesar Rp" . number_format($payment->amount, 0, ',', '.') . " berhasil diterima.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
