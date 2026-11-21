@php
    $routeLabels = [
        'dashboard.*' => ['group' => 'Ringkasan', 'title' => 'Dashboard Operasional', 'icon' => 'dashboard'],
        'products.*' => ['group' => 'Inventori', 'title' => 'Master Barang', 'icon' => 'inventory_2'],
        'categories.*' => ['group' => 'Inventori', 'title' => 'Master Barang', 'icon' => 'inventory_2'],
        'incoming-goods.*' => ['group' => 'Inventori', 'title' => 'Barang Masuk', 'icon' => 'input'],
        'stock.*' => ['group' => 'Inventori', 'title' => 'Stok Barang', 'icon' => 'warehouse'],
        'outgoing-goods.*' => ['group' => 'Inventori', 'title' => 'Barang Keluar', 'icon' => 'point_of_sale'],
        'receivables.*' => ['group' => 'Keuangan', 'title' => 'Buku Tagihan', 'icon' => 'receipt_long'],
        'cash-ins.*' => ['group' => 'Keuangan', 'title' => 'Kas Masuk', 'icon' => 'payments'],
        'cash-outs.*' => ['group' => 'Keuangan', 'title' => 'Kas Keluar', 'icon' => 'account_balance_wallet'],
    ];

    $activeCtx = [
        'group' => 'SUPOS',
        'title' => trim($__env->yieldContent('title', 'Dashboard')),
        'icon' => 'storefront',
    ];
    foreach ($routeLabels as $pattern => $meta) {
        if (request()->routeIs($pattern)) {
            $activeCtx = $meta;
            break;
        }
    }

    $wibNow = now('Asia/Jakarta');
@endphp

<header
    class="fixed top-0 right-0 left-0 lg:left-72 z-30 h-16 bg-surface/85 backdrop-blur-xl border-b border-outline-variant/15 px-margin flex items-center justify-between gap-space-md print:hidden">

    {{-- Kiri: Hamburger Mobile + Konteks Halaman Aktif --}}
    <div class="flex items-center gap-3 min-w-0">
        <button id="mobileNavToggle" type="button" aria-label="Buka menu navigasi" aria-controls="appSidebar"
            aria-expanded="false"
            class="lg:hidden w-10 h-10 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface flex items-center justify-center transition-colors shrink-0 cursor-pointer">
            <span class="material-symbols-outlined text-[22px]">menu</span>
        </button>

        <div class="flex items-center gap-2 min-w-0">
            <span
                class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-container text-on-surface-variant font-label-sm text-label-sm shrink-0">
                <span class="material-symbols-outlined text-[15px] text-primary">{{ $activeCtx['icon'] }}</span>
                <span>{{ $activeCtx['group'] }}</span>
            </span>
            <span class="hidden sm:inline text-outline-variant">/</span>
            <span class="font-headline-sm text-body-md font-bold text-on-surface truncate">
                {{ $activeCtx['title'] }}
            </span>
        </div>
    </div>

    {{-- Tengah: Pencarian Cepat Katalog Produk (Ctrl + K) --}}
    <form method="GET" action="{{ route('products.index') }}" class="hidden md:flex items-center flex-1 max-w-md">
        <div class="relative w-full">
            <span
                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">
                search
            </span>
            <input id="globalHeaderSearch" name="search" type="text"
                value="{{ request()->routeIs('products.*') ? request('search') : '' }}"
                placeholder="Cari cepat SKU atau nama barang..." autocomplete="off"
                class="w-full h-9 pl-9 pr-14 rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/60 font-body-sm text-body-sm border border-outline-variant/15 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" />
            <kbd
                class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 px-1.5 py-0.5 rounded bg-surface-container-high text-on-surface-variant font-mono text-[10px] font-semibold border border-outline-variant/20">
                Ctrl K
            </kbd>
        </div>
    </form>

    {{-- Kanan: Jam Operasional WIB + Toggle Tema --}}
    <div class="flex items-center gap-2.5 shrink-0">
        <div class="hidden sm:flex items-center gap-2 h-9 px-3 rounded-xl bg-surface-container border border-outline-variant/15 text-on-surface-variant font-label-sm text-label-sm"
            title="Zona Waktu Operasional Toko (WIB)">
            <span class="material-symbols-outlined text-[16px] text-primary">schedule</span>
            <span id="headerWibClock" class="font-mono text-on-surface font-semibold whitespace-nowrap">
                {{ $wibNow->translatedFormat('d M Y, H:i') }} WIB
            </span>
        </div>

        <button id="themeToggle" type="button" aria-label="Ubah tema tampilan" title="Ganti Mode Gelap / Terang"
            class="w-10 h-10 rounded-xl bg-surface-container hover:bg-surface-container-high border border-outline-variant/15 text-on-surface-variant hover:text-primary flex items-center justify-center transition-colors cursor-pointer">
            <span id="themeIcon" class="material-symbols-outlined text-[20px]">light_mode</span>
        </button>
    </div>
</header>

<script>
    (function() {
        var clockEl = document.getElementById('headerWibClock');
        if (!clockEl) return;

        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        function refreshWibClock() {
            try {
                var wib = new Date(new Date().toLocaleString('en-US', {
                    timeZone: 'Asia/Jakarta'
                }));
                var dd = String(wib.getDate()).padStart(2, '0');
                var mon = months[wib.getMonth()];
                var yy = wib.getFullYear();
                var hh = String(wib.getHours()).padStart(2, '0');
                var mm = String(wib.getMinutes()).padStart(2, '0');
                clockEl.textContent = dd + ' ' + mon + ' ' + yy + ', ' + hh + ':' + mm + ' WIB';
            } catch (e) {
                /* Abaikan jika browser tidak mendukung timeZone */ }
        }

        refreshWibClock();
        setInterval(refreshWibClock, 30000);
    })();
</script>