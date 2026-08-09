@extends('layouts.app')

@section('title', 'Antrean Cuci Motor')

@section('content')
@php
    $washStatusFlow = [
        'menunggu'      => ['label' => 'Menunggu',      'next' => 'antri_cuci',    'next_label' => 'Masukkan ke Antri Cuci', 'badge' => 'bg-gray-100 text-gray-700'],
        'antri_cuci'    => ['label' => 'Antri Cuci',    'next' => 'sedang_dicuci', 'next_label' => 'Mulai Cuci',             'badge' => 'bg-yellow-100 text-yellow-800'],
        'sedang_dicuci' => ['label' => 'Sedang Dicuci', 'next' => 'pengeringan',   'next_label' => 'Lanjut Pengeringan',     'badge' => 'bg-blue-100 text-blue-800'],
        'pengeringan'   => ['label' => 'Pengeringan',   'next' => 'finishing',     'next_label' => 'Lanjut Finishing',       'badge' => 'bg-indigo-100 text-indigo-800'],
        'finishing'     => ['label' => 'Finishing',     'next' => 'quality_check', 'next_label' => 'Cek Kualitas',           'badge' => 'bg-purple-100 text-purple-800'],
        'quality_check' => ['label' => 'Quality Check', 'next' => 'selesai',       'next_label' => 'Selesaikan',             'badge' => 'bg-teal-100 text-teal-800'],
        'selesai'       => ['label' => 'Selesai',       'next' => null,            'next_label' => null,                     'badge' => 'bg-green-100 text-green-800'],
    ];
@endphp

<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Antrean Cuci Motor</h1>
            <p class="text-xs text-gray-500">Kelola proses pencucian motor dari antrean hingga selesai</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-white border border-gray-200 rounded-lg px-4 py-2 text-center shadow-sm">
                <div class="text-2xl font-black text-blue-600">{{ $activeWashOrders->count() }}</div>
                <div class="text-[11px] text-gray-500 font-medium">Antrean Aktif</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg px-4 py-2 text-center shadow-sm">
                <div class="text-2xl font-black text-green-600">{{ $completedToday->count() }}</div>
                <div class="text-[11px] text-gray-500 font-medium">Selesai Hari Ini</div>
            </div>
        </div>
    </div>

    <!-- Antrean Aktif -->
    <div>
        <h2 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wide">Sedang Dikerjakan</h2>

        @if($activeWashOrders->isEmpty())
            <div class="bg-white border border-dashed border-gray-300 rounded-lg p-10 text-center">
                <p class="text-sm text-gray-500">Tidak ada antrean cuci yang aktif saat ini.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($activeWashOrders as $wo)
                    @php $cfg = $washStatusFlow[$wo->status] ?? $washStatusFlow['menunggu']; @endphp
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between mb-3">
                                <div class="font-mono font-black text-blue-700 text-lg">
                                    {{ $wo->queue?->queue_number ?? ('C-' . str_pad($wo->id, 3, '0', STR_PAD_LEFT)) }}
                                </div>
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $cfg['badge'] }}">
                                    {{ $cfg['label'] }}
                                </span>
                            </div>

                            <div class="mb-2">
                                <div class="font-bold text-gray-900 text-sm">{{ $wo->vehicle?->full_name }}</div>
                                <div class="text-xs font-mono text-gray-500">{{ $wo->vehicle?->license_plate }}</div>
                            </div>

                            <div class="text-xs text-gray-600 space-y-1 mb-3">
                                <div><span class="text-gray-400">Pelanggan:</span> <span class="font-medium">{{ $wo->customer?->name ?? '-' }}</span></div>
                                <div><span class="text-gray-400">Paket:</span> <span class="font-medium">{{ $wo->washPackage?->name ?? '-' }}</span></div>
                                <div><span class="text-gray-400">Harga:</span> <span class="font-semibold text-gray-800">Rp {{ number_format($wo->price ?? 0, 0, ',', '.') }}</span></div>
                            </div>
                        </div>

                        @if($cfg['next'])
                            <form method="POST" action="{{ route('washer.update_status', $wo) }}" class="mt-2">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $cfg['next'] }}">
                                <button type="submit" class="btn btn-primary w-full text-xs py-2">
                                    {{ $cfg['next_label'] }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Selesai Hari Ini -->
    <div>
        <h2 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wide">Selesai Hari Ini</h2>

        @if($completedToday->isEmpty())
            <div class="bg-white border border-dashed border-gray-300 rounded-lg p-8 text-center">
                <p class="text-sm text-gray-500">Belum ada pencucian yang selesai hari ini.</p>
            </div>
        @else
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Motor & Plat</th>
                                <th>Pelanggan</th>
                                <th>Paket</th>
                                <th>Harga</th>
                                <th>Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($completedToday as $wo)
                                <tr>
                                    <td>
                                        <div class="font-bold text-gray-900 text-xs">{{ $wo->vehicle?->full_name }}</div>
                                        <div class="text-[11px] font-mono text-gray-500">{{ $wo->vehicle?->license_plate }}</div>
                                    </td>
                                    <td class="text-xs font-medium">{{ $wo->customer?->name ?? '-' }}</td>
                                    <td class="text-xs">{{ $wo->washPackage?->name ?? '-' }}</td>
                                    <td class="text-xs font-semibold">Rp {{ number_format($wo->price ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-xs text-gray-500">{{ $wo->completed_at?->format('H:i') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
