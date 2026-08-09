@extends('layouts.app')

@section('title', 'Detail Pekerjaan ' . $serviceOrder->order_number)

@section('content')
@php
    $soStatus = [
        'pending'       => ['label' => 'Menunggu',           'badge' => 'bg-gray-100 text-gray-700'],
        'inspection'    => ['label' => 'Pemeriksaan',        'badge' => 'bg-yellow-100 text-yellow-800'],
        'in_progress'   => ['label' => 'Pengerjaan',         'badge' => 'bg-blue-100 text-blue-800'],
        'waiting_parts' => ['label' => 'Menunggu Sparepart', 'badge' => 'bg-orange-100 text-orange-800'],
        'final_check'   => ['label' => 'Pemeriksaan Akhir',  'badge' => 'bg-purple-100 text-purple-800'],
        'ready'         => ['label' => 'Siap Diambil',       'badge' => 'bg-teal-100 text-teal-800'],
        'completed'     => ['label' => 'Selesai',            'badge' => 'bg-green-100 text-green-800'],
    ];
    $cfg = $soStatus[$serviceOrder->status] ?? $soStatus['pending'];
    $condLabels = [
        'baik' => ['Baik', 'text-green-700 bg-green-50'],
        'perlu_diperiksa' => ['Perlu Diperiksa', 'text-yellow-700 bg-yellow-50'],
        'perlu_diganti' => ['Perlu Diganti', 'text-red-700 bg-red-50'],
    ];
@endphp

