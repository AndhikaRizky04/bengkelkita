<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ServiceOrder;
use App\Models\WashOrder;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function generateInvoiceNumber(): string
    {
        $today = now()->format('Ymd');
        $last = Invoice::where('invoice_number', 'like', "INV-{$today}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if (!$last) {
            return "INV-{$today}-001";
        }

        $lastNumber = intval(substr($last->invoice_number, -3));
        return "INV-{$today}-" . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    public function createFromServiceOrder(ServiceOrder $serviceOrder, ?int $userId = null): Invoice
    {
        return DB::transaction(function () use ($serviceOrder, $userId) {
            $serviceOrder->calculateTotals();

            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'service_order_id' => $serviceOrder->id,
                'wash_order_id' => $serviceOrder->washOrder?->id,
                'customer_id' => $serviceOrder->customer_id,
                'total_amount' => $serviceOrder->grand_total,
                'discount' => $serviceOrder->discount,
                'tax' => 0,
                'grand_total' => $serviceOrder->grand_total,
                'status' => 'pending',
                'created_by' => $userId ?? auth()->id(),
            ]);

            // Add service items
            foreach ($serviceOrder->serviceOrderItems as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'service',
                    'item_name' => $item->service->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'subtotal' => $item->subtotal,
                ]);
            }

            // Add spareparts
            foreach ($serviceOrder->serviceOrderSpareparts as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'sparepart',
                    'item_name' => $item->sparepart->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'subtotal' => $item->subtotal,
                ]);
            }

            // Add oils
            foreach ($serviceOrder->serviceOrderOils as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'oil',
                    'item_name' => $item->oilProduct->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'subtotal' => $item->subtotal,
                ]);
            }

            // Add wash if exists
            if ($serviceOrder->washOrder) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'wash',
                    'item_name' => $serviceOrder->washOrder->washPackage->name,
                    'quantity' => 1,
                    'unit_price' => $serviceOrder->washOrder->price,
                    'subtotal' => $serviceOrder->washOrder->price,
                ]);
            }

            return $invoice;
        });
    }

    public function createFromWashOrder(WashOrder $washOrder, ?int $userId = null): Invoice
    {
        return DB::transaction(function () use ($washOrder, $userId) {
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'wash_order_id' => $washOrder->id,
                'customer_id' => $washOrder->customer_id,
                'total_amount' => $washOrder->price,
                'discount' => 0,
                'tax' => 0,
                'grand_total' => $washOrder->price,
                'status' => 'pending',
                'created_by' => $userId ?? auth()->id(),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'wash',
                'item_name' => $washOrder->washPackage->name,
                'quantity' => 1,
                'unit_price' => $washOrder->price,
                'subtotal' => $washOrder->price,
            ]);

            return $invoice;
        });
    }
}
