@extends('layouts.public')

@section('title', 'Cek Status Pengerjaan Motor - BengkelKita')

@section('content')
<section class="py-12 bg-gray-100 min-h-[calc(100vh-200px)]">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">

        <!-- Search Box Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8 mb-8">
            <h1 class="text-xl font-bold text-gray-900 mb-2">Cek Status Progress Kendaraan</h1>
            <p class="text-xs text-gray-600 mb-6">Masukkan nomor antrean yang Anda terima saat mendaftar (contoh: A-001)</p>

            @if(session('success_queue'))
                <div class="p-4 mb-6 bg-green-50 border border-green-200 text-green-900 rounded-lg">
                    <div class="font-bold text-lg text-green-700">{{ session('success_queue') }}</div>
                    <div class="text-xs text-green-600 mt-1">Silakan simpan nomor antrean ini untuk mengecek status atau mengambil motor.</div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <form method="GET" action="{{ route('public.status') }}" class="flex gap-3">
                <input
                    type="text"
                    name="queue"
                    value="{{ $queueNumber }}"
                    required
                    placeholder="Masukkan Nomor Antrean (Contoh: A-024)"
                    class="form-input text-base font-bold uppercase tracking-wider py-3"
                >
                <button type="submit" class="btn btn-primary px-6 font-bold whitespace-nowrap">
                    Cek Status
                </button>
            </form>
        </div>

        <!-- Queue Status Result Card -->
        @if($queue)
            <!-- BIG BANNERS FOR "SIAP DIAMBIL" OR "SELESAI" -->
            @php
                $statusInvoice = $queue->serviceOrder?->invoice ?? $queue->washOrder?->invoice;
                $needsPayment = $queue->status === 'menunggu_pembayaran' && $statusInvoice && !$statusInvoice->isPaid();
            @endphp

            @if($needsPayment)
                <div class="bg-amber-500 text-white rounded-xl p-6 sm:p-8 mb-8 shadow-lg text-center">
                    <div class="text-2xl sm:text-3xl font-black mb-2">💳 PENGERJAAN SELESAI — SILAKAN LAKUKAN PEMBAYARAN</div>
                    <p class="text-sm text-amber-50 mb-4">Total tagihan Anda: <span class="font-extrabold">Rp {{ number_format($statusInvoice->grand_total, 0, ',', '.') }}</span>. Lakukan pembayaran agar kendaraan dapat diserahterimakan.</p>
                    <a href="{{ route('public.payment.show', ['queueNumber' => $queue->queue_number]) }}" class="inline-block bg-white text-amber-700 font-extrabold px-6 py-3 rounded-lg text-base hover:bg-amber-50 transition">
                        Bayar Sekarang →
                    </a>
                </div>
            @elseif(in_array($queue->status, ['siap_diambil', 'menunggu_pembayaran']))
                <div class="bg-emerald-600 text-white rounded-xl p-6 sm:p-8 mb-8 shadow-lg text-center animate-bounce-short">
                    <div class="text-3xl font-black mb-2">🎉 MOTOR ANDA SUDAH SIAP DIAMBIL!</div>
                    <p class="text-sm text-emerald-100 mb-4">Pembayaran telah selesai. Silakan tunjukkan nomor antrean <span class="underline font-bold">{{ $queue->queue_number }}</span> kepada petugas di bengkel.</p>
                    <div class="inline-block bg-white text-emerald-800 font-extrabold px-4 py-2 rounded-lg text-lg">
                        Nomor Antrean: {{ $queue->queue_number }}
                    </div>
                </div>
            @elseif($queue->status === 'selesai')
                <div class="bg-gray-800 text-white rounded-xl p-6 mb-8 text-center">
                    <div class="text-xl font-bold text-gray-200">✅ KENDARAAN SUDAH DIAMBIL</div>
                    <p class="text-xs text-gray-400 mt-1">Terima kasih telah mempercayakan perawatan motor Anda di BengkelKita.</p>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Card Header -->
                <div class="p-6 border-b border-gray-200 bg-gray-50 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Antrean Hari Ini</div>
                        <div class="text-3xl font-black text-blue-600">{{ $queue->queue_number }}</div>
                    </div>

                    <div class="text-right">
                        <div class="text-base font-bold text-gray-900">{{ $queue->vehicle?->full_name }}</div>
                        <div class="text-xs font-semibold text-gray-600">{{ $queue->vehicle?->license_plate }}</div>
                        <div class="text-xs text-gray-500 mt-1">Tanggal: {{ \Carbon\Carbon::parse($queue->queue_date)->translatedFormat('d F Y') }}</div>
                    </div>
                </div>

                <!-- Current Status Badge -->
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-gray-500 font-medium">Status Pengerjaan Saat Ini:</span>
                    @php
                        $badgeClasses = [
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
                    <span class="badge {{ $badgeClasses[$queue->status] ?? 'badge-dipanggil' }} text-sm font-bold px-3 py-1">
                        {{ strtoupper(str_replace('_', ' ', $queue->status)) }}
                    </span>
                </div>

                <!-- Progress Timeline (Simple & Functional) -->
                <div class="p-6 sm:p-8">
                    <h3 class="text-sm font-bold text-gray-800 mb-6">Progress Pengerjaan Kendaraan</h3>

                    @php
                        $steps = [
                            '1. Pendaftaran' => in_array($queue->status, ['menunggu', 'dipanggil', 'pemeriksaan', 'pengerjaan', 'menunggu_sparepart', 'menunggu_pembayaran', 'siap_diambil', 'selesai']),
                            '2. Pemeriksaan Motor' => in_array($queue->status, ['pemeriksaan', 'pengerjaan', 'menunggu_sparepart', 'menunggu_pembayaran', 'siap_diambil', 'selesai']),
                            '3. Pengerjaan Servis / Cuci' => in_array($queue->status, ['pengerjaan', 'menunggu_pembayaran', 'siap_diambil', 'selesai']),
                            '4. Pemeriksaan Akhir / Pembayaran' => in_array($queue->status, ['menunggu_pembayaran', 'siap_diambil', 'selesai']),
                            '5. Siap Diambil' => in_array($queue->status, ['siap_diambil', 'selesai']),
                        ];

                        $currentStepIndex = match($queue->status) {
                            'menunggu', 'dipanggil' => 1,
                            'pemeriksaan' => 2,
                            'pengerjaan', 'menunggu_sparepart' => 3,
                            'menunggu_pembayaran' => 4,
                            'siap_diambil', 'selesai' => 5,
                            default => 1
                        };
                    @endphp

                    <div class="space-y-6 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-gray-200">
                        @php $idx = 1; @endphp
                        @foreach($steps as $title => $isPassed)
                            <div class="relative flex items-start gap-4">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0 z-10 {{ $isPassed ? ($idx === $currentStepIndex ? 'bg-blue-600 text-white ring-4 ring-blue-100' : 'bg-green-500 text-white') : 'bg-gray-200 text-gray-500' }}">
                                    @if($isPassed && $idx < $currentStepIndex)
                                        ✓
                                    @elseif($idx === $currentStepIndex)
                                        ●
                                    @else
                                        ○
                                    @endif
                                </div>
                                <div class="pt-1">
                                    <div class="text-sm font-bold {{ $isPassed ? 'text-gray-900' : 'text-gray-400' }}">{{ $title }}</div>
                                    @if($idx === $currentStepIndex)
                                        <div class="text-xs text-blue-600 font-semibold mt-0.5">Sedang Berlangsung...</div>
                                    @endif
                                </div>
                            </div>
                            @php $idx++; @endphp
                        @endforeach
                    </div>
                </div>

                <!-- Complaint Details -->
                <div class="p-6 bg-gray-50 border-t border-gray-200">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Keluhan Pelanggan</div>
                    <div class="text-xs text-gray-800 italic">"{{ $queue->complaint }}"</div>
                </div>
            </div>
        @elseif($queueNumber)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                <div class="text-4xl mb-3">🔍</div>
                <h3 class="text-base font-bold text-gray-800">Nomor Antrean "{{ $queueNumber }}" Tidak Ditemukan</h3>
                <p class="text-xs text-gray-500 mt-1">Pastikan Anda memasukkan nomor antrean yang tepat (contoh: A-001) yang terdaftar dalam 7 hari terakhir.</p>
            </div>
        @endif

    </div>
</section>
@endsection
