@extends('layouts.public')

@section('title', 'BengkelKita - Servis Motor & Cuci Motor Semarang')

@section('content')
<!-- Hero Section -->
<section class="bg-gray-900 text-white py-16 lg:py-24 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Hero Text -->
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-900/60 border border-blue-700 text-blue-300 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    <span>Bengkel Buka Hari Ini • 08:00 - 17:00 WIB</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Servis Motor Lebih Mudah.
                </h1>

                <p class="text-base sm:text-lg text-gray-300 max-w-2xl leading-relaxed">
                    Daftar servis, pantau pengerjaan motor secara langsung, dan ambil kendaraan tanpa harus menunggu lama di bengkel.
                </p>

                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="#daftar" class="btn btn-primary btn-lg font-semibold text-sm">
                        Servis Sekarang
                    </a>
                    <a href="#cuci" class="btn btn-secondary btn-lg font-semibold text-sm bg-gray-800 text-white border-gray-700 hover:bg-gray-700">
                        Layanan Cuci Motor
                    </a>
                </div>

                <!-- Quick Features List -->
                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-gray-800 text-xs text-gray-400">
                    <div>
                        <div class="text-white font-bold text-base">A-001</div>
                        <div>Antrean Otomatis</div>
                    </div>
                    <div>
                        <div class="text-white font-bold text-base">Checklist</div>
                        <div>Inspeksi Transparan</div>
                    </div>
                    <div>
                        <div class="text-white font-bold text-base">Live Status</div>
                        <div>Pantau via HP</div>
                    </div>
                </div>
            </div>

            <!-- Quick Registration Card -->
            <div class="lg:col-span-5 bg-white text-gray-900 rounded-xl p-6 sm:p-8 shadow-xl border border-gray-200" id="daftar">
                <div class="flex items-center justify-between border-b pb-4 mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Pendaftaran Servis & Cuci</h2>
                        <p class="text-xs text-gray-500">Dapatkan nomor antrean langsung</p>
                    </div>
                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-1 rounded">Online</span>
                </div>

                @if($errors->any())
                    <div class="p-3 mb-4 text-xs bg-red-50 border border-red-200 text-red-700 rounded-md">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('public.register_service') }}" class="space-y-4" x-data="registerForm()">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label text-xs">Nama Lengkap *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="form-input text-xs" placeholder="Andhika Rizky">
                        </div>
                        <div>
                            <label class="form-label text-xs">Nomor WhatsApp *</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required class="form-input text-xs" placeholder="081234567890">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label text-xs">Nomor Polisi (Plat) *</label>
                            <input type="text" name="license_plate" value="{{ old('license_plate') }}" required class="form-input text-xs uppercase" placeholder="H 1234 ABC">
                        </div>
                        <div>
                            <label class="form-label text-xs">Kilometer Saat Ini *</label>
                            <input type="number" name="kilometer" value="{{ old('kilometer') }}" required class="form-input text-xs" placeholder="12450">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label text-xs">Merk Motor *</label>
                            <select name="vehicle_brand_id" x-model="selectedBrand" @change="fetchModels()" required class="form-select text-xs">
                                <option value="">-- Pilih Merk --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs">Tipe Motor *</label>
                            <select name="vehicle_model_id" x-model="selectedModel" required class="form-select text-xs" :disabled="models.length === 0">
                                <option value="">-- Pilih Tipe --</option>
                                <template x-for="m in models" :key="m.id">
                                    <option :value="m.id" x-text="m.name + ' (' + m.type + ')'"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label text-xs">Pilih Jenis Layanan *</label>
                        <select name="service_type" x-model="serviceType" required class="form-select text-xs">
                            <option value="servis">Servis Motor Sahaja</option>
                            <option value="cuci">Cuci Motor Sahaja</option>
                            <option value="servis_cuci">Servis + Cuci Motor (Rekomendasi)</option>
                        </select>
                    </div>

                    <div x-show="serviceType === 'cuci' || serviceType === 'servis_cuci'" x-cloak>
                        <label class="form-label text-xs">Pilih Paket Cuci Motor</label>
                        <select name="wash_package_id" class="form-select text-xs">
                            <option value="">-- Pilih Paket Cuci --</option>
                            @foreach($washPackages as $wp)
                                <option value="{{ $wp->id }}">{{ $wp->name }} - Rp{{ number_format($wp->price, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label text-xs">Keluhan / Catatan Kendaraan *</label>
                        <textarea name="complaint" rows="2" required class="form-input text-xs" placeholder="Contoh: Motor terasa bergetar saat kecepatan 40-60 km/jam dan suara mesin agak kasar."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-full py-2.5 font-bold text-sm">
                        Dapatkan Nomor Antrean Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Services & Pricing Section -->
<section class="py-16 bg-white" id="layanan">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Layanan Servis Popular</h2>
            <p class="text-sm text-gray-600 mt-2">Daftar tarif jasa servis transparan tanpa biaya tersembunyi</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($services as $svc)
                <div class="border border-gray-200 rounded-lg p-6 hover:border-blue-500 transition shadow-sm bg-white">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-blue-50 text-blue-700">
                            {{ $svc->serviceCategory?->name ?? 'Servis' }}
                        </span>
                        <span class="text-xs text-gray-500 font-medium">± {{ $svc->estimated_duration_minutes }} Menit</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">{{ $svc->name }}</h3>
                    <p class="text-xs text-gray-600 mb-4 min-h-[36px] line-clamp-2">{{ $svc->description }}</p>
                    <div class="border-t pt-3 flex items-center justify-between">
                        <span class="text-xs text-gray-500">Biaya Jasa</span>
                        <span class="text-lg font-extrabold text-blue-600">Rp{{ number_format($svc->price, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Wash Packages Section -->
<section class="py-16 bg-gray-50 border-t border-b border-gray-200" id="cuci">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Layanan Cuci Motor</h2>
            <p class="text-sm text-gray-600 mt-2">Cuci bersih hingga sela mesin menggunakan sampo khusus dan pengeringan lap microfiber</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($washPackages as $wp)
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1">Paket Cuci</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $wp->name }}</h3>
                        <p class="text-xs text-gray-600 mb-4 leading-relaxed">{{ $wp->description }}</p>
                    </div>
                    <div class="border-t pt-4">
                        <div class="text-2xl font-black text-gray-900">Rp{{ number_format($wp->price, 0, ',', '.') }}</div>
                        <div class="text-xs text-gray-500 mt-1">Estimasi waktu: {{ $wp->estimated_duration_minutes }} menit</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-2xl font-bold text-gray-900">Mengapa Memilih BengkelKita?</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-6 bg-gray-50 rounded-lg border border-gray-200">
                <div class="w-10 h-10 rounded-md bg-blue-600 text-white flex items-center justify-center font-bold text-lg mb-4">1</div>
                <h3 class="font-bold text-gray-900 mb-2">Checklist Inspeksi Lengkap</h3>
                <p class="text-xs text-gray-600 leading-relaxed">Mekanik memeriksa mesin, rem, ban, kelistrikan, dan CVT dengan checklist transparan sebelum pengerjaan.</p>
            </div>
            <div class="p-6 bg-gray-50 rounded-lg border border-gray-200">
                <div class="w-10 h-10 rounded-md bg-blue-600 text-white flex items-center justify-center font-bold text-lg mb-4">2</div>
                <h3 class="font-bold text-gray-900 mb-2">Pantau Status Realtime</h3>
                <p class="text-xs text-gray-600 leading-relaxed">Pantau progress pengerjaan dari HP tanpa perlu nongkrong lama di ruang tunggu bengkel.</p>
            </div>
            <div class="p-6 bg-gray-50 rounded-lg border border-gray-200">
                <div class="w-10 h-10 rounded-md bg-blue-600 text-white flex items-center justify-center font-bold text-lg mb-4">3</div>
                <h3 class="font-bold text-gray-900 mb-2">Sparepart & Oli Original</h3>
                <p class="text-xs text-gray-600 leading-relaxed">Hanya menggunakan produk oli resmi (Pertamina, Shell, Motul, AHM, Yamalube) dan sparepart presisi.</p>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    function registerForm() {
        return {
            selectedBrand: '{{ old('vehicle_brand_id') }}',
            selectedModel: '{{ old('vehicle_model_id') }}',
            serviceType: '{{ old('service_type', 'servis') }}',
            models: [],
            async fetchModels() {
                if (!this.selectedBrand) {
                    this.models = [];
                    return;
                }
                try {
                    const res = await fetch(`/api/brands/${this.selectedBrand}/models`);
                    this.models = await res.json();
                } catch(e) {
                    this.models = [];
                }
            },
            init() {
                if (this.selectedBrand) {
                    this.fetchModels();
                }
            }
        }
    }
</script>
@endpush
@endsection
