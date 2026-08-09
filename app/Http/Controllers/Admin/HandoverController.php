<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Services\QueueService;
use App\Services\ServiceOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HandoverController extends Controller
{
    public function index(Request $request)
    {
        $queueNumber = strtoupper(trim($request->query('queue_number', '')));
        $queue = null;

        if ($queueNumber) {
            $queue = Queue::with([
                'customer',
                'vehicle.vehicleBrand',
                'vehicle.vehicleModel',
                'serviceOrder.invoice.payments',
                'washOrder.invoice.payments'
            ])
            ->where('queue_number', $queueNumber)
            ->whereDate('queue_date', '>=', now()->subDays(3))
            ->latest()
            ->first();
        }

        return view('admin.handover.index', compact('queue', 'queueNumber'));
    }

    public function completeHandover(Request $request, Queue $queue)
    {
        return DB::transaction(function () use ($queue) {
            // Check payment status
            $serviceInvoice = $queue->serviceOrder?->invoice;
            $washInvoice = $queue->washOrder?->invoice;

            $isPaid = false;

            if ($serviceInvoice) {
                $isPaid = $serviceInvoice->isPaid();
            } elseif ($washInvoice) {
                $isPaid = $washInvoice->isPaid();
            } else {
                $isPaid = true; // No payment required
            }

            if (!$isPaid) {
                return back()->with('error', 'Pembayaran untuk antrean ini belum lunas. Selesaikan pembayaran di kasir terlebih dahulu.');
            }

            // Update Queue status to Selesai
            app(QueueService::class)->updateStatus(
                $queue,
                'selesai',
                auth()->id(),
                'Kendaraan diserahkan kepada pelanggan berdasarkan nomor antrean ' . $queue->queue_number
            );

            // Update Service Order status to completed
            if ($queue->serviceOrder) {
                app(ServiceOrderService::class)->updateStatus(
                    $queue->serviceOrder,
                    'completed',
                    auth()->id(),
                    'Kendaraan diserahkan'
                );
            }

            return redirect()->route('admin.handover.index')
                ->with('success', "Penyerahan kendaraan antrean {$queue->queue_number} ({$queue->vehicle->full_name} - {$queue->customer->name}) BERHASIL diselesaikan.");
        });
    }
}
