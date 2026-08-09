@extends('layouts.app')

@section('title', 'Pekerjaan Saya')

@section('content')
@php
    $soStatus = [
        'pending'       => ['label' => 'Menunggu',        'badge' => 'bg-gray-100 text-gray-700'],
        'inspection'    => ['label' => 'Pemeriksaan',     'badge' => 'bg-yellow-100 text-yellow-800'],
        'in_progress'   => ['label' => 'Pengerjaan',      'badge' => 'bg-blue-100 text-blue-800'],
        'waiting_parts' => ['label' => 'Menunggu Sparepart', 'badge' => 'bg-orange-100 text-orange-800'],
        'final_check'   => ['label' => 'Pemeriksaan Akhir', 'badge' => 'bg-purple-100 text-purple-800'],
        'ready'         => ['label' => 'Siap Diambil',    'badge' => 'bg-teal-100 text-teal-800'],
        'completed'     => ['label' => 'Selesai',         'badge' => 'bg-green-100 text-green-800'],
    ];
@endphp

<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Pekerjaan Saya</h1>
            <p class="text-xs text-gray-500">Daftar servis yang perlu dikerjakan hari ini</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-white border border-gray-200 rounded-lg px-4 py-2 text-center shadow-sm">
                <div class="text-2xl font-black text-blue-600">{{ $assignedJobs->count() }}</div>
                <div class="text-[11px] text-gray-500 font-medium">Antrean Servis</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg px-4 py-2 text-center shadow-sm">
                <div class="text-2xl font-black text-green-600">{{ $completedToday->count() }}</div>
                <div class="text-[11px] text-gray-500 font-medium">Selesai Hari Ini</div>
            </div>
        </div>
    </div>

    <!-- Daftar Pekerjaan -->
    <div>
        <h2 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wide">Perlu Dikerjakan</h2>

        @if($assignedJobs->isEmpty())
            <div class="bg-white border border-dashed border-gray-300 rounded-lg p-10 text-center">
                <p class="text-sm text-gray-500">Tidak ada pekerjaan servis yang aktif saat ini.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($assignedJobs as $job)
                    @php $cfg = $soStatus[$job->status] ?? $soStatus['pending']; @endphp
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <div class="font-mono font-black text-blue-700 text-sm">{{ $job->order_number }}</div>
                                    <div class="text-[11px] text-gray-400">
                                        Antrean {{ $job->queue?->queue_number ?? '-' }}
                                    </div>
                                </div>
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $cfg['badge'] }}">
                                    {{ $cfg['label'] }}
                                </span>
                            </div>

                            <div class="mb-2">
                                <div class="font-bold text-gray-900 text-sm">{{ $job->vehicle?->full_name }}</div>
                                <div class="text-xs font-mono text-gray-500">{{ $job->vehicle?->license_plate }}</div>
                            </div>

                            <div class="text-xs text-gray-600 space-y-1 mb-3">
                                <div><span class="text-gray-400">Pelanggan:</span> <span class="font-medium">{{ $job->customer?->name ?? '-' }}</span></div>
                                @if($job->complaint)
                                    <div class="text-gray-500"><span class="text-gray-400">Keluhan:</span> {{ \Illuminate\Support\Str::limit($job->complaint, 60) }}</div>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('mechanic.job_detail', $job) }}" class="btn btn-primary w-full text-xs py-2 mt-2 text-center">
                            Kerjakan &rarr;
                        </a>
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
                <p class="text-sm text-gray-500">Belum ada pekerjaan yang selesai hari ini.</p>
            </div>
        @else
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No. Order</th>
                                <th>Motor & Plat</th>
                                <th>Pelanggan</th>
                                <th>Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($completedToday as $job)
                                <tr>
                                    <td class="font-mono font-bold text-xs text-blue-700">{{ $job->order_number }}</td>
                                    <td>
                                        <div class="font-bold text-gray-900 text-xs">{{ $job->vehicle?->full_name }}</div>
                                        <div class="text-[11px] font-mono text-gray-500">{{ $job->vehicle?->license_plate }}</div>
                                    </td>
                                    <td class="text-xs font-medium">{{ $job->customer?->name ?? '-' }}</td>
                                    <td class="text-xs text-gray-500">{{ $job->completed_at?->format('H:i') ?? '-' }}</td>
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
