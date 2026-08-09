<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BengkelKita - Servis Motor & Cuci Motor Semarang')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">

    <!-- Header Navigation -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-black text-xl text-gray-900 tracking-tight">
                <span class="bg-blue-600 text-white w-9 h-9 rounded-md flex items-center justify-center font-black">BK</span>
                <span>BengkelKita</span>
            </a>

            <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
                <a href="{{ route('home') }}#layanan" class="hover:text-blue-600 transition">Layanan & Harga</a>
                <a href="{{ route('home') }}#cuci" class="hover:text-blue-600 transition">Cuci Motor</a>
                <a href="{{ route('home') }}#daftar" class="hover:text-blue-600 transition">Daftar Servis</a>
                <a href="{{ route('public.status') }}" class="text-blue-600 font-semibold hover:underline">Cek Status Antrean</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="{{ route('public.status') }}" class="btn btn-secondary btn-sm md:hidden text-xs">Cek Status</a>
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm text-xs font-semibold">
                    Masuk Staff / Admin
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <div class="flex items-center gap-2 text-white font-bold text-lg mb-3">
                    <span class="bg-blue-600 text-white w-7 h-7 rounded flex items-center justify-center font-black text-xs">BK</span>
                    <span>BengkelKita</span>
                </div>
                <p class="text-sm text-gray-400 leading-relaxed">
                    Sistem operasional bengkel dan cuci motor terpercaya. Memberikan pelayanan transparan, sparepart berkualitas, dan pengerjaan mekanik berpengalaman.
                </p>
            </div>

            <div>
                <h4 class="text-white font-semibold text-sm mb-3">Jam Operasional & Alamat</h4>
                <p class="text-sm text-gray-400 mb-1">📍 Jl. Merdeka No. 45, Semarang, Jawa Tengah</p>
                <p class="text-sm text-gray-400 mb-1">🕒 Senin - Sabtu: 08:00 - 17:00 WIB</p>
                <p class="text-sm text-gray-400">📱 WhatsApp: 0812-3456-7890</p>
            </div>

            <div>
                <h4 class="text-white font-semibold text-sm mb-3">Pintasan Informasi</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('public.status') }}" class="hover:text-white transition">Cek Progress Antrean</a></li>
                    <li><a href="{{ route('home') }}#daftar" class="hover:text-white transition">Daftar Servis Online</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Portal Staff Bengkel</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 mt-8 border-t border-gray-800 text-center text-xs text-gray-500">
            &copy; {{ date('Y') }} BengkelKita Motor & Cuci. Hak Cipta Dilindungi.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
