@extends('layouts.app')

@section('title', 'Serah Terima Kendaraan (Pengambilan Motor)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header info banner -->
    <div class="bg-blue-900 text-white rounded-xl p-6 shadow-sm">
        <h1 class="text-xl font-extrabold mb-1">🔑 Serah Terima Kendaraan Pelanggan</h1>
        <p class="text-xs text-blue-200 leading-relaxed">
            Sistem verifikasi pengambilan motor berdasarkan <strong>Nomor Antrean</strong>. Pelanggan tidak perlu login, cukup menunjukkan nomor antrean (contoh: A-001).
        </p>
    </div>

    <!-- Search Form -->
    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.handover.index') }}" class="space-y-3">
            <label class="form-label font-bold text-gray-800">Cari Nomor Antrean Pelanggan</label>
            <div class="flex gap-3">
                <input
                    type="text"
                    name="queue_number"
                    value="{{ $queueNumber }}"
                    required
                    placeholder="Masukkan Nomor Antrean (Contoh: A-001)"
                    class="form-input text-lg font-black uppercase tracking-wider py-3"
                    autofocus
                >
                <button type="submit" class="btn btn-primary px-8 font-bold text-sm">
                    Cari Antrean
                </button>
            </div>
        </form>
    </div>

    <!-- Results Card -->
    @if($queue)
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Nomor Antrean</div>
                    <div class="text-4xl font-black text-blue-600 tracking-wide">{{ $queue->queue_number }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 font-medium">Tanggal Antrean</div>
                    <div class="text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($queue->queue_date)->translatedFormat('d F Y') }}</div>
                </div>

                <div>
                    <span class="badge text-sm font-bold px-3 py-1 bg-emerald-100 text-emerald-800 border border-emerald-300">
                        STATUS: {{ strtoupper(str_replace('_', ' ', $queue->status)) }}
                    </span>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 border-b border-gray-200">
                <!-- Vehicle Details -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Informasi Kendaraan</h3>
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Nomor Polisi:</span>
                            <span class="font-bold text-gray-900 text-sm font-mono">{{ $queue->vehicle?->license_plate }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Merk & Tipe:</span>
                            <span class="font-bold text-gray-900">{{ $queue->vehicle?->full_name }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Tahun:</span>
                            <span class="font-medium text-gray-700">{{ $queue->vehicle?->year }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Warna:</span>
                            <span class="font-medium text-gray-700">{{ $queue->vehicle?->color ?? 'Hitam' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Informasi Pelanggan (Internal)</h3>
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Nama Pelanggan:</span>
                            <span class="font-bold text-gray-900 text-sm">{{ $queue->customer?->name }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">WhatsApp / HP:</span>
                            <span class="font-bold text-blue-700 font-mono">{{ $queue->customer?->phone }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Jenis Layanan:</span>
                            <span class="font-semibold text-gray-800 uppercase">{{ str_replace('_', ' + ', $queue->service_type) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Check & Confirmation Action -->
            <div class="p-6 bg-gray-50 flex flex-wrap items-center justify-between gap-4">
                <div>
                    @php
                        $serviceInvoice = $queue->serviceOrder?->invoice;
                        $washInvoice = $queue->washOrder?->invoice;
                        $invoice = $serviceInvoice ?? $washInvoice;
                    @endphp

                    @if($invoice)
                        <div class="text-xs font-semibold text-gray-500 mb-1">Status Pembayaran Faktur (#{{ $invoice->invoice_number }}):</div>
                        @if($invoice->isPaid())
                            <div class="inline-flex items-center gap-1.5 text-xs font-bold text-green-700 bg-green-100 border border-green-300 px-3 py-1 rounded-full">
                                <span>LUNAS (Rp {{ number_format($invoice->grand_total, 0, ',', '.') }})</span>
                            </div>
                        @else
                            <div class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-100 border border-amber-300 px-3 py-1 rounded-full">
                                <span>BELUM DIBAYAR (Rp {{ number_format($invoice->grand_total, 0, ',', '.') }})</span>
                            </div>
                            <div class="text-[11px] text-red-600 mt-1">Selesaikan pembayaran di kasir terlebih dahulu.</div>
                        @endif
                    @else
                        <div class="text-xs font-bold text-gray-700">Belum Ada Tagihan Pembayaran</div>
                    @endif
                </div>

                <div>
                    @if($queue->status === 'selesai')
                        <div class="text-xs font-bold text-gray-500 bg-gray-200 px-4 py-2 rounded-lg">
                            ✅ Kendaraan Sudah Diserahkan
                        </div>
                    @else
                        <form method="POST" action="{{ route('admin.handover.complete', $queue) }}" onsubmit="return confirm('Pastikan nomor antrean {{ $queue->queue_number }} dan kunci motor sesuai dengan pelanggan. Konfirmasi serah terima?')">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg font-bold">
                                🔑 Serahkan Kendaraan Ke Pelanggan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @elseif($queueNumber)
        <div class="bg-white border border-gray-200 rounded-xl p-8 text-center text-gray-500">
            <div class="text-4xl mb-2">🔍</div>
            <div class="font-bold text-gray-800 text-base">Antrean "{{ $queueNumber }}" Tidak Ditemukan</div>
            <div class="text-xs text-gray-500 mt-1">Periksa kembali nomor antrean yang dimasukkan.</div>
        </div>
    @endif

</div>
@endsection
