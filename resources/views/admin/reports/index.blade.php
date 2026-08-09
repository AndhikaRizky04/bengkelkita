@extends('layouts.app')

@section('title', 'Laporan Bengkel')

@section('content')
<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Laporan Bengkel</h1>
            <p class="text-xs text-gray-500">Ringkasan pendapatan dan aktivitas bengkel</p>
        </div>
    </div>

    <!-- Filter periode -->
    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label text-xs">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-input text-xs py-1.5">
            </div>
            <div>
                <label class="form-label text-xs">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-input text-xs py-1.5">
            </div>
            <button type="submit" class="btn btn-primary text-xs py-1.5">Terapkan</button>
        </form>
    </div>

    <!-- Revenue cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
            <div class="text-[11px] text-gray-400 uppercase font-semibold">Total Pendapatan</div>
            <div class="text-xl font-black text-blue-600 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
            <div class="text-[11px] text-gray-400 uppercase font-semibold">Tunai</div>
            <div class="text-xl font-black text-gray-800 mt-1">Rp {{ number_format($cashRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
            <div class="text-[11px] text-gray-400 uppercase font-semibold">Transfer</div>
            <div class="text-xl font-black text-gray-800 mt-1">Rp {{ number_format($transferRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
            <div class="text-[11px] text-gray-400 uppercase font-semibold">QRIS</div>
            <div class="text-xl font-black text-gray-800 mt-1">Rp {{ number_format($qrisRevenue, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
            <div class="text-[11px] text-gray-400 uppercase font-semibold">Servis Selesai</div>
            <div class="text-2xl font-black text-gray-800 mt-1">{{ $completedServices->count() }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
            <div class="text-[11px] text-gray-400 uppercase font-semibold">Cuci Selesai</div>
            <div class="text-2xl font-black text-gray-800 mt-1">{{ $completedWashes->count() }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
            <div class="text-[11px] text-gray-400 uppercase font-semibold">Nilai Persediaan</div>
            <div class="text-2xl font-black text-gray-800 mt-1">Rp {{ number_format($inventoryValue ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top services -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-700 mb-3">Servis Terpopuler</h3>
            @if($topServices->isEmpty())
                <p class="text-xs text-gray-500">Tidak ada data.</p>
            @else
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Servis</th><th>Jumlah</th><th>Pendapatan</th></tr></thead>
                        <tbody>
                            @foreach($topServices as $ts)
                                <tr>
                                    <td class="text-xs font-medium">{{ $ts->service?->name ?? 'Servis #' . $ts->service_id }}</td>
                                    <td class="text-xs">{{ $ts->total_count }}</td>
                                    <td class="text-xs font-semibold">Rp {{ number_format($ts->total_revenue, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Top spareparts -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-700 mb-3">Sparepart Terlaris</h3>
            @if($topSpareparts->isEmpty())
                <p class="text-xs text-gray-500">Tidak ada data.</p>
            @else
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Sparepart</th><th>Qty</th><th>Pendapatan</th></tr></thead>
                        <tbody>
                            @foreach($topSpareparts as $tsp)
                                <tr>
                                    <td class="text-xs font-medium">{{ $tsp->sparepart?->name ?? 'Sparepart #' . $tsp->sparepart_id }}</td>
                                    <td class="text-xs">{{ $tsp->total_qty }}</td>
                                    <td class="text-xs font-semibold">Rp {{ number_format($tsp->total_revenue, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Top wash packages -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 lg:col-span-2">
            <h3 class="text-sm font-bold text-gray-700 mb-3">Paket Cuci Terpopuler</h3>
            @if($topWashPackages->isEmpty())
                <p class="text-xs text-gray-500">Tidak ada data.</p>
            @else
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Paket Cuci</th><th>Jumlah</th><th>Pendapatan</th></tr></thead>
                        <tbody>
                            @foreach($topWashPackages as $twp)
                                <tr>
                                    <td class="text-xs font-medium">{{ $twp->washPackage?->name ?? 'Paket #' . $twp->wash_package_id }}</td>
                                    <td class="text-xs">{{ $twp->total_count }}</td>
                                    <td class="text-xs font-semibold">Rp {{ number_format($twp->total_revenue, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
