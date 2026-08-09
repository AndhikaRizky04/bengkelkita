<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - BengkelKita</title>

    @if(session('success'))
        <meta name="flash-message" content="{{ session('success') }}" data-type="success">
    @elseif(session('error'))
        <meta name="flash-message" content="{{ session('error') }}" data-type="error">
    @elseif(session('info'))
        <meta name="flash-message" content="{{ session('info') }}" data-type="info">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased" x-data="sidebar">

    <!-- Toast Component -->
    <div x-data="toast" x-show="show" x-cloak class="toast" :class="'toast-' + type" x-transition>
        <span x-text="message"></span>
    </div>

    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="sidebar" :class="{'open': open}">
            <div class="p-4 border-b border-gray-800 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-white font-bold text-lg tracking-wide">
                    <span class="bg-blue-600 text-white w-8 h-8 rounded flex items-center justify-center font-black">BK</span>
                    <span>BengkelKita</span>
                </a>
                <button @click="open = false" class="lg:hidden text-gray-400 hover:text-white">
                    ✕
                </button>
            </div>

            <div class="px-3 py-4 flex flex-col justify-between h-[calc(100vh-65px)] overflow-y-auto">
                <nav class="space-y-1">
                    @if(auth()->user()->isAdmin() || auth()->user()->isKasir())
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Operasional</div>
                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('admin.queues.index') }}" class="sidebar-link {{ request()->routeIs('admin.queues.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Antrean Hari Ini</span>
                        </a>

                        <a href="{{ route('admin.handover.index') }}" class="sidebar-link bg-emerald-950/40 text-emerald-400 hover:bg-emerald-900/50 {{ request()->routeIs('admin.handover.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="font-medium text-emerald-300">Serah Terima Motor</span>
                        </a>

                        <a href="{{ route('admin.service_orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.service_orders.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Pengerjaan Servis</span>
                        </a>

                        <a href="{{ route('admin.wash_orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.wash_orders.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Cuci Motor</span>
                        </a>

                        <div class="px-3 pt-4 pb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Master Data</div>
                        <a href="{{ route('admin.customers.index') }}" class="sidebar-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Pelanggan</span>
                        </a>

                        <a href="{{ route('admin.vehicles.index') }}" class="sidebar-link {{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Kendaraan</span>
                        </a>

                        <a href="{{ route('admin.spareparts.index') }}" class="sidebar-link {{ request()->routeIs('admin.spareparts.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            <span>Stok Sparepart</span>
                        </a>

                        <a href="{{ route('admin.oils.index') }}" class="sidebar-link {{ request()->routeIs('admin.oils.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.595 15.12a2 2 0 00-1.802.738l-1.077 1.436a1 1 0 00.198 1.408l.842.631a1 1 0 001.272-.093l1.107-1.107a4 4 0 012.573-1.127l.55-.055a4 4 0 002.573-1.127l.956-.956a1 1 0 011.414 0l.956.956a4 4 0 002.573 1.127l.55.055a4 4 0 012.573 1.127l1.107 1.107a1 1 0 001.272.093l.842-.631a1 1 0 00.198-1.408l-1.077-1.436z"/></svg>
                            <span>Stok Oli</span>
                        </a>

                        <div class="px-3 pt-4 pb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Keuangan & Laporan</div>
                        <a href="{{ route('admin.invoices.index') }}" class="sidebar-link {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Kasir / Transaksi</span>
                        </a>

                        <a href="{{ route('admin.reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span>Laporan Bengkel</span>
                        </a>
                    @elseif(auth()->user()->isMechanic())
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mekanik</div>
                        <a href="{{ route('mechanic.dashboard') }}" class="sidebar-link {{ request()->routeIs('mechanic.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Pekerjaan Saya</span>
                        </a>
                    @elseif(auth()->user()->isCuci())
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Petugas Cuci</div>
                        <a href="{{ route('washer.dashboard') }}" class="sidebar-link {{ request()->routeIs('washer.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Antrean Cuci</span>
                        </a>
                    @endif
                </nav>

                <!-- User profile footer in sidebar -->
                <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="text-sm font-medium text-white truncate max-w-[130px]">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-gray-400 capitalize">{{ auth()->user()->role?->display_name ?? 'User' }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar" class="text-gray-400 hover:text-red-400 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-100">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button @click="open = !open" class="lg:hidden text-gray-600 hover:text-gray-900 p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-lg font-bold text-gray-800 truncate">@yield('title', 'Dashboard')</h1>
                </div>

                <!-- Global Search Component -->
                @if(auth()->user()->isAdmin() || auth()->user()->isKasir())
                    <div class="relative max-w-md w-full hidden sm:block" x-data="globalSearch">
                        <div class="relative">
                            <input
                                type="text"
                                x-model="query"
                                @input.debounce.300ms="search()"
                                @click.away="close()"
                                placeholder="Cari nama, plat H 1234 ABC, antrean A-001..."
                                class="form-input pl-9 py-1.5 text-xs rounded-lg bg-gray-50 focus:bg-white"
                            >
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>

                        <!-- Dropdown Search Results -->
                        <div x-show="showResults" x-cloak class="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden z-50">
                            <template x-if="loading">
                                <div class="p-3 text-center text-xs text-gray-500">Mencari...</div>
                            </template>

                            <template x-if="!loading && results.length === 0">
                                <div class="p-3 text-center text-xs text-gray-500">Tidak ada data ditemukan.</div>
                            </template>

                            <div class="max-h-64 overflow-y-auto divide-y divide-gray-100">
                                <template x-for="item in results" :key="item.url">
                                    <a :href="item.url" class="block p-2.5 hover:bg-gray-50 transition">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-semibold px-1.5 py-0.5 rounded bg-blue-50 text-blue-700" x-text="item.type"></span>
                                            <span class="text-xs font-bold text-gray-800" x-text="item.title"></span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1 truncate" x-text="item.subtitle"></div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" target="_blank" class="text-xs text-gray-600 hover:text-blue-600 flex items-center gap-1 font-medium bg-gray-100 px-2.5 py-1.5 rounded-md">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Lihat Website</span>
                    </a>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
