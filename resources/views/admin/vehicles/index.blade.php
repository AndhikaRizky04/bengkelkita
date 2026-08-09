@extends('layouts.app')

@section('title', 'Data Kendaraan Motor')

@section('content')
<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Data Kendaraan Motor</h1>
            <p class="text-xs text-gray-500">Daftar seluruh kendaraan terdaftar di bengkel</p>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.vehicles.index') }}" class="flex gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari plat H 1234 ABC, nama pelanggan, merk..." class="form-input text-xs uppercase">
            <button type="submit" class="btn btn-secondary text-xs">Cari Kendaraan</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nomor Polisi</th>
                        <th>Merk & Tipe Motor</th>
                        <th>Pemilik / Pelanggan</th>
                        <th>Tahun</th>
                        <th>KM Terakhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $v)
                        <tr>
                            <td class="font-black text-sm text-gray-900 font-mono">{{ $v->license_plate }}</td>
                            <td class="font-bold text-blue-700 text-xs">{{ $v->full_name }}</td>
                            <td class="text-xs">
                                <a href="{{ route('admin.customers.show', $v->customer) }}" class="font-semibold text-gray-800 hover:underline">
                                    {{ $v->customer?->name }}
                                </a>
                                <div class="text-[10px] text-gray-500 font-mono">{{ $v->customer?->phone }}</div>
                            </td>
                            <td class="text-xs text-gray-600">{{ $v->year }}</td>
                            <td class="text-xs font-mono font-semibold">{{ number_format($v->last_kilometer, 0, ',', '.') }} KM</td>
                            <td>
                                <a href="{{ route('admin.vehicles.show', $v) }}" class="btn btn-secondary btn-sm text-xs">
                                    Riwayat Servis
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div>Belum ada data kendaraan.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200">
            {{ $vehicles->appends(request()->query())->links() }}
        </div>
    </div>

</div>
@endsection
