<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - BengkelKita</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="max-w-md w-full">
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 font-black text-2xl text-gray-900">
                <span class="bg-blue-600 text-white w-10 h-10 rounded-lg flex items-center justify-center font-black">BK</span>
                <span>BengkelKita</span>
            </a>
            <p class="text-sm text-gray-600 mt-2">Sistem Operasional Bengkel & Cuci Motor</p>
        </div>

        <div class="bg-white p-6 sm:p-8 border border-gray-200 rounded-xl shadow-sm">
            <h2 class="text-lg font-bold text-gray-800 mb-6 border-b pb-3">Masuk ke Sistem</h2>

            @if(session('success'))
                <div class="p-3 mb-4 text-xs font-medium text-green-800 bg-green-50 rounded-md border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="form-label">Email Staff / Admin</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required class="form-input" placeholder="contoh: admin@bengkelkita.test">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">Kata Sandi</label>
                    <input type="password" name="password" id="password" required class="form-input" placeholder="••••••••">
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 text-gray-600">
                        <input type="checkbox" name="remember" class="rounded border-gray-300">
                        <span>Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full py-2.5 font-semibold">
                    Masuk
                </button>
            </form>

            <!-- Quick Demo Login Account Buttons -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3 text-center">Akun Demo (Klik untuk Isi):</p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillDemo('admin@bengkelkita.test', 'password')" class="text-left p-2 border rounded bg-gray-50 hover:bg-blue-50 hover:border-blue-300 transition text-xs">
                        <div class="font-semibold text-gray-800">Admin / Kasir</div>
                        <div class="text-gray-500 text-[10px]">admin@bengkelkita.test</div>
                    </button>
                    <button type="button" onclick="fillDemo('mekanik@bengkelkita.test', 'password')" class="text-left p-2 border rounded bg-gray-50 hover:bg-blue-50 hover:border-blue-300 transition text-xs">
                        <div class="font-semibold text-gray-800">Mekanik</div>
                        <div class="text-gray-500 text-[10px]">mekanik@bengkelkita.test</div>
                    </button>
                    <button type="button" onclick="fillDemo('kasir@bengkelkita.test', 'password')" class="text-left p-2 border rounded bg-gray-50 hover:bg-blue-50 hover:border-blue-300 transition text-xs">
                        <div class="font-semibold text-gray-800">Kasir</div>
                        <div class="text-gray-500 text-[10px]">kasir@bengkelkita.test</div>
                    </button>
                    <button type="button" onclick="fillDemo('cuci@bengkelkita.test', 'password')" class="text-left p-2 border rounded bg-gray-50 hover:bg-blue-50 hover:border-blue-300 transition text-xs">
                        <div class="font-semibold text-gray-800">Petugas Cuci</div>
                        <div class="text-gray-500 text-[10px]">cuci@bengkelkita.test</div>
                    </button>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-gray-500">
            <a href="{{ route('home') }}" class="hover:underline">← Kembali ke Halaman Utama Bengkel</a>
        </div>
    </div>

    <script>
        function fillDemo(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
