@extends('layouts.app')

@section('title', 'Data Pelanggan Bengkel')

@section('content')
<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Data Pelanggan</h1>
            <p class="text-xs text-gray-500">Kelola informasi kontak dan daftar kendaraan milik pelanggan</p>
        </div>

        <button onclick="document.getElementById('modalAddCustomer').classList.remove('hidden')" class="btn btn-primary font-semibold text-xs">
            + Tambah Pelanggan Baru
        </button>
    </div>

    <!-- Search bar -->
    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, nomor WhatsApp, email..." class="form-input text-xs">
            <button type="submit" class="btn btn-secondary text-xs">Cari</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>Nomor WhatsApp</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th>Jumlah Motor</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $c)
                        <tr>
                            <td class="font-bold text-gray-900 text-xs">{{ $c->name }}</td>
                            <td class="font-mono text-blue-700 text-xs">{{ $c->phone }}</td>
                            <td class="text-xs text-gray-500">{{ $c->email ?? '-' }}</td>
                            <td class="text-xs text-gray-600 truncate max-w-xs">{{ $c->address ?? '-' }}</td>
                            <td>
                                <span class="badge bg-blue-100 text-blue-800 font-bold text-xs">
                                    {{ $c->vehicles_count }} Kendaraan
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.customers.show', $c) }}" class="btn btn-secondary btn-sm text-xs">
                                    Detail & Motor
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div>Belum ada data pelanggan.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200">
            {{ $customers->appends(request()->query())->links() }}
        </div>
    </div>

</div>

<!-- Modal Add Customer -->
<div id="modalAddCustomer" class="hidden modal-overlay">
    <div class="modal-content">
        <div class="modal-header flex justify-between items-center">
            <h3 class="font-bold text-gray-900 text-sm">Tambah Pelanggan Baru</h3>
            <button onclick="document.getElementById('modalAddCustomer').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.customers.store') }}">
            @csrf
            <div class="modal-body space-y-4 text-xs">
                <div>
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="name" required class="form-input text-xs" placeholder="Andhika Rizky">
                </div>
                <div>
                    <label class="form-label">Nomor WhatsApp *</label>
                    <input type="text" name="phone" required class="form-input text-xs" placeholder="081234567890">
                </div>
                <div>
                    <label class="form-label">Email (Opsional)</label>
                    <input type="email" name="email" class="form-input text-xs" placeholder="andhika@gmail.com">
                </div>
                <div>
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea name="address" rows="2" class="form-input text-xs" placeholder="Jl. Pemuda No. 12, Semarang"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('modalAddCustomer').classList.add('hidden')" class="btn btn-secondary text-xs">Batal</button>
                <button type="submit" class="btn btn-primary text-xs font-bold">Simpan Pelanggan</button>
            </div>
        </form>
    </div>
</div>
@endsection
