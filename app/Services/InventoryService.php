<?php

namespace App\Services;

use App\Models\Sparepart;
use App\Models\OilProduct;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function reduceStock(string $itemType, int $itemId, float $quantity, ?string $referenceType = null, ?int $referenceId = null, ?int $userId = null): InventoryTransaction
    {
        return DB::transaction(function () use ($itemType, $itemId, $quantity, $referenceType, $referenceId, $userId) {
            $item = $this->getItem($itemType, $itemId);
            $stockBefore = $item->stock;

            if ($stockBefore < $quantity) {
                throw new \Exception("Stok tidak mencukupi. Tersedia: {$stockBefore}, Dibutuhkan: {$quantity}");
            }

            $item->decrement('stock', $quantity);

            return InventoryTransaction::create([
                'item_type' => $itemType,
                'item_id' => $itemId,
                'transaction_type' => 'out',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockBefore - $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => "Penggunaan untuk {$referenceType} #{$referenceId}",
                'created_by' => $userId,
            ]);
        });
    }

    public function addStock(string $itemType, int $itemId, float $quantity, ?string $notes = null, ?int $userId = null): InventoryTransaction
    {
        return DB::transaction(function () use ($itemType, $itemId, $quantity, $notes, $userId) {
            $item = $this->getItem($itemType, $itemId);
            $stockBefore = $item->stock;

            $item->increment('stock', $quantity);

            return InventoryTransaction::create([
                'item_type' => $itemType,
                'item_id' => $itemId,
                'transaction_type' => 'in',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockBefore + $quantity,
                'notes' => $notes ?? 'Penambahan stok',
                'created_by' => $userId,
            ]);
        });
    }

    public function adjustStock(string $itemType, int $itemId, int $newStock, ?string $notes = null, ?int $userId = null): InventoryTransaction
    {
        return DB::transaction(function () use ($itemType, $itemId, $newStock, $notes, $userId) {
            $item = $this->getItem($itemType, $itemId);
            $stockBefore = $item->stock;

            $item->update(['stock' => $newStock]);

            return InventoryTransaction::create([
                'item_type' => $itemType,
                'item_id' => $itemId,
                'transaction_type' => 'adjustment',
                'quantity' => abs($newStock - $stockBefore),
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'notes' => $notes ?? 'Penyesuaian stok',
                'created_by' => $userId,
            ]);
        });
    }

    protected function getItem(string $type, int $id)
    {
        return match ($type) {
            'sparepart' => Sparepart::findOrFail($id),
            'oil' => OilProduct::findOrFail($id),
            default => throw new \InvalidArgumentException("Tipe item tidak dikenali: {$type}"),
        };
    }

    public function getLowStockSpareparts()
    {
        return Sparepart::whereColumn('stock', '<=', 'minimum_stock')
            ->where('is_active', true)
            ->get();
    }

    public function getLowStockOils()
    {
        return OilProduct::whereColumn('stock', '<=', 'minimum_stock')
            ->where('is_active', true)
            ->get();
    }
}
