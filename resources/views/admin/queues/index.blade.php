@extends('layouts.app')

@section('title', 'Daftar Antrean Bengkel')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Manajemen Antrean Harian</h1>
            <p class="text-xs text-gray-500">Kelola antrean masuk, panggil antrean, dan perbarui status pengerjaan</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.queues.create') }}" class="btn btn-primary font-semibold text-xs">
                + Pendaftaran Antrean Baru
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.queues.index') }}" class="flex flex-wrap items-center gap-4">
            <div>
                <label class="form-label text-xs">Tanggal Antrean</label>
                <input type="date" name="date" value="{{ $date }}" class="form-input text-xs py-1.5" onchange="this.form.submit()">
            </div>

            <div>
                <label class="form-label text-xs">Filter Status</label>
                <select name="status" class="form-select text-xs py-1.5" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ $status === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="dipanggil" {{ $status === 'dipanggil' ? 'selected' : '' }}>Dipanggil</option>
                    <option value="pemeriksaan" {{ $status === 'pemeriksaan' ? 'selected' : '' }}>Pemeriksaan</option>
                    <option value="pengerjaan" {{ $status === 'pengerjaan' ? 'selected' : '' }}>Pengerjaan</option>
                    <option value="menunggu_sparepart" {{ $status === 'menunggu_sparepart' ? 'selected' : '' }}>Menunggu Sparepart</option>
                    <option value="menunggu_pembayaran" {{ $status === 'menunggu_pembayaran' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                    <option value="siap_diambil" {{ $status === 'siap_diambil' ? 'selected' : '' }}>Siap Diambil</option>
                    <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Antrean</th>
                        <th>Waktu Daftar</th>
                        <th>Pelanggan</th>
                        <th>Motor & Plat</th>
                        <th>Layanan</th>
                        <th>Keluhan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queues as $q)
                        <tr>
                            <td class="font-black text-base text-blue-700 font-mono">{{ $q->queue_number }}</td>
                            <td class="text-xs text-gray-500 font-mono">
                                {{ \Carbon\Carbon::parse($q->registered_at)->format('H:i') }} WIB
                            </td>
                            <td>
                                <div class="font-bold text-gray-900 text-xs">{{ $q->customer?->name }}</div>
                                <div class="text-[11px] text-gray-500 font-mono">{{ $q->customer?->phone }}</div>
                            </td>
                            <td>
                                <div class="font-bold text-gray-900 text-xs">{{ $q->vehicle?->full_name }}</div>
                                <div class="text-[11px] font-mono text-gray-600">{{ $q->vehicle?->license_plate }}</div>
                            </td>
                            <td class="text-xs font-semibold capitalize">
                                {{ str_replace('_', ' + ', $q->service_type) }}
                            </td>
                            <td class="text-xs text-gray-600 max-w-xs truncate" title="{{ $q->complaint }}">
                                "{{ $q->complaint }}"
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
                                <div class="flex items-center gap-2">
                                    @if($q->serviceOrder)
                                        <a href="{{ route('admin.service_orders.show', $q->serviceOrder) }}" class="btn btn-secondary btn-sm text-xs">
                                            Order Servis
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.handover.index', ['queue_number' => $q->queue_number]) }}" class="btn btn-secondary btn-sm text-xs">
                                        Serah Terima
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <div class="empty-state-icon">📋</div>
                                <div>Tidak ada antrean ditemukan untuk filter ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200">
            {{ $queues->appends(request()->query())->links() }}
        </div>
    </div>

</div>
@endsection
