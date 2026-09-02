<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- FOUC Prevention: apply dark class before paint (auth pages excluded) -->
        <script>
            (function() {
                var theme = localStorage.getItem('theme') || 'system';
                var isAuthPage = {{ in_array($page['component'] ?? '', ['Auth/Login', 'Auth/Register', 'Auth/ForgotPassword', 'Auth/ResetPassword', 'Auth/VerifyEmail', 'Auth/ConfirmPassword']) ? 'true' : 'false' }};
                if (!isAuthPage && (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches))) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-canvas text-gray-800 dark:text-gray-100 overflow-x-clip">
        @inertia
    </body>
</html>
