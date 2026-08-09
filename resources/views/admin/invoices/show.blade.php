@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
@php
    $invStatus = [
        'unpaid'  => ['Belum Bayar', 'bg-red-100 text-red-800'],
        'partial' => ['Sebagian',    'bg-yellow-100 text-yellow-800'],
        'paid'    => ['Lunas',       'bg-green-100 text-green-800'],
    ];
    $cfg = $invStatus[$invoice->status] ?? [$invoice->status, 'bg-gray-100 text-gray-700'];
    $paid = $invoice->payments->where('status', 'completed')->sum('amount');
    $remaining = max($invoice->grand_total - $paid, 0);
    $vehicle = $invoice->serviceOrder?->vehicle ?? $invoice->washOrder?->vehicle;
@endphp

<div class="space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.invoices.index') }}" class="text-gray-400 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-extrabold text-gray-900 font-mono">{{ $invoice->invoice_number }}</h1>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $cfg[1] }}">{{ $cfg[0] }}</span>
            </div>
            <p class="text-xs text-gray-500">{{ $invoice->created_at?->format('d M Y, H:i') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: detail -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <div class="text-[11px] text-gray-400 uppercase font-semibold">Pelanggan</div>
                        <div class="text-sm font-bold text-gray-900">{{ $invoice->customer?->name ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $invoice->customer?->phone ?? '' }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-gray-400 uppercase font-semibold">Kendaraan</div>
                        <div class="text-sm font-bold text-gray-900">{{ $vehicle?->full_name ?? '-' }}</div>
                        <div class="text-xs font-mono text-gray-500">{{ $vehicle?->license_plate ?? '' }}</div>
                    </div>
                </div>

                <div class="table-container border-t border-gray-100 pt-3">
                    <table class="data-table">
                        <thead>
                            <tr><th>Item</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->invoiceItems as $item)
                                <tr>
                                    <td class="text-xs font-medium">{{ $item->item_name }}</td>
                                    <td class="text-xs">{{ $item->quantity }}</td>
                                    <td class="text-xs">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-xs font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-xs text-gray-500 py-4">Tidak ada rincian item.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 mt-3 pt-3 space-y-1 text-sm">
                    <div class="flex justify-between text-gray-600"><span>Subtotal</span><span>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span></div>
                    @if($invoice->discount > 0)
                        <div class="flex justify-between text-gray-600"><span>Diskon</span><span>- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</span></div>
                    @endif
                    @if($invoice->tax > 0)
                        <div class="flex justify-between text-gray-600"><span>Pajak</span><span>Rp {{ number_format($invoice->tax, 0, ',', '.') }}</span></div>
                    @endif
                    <div class="flex justify-between font-black text-gray-900 text-base pt-1"><span>Grand Total</span><span>Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</span></div>
                </div>
            </div>

            <!-- Payment history -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
                <h3 class="text-sm font-bold text-gray-700 mb-3">Riwayat Pembayaran</h3>
                @if($invoice->payments->isEmpty())
                    <p class="text-xs text-gray-500">Belum ada pembayaran.</p>
                @else
                    <div class="table-container">
                        <table class="data-table">
                            <thead><tr><th>Tanggal</th><th>Metode</th><th>Jumlah</th><th>Kasir</th></tr></thead>
                            <tbody>
                                @foreach($invoice->payments as $pay)
                                    <tr>
                                        <td class="text-xs">{{ $pay->payment_date?->format('d/m/Y H:i') }}</td>
                                        <td class="text-xs capitalize">{{ $pay->payment_method }}</td>
                                        <td class="text-xs font-semibold">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                                        <td class="text-xs">{{ $pay->receivedBy?->name ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: payment form -->
        <div>
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 sticky top-20">
                <div class="mb-4">
                    <div class="flex justify-between text-xs text-gray-600 mb-1"><span>Total Tagihan</span><span class="font-semibold">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-xs text-gray-600 mb-1"><span>Sudah Dibayar</span><span class="font-semibold text-green-600">Rp {{ number_format($paid, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-sm font-bold text-gray-900 border-t border-gray-100 pt-2"><span>Sisa</span><span class="text-red-600">Rp {{ number_format($remaining, 0, ',', '.') }}</span></div>
                </div>

                @if($invoice->status !== 'paid')
                    <form method="POST" action="{{ route('admin.invoices.payment', $invoice) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="form-label text-xs">Metode Pembayaran</label>
                            <select name="payment_method" required class="form-select text-xs py-1.5">
                                <option value="tunai">Tunai</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs">Jumlah Bayar</label>
                            <input type="number" name="amount" min="1" value="{{ $remaining }}" required class="form-input text-xs py-1.5">
                        </div>
                        <div>
                            <label class="form-label text-xs">No. Referensi (opsional)</label>
                            <input type="text" name="reference_number" class="form-input text-xs py-1.5">
                        </div>
                        <div>
                            <label class="form-label text-xs">Catatan</label>
                            <input type="text" name="notes" class="form-input text-xs py-1.5">
                        </div>
                        <button type="submit" class="btn btn-success w-full text-xs py-2">Terima Pembayaran</button>
                    </form>
                @else
                    <div class="text-center py-4 text-green-600 text-sm font-semibold">✓ Tagihan sudah lunas</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
