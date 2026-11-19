<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Master Barang') - SUPOS | Sinar Utama</title>

    {{-- 🔥 FOUC Prevention: set theme SEBELUM CSS load --}}
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('theme');
                // Default = dark (kalau belum pernah pilih)
                var isDark = saved === null ? true : saved === 'dark';
                if (isDark) document.documentElement.classList.add('dark');
            } catch (e) {
                // localStorage disabled → fallback ke dark
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background font-body-md text-body-md text-on-surface antialiased selection:bg-primary-container selection:text-on-primary-container">

    {{-- ================= SIDEBAR ================= --}}
    <button id="mobileSidebarBackdrop" type="button" aria-label="Tutup menu navigasi"
            class="fixed inset-0 z-40 hidden bg-black/50 backdrop-blur-sm lg:hidden"></button>
    @include('layouts.partials.sidebar')

    <div class="pl-0 lg:pl-72">
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
    <script>
        (() => {
            const sidebar = document.getElementById('appSidebar');
            const toggle = document.getElementById('mobileNavToggle');
            const close = document.getElementById('mobileNavClose');
            const backdrop = document.getElementById('mobileSidebarBackdrop');

            const setSidebarOpen = (isOpen) => {
                sidebar.classList.toggle('-translate-x-full', !isOpen);
                backdrop.classList.toggle('hidden', !isOpen);
                document.body.classList.toggle('overflow-hidden', isOpen);
                toggle.setAttribute('aria-expanded', String(isOpen));
            };

            toggle.addEventListener('click', () => setSidebarOpen(true));
            close.addEventListener('click', () => setSidebarOpen(false));
            backdrop.addEventListener('click', () => setSidebarOpen(false));
            sidebar.querySelectorAll('a[href]:not([href="#"])').forEach(link => {
                link.addEventListener('click', () => setSidebarOpen(false));
            });
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') setSidebarOpen(false);
            });
        })();
    </script>
    
    <script>
        (function () {
            var btn  = document.getElementById('themeToggle');
            var icon = document.getElementById('themeIcon');
            if (!btn || !icon) return;

            function updateIcon() {
                // Kalau .dark aktif → icon jadi ☀️ (light_mode) untuk switch ke light
                // Kalau .dark tidak aktif → icon jadi 🌙 (dark_mode) untuk switch ke dark
                icon.textContent = document.documentElement.classList.contains('dark')
                    ? 'light_mode'
                    : 'dark_mode';
            }

            // Sinkronkan icon saat page load (localStorage sudah di-set di <head>)
            updateIcon();

            btn.addEventListener('click', function () {
                var isDark = document.documentElement.classList.toggle('dark');
                try {
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                } catch (e) { /* localStorage disabled */ }
                updateIcon();
            });
        })();
    </script>
</body>
</html>