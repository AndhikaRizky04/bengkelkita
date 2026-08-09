@extends('layouts.app')

@section('title', 'Tambah Pelanggan')

@section('content')
<div class="max-w-2xl space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.customers.index') }}" class="text-gray-400 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Tambah Pelanggan</h1>
            <p class="text-xs text-gray-500">Daftarkan pelanggan baru</p>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
        <form method="POST" action="{{ route('admin.customers.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="form-label text-xs">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="form-input text-sm">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs">No. WhatsApp / HP <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required class="form-input text-sm" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="form-label text-xs">Email (opsional)</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input text-sm">
                </div>
            </div>
            <div>
                <label class="form-label text-xs">Alamat (opsional)</label>
                <textarea name="address" rows="2" class="form-textarea text-sm">{{ old('address') }}</textarea>
            </div>
            <div>
                <label class="form-label text-xs">Catatan (opsional)</label>
                <textarea name="notes" rows="2" class="form-textarea text-sm">{{ old('notes') }}</textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary text-xs py-2">Batal</a>
                <button type="submit" class="btn btn-primary text-xs py-2">Simpan Pelanggan</button>
            </div>
        </form>
    </div>
</div>
@endsection
