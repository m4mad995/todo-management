<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Second Brain</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script>
        (function() {
            var theme = localStorage.getItem('theme') || 'system';
            if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', 'Figtree', system-ui, sans-serif;
            background-color: #F8FAFC;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }
        .dark body { background-color: #0F172A; color: #F1F5F9; }
        .container { text-align: center; max-width: 28rem; }
        .logo {
            width: 3.5rem; height: 3.5rem; border-radius: 12px;
            background-color: #2563EB; display: inline-flex;
            align-items: center; justify-content: center; margin-bottom: 1.5rem;
        }
        .logo svg { width: 1.5rem; height: 1.5rem; color: white; }
        .brand { font-size: 1.125rem; font-weight: 700; color: #111827; margin-bottom: 2rem; }
        .dark .brand { color: #F1F5F9; }
        .code { font-size: 5rem; font-weight: 800; color: #E5E7EB; line-height: 1; margin-bottom: 0.5rem; }
        .dark .code { color: #334155; }
        .title { font-size: 1.25rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
        .dark .title { color: #E2E8F0; }
        .desc { font-size: 0.875rem; color: #6B7280; margin-bottom: 2rem; }
        .dark .desc { color: #94A3B8; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.625rem 1.5rem; background-color: #2563EB; color: white;
            font-weight: 600; font-size: 0.875rem; border-radius: 8px;
            text-decoration: none; transition: background-color 0.15s;
        }
        .btn:hover { background-color: #1D4ED8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
        </div>
        <div class="brand">Second Brain</div>
        <div class="code">500</div>
        <div class="title">Terjadi kesalahan server</div>
        <div class="desc">Terjadi kesalahan yang tidak terduga. Silakan coba lagi nanti.</div>
        <a href="javascript:location.reload()" class="btn">Coba Lagi</a>
    </div>
</body>
</html>
