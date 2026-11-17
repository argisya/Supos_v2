@extends('layouts.app')

@section('title', 'Barang Keluar')

@php
    $fmt = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $fmtJt = function ($n) {
        if ($n >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 2, ',', '.') . ' M';
        if ($n >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 2, ',', '.') . ' Jt';
        if ($n >= 1_000)         return 'Rp ' . number_format($n / 1_000, 0, ',', '.') . ' Rb';
        return 'Rp ' . number_format((int) $n, 0, ',', '.');
    };
    $num = fn ($n) => number_format((int) $n, 0, ',', '.');
    $pct = fn ($a, $b) => $b > 0 ? round(($a / $b) * 100, 1) : 0;
@endphp

@section('content')
<div class="flex flex-col w-full pb-space-xl">
<div class="relative w-full overflow-hidden">
    <div class="absolute -top-24 right-1/4 w-96 h-96 bg-secondary-container/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-48 left-10 w-80 h-80 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>

    {{-- ============ HEADER ============ --}}
    <div class="relative z-10 pt-space-md mb-space-lg">
        <div class="flex items-center gap-space-xs text-on-surface-variant font-label-md text-label-md mb-space-xs">
            <span class="hover:text-on-surface cursor-pointer transition-colors">Sistem Grosir</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="hover:text-on-surface cursor-pointer transition-colors">Operasional Penjualan</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-primary font-semibold">Barang Keluar & Hitung Laba</span>
        </div>

        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-space-md">
            <div class="min-w-0">
                <div class="flex items-center gap-space-sm flex-wrap">
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Barang Keluar & Kasir Grosir</h1>
                    <span class="inline-flex items-center gap-1.5 px-space-sm py-0.5 rounded-full bg-primary/15 text-primary font-label-sm text-label-sm border border-primary/25 shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                        Auto-Profit Lock
                    </span>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1 max-w-2xl">
                    Kalkulator laba otomatis berbasis HPP terakhir. Pembayaran lunas terhubung ke Kas Masuk, transaksi tempo langsung masuk ke Buku Tagihan.
                </p>
            </div>

            {{-- Toolbar 3 Tombol Header yang Rapi & Presisi --}}
            <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                <button type="button" onclick="openModal('dailyProfitModal')"
                        class="h-10 px-4 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface border border-white/10 font-headline-sm text-body-sm font-medium inline-flex items-center justify-center gap-2 transition-all shadow-sm whitespace-nowrap cursor-pointer">
                    <span class="material-symbols-outlined text-[18px] text-tertiary">analytics</span>
                    <span>Rekap Laba Hari Ini</span>
                </button>

                @if($transactions->isNotEmpty())
                    <a href="{{ route('outgoing-goods.receipt', $transactions->first()) }}" target="_blank"
                       class="h-10 px-4 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface border border-white/10 font-headline-sm text-body-sm font-medium inline-flex items-center justify-center gap-2 transition-all shadow-sm whitespace-nowrap">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">receipt_long</span>
                        <span>Cetak Invoice Terakhir</span>
                    </a>
                @else
                    <button type="button" disabled
                            class="h-10 px-4 rounded-xl bg-surface-container text-on-surface-variant/50 border border-white/5 font-headline-sm text-body-sm font-medium inline-flex items-center justify-center gap-2 whitespace-nowrap cursor-not-allowed"
                            title="Belum ada invoice untuk dicetak">
                        <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                        <span>Cetak Invoice Terakhir</span>
                    </button>
                @endif

                <button type="button" onclick="openSaleModal()"
                        class="h-10 px-4 rounded-xl bg-primary hover:bg-primary-container text-on-primary border border-primary/40 font-headline-sm text-body-sm font-bold inline-flex items-center justify-center gap-2 transition-all shadow-[0_0_20px_rgba(16,185,129,0.3)] hover:scale-[1.01] active:scale-[0.98] whitespace-nowrap cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
                    <span>Input Penjualan Baru</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ============ FORMULA HERO ============ --}}
    <div class="relative z-10 mb-space-lg p-space-md rounded-xl bg-surface-container-low/80 backdrop-blur-xl shadow-sm border border-white/5 flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md">
        <div class="flex items-center gap-space-md">
            <div class="w-10 h-10 rounded-xl bg-tertiary/15 text-tertiary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[22px]">functions</span>
            </div>
            <div class="flex flex-col">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Formula Inti Gross Profit Grosir</span>
                <div class="font-label-lg text-label-lg text-on-surface flex items-center gap-1.5 flex-wrap">
                    <span class="text-primary font-bold">Keuntungan</span>
                    <span class="text-on-surface-variant">=</span>
                    <span class="text-tertiary">(Harga Jual Box - HPP Modal Terakhir)</span>
                    <span class="text-on-surface-variant">×</span>
                    <span class="text-secondary font-bold">Jumlah Box Terjual</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-space-sm self-end md:self-auto font-label-md text-label-md">
            <span class="px-space-sm py-1 rounded bg-surface-container text-on-surface-variant">Lock Historical HPP: <strong class="text-primary">Aktif</strong></span>
            <span class="px-space-sm py-1 rounded bg-surface-container text-on-surface-variant">Sinkron Kas/Piutang: <strong class="text-tertiary">Real-Time</strong></span>
        </div>
    </div>

    {{-- ============ KPI CARDS ============ --}}
    <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter mb-space-lg">

        {{-- KPI 1: Total Penjualan Hari Ini --}}
        <div class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-sm border border-white/5 flex flex-col justify-between hover:bg-surface-container transition-colors">
            <div class="flex items-center justify-between mb-space-sm">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Total Penjualan Hari Ini</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                </div>
            </div>
            <div>
                <div class="font-display-lg text-display-lg text-on-surface font-extrabold tracking-tight">{{ $fmtJt($kpi['today_sales']) }}</div>
                <div class="flex items-center gap-space-xs mt-space-xs font-label-md text-label-md text-on-surface-variant">
                    <span class="text-primary font-semibold">{{ $kpi['today_count'] }} Transaksi</span>
                    <span>•</span>
                    <span>{{ $num($kpi['today_box']) }} Box Terdistribusi</span>
                </div>
            </div>
            <div class="mt-space-md pt-space-xs border-t border-white/5 flex items-center justify-between font-label-sm text-label-sm text-on-surface-variant">
                <span>Total Pcs Keluar: {{ $num($kpi['today_pcs']) }}</span>
                <span class="text-primary font-bold">{{ $kpi['today_box'] > 0 ? round($kpi['today_box'] / 400 * 100) : 0 }}% Target</span>
            </div>
        </div>

        {{-- KPI 2: Estimasi Laba Bersih --}}
        <div class="p-space-md rounded-xl bg-linear-to-br from-primary-container/20 via-surface-container-low to-surface-container-low backdrop-blur-md shadow-[0_8px_24px_rgba(16,185,129,0.15)] border border-primary/30 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-primary/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center justify-between mb-space-sm">
                <span class="font-label-sm text-label-sm text-primary uppercase tracking-wider font-bold">Estimasi Laba Bersih</span>
                <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">trending_up</span>
                </div>
            </div>
            <div>
                <div class="font-display-lg text-display-lg text-primary font-extrabold tracking-tight">{{ $fmtJt($kpi['today_profit']) }}</div>
                <div class="inline-flex items-center gap-1 px-space-xs py-0.5 rounded bg-primary/20 text-primary font-label-md text-label-md mt-space-xs font-bold">
                    <span class="material-symbols-outlined text-[14px]">arrow_upward</span>
                    {{ $kpi['today_sales'] > 0 ? round(($kpi['today_profit'] / $kpi['today_sales']) * 100, 1) : 0 }}% Margin Rata-rata
                </div>
            </div>
            <div class="mt-space-md pt-space-xs border-t border-primary/20 flex items-center justify-between font-label-sm text-label-sm text-on-surface-variant">
                <span>HPP Terkunci: {{ $fmtJt($kpi['today_hpp']) }}</span>
                <span class="text-primary font-semibold">Tercatat Akurat</span>
            </div>
        </div>

        {{-- KPI 3: Lunas Masuk Kas --}}
        <div class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-sm border border-white/5 flex flex-col justify-between hover:bg-surface-container transition-colors">
            <div class="flex items-center justify-between mb-space-sm">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Lunas Masuk Kas Toko</span>
                <div class="w-8 h-8 rounded-lg bg-tertiary/15 flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                </div>
            </div>
            <div>
                <div class="font-display-lg text-display-lg text-on-surface font-extrabold tracking-tight">{{ $fmtJt($kpi['lunas_total']) }}</div>
                <div class="flex items-center gap-space-xs mt-space-xs font-label-md text-label-md text-tertiary">
                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                    <span>Otomatis di Modul Kas Masuk</span>
                </div>
            </div>
            <div class="mt-space-md pt-space-xs border-t border-white/5 flex items-center justify-between font-label-sm text-label-sm text-on-surface-variant">
                <span>{{ $kpi['lunas_count'] }} Transaksi Tunai/Transfer</span>
                <span class="text-tertiary font-bold">{{ $pct($kpi['lunas_total'], $kpi['today_sales']) }}% Realisasi</span>
            </div>
        </div>

        {{-- KPI 4: Piutang Tempo --}}
        <div class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-sm border border-white/5 flex flex-col justify-between hover:bg-surface-container transition-colors">
            <div class="flex items-center justify-between mb-space-sm">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Piutang Belum Lunas (Tempo)</span>
                <div class="w-8 h-8 rounded-lg bg-secondary-container/30 flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined text-[18px]">pending_actions</span>
                </div>
            </div>
            <div>
                <div class="font-display-lg text-display-lg text-secondary font-extrabold tracking-tight">{{ $fmtJt($kpi['tempo_total']) }}</div>
                <div class="flex items-center gap-space-xs mt-space-xs font-label-md text-label-md text-secondary">
                    <span class="material-symbols-outlined text-[14px]">book_online</span>
                    <span>Masuk Buku Tagihan ({{ $kpi['tempo_count'] }} Pelanggan)</span>
                </div>
            </div>
            <div class="mt-space-md pt-space-xs border-t border-white/5 flex items-center justify-between font-label-sm text-label-sm text-on-surface-variant">
                <span>{{ $kpi['tempo_rows'] }} Faktur Aktif</span>
                <span class="text-secondary font-semibold">Terschedule Otomatis</span>
            </div>
        </div>
    </div>

    {{-- ============ TABEL RIWAYAT ============ --}}
    <div class="relative z-10 flex flex-col gap-space-md">
        <form method="GET" action="{{ route('outgoing-goods.index') }}" id="filterForm"
              class="p-space-md rounded-2xl bg-surface-container-low/90 backdrop-blur-md border border-white/5 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-space-md shadow-sm">
            <div class="flex items-center gap-space-xs flex-wrap">
                <button type="submit" name="status" value="SEMUA"
                        class="h-9 px-space-md rounded-xl font-headline-sm text-body-sm flex items-center gap-1 transition-all cursor-pointer
                        {{ $status === 'SEMUA' ? 'bg-primary text-on-primary shadow-sm font-semibold' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-bright hover:text-on-surface' }}">
                    <span>Semua</span>
                    <span class="w-5 h-5 rounded-full {{ $status === 'SEMUA' ? 'bg-on-primary/20 text-on-primary' : 'bg-surface-container text-on-surface-variant' }} text-label-sm font-bold flex items-center justify-center">{{ $kpi['total_count'] }}</span>
                </button>
                <button type="submit" name="status" value="LUNAS"
                        class="h-9 px-space-md rounded-xl font-body-sm text-body-sm flex items-center gap-1 transition-all cursor-pointer
                        {{ $status === 'LUNAS' ? 'bg-primary text-on-primary shadow-sm font-semibold' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-bright hover:text-on-surface' }}">
                    <span>Lunas (Kas Masuk)</span>
                    <span class="w-5 h-5 rounded-full {{ $status === 'LUNAS' ? 'bg-on-primary/20 text-on-primary' : 'bg-surface-container text-tertiary' }} text-label-sm font-bold flex items-center justify-center">{{ $kpi['lunas_all'] }}</span>
                </button>
                <button type="submit" name="status" value="TEMPO"
                        class="h-9 px-space-md rounded-xl font-body-sm text-body-sm flex items-center gap-1 transition-all cursor-pointer
                        {{ $status === 'TEMPO' ? 'bg-secondary text-on-secondary shadow-sm font-semibold' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-bright hover:text-on-surface' }}">
                    <span>Tempo / Piutang</span>
                    <span class="w-5 h-5 rounded-full {{ $status === 'TEMPO' ? 'bg-on-secondary/20 text-on-secondary' : 'bg-secondary-container/40 text-secondary' }} text-label-sm font-bold flex items-center justify-center">{{ $kpi['tempo_all'] }}</span>
                </button>
            </div>
            <div class="flex items-center gap-space-sm">
                <div class="relative flex-1 sm:w-72">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input name="search" value="{{ request('search') }}" oninput="debouncedFilter()"
                           placeholder="Cari No Invoice, Mitra, Produk..."
                           class="w-full h-9 pl-9 pr-space-md rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/60 font-body-sm text-body-sm border border-white/5 focus:outline-none focus:border-primary" />
                </div>
                <a href="{{ route('outgoing-goods.index') }}" class="h-9 w-9 rounded-xl bg-surface-container hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant transition-colors" title="Reset Filter">
                    <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                </a>
            </div>
        </form>

        <div class="rounded-2xl bg-surface-container-low/90 backdrop-blur-md border border-white/5 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 bg-surface-container font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">
                            <th class="py-space-md px-space-md">No. Invoice & Jam</th>
                            <th class="py-space-md px-space-md">Pelanggan / Mitra</th>
                            <th class="py-space-md px-space-md">Detail Produk & Box Terjual</th>
                            <th class="py-space-md px-space-md">HPP Modal → Jual / Box</th>
                            <th class="py-space-md px-space-md">Keuntungan Bersih</th>
                            <th class="py-space-md px-space-md text-right">Total Faktur</th>
                            <th class="py-space-md px-space-md">Status & Alur Kas</th>
                            <th class="py-space-md px-space-md text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 font-body-sm text-body-sm text-on-surface">
                        @forelse ($transactions as $trx)
                            @php
                                $isLunas = $trx->payment_status === 'lunas';
                                $detailPayload = [
                                    'no_invoice' => $trx->no_invoice,
                                    'customer'   => $trx->customer->name,
                                    'sold_at'    => $trx->sold_at->format('d M Y H:i') . ' WIB',
                                    'status'     => strtoupper($trx->payment_status),
                                    'due_date'   => $trx->due_date?->format('d M Y') ?? 'Lunas Tunai/Transfer',
                                    'product'    => $trx->product->name,
                                    'qty'        => $num($trx->box_qty) . ' Box (' . $num($trx->total_pcs) . ' Pcs)',
                                    'hpp_box'    => $fmt($trx->hpp_per_box),
                                    'sell_box'   => $fmt($trx->sell_per_box),
                                    'subtotal'   => $fmt($trx->subtotal),
                                    'profit'     => '+' . $fmt($trx->profit),
                                    'print_url'  => route('outgoing-goods.receipt', $trx),
                                ];
                                $rollbackPayload = [
                                    'no_invoice' => $trx->no_invoice,
                                    'customer'   => $trx->customer->name,
                                    'product'    => $trx->product->name,
                                    'qty'        => $num($trx->box_qty) . ' Box (' . $num($trx->total_pcs) . ' Pcs)',
                                    'subtotal'   => $fmt($trx->subtotal),
                                    'status'     => strtoupper($trx->payment_status),
                                ];
                            @endphp
                            <tr class="hover:bg-surface-container/60 transition-colors group">
                                <td class="py-space-md px-space-md whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-label-md text-label-md {{ $isLunas ? 'text-primary' : 'text-secondary' }} font-bold">{{ $trx->no_invoice }}</span>
                                        <span class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[12px]">schedule</span> {{ $trx->sold_at->format('H:i') }} WIB
                                        </span>
                                    </div>
                                </td>
                                <td class="py-space-md px-space-md">
                                    <div class="flex flex-col">
                                        <span class="font-headline-sm text-body-md text-on-surface font-semibold group-hover:text-primary transition-colors">{{ $trx->customer->name }}</span>
                                        <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $trx->customer->type ?? 'Mitra Grosir' }}</span>
                                    </div>
                                </td>
                                <td class="py-space-md px-space-md">
                                    <div class="flex flex-col">
                                        <span class="text-on-surface font-medium">{{ $trx->product->name }}</span>
                                        <div class="flex items-center gap-1 mt-0.5 font-label-sm text-label-sm text-tertiary">
                                            <span class="px-1.5 py-0.5 rounded bg-tertiary/15 text-tertiary font-bold">{{ $num($trx->box_qty) }} BOX</span>
                                            <span>= {{ $num($trx->total_pcs) }} Pcs ({{ $trx->product->box_pcs_ratio }}/Box)</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-space-md px-space-md whitespace-nowrap">
                                    <div class="flex flex-col font-label-md text-label-md">
                                        <span class="text-on-surface-variant">HPP: {{ $fmt($trx->hpp_per_box) }}</span>
                                        <span class="text-on-surface font-bold">→ Jual: {{ $fmt($trx->sell_per_box) }}</span>
                                    </div>
                                </td>
                                <td class="py-space-md px-space-md whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <div class="inline-flex items-center gap-1 px-space-sm py-0.5 rounded bg-primary/15 text-primary font-label-md text-label-md font-bold">
                                            <span class="material-symbols-outlined text-[14px]">trending_up</span>
                                            +{{ $fmt($trx->profit) }}
                                        </div>
                                        <span class="font-label-sm text-label-sm text-on-surface-variant mt-0.5">
                                            {{ $fmt($trx->sell_per_box - $trx->hpp_per_box) }} × {{ $trx->box_qty }} Box
                                            ({{ $trx->hpp_per_box > 0 ? round((($trx->sell_per_box - $trx->hpp_per_box) / $trx->hpp_per_box) * 100, 1) : 0 }}%)
                                        </span>
                                    </div>
                                </td>
                                <td class="py-space-md px-space-md text-right whitespace-nowrap">
                                    <span class="font-headline-sm text-body-md font-extrabold text-on-surface">{{ $fmt($trx->subtotal) }}</span>
                                </td>
                                <td class="py-space-md px-space-md whitespace-nowrap">
                                    @if($isLunas)
                                        <div class="inline-flex items-center gap-1.5 px-space-sm py-1 rounded-full bg-primary/15 text-primary font-label-sm text-label-sm font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                            <span>Lunas → Kas Masuk</span>
                                        </div>
                                    @else
                                        <div class="inline-flex items-center gap-1.5 px-space-sm py-1 rounded-full bg-secondary-container/30 text-secondary font-label-sm text-label-sm font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                                            <span>Tempo (Buku Tagihan)</span>
                                        </div>
                                        <div class="font-label-sm text-label-sm text-on-surface-variant mt-0.5">
                                            Jatuh Tempo: {{ $trx->due_date?->format('d M Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-space-md px-space-md text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button"
                                                data-detail="{{ json_encode($detailPayload) }}"
                                                onclick="openInvoiceDetail(this)"
                                                class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-bright text-on-surface flex items-center justify-center transition-colors cursor-pointer" title="Lihat Faktur">
                                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        </button>
                                        <a href="{{ route('outgoing-goods.receipt', $trx) }}" target="_blank"
                                           class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-bright text-on-surface flex items-center justify-center transition-colors" title="Cetak Nota">
                                            <span class="material-symbols-outlined text-[16px]">print</span>
                                        </a>
                                        <form method="POST" action="{{ route('outgoing-goods.rollback', $trx) }}" id="rollback-form-{{ $trx->id }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                    data-form-id="rollback-form-{{ $trx->id }}"
                                                    data-rollback="{{ json_encode($rollbackPayload) }}"
                                                    onclick="confirmRollback(this)"
                                                    class="w-8 h-8 rounded-lg bg-surface-container hover:bg-error/20 text-on-surface-variant hover:text-error flex items-center justify-center transition-colors cursor-pointer" title="Rollback Transaksi">
                                                <span class="material-symbols-outlined text-[16px]">undo</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-space-xl text-center text-on-surface-variant">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="material-symbols-outlined text-[40px] text-surface-container-highest">receipt_long</span>
                                        <span>Belum ada transaksi yang cocok.</span>
                                        <button type="button" onclick="openSaleModal()" class="mt-1 px-space-md py-1.5 rounded-xl bg-primary/15 text-primary font-headline-sm text-body-sm hover:bg-primary/25 transition-colors cursor-pointer">
                                            + Input Penjualan Pertama
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-space-lg py-space-md bg-surface-container border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-space-sm font-label-sm text-label-sm text-on-surface-variant">
                <div class="flex items-center gap-space-sm">
                    @if($transactions->total() > 0)
                        <span>Menampilkan {{ $transactions->firstItem() }}-{{ $transactions->lastItem() }} dari {{ $transactions->total() }} transaksi</span>
                        <span>•</span>
                        <span class="text-primary font-semibold">Total Laba Batch Ini: +{{ $fmt($batchProfit) }}</span>
                    @else
                        <span>Belum ada data</span>
                    @endif
                </div>
                <div>{{ $transactions->links() }}</div>
            </div>
        </div>
    </div>

    {{-- ============ FOOTER CALLOUT ============ --}}
    <div class="relative z-10 mt-space-lg p-space-md rounded-2xl bg-surface-container-lowest/80 border border-white/5 flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md">
        <div class="flex items-center gap-space-md">
            <div class="w-10 h-10 rounded-xl bg-tertiary-container/20 text-tertiary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[24px]">verified_user</span>
            </div>
            <div class="flex flex-col">
                <h3 class="font-headline-sm text-body-md text-on-surface font-semibold">Protokol Integritas Stok & Keuntungan Historis GrosirBox PRO</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant max-w-3xl">
                    Stok fisik otomatis berkurang pada level Box dan Pcs saat transaksi diverifikasi. Margin keuntungan per nota <strong>terkunci permanen</strong> mengikuti HPP saat transaksi dibuat.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-space-xs font-label-sm text-label-sm self-end md:self-auto shrink-0">
            <span class="px-space-sm py-1 rounded-lg bg-surface-container text-primary font-bold">HPP Audit Ready</span>
            <span class="px-space-sm py-1 rounded-lg bg-surface-container text-tertiary font-bold">Zero-Discrepancy</span>
        </div>
    </div>
</div>
</div>

@include('outgoing-goods.partials.modals')
@endsection

@push('scripts')
<script>
(function () {
    const $ = id => document.getElementById(id);
    const fmtRp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
    let pendingConfirmCallback = null;

    window.openModal = id => $(id)?.classList.remove('hidden');
    window.closeModal = id => $(id)?.classList.add('hidden');

    /* ============ MODAL KONFIRMASI UNIVERSAL ============ */
    window.showConfirmModal = function ({
        badge = 'Konfirmasi Tindakan',
        title = 'Apakah Anda yakin?',
        message = '',
        details = [],
        variant = 'danger', // 'danger' | 'primary' | 'warning'
        confirmLabel = 'Ya, Lanjutkan',
        confirmIcon = 'check',
        onConfirm = null,
    }) {
        const iconWrap = $('confirmIconWrap');
        const iconEl = $('confirmIcon');
        const badgeEl = $('confirmBadge');
        const proceedBtn = $('confirmProceedBtn');

        badgeEl.textContent = badge;
        $('confirmTitle').textContent = title;
        $('confirmMessage').textContent = message;
        $('confirmProceedLabel').textContent = confirmLabel;
        $('confirmProceedIcon').textContent = confirmIcon;

        if (variant === 'primary') {
            iconWrap.className = 'w-11 h-11 rounded-xl bg-primary/15 text-primary flex items-center justify-center shrink-0';
            iconEl.textContent = 'verified';
            badgeEl.className = 'font-label-sm text-label-sm uppercase tracking-wider text-primary font-bold';
            proceedBtn.className = 'h-10 px-space-lg rounded-xl bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-body-sm font-bold flex items-center gap-1.5 transition-all shadow-[0_0_16px_rgba(16,185,129,0.35)] cursor-pointer';
        } else if (variant === 'warning') {
            iconWrap.className = 'w-11 h-11 rounded-xl bg-secondary-container/30 text-secondary flex items-center justify-center shrink-0';
            iconEl.textContent = 'restart_alt';
            badgeEl.className = 'font-label-sm text-label-sm uppercase tracking-wider text-secondary font-bold';
            proceedBtn.className = 'h-10 px-space-lg rounded-xl bg-secondary hover:bg-secondary/90 text-on-secondary font-headline-sm text-body-sm font-bold flex items-center gap-1.5 transition-all cursor-pointer';
        } else {
            iconWrap.className = 'w-11 h-11 rounded-xl bg-error/15 text-error flex items-center justify-center shrink-0';
            iconEl.textContent = 'gpp_maybe';
            badgeEl.className = 'font-label-sm text-label-sm uppercase tracking-wider text-error font-bold';
            proceedBtn.className = 'h-10 px-space-lg rounded-xl bg-error hover:bg-error/90 text-on-error font-headline-sm text-body-sm font-bold flex items-center gap-1.5 transition-all cursor-pointer';
        }

        const detailBox = $('confirmDetailBox');
        if (details.length > 0) {
            detailBox.innerHTML = details.map(row => `
                <div class="flex items-center justify-between gap-2">
                    <span class="text-on-surface-variant">${row.label}</span>
                    <strong class="${row.highlight ? 'text-primary' : 'text-on-surface'} text-right">${row.value}</strong>
                </div>
            `).join('');
            detailBox.classList.remove('hidden');
        } else {
            detailBox.classList.add('hidden');
            detailBox.innerHTML = '';
        }

        pendingConfirmCallback = onConfirm;
        openModal('confirmActionModal');
    };

    $('confirmProceedBtn')?.addEventListener('click', function () {
        const cb = pendingConfirmCallback;
        pendingConfirmCallback = null;
        closeModal('confirmActionModal');
        if (typeof cb === 'function') cb();
    });

    /* Konfirmasi 1: Rollback Faktur */
    window.confirmRollback = function (btn) {
        const formId = btn.dataset.formId;
        const data = JSON.parse(btn.dataset.rollback);
        showConfirmModal({
            badge: 'Rollback Transaksi',
            title: 'Batalkan Faktur ' + data.no_invoice + '?',
            message: 'Faktur akan dihapus dari riwayat penjualan. Stok fisik produk akan dikembalikan otomatis ke gudang dan pencatatan kas/piutang terkait akan ditarik balik.',
            variant: 'danger',
            confirmLabel: 'Ya, Rollback Faktur',
            confirmIcon: 'undo',
            details: [
                { label: 'Pelanggan / Mitra', value: data.customer },
                { label: 'Produk & Volume', value: `${data.product} (${data.qty})` },
                { label: 'Nilai Faktur & Status', value: `${data.subtotal} (${data.status})` },
            ],
            onConfirm: () => document.getElementById(formId)?.submit(),
        });
    };

    /* Konfirmasi 2: Simpan Penjualan & Potong Stok */
    window.confirmSubmitSale = function () {
        const form = $('saleForm');
        if (!form || !form.reportValidity()) return;

        const d = getProductData();
        if (!d) return;

        const boxQty = parseInt($('qtyInput').value, 10) || 0;
        if (boxQty <= 0 || boxQty > d.stockBox) return;

        const sellBox = parseFloat($('sellBoxInput').value) || 0;
        const totalPcs = boxQty * d.pcsPerBox;
        const omzet = sellBox * boxQty;
        const profit = omzet - (d.hpp * boxQty);
        const status = $('paymentStatusInput').value === 'lunas' ? 'LUNAS → Kas Masuk' : 'TEMPO → Buku Tagihan';
        const custSelect = $('customerSelect');
        const custName = custSelect?.options[custSelect.selectedIndex]?.text || 'Mitra';

        showConfirmModal({
            badge: 'Verifikasi Posting Penjualan',
            title: 'Simpan Transaksi & Potong Stok?',
            message: 'Pastikan data pelanggan, kuantitas Box, dan harga jual sudah sesuai. Stok fisik akan langsung terpotong secara permanen.',
            variant: 'primary',
            confirmLabel: 'Ya, Posting Transaksi',
            confirmIcon: 'verified',
            details: [
                { label: 'Pelanggan', value: custName },
                { label: 'Barang & Kuantitas', value: `${d.name} — ${boxQty} Box (${totalPcs.toLocaleString('id-ID')} Pcs)` },
                { label: 'Total Tagihan', value: fmtRp(omzet) },
                { label: 'Keuntungan Bersih', value: '+' + fmtRp(profit), highlight: true },
                { label: 'Alur Pembayaran', value: status },
            ],
            onConfirm: () => form.submit(),
        });
    };

    /* Konfirmasi 3: Reset Form Kasir */
    window.confirmResetSaleForm = function () {
        showConfirmModal({
            badge: 'Reset Input Kasir',
            title: 'Kembalikan Form ke Awal?',
            message: 'Semua perubahan jumlah Box, harga jual, dan mode pembayaran yang belum disimpan akan dikembalikan ke pengaturan default.',
            variant: 'warning',
            confirmLabel: 'Ya, Reset Form',
            confirmIcon: 'restart_alt',
            onConfirm: () => {
                $('qtyInput').value = 1;
                setPaymentMode('lunas');
                onProductChange(true);
            },
        });
    };

    /* ============ MODAL KASIR & DETAIL ============ */
    window.openSaleModal = function () {
        openModal('saleFormModal');
        if ($('productSelect')) {
            onProductChange(false);
        }
    };

    window.openInvoiceDetail = function (btn) {
        const data = JSON.parse(btn.dataset.detail);
        $('mdInvNo').textContent = 'Detail Faktur ' + data.no_invoice;
        $('mdCustomer').textContent = data.customer;
        $('mdSoldAt').textContent = data.sold_at;
        $('mdStatus').textContent = data.status;
        $('mdStatus').className = data.status === 'LUNAS' ? 'text-primary font-bold' : 'text-secondary font-bold';
        $('mdDueDate').textContent = data.due_date;
        $('mdProduct').textContent = data.product;
        $('mdQty').textContent = data.qty;
        $('mdHppBox').textContent = data.hpp_box;
        $('mdSellBox').textContent = data.sell_box;
        $('mdSubtotal').textContent = data.subtotal;
        $('mdProfit').textContent = data.profit;
        $('mdPrintLink').href = data.print_url;
        openModal('invoiceDetailModal');
    };

    window.submitQuickCustomer = async function (e) {
        e.preventDefault();
        const form = e.target;
        const errEl = $('quickCustomerError');
        const btn = $('quickCustomerSubmit');
        errEl.classList.add('hidden');
        btn.disabled = true;

        try {
            const res = await fetch("{{ route('outgoing-goods.customers.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(new FormData(form))),
            });
            const json = await res.json();
            if (!res.ok) {
                throw new Error(json.message || 'Gagal menyimpan pelanggan.');
            }
            const select = $('customerSelect');
            if (select) {
                const opt = new Option(
                    json.customer.name + (json.customer.type ? ` (${json.customer.type})` : ''),
                    json.customer.id,
                    true,
                    true
                );
                select.add(opt);
            }
            form.reset();
            closeModal('newCustomerModal');
        } catch (err) {
            errEl.textContent = err.message;
            errEl.classList.remove('hidden');
        } finally {
            btn.disabled = false;
        }
    };

    function getProductData() {
        const sel = $('productSelect');
        if (!sel || !sel.options[sel.selectedIndex]) return null;
        const opt = sel.options[sel.selectedIndex];
        return {
            name:      opt.dataset.name,
            sku:       opt.dataset.sku,
            barcode:   opt.dataset.barcode,
            hpp:       parseInt(opt.dataset.hpp || 0, 10),
            pcsPerBox: Math.max(1, parseInt(opt.dataset.pcsPerBox || 1, 10)),
            stock:     parseInt(opt.dataset.stock || 0, 10),
            stockBox:  parseInt(opt.dataset.stockBox || 0, 10),
        };
    }

    window.onProductChange = function (forceSuggest = true) {
        const d = getProductData();
        if (!d) return;

        $('barcodeLabel').textContent = 'Barcode: ' + (d.barcode || '—');
        $('hppBoxDisplay').textContent = fmtRp(d.hpp);
        $('hppPcsDisplay').textContent = 'Rp ' + (d.hpp / d.pcsPerBox).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        $('ratioInfo').textContent = 'Otomatis / ' + d.pcsPerBox + ' Pcs';
        $('hppPerBoxInput').value = d.hpp;

        const currentSell = parseFloat($('sellBoxInput').value) || 0;
        if (forceSuggest || currentSell <= 0) {
            $('sellBoxInput').value = Math.round((d.hpp * 1.063) / 500) * 500;
        }

        $('qtyInput').setAttribute('max', Math.max(1, d.stockBox));
        if (parseInt($('qtyInput').value, 10) > d.stockBox) {
            $('qtyInput').value = Math.max(1, d.stockBox);
        }

        calculateProfit();
    };

    window.calculateProfit = function () {
        const d = getProductData();
        if (!d) return;

        const boxQty       = parseInt($('qtyInput').value, 10) || 0;
        const sellBox      = parseFloat($('sellBoxInput').value) || 0;
        const totalPcs     = boxQty * d.pcsPerBox;
        const hppTotal     = d.hpp * boxQty;
        const omzet        = sellBox * boxQty;
        const profit       = omzet - hppTotal;
        const marginPct    = omzet > 0 ? ((profit / omzet) * 100).toFixed(2) : 0;
        const marginBox    = sellBox - d.hpp;
        const marginPctBox = d.hpp > 0 ? ((marginBox / d.hpp) * 100).toFixed(1) : 0;
        const sellPcs      = sellBox / d.pcsPerBox;
        const hppPcs       = d.hpp / d.pcsPerBox;

        $('stockDisplay').innerHTML = d.stockBox + ' Box <span class="text-on-surface-variant font-normal text-body-sm">(' + d.stock.toLocaleString('id-ID') + ' Pcs)</span>';
        $('pcsCounter').textContent = '= ' + totalPcs.toLocaleString('id-ID') + ' Pcs';
        $('marginBoxDisplay').textContent = (marginBox >= 0 ? '+' : '') + fmtRp(marginBox) + ' / Box (' + (marginBox >= 0 ? '+' : '') + marginPctBox + '%)';
        $('sellPcsDisplay').textContent = fmtRp(sellPcs);
        $('marginPcsDisplay').textContent = 'Laba: ' + fmtRp(sellPcs - hppPcs) + '/Pcs';
        $('retailRec').textContent = fmtRp(Math.round(sellPcs * 1.10));

        $('calcOmzet').textContent = fmtRp(omzet);
        $('calcBoxInfo').textContent = '(' + boxQty + ' Box / ' + totalPcs.toLocaleString('id-ID') + ' Pcs)';
        $('calcHpp').textContent = fmtRp(hppTotal);
        $('calcProfit').textContent = (profit >= 0 ? '+' : '') + fmtRp(profit);
        $('calcMarginPct').textContent = 'Margin: ' + marginPct + '% (' + fmtRp(marginBox) + '/Box)';

        const errBanner  = $('stockErrorBanner');
        const stockBadge = $('stockBadge');
        const submitBtn  = $('submitSaleBtn');

        if (boxQty > d.stockBox || boxQty <= 0) {
            errBanner.classList.remove('hidden');
            $('stockErrorMsg').textContent = `Permintaan ${boxQty} Box tidak valid atau melebihi sisa fisik ${d.stockBox} Box.`;
            stockBadge.textContent = 'OVER';
            stockBadge.className = 'font-label-sm text-label-sm px-1.5 py-0.5 rounded bg-error/20 text-error font-bold';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        } else {
            errBanner.classList.add('hidden');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
            if (boxQty > d.stockBox * 0.8) {
                stockBadge.textContent = 'LOW';
                stockBadge.className = 'font-label-sm text-label-sm px-1.5 py-0.5 rounded bg-tertiary/20 text-tertiary font-bold';
            } else {
                stockBadge.textContent = 'AMAN';
                stockBadge.className = 'font-label-sm text-label-sm px-1.5 py-0.5 rounded bg-primary/20 text-primary font-bold';
            }
        }
    };

    window.adjustQty = function (delta) {
        const input = $('qtyInput');
        const current = parseInt(input.value, 10) || 0;
        input.value = Math.max(1, current + delta);
        calculateProfit();
    };

    window.resetQtyToMax = function () {
        const d = getProductData();
        if (!d) return;
        $('qtyInput').value = Math.max(1, d.stockBox);
        calculateProfit();
    };

    window.setPaymentMode = function (mode) {
        const inputStatus = $('paymentStatusInput');
        if (!inputStatus) return;

        const lunas = $('pay-lunas-btn');
        const tempo = $('pay-tempo-btn');
        const picker = $('tempo-due-picker');
        const cashAccountPicker = $('cash-account-picker');
        const routing = $('calcRouting');
        const inputDue = $('dueDateInput');

        if (mode === 'lunas') {
            lunas.className = 'h-9 rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all bg-primary-container text-on-primary-container shadow-sm font-semibold cursor-pointer';
            tempo.className = 'h-9 rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all text-on-surface-variant hover:text-on-surface cursor-pointer';
            picker.classList.add('hidden');
            cashAccountPicker.classList.remove('hidden');
            routing.textContent = 'Kas Masuk Toko (Lunas)';
            routing.className = 'text-primary font-bold';
            inputStatus.value = 'lunas';
            if (inputDue) inputDue.required = false;
        } else {
            tempo.className = 'h-9 rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all bg-secondary text-on-secondary shadow-sm font-semibold cursor-pointer';
            lunas.className = 'h-9 rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all text-on-surface-variant hover:text-on-surface cursor-pointer';
            picker.classList.remove('hidden');
            cashAccountPicker.classList.add('hidden');
            routing.textContent = 'Buku Tagihan (Piutang Tempo)';
            routing.className = 'text-secondary font-bold';
            inputStatus.value = 'tempo';
            if (inputDue) inputDue.required = true;
        }
    };

    let filterTimer;
    window.debouncedFilter = function () {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => $('filterForm').submit(), 500);
    };

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const topModals = ['confirmActionModal', 'newCustomerModal', 'invoiceDetailModal', 'dailyProfitModal', 'saleFormModal'];
            for (const id of topModals) {
                const el = $(id);
                if (el && !el.classList.contains('hidden')) {
                    closeModal(id);
                    break;
                }
            }
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        setPaymentMode("{{ old('payment_status', 'lunas') }}");
        if ($('productSelect')) {
            onProductChange({{ old('sell_per_box') ? 'false' : 'true' }});
        }
        @if ($errors->any())
            openModal('saleFormModal');
        @endif
    });
})();
</script>
@endpush