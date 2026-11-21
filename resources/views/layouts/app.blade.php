<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - SUPOS | Sinar Utama</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('icon.svg') }}">

    {{-- FOUC Prevention: set theme SEBELUM CSS load --}}
    <script>
        (function() {
            try {
                var saved = localStorage.getItem('theme');
                // Default = dark (kalau belum pernah pilih)
                var isDark = saved === null ? true : saved === 'dark';
                if (isDark) document.documentElement.classList.add('dark');
            } catch (e) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="bg-surface text-on-surface antialiased selection:bg-primary/25 selection:text-primary">

    {{-- ================= SIDEBAR ================= --}}
    <button id="mobileSidebarBackdrop" type="button" aria-label="Tutup menu navigasi"
        class="fixed inset-0 z-40 hidden bg-black/50 backdrop-blur-sm lg:hidden print:hidden"></button>

    @include('layouts.partials.sidebar')

    <div class="pl-0 lg:pl-72 print:pl-0 transition-[padding] duration-200">
        {{-- ================= HEADER ================= --}}
        @include('layouts.partials.header')

        <main class="w-full pt-16 print:pt-0 bg-surface min-h-screen px-margin">
            @yield('content')
        </main>
    </div>

    {{-- ================= GLOBAL TOAST NOTIFICATIONS ================= --}}
    @if (session('success'))
        <div id="flashSuccess" role="status"
            class="fixed bottom-6 right-6 z-[100] max-w-[min(92vw,28rem)] bg-primary text-on-primary px-space-lg py-space-md rounded-xl shadow-2xl font-headline-sm text-body-md flex items-center gap-2.5 print:hidden">
            <span class="material-symbols-outlined text-[20px] shrink-0">check_circle</span>
            <span class="flex-1">{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" aria-label="Tutup notifikasi"
                class="opacity-75 hover:opacity-100 flex items-center cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <script>
            setTimeout(() => document.getElementById('flashSuccess')?.remove(), 4500);
        </script>
    @endif

    @if (session('error'))
        <div id="flashError" role="alert"
            class="fixed bottom-6 right-6 z-[100] max-w-[min(92vw,28rem)] bg-error text-on-error px-space-lg py-space-md rounded-xl shadow-2xl font-headline-sm text-body-md flex items-center gap-2.5 print:hidden">
            <span class="material-symbols-outlined text-[20px] shrink-0">error</span>
            <span class="flex-1">{{ session('error') }}</span>
            <button type="button" onclick="this.parentElement.remove()" aria-label="Tutup peringatan"
                class="opacity-75 hover:opacity-100 flex items-center cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <script>
            setTimeout(() => document.getElementById('flashError')?.remove(), 6000);
        </script>
    @endif

    @stack('scripts')

    {{-- ================= GLOBAL DRAWER, THEME, & SHORTCUTS ================= --}}
    <script>
        (() => {
            const sidebar = document.getElementById('appSidebar');
            const toggle = document.getElementById('mobileNavToggle');
            const close = document.getElementById('mobileNavClose');
            const backdrop = document.getElementById('mobileSidebarBackdrop');

            if (!sidebar || !toggle || !backdrop) return;

            const isSidebarOpen = () => !sidebar.classList.contains('-translate-x-full');

            const setSidebarOpen = (open) => {
                sidebar.classList.toggle('-translate-x-full', !open);
                backdrop.classList.toggle('hidden', !open);
                document.body.classList.toggle('overflow-hidden', open);
                toggle.setAttribute('aria-expanded', String(open));
            };

            toggle.addEventListener('click', () => setSidebarOpen(true));
            close?.addEventListener('click', () => setSidebarOpen(false));
            backdrop.addEventListener('click', () => setSidebarOpen(false));

            sidebar.querySelectorAll('a[href]:not([href="#"])').forEach(link => {
                link.addEventListener('click', () => setSidebarOpen(false));
            });

            document.addEventListener('keydown', (event) => {
                // Tutup drawer mobile hanya jika sedang terbuka (tidak mengganggu modal)
                if (event.key === 'Escape' && isSidebarOpen()) {
                    setSidebarOpen(false);
                }
            });
        })();
    </script>

    <script>
        (function() {
            var btn = document.getElementById('themeToggle');
            var icon = document.getElementById('themeIcon');
            if (!btn || !icon) return;

            function updateIcon() {
                icon.textContent = document.documentElement.classList.contains('dark') ?
                    'light_mode' :
                    'dark_mode';
            }

            updateIcon();

            btn.addEventListener('click', function() {
                var isDark = document.documentElement.classList.toggle('dark');
                try {
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                } catch (e) {
                    /* localStorage disabled */
                }
                updateIcon();
            });
        })();
    </script>

    <script>
        (function() {
            // Standarisasi pintasan global:
            // - Ctrl/Cmd + K => Pencarian cepat global di Header (fallback ke pencarian halaman)
            // - '/'          => Fokus ke pencarian tabel lokal halaman (jika tidak sedang mengetik)
            document.addEventListener('keydown', function(e) {
                var tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
                var isTyping = tag === 'input' || tag === 'textarea' || tag === 'select' || (e.target && e
                    .target.isContentEditable);

                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    var globalSearch = document.getElementById('globalHeaderSearch');
                    var localSearch = document.querySelector('main input[name="search"]');
                    var targetInput = (globalSearch && globalSearch.offsetParent !== null) ? globalSearch :
                        localSearch;
                    if (targetInput) {
                        e.preventDefault();
                        targetInput.focus();
                        targetInput.select();
                    }
                    return;
                }

                if (e.key === '/' && !isTyping && !e.ctrlKey && !e.metaKey && !e.altKey) {
                    var pageSearch = document.querySelector('main input[name="search"]');
                    if (pageSearch && pageSearch.offsetParent !== null) {
                        e.preventDefault();
                        pageSearch.focus();
                        pageSearch.select();
                    }
                }
            });
        })();
    </script>
</body>

</html>
