<?php

namespace App\Services;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\ServiceOrderSparepart;
use App\Models\ServiceOrderOil;
use App\Models\Queue;
use App\Models\StatusLog;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class ServiceOrderService
{
    public function createFromQueue(Queue $queue, ?int $mechanicId = null): ServiceOrder
    {
        return DB::transaction(function () use ($queue, $mechanicId) {
            $orderNumber = ServiceOrder::generateOrderNumber();

            $serviceOrder = ServiceOrder::create([
                'order_number' => $orderNumber,
                'queue_id' => $queue->id,
                'customer_id' => $queue->customer_id,
                'vehicle_id' => $queue->vehicle_id,
                'mechanic_id' => $mechanicId,
                'status' => 'pending',
                'complaint' => $queue->complaint,
                'kilometer_in' => $queue->vehicle->last_kilometer ?? 0,
            ]);

            $this->logStatus($serviceOrder, null, 'pending', null, 'Order servis dibuat');

            return $serviceOrder;
        });
    }

    public function updateStatus(ServiceOrder $order, string $newStatus, ?int $userId = null, ?string $notes = null): ServiceOrder
    {
        $oldStatus = $order->status;

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'in_progress' && !$order->started_at) {
            $updateData['started_at'] = now();
        }

        if (in_array($newStatus, ['completed', 'ready'])) {
            $updateData['completed_at'] = now();
        }

        $order->update($updateData);

        $this->logStatus($order, $oldStatus, $newStatus, $userId, $notes);

        // Update queue status accordingly
        if ($order->queue) {
            $queueStatusMap = [
                'inspection' => 'pemeriksaan',
                'in_progress' => 'pengerjaan',
                'waiting_parts' => 'menunggu_sparepart',
                'final_check' => 'pengerjaan',
                'ready' => 'siap_diambil',
                'completed' => 'selesai',
            ];

            if (isset($queueStatusMap[$newStatus])) {
                app(QueueService::class)->updateStatus(
                    $order->queue,
                    $queueStatusMap[$newStatus],
                    $userId,
                    $notes
                );
            }
        }

        return $order;
    }

    public function addServiceItem(ServiceOrder $order, int $serviceId, float $price, int $quantity = 1, ?string $notes = null): ServiceOrderItem
    {
        $item = ServiceOrderItem::create([
            'service_order_id' => $order->id,
            'service_id' => $serviceId,
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $price * $quantity,
            'notes' => $notes,
        ]);

        $order->calculateTotals();

        return $item;
    }

    public function addSparepart(ServiceOrder $order, int $sparepartId, float $price, int $quantity = 1, ?string $notes = null): ServiceOrderSparepart
    {
        return DB::transaction(function () use ($order, $sparepartId, $price, $quantity, $notes) {
            $sparepart = \App\Models\Sparepart::findOrFail($sparepartId);

            if ($sparepart->stock < $quantity) {
                throw new \Exception("Stok {$sparepart->name} tidak mencukupi. Tersedia: {$sparepart->stock}");
            }

            $item = ServiceOrderSparepart::create([
                'service_order_id' => $order->id,
                'sparepart_id' => $sparepartId,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $price * $quantity,
                'notes' => $notes,
            ]);

            // Reduce stock
            app(InventoryService::class)->reduceStock(
                'sparepart',
                $sparepartId,
                $quantity,
                'service_order',
                $order->id,
                auth()->id()
            );

            $order->calculateTotals();

            return $item;
        });
    }

    public function addOil(ServiceOrder $order, int $oilProductId, float $price, float $quantity = 1, ?string $notes = null): ServiceOrderOil
    {
        return DB::transaction(function () use ($order, $oilProductId, $price, $quantity, $notes) {
            $oil = \App\Models\OilProduct::findOrFail($oilProductId);

            if ($oil->stock < $quantity) {
                throw new \Exception("Stok {$oil->name} tidak mencukupi. Tersedia: {$oil->stock}");
            }

            $item = ServiceOrderOil::create([
                'service_order_id' => $order->id,
                'oil_product_id' => $oilProductId,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $price * $quantity,
                'notes' => $notes,
            ]);

            app(InventoryService::class)->reduceStock(
                'oil',
                $oilProductId,
                $quantity,
                'service_order',
                $order->id,
                auth()->id()
            );

            $order->calculateTotals();

            return $item;
        });
    }

    public function assignMechanic(ServiceOrder $order, int $mechanicId): ServiceOrder
    {
        $order->update(['mechanic_id' => $mechanicId]);
        return $order;
    }

    protected function logStatus(ServiceOrder $order, ?string $from, string $to, ?int $userId = null, ?string $notes = null): void
    {
        StatusLog::create([
            'loggable_type' => ServiceOrder::class,
            'loggable_id' => $order->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $userId ?? auth()->id(),
            'notes' => $notes,
        ]);
    }
}
