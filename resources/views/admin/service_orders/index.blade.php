@extends('layouts.app')

@section('title', 'Pengerjaan Servis Motor (Work Orders)')

@section('content')
<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Pengerjaan Servis (Work Orders)</h1>
            <p class="text-xs text-gray-500">Daftar pengerjaan teknis servis motor oleh mekanik</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.service_orders.index') }}" class="flex flex-wrap items-center gap-4">
            <div>
                <label class="form-label text-xs">Filter Status</label>
                <select name="status" class="form-select text-xs py-1.5" onchange="this.form.submit()">
                    <option value="">Semua Status Pengerjaan</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="inspection" {{ $status === 'inspection' ? 'selected' : '' }}>Pemeriksaan</option>
                    <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>Pengerjaan</option>
                    <option value="waiting_parts" {{ $status === 'waiting_parts' ? 'selected' : '' }}>Menunggu Sparepart</option>
                    <option value="final_check" {{ $status === 'final_check' ? 'selected' : '' }}>Pemeriksaan Akhir</option>
                    <option value="ready" {{ $status === 'ready' ? 'selected' : '' }}>Siap Diambil</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai</option>
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
                        <th>Kode WO</th>
                        <th>Antrean</th>
                        <th>Kendaraan & Plat</th>
                        <th>Pelanggan</th>
                        <th>Mekanik</th>
                        <th>Total Sementara</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($serviceOrders as $wo)
                        <tr>
                            <td class="font-black text-sm text-blue-700 font-mono">{{ $wo->order_number }}</td>
                            <td class="font-bold text-xs text-gray-800">{{ $wo->queue?->queue_number }}</td>
                            <td>
                                <div class="font-bold text-gray-900 text-xs">{{ $wo->vehicle?->full_name }}</div>
                                <div class="text-[11px] font-mono text-gray-500">{{ $wo->vehicle?->license_plate }}</div>
                            </td>
                            <td class="text-xs font-medium">{{ $wo->customer?->name }}</td>
                            <td class="text-xs">
                                @if($wo->mechanic)
                                    <span class="font-bold text-gray-800">{{ $wo->mechanic->name }}</span>
                                @else
                                    <span class="text-amber-600 italic font-semibold">Belum Ditugaskan</span>
                                @endif
                            </td>
                            <td class="font-mono font-bold text-xs">Rp {{ number_format($wo->grand_total, 0, ',', '.') }}</td>
                            <td>
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
                                <span class="badge {{ $badgeMap[$wo->status] ?? 'badge-dipanggil' }}">
                                    {{ strtoupper(str_replace('_', ' ', $wo->status)) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.service_orders.show', $wo) }}" class="btn btn-primary btn-sm text-xs font-bold">
                                    Kelola WO →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <div>Tidak ada pengerjaan servis ditemukan.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200">
            {{ $serviceOrders->appends(request()->query())->links() }}
        </div>
    </div>

</div>
@endsection
