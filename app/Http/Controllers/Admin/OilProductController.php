<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OilProduct;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class OilProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $type = $request->query('type');
        $lowStock = $request->query('low_stock');

        $query = OilProduct::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('viscosity', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($lowStock) {
            $query->whereColumn('stock', '<=', 'minimum_stock');
        }

        $oils = $query->orderBy('brand')->orderBy('name')->paginate(15);

        return view('admin.oils.index', compact('oils', 'search', 'type', 'lowStock'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'viscosity' => 'nullable|string|max:50',
            'type' => 'required|in:matic,manual,universal',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:20',
        ]);

        $oil = OilProduct::create($validated);

        return back()->with('success', "Produk oli {$oil->name} berhasil ditambahkan.");
    }

    public function update(Request $request, OilProduct $oilProduct)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'viscosity' => 'nullable|string|max:50',
            'type' => 'required|in:matic,manual,universal',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:20',
        ]);

        $oilProduct->update($validated);

        return back()->with('success', "Data produk oli {$oilProduct->name} berhasil diperbarui.");
    }

    public function addStock(Request $request, OilProduct $oilProduct)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.5',
            'notes' => 'nullable|string',
        ]);

        app(InventoryService::class)->addStock(
            'oil',
            $oilProduct->id,
            $validated['quantity'],
            $validated['notes'] ?? null,
            auth()->id()
        );

        return back()->with('success', "Stok {$oilProduct->name} bertambah {$validated['quantity']} {$oilProduct->unit}.");
    }
}
