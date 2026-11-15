<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GrosirBox PRO — @yield('title', 'Master Barang')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background font-body-md text-body-md text-on-surface antialiased selection:bg-primary-container selection:text-on-primary-container">

    {{-- ================= SIDEBAR ================= --}}
    @include('layouts.partials.sidebar')

    <div class="pl-72">
        {{-- ================= HEADER ================= --}}
        @include('layouts.partials.header')

        <main class="w-full pt-16 bg-surface min-h-screen px-margin">
            @yield('content')
        </main>
    </div>

    {{-- Flash message --}}
    @if(session('success'))
        <div id="flashSuccess"
             class="fixed bottom-6 right-6 z-[100] bg-primary text-on-primary px-space-lg py-space-md rounded-xl shadow-2xl font-headline-sm text-body-md flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('success') }}
        </div>
        <script>
            setTimeout(() => document.getElementById('flashSuccess')?.remove(), 4000);
        </script>
    @endif

    @stack('scripts')
</body>
</html>