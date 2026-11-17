@extends('layouts.app')

@section('title', 'Kas Masuk')

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
        'penjualan_tunai' => [
            'label' => 'Penjualan Tunai Lunas',
            'badge' => 'bg-primary/15 text-primary',
            'dot' => 'bg-primary',
            'text' => 'text-primary',
        ],
        'pelunasan_piutang' => [
            'label' => 'Pelunasan Piutang',
            'badge' => 'bg-secondary/15 text-secondary',
            'dot' => 'bg-secondary',
            'text' => 'text-secondary',
        ],
        'refund_supplier' => [
            'label' => 'Refund Retur Supplier',
            'badge' => 'bg-tertiary/15 text-tertiary',
            'dot' => 'bg-tertiary',
            'text' => 'text-tertiary',
        ],
        'modal_pemilik' => [
            'label' => 'Setoran Modal Pemilik',
            'badge' => 'bg-primary/15 text-primary',
            'dot' => 'bg-primary',
            'text' => 'text-primary',
        ],
        'penjualan_palet' => [
            'label' => 'Penjualan Palet / Bekas',
            'badge' => 'bg-tertiary/15 text-tertiary',
            'dot' => 'bg-tertiary',
            'text' => 'text-tertiary',
        ],
        'pendapatan_non_ops' => [
            'label' => 'Pendapatan Non-Operasional',
            'badge' => 'bg-secondary/15 text-secondary',
            'dot' => 'bg-secondary',
            'text' => 'text-secondary',
        ],
        'deposit_kembali' => [
            'label' => 'Pengembalian Deposit',
            'badge' => 'bg-primary/15 text-primary',
            'dot' => 'bg-primary',
            'text' => 'text-primary',
        ],
    ];

    $acctStyles = [
        'bca' => [
            'label' => 'BCA Bisnis',
            'short' => 'BCA',
            'box' => 'bg-primary-container/20 text-primary',
            'badge' => 'bg-primary/20 text-primary',
            'dot' => 'bg-primary',
        ],
        'mandiri' => [
            'label' => 'Mandiri Giro',
            'short' => 'MDR',
            'box' => 'bg-secondary-container/25 text-secondary',
            'badge' => 'bg-secondary/20 text-secondary',
            'dot' => 'bg-secondary',
        ],
        'kas_tunai' => [
            'label' => 'Kas Laci Toko',
            'short' => 'Tunai',
            'box' => 'bg-tertiary-container/25 text-tertiary',
            'badge' => 'bg-tertiary/20 text-tertiary',
            'dot' => 'bg-tertiary',
        ],
    ];

    $totalSource = max(0.01, array_sum(array_column($catBreakdown->toArray(), 'total')));
    $hasActiveFilter =
        $cat !== 'all' ||
        $acc !== 'all' ||
        request()->filled('search') ||
        request()->filled('date_from') ||
        request()->filled('date_to');
@endphp

