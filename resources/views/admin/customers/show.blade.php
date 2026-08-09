@extends('layouts.app')

@section('title', 'Detail Pelanggan - ' . $customer->name)

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.customers.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">← Pelanggan</a>
                <span class="text-gray-400">/</span>
                <span class="text-xs font-bold text-gray-700">Detail</span>
            </div>
            <h1 class="text-xl font-extrabold text-gray-900 mt-1">{{ $customer->name }}</h1>
        </div>

        <button onclick="document.getElementById('modalAddVehicle').classList.remove('hidden')" class="btn btn-primary font-semibold text-xs">
            + Tambah Kendaraan Motor
        </button>
    </div>

    <!-- Customer info card -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nama Pelanggan</div>
            <div class="text-base font-bold text-gray-900 mt-1">{{ $customer->name }}</div>
        </div>

        <div>
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nomor WhatsApp</div>
            <div class="text-base font-bold text-blue-700 font-mono mt-1">{{ $customer->phone }}</div>
        </div>

        <div>
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Alamat</div>
            <div class="text-xs text-gray-700 mt-1">{{ $customer->address ?? '-' }}</div>
        </div>
    </div>

    <!-- Vehicles List Section -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <h2 class="text-base font-bold text-gray-900 mb-4">Daftar Kendaraan Motor ({{ $customer->vehicles->count() }})</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($customer->vehicles as $v)
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 flex items-center justify-between">
                    <div>
                        <div class="text-lg font-black text-gray-900 font-mono">{{ $v->license_plate }}</div>
                        <div class="text-xs font-bold text-blue-700">{{ $v->full_name }} ({{ $v->year }})</div>
                        <div class="text-[11px] text-gray-500 mt-1">KM Terakhir: {{ number_format($v->last_kilometer, 0, ',', '.') }} KM</div>
                    </div>
                    <div>
                        <a href="{{ route('admin.vehicles.show', $v) }}" class="btn btn-secondary btn-sm text-xs">
                            Riwayat Servis →
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-2 text-center text-xs text-gray-500 py-6">
                    Pelanggan ini belum memiliki kendaraan terdaftar.
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Modal Add Vehicle for Customer -->
<div id="modalAddVehicle" class="hidden modal-overlay" x-data="addVehicleForm()">
    <div class="modal-content">
        <div class="modal-header flex justify-between items-center">
            <h3 class="font-bold text-gray-900 text-sm">Tambah Kendaraan Motor Baru</h3>
            <button onclick="document.getElementById('modalAddVehicle').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.vehicles.store') }}">
            @csrf
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">

            <div class="modal-body space-y-4 text-xs">
                <div>
                    <label class="form-label">Nomor Polisi (Plat) *</label>
                    <input type="text" name="license_plate" required class="form-input text-xs uppercase" placeholder="H 1234 ABC">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Merk Motor *</label>
                        <select name="vehicle_brand_id" x-model="selectedBrand" @change="fetchModels()" required class="form-select text-xs">
                            <option value="">-- Pilih Merk --</option>
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tipe Motor *</label>
                        <select name="vehicle_model_id" x-model="selectedModel" required class="form-select text-xs" :disabled="models.length === 0">
                            <option value="">-- Pilih Tipe --</option>
                            <template x-for="m in models" :key="m.id">
                                <option :value="m.id" x-text="m.name + ' (' + m.type + ')'"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Tahun Pembuatan</label>
                        <input type="number" name="year" value="{{ date('Y') }}" class="form-input text-xs">
                    </div>
                    <div>
                        <label class="form-label">Kilometer Terakhir</label>
                        <input type="number" name="last_kilometer" value="0" class="form-input text-xs">
                    </div>
                </div>

                <div>
                    <label class="form-label">Warna Kendaraan</label>
                    <input type="text" name="color" class="form-input text-xs" placeholder="Hitam">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('modalAddVehicle').classList.add('hidden')" class="btn btn-secondary text-xs">Batal</button>
                <button type="submit" class="btn btn-primary text-xs font-bold">Simpan Kendaraan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function addVehicleForm() {
        return {
            selectedBrand: '',
            selectedModel: '',
            models: [],
            async fetchModels() {
                if (!this.selectedBrand) {
                    this.models = [];
                    return;
                }
                const res = await fetch(`/api/brands/${this.selectedBrand}/models`);
                this.models = await res.json();
            }
        }
    }
</script>
@endpush
@endsection
