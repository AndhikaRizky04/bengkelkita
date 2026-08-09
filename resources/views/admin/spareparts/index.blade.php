@extends('layouts.app')

@section('title', 'Stok Sparepart')

@section('content')
<div class="space-y-6" x-data="{ showAdd: false, editId: null, addStockId: null }">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Stok Sparepart</h1>
            <p class="text-xs text-gray-500">Kelola data dan stok sparepart bengkel</p>
        </div>
        <button @click="showAdd = true" class="btn btn-primary text-xs py-2">+ Tambah Sparepart</button>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.spareparts.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="form-label text-xs">Cari</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama, kode, atau merek..." class="form-input text-xs py-1.5">
            </div>
            <div>
                <label class="form-label text-xs">Kategori</label>
                <select name="category_id" class="form-select text-xs py-1.5">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$category === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 pb-1">
                <input type="checkbox" name="low_stock" value="1" id="low_stock" {{ $lowStock ? 'checked' : '' }} class="rounded">
                <label for="low_stock" class="text-xs text-gray-600">Stok Menipis</label>
            </div>
            <button type="submit" class="btn btn-primary text-xs py-1.5">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Sparepart</th>
                        <th>Kategori</th>
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($spareparts as $sp)
                        <tr>
                            <td class="text-xs font-mono">{{ $sp->code }}</td>
                            <td>
                                <div class="text-xs font-bold text-gray-900">{{ $sp->name }}</div>
                                <div class="text-[11px] text-gray-500">{{ $sp->brand }}</div>
                            </td>
                            <td class="text-xs">{{ $sp->sparepartCategory?->name ?? '-' }}</td>
                            <td class="text-xs font-semibold">Rp {{ number_format($sp->selling_price, 0, ',', '.') }}</td>
                            <td>
                                <span class="text-xs font-bold {{ $sp->stock <= $sp->minimum_stock ? 'text-red-600' : 'text-gray-800' }}">
                                    {{ $sp->stock }} {{ $sp->unit }}
                                </span>
                                @if($sp->stock <= $sp->minimum_stock)
                                    <span class="ml-1 text-[10px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full font-semibold">Menipis</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button @click="addStockId = {{ $sp->id }}" class="text-[11px] text-green-600 hover:underline font-semibold">+ Stok</button>
                                    <button @click="editId = {{ $sp->id }}" class="text-[11px] text-blue-600 hover:underline font-semibold">Edit</button>
                                </div>
                            </td>
                        </tr>

                        <!-- Add Stock Modal -->
                        <tr x-show="addStockId === {{ $sp->id }}" x-cloak>
                            <td colspan="6" class="bg-green-50 p-4">
                                <form method="POST" action="{{ route('admin.spareparts.add_stock', $sp) }}" class="flex flex-wrap items-end gap-3">
                                    @csrf
                                    <div><label class="form-label text-xs">Tambah Jumlah</label><input type="number" name="quantity" min="1" required class="form-input text-xs py-1.5 w-32"></div>
                                    <div class="flex-1 min-w-[200px]"><label class="form-label text-xs">Catatan</label><input type="text" name="notes" class="form-input text-xs py-1.5"></div>
                                    <button type="submit" class="btn btn-success text-xs py-1.5">Simpan</button>
                                    <button type="button" @click="addStockId = null" class="btn btn-secondary text-xs py-1.5">Batal</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <tr x-show="editId === {{ $sp->id }}" x-cloak>
                            <td colspan="6" class="bg-blue-50 p-4">
                                <form method="POST" action="{{ route('admin.spareparts.update', $sp) }}" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @csrf @method('PUT')
                                    <div><label class="form-label text-xs">Kode</label><input type="text" name="code" value="{{ $sp->code }}" required class="form-input text-xs py-1.5"></div>
                                    <div><label class="form-label text-xs">Nama</label><input type="text" name="name" value="{{ $sp->name }}" required class="form-input text-xs py-1.5"></div>
                                    <div><label class="form-label text-xs">Merek</label><input type="text" name="brand" value="{{ $sp->brand }}" required class="form-input text-xs py-1.5"></div>
                                    <div><label class="form-label text-xs">Kategori</label>
                                        <select name="sparepart_category_id" required class="form-select text-xs py-1.5">
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ $sp->sparepart_category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div><label class="form-label text-xs">Harga Beli</label><input type="number" name="purchase_price" value="{{ $sp->purchase_price }}" required class="form-input text-xs py-1.5"></div>
                                    <div><label class="form-label text-xs">Harga Jual</label><input type="number" name="selling_price" value="{{ $sp->selling_price }}" required class="form-input text-xs py-1.5"></div>
                                    <div><label class="form-label text-xs">Stok</label><input type="number" name="stock" value="{{ $sp->stock }}" required class="form-input text-xs py-1.5"></div>
                                    <div><label class="form-label text-xs">Stok Minimum</label><input type="number" name="minimum_stock" value="{{ $sp->minimum_stock }}" required class="form-input text-xs py-1.5"></div>
                                    <div><label class="form-label text-xs">Satuan</label><input type="text" name="unit" value="{{ $sp->unit }}" required class="form-input text-xs py-1.5"></div>
                                    <div class="col-span-2 md:col-span-4 flex gap-2">
                                        <button type="submit" class="btn btn-primary text-xs py-1.5">Simpan Perubahan</button>
                                        <button type="button" @click="editId = null" class="btn btn-secondary text-xs py-1.5">Batal</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-sm text-gray-500 py-8">Tidak ada data sparepart.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $spareparts->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Add Sparepart Modal -->
    <div x-show="showAdd" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-bold text-gray-800 text-sm">Tambah Sparepart</h3>
                <button @click="showAdd = false" class="text-gray-400 hover:text-gray-700">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.spareparts.store') }}">
                @csrf
                <div class="p-5 grid grid-cols-2 gap-3">
                    <div><label class="form-label text-xs">Kode</label><input type="text" name="code" required class="form-input text-xs py-1.5"></div>
                    <div><label class="form-label text-xs">Nama</label><input type="text" name="name" required class="form-input text-xs py-1.5"></div>
                    <div><label class="form-label text-xs">Merek</label><input type="text" name="brand" required class="form-input text-xs py-1.5"></div>
                    <div><label class="form-label text-xs">Kategori</label>
                        <select name="sparepart_category_id" required class="form-select text-xs py-1.5">
                            <option value="">- Pilih -</option>
                            @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="form-label text-xs">Harga Beli</label><input type="number" name="purchase_price" value="0" required class="form-input text-xs py-1.5"></div>
                    <div><label class="form-label text-xs">Harga Jual</label><input type="number" name="selling_price" value="0" required class="form-input text-xs py-1.5"></div>
                    <div><label class="form-label text-xs">Stok Awal</label><input type="number" name="stock" value="0" required class="form-input text-xs py-1.5"></div>
                    <div><label class="form-label text-xs">Stok Minimum</label><input type="number" name="minimum_stock" value="1" required class="form-input text-xs py-1.5"></div>
                    <div><label class="form-label text-xs">Satuan</label><input type="text" name="unit" value="pcs" required class="form-input text-xs py-1.5"></div>
                </div>
                <div class="px-5 py-3 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" @click="showAdd = false" class="btn btn-secondary text-xs py-1.5">Batal</button>
                    <button type="submit" class="btn btn-primary text-xs py-1.5">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
