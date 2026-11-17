@extends('layouts.app')

@section('title', 'Kas Masuk')

@php
    $fmt   = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $fmtJt = function ($n) {
        if ($n >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 2, ',', '.') . ' M';
        if ($n >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 2, ',', '.') . ' Jt';
        if ($n >= 1_000)         return 'Rp ' . number_format($n / 1_000, 0, ',', '.') . ' Rb';
        return 'Rp ' . number_format((int) $n, 0, ',', '.');
    };

    $catStyles = [
        'penjualan_tunai'    => ['label' => 'Penjualan Tunai Lunas',      'badge' => 'bg-primary/15 text-primary',     'dot' => 'bg-primary',   'text' => 'text-primary'],
        'pelunasan_piutang'  => ['label' => 'Pelunasan Piutang',          'badge' => 'bg-secondary/15 text-secondary', 'dot' => 'bg-secondary', 'text' => 'text-secondary'],
        'refund_supplier'    => ['label' => 'Refund Retur Supplier',      'badge' => 'bg-tertiary/15 text-tertiary',   'dot' => 'bg-tertiary',  'text' => 'text-tertiary'],
        'modal_pemilik'      => ['label' => 'Setoran Modal Pemilik',      'badge' => 'bg-primary/15 text-primary',     'dot' => 'bg-primary',   'text' => 'text-primary'],
        'penjualan_palet'    => ['label' => 'Penjualan Palet / Bekas',    'badge' => 'bg-tertiary/15 text-tertiary',   'dot' => 'bg-tertiary',  'text' => 'text-tertiary'],
        'pendapatan_non_ops' => ['label' => 'Pendapatan Non-Operasional', 'badge' => 'bg-secondary/15 text-secondary', 'dot' => 'bg-secondary', 'text' => 'text-secondary'],
        'deposit_kembali'    => ['label' => 'Pengembalian Deposit',       'badge' => 'bg-primary/15 text-primary',     'dot' => 'bg-primary',   'text' => 'text-primary'],
    ];

    $acctStyles = [
        'bca'       => ['label' => 'BCA Bisnis',    'short' => 'BCA',   'box' => 'bg-primary-container/20 text-primary',     'badge' => 'bg-primary/20 text-primary',     'dot' => 'bg-primary'],
        'mandiri'   => ['label' => 'Mandiri Giro',  'short' => 'MDR',   'box' => 'bg-secondary-container/25 text-secondary', 'badge' => 'bg-secondary/20 text-secondary', 'dot' => 'bg-secondary'],
        'kas_tunai' => ['label' => 'Kas Laci Toko', 'short' => 'Tunai', 'box' => 'bg-tertiary-container/25 text-tertiary',   'badge' => 'bg-tertiary/20 text-tertiary',   'dot' => 'bg-tertiary'],
    ];

    $totalSource     = max(0.01, array_sum(array_column($catBreakdown->toArray(), 'total')));
    $hasActiveFilter = $cat !== 'all' || $acc !== 'all' || request()->filled('search') || request()->filled('date_from') || request()->filled('date_to');
@endphp

