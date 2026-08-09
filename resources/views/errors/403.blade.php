<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak - BengkelKita</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; color: #111827; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 1rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,.08); max-width: 30rem; width: 100%; padding: 2.5rem; text-align: center; }
        .badge { display: inline-flex; align-items: center; justify-content: center; width: 4rem; height: 4rem; border-radius: 9999px; background: #fef2f2; color: #dc2626; font-size: 1.5rem; font-weight: 800; margin-bottom: 1.25rem; }
        h1 { font-size: 1.25rem; font-weight: 800; margin-bottom: .5rem; }
        p { font-size: .875rem; color: #6b7280; margin-bottom: 1.75rem; line-height: 1.5; }
        .actions { display: flex; flex-direction: column; gap: .625rem; }
        .btn { display: block; width: 100%; padding: .625rem 1rem; border-radius: .5rem; font-size: .8125rem; font-weight: 600; text-decoration: none; cursor: pointer; border: none; transition: background .15s ease; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-ghost { background: #f3f4f6; color: #374151; }
        .btn-ghost:hover { background: #e5e7eb; }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">403</div>
        <h1>Akses Ditolak</h1>
        <p>{{ $exception?->getMessage() ?: 'Anda tidak memiliki akses ke halaman ini.' }}</p>

        <div class="actions">
            @auth
                <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="btn btn-primary">Kembali ke Dashboard Saya</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost">Keluar / Ganti Akun</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">Masuk</a>
                <a href="{{ route('home') }}" class="btn btn-ghost">Ke Halaman Utama</a>
            @endauth
        </div>
    </div>
</body>
</html>
