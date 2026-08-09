@extends('layouts.app')

@section('title', 'Riwayat Servis Kendaraan - ' . $vehicle->license_plate)

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.vehicles.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">← Kendaraan</a>
                <span class="text-gray-400">/</span>
                <span class="text-xs font-bold text-gray-700">Riwayat Servis</span>
            </div>
            <h1 class="text-xl font-extrabold text-gray-900 mt-1 font-mono">{{ $vehicle->license_plate }} — {{ $vehicle->full_name }}</h1>
        </div>
    </div>

    <!-- Vehicle Details & Maintenance Reminder Box -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Vehicle specs -->
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Spesifikasi Kendaraan</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-gray-500 block">Pemilik:</span>
                    <a href="{{ route('admin.customers.show', $vehicle->customer) }}" class="font-bold text-blue-700 hover:underline">
                        {{ $vehicle->customer?->name }}
                    </a>
                </div>
                <div>
                    <span class="text-gray-500 block">WhatsApp:</span>
                    <span class="font-bold font-mono">{{ $vehicle->customer?->phone }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">Tahun / Warna:</span>
                    <span class="font-semibold text-gray-800">{{ $vehicle->year }} / {{ $vehicle->color ?? 'Hitam' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">KM Terakhir:</span>
                    <span class="font-bold text-emerald-700 font-mono">{{ number_format($vehicle->last_kilometer, 0, ',', '.') }} KM</span>
                </div>
            </div>
        </div>

        <!-- Next Service Reminder Box (Requirement 29) -->
        <div class="bg-blue-900 text-white rounded-xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="text-xs font-semibold text-blue-300 uppercase tracking-wider mb-1">Rekomendasi Servis Berikutnya</div>
                @php
                    $nextKm = $vehicle->last_kilometer + 2500;
                    $diffKm = 2500;
                @endphp
                <div class="text-2xl font-black mt-1">± {{ number_format($nextKm, 0, ',', '.') }} KM</div>
                <div class="text-xs text-blue-200 mt-2">
                    Servis berikutnya disarankan pada sekitar <strong>{{ number_format($nextKm, 0, ',', '.') }} KM</strong> (± {{ number_format($diffKm, 0, ',', '.') }} KM lagi).
                </div>
            </div>
            <div class="text-[11px] text-blue-300 italic border-t border-blue-800 pt-3 mt-4">
                *Jadwal estimasi berbasis interval berkala 2.500 KM.
            </div>
        </div>

    </div>

    <!-- Service History Timeline Table (Requirement 28) -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <h2 class="text-base font-bold text-gray-900 mb-4">Riwayat Servis & Perawatan ({{ $serviceHistories->count() }})</h2>

        <div class="space-y-6">
            @forelse($serviceHistories as $history)
                <div class="border border-gray-200 rounded-lg p-5 bg-gray-50 space-y-3">
                    <div class="flex flex-wrap items-center justify-between border-b pb-3 gap-2">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-blue-700 bg-blue-100 px-2.5 py-1 rounded">
                                🗓️ {{ \Carbon\Carbon::parse($history->service_date)->translatedFormat('d F Y') }}
                            </span>
                            <span class="text-xs font-mono font-bold text-gray-700 bg-gray-200 px-2 py-0.5 rounded">
                                {{ number_format($history->kilometer, 0, ',', '.') }} KM
                            </span>
                        </div>
                        <div class="text-xs text-gray-500 font-medium">
                            Mekanik: <span class="font-bold text-gray-800">{{ $history->mechanic_name ?? 'Mekanik BengkelKita' }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                        <div>
                            <span class="font-bold text-gray-700 block mb-1">Keluhan:</span>
                            <p class="text-gray-600 italic">"{{ $history->description }}"</p>
                        </div>

                        <div>
                            <span class="font-bold text-gray-700 block mb-1">Layanan Dikerjakan:</span>
                            <p class="text-gray-800 font-medium">{{ $history->services_performed ?: '-' }}</p>
                        </div>

                        <div>
                            <span class="font-bold text-gray-700 block mb-1">Sparepart & Oli Terpasang:</span>
                            <p class="text-gray-800">{{ $history->spareparts_used ?: 'Tidak ada sparepart' }}</p>
                            @if($history->oil_used)
                                <p class="text-blue-700 font-semibold mt-1">Oli: {{ $history->oil_used }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="border-t pt-3 flex justify-between items-center text-xs">
                        <span class="text-gray-500">Total Biaya:</span>
                        <span class="font-extrabold text-blue-700 text-sm">Rp {{ number_format($history->total_cost, 0, ',', '.') }}</span>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-state-icon">🏍️</div>
                    <div class="text-xs">Belum ada riwayat servis untuk kendaraan ini.</div>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