@section('content')
<div class="flex flex-col w-full pb-16 space-y-space-lg">

    {{-- ============ VALIDATION ERRORS ============ --}}
    @if ($errors->any())
        <div class="p-space-md rounded-xl bg-error/15 border border-error/30 text-error font-body-sm flex items-start justify-between gap-space-sm">
            <div class="flex flex-col gap-1">
                <span class="font-bold flex items-center gap-space-xs">
                    <span class="material-symbols-outlined text-[18px]">error</span>
                    Periksa kembali:
                </span>
                <ul class="list-disc list-inside ml-1">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-error/70 hover:text-error">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endif

    {{-- ============ BREADCRUMB & UPDATE STATUS ============ --}}
    <div class="flex flex-wrap items-center justify-between gap-space-sm pt-space-xs">
        <div class="flex items-center gap-space-xs font-body-sm text-on-surface-variant">
            <span>Sistem Grosir</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span>Manajemen Finansial</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-on-surface font-semibold">Kas Masuk (Penerimaan Tunai & Bank)</span>
        </div>
        <div class="inline-flex items-center gap-space-xs px-space-md py-1 rounded-full bg-surface-container-low border border-primary/20 shadow-[0_0_12px_rgba(16,185,129,0.15)]">
            <div class="w-2 h-2 rounded-full bg-primary"></div>
            <span class="font-label-sm text-primary uppercase tracking-wider">JURNAL DIPERBARUI</span>
            <span class="font-label-sm text-on-surface-variant pl-1">{{ now()->translatedFormat('d M Y • H:i') }} WIB</span>
        </div>
    </div>

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-space-md pb-space-xs">
        <div class="flex flex-col max-w-3xl">
            <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight font-extrabold flex items-center gap-space-sm">
                <span class="material-symbols-outlined text-primary text-[32px]" style="font-variation-settings: 'FILL' 1;">payments</span>
                Kas Masuk & Penerimaan Dana
            </h1>
            <p class="font-body-md text-on-surface-variant mt-1 leading-relaxed">
                Pantau penjualan tunai, pelunasan piutang, dan penerimaan lainnya dalam satu jurnal dengan sumber transaksi yang dapat ditelusuri.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-space-sm">
            <a href="{{ route('cash-ins.export', request()->query()) }}"
               class="px-space-md py-2.5 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-body-sm font-medium flex items-center gap-space-xs shadow-sm transition-colors">
                <span class="material-symbols-outlined text-[18px] text-tertiary">download</span>
                <span>Export Buku Kas (CSV)</span>
            </a>
            <div class="px-space-md py-2.5 rounded-xl bg-surface-container-high text-on-surface font-body-sm font-medium flex items-center gap-space-xs shadow-sm">
                <span class="material-symbols-outlined text-[18px] text-on-surface-variant">calendar_today</span>
                <span>Hari Ini: {{ now()->translatedFormat('d M Y') }}</span>
            </div>
            <button type="button" onclick="ciOpenModal('createCashInModal')"
                    class="px-space-lg py-2.5 rounded-xl bg-primary-container hover:bg-primary-fixed-dim text-on-primary-container font-headline-sm font-semibold flex items-center gap-space-xs shadow-[0_4px_20px_rgba(16,185,129,0.35)] transition-all">
                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">add_circle</span>
                <span>+ Catat Penerimaan Non-Penjualan</span>
            </button>
        </div>
    </div>

    {{-- ============ PARTIAL: REKONSILIASI & KARTU REKENING ============ --}}
    @include('cash-ins.partials.account-cards')

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-md">
        <div class="relative overflow-hidden rounded-xl bg-surface-container-low p-space-md shadow-sm">
            <div class="flex items-center justify-between text-on-surface-variant">
                <span class="font-label-sm uppercase tracking-wider">TOTAL KAS MASUK BULAN INI</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                </div>
            </div>
            <div class="mt-space-sm">
                <span class="font-headline-md font-extrabold text-on-surface tracking-tight">{{ $fmt($monthTotal) }}</span>
                <div class="mt-2 flex items-center gap-space-xs text-primary font-body-sm">
                    <span class="material-symbols-outlined text-[16px]">receipt</span>
                    <span class="font-semibold">{{ $monthCount }} Mutasi Terposting</span>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl bg-surface-container-low p-space-md shadow-sm">
            <div class="flex items-center justify-between text-on-surface-variant">
                <span class="font-label-sm uppercase tracking-wider">PENERIMAAN HARI INI</span>
                <div class="w-8 h-8 rounded-lg bg-primary-container/20 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">today</span>
                </div>
            </div>
            <div class="mt-space-sm">
                <span class="font-headline-md font-extrabold text-primary tracking-tight">{{ $fmt($todayTotal) }}</span>
                <div class="mt-2 font-body-sm text-on-surface-variant">
                    Dari {{ $todayCount }} transaksi hari ini
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl bg-surface-container-low p-space-md shadow-sm">
            <div class="flex items-center justify-between text-on-surface-variant">
                <span class="font-label-sm uppercase tracking-wider">PENJUALAN TUNAI BULAN INI</span>
                <div class="w-8 h-8 rounded-lg bg-secondary-container/30 flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                </div>
            </div>
            <div class="mt-space-sm">
                <span class="font-headline-md font-extrabold text-on-surface tracking-tight">{{ $fmt($cashInFromSales) }}</span>
                <div class="mt-2 font-body-sm text-secondary font-medium">
                    Otomatis dari transaksi penjualan lunas
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl bg-surface-container-low p-space-md shadow-sm">
            <div class="flex items-center justify-between text-on-surface-variant">
                <span class="font-label-sm uppercase tracking-wider">SUMBER DANA DOMINAN</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined text-[18px]">pie_chart</span>
                </div>
            </div>
            <div class="mt-space-sm flex flex-col space-y-1.5">
                @php $hasCatData = false; @endphp
                @foreach($catStyles as $key => $meta)
                    @php
                        $total = $catBreakdown[$key]['total'] ?? 0;
                        if ($total <= 0) continue;
                        $hasCatData = true;
                        $pct = round(($total / $totalSource) * 100);
                    @endphp
                    <div class="flex items-center justify-between font-body-sm">
                        <span class="text-on-surface font-medium flex items-center gap-1.5 truncate pr-2">
                            <span class="w-2 h-2 rounded-full shrink-0 {{ $meta['dot'] }}"></span>
                            <span class="truncate">{{ $meta['label'] }}</span>
                        </span>
                        <span class="font-mono font-bold shrink-0 {{ $meta['text'] }}">{{ $pct }}%</span>
                    </div>
                @endforeach
                @if(! $hasCatData)
                    <span class="font-body-sm text-on-surface-variant">Belum ada pemasukan bulan ini.</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ============ TABEL JURNAL ============ --}}
    <div class="flex flex-col rounded-xl bg-surface-container-low shadow-sm overflow-hidden">
        <div class="p-space-lg flex flex-col space-y-space-md border-b border-surface-container-highest">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
                <div>
                    <h2 class="font-headline-md font-extrabold text-on-surface flex items-center gap-space-xs">
                        Jurnal Buku Kas Masuk
                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                    </h2>
                    <p class="font-body-sm text-on-surface-variant mt-0.5">Jurnal otomatis bersumber dari Barang Keluar dan Buku Tagihan; data diperbarui saat halaman dimuat.</p>
                </div>

                {{-- Preset Tanggal Cepat & Reset --}}
                <div class="flex flex-wrap items-center gap-space-xs">
                    <button type="button" onclick="ciSetQuickDate('today')"
                            class="px-space-sm py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface font-label-sm uppercase font-semibold transition-colors">
                        Hari Ini
                    </button>
                    <button type="button" onclick="ciSetQuickDate('7days')"
                            class="px-space-sm py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface font-label-sm uppercase font-semibold transition-colors">
                        7 Hari
                    </button>
                    <button type="button" onclick="ciSetQuickDate('month')"
                            class="px-space-sm py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface font-label-sm uppercase font-semibold transition-colors">
                        Bulan Ini
                    </button>
                    @if($hasActiveFilter)
                        <a href="{{ route('cash-ins.index') }}"
                           class="px-space-sm py-1.5 rounded-lg bg-error/15 hover:bg-error/25 text-error font-label-sm uppercase font-bold flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined text-[15px]">filter_alt_off</span>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </div>

            <form method="GET" action="{{ route('cash-ins.index') }}" id="ciFilterForm" class="flex flex-col space-y-space-sm">
                {{-- Baris 1: Tabs Kategori & Pencarian --}}
                <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-space-md">
                    <div class="flex items-center gap-1 p-1 rounded-xl bg-surface-container overflow-x-auto">
                        @php
                            $tabs = [
                                ['value' => 'all',               'label' => "Semua ({$tabCounts['all']})"],
                                ['value' => 'penjualan_tunai',   'label' => "Penjualan Tunai ({$tabCounts['penjualan_tunai']})"],
                                ['value' => 'pelunasan_piutang', 'label' => "Pelunasan Piutang ({$tabCounts['pelunasan_piutang']})"],
                                ['value' => 'manual',            'label' => "Penerimaan Lain ({$tabCounts['manual']})"],
                            ];
                        @endphp
                        @foreach($tabs as $t)
                            @php $active = $cat === $t['value']; @endphp
                            <button type="button" onclick="ciSelectCategory('{{ $t['value'] }}')"
                                    class="px-space-md py-1.5 rounded-lg font-headline-sm text-body-sm whitespace-nowrap transition-all
                                    {{ $active ? 'bg-primary-container text-on-primary-container font-bold shadow-sm' : 'hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface font-semibold' }}">
                                {{ $t['label'] }}
                            </button>
                        @endforeach
                        <input type="hidden" name="category" id="ciCategoryInput" value="{{ $cat }}">
                    </div>

                    <div class="relative flex-1 lg:max-w-sm">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                        <input name="search" id="ciSearchInput" value="{{ request('search') }}"
                               placeholder="Cari No. KM, faktur, pelanggan, produk, keterangan..."
                               class="w-full h-9 pl-9 pr-space-md rounded-lg bg-surface-container text-on-surface font-body-sm placeholder:text-on-surface-variant/50 focus:outline-none focus:ring-1 focus:ring-primary" />
                    </div>
                </div>

                {{-- Baris 2: Filter Periode Tanggal, Akun, & Urutan --}}
                <div class="flex flex-wrap items-center gap-space-sm">
                    <div class="flex items-center gap-space-xs px-space-sm h-9 rounded-lg bg-surface-container">
                        <span class="material-symbols-outlined text-[16px] text-on-surface-variant">date_range</span>
                        <span class="font-label-sm text-on-surface-variant uppercase">Periode:</span>
                        <input type="date" name="date_from" id="ciDateFrom" value="{{ request('date_from') }}"
                               onchange="this.form.submit()"
                               class="bg-transparent text-on-surface font-body-sm focus:outline-none" />
                        <span class="text-on-surface-variant font-body-sm">—</span>
                        <input type="date" name="date_to" id="ciDateTo" value="{{ request('date_to') }}"
                               onchange="this.form.submit()"
                               class="bg-transparent text-on-surface font-body-sm focus:outline-none" />
                    </div>

                    <select name="account" onchange="this.form.submit()"
                            class="h-9 pl-3 pr-8 rounded-lg bg-surface-container text-on-surface font-body-sm focus:outline-none">
                        <option value="all" @selected($acc === 'all')>Semua Akun</option>
                        @foreach($acctStyles as $k => $m)
                            <option value="{{ $k }}" @selected($acc === $k)>{{ $m['label'] }}</option>
                        @endforeach
                    </select>

                    <select name="sort" onchange="this.form.submit()"
                            class="h-9 pl-3 pr-8 rounded-lg bg-surface-container text-on-surface font-body-sm focus:outline-none">
                        <option value="latest"      @selected($sort === 'latest')>Waktu Terbaru</option>
                        <option value="amount_desc" @selected($sort === 'amount_desc')>Nominal Tertinggi</option>
                        <option value="amount_asc"  @selected($sort === 'amount_asc')>Nominal Terendah</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container/50 border-b border-surface-container-highest text-on-surface-variant font-label-sm uppercase tracking-wider text-[11px]">
                        <th class="py-space-sm px-space-md">NO. KAS MASUK & WAKTU</th>
                        <th class="py-space-sm px-space-md">SUMBER / DARI</th>
                        <th class="py-space-sm px-space-md">KATEGORI & RINCIAN</th>
                        <th class="py-space-sm px-space-md">AKUN PENAMPUNG</th>
                        <th class="py-space-sm px-space-md text-right">JUMLAH KAS MASUK</th>
                        <th class="py-space-sm px-space-md text-center">STATUS</th>
                        <th class="py-space-sm px-space-md text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-highest font-body-sm">
                    @forelse($cashIns as $ci)
                        @php
                            $cStyle     = $catStyles[$ci->category] ?? ['label' => ucfirst($ci->category), 'badge' => 'bg-primary/15 text-primary', 'dot' => 'bg-primary', 'text' => 'text-primary'];
                            $aStyle     = $acctStyles[$ci->account] ?? ['label' => ucfirst($ci->account), 'short' => '?', 'dot' => 'bg-primary'];
                            $isAuto     = $ci->is_auto;
                            $sourceName = $ci->source?->customer?->name ?? $ci->source?->product?->name ?? 'Penerimaan Manual';
                            $rowJson    = [
                                'code'         => $ci->code,
                                'received_at'  => $ci->received_at->translatedFormat('d M Y, H:i') . ' WIB',
                                'is_auto'      => $isAuto,
                                'source_type'  => $ci->source_type ? class_basename($ci->source_type) : 'Manual Input',
                                'source_name'  => $sourceName,
                                'category'     => $cStyle['label'],
                                'account'      => $aStyle['label'],
                                'amount'       => $fmt($ci->amount),
                                'reference_no' => $ci->reference_no ?? '-',
                                'notes'        => $ci->notes ?? '-',
                            ];
                        @endphp
                        <tr class="hover:bg-surface-container transition-colors group">
                            <td class="py-space-md px-space-md">
                                <div class="flex flex-col">
                                    <span class="font-mono font-bold text-on-surface group-hover:text-primary transition-colors">{{ $ci->code }}</span>
                                    <span class="font-label-sm text-on-surface-variant mt-0.5">{{ $ci->received_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                    <span class="font-label-sm {{ $isAuto ? 'text-primary' : 'text-tertiary' }} flex items-center gap-0.5 mt-0.5">
                                        <span class="material-symbols-outlined text-[12px]">{{ $isAuto ? 'link' : 'edit_note' }}</span>
                                        {{ $isAuto ? 'Auto-Journal' : 'Manual Input' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="flex flex-col">
                                    <span class="font-headline-sm text-body-md font-bold text-on-surface">{{ $sourceName }}</span>
                                    @if($ci->source_type)
                                        <span class="font-label-sm text-on-surface-variant uppercase font-mono">{{ class_basename($ci->source_type) }}</span>
                                    @endif
                                    @if($ci->reference_no)
                                        <span class="font-mono font-body-sm text-tertiary mt-0.5">Ref: {{ $ci->reference_no }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="flex flex-col items-start gap-1">
                                    <span class="px-2 py-0.5 rounded-full font-label-sm uppercase font-bold {{ $cStyle['badge'] }}">
                                        {{ $cStyle['label'] }}
                                    </span>
                                    <span class="font-body-sm text-on-surface-variant">{{ Str::limit($ci->notes, 60) }}</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full {{ $aStyle['dot'] }}"></span>
                                    <span class="font-mono font-body-sm text-on-surface font-semibold">{{ $aStyle['label'] }}</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md text-right">
                                <span class="font-mono font-extrabold text-body-lg text-primary flex items-center gap-0.5 justify-end">
                                    <span class="material-symbols-outlined text-[16px]">arrow_upward</span>
                                    +{{ $fmt($ci->amount) }}
                                </span>
                            </td>
                            <td class="py-space-md px-space-md text-center">
                                @if($isAuto)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/20 text-primary font-label-sm uppercase font-bold tracking-wider">
                                        <span class="material-symbols-outlined text-[14px]">check</span>
                                        Terposting
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-tertiary/20 text-tertiary font-label-sm uppercase font-bold tracking-wider">
                                        <span class="material-symbols-outlined text-[14px]">done_all</span>
                                        Tercatat
                                    </span>
                                @endif
                            </td>
                            <td class="py-space-md px-space-md text-center">
                                <script type="application/json" id="ci-json-{{ $ci->id }}">@json($rowJson)</script>
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button"
                                            onclick="ciShowDetail({{ $ci->id }})"
                                            class="w-8 h-8 rounded-lg hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors"
                                            title="Lihat Detail">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </button>
                                    <button type="button"
                                            onclick="ciShowReceipt({{ $ci->id }})"
                                            class="w-8 h-8 rounded-lg hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors"
                                            title="Cetak Bukti Kas Masuk">
                                        <span class="material-symbols-outlined text-[18px]">print</span>
                                    </button>
                                    @if(! $isAuto)
                                        <button type="button"
                                                onclick="ciConfirmDelete('{{ route('cash-ins.destroy', $ci) }}', '{{ $ci->code }}')"
                                                class="w-8 h-8 rounded-lg hover:bg-error/15 text-on-surface-variant hover:text-error flex items-center justify-center transition-colors"
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
                                    <span class="material-symbols-outlined text-[40px] text-surface-container-highest">payments</span>
                                    <span>Belum ada mutasi kas masuk yang cocok dengan filter.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-space-md border-t border-surface-container-highest flex flex-col sm:flex-row items-center justify-between gap-space-sm bg-surface-container-low">
            <div class="flex items-center gap-space-sm font-body-sm text-on-surface-variant">
                @if($cashIns->total() > 0)
                    <span>Menampilkan <strong>{{ $cashIns->firstItem() }}–{{ $cashIns->lastItem() }}</strong> dari <strong>{{ $cashIns->total() }}</strong> mutasi</span>
                    <span class="h-3 w-px bg-surface-container-highest"></span>
                    <span>Total Halaman: <strong class="text-primary font-mono font-bold">{{ $fmt($cashIns->getCollection()->sum('amount')) }}</strong></span>
                @else
                    <span>Belum ada data</span>
                @endif
            </div>
            <div>{{ $cashIns->links() }}</div>
        </div>
    </div>

    {{-- ============ FOOTER GUARANTEE ============ --}}
    <div class="rounded-xl bg-surface-container-low p-space-lg shadow-sm">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md">
            <div class="flex items-start gap-space-md">
                <div class="w-10 h-10 rounded-xl bg-primary-container/20 flex items-center justify-center text-primary shrink-0 mt-0.5">
                    <span class="material-symbols-outlined text-[22px]">gavel</span>
                </div>
                <div class="flex flex-col">
                    <h4 class="font-headline-sm text-body-lg font-bold text-on-surface">Prinsip Integritas Arus Kas Masuk SUPOS | Sinar Utama</h4>
                    <p class="font-body-sm text-on-surface-variant mt-1 leading-relaxed max-w-4xl">
                        Sistem menerapkan <strong>Zero Double-Entry</strong> — Kasir dan staf finance tidak perlu mencatat ulang pembayaran yang telah diotorisasi di modul <em>Barang Keluar</em> atau <em>Buku Tagihan</em>. Semua kode kas masuk diterbitkan otomatis dengan audit trail nomor faktur asal.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-space-xs shrink-0 self-end md:self-center">
                <span class="px-space-sm py-1 rounded-full bg-surface-container text-primary font-label-sm font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">shield</span> Zero Double-Entry
                </span>
                <span class="px-space-sm py-1 rounded-full bg-surface-container text-tertiary font-label-sm font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">sync</span> Jurnal otomatis
                </span>
            </div>
        </div>
    </div>
</div>

{{-- ============ PARTIAL: MODALS ============ --}}
@include('cash-ins.partials.modals')

{{-- ============ INLINE SCRIPT LANGSUNG DI CONTENT (DIJAMIN JALAN TANPA @STACK) ============ --}}
<script>
function ciOpenModal(id) {
    const el = document.getElementById(id);
    if (!el) {
        console.error('Modal Kas Masuk tidak ditemukan:', id);
        return;
    }
    el.classList.remove('hidden');
    el.classList.add('flex');
    el.style.display = 'flex';
    el.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    const firstInput = el.querySelector('form input:not([type="hidden"]), form select, form textarea');
    if (firstInput) {
        firstInput.focus();
    }
}

function ciCloseModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('hidden');
    el.classList.remove('flex');
    el.style.display = 'none';
    el.setAttribute('aria-hidden', 'true');

    const anotherModalIsOpen = Array.from(document.querySelectorAll('[data-cash-in-modal]'))
        .some(function (modal) { return modal.style.display === 'flex'; });
    if (!anotherModalIsOpen) {
        document.body.style.overflow = '';
    }
}

function ciGetRowData(id) {
    const node = document.getElementById('ci-json-' + id);
    if (!node) return null;
    try {
        return JSON.parse(node.textContent);
    } catch (e) {
        return null;
    }
}

function ciShowDetail(id) {
    const d = ciGetRowData(id);
    if (!d) return;
    document.getElementById('detCode').textContent     = d.code;
    document.getElementById('detAmount').textContent   = d.amount;
    document.getElementById('detTime').textContent     = d.received_at;
    document.getElementById('detAccount').textContent  = d.account;
    document.getElementById('detCategory').textContent = d.category;
    document.getElementById('detRef').textContent      = d.reference_no;
    document.getElementById('detSource').textContent   = d.source_name + ' (' + d.source_type + ')';
    document.getElementById('detNotes').textContent    = d.notes;
    ciOpenModal('cashInDetailModal');
}

function ciShowReceipt(id) {
    const d = ciGetRowData(id);
    if (!d) return;
    document.getElementById('recCode').textContent     = d.code;
    document.getElementById('recTime').textContent     = d.received_at;
    document.getElementById('recAccount').textContent  = d.account;
    document.getElementById('recCategory').textContent = d.category;
    document.getElementById('recSource').textContent   = d.source_name;
    document.getElementById('recRef').textContent      = d.reference_no;
    document.getElementById('recAmount').textContent   = d.amount;
    document.getElementById('recNotes').textContent    = d.notes;
    ciOpenModal('cashInReceiptModal');
}

function ciPrintSlip() {
    const area = document.getElementById('printableReceiptArea');
    if (!area) return;
    const win = window.open('', '_blank', 'width=420,height=620');
    if (!win) {
        window.alert('Izinkan pop-up untuk mencetak bukti kas masuk.');
        return;
    }
    win.document.write('<!doctype html><html><head><title>Bukti Kas Masuk<\/title><style>body{font-family:monospace;padding:16px;font-size:12px;color:#0f172a;} .flex{display:flex;justify-content:space-between;gap:12px;} .text-center{text-align:center;} .border-b{border-bottom:1px dashed #666;padding-bottom:8px;margin-bottom:8px;} .border-y{border-top:1px dashed #666;border-bottom:1px dashed #666;padding:8px 0;margin:8px 0;} .pt-4{padding-top:24px;} .space-y-1>*+*{margin-top:4px;} .space-y-3>*+*{margin-top:12px;} .text-slate-600{color:#475569;} .border-slate-400{border-color:#94a3b8;} @media print{body{padding:0;}}</style><\/head><body>' + area.innerHTML + '<\/body><\/html>');
    win.document.close();
    win.focus();
    win.print();
    win.close();
}

function ciConfirmDelete(url, code) {
    document.getElementById('deleteCashInForm').action   = url;
    document.getElementById('delCashInCode').textContent = code;
    ciOpenModal('deleteCashInModal');
}

function ciSetNominal(val) {
    const input = document.getElementById('nominalInput');
    if (!input) return;
    input.value = val;
    ciUpdateNominalPreview();
}

function ciUpdateNominalPreview() {
    const input   = document.getElementById('nominalInput');
    const preview = document.getElementById('nominalPreview');
    if (!input || !preview) return;
    const v = parseFloat(input.value) || 0;
    preview.textContent = 'IDR ' + v.toLocaleString('id-ID');
}

function ciSelectAccount(radio) {
    document.querySelectorAll('.ci-account-card').forEach(function (card) {
        card.classList.remove('border-primary', 'bg-primary-container/10');
        card.classList.add('border-transparent');
    });
    const active = radio.closest('.ci-account-card');
    if (active) {
        active.classList.remove('border-transparent');
        active.classList.add('border-primary', 'bg-primary-container/10');
    }
}

function ciSelectCategory(val) {
    document.getElementById('ciCategoryInput').value = val;
    document.getElementById('ciFilterForm').submit();
}

function ciSetQuickDate(mode) {
    const now = new Date();
    const pad = function (n) { return String(n).padStart(2, '0'); };
    const todayStr = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate());
    const fromEl = document.getElementById('ciDateFrom');
    const toEl   = document.getElementById('ciDateTo');
    if (!fromEl || !toEl) return;

    if (mode === 'today') {
        fromEl.value = todayStr;
        toEl.value   = todayStr;
    } else if (mode === '7days') {
        const past = new Date(now.getTime() - 6 * 86400000);
        fromEl.value = past.getFullYear() + '-' + pad(past.getMonth() + 1) + '-' + pad(past.getDate());
        toEl.value   = todayStr;
    } else if (mode === 'month') {
        fromEl.value = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-01';
        toEl.value   = todayStr;
    }
    document.getElementById('ciFilterForm').submit();
}

(function () {
    const searchEl = document.getElementById('ciSearchInput');
    if (searchEl) {
        let t;
        searchEl.addEventListener('input', function () {
            clearTimeout(t);
            t = setTimeout(function () {
                document.getElementById('ciFilterForm').submit();
            }, 500);
        });
    }

    const nomEl = document.getElementById('nominalInput');
    if (nomEl) {
        nomEl.addEventListener('input', ciUpdateNominalPreview);
        ciUpdateNominalPreview();
    }

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        const openModal = Array.from(document.querySelectorAll('[data-cash-in-modal]'))
            .find(function (modal) { return modal.style.display === 'flex'; });
        if (openModal) {
            ciCloseModal(openModal.id);
        }
    });

    @if($errors->hasAny(['category', 'account', 'amount', 'received_at', 'reference_no', 'notes']))
        ciOpenModal('createCashInModal');
    @endif
})();
</script>
@endsection