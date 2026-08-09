@extends('layouts.app')

@section('title', 'Tambah Antrean Baru')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Pendaftaran Antrean Baru</h1>
            <p class="text-xs text-gray-500">Pilih pelanggan dan kendaraan untuk menerbitkan nomor antrean hari ini</p>
        </div>
        <a href="{{ route('admin.queues.index') }}" class="btn btn-secondary text-xs">
            ← Kembali ke Antrean
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm" x-data="createQueueForm()">
        <form method="POST" action="{{ route('admin.queues.store') }}" class="space-y-5">
            @csrf

            <!-- Select Customer -->
            <div>
                <label class="form-label font-bold text-xs">Pilih Pelanggan *</label>
                <select name="customer_id" x-model="customerId" @change="onCustomerChange()" required class="form-select text-xs">
                    <option value="">-- Pilih Pelanggan --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} (WA: {{ $c->phone }})</option>
                    @endforeach
                </select>
                <div class="text-[11px] text-gray-500 mt-1">Belum ada di list? <a href="{{ route('admin.customers.create') }}" class="text-blue-600 font-semibold hover:underline">+ Tambah Pelanggan Baru</a></div>
            </div>

            <!-- Select Vehicle -->
            <div>
                <label class="form-label font-bold text-xs">Pilih Kendaraan *</label>
                <select name="vehicle_id" x-model="vehicleId" required class="form-select text-xs">
                    <option value="">-- Pilih Kendaraan --</option>
                    <template x-for="v in filteredVehicles" :key="v.id">
                        <option :value="v.id" x-text="v.license_plate + ' - ' + v.brand_name + ' ' + v.model_name"></option>
                    </template>
                </select>
            </div>

            <!-- Service Type -->
            <div>
                <label class="form-label font-bold text-xs">Jenis Layanan *</label>
                <select name="service_type" x-model="serviceType" required class="form-select text-xs">
                    <option value="servis">Servis Motor Sahaja</option>
                    <option value="cuci">Cuci Motor Sahaja</option>
                    <option value="servis_cuci">Servis + Cuci Motor (Rekomendasi)</option>
                </select>
            </div>

            <!-- Wash Package Selection if wash included -->
            <div x-show="serviceType === 'cuci' || serviceType === 'servis_cuci'" x-cloak>
                <label class="form-label font-bold text-xs">Paket Cuci Motor</label>
                <select name="wash_package_id" class="form-select text-xs">
                    <option value="">-- Pilih Paket Cuci --</option>
                    @foreach($washPackages as $wp)
                        <option value="{{ $wp->id }}">{{ $wp->name }} (Rp{{ number_format($wp->price, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Complaint -->
            <div>
                <label class="form-label font-bold text-xs">Keluhan / Catatan Kendaraan *</label>
                <textarea name="complaint" rows="3" required class="form-input text-xs" placeholder="Misal: Tarikan gredeg saat awal, ganti oli mesin & oli gardan..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-full py-2.5 font-bold text-sm">
                Terbitkan Nomor Antrean
            </button>
        </form>
    </div>

</div>

@push('scripts')
<script>
    function createQueueForm() {
        const allCustomers = @json($customers->load('vehicles.vehicleBrand', 'vehicles.vehicleModel'));
        return {
            customerId: '',
            vehicleId: '',
            serviceType: 'servis',
            filteredVehicles: [],
            onCustomerChange() {
                this.vehicleId = '';
                const found = allCustomers.find(c => c.id == this.customerId);
                if (found && found.vehicles) {
                    this.filteredVehicles = found.vehicles.map(v => ({
                        id: v.id,
                        license_plate: v.license_plate,
                        brand_name: v.vehicle_brand ? v.vehicle_brand.name : '',
                        model_name: v.vehicle_model ? v.vehicle_model.name : '',
                    }));
                } else {
                    this.filteredVehicles = [];
                }
            }
        }
    }
</script>
@endpush
@endsection
