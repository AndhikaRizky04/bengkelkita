@extends('layouts.app')

@section('title', 'Dashboard Operasional Bengkel')

@section('content')
<div class="space-y-6">

    <!-- Top Operational Metrics Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="stat-card">
            <div class="stat-card-value text-blue-600">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</div>
            <div class="stat-card-label">Pendapatan Hari Ini</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-value">{{ $vehiclesEntered }}</div>
            <div class="stat-card-label">Kendaraan Masuk</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-value text-amber-600">{{ $inProgress }}</div>
            <div class="stat-card-label">Sedang Dikerjakan</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-value text-emerald-600">{{ $readyForPickup }}</div>
            <div class="stat-card-label">Siap Diambil</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-value text-cyan-600">{{ $washQueueCount }}</div>
            <div class="stat-card-label">Antrean Cuci</div>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div class="bg-white p-4 rounded-lg border border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('admin.queues.call_next') }}">
                @csrf
                <button type="submit" class="btn btn-primary font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 58l10.586-10.586a2 2 0 112.828 2.828L13.828 60H11v-2.828z"/></svg>
                    <span>📢 Panggil Antrean Berikutnya</span>
                </button>
            </form>

            <a href="{{ route('admin.queues.create') }}" class="btn btn-secondary font-medium">
                + Tambah Antrean Baru
            </a>

            <a href="{{ route('admin.handover.index') }}" class="btn btn-success font-semibold text-xs">
                🔑 Serah Terima Kendaraan (A-000)
            </a>
        </div>

        <div class="text-xs text-gray-500 font-medium">
            Tanggal: {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
        </div>
    </div>

    <!-- Rekap Pembayaran Hari Ini -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Recap cards -->
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
                <div class="text-[11px] text-gray-400 uppercase font-semibold tracking-wider mb-1">Total Pembayaran Hari Ini</div>
                <div class="text-2xl font-black text-blue-600">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</div>
                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span> Pembayaran Online
                        </span>
                        <span class="text-sm font-bold text-gray-800">Rp {{ number_format($onlineTodayTotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Kasir / Manual
                        </span>
                        <span class="text-sm font-bold text-gray-800">Rp {{ number_format($cashierTodayTotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent transactions -->
        <div class="lg:col-span-8">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm h-full">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-700">Transaksi Terbaru Hari Ini</h3>
                    <a href="{{ route('admin.invoices.index') }}" class="text-xs text-blue-600 hover:underline font-semibold">Lihat Semua</a>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Invoice</th>
                                <th>Pelanggan</th>
                                <th>Metode</th>
                                <th>Sumber</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $pay)
                                @php $isOnline = str_starts_with((string) $pay->reference_number, 'ONLINE-'); @endphp
                                <tr>
                                    <td class="text-xs text-gray-500">{{ $pay->payment_date?->format('H:i') }}</td>
                                    <td class="text-xs font-mono font-bold text-blue-700">{{ $pay->invoice?->invoice_number ?? '-' }}</td>
                                    <td class="text-xs font-medium">{{ $pay->invoice?->customer?->name ?? '-' }}</td>
                                    <td class="text-xs capitalize">{{ $pay->payment_method }}</td>
                                    <td>
                                        @if($isOnline)
                                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">Online</span>
                                        @else
                                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Kasir</span>
                                        @endif
                                    </td>
                                    <td class="text-xs font-semibold">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-sm text-gray-500 py-8">Belum ada transaksi hari ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Alerts (Low Stock) -->
    @if($lowStockSpareparts->count() > 0 || $lowStockOils->count() > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-amber-900 text-xs">
            <div class="font-bold text-sm mb-1 flex items-center gap-2 text-amber-800">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Peringatan Stok Menipis (Perlu Restok)</span>
            </div>
            <div class="flex flex-wrap gap-2 mt-2">
                @foreach($lowStockSpareparts as $sp)
                    <a href="{{ route('admin.spareparts.index', ['low_stock' => 1]) }}" class="bg-amber-100 border border-amber-300 px-2 py-1 rounded font-medium hover:underline">
                        {{ $sp->name }}: <span class="font-bold text-red-600">{{ $sp->stock }} {{ $sp->unit }}</span>
                    </a>
                @endforeach
                @foreach($lowStockOils as $oil)
                    <a href="{{ route('admin.oils.index', ['low_stock' => 1]) }}" class="bg-amber-100 border border-amber-300 px-2 py-1 rounded font-medium hover:underline">
                        {{ $oil->name }}: <span class="font-bold text-red-600">{{ $oil->stock }} {{ $oil->unit }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Tables Grid (Antrean Servis vs Status Cuci) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Antrean Servis Saat Ini -->
        <div class="lg:col-span-8 bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-gray-900">Antrean Servis Hari Ini</h2>
                <a href="{{ route('admin.queues.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">Lihat Semua →</a>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Antrean</th>
                            <th>Kendaraan & Plat</th>
                            <th>Pelanggan</th>
                            <th>Layanan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeQueues as $idx => $q)
                            <tr>
                                <td class="text-xs text-gray-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="font-black text-blue-700 text-sm">{{ $q->queue_number }}</td>
                                <td>
                                    <div class="font-bold text-gray-900 text-xs">{{ $q->vehicle?->full_name }}</div>
                                    <div class="text-[11px] text-gray-500 font-mono">{{ $q->vehicle?->license_plate }}</div>
                                </td>
                                <td class="text-xs font-medium">{{ $q->customer?->name }}</td>
                                <td class="text-xs">
                                    <span class="capitalize text-gray-700 font-medium">{{ str_replace('_', ' + ', $q->service_type) }}</span>
                                </td>
                                <td>
                                    @php
                                        $badgeMap = [
                                            'menunggu' => 'badge-menunggu',
                                            'dipanggil' => 'badge-dipanggil',
                                            'pemeriksaan' => 'badge-pemeriksaan',
                                            'pengerjaan' => 'badge-pengerjaan',
                                            'menunggu_sparepart' => 'badge-menunggu-sparepart',
                                            'menunggu_pembayaran' => 'badge-menunggu-pembayaran',
                                            'siap_diambil' => 'badge-siap-diambil',
                                            'selesai' => 'badge-selesai',
                                            'dibatalkan' => 'badge-dibatalkan',
                                        ];
                                    @endphp
                                    <span class="badge {{ $badgeMap[$q->status] ?? 'badge-dipanggil' }}">
                                        {{ strtoupper(str_replace('_', ' ', $q->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($q->serviceOrder)
                                        <a href="{{ route('admin.service_orders.show', $q->serviceOrder) }}" class="btn btn-secondary btn-sm text-xs">
                                            Detail
                                        </a>
                                    @else
                                        <a href="{{ route('admin.handover.index', ['queue_number' => $q->queue_number]) }}" class="btn btn-secondary btn-sm text-xs">
                                            Cek
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <div class="empty-state-icon">🏍️</div>
                                    <div>Belum ada antrean untuk hari ini.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Status Cuci Motor Tracker -->
        <div class="lg:col-span-4 bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-gray-900">Status Cuci Motor</h2>
                <a href="{{ route('admin.wash_orders.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">Kelola →</a>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Antrean</th>
                            <th>Motor</th>
                            <th>Paket</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeWashOrders as $wo)
                            <tr>
                                <td class="font-bold text-xs text-blue-600">
                                    {{ $wo->queue?->queue_number ?? ('C-' . str_pad($wo->id, 3, '0', STR_PAD_LEFT)) }}
                                </td>
                                <td>
                                    <div class="font-semibold text-xs text-gray-900">{{ $wo->vehicle?->full_name }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $wo->vehicle?->license_plate }}</div>
                                </td>
                                <td class="text-xs text-gray-600">{{ $wo->washPackage?->name }}</td>
                                <td>
                                    <span class="badge badge-in-progress text-[10px]">
                                        {{ strtoupper(str_replace('_', ' ', $wo->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state py-8">
                                    <div class="empty-state-icon text-2xl">🧼</div>
                                    <div class="text-xs">Tidak ada antrean cuci aktif.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
