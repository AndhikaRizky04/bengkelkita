@extends('layouts.app')

@section('title', 'Layanan Cuci Motor')

@section('content')
<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Antrean Cuci Motor</h1>
            <p class="text-xs text-gray-500">Kelola status pencucian motor dari antrean hingga finishing</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.wash_orders.index') }}" class="flex items-center gap-4">
            <div>
                <label class="form-label text-xs">Filter Status Cuci</label>
                <select name="status" class="form-select text-xs py-1.5" onchange="this.form.submit()">
                    <option value="">Semua Status Cuci</option>
                    <option value="menunggu" {{ $status === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="antri_cuci" {{ $status === 'antri_cuci' ? 'selected' : '' }}>Antri Cuci</option>
                    <option value="sedang_dicuci" {{ $status === 'sedang_dicuci' ? 'selected' : '' }}>Sedang Dicuci</option>
                    <option value="pengeringan" {{ $status === 'pengeringan' ? 'selected' : '' }}>Pengeringan</option>
                    <option value="finishing" {{ $status === 'finishing' ? 'selected' : '' }}>Finishing</option>
                    <option value="quality_check" {{ $status === 'quality_check' ? 'selected' : '' }}>Quality Check</option>
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
                        <th>Antrean</th>
                        <th>Motor & Plat</th>
                        <th>Pelanggan</th>
                        <th>Paket Cuci</th>
                        <th>Petugas Cuci</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Ubah Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($washOrders as $wo)
                        <tr>
                            <td class="font-black text-sm text-blue-700 font-mono">
                                {{ $wo->queue?->queue_number ?? ('C-' . str_pad($wo->id, 3, '0', STR_PAD_LEFT)) }}
                            </td>
                            <td>
                                <div class="font-bold text-gray-900 text-xs">{{ $wo->vehicle?->full_name }}</div>
                                <div class="text-[11px] font-mono text-gray-500">{{ $wo->vehicle?->license_plate }}</div>
                            </td>
                            <td class="text-xs font-medium">{{ $wo->customer?->name }}</td>
                            <td class="text-xs font-bold text-gray-800">{{ $wo->washPackage?->name }}</td>
                            <td class="text-xs">
                                {{ $wo->washer?->name ?? 'Belum Ditugaskan' }}
                            </td>
                            <td class="text-xs font-mono font-bold text-gray-900">Rp {{ number_format($wo->price, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge badge-in-progress text-xs">
                                    {{ strtoupper(str_replace('_', ' ', $wo->status)) }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.wash_orders.update_status', $wo) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select text-[11px] py-1" onchange="this.form.submit()">
                                        <option value="menunggu" {{ $wo->status === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                        <option value="antri_cuci" {{ $wo->status === 'antri_cuci' ? 'selected' : '' }}>Antri Cuci</option>
                                        <option value="sedang_dicuci" {{ $wo->status === 'sedang_dicuci' ? 'selected' : '' }}>Sedang Dicuci</option>
                                        <option value="pengeringan" {{ $wo->status === 'pengeringan' ? 'selected' : '' }}>Pengeringan</option>
                                        <option value="finishing" {{ $wo->status === 'finishing' ? 'selected' : '' }}>Finishing</option>
                                        <option value="quality_check" {{ $wo->status === 'quality_check' ? 'selected' : '' }}>Quality Check</option>
                                        <option value="selesai" {{ $wo->status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <div>Tidak ada order cuci motor.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200">
            {{ $washOrders->appends(request()->query())->links() }}
        </div>
    </div>

</div>
@endsection
