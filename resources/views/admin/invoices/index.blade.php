@extends('layouts.app')

@section('title', 'Kasir / Transaksi')

@section('content')
@php
    $invStatus = [
        'unpaid'  => ['Belum Bayar', 'bg-red-100 text-red-800'],
        'partial' => ['Sebagian',    'bg-yellow-100 text-yellow-800'],
        'paid'    => ['Lunas',       'bg-green-100 text-green-800'],
    ];
@endphp
<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Kasir / Transaksi</h1>
            <p class="text-xs text-gray-500">Daftar tagihan servis dan cuci motor</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="flex items-center gap-4">
            <div>
                <label class="form-label text-xs">Filter Status</label>
                <select name="status" class="form-select text-xs py-1.5" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="partial" {{ $status === 'partial' ? 'selected' : '' }}>Sebagian</option>
                    <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Lunas</option>
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
                        <th>No. Invoice</th>
                        <th>Pelanggan</th>
                        <th>Kendaraan</th>
                        <th>Total</th>
                        <th>Terbayar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        @php
                            $cfg = $invStatus[$inv->status] ?? [$inv->status, 'bg-gray-100 text-gray-700'];
                            $paid = $inv->payments->where('status', 'completed')->sum('amount');
                            $vehicle = $inv->serviceOrder?->vehicle ?? $inv->washOrder?->vehicle;
                        @endphp
                        <tr>
                            <td class="text-xs font-mono font-bold text-blue-700">{{ $inv->invoice_number }}</td>
                            <td class="text-xs font-medium">{{ $inv->customer?->name ?? '-' }}</td>
                            <td>
                                <div class="text-xs">{{ $vehicle?->vehicleModel?->name ?? '-' }}</div>
                                <div class="text-[11px] font-mono text-gray-500">{{ $vehicle?->license_plate ?? '' }}</div>
                            </td>
                            <td class="text-xs font-semibold">Rp {{ number_format($inv->grand_total, 0, ',', '.') }}</td>
                            <td class="text-xs">Rp {{ number_format($paid, 0, ',', '.') }}</td>
                            <td><span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $cfg[1] }}">{{ $cfg[0] }}</span></td>
                            <td>
                                <a href="{{ route('admin.invoices.show', $inv) }}" class="text-[11px] text-blue-600 hover:underline font-semibold">Detail / Bayar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-sm text-gray-500 py-8">Belum ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $invoices->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
