<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\ServiceOrder;
use App\Models\StatusLog;
use Illuminate\Support\Facades\DB;

class QueueService
{
    public function generateQueueNumber(?string $date = null): string
    {
        $date = $date ?: now()->toDateString();

        $lastQueue = Queue::where('queue_date', $date)
            ->orderBy('queue_number', 'desc')
            ->first();

        if (!$lastQueue) {
            return 'A-001';
        }

        $lastNumber = intval(substr($lastQueue->queue_number, 2));
        return 'A-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    public function createQueue(array $data): Queue
    {
        return DB::transaction(function () use ($data) {
            $queue = Queue::create([
                'queue_number' => $this->generateQueueNumber(),
                'queue_date' => now()->toDateString(),
                'customer_id' => $data['customer_id'],
                'vehicle_id' => $data['vehicle_id'],
                'service_type' => $data['service_type'],
                'status' => 'menunggu',
                'complaint' => $data['complaint'] ?? null,
                'registered_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $this->logStatus($queue, null, 'menunggu', null, 'Antrean dibuat');

            return $queue;
        });
    }

    public function updateStatus(Queue $queue, string $newStatus, ?int $userId = null, ?string $notes = null): Queue
    {
        $oldStatus = $queue->status;

        $queue->update([
            'status' => $newStatus,
            'called_at' => $newStatus === 'dipanggil' ? now() : $queue->called_at,
            'completed_at' => in_array($newStatus, ['selesai', 'dibatalkan']) ? now() : $queue->completed_at,
        ]);

        $this->logStatus($queue, $oldStatus, $newStatus, $userId, $notes);

        return $queue;
    }

    public function callNext(): ?Queue
    {
        $queue = Queue::where('queue_date', now()->toDateString())
            ->where('status', 'menunggu')
            ->orderBy('queue_number')
            ->first();

        if ($queue) {
            $this->updateStatus($queue, 'dipanggil', auth()->id(), 'Antrean dipanggil');
        }

        return $queue;
    }

    protected function logStatus(Queue $queue, ?string $from, string $to, ?int $userId = null, ?string $notes = null): void
    {
        StatusLog::create([
            'loggable_type' => Queue::class,
            'loggable_id' => $queue->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $userId ?? auth()->id(),
            'notes' => $notes,
        ]);
    }
}