@section('content')
    <div class="flex flex-col w-full pb-16 space-y-space-lg">

        {{-- ============ HEADER (SERAGAM DENGAN BUKU TAGIHAN) ============ --}}
        <div class="flex flex-col gap-space-sm pt-space-xs">
            <div class="flex items-center gap-space-xs font-label-md text-label-md text-on-surface-variant">
                <span class="hover:text-primary cursor-pointer">Sistem Grosir</span>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="hover:text-primary cursor-pointer">Manajemen Finansial</span>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-primary font-semibold">Kas Masuk (Penerimaan Tunai & Bank)</span>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-space-md">
                <div class="flex flex-col gap-1 max-w-3xl">
                    <div class="flex items-center gap-space-sm flex-wrap">
                        <h1 class="font-headline-lg text-headline-lg text-on-surface font-extrabold tracking-tight">
                            Kas Masuk & Penerimaan Dana
                        </h1>
                        <div class="flex items-center gap-1.5 px-space-sm py-0.5 rounded-full bg-primary/10 text-primary">
                            <span class="w-2 h-2 rounded-full bg-primary animate-ping"></span>
                            <span class="font-label-sm text-label-sm uppercase tracking-wider font-bold">Sinkronisasi Kas
                                Masuk Aktif</span>
                        </div>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                        Pantau penjualan tunai, pelunasan piutang dari <span class="text-primary font-medium">Buku
                            Tagihan</span>, dan penerimaan lainnya dalam satu jurnal dengan sumber transaksi yang dapat
                        ditelusuri.
                    </p>
                </div>
                <div class="flex items-center gap-space-sm flex-wrap">
                    <a href="{{ route('cash-ins.export', request()->query()) }}"
                        class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm flex items-center gap-space-xs transition-colors shadow-sm whitespace-nowrap">
                        <span class="material-symbols-outlined text-[18px] text-tertiary">download</span>
                        <span>Export Buku Kas (CSV)</span>
                    </a>
                    <button type="button" onclick="openCreateCashInModal()"
                        class="h-10 px-space-lg rounded-xl bg-primary-container text-on-primary-container font-headline-sm text-body-sm font-bold flex items-center gap-space-xs shadow-[0_4px_20px_rgba(16,185,129,0.35)] hover:bg-primary transition-all active:scale-95 whitespace-nowrap cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        <span>Catat Penerimaan Non-Penjualan</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ============ VALIDATION ERRORS ============ --}}
        @if ($errors->any())
            <div
                class="p-space-md rounded-xl bg-error/15 border border-error/30 text-error font-body-sm text-body-sm flex items-start justify-between gap-space-sm">
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
                <button type="button" onclick="this.parentElement.remove()"
                    class="text-error/70 hover:text-error cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
        @endif

        {{-- ============ PARTIAL: REKONSILIASI & KARTU REKENING ============ --}}
        @include('cash-ins.partials.account-cards')

        {{-- ============ KPI CARDS ============ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-space-md">
            <div
                class="p-space-md rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex flex-col justify-between shadow-sm relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-28 h-28 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/10">
                </div>
                <div class="flex items-center justify-between mb-space-xs">
                    <span
                        class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-bold">Total
                        Kas Masuk Bulan Ini</span>
                    <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                    </div>
                </div>
                <div class="my-space-xs">
                    <span
                        class="font-display-lg text-display-lg text-on-surface font-extrabold tracking-tight">{{ $fmt($monthTotal) }}</span>
                </div>
                <div class="flex items-center justify-between pt-space-xs">
                    <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $monthCount }} Mutasi
                        Terposting</span>
                    <span
                        class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded-full">{{ $fmtJt($monthTotal) }}</span>
                </div>
            </div>

            <div
                class="p-space-md rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex flex-col justify-between shadow-sm relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-28 h-28 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/10">
                </div>
                <div class="flex items-center justify-between mb-space-xs">
                    <span
                        class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-bold">Penerimaan
                        Hari Ini</span>
                    <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-[18px]">today</span>
                    </div>
                </div>
                <div class="my-space-xs">
                    <span
                        class="font-display-lg text-display-lg text-primary font-extrabold tracking-tight">{{ $fmt($todayTotal) }}</span>
                </div>
                <div class="flex items-center justify-between pt-space-xs">
                    <span class="font-body-sm text-body-sm text-on-surface-variant">Dari {{ $todayCount }} transaksi hari
                        ini</span>
                    <span
                        class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded-full">{{ now()->translatedFormat('d M') }}</span>
                </div>
            </div>

            <div
                class="p-space-md rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex flex-col justify-between shadow-sm relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-28 h-28 bg-secondary/5 rounded-full blur-2xl group-hover:bg-secondary/10">
                </div>
                <div class="flex items-center justify-between mb-space-xs">
                    <span
                        class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-bold">Penjualan
                        Tunai Bulan Ini</span>
                    <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                    </div>
                </div>
                <div class="my-space-xs">
                    <span
                        class="font-display-lg text-display-lg text-on-surface font-extrabold tracking-tight">{{ $fmt($cashInFromSales) }}</span>
                </div>
                <div class="flex items-center justify-between pt-space-xs">
                    <span class="font-body-sm text-body-sm text-on-surface-variant">Otomatis dari Barang Keluar</span>
                    <span
                        class="font-label-sm text-label-sm text-secondary font-bold bg-secondary/10 px-2 py-0.5 rounded-full">Auto-Sync</span>
                </div>
            </div>

            <div
                class="p-space-md rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex flex-col justify-between shadow-sm relative overflow-hidden group">
                <div class="flex items-center justify-between mb-space-xs">
                    <span
                        class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-bold">Sumber
                        Dana Dominan</span>
                    <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-tertiary">
                        <span class="material-symbols-outlined text-[18px]">pie_chart</span>
                    </div>
                </div>
                <div class="my-auto flex flex-col space-y-1.5">
                    @php $hasCatData = false; @endphp
                    @foreach ($catStyles as $key => $meta)
                        @php
                            $total = $catBreakdown[$key]['total'] ?? 0;
                            if ($total <= 0) {
                                continue;
                            }
                            $hasCatData = true;
                            $pct = round(($total / $totalSource) * 100);
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
                        <span class="font-body-sm text-body-sm text-on-surface-variant">Belum ada pemasukan bulan
                            ini.</span>
                    @endif
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
                                'value' => 'all',
                                'label' => 'Semua Mutasi',
                                'count' => $tabCounts['all'],
                                'count_class' => 'bg-on-primary-container text-primary-container',
                            ],
                            [
                                'value' => 'penjualan_tunai',
                                'label' => 'Penjualan Tunai',
                                'count' => $tabCounts['penjualan_tunai'],
                                'count_class' => 'bg-primary/20 text-primary font-bold',
                            ],
                            [
                                'value' => 'pelunasan_piutang',
                                'label' => 'Pelunasan Piutang',
                                'count' => $tabCounts['pelunasan_piutang'],
                                'count_class' => 'bg-secondary/20 text-secondary font-bold',
                            ],
                            [
                                'value' => 'manual',
                                'label' => 'Penerimaan Lain',
                                'count' => $tabCounts['manual'],
                                'count_class' => 'bg-tertiary/20 text-tertiary font-bold',
                            ],
                        ];
                    @endphp
                    @foreach ($tabs as $t)
                        @php $active = $cat === $t['value']; @endphp
                        <button type="button" onclick="selectCashInCategory('{{ $t['value'] }}')"
                            class="px-space-md py-space-xs rounded-xl font-headline-sm text-body-sm flex items-center gap-space-xs whitespace-nowrap transition-colors cursor-pointer
                            {{ $active ? 'bg-primary-container text-on-primary-container font-bold shadow-sm' : 'bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface' }}">
                            <span>{{ $t['label'] }}</span>
                            <span
                                class="px-1.5 py-0.2 rounded-full {{ $t['count_class'] }} font-label-sm text-label-sm">{{ $t['count'] }}</span>
                        </button>
                    @endforeach
                </div>

                {{-- Preset Periode Cepat --}}
                <div class="flex items-center gap-space-xs flex-wrap">
                    <button type="button" onclick="setCashInQuickDate('today')"
                        class="h-8 px-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface font-label-sm text-label-sm font-semibold transition-colors cursor-pointer">
                        Hari Ini
                    </button>
                    <button type="button" onclick="setCashInQuickDate('7days')"
                        class="h-8 px-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface font-label-sm text-label-sm font-semibold transition-colors cursor-pointer">
                        7 Hari Terakhir
                    </button>
                    <button type="button" onclick="setCashInQuickDate('month')"
                        class="h-8 px-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface font-label-sm text-label-sm font-semibold transition-colors cursor-pointer">
                        Bulan Ini
                    </button>
                    @if ($hasActiveFilter)
                        <a href="{{ route('cash-ins.index') }}"
                            class="h-8 px-space-sm rounded-lg bg-error/15 hover:bg-error/25 text-error font-label-sm text-label-sm font-bold flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined text-[15px]">filter_alt_off</span>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </div>

            <form method="GET" action="{{ route('cash-ins.index') }}" id="ciFilterForm"
                class="flex flex-wrap items-center justify-between gap-space-sm bg-surface-container-low p-space-sm rounded-xl shadow-sm">
                <input type="hidden" name="category" id="ciCategoryInput" value="{{ $cat }}">

                <div class="flex items-center gap-space-sm flex-1 min-w-[260px]">
                    <div class="relative w-full max-w-md">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                        <input name="search" id="ciSearchInput" value="{{ request('search') }}"
                            placeholder="Cari No. KM, faktur, pelanggan, produk, keterangan..."
                            class="w-full h-10 pl-10 pr-space-md rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/60 font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-space-sm">
                    <div class="flex items-center gap-space-xs px-space-sm h-10 rounded-xl bg-surface-container">
                        <span class="material-symbols-outlined text-[18px] text-primary">date_range</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold">Dari</span>
                        <input type="date" name="date_from" id="ciDateFrom" value="{{ request('date_from') }}"
                            onchange="this.form.submit()"
                            class="bg-transparent text-on-surface font-body-sm text-body-sm focus:outline-none" />
                        <span
                            class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold pl-1">s/d</span>
                        <input type="date" name="date_to" id="ciDateTo" value="{{ request('date_to') }}"
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

                    <a href="{{ route('cash-ins.index') }}"
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
                        <tr
                            class="bg-surface-container text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">
                            <th class="py-space-md px-space-md font-bold">No. Kas Masuk & Waktu</th>
                            <th class="py-space-md px-space-md font-bold">Sumber / Dari</th>
                            <th class="py-space-md px-space-md font-bold">Kategori & Rincian</th>
                            <th class="py-space-md px-space-md font-bold">Akun Penampung</th>
                            <th class="py-space-md px-space-md font-bold text-right">Jumlah Kas Masuk</th>
                            <th class="py-space-md px-space-md font-bold text-center">Status</th>
                            <th class="py-space-md px-space-md font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-highest/20 font-body-sm text-body-sm">
                        @forelse($cashIns as $ci)
                            @php
                                $cStyle = $catStyles[$ci->category] ?? [
                                    'label' => ucfirst($ci->category),
                                    'badge' => 'bg-primary/15 text-primary',
                                    'dot' => 'bg-primary',
                                    'text' => 'text-primary',
                                ];
                                $aStyle = $acctStyles[$ci->account] ?? [
                                    'label' => ucfirst($ci->account),
                                    'short' => '?',
                                    'dot' => 'bg-primary',
                                ];
                                $isAuto = $ci->is_auto;
                                $sourceName =
                                    $ci->source?->customer?->name ??
                                    ($ci->source?->product?->name ?? 'Penerimaan Manual');
                                $rowJson = [
                                    'code' => $ci->code,
                                    'received_at' => $ci->received_at->translatedFormat('d M Y, H:i') . ' WIB',
                                    'is_auto' => $isAuto,
                                    'source_type' => $ci->source_type
                                        ? class_basename($ci->source_type)
                                        : 'Manual Input',
                                    'source_name' => $sourceName,
                                    'category' => $cStyle['label'],
                                    'account' => $aStyle['label'],
                                    'amount' => $fmt($ci->amount),
                                    'reference_no' => $ci->reference_no ?? '-',
                                    'notes' => $ci->notes ?? '-',
                                ];
                            @endphp
                            <tr class="hover:bg-surface-container/60 transition-colors group">
                                <td class="py-space-md px-space-md align-top">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-label-md text-label-md font-bold text-on-surface group-hover:text-primary transition-colors">{{ $ci->code }}</span>
                                        <span
                                            class="font-label-sm text-label-sm text-on-surface-variant mt-0.5">{{ $ci->received_at->translatedFormat('d M Y, H:i') }}
                                            WIB</span>
                                        <span
                                            class="font-label-sm text-label-sm {{ $isAuto ? 'text-primary' : 'text-tertiary' }} flex items-center gap-0.5 mt-0.5">
                                            <span
                                                class="material-symbols-outlined text-[12px]">{{ $isAuto ? 'link' : 'edit_note' }}</span>
                                            {{ $isAuto ? 'Auto-Journal' : 'Manual Input' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-space-md px-space-md align-top">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-headline-sm text-body-md font-bold text-on-surface">{{ $sourceName }}</span>
                                        @if ($ci->source_type)
                                            <span
                                                class="font-label-sm text-label-sm text-on-surface-variant uppercase font-mono">{{ class_basename($ci->source_type) }}</span>
                                        @endif
                                        @if ($ci->reference_no)
                                            <span class="font-mono font-label-sm text-label-sm text-tertiary mt-0.5">Ref:
                                                {{ $ci->reference_no }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-space-md px-space-md align-top">
                                    <div class="flex flex-col items-start gap-1">
                                        <span
                                            class="px-2 py-0.5 rounded-full font-label-sm text-label-sm uppercase font-bold {{ $cStyle['badge'] }}">
                                            {{ $cStyle['label'] }}
                                        </span>
                                        <span
                                            class="font-body-sm text-body-sm text-on-surface-variant">{{ Str::limit($ci->notes, 60) }}</span>
                                    </div>
                                </td>
                                <td class="py-space-md px-space-md align-top">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full {{ $aStyle['dot'] }}"></span>
                                        <span
                                            class="font-mono font-body-sm text-body-sm text-on-surface font-semibold">{{ $aStyle['label'] }}</span>
                                    </div>
                                </td>
                                <td class="py-space-md px-space-md align-top text-right">
                                    <span
                                        class="font-mono font-extrabold text-body-lg text-primary flex items-center gap-0.5 justify-end">
                                        <span class="material-symbols-outlined text-[16px]">arrow_upward</span>
                                        +{{ $fmt($ci->amount) }}
                                    </span>
                                </td>
                                <td class="py-space-md px-space-md align-top text-center">
                                    @if ($isAuto)
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/15 text-primary font-label-sm text-label-sm uppercase font-bold tracking-wider">
                                            <span class="material-symbols-outlined text-[14px]">check</span>
                                            Terposting
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-tertiary/15 text-tertiary font-label-sm text-label-sm uppercase font-bold tracking-wider">
                                            <span class="material-symbols-outlined text-[14px]">done_all</span>
                                            Tercatat
                                        </span>
                                    @endif
                                </td>
                                <td class="py-space-md px-space-md align-top text-center">
                                    <div class="flex items-center justify-center gap-space-xs">
                                        <button type="button" data-detail='@json($rowJson)'
                                            onclick="openCashInDetailModal(this)"
                                            class="h-8 w-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors cursor-pointer"
                                            title="Lihat Detail">
                                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                                        </button>
                                        <button type="button" data-detail='@json($rowJson)'
                                            onclick="openCashInReceiptModal(this)"
                                            class="h-8 w-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors cursor-pointer"
                                            title="Cetak Bukti Kas Masuk">
                                            <span class="material-symbols-outlined text-[18px]">print</span>
                                        </button>
                                        @if (!$isAuto)
                                            <button type="button" data-url="{{ route('cash-ins.destroy', $ci) }}"
                                                data-code="{{ $ci->code }}" onclick="openDeleteCashInModal(this)"
                                                class="h-8 w-8 rounded-lg bg-error/15 hover:bg-error text-error hover:text-on-error flex items-center justify-center transition-colors cursor-pointer"
                                                title="Hapus Entri Manual">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-space-xl text-center text-on-surface-variant">
                                    <div class="flex flex-col items-center gap-2">
                                        <span
                                            class="material-symbols-outlined text-[40px] text-surface-container-highest">payments</span>
                                        <span>Belum ada mutasi kas masuk yang cocok dengan filter ini.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-space-md bg-surface-container flex flex-wrap items-center justify-between gap-space-sm">
                <div class="flex items-center gap-space-sm font-body-sm text-body-sm text-on-surface-variant">
                    @if ($cashIns->total() > 0)
                        <span>Menampilkan <strong
                                class="text-on-surface">{{ $cashIns->firstItem() }}–{{ $cashIns->lastItem() }}</strong>
                            dari <strong class="text-on-surface">{{ $cashIns->total() }}</strong> Mutasi</span>
                        <span class="h-4 w-px bg-surface-container-highest"></span>
                        <span>Total di Halaman: <strong
                                class="text-primary font-label-md">{{ $fmt($cashIns->getCollection()->sum('amount')) }}</strong></span>
                    @else
                        <span>Belum ada data</span>
                    @endif
                </div>
                <div>{{ $cashIns->links() }}</div>
            </div>
        </div>

        {{-- ============ SOP BANNER ============ --}}
        <div
            class="p-space-md rounded-xl bg-surface-container-low flex flex-col md:flex-row items-center justify-between gap-space-md shadow-sm">
            <div class="flex items-center gap-space-md">
                <div
                    class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0 shadow-[0_0_16px_rgba(16,185,129,0.25)]">
                    <span class="material-symbols-outlined text-[28px]">gavel</span>
                </div>
                <div class="flex flex-col">
                    <span class="font-headline-sm text-body-md font-bold text-on-surface">Prinsip Integritas Arus Kas Masuk
                        SUPOS | Sinar Utama</span>
                    <p class="font-body-sm text-body-sm text-on-surface-variant max-w-3xl leading-relaxed">
                        Sistem menerapkan <strong class="text-on-surface">Zero Double-Entry</strong> — Kasir dan staf
                        finance tidak perlu mencatat ulang pembayaran yang telah diotorisasi di modul <strong
                            class="text-on-surface">Barang Keluar</strong> atau <strong class="text-primary">Buku
                            Tagihan</strong>. Semua kode kas masuk diterbitkan otomatis dengan audit trail nomor faktur
                        asal.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-space-sm shrink-0">
                <div
                    class="px-space-sm py-1 rounded-lg bg-surface-container flex items-center gap-1.5 font-label-sm text-label-sm">
                    <span class="material-symbols-outlined text-primary text-[16px]">shield</span>
                    <span>Zero Double-Entry</span>
                </div>
                <div
                    class="px-space-sm py-1 rounded-lg bg-surface-container flex items-center gap-1.5 font-label-sm text-label-sm">
                    <span class="material-symbols-outlined text-primary text-[16px]">sync</span>
                    <span>Jurnal Otomatis</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ PARTIAL: MODALS ============ --}}
    @include('cash-ins.partials.modals')

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const createModal = document.getElementById('createCashInModal');
            const detailModal = document.getElementById('cashInDetailModal');
            const receiptModal = document.getElementById('cashInReceiptModal');
            const deleteModal = document.getElementById('deleteCashInModal');

            function showModal(el) {
                if (!el) return;
                el.classList.remove('hidden');
                el.classList.add('flex');
                el.setAttribute('aria-hidden', 'false');
            }

            function hideModal(el) {
                if (!el) return;
                el.classList.add('hidden');
                el.classList.remove('flex');
                el.setAttribute('aria-hidden', 'true');
            }

            function parseBtnData(btn) {
                if (!btn || !btn.dataset || !btn.dataset.detail) return null;
                try {
                    return JSON.parse(btn.dataset.detail);
                } catch (e) {
                    console.error('Gagal membaca data baris:', e);
                    return null;
                }
            }

            // ---- Modal Input Kas Masuk Manual ----
            window.openCreateCashInModal = function() {
                showModal(createModal);
            };
            window.closeCreateCashInModal = function() {
                hideModal(createModal);
            };

            // ---- Modal Detail ----
            window.openCashInDetailModal = function(btn) {
                const d = parseBtnData(btn);
                if (!d) return;
                document.getElementById('detCode').textContent = d.code;
                document.getElementById('detAmount').textContent = d.amount;
                document.getElementById('detTime').textContent = d.received_at;
                document.getElementById('detAccount').textContent = d.account;
                document.getElementById('detCategory').textContent = d.category;
                document.getElementById('detRef').textContent = d.reference_no;
                document.getElementById('detSource').textContent = d.source_name + ' (' + d.source_type + ')';
                document.getElementById('detNotes').textContent = d.notes;
                showModal(detailModal);
            };
            window.closeCashInDetailModal = function() {
                hideModal(detailModal);
            };

            // ---- Modal Print Bukti ----
            window.openCashInReceiptModal = function(btn) {
                const d = parseBtnData(btn);
                if (!d) return;
                document.getElementById('recCode').textContent = d.code;
                document.getElementById('recTime').textContent = d.received_at;
                document.getElementById('recAccount').textContent = d.account;
                document.getElementById('recCategory').textContent = d.category;
                document.getElementById('recSource').textContent = d.source_name;
                document.getElementById('recRef').textContent = d.reference_no;
                document.getElementById('recAmount').textContent = d.amount;
                document.getElementById('recNotes').textContent = d.notes;
                showModal(receiptModal);
            };
            window.closeCashInReceiptModal = function() {
                hideModal(receiptModal);
            };
            window.printCashInSlip = function() {
                const area = document.getElementById('printableReceiptArea');
                if (!area) return;
                const win = window.open('', '_blank', 'width=420,height=620');
                if (!win) {
                    window.alert('Izinkan pop-up pada browser untuk mencetak bukti kas masuk.');
                    return;
                }
                const htmlContent = [
                    '<!doctype html><html><head><title>Bukti Kas Masuk<\/title>',
                    '<style>body{font-family:monospace;padding:16px;font-size:12px;color:#0f172a;}.flex{display:flex;justify-content:space-between;gap:12px;}.text-center{text-align:center;}.border-b{border-bottom:1px dashed #666;padding-bottom:8px;margin-bottom:8px;}.border-y{border-top:1px dashed #666;border-bottom:1px dashed #666;padding:8px 0;margin:8px 0;}.pt-4{padding-top:24px;}.space-y-1>*+*{margin-top:4px;}.space-y-3>*+*{margin-top:12px;}.text-slate-600{color:#475569;}.border-slate-400{border-color:#94a3b8;}@media print{body{padding:0;}}<\/style>',
                    '<\/head><body>',
                    area.innerHTML,
                    '<\/body><\/html>'
                ].join('');

                win.document.write(htmlContent);
                win.document.close();
                win.focus();
                win.print();
                win.close();
            };

            // ---- Modal Hapus Entri Manual ----
            window.openDeleteCashInModal = function(btn) {
                if (!btn || !btn.dataset) return;
                document.getElementById('deleteCashInForm').action = btn.dataset.url;
                document.getElementById('delCashInCode').textContent = btn.dataset.code;
                showModal(deleteModal);
            };
            window.closeDeleteCashInModal = function() {
                hideModal(deleteModal);
            };

            // ---- Form Helpers ----
            window.setCashInNominal = function(val) {
                const input = document.getElementById('nominalInput');
                if (!input) return;
                input.value = val;
                window.updateNominalPreview();
            };
            window.updateNominalPreview = function() {
                const input = document.getElementById('nominalInput');
                const preview = document.getElementById('nominalPreview');
                if (!input || !preview) return;
                const v = parseFloat(input.value) || 0;
                preview.textContent = 'IDR ' + v.toLocaleString('id-ID');
            };
            window.selectCashInAccount = function(radio) {
                document.querySelectorAll('.ci-account-card').forEach(function(card) {
                    card.classList.remove('border-primary', 'bg-primary-container/10');
                    card.classList.add('border-transparent');
                });
                const active = radio.closest('.ci-account-card');
                if (active) {
                    active.classList.remove('border-transparent');
                    active.classList.add('border-primary', 'bg-primary-container/10');
                }
            };

            // ---- Filter & Preset Tanggal ----
            window.selectCashInCategory = function(val) {
                const catInput = document.getElementById('ciCategoryInput');
                const form = document.getElementById('ciFilterForm');
                if (catInput && form) {
                    catInput.value = val;
                    form.submit();
                }
            };
            window.setCashInQuickDate = function(mode) {
                const now = new Date();
                const pad = function(n) {
                    return String(n).padStart(2, '0');
                };
                const todayStr = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate());
                const fromEl = document.getElementById('ciDateFrom');
                const toEl = document.getElementById('ciDateTo');
                const form = document.getElementById('ciFilterForm');
                if (!fromEl || !toEl || !form) return;

                if (mode === 'today') {
                    fromEl.value = todayStr;
                    toEl.value = todayStr;
                } else if (mode === '7days') {
                    const past = new Date(now.getTime() - (6 * 86400000));
                    fromEl.value = past.getFullYear() + '-' + pad(past.getMonth() + 1) + '-' + pad(past
                    .getDate());
                    toEl.value = todayStr;
                } else if (mode === 'month') {
                    fromEl.value = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-01';
                    toEl.value = todayStr;
                }
                form.submit();
            };

            // Debounce Search
            const searchEl = document.getElementById('ciSearchInput');
            if (searchEl) {
                let timer;
                searchEl.addEventListener('input', function() {
                    clearTimeout(timer);
                    timer = setTimeout(function() {
                        document.getElementById('ciFilterForm')?.submit();
                    }, 500);
                });
            }

            // Tutup modal dengan tombol Escape
            document.addEventListener('keydown', function(event) {
                if (event.key !== 'Escape') return;
                [createModal, detailModal, receiptModal, deleteModal].forEach(hideModal);
            });

            window.updateNominalPreview();

            const hasFormErrors =
                {{ $errors->hasAny(['category', 'account', 'amount', 'received_at', 'reference_no', 'notes']) ? 'true' : 'false' }};
            if (hasFormErrors) {
                window.openCreateCashInModal();
            }
        });
    </script>
@endpush
