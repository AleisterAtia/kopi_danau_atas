<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 | {{ config('app.name', 'Kopi Danau Diatas') }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #FDFBF7;
            color: #1E293B;
        }
        .card { max-width: 26rem; text-align: center; }
        .brand { font-weight: 800; color: #1B4332; letter-spacing: -0.02em; margin-bottom: 2.5rem; font-size: 1.1rem; }
        .code { font-size: 4rem; font-weight: 800; color: rgba(27,67,50,0.15); margin: 0 0 0.5rem; letter-spacing: -0.02em; }
        h1 { font-size: 1.4rem; font-weight: 700; margin: 0 0 0.75rem; color: #1E293B; }
        p { color: #64748B; line-height: 1.7; margin: 0; }
    </style>
</head>
<body>
    <div class="card">
        <p class="brand">Kopi Danau Diatas</p>
        <p class="code">503</p>
        <h1>{{ __('Sedang Pemeliharaan') }}</h1>
        <p>{{ __('Kami sedang melakukan pemeliharaan singkat untuk meningkatkan layanan. Mohon coba kembali beberapa saat lagi.') }}</p>
    </div>
</body>
</html>
