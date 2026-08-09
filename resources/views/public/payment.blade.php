@extends('layouts.public')

@section('title', 'Pembayaran ' . $queue->queue_number . ' - BengkelKita')

@section('content')
<section class="py-12 bg-gray-100 min-h-[calc(100vh-200px)]">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">

        <a href="{{ route('public.status', ['queue' => $queue->queue_number]) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800 mb-4">
            ← Kembali ke status antrean
        </a>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pembayaran Antrean</div>
                    <div class="text-3xl font-black text-blue-600">{{ $queue->queue_number }}</div>
                </div>
                <div class="text-right">
                    <div class="text-base font-bold text-gray-900">{{ $queue->vehicle?->full_name }}</div>
                    <div class="text-xs font-semibold text-gray-600">{{ $queue->vehicle?->license_plate }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ $queue->customer?->name }}</div>
                </div>
            </div>

            <!-- Invoice items -->
            <div class="p-6">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Rincian Tagihan — {{ $invoice->invoice_number }}</div>
                <div class="divide-y divide-gray-100">
                    @foreach($invoice->invoiceItems as $item)
                        <div class="flex items-center justify-between py-2">
                            <div>
                                <div class="text-sm font-medium text-gray-800">{{ $item->item_name }}</div>
                                <div class="text-xs text-gray-400 capitalize">{{ $item->item_type }} · {{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</div>
                            </div>
                            <div class="text-sm font-semibold text-gray-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-200 mt-3 pt-3 space-y-1">
                    <div class="flex justify-between text-sm text-gray-600"><span>Subtotal</span><span>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span></div>
                    @if($invoice->discount > 0)
                        <div class="flex justify-between text-sm text-gray-600"><span>Diskon</span><span>- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</span></div>
                    @endif
                    @if($paidSoFar > 0)
                        <div class="flex justify-between text-sm text-green-600"><span>Sudah dibayar</span><span>- Rp {{ number_format($paidSoFar, 0, ',', '.') }}</span></div>
                    @endif
                    <div class="flex justify-between text-lg font-black text-gray-900 pt-1"><span>Total Bayar</span><span>Rp {{ number_format($remaining, 0, ',', '.') }}</span></div>
                </div>
            </div>

            <!-- Payment method -->
            <form method="POST" action="{{ route('public.payment.pay', ['queueNumber' => $queue->queue_number]) }}" class="p-6 border-t border-gray-200 bg-gray-50" x-data="{ method: '' }">
                @csrf
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Pilih Metode Pembayaran</div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
                    <label class="cursor-pointer border-2 rounded-lg p-4 flex items-center gap-3 transition" :class="method === 'transfer' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 bg-white'">
                        <input type="radio" name="payment_method" value="transfer" x-model="method" class="sr-only">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold">TF</div>
                        <div>
                            <div class="text-sm font-bold text-gray-800">Transfer Bank</div>
                            <div class="text-xs text-gray-500">BCA / Mandiri / BRI</div>
                        </div>
                    </label>

                    <label class="cursor-pointer border-2 rounded-lg p-4 flex items-center gap-3 transition" :class="method === 'qris' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 bg-white'">
                        <input type="radio" name="payment_method" value="qris" x-model="method" class="sr-only">
                        <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center font-bold">QR</div>
                        <div>
                            <div class="text-sm font-bold text-gray-800">QRIS</div>
                            <div class="text-xs text-gray-500">Scan & bayar</div>
                        </div>
                    </label>
                </div>

                <button type="submit" :disabled="!method" class="btn btn-primary w-full py-3 font-bold text-base disabled:opacity-50 disabled:cursor-not-allowed">
                    Bayar Rp {{ number_format($remaining, 0, ',', '.') }}
                </button>

                <p class="text-[11px] text-gray-400 text-center mt-3">
                    Ini adalah pembayaran simulasi untuk keperluan demo. Tagihan akan langsung ditandai lunas setelah tombol ditekan.
                </p>
            </form>
        </div>
    </div>
</section>
@endsection
