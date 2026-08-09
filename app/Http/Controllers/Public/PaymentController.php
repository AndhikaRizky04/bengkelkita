<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Invoice;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Resolve the active invoice for a given queue number.
     * Returns [queue, invoice] or [queue, null] when there is nothing payable.
     */
    protected function resolve(string $queueNumber): array
    {
        $queueNumber = strtoupper(trim($queueNumber));

        $queue = Queue::with([
            'customer',
            'vehicle.vehicleBrand',
            'vehicle.vehicleModel',
            'serviceOrder.invoice.invoiceItems',
            'serviceOrder.invoice.payments',
            'washOrder.invoice.invoiceItems',
            'washOrder.invoice.payments',
        ])
        ->where('queue_number', $queueNumber)
        ->whereDate('queue_date', '>=', now()->subDays(7))
        ->latest()
        ->first();

        if (!$queue) {
            return [null, null];
        }

        $invoice = $queue->serviceOrder?->invoice ?? $queue->washOrder?->invoice;

        return [$queue, $invoice];
    }

    /**
     * Show the customer payment page for a queue number.
     */
    public function show(Request $request, string $queueNumber)
    {
        [$queue, $invoice] = $this->resolve($queueNumber);

        if (!$queue) {
            return redirect()->route('public.status')
                ->with('error', 'Nomor antrean tidak ditemukan atau sudah kedaluwarsa.');
        }

        // Payment is only allowed once an invoice has been issued and is unpaid,
        // i.e. the queue is at the "menunggu_pembayaran" stage.
        if (!$invoice) {
            return redirect()->route('public.status', ['queue' => $queue->queue_number])
                ->with('error', 'Belum ada tagihan untuk antrean ini. Pembayaran baru dapat dilakukan pada tahap Pembayaran.');
        }

        if ($invoice->isPaid()) {
            return redirect()->route('public.status', ['queue' => $queue->queue_number])
                ->with('success_queue', 'Tagihan Anda sudah LUNAS. Silakan tunggu penyerahan kendaraan oleh petugas.');
        }

        $paidSoFar = $invoice->payments->where('status', 'completed')->sum('amount');
        $remaining = max($invoice->grand_total - $paidSoFar, 0);

        return view('public.payment', compact('queue', 'invoice', 'paidSoFar', 'remaining'));
    }

    /**
     * Process a simulated online payment for the queue's invoice.
     */
    public function pay(Request $request, string $queueNumber)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:transfer,qris',
        ], [
            'payment_method.required' => 'Silakan pilih metode pembayaran.',
        ]);

        [$queue, $invoice] = $this->resolve($queueNumber);

        if (!$queue || !$invoice) {
            return redirect()->route('public.status', ['queue' => $queueNumber])
                ->with('error', 'Tagihan tidak ditemukan.');
        }

        if ($invoice->isPaid()) {
            return redirect()->route('public.status', ['queue' => $queue->queue_number])
                ->with('success_queue', 'Tagihan ini sudah lunas.');
        }

        $paidSoFar = $invoice->payments->where('status', 'completed')->sum('amount');
        $remaining = max($invoice->grand_total - $paidSoFar, 0);

        if ($remaining <= 0) {
            return redirect()->route('public.status', ['queue' => $queue->queue_number])
                ->with('success_queue', 'Tagihan ini sudah lunas.');
        }

        // Simulated payment: mark the remaining amount as paid immediately.
        // PaymentService handles marking the invoice as paid and moving the
        // service/wash order to "ready" / "siap_diambil" automatically.
        app(PaymentService::class)->processPayment(
            $invoice,
            $validated['payment_method'],
            $remaining,
            'ONLINE-' . strtoupper(uniqid()),
            null, // no staff user; this is a customer self-service payment
            'Pembayaran online oleh pelanggan (simulasi)'
        );

        return redirect()->route('public.status', ['queue' => $queue->queue_number])
            ->with('success_queue', 'Pembayaran BERHASIL! Tagihan Anda sudah lunas. Silakan tunggu penyerahan kendaraan oleh petugas bengkel.');
    }
}
