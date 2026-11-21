@php
    $navBadges = ['low_stock' => 0, 'overdue_receivables' => 0];
    try {
        $navBadges = \Illuminate\Support\Facades\Cache::remember('supos_sidebar_badges_v1', 60, function () {
            $lowStock = \App\Models\Product::active()
                ->whereRaw('stock_pcs <= COALESCE(min_stock_box, 10) * GREATEST(box_pcs_ratio, 1)')
                ->count();

            $overdue = \App\Models\OutgoingTransaction::where('payment_status', 'tempo')
                ->whereDate('due_date', '<', now('Asia/Jakarta')->toDateString())
                ->count();

            return [
                'low_stock' => (int) $lowStock,
                'overdue_receivables' => (int) $overdue,
            ];
        });
    } catch (\Throwable $e) {
        // Fallback aman jika tabel belum tersedia saat instalasi awal
    }

    $menuGroups = [
        [
            'heading' => 'Ringkasan Utama',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'dashboard.index',
                    'active' => ['dashboard.*'],
                    'icon' => 'dashboard',
                    'badge' => null,
                ],
            ],
        ],
        [
            'heading' => 'Inventori & Katalog',
            'items' => [
                [
                    'label' => 'Master Barang',
                    'route' => 'products.index',
                    'active' => ['products.*', 'categories.*'],
                    'icon' => 'inventory_2',
                    'badge' => null,
                ],
                [
                    'label' => 'Stok Barang',
                    'route' => 'stock.index',
                    'active' => ['stock.*'],
                    'icon' => 'warehouse',
                    'badge' =>
                        $navBadges['low_stock'] > 0
                            ? [
                                'count' => $navBadges['low_stock'],
                                'class' => 'bg-tertiary/15 text-tertiary border-tertiary/30',
                                'title' => 'SKU menipis / habis',
                            ]
                            : null,
                ],
                [
                    'label' => 'Barang Masuk',
                    'route' => 'incoming-goods.index',
                    'active' => ['incoming-goods.*'],
                    'icon' => 'input',
                    'badge' => null,
                ],
                [
                    'label' => 'Barang Keluar',
                    'route' => 'outgoing-goods.index',
                    'active' => ['outgoing-goods.*'],
                    'icon' => 'point_of_sale',
                    'badge' => null,
                ],
            ],
        ],
        [
            'heading' => 'Keuangan & Piutang',
            'items' => [
                [
                    'label' => 'Buku Tagihan',
                    'route' => 'receivables.index',
                    'active' => ['receivables.*'],
                    'icon' => 'receipt_long',
                    'badge' =>
                        $navBadges['overdue_receivables'] > 0
                            ? [
                                'count' => $navBadges['overdue_receivables'],
                                'class' => 'bg-error/15 text-error border-error/30',
                                'title' => 'Faktur piutang melewati jatuh tempo',
                            ]
                            : null,
                ],
                [
                    'label' => 'Kas Masuk',
                    'route' => 'cash-ins.index',
                    'active' => ['cash-ins.*'],
                    'icon' => 'payments',
                    'badge' => null,
                ],
                [
                    'label' => 'Kas Keluar',
                    'route' => 'cash-outs.index',
                    'active' => ['cash-outs.*'],
                    'icon' => 'account_balance_wallet',
                    'badge' => null,
                ],
            ],
        ],
    ];
@endphp

<aside id="appSidebar" aria-label="Navigasi Utama"
    class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-out bg-surface-container-low border-r border-outline-variant/15 flex flex-col justify-between select-none print:hidden">

    {{-- Brand Header --}}
    <div class="flex flex-col min-h-0 flex-1">
        <div class="h-16 px-space-lg flex items-center justify-between border-b border-outline-variant/15 shrink-0">
            <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 min-w-0 group">
                <div
                    class="w-10 h-10 rounded-xl bg-primary/15 border border-primary/25 flex items-center justify-center text-primary shadow-[0_0_20px_rgba(16,185,129,0.2)] group-hover:scale-105 transition-transform shrink-0">
                    <span class="material-symbols-outlined text-[24px]">storefront</span>
                </div>
                <div class="flex flex-col min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span
                            class="font-headline-lg text-headline-sm font-extrabold tracking-tight text-on-surface leading-none">
                            SUPOS
                        </span>
                        <span
                            class="px-1.5 py-0.5 rounded bg-primary/15 text-primary font-label-sm text-[10px] font-bold tracking-wider">
                            GROSIR
                        </span>
                    </div>
                    <span
                        class="font-label-sm text-label-sm text-on-surface-variant tracking-widest uppercase mt-1 truncate">
                        Sinar Utama
                    </span>
                </div>
            </a>

            <button id="mobileNavClose" type="button" aria-label="Tutup menu navigasi"
                class="lg:hidden w-9 h-9 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        {{-- Navigation Groups --}}
        <nav class="flex-1 overflow-y-auto px-space-md py-space-md">
            @foreach ($menuGroups as $group)
                <div
                    class="space-y-1 {{ !$loop->first ? 'mt-space-lg pt-space-md border-t border-outline-variant/15' : '' }}">
                    <div
                        class="px-3 pb-space-xs font-label-sm text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/70">
                        {{ $group['heading'] }}
                    </div>

                    @foreach ($group['items'] as $item)
                        @php
                            $isActive = request()->routeIs($item['active']);
                        @endphp
                        <a href="{{ route($item['route']) }}"
                            @if ($isActive) aria-current="page" @endif
                            class="group relative flex items-center justify-between gap-2.5 px-3 py-2.5 rounded-xl font-headline-sm text-body-sm transition-all
                                  {{ $isActive
                                      ? 'bg-primary/15 text-primary font-bold shadow-xs'
                                      : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface font-medium' }}">

                            @if ($isActive)
                                <span
                                    class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 rounded-r-full bg-primary"></span>
                            @endif

                            <div class="flex items-center gap-3 min-w-0">
                                <span
                                    class="material-symbols-outlined text-[20px] shrink-0 transition-colors {{ $isActive ? 'text-primary' : 'text-on-surface-variant group-hover:text-on-surface' }}">
                                    {{ $item['icon'] }}
                                </span>
                                <span class="truncate">{{ $item['label'] }}</span>
                            </div>

                            @if (!empty($item['badge']))
                                <span title="{{ $item['badge']['title'] }}"
                                    class="px-2 py-0.5 rounded-full border font-label-sm text-[11px] font-bold leading-none shrink-0 {{ $item['badge']['class'] }}">
                                    {{ $item['badge']['count'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>
    </div>

    {{-- Footer Status Card --}}
    <div class="p-space-md border-t border-outline-variant/15 shrink-0">
        <div class="p-3 rounded-xl bg-surface-container flex items-center justify-between gap-2">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-2.5 h-2.5 rounded-full bg-primary animate-pulse shrink-0"></span>
                <div class="flex flex-col min-w-0">
                    <span class="font-label-md text-label-md text-on-surface font-semibold truncate">
                        Toko Sinar Utama
                    </span>
                    {{-- <span class="font-label-sm text-[11px] text-on-surface-variant truncate">
                        Ledger Box–Pcs Aktif
                    </span> --}}
                </div>
            </div>
            <span
                class="px-2 py-0.5 rounded-md bg-surface-container-high text-on-surface-variant font-mono text-[11px] font-semibold shrink-0">
                WIB
            </span>
        </div>
    </div>
</aside>