<div class="space-y-6" x-data="{ tab: 'pemeriksaan' }">

    <!-- Header -->
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('mechanic.dashboard') }}" class="text-gray-400 hover:text-gray-700 mt-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-extrabold text-gray-900 font-mono">{{ $serviceOrder->order_number }}</h1>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $cfg['badge'] }}">{{ $cfg['label'] }}</span>
                </div>
                <p class="text-xs text-gray-500">Antrean {{ $serviceOrder->queue?->queue_number ?? '-' }}</p>
            </div>
        </div>

        <!-- Status action buttons -->
        <div class="flex flex-wrap items-center gap-2">
            @if($serviceOrder->status === 'pending')
                <form method="POST" action="{{ route('mechanic.start_inspection', $serviceOrder) }}">
                    @csrf
                    <button class="btn btn-primary text-xs py-1.5">Mulai Pemeriksaan</button>
                </form>
            @endif
            @if(in_array($serviceOrder->status, ['inspection', 'waiting_parts']))
                <form method="POST" action="{{ route('mechanic.start_work', $serviceOrder) }}">
                    @csrf
                    <button class="btn btn-primary text-xs py-1.5">Mulai Pengerjaan</button>
                </form>
            @endif
            @if($serviceOrder->status === 'in_progress')
                <button type="button" class="btn btn-success text-xs py-1.5" onclick="document.getElementById('complete-modal').classList.remove('hidden')">Selesaikan Pekerjaan</button>
            @endif
        </div>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
            <div class="text-[11px] text-gray-400 uppercase font-semibold mb-1">Kendaraan</div>
            <div class="font-bold text-gray-900 text-sm">{{ $serviceOrder->vehicle?->full_name }}</div>
            <div class="text-xs font-mono text-gray-500">{{ $serviceOrder->vehicle?->license_plate }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
            <div class="text-[11px] text-gray-400 uppercase font-semibold mb-1">Pelanggan</div>
            <div class="font-bold text-gray-900 text-sm">{{ $serviceOrder->customer?->name ?? '-' }}</div>
            <div class="text-xs text-gray-500">{{ $serviceOrder->customer?->phone ?? '' }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
            <div class="text-[11px] text-gray-400 uppercase font-semibold mb-1">Keluhan Pelanggan</div>
            <div class="text-xs text-gray-700">{{ $serviceOrder->complaint ?: 'Tidak ada keluhan dicatat.' }}</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="border-b border-gray-200 flex gap-1 px-3 pt-2 overflow-x-auto">
            <button @click="tab='pemeriksaan'" :class="tab==='pemeriksaan' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-800'" class="px-3 py-2 text-xs font-semibold border-b-2 whitespace-nowrap">Pemeriksaan</button>
            <button @click="tab='rekomendasi'" :class="tab==='rekomendasi' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-800'" class="px-3 py-2 text-xs font-semibold border-b-2 whitespace-nowrap">Rekomendasi Servis</button>
            <button @click="tab='riwayat'" :class="tab==='riwayat' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-800'" class="px-3 py-2 text-xs font-semibold border-b-2 whitespace-nowrap">Hasil Pemeriksaan</button>
        </div>

        <!-- Tab: Form Pemeriksaan -->
        <div x-show="tab==='pemeriksaan'" class="p-4">
            @if($serviceOrder->status === 'pending')
                <div class="text-center text-sm text-gray-500 py-6">
                    Klik <span class="font-semibold">Mulai Pemeriksaan</span> di atas untuk mengaktifkan checklist.
                </div>
            @else
                <form method="POST" action="{{ route('mechanic.store_inspection', $serviceOrder) }}" x-data="{ rows: [] }">
                    @csrf
                    <p class="text-xs text-gray-500 mb-4">Isi checklist kondisi komponen kendaraan. Item dengan kondisi <span class="font-semibold text-red-600">Perlu Diganti</span> otomatis menjadi draft rekomendasi.</p>

                    @if($inspectionCategories->isEmpty())
                        <div class="text-xs text-gray-500 mb-4 p-3 bg-gray-50 rounded">Belum ada template pemeriksaan. Tambahkan item manual di bawah.</div>
                    @else
                        @foreach($inspectionCategories as $category)
                            <div class="mb-4">
                                <div class="text-xs font-bold text-gray-700 uppercase mb-2">{{ $category->name }}</div>
                                <div class="space-y-2">
                                    @foreach($category->inspectionTemplates as $tpl)
                                        <div class="grid grid-cols-12 gap-2 items-center">
                                            <div class="col-span-12 sm:col-span-5 text-xs font-medium text-gray-800">
                                                {{ $tpl->name }}
                                                <input type="hidden" name="items[{{ $tpl->id }}][name]" value="{{ $tpl->name }}">
                                                <input type="hidden" name="items[{{ $tpl->id }}][category]" value="{{ $category->name }}">
                                            </div>
                                            <div class="col-span-6 sm:col-span-3">
                                                <select name="items[{{ $tpl->id }}][condition]" class="form-select text-xs py-1.5">
                                                    <option value="baik">Baik</option>
                                                    <option value="perlu_diperiksa">Perlu Diperiksa</option>
                                                    <option value="perlu_diganti">Perlu Diganti</option>
                                                </select>
                                            </div>
                                            <div class="col-span-6 sm:col-span-4">
                                                <input type="text" name="items[{{ $tpl->id }}][notes]" placeholder="Catatan..." class="form-input text-xs py-1.5">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <div class="mt-4">
                        <label class="form-label text-xs">Catatan Umum Pemeriksaan</label>
                        <textarea name="general_notes" rows="2" class="form-textarea text-xs" placeholder="Catatan tambahan hasil pemeriksaan..."></textarea>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="btn btn-primary text-xs py-2">Simpan Hasil Pemeriksaan</button>
                    </div>
                </form>
            @endif
        </div>

        <!-- Tab: Rekomendasi -->
        <div x-show="tab==='rekomendasi'" x-cloak class="p-4">
            <form method="POST" action="{{ route('mechanic.add_recommendation', $serviceOrder) }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end mb-5 p-3 bg-gray-50 rounded-lg">
                @csrf
                <div class="md:col-span-4">
                    <label class="form-label text-xs">Deskripsi Rekomendasi</label>
                    <input type="text" name="description" required class="form-input text-xs py-1.5" placeholder="Ganti kampas rem depan">
                </div>
                <div class="md:col-span-3">
                    <label class="form-label text-xs">Sparepart (opsional)</label>
                    <select name="sparepart_id" class="form-select text-xs py-1.5">
                        <option value="">- Tanpa Sparepart -</option>
                        @foreach($spareparts as $sp)
                            <option value="{{ $sp->id }}">{{ $sp->name }} (Rp {{ number_format($sp->selling_price, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label text-xs">Harga Jasa</label>
                    <input type="number" name="service_price" min="0" value="0" class="form-input text-xs py-1.5">
                </div>
                <div class="md:col-span-2">
                    <label class="form-label text-xs">Harga Part</label>
                    <input type="number" name="sparepart_price" min="0" placeholder="Otomatis" class="form-input text-xs py-1.5">
                </div>
                <div class="md:col-span-1">
                    <button type="submit" class="btn btn-primary text-xs py-1.5 w-full">+ Tambah</button>
                </div>
            </form>

            @if($serviceOrder->serviceRecommendations->isEmpty())
                <div class="text-center text-sm text-gray-500 py-6">Belum ada rekomendasi servis.</div>
            @else
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Deskripsi</th>
                                <th>Sparepart</th>
                                <th>Harga Part</th>
                                <th>Harga Jasa</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serviceOrder->serviceRecommendations as $rec)
                                <tr>
                                    <td class="text-xs font-medium">{{ $rec->description }}</td>
                                    <td class="text-xs">{{ $rec->sparepart?->name ?? '-' }}</td>
                                    <td class="text-xs">Rp {{ number_format($rec->sparepart_price ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-xs">Rp {{ number_format($rec->service_price ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-xs font-semibold">Rp {{ number_format($rec->total_price ?? 0, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full
                                            {{ $rec->status === 'approved' ? 'bg-green-100 text-green-800' : ($rec->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst($rec->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Tab: Hasil Pemeriksaan -->
        <div x-show="tab==='riwayat'" x-cloak class="p-4">
            @if($serviceOrder->inspections->isEmpty())
                <div class="text-center text-sm text-gray-500 py-6">Belum ada hasil pemeriksaan tersimpan.</div>
            @else
                @foreach($serviceOrder->inspections as $insp)
                    <div class="mb-5 border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 flex items-center justify-between">
                            <div class="text-xs font-semibold text-gray-700">Pemeriksaan #{{ $insp->id }}</div>
                            <div class="text-[11px] text-gray-500">{{ $insp->inspected_at?->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Komponen</th>
                                        <th>Kategori</th>
                                        <th>Kondisi</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($insp->inspectionItems as $item)
                                        @php $c = $condLabels[$item->condition] ?? [$item->condition, 'text-gray-700 bg-gray-50']; @endphp
                                        <tr>
                                            <td class="text-xs font-medium">{{ $item->name }}</td>
                                            <td class="text-xs text-gray-500">{{ $item->category }}</td>
                                            <td><span class="text-[11px] font-semibold px-2 py-0.5 rounded {{ $c[1] }}">{{ $c[0] }}</span></td>
                                            <td class="text-xs text-gray-500">{{ $item->notes ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($insp->notes)
                            <div class="px-4 py-2 text-xs text-gray-600 bg-gray-50 border-t border-gray-100">
                                <span class="font-semibold">Catatan:</span> {{ $insp->notes }}
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<!-- Complete Work Modal -->
<div id="complete-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 text-sm">Selesaikan Pekerjaan</h3>
            <button type="button" onclick="document.getElementById('complete-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700">✕</button>
        </div>
        <form method="POST" action="{{ route('mechanic.complete_work', $serviceOrder) }}">
            @csrf
            <div class="p-5 space-y-4">
                <div>
                    <label class="form-label text-xs">Kilometer Keluar (opsional)</label>
                    <input type="number" name="kilometer_out" min="0" class="form-input text-xs py-1.5" placeholder="{{ $serviceOrder->kilometer_in }}">
                </div>
                <div>
                    <label class="form-label text-xs">Catatan Mekanik</label>
                    <textarea name="mechanic_notes" rows="3" class="form-textarea text-xs" placeholder="Ringkasan pengerjaan yang dilakukan...">{{ $serviceOrder->mechanic_notes }}</textarea>
                </div>
            </div>
            <div class="px-5 py-3 border-t border-gray-200 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('complete-modal').classList.add('hidden')" class="btn btn-secondary text-xs py-1.5">Batal</button>
                <button type="submit" class="btn btn-success text-xs py-1.5">Selesaikan</button>
            </div>
        </form>
    </div>
</div>
@endsection
