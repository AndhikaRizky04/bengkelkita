@extends('layouts.app')

@section('title', 'Detail Work Order ' . $serviceOrder->order_number)

@section('content')
<div class="space-y-6">

    <!-- Header bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.service_orders.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">← Servis</a>
                <span class="text-gray-400">/</span>
                <span class="text-xs font-bold text-gray-700 font-mono">{{ $serviceOrder->order_number }}</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 mt-1">Work Order #{{ $serviceOrder->order_number }}</h1>
            <div class="text-xs text-gray-500 mt-1">
                Antrean: <strong class="text-blue-700 font-mono">{{ $serviceOrder->queue?->queue_number }}</strong> • Tanggal: {{ \Carbon\Carbon::parse($serviceOrder->created_at)->translatedFormat('d F Y H:i') }}
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if($serviceOrder->invoice)
                <a href="{{ route('admin.invoices.show', $serviceOrder->invoice) }}" class="btn btn-success font-bold text-xs">
                    🧾 Lihat Invoice (#{{ $serviceOrder->invoice->invoice_number }})
                </a>
            @else
                <form method="POST" action="{{ route('admin.service_orders.create_invoice', $serviceOrder) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary font-bold text-xs">
                        💳 Terbitkan Invoice Kasir
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Status & Mechanic Assignment Bar -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Status updater -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm space-y-3">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Status Pengerjaan Saat Ini</div>
            <div class="flex items-center gap-3">
                @php
                    $badgeMap = [
                        'pending' => 'badge-menunggu',
                        'inspection' => 'badge-pemeriksaan',
                        'in_progress' => 'badge-pengerjaan',
                        'waiting_parts' => 'badge-menunggu-sparepart',
                        'final_check' => 'badge-pengerjaan',
                        'ready' => 'badge-siap-diambil',
                        'completed' => 'badge-selesai',
                    ];
                @endphp
                <span class="badge {{ $badgeMap[$serviceOrder->status] ?? 'badge-dipanggil' }} text-sm font-bold px-3 py-1">
                    {{ strtoupper(str_replace('_', ' ', $serviceOrder->status)) }}
                </span>
            </div>

            <form method="POST" action="{{ route('admin.service_orders.update_status', $serviceOrder) }}" class="flex gap-2 pt-2">
                @csrf
                @method('PATCH')
                <select name="status" class="form-select text-xs">
                    <option value="pending" {{ $serviceOrder->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="inspection" {{ $serviceOrder->status === 'inspection' ? 'selected' : '' }}>Pemeriksaan</option>
                    <option value="in_progress" {{ $serviceOrder->status === 'in_progress' ? 'selected' : '' }}>Pengerjaan Servis</option>
                    <option value="waiting_parts" {{ $serviceOrder->status === 'waiting_parts' ? 'selected' : '' }}>Menunggu Sparepart</option>
                    <option value="final_check" {{ $serviceOrder->status === 'final_check' ? 'selected' : '' }}>Pemeriksaan Akhir</option>
                    <option value="ready" {{ $serviceOrder->status === 'ready' ? 'selected' : '' }}>Siap Diambil</option>
                    <option value="completed" {{ $serviceOrder->status === 'completed' ? 'selected' : '' }}>Selesai</option>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm text-xs font-semibold whitespace-nowrap">
                    Ubah Status
                </button>
            </form>
        </div>

        <!-- Mechanic assignment -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm space-y-3">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Penugasan Mekanik</div>
            <div class="text-sm font-bold text-gray-800">
                {{ $serviceOrder->mechanic?->name ?? 'Belum Ada Mekanik Ditugaskan' }}
            </div>

            <form method="POST" action="{{ route('admin.service_orders.assign_mechanic', $serviceOrder) }}" class="flex gap-2 pt-2">
                @csrf
                <select name="mechanic_id" class="form-select text-xs">
                    <option value="">-- Pilih Mekanik --</option>
                    @foreach($mechanics as $m)
                        <option value="{{ $m->id }}" {{ $serviceOrder->mechanic_id == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-secondary btn-sm text-xs font-semibold whitespace-nowrap">
                    Tugaskan
                </button>
            </form>
        </div>

    </div>

    <!-- Vehicle & Customer Details -->
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
        <div>
            <span class="text-gray-500 block">Pelanggan:</span>
            <div class="font-bold text-gray-900 text-sm mt-0.5">{{ $serviceOrder->customer?->name }}</div>
            <div class="text-blue-700 font-mono font-semibold">{{ $serviceOrder->customer?->phone }}</div>
        </div>

        <div>
            <span class="text-gray-500 block">Kendaraan Motor:</span>
            <div class="font-bold text-gray-900 text-sm mt-0.5">{{ $serviceOrder->vehicle?->full_name }}</div>
            <div class="font-mono font-bold text-gray-700">{{ $serviceOrder->vehicle?->license_plate }}</div>
        </div>

        <div>
            <span class="text-gray-500 block">Keluhan Pelanggan:</span>
            <div class="text-gray-800 italic mt-0.5">"{{ $serviceOrder->complaint }}"</div>
        </div>
    </div>

    <!-- Items Section (Services, Spareparts, Oils) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: Item Tables & Calculations -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Services Items -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900">Jasa Servis Terpasang</h3>
                    <button onclick="document.getElementById('modalAddService').classList.remove('hidden')" class="btn btn-secondary btn-sm text-xs">
                        + Tambah Jasa
                    </button>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama Layanan</th>
                                <th>Harga Jasa</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviceOrder->serviceOrderItems as $item)
                                <tr>
                                    <td class="font-semibold text-xs text-gray-900">{{ $item->service?->name }}</td>
                                    <td class="text-xs font-mono">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-xs font-mono">{{ $item->quantity }}</td>
                                    <td class="text-xs font-mono font-bold text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-state text-xs py-4">Belum ada jasa servis ditambahkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Spareparts Items -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900">Sparepart Terpasang</h3>
                    <button onclick="document.getElementById('modalAddSparepart').classList.remove('hidden')" class="btn btn-secondary btn-sm text-xs">
                        + Tambah Sparepart
                    </button>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Kode & Nama Sparepart</th>
                                <th>Harga Satuan</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviceOrder->serviceOrderSpareparts as $sp)
                                <tr>
                                    <td>
                                        <div class="font-semibold text-xs text-gray-900">{{ $sp->sparepart?->name }}</div>
                                        <div class="text-[10px] text-gray-500 font-mono">{{ $sp->sparepart?->code }} ({{ $sp->sparepart?->brand }})</div>
                                    </td>
                                    <td class="text-xs font-mono">Rp {{ number_format($sp->price, 0, ',', '.') }}</td>
                                    <td class="text-xs font-mono font-bold">{{ $sp->quantity }} {{ $sp->sparepart?->unit }}</td>
                                    <td class="text-xs font-mono font-bold text-gray-900">Rp {{ number_format($sp->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-state text-xs py-4">Belum ada sparepart ditambahkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Oils Items -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900">Oli Terpasang</h3>
                    <button onclick="document.getElementById('modalAddOil').classList.remove('hidden')" class="btn btn-secondary btn-sm text-xs">
                        + Tambah Oli
                    </button>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produk Oli</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviceOrder->serviceOrderOils as $oil)
                                <tr>
                                    <td>
                                        <div class="font-semibold text-xs text-gray-900">{{ $oil->oilProduct?->name }}</div>
                                        <div class="text-[10px] text-blue-600 font-semibold">{{ $oil->oilProduct?->viscosity }}</div>
                                    </td>
                                    <td class="text-xs font-mono">Rp {{ number_format($oil->price, 0, ',', '.') }}</td>
                                    <td class="text-xs font-mono font-bold">{{ $oil->quantity }} {{ $oil->oilProduct?->unit }}</td>
                                    <td class="text-xs font-mono font-bold text-gray-900">Rp {{ number_format($oil->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-state text-xs py-4">Belum ada oli ditambahkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right: Totals Summary & Recommendations -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Total Cost Summary Card -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-gray-900 border-b pb-2">Rincian Subtotal Servis</h3>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Jasa Servis:</span>
                        <span class="font-mono font-semibold">Rp {{ number_format($serviceOrder->total_service_cost, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Sparepart:</span>
                        <span class="font-mono font-semibold">Rp {{ number_format($serviceOrder->total_sparepart_cost, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Oli:</span>
                        <span class="font-mono font-semibold">Rp {{ number_format($serviceOrder->total_oil_cost, 0, ',', '.') }}</span>
                    </div>

                    @if($serviceOrder->washOrder)
                        <div class="flex justify-between text-blue-700">
                            <span>Cuci Motor ({{ $serviceOrder->washOrder->washPackage?->name }}):</span>
                            <span class="font-mono font-semibold">Rp {{ number_format($serviceOrder->washOrder->price, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="border-t pt-2 flex justify-between items-center text-sm font-black text-blue-700">
                        <span>GRAND TOTAL:</span>
                        <span class="font-mono text-base">Rp {{ number_format($serviceOrder->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Mechanic Recommendations Card (Requirement 13) -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-gray-900 border-b pb-2">Rekomendasi Servis Mekanik</h3>

                <div class="space-y-3">
                    @forelse($serviceOrder->serviceRecommendations as $rec)
                        <div class="p-3 bg-gray-50 border rounded-lg space-y-2 text-xs">
                            <div class="font-bold text-gray-900">{{ $rec->description }}</div>
                            <div class="text-gray-600 font-mono">Estimasi: Rp {{ number_format($rec->total_price, 0, ',', '.') }}</div>

                            <div class="flex items-center justify-between border-t pt-2">
                                <span class="badge text-[10px] {{ $rec->status === 'approved' ? 'badge-selesai' : ($rec->status === 'rejected' ? 'badge-dibatalkan' : 'badge-menunggu') }}">
                                    {{ strtoupper($rec->status) }}
                                </span>

                                @if($rec->status === 'pending')
                                    <div class="flex gap-1">
                                        <form method="POST" action="{{ route('admin.service_orders.update_recommendation', $rec) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-success btn-sm text-[10px] py-0.5 px-2 font-bold">
                                                Setujui
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.service_orders.update_recommendation', $rec) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-danger btn-sm text-[10px] py-0.5 px-2">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-gray-500 py-2 text-center">Belum ada rekomendasi dari mekanik.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Modal Add Service -->
<div id="modalAddService" class="hidden modal-overlay">
    <div class="modal-content">
        <div class="modal-header flex justify-between items-center">
            <h3 class="font-bold text-gray-900 text-sm">Tambah Jasa Servis</h3>
            <button onclick="document.getElementById('modalAddService').classList.add('hidden')" class="text-gray-400">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.service_orders.add_service', $serviceOrder) }}">
            @csrf
            <div class="modal-body space-y-4 text-xs">
                <div>
                    <label class="form-label">Pilih Layanan Jasa *</label>
                    <select name="service_id" required class="form-select text-xs">
                        <option value="">-- Pilih Jasa --</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} (Rp {{ number_format($s->price, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('modalAddService').classList.add('hidden')" class="btn btn-secondary text-xs">Batal</button>
                <button type="submit" class="btn btn-primary text-xs font-bold">Tambahkan Jasa</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Add Sparepart -->
<div id="modalAddSparepart" class="hidden modal-overlay">
    <div class="modal-content">
        <div class="modal-header flex justify-between items-center">
            <h3 class="font-bold text-gray-900 text-sm">Tambah Sparepart</h3>
            <button onclick="document.getElementById('modalAddSparepart').classList.add('hidden')" class="text-gray-400">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.service_orders.add_sparepart', $serviceOrder) }}">
            @csrf
            <div class="modal-body space-y-4 text-xs">
                <div>
                    <label class="form-label">Pilih Sparepart *</label>
                    <select name="sparepart_id" required class="form-select text-xs">
                        <option value="">-- Pilih Sparepart --</option>
                        @foreach($spareparts as $sp)
                            <option value="{{ $sp->id }}">{{ $sp->code }} - {{ $sp->name }} (Stok: {{ $sp->stock }} {{ $sp->unit }} | Rp {{ number_format($sp->selling_price, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Jumlah (Qty) *</label>
                    <input type="number" name="quantity" value="1" min="1" required class="form-input text-xs">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('modalAddSparepart').classList.add('hidden')" class="btn btn-secondary text-xs">Batal</button>
                <button type="submit" class="btn btn-primary text-xs font-bold">Tambahkan & Potong Stok</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Add Oil -->
<div id="modalAddOil" class="hidden modal-overlay">
    <div class="modal-content">
        <div class="modal-header flex justify-between items-center">
            <h3 class="font-bold text-gray-900 text-sm">Tambah Produk Oli</h3>
            <button onclick="document.getElementById('modalAddOil').classList.add('hidden')" class="text-gray-400">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.service_orders.add_oil', $serviceOrder) }}">
            @csrf
            <div class="modal-body space-y-4 text-xs">
                <div>
                    <label class="form-label">Pilih Produk Oli *</label>
                    <select name="oil_product_id" required class="form-select text-xs">
                        <option value="">-- Pilih Oli --</option>
                        @foreach($oils as $oil)
                            <option value="{{ $oil->id }}">{{ $oil->brand }} - {{ $oil->name }} (Stok: {{ $oil->stock }} {{ $oil->unit }} | Rp {{ number_format($oil->price, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Jumlah Botol/Liter *</label>
                    <input type="number" name="quantity" value="1" step="0.1" min="0.1" required class="form-input text-xs">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('modalAddOil').classList.add('hidden')" class="btn btn-secondary text-xs">Batal</button>
                <button type="submit" class="btn btn-primary text-xs font-bold">Tambahkan & Potong Stok</button>
            </div>
        </form>
    </div>
</div>
@endsection
