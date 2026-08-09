<?php

namespace App\Services;

use App\Models\WashOrder;
use App\Models\Queue;
use App\Models\StatusLog;
use Illuminate\Support\Facades\DB;

class WashService
{
    public function createWashOrder(array $data): WashOrder
    {
        return DB::transaction(function () use ($data) {
            $washOrder = WashOrder::create([
                'queue_id' => $data['queue_id'] ?? null,
                'service_order_id' => $data['service_order_id'] ?? null,
                'customer_id' => $data['customer_id'],
                'vehicle_id' => $data['vehicle_id'],
                'wash_package_id' => $data['wash_package_id'],
                'washer_id' => $data['washer_id'] ?? null,
                'status' => 'menunggu',
                'price' => $data['price'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->logStatus($washOrder, null, 'menunggu', null, 'Order cuci dibuat');

            return $washOrder;
        });
    }

    public function updateStatus(WashOrder $washOrder, string $newStatus, ?int $userId = null, ?string $notes = null): WashOrder
    {
        $oldStatus = $washOrder->status;

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'sedang_dicuci' && !$washOrder->started_at) {
            $updateData['started_at'] = now();
        }

        if ($newStatus === 'selesai') {
            $updateData['completed_at'] = now();
        }

        $washOrder->update($updateData);

        $this->logStatus($washOrder, $oldStatus, $newStatus, $userId, $notes);

        return $washOrder;
    }

    public function assignWasher(WashOrder $washOrder, int $washerId): WashOrder
    {
        $washOrder->update(['washer_id' => $washerId]);
        return $washOrder;
    }

    protected function logStatus(WashOrder $washOrder, ?string $from, string $to, ?int $userId = null, ?string $notes = null): void
    {
        StatusLog::create([
            'loggable_type' => WashOrder::class,
            'loggable_id' => $washOrder->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $userId ?? auth()->id(),
            'notes' => $notes,
        ]);
    }
}
