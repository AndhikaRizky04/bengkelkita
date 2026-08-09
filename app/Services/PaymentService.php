<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\ServiceHistory;
use App\Models\StatusLog;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Abstracted payment processing method to allow future gateway integration (e.g. Midtrans)
     */
    public function processPayment(Invoice $invoice, string $method, float $amount, ?string $refNumber = null, ?int $userId = null, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($invoice, $method, $amount, $refNumber, $userId, $notes) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_method' => $method,
                'amount' => $amount,
                'payment_date' => now(),
                'reference_number' => $refNumber,
                'status' => 'completed',
                'received_by' => $userId ?? auth()->id(),
                'notes' => $notes,
            ]);

            $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');

            if ($totalPaid >= $invoice->grand_total) {
                $invoice->update(['status' => 'paid']);

                // Update related service order status if present
                if ($invoice->serviceOrder) {
                    $order = $invoice->serviceOrder;
                    app(ServiceOrderService::class)->updateStatus($order, 'ready', $userId, 'Pembayaran lunas, kendaraan siap diambil');

                    // Create service history record
                    $this->createServiceHistory($order);
                }

                // Update related wash order status if present and no service order
                if ($invoice->washOrder && !$invoice->serviceOrder) {
                    $washOrder = $invoice->washOrder;
                    app(WashService::class)->updateStatus($washOrder, 'selesai', $userId, 'Pembayaran cuci lunas');
                }
            }

            return $payment;
        });
    }

    protected function createServiceHistory($serviceOrder): void
    {
        $services = $serviceOrder->serviceOrderItems->map(fn($item) => $item->service->name)->implode(', ');
        $spareparts = $serviceOrder->serviceOrderSpareparts->map(fn($item) => $item->sparepart->name . " ({$item->quantity} {$item->sparepart->unit})")->implode(', ');
        $oils = $serviceOrder->serviceOrderOils->map(fn($item) => $item->oilProduct->name . " ({$item->quantity} {$item->oilProduct->unit})")->implode(', ');

        ServiceHistory::create([
            'vehicle_id' => $serviceOrder->vehicle_id,
            'service_order_id' => $serviceOrder->id,
            'wash_order_id' => $serviceOrder->washOrder?->id,
            'service_date' => now()->toDateString(),
            'kilometer' => $serviceOrder->kilometer_in,
            'description' => $serviceOrder->complaint ?? 'Servis Kendaraan',
            'services_performed' => $services,
            'spareparts_used' => $spareparts,
            'oil_used' => $oils,
            'mechanic_name' => $serviceOrder->mechanic?->name ?? 'Mekanik BengkelKita',
            'total_cost' => $serviceOrder->grand_total,
            'notes' => $serviceOrder->mechanic_notes,
        ]);

        // Also update vehicle's last kilometer
        if ($serviceOrder->kilometer_in > 0) {
            $serviceOrder->vehicle->update(['last_kilometer' => $serviceOrder->kilometer_in]);
        }
    }
}
