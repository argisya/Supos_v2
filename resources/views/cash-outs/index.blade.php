@extends('layouts.app')

@section('title', 'Kas Keluar')

@php
    $fmt = fn($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $fmtJt = function ($n) {
        if ($n >= 1_000_000_000) {
            return 'Rp ' . number_format($n / 1_000_000_000, 2, ',', '.') . ' M';
        }
        if ($n >= 1_000_000) {
            return 'Rp ' . number_format($n / 1_000_000, 2, ',', '.') . ' Jt';
        }
        if ($n >= 1_000) {
            return 'Rp ' . number_format($n / 1_000, 0, ',', '.') . ' Rb';
        }
        return 'Rp ' . number_format((int) $n, 0, ',', '.');
    };

    $catStyles = [
        'kulakan_lunas' => [
            'label' => 'Kulakan Tunai Lunas',
            'badge' => 'bg-primary/15 text-primary',
            'dot'   => 'bg-primary',
            'text'  => 'text-primary',
        ],
        'pelunasan_hutang_supplier' => [
            'label' => 'Pelunasan Hutang Supplier',
            'badge' => 'bg-secondary/15 text-secondary',
            'dot'   => 'bg-secondary',
            'text'  => 'text-secondary',
        ],
        'biaya_operasional' => [
            'label' => 'Biaya Operasional',
            'badge' => 'bg-tertiary/15 text-tertiary',
            'dot'   => 'bg-tertiary',
            'text'  => 'text-tertiary',
        ],
        'biaya_listrik' => [
            'label' => 'Listrik & Utilitas',
            'badge' => 'bg-tertiary/15 text-tertiary',
            'dot'   => 'bg-tertiary',
            'text'  => 'text-tertiary',
        ],
        'gaji_staff' => [
            'label' => 'Gaji & Kasbon Staff',
            'badge' => 'bg-secondary/15 text-secondary',
            'dot'   => 'bg-secondary',
            'text'  => 'text-secondary',
        ],
        'logistik_armada' => [
            'label' => 'Logistik & Armada',
            'badge' => 'bg-tertiary/15 text-tertiary',
            'dot'   => 'bg-tertiary',
            'text'  => 'text-tertiary',
        ],
        'bensin_distribusi' => [
            'label' => 'Bensin & Tol Distribusi',
            'badge' => 'bg-tertiary/15 text-tertiary',
            'dot'   => 'bg-tertiary',
            'text'  => 'text-tertiary',
        ],
        'lain_lain' => [
            'label' => 'Pengeluaran Lain-lain',
            'badge' => 'bg-surface-container-highest text-on-surface-variant',
            'dot'   => 'bg-on-surface-variant',
            'text'  => 'text-on-surface-variant',
        ],
    ];

    $acctStyles = [
        'bca' => [
            'label' => 'BCA Bisnis',
            'short' => 'BCA',
            'box'   => 'bg-primary-container/20 text-primary',
            'badge' => 'bg-primary/20 text-primary',
            'dot'   => 'bg-primary',
        ],
        'mandiri' => [
            'label' => 'Mandiri Giro',
            'short' => 'MDR',
            'box'   => 'bg-secondary-container/25 text-secondary',
            'badge' => 'bg-secondary/20 text-secondary',
            'dot'   => 'bg-secondary',
        ],
        'kas_tunai' => [
            'label' => 'Kas Laci Toko',
            'short' => 'Tunai',
            'box'   => 'bg-tertiary-container/25 text-tertiary',
            'badge' => 'bg-tertiary/20 text-tertiary',
            'dot'   => 'bg-tertiary',
        ],
    ];

    $totalAlloc = max(0.01, (float) $catTotal);
    $hasActiveFilter =
        $cat !== 'all' ||
        $acc !== 'all' ||
        request()->filled('search') ||
        request()->filled('from') ||
        request()->filled('to');
@endphp

@section('content')
    <div class="flex flex-col w-full pb-16 space-y-space-lg">

        {{-- ============ HEADER ============ --}}
        <div class="flex flex-col gap-space-sm pt-space-xs">
            <div class="flex items-center gap-space-xs font-label-md text-label-md text-on-surface-variant">
                <span class="hover:text-primary cursor-pointer">Sistem Grosir</span>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="hover:text-primary cursor-pointer">Manajemen Finansial</span>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-primary font-semibold">Kas Keluar (Pengeluaran &amp; Hutang Supplier)</span>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-space-md">
                <div class="flex flex-col gap-1 max-w-3xl">
                    <div class="flex items-center gap-space-sm flex-wrap">
                        <h1 class="font-headline-lg text-headline-lg text-on-surface font-extrabold tracking-tight">
                            Kas Keluar &amp; Pembayaran Hutang Supplier
                        </h1>
                        <div class="flex items-center gap-1.5 px-space-sm py-0.5 rounded-full bg-error/10 text-error">
                            <span class="w-2 h-2 rounded-full bg-error animate-ping"></span>
                            <span class="font-label-sm text-label-sm uppercase tracking-wider font-bold">Sinkronisasi Kas Keluar Aktif</span>
                        </div>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                        Pantau pelunasan faktur tempo dari modul <span class="text-primary font-medium">Barang Masuk</span>, pembelian tunai kulakan, serta pencatatan biaya operasional dalam satu jurnal terintegrasi.
                    </p>
                </div>
                <div class="flex items-center gap-space-sm flex-wrap">
                    <a href="{{ route('cash-outs.export', request()->query()) }}"
                        class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm flex items-center gap-space-xs transition-colors shadow-sm whitespace-nowrap">
                        <span class="material-symbols-outlined text-[18px] text-tertiary">download</span>
                        <span>Export Buku Kas (CSV)</span>
                    </a>
                    <button type="button" onclick="openCashOutInputModal('supplier')"
                        class="h-10 px-space-lg rounded-xl bg-primary-container text-on-primary-container font-headline-sm text-body-sm font-bold flex items-center gap-space-xs shadow-[0_4px_20px_rgba(16,185,129,0.35)] hover:bg-primary transition-all active:scale-95 whitespace-nowrap cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        <span>Catat Kas Keluar</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ============ VALIDATION ERRORS ============ --}}
        @if ($errors->any())
            <div class="p-space-md rounded-xl bg-error/15 border border-error/30 text-error font-body-sm text-body-sm flex items-start justify-between gap-space-sm">
                <div class="flex flex-col gap-1">
                    <span class="font-bold flex items-center gap-space-xs">
                        <span class="material-symbols-outlined text-[18px]">error</span>
                        Periksa kembali:
                    </span>
                    <ul class="list-disc list-inside ml-1">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-error/70 hover:text-error cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
        @endif

        {{-- ============ KARTU SALDO REKENING SUMBER DANA ============ --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
            @foreach ($acctStyles as $key => $meta)
                @php $bal = $balances[$key] ?? 0; @endphp
                <div class="p-space-md rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex items-center justify-between gap-space-md shadow-sm">
                    <div class="flex items-center gap-space-sm min-w-0">
                        <div class="w-11 h-11 rounded-xl {{ $meta['box'] }} font-mono font-extrabold text-body-sm flex items-center justify-center shrink-0">
                            {{ $meta['short'] }}
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="font-headline-sm text-body-md font-bold text-on-surface truncate">{{ $meta['label'] }}</span>
                            <span class="font-label-sm text-label-sm text-on-surface-variant">Saldo Tersedia Realtime</span>
                        </div>
                    </div>
                    <div class="flex flex-col items-end shrink-0">
                        <span class="font-mono font-extrabold text-body-lg text-on-surface">{{ $fmt($bal) }}</span>
                        <span class="px-2 py-0.5 rounded-full {{ $meta['badge'] }} font-label-sm text-label-sm font-bold">Aktif</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ============ 4 KARTU KPI ============ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-space-md">
            {{-- KPI 1 --}}
            <div class="p-space-md rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex flex-col justify-between shadow-sm relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-28 h-28 bg-error/5 rounded-full blur-2xl group-hover:bg-error/10"></div>
                <div class="flex items-center justify-between mb-space-xs">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-bold">Total Kas Keluar Bulan Ini</span>
                    <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-error">
                        <span class="material-symbols-outlined text-[18px]">payments</span>
                    </div>
                </div>
                <div class="my-space-xs">
                    <span class="font-display-lg text-display-lg text-on-surface font-extrabold tracking-tight">{{ $fmt($monthTotal) }}</span>
                </div>
                <div class="flex items-center justify-between pt-space-xs">
                    <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $monthCount }} Mutasi Terposting</span>
                    <span class="font-label-sm text-label-sm text-error font-bold bg-error/10 px-2 py-0.5 rounded-full">{{ $fmtJt($monthTotal) }}</span>
                </div>
            </div>

            {{-- KPI 2 --}}
            <div class="p-space-md rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex flex-col justify-between shadow-sm relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-28 h-28 bg-error/5 rounded-full blur-2xl group-hover:bg-error/10"></div>
                <div class="flex items-center justify-between mb-space-xs">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-bold">Pengeluaran Hari Ini</span>
                    <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-error">
                        <span class="material-symbols-outlined text-[18px]">today</span>
                    </div>
                </div>
                <div class="my-space-xs">
                    <span class="font-display-lg text-display-lg text-error font-extrabold tracking-tight">{{ $fmt($todayTotal) }}</span>
                </div>
                <div class="flex items-center justify-between pt-space-xs">
                    <span class="font-body-sm text-body-sm text-on-surface-variant">Dari {{ $todayCount }} transaksi hari ini</span>
                    <span class="font-label-sm text-label-sm text-error font-bold bg-error/10 px-2 py-0.5 rounded-full">{{ now('Asia/Jakarta')->translatedFormat('d M') }}</span>
                </div>
            </div>

            {{-- KPI 3 --}}
            <div class="p-space-md rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex flex-col justify-between shadow-sm relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-28 h-28 bg-secondary/5 rounded-full blur-2xl group-hover:bg-secondary/10"></div>
                <div class="flex items-center justify-between mb-space-xs">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-bold">Belanja Supplier Hari Ini</span>
                    <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined text-[18px]">inventory_2</span>
                    </div>
                </div>
                <div class="my-space-xs">
                    <span class="font-display-lg text-display-lg text-on-surface font-extrabold tracking-tight">{{ $fmt($todaySupplier) }}</span>
                </div>
                <div class="flex items-center justify-between pt-space-xs">
                    <span class="font-body-sm text-body-sm text-on-surface-variant">Ops: {{ $fmtJt($todayOps) }}</span>
                    <span class="font-label-sm text-label-sm text-secondary font-bold bg-secondary/10 px-2 py-0.5 rounded-full">Auto-Sync</span>
                </div>
            </div>

            {{-- KPI 4 --}}
            <div class="p-space-md rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex flex-col justify-between shadow-sm relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-28 h-28 bg-tertiary/5 rounded-full blur-2xl group-hover:bg-tertiary/10"></div>
                <div class="flex items-center justify-between mb-space-xs">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-bold">Alokasi Dana Dominan</span>
                    <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-tertiary">
                        <span class="material-symbols-outlined text-[18px]">pie_chart</span>
                    </div>
                </div>
                <div class="my-auto flex flex-col space-y-1.5">
                    @php
                        $hasCatData = false;
                        $topCats = $catBreakdown->sortByDesc('total')->take(3);
                    @endphp
                    @foreach ($topCats as $key => $row)
                        @php
                            $total = $row['total'] ?? 0;
                            if ($total <= 0) continue;
                            $hasCatData = true;
                            $meta = $catStyles[$key] ?? ['label' => ucfirst($key), 'dot' => 'bg-primary', 'text' => 'text-primary'];
                            $pct  = round(($total / $totalAlloc) * 100);
                        @endphp
                        <div class="flex items-center justify-between font-body-sm text-body-sm">
                            <span class="text-on-surface font-medium flex items-center gap-1.5 truncate pr-2">
                                <span class="w-2 h-2 rounded-full shrink-0 {{ $meta['dot'] }}"></span>
                                <span class="truncate">{{ $meta['label'] }}</span>
                            </span>
                            <span class="font-mono font-bold shrink-0 {{ $meta['text'] }}">{{ $pct }}%</span>
                        </div>
                    @endforeach
                    @if (!$hasCatData)
                        <span class="font-body-sm text-body-sm text-on-surface-variant">Belum ada pengeluaran bulan ini.</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============ WIDGET OPERASIONAL: JATUH TEMPO & BIAYA RUTIN ============ --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-md">
            {{-- Jatuh Tempo Supplier <= 5 Hari --}}
            <div class="lg:col-span-7 p-space-md rounded-xl bg-surface-container-low shadow-sm flex flex-col gap-space-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-space-xs">
                        <span class="material-symbols-outlined text-error text-[20px]">alarm</span>
                        <h3 class="font-headline-sm text-body-md text-on-surface font-bold">Jatuh Tempo Supplier Segera (&le; 5 Hari)</h3>
                    </div>
                    <span class="px-2 py-0.5 rounded-full bg-error/15 text-error font-label-sm text-label-sm font-bold">{{ $upcomingDue->count() }} Tagihan</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-space-sm">
                    @forelse ($upcomingDue as $t)
                        @php $days = now('Asia/Jakarta')->startOfDay()->diffInDays($t->due_date, false); @endphp
                        <div class="p-space-sm rounded-xl bg-surface-container flex flex-col justify-between gap-space-xs">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex flex-col min-w-0">
                                    <span class="font-headline-sm text-body-sm text-on-surface font-bold truncate">{{ $t->supplier?->name }}</span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant truncate">Faktur {{ $t->no_faktur }} • {{ $t->product?->name }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full shrink-0 {{ $days <= 1 ? 'bg-error/15 text-error' : 'bg-tertiary/15 text-tertiary' }} font-label-sm text-label-sm font-bold">
                                    {{ $days < 0 ? 'Telat ' . abs($days) . ' hr' : ($days == 0 ? 'Hari ini' : 'Sisa ' . $days . ' hr') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between pt-1.5 border-t border-surface-container-highest/40">
                                <div class="flex flex-col">
                                    <span class="font-mono text-body-sm {{ $days <= 1 ? 'text-error' : 'text-on-surface' }} font-extrabold">{{ $fmt($t->remaining) }}</span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant">JT: {{ $t->due_date->format('d M Y') }}</span>
                                </div>
                                <button type="button" onclick="payFromWidget({{ $t->id }})"
                                    class="h-8 px-space-sm rounded-lg bg-primary-container hover:bg-primary text-on-primary-container font-headline-sm text-body-sm font-bold transition-all cursor-pointer">
                                    Bayar
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="md:col-span-2 p-space-md rounded-xl bg-surface-container flex items-center justify-center gap-2 text-on-surface-variant font-body-sm text-body-sm">
                            <span class="material-symbols-outlined text-[20px] text-primary">check_circle</span>
                            <span>Tidak ada tagihan supplier jatuh tempo dalam 5 hari ke depan.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Biaya Rutin Terakhir --}}
            <div class="lg:col-span-5 p-space-md rounded-xl bg-surface-container-low shadow-sm flex flex-col gap-space-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-space-xs">
                        <span class="material-symbols-outlined text-tertiary text-[20px]">calendar_month</span>
                        <h3 class="font-headline-sm text-body-md text-on-surface font-bold">Biaya Rutin Terakhir</h3>
                    </div>
                    <button type="button" onclick="openCashOutInputModal('ops')" class="text-primary hover:underline font-label-sm text-label-sm font-bold cursor-pointer">
                        + Catat Biaya
                    </button>
                </div>
                <div class="flex flex-col gap-space-xs">
                    @foreach ($recurringCats as $rc)
                        @php
                            $last = $recurringCosts[$rc] ?? null;
                            $meta = $catStyles[$rc] ?? ['label' => ucfirst($rc)];
                        @endphp
                        <div class="p-space-xs px-space-sm rounded-xl bg-surface-container flex items-center justify-between">
                            <div class="flex items-center gap-space-sm min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-tertiary shrink-0">
                                    <span class="material-symbols-outlined text-[18px]">
                                        {{ $rc === 'biaya_listrik' ? 'bolt' : ($rc === 'logistik_armada' ? 'local_shipping' : 'local_gas_station') }}
                                    </span>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="font-headline-sm text-body-sm text-on-surface font-semibold truncate">{{ $meta['label'] }}</span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant">
                                        {{ $last ? 'Terakhir: ' . $last->paid_at->translatedFormat('d M Y') : 'Belum pernah dicatat' }}
                                    </span>
                                </div>
                            </div>
                            <span class="font-mono text-body-sm text-on-surface font-bold shrink-0">{{ $last ? $fmt($last->amount) : '—' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ============ FILTER TABS & PERIODE TANGGAL ============ --}}
        <div class="flex flex-col gap-space-sm">
            <div class="flex items-center justify-between gap-space-sm flex-wrap">
                <div class="flex items-center gap-space-xs overflow-x-auto pb-1">
                    @php
                        $tabs = [
                            [
                                'value'       => 'all',
                                'label'       => 'Semua Mutasi',
                                'count'       => $tabCounts['all'],
                                'count_class' => 'bg-on-primary-container text-primary-container',
                            ],
                            [
                                'value'       => 'supplier',
                                'label'       => 'Supplier & Kulakan',
                                'count'       => $tabCounts['supplier'],
                                'count_class' => 'bg-primary/20 text-primary font-bold',
                            ],
                            [
                                'value'       => 'operasional',
                                'label'       => 'Biaya Operasional',
                                'count'       => $tabCounts['operasional'],
                                'count_class' => 'bg-secondary/20 text-secondary font-bold',
                            ],
                            [
                                'value'       => 'manual',
                                'label'       => 'Input Manual',
                                'count'       => $tabCounts['manual'],
                                'count_class' => 'bg-tertiary/20 text-tertiary font-bold',
                            ],
                        ];
                    @endphp
                    @foreach ($tabs as $t)
                        @php $active = $cat === $t['value']; @endphp
                        <button type="button" onclick="selectCashOutCategory('{{ $t['value'] }}')"
                            class="px-space-md py-space-xs rounded-xl font-headline-sm text-body-sm flex items-center gap-space-xs whitespace-nowrap transition-colors cursor-pointer
                            {{ $active ? 'bg-primary-container text-on-primary-container font-bold shadow-sm' : 'bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface' }}">
                            <span>{{ $t['label'] }}</span>
                            <span class="px-1.5 py-0.2 rounded-full {{ $t['count_class'] }} font-label-sm text-label-sm">{{ $t['count'] }}</span>
                        </button>
                    @endforeach
                </div>

                {{-- Preset Periode Cepat --}}
                <div class="flex items-center gap-space-xs flex-wrap">
                    <button type="button" onclick="setCashOutQuickDate('today')"
                        class="h-8 px-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface font-label-sm text-label-sm font-semibold transition-colors cursor-pointer">
                        Hari Ini
                    </button>
                    <button type="button" onclick="setCashOutQuickDate('7days')"
                        class="h-8 px-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface font-label-sm text-label-sm font-semibold transition-colors cursor-pointer">
                        7 Hari Terakhir
                    </button>
                    <button type="button" onclick="setCashOutQuickDate('month')"
                        class="h-8 px-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface font-label-sm text-label-sm font-semibold transition-colors cursor-pointer">
                        Bulan Ini
                    </button>
                    @if ($hasActiveFilter)
                        <a href="{{ route('cash-outs.index') }}"
                            class="h-8 px-space-sm rounded-lg bg-error/15 hover:bg-error/25 text-error font-label-sm text-label-sm font-bold flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined text-[15px]">filter_alt_off</span>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </div>

            <form method="GET" action="{{ route('cash-outs.index') }}" id="coFilterForm"
                class="flex flex-wrap items-center justify-between gap-space-sm bg-surface-container-low p-space-sm rounded-xl shadow-sm">
                <input type="hidden" name="category" id="coCategoryInput" value="{{ $cat }}">

                <div class="flex items-center gap-space-sm flex-1 min-w-[260px]">
                    <div class="relative w-full max-w-md">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                        <input name="search" id="coSearchInput" value="{{ request('search') }}"
                            placeholder="Cari No. KK, faktur, supplier, keterangan..."
                            class="w-full h-10 pl-10 pr-space-md rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/60 font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-space-sm">
                    <div class="flex items-center gap-space-xs px-space-sm h-10 rounded-xl bg-surface-container">
                        <span class="material-symbols-outlined text-[18px] text-primary">date_range</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold">Dari</span>
                        <input type="date" name="from" id="coDateFrom" value="{{ $from }}"
                            onchange="this.form.submit()"
                            class="bg-transparent text-on-surface font-body-sm text-body-sm focus:outline-none" />
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold pl-1">s/d</span>
                        <input type="date" name="to" id="coDateTo" value="{{ $to }}"
                            onchange="this.form.submit()"
                            class="bg-transparent text-on-surface font-body-sm text-body-sm focus:outline-none" />
                    </div>

                    <select name="account" onchange="this.form.submit()"
                        class="h-10 px-space-sm rounded-xl bg-surface-container text-on-surface font-headline-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="all" @selected($acc === 'all')>Semua Akun</option>
                        @foreach ($acctStyles as $k => $m)
                            <option value="{{ $k }}" @selected($acc === $k)>{{ $m['label'] }}</option>
                        @endforeach
                    </select>

                    <select name="sort" onchange="this.form.submit()"
                        class="h-10 px-space-sm rounded-xl bg-surface-container text-on-surface font-headline-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="latest" @selected($sort === 'latest')>Waktu Terbaru</option>
                        <option value="amount_desc" @selected($sort === 'amount_desc')>Nominal Tertinggi</option>
                        <option value="amount_asc" @selected($sort === 'amount_asc')>Nominal Terendah</option>
                    </select>

                    <a href="{{ route('cash-outs.index') }}"
                        class="h-10 w-10 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface flex items-center justify-center"
                        title="Muat Ulang">
                        <span class="material-symbols-outlined text-[20px]">refresh</span>
                    </a>
                </div>
            </form>
        </div>

        {{-- ============ TABEL JURNAL ============ --}}
        <div class="w-full overflow-hidden rounded-xl bg-surface-container-low shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">
                            <th class="py-space-md px-space-md font-bold">No. Kas Keluar &amp; Waktu</th>
                            <th class="py-space-md px-space-md font-bold">Tujuan / Penerima</th>
                            <th class="py-space-md px-space-md font-bold">Kategori &amp; Rincian</th>
                            <th class="py-space-md px-space-md font-bold">Akun Sumber</th>
                            <th class="py-space-md px-space-md font-bold text-right">Jumlah Kas Keluar</th>
                            <th class="py-space-md px-space-md font-bold text-center">Status</th>
                            <th class="py-space-md px-space-md font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-highest/20 font-body-sm text-body-sm">
                        @forelse ($cashOuts as $co)
                            @php
                                $cStyle = $catStyles[$co->category] ?? [
                                    'label' => ucfirst($co->category),
                                    'badge' => 'bg-primary/15 text-primary',
                                    'dot'   => 'bg-primary',
                                    'text'  => 'text-primary',
                                ];
                                $aStyle = $acctStyles[$co->account] ?? [
                                    'label' => ucfirst($co->account),
                                    'short' => '?',
                                    'dot'   => 'bg-primary',
                                ];
                                $isAuto    = $co->is_auto;
                                $recipient = $co->source?->supplier?->name ?? 'Pengeluaran Operasional';
                                $rowJson   = [
                                    'code'         => $co->code,
                                    'paid_at'      => $co->paid_at->translatedFormat('d M Y, H:i') . ' WIB',
                                    'is_auto'      => $isAuto,
                                    'source_type'  => $co->source_type ? class_basename($co->source_type) : 'Manual Input',
                                    'recipient'    => $recipient,
                                    'category'     => $cStyle['label'],
                                    'account'      => $aStyle['label'],
                                    'amount'       => $fmt($co->amount),
                                    'reference_no' => $co->reference_no ?? '-',
                                    'notes'        => $co->notes ?? '-',
                                ];
                            @endphp
                            <tr class="hover:bg-surface-container/60 transition-colors group">
                                <td class="py-space-md px-space-md align-top">
                                    <div class="flex flex-col">
                                        <span class="font-label-md text-label-md font-bold text-on-surface group-hover:text-primary transition-colors">{{ $co->code }}</span>
                                        <span class="font-label-sm text-label-sm text-on-surface-variant mt-0.5">{{ $co->paid_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                        <span class="font-label-sm text-label-sm {{ $isAuto ? 'text-primary' : 'text-tertiary' }} flex items-center gap-0.5 mt-0.5">
                                            <span class="material-symbols-outlined text-[12px]">{{ $isAuto ? 'link' : 'edit_note' }}</span>
                                            {{ $isAuto ? 'Auto-Journal' : 'Manual Input' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-space-md px-space-md align-top">
                                    <div class="flex flex-col">
                                        <span class="font-headline-sm text-body-md font-bold text-on-surface">{{ $recipient }}</span>
                                        @if ($co->source_type)
                                            <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-mono">{{ class_basename($co->source_type) }}</span>
                                        @endif
                                        @if ($co->reference_no)
                                            <span class="font-mono font-label-sm text-label-sm text-tertiary mt-0.5">Ref: {{ $co->reference_no }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-space-md px-space-md align-top">
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm uppercase font-bold {{ $cStyle['badge'] }}">
                                            {{ $cStyle['label'] }}
                                        </span>
                                        <span class="font-body-sm text-body-sm text-on-surface-variant">{{ Str::limit($co->notes, 60) }}</span>
                                    </div>
                                </td>
                                <td class="py-space-md px-space-md align-top">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full {{ $aStyle['dot'] }}"></span>
                                        <span class="font-mono font-body-sm text-body-sm text-on-surface font-semibold">{{ $aStyle['label'] }}</span>
                                    </div>
                                </td>
                                <td class="py-space-md px-space-md align-top text-right">
                                    <span class="font-mono font-extrabold text-body-lg text-error flex items-center gap-0.5 justify-end">
                                        <span class="material-symbols-outlined text-[16px]">arrow_downward</span>
                                        -{{ $fmt($co->amount) }}
                                    </span>
                                </td>
                                <td class="py-space-md px-space-md align-top text-center">
                                    @if ($isAuto)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/15 text-primary font-label-sm text-label-sm uppercase font-bold tracking-wider">
                                            <span class="material-symbols-outlined text-[14px]">check</span>
                                            Terposting
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-tertiary/15 text-tertiary font-label-sm text-label-sm uppercase font-bold tracking-wider">
                                            <span class="material-symbols-outlined text-[14px]">done_all</span>
                                            Tercatat
                                        </span>
                                    @endif
                                </td>
                                <td class="py-space-md px-space-md align-top text-center">
                                    <div class="flex items-center justify-center gap-space-xs">
                                        <button type="button" data-detail='@json($rowJson)'
                                            onclick="openCashOutDetail(this)"
                                            class="h-8 w-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors cursor-pointer"
                                            title="Lihat Detail">
                                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                                        </button>
                                        <button type="button" data-detail='@json($rowJson)'
                                            onclick="printCashOutDirect(this)"
                                            class="h-8 w-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors cursor-pointer"
                                            title="Cetak Bukti Kas Keluar">
                                            <span class="material-symbols-outlined text-[18px]">print</span>
                                        </button>
                                        @if (!$isAuto)
                                            <form method="POST" action="{{ route('cash-outs.destroy', $co) }}" id="del-co-{{ $co->id }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    data-form-id="del-co-{{ $co->id }}"
                                                    data-code="{{ $co->code }}"
                                                    data-amount="{{ $fmt($co->amount) }}"
                                                    onclick="confirmDeleteCashOut(this)"
                                                    class="h-8 w-8 rounded-lg bg-error/15 hover:bg-error text-error hover:text-on-error flex items-center justify-center transition-colors cursor-pointer"
                                                    title="Hapus Entri Manual">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-space-xl text-center text-on-surface-variant">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="material-symbols-outlined text-[40px] text-surface-container-highest">payments</span>
                                        <span>Belum ada mutasi kas keluar yang cocok dengan filter ini.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-space-md bg-surface-container flex flex-wrap items-center justify-between gap-space-sm">
                <div class="flex items-center gap-space-sm font-body-sm text-body-sm text-on-surface-variant">
                    @if ($cashOuts->total() > 0)
                        <span>Menampilkan <strong class="text-on-surface">{{ $cashOuts->firstItem() }}–{{ $cashOuts->lastItem() }}</strong>
                            dari <strong class="text-on-surface">{{ $cashOuts->total() }}</strong> Mutasi</span>
                        <span class="h-4 w-px bg-surface-container-highest"></span>
                        <span>Total di Halaman: <strong class="text-error font-label-md">{{ $fmt($cashOuts->getCollection()->sum('amount')) }}</strong></span>
                    @else
                        <span>Belum ada data</span>
                    @endif
                </div>
                <div>{{ $cashOuts->links() }}</div>
            </div>
        </div>

        {{-- ============ SOP BANNER ============ --}}
        <div class="p-space-md rounded-xl bg-surface-container-low flex flex-col md:flex-row items-center justify-between gap-space-md shadow-sm">
            <div class="flex items-center gap-space-md">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0 shadow-[0_0_16px_rgba(16,185,129,0.25)]">
                    <span class="material-symbols-outlined text-[28px]">gavel</span>
                </div>
                <div class="flex flex-col">
                    <span class="font-headline-sm text-body-md font-bold text-on-surface">Prinsip Integritas Arus Kas Keluar SUPOS | Sinar Utama</span>
                    <p class="font-body-sm text-body-sm text-on-surface-variant max-w-3xl leading-relaxed">
                        Sistem menerapkan <strong class="text-on-surface">Zero Double-Entry</strong> — Staf gudang dan finance tidak perlu mencatat ulang pembayaran yang telah diotorisasi di modul <strong class="text-on-surface">Barang Masuk</strong>. Semua kode kas keluar diterbitkan otomatis dengan audit trail nomor faktur asal.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-space-sm shrink-0">
                <div class="px-space-sm py-1 rounded-lg bg-surface-container flex items-center gap-1.5 font-label-sm text-label-sm">
                    <span class="material-symbols-outlined text-primary text-[16px]">shield</span>
                    <span>Zero Double-Entry</span>
                </div>
                <div class="px-space-sm py-1 rounded-lg bg-surface-container flex items-center gap-1.5 font-label-sm text-label-sm">
                    <span class="material-symbols-outlined text-primary text-[16px]">sync</span>
                    <span>Jurnal Otomatis</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ PARTIAL: MODALS ============ --}}
    @include('cash-outs.partials.modals')

    @if ($errors->any())
        <script>
            window.addEventListener('load', function() {
                if (typeof openCashOutInputModal === 'function') {
                    openCashOutInputModal(@json(old('active_tab', 'supplier')));
                }
            });
        </script>
    @endif
@endsection