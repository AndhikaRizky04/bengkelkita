<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use App\Models\SparepartCategory;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class SparepartController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category_id');
        $lowStock = $request->query('low_stock');

        $query = Sparepart::with('sparepartCategory');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('sparepart_category_id', $category);
        }

        if ($lowStock) {
            $query->whereColumn('stock', '<=', 'minimum_stock');
        }

        $spareparts = $query->orderBy('name')->paginate(15);
        $categories = SparepartCategory::all();

        return view('admin.spareparts.index', compact('spareparts', 'categories', 'search', 'category', 'lowStock'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:spareparts,code|max:50',
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:100',
            'sparepart_category_id' => 'required|exists:sparepart_categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:20',
        ]);

        $sparepart = Sparepart::create($validated);

        return back()->with('success', "Sparepart {$sparepart->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Sparepart $sparepart)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:spareparts,code,' . $sparepart->id,
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:100',
            'sparepart_category_id' => 'required|exists:sparepart_categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:20',
        ]);

        $sparepart->update($validated);

        return back()->with('success', "Data sparepart {$sparepart->name} berhasil diperbarui.");
    }

    public function addStock(Request $request, Sparepart $sparepart)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        app(InventoryService::class)->addStock(
            'sparepart',
            $sparepart->id,
            $validated['quantity'],
            $validated['notes'] ?? null,
            auth()->id()
        );

        return back()->with('success', "Stok {$sparepart->name} bertambah {$validated['quantity']} {$sparepart->unit}.");
    }
}
