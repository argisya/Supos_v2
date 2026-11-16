<aside id="appSidebar" class="fixed left-0 top-0 h-full w-72 -translate-x-full lg:translate-x-0 transition-transform duration-200 bg-surface-container-lowest z-50 flex flex-col justify-between py-space-md shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
    <div class="flex flex-col gap-space-md">
        <div class="px-margin flex items-center justify-between gap-space-sm">
            <div class="flex items-center gap-space-sm">
            <div class="w-9 h-9 rounded-xl bg-primary-container/20 flex items-center justify-center text-primary shadow-[0_0_16px_rgba(16,185,129,0.35)]">
                <span class="material-symbols-outlined text-[22px]">inventory_2</span>
            </div>
            <div class="flex flex-col">
                <span class="font-headline-sm text-headline-sm text-on-surface tracking-tight leading-none">GrosirBox <span class="text-primary">PRO</span></span>
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mt-1">Box-Pcs Engine v2.4</span>
            </div>
            </div>
            <button id="mobileNavClose" type="button" aria-label="Tutup menu navigasi"
                    class="lg:hidden w-9 h-9 rounded-xl text-on-surface-variant hover:bg-surface-container-high flex items-center justify-center">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="px-margin pt-space-xs">
            <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider px-space-xs">Modul Operasional</span>
        </div>

        <nav class="px-space-md flex flex-col gap-space-xs overflow-y-auto max-h-[calc(100vh-270px)]">
            @php
                $navItems = [
                    ['path' => 'dashboard',      'icon' => 'space_dashboard',       'label' => 'Dashboard'],
                    ['path' => 'master-barang',  'icon' => 'category',              'label' => 'Master Barang',  'route' => 'products.index'],
                    ['path' => 'barang-masuk',   'icon' => 'input',                 'label' => 'Barang Masuk',   'route' => 'incoming-goods.index'],
                    ['path' => 'barang-keluar',  'icon' => 'output',                'label' => 'Barang Keluar',  'route' => 'outgoing-goods.index'],
                    ['path' => 'stok-barang',    'icon' => 'warehouse',             'label' => 'Stok Barang',    'route' => 'stock.index'],
                    ['path' => 'buku-tagihan',   'icon' => 'receipt_long',          'label' => 'Buku Tagihan',   'route' => 'receivables.index'],
                    ['path' => 'kas-masuk',      'icon' => 'payments',              'label' => 'Kas Masuk',      'route' => 'cash-ins.index'],
                    ['path' => 'kas-keluar',     'icon' => 'account_balance_wallet','label' => 'Kas Keluar'],
                ];

                $currentRoute = match (true) {
                    request()->routeIs('products.*')       => 'master-barang',
                    request()->routeIs('incoming-goods.*') => 'barang-masuk',
                    request()->routeIs('outgoing-goods.*') => 'barang-keluar',
                    request()->routeIs('stock.*')          => 'stok-barang',
                    request()->routeIs('receivables.*')    => 'buku-tagihan',
                    request()->routeIs('cash-ins.*')       => 'kas-masuk',
                    default                                => null,
                };
            @endphp
            @foreach($navItems as $item)
                @php $active = $currentRoute === $item['path']; @endphp
                <a href="{{ isset($item['route']) ? route($item['route']) : '#' }}"
                   class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl transition-all group
                          {{ $active
                              ? 'bg-primary-container text-on-primary-container font-semibold shadow-[0_4px_16px_rgba(16,185,129,0.25)]'
                              : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                    <span class="material-symbols-outlined text-[20px] {{ $active ? 'text-on-primary-container' : 'text-on-surface-variant group-hover:text-primary' }} transition-colors">{{ $item['icon'] }}</span>
                    <span class="font-body-md text-body-md">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="px-margin pt-space-xs">
        <div class="p-space-sm rounded-xl bg-surface-container-low flex items-center justify-between">
            <div class="flex items-center gap-space-sm">
                <span class="material-symbols-outlined text-primary text-[20px]">shield</span>
                <div class="flex flex-col">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Otoritas Akun</span>
                    <span class="font-label-md text-label-md text-on-surface">Head Office (Full)</span>
                </div>
            </div>
            <span class="material-symbols-outlined text-on-surface-variant text-[16px]">lock</span>
        </div>
    </div>
</aside>