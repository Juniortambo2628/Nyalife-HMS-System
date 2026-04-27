<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/jpeg" href="/assets/logo/logo-white-bg.jpg">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
        
        <!-- Vendor CSS Files (Local) -->
        <link rel="stylesheet" href="/assets/css/bootstrap-local.min.css">
        <link rel="stylesheet" href="/assets/css/fontawesome-local.min.css">

        <!-- Custom CSS -->
        <link href="/assets/css/z-index.css" rel="stylesheet">
        <link href="/assets/css/nyalife-loader-unified.css" rel="stylesheet">
        <link href="/assets/css/footer.css" rel="stylesheet">
        <link href="/assets/css/modal-unified.css" rel="stylesheet">
        <link href="/assets/css/nyalife-theme.css" rel="stylesheet">
        <link href="/assets/css/style.css" rel="stylesheet">
        <link href="/assets/css/custom.css" rel="stylesheet">
        <link href="/assets/css/sidebar.css" rel="stylesheet">
        <link href="/assets/css/layout-system.css" rel="stylesheet">
        <link href="/assets/css/dashboard-fresh.css" rel="stylesheet">
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
