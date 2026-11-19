@extends('layouts.app')

@section('title', 'Barang Keluar')

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
    $num = fn($n) => number_format((int) $n, 0, ',', '.');
    $pct = fn($a, $b) => $b > 0 ? round(($a / $b) * 100, 1) : 0;
    $avgTicket = $kpi['today_count'] > 0 ? (int) round($kpi['today_sales'] / $kpi['today_count']) : 0;
@endphp

@section('content')
    <div class="flex flex-col w-full pb-space-xl">
        <div class="relative w-full overflow-hidden">
            <div
                class="absolute -top-24 right-1/4 w-96 h-96 bg-secondary-container/15 rounded-full blur-3xl pointer-events-none">
            </div>
            <div class="absolute top-48 left-10 w-80 h-80 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>

            {{-- ============ HEADER ============ --}}
            <div class="relative z-10 pt-space-md mb-space-lg">
                <div class="flex items-center gap-space-xs text-on-surface-variant font-label-md text-label-md mb-space-xs">
                    <span class="hover:text-on-surface cursor-pointer transition-colors">Sistem Grosir</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="hover:text-on-surface cursor-pointer transition-colors">Operasional Penjualan</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="text-primary font-semibold">Barang Keluar</span>
                </div>

                <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-space-md">
                    <div class="min-w-0">
                        <div class="flex items-center gap-space-sm flex-wrap">
                            <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Barang Keluar &
                                Kasir Grosir</h1>
                            <span
                                class="inline-flex items-center gap-1.5 px-space-sm py-0.5 rounded-full bg-primary/15 text-primary font-label-sm text-label-sm border border-primary/25 uppercase tracking-wider font-bold shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                                Auto-Profit Lock
                            </span>
                        </div>
                        <p class="font-body-md text-body-md text-on-surface-variant mt-1 max-w-2xl">
                            Pencatatan faktur penjualan grosir. Pembayaran lunas otomatis masuk ke <a
                                href="{{ route('cash-ins.index') }}" class="text-primary font-semibold hover:underline">Kas
                                Masuk</a>, transaksi tempo masuk ke <a href="{{ route('receivables.index') }}"
                                class="text-secondary font-semibold hover:underline">Buku Tagihan</a> & memotong stok
                            gudang.
                        </p>
                    </div>

                    {{-- Toolbar 3 Tombol Header Seragam dengan Barang Masuk --}}
                    <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                        <button type="button" onclick="openSaleModal()"
                            class="h-10 px-4 rounded-xl bg-primary hover:bg-primary-container text-on-primary border border-primary/40 font-headline-sm text-body-sm font-bold inline-flex items-center justify-center gap-2 transition-all shadow-[0_0_20px_rgba(16,185,129,0.3)] hover:scale-[1.01] active:scale-[0.98] whitespace-nowrap cursor-pointer">
                            <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
                            <span>Input Penjualan Baru</span>
                        </button>

                        <button type="button" onclick="openModal('dailyProfitModal')"
                            class="h-10 px-4 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface border border-white/10 font-headline-sm text-body-sm font-medium inline-flex items-center justify-center gap-2 transition-all shadow-sm whitespace-nowrap cursor-pointer">
                            <span class="material-symbols-outlined text-[18px] text-tertiary">analytics</span>
                            <span>Rekap Laba Hari Ini</span>
                        </button>

                        @if ($latestTransaction)
                            <a href="{{ route('outgoing-goods.receipt', $latestTransaction) }}" target="_blank"
                                class="h-10 px-4 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface border border-white/10 font-headline-sm text-body-sm font-medium inline-flex items-center justify-center gap-2 transition-all shadow-sm whitespace-nowrap"
                                title="Cetak {{ $latestTransaction->no_invoice }}">
                                <span
                                    class="material-symbols-outlined text-[18px] text-on-surface-variant">receipt_long</span>
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
                    </div>
                </div>
            </div>

            {{-- ============ KPI CARDS (4 KARTU SERAGAM DENGAN BARANG MASUK) ============ --}}
            <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter mb-space-lg">

                {{-- KPI 1: Total Penjualan Hari Ini --}}
                <div
                    class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-sm border border-white/5 flex flex-col justify-between hover:bg-surface-container transition-colors">
                    <div class="flex items-center justify-between mb-space-sm">
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Total
                            Penjualan (Hari Ini)</span>
                        <div class="w-8 h-8 rounded-lg bg-primary/15 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                        </div>
                    </div>
                    <div>
                        <div class="font-display-lg text-display-lg text-on-surface font-extrabold tracking-tight">
                            {{ $fmtJt($kpi['today_sales']) }}</div>
                    </div>
                    <div
                        class="mt-space-md pt-space-xs border-t border-white/5 flex items-center justify-between font-label-sm text-label-sm text-on-surface-variant">
                        <span>{{ $num($kpi['today_box']) }} Box / {{ $num($kpi['today_pcs']) }} Pcs</span>
                        <span
                            class="px-2 py-0.5 rounded-md bg-surface-container text-on-surface font-bold">{{ $kpi['today_count'] }}
                            Faktur</span>
                    </div>
                </div>

                {{-- KPI 2: Estimasi Laba Bersih --}}
                <div
                    class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-sm border border-primary/25 flex flex-col justify-between hover:bg-surface-container transition-colors">
                    <div class="flex items-center justify-between mb-space-sm">
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Estimasi
                            Laba Bersih (Hari Ini)</span>
                        <div class="w-8 h-8 rounded-lg bg-primary/15 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-[18px]">trending_up</span>
                        </div>
                    </div>
                    <div>
                        <div class="font-display-lg text-display-lg text-primary font-extrabold tracking-tight">
                            {{ $fmtJt($kpi['today_profit']) }}</div>
                    </div>
                    <div
                        class="mt-space-md pt-space-xs border-t border-white/5 flex items-center justify-between font-label-sm text-label-sm text-on-surface-variant">
                        <span>HPP: {{ $fmtJt($kpi['today_hpp']) }}</span>
                        <span class="text-primary font-bold">Margin
                            {{ $kpi['today_sales'] > 0 ? round(($kpi['today_profit'] / $kpi['today_sales']) * 100, 1) : 0 }}%</span>
                    </div>
                </div>

                {{-- KPI 3: Piutang Belum Lunas (Tempo) --}}
                <div
                    class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-sm border border-error/25 flex flex-col justify-between hover:bg-surface-container transition-colors">
                    <div class="flex items-center justify-between mb-space-sm">
                        <span
                            class="font-label-sm text-label-sm text-error uppercase tracking-wider font-bold flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                            Piutang Belum Lunas (Tempo)
                        </span>
                        <div class="w-8 h-8 rounded-lg bg-error/15 flex items-center justify-center text-error">
                            <span class="material-symbols-outlined text-[18px]">pending_actions</span>
                        </div>
                    </div>
                    <div>
                        <div class="font-display-lg text-display-lg text-on-surface font-extrabold tracking-tight">
                            {{ $fmtJt($kpi['tempo_total']) }}</div>
                    </div>
                    <div
                        class="mt-space-md pt-space-xs border-t border-white/5 flex items-center justify-between font-label-sm text-label-sm">
                        <span class="text-error font-semibold">{{ $kpi['tempo_rows'] }} Faktur Tempo Aktif</span>
                        <span class="text-on-surface-variant">{{ $kpi['tempo_count'] }} Pelanggan</span>
                    </div>
                </div>

                {{-- KPI 4: Lunas Masuk Kas --}}
                <div
                    class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-sm border border-white/5 flex flex-col justify-between hover:bg-surface-container transition-colors">
                    <div class="flex items-center justify-between mb-space-sm">
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Lunas
                            Masuk Kas (Hari Ini)</span>
                        <div class="w-8 h-8 rounded-lg bg-tertiary/15 flex items-center justify-center text-tertiary">
                            <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                        </div>
                    </div>
                    <div>
                        <div class="font-display-lg text-display-lg text-on-surface font-extrabold tracking-tight">
                            {{ $fmtJt($kpi['lunas_total']) }}</div>
                    </div>
                    <div
                        class="mt-space-md pt-space-xs border-t border-white/5 flex items-center justify-between font-label-sm text-label-sm text-on-surface-variant">
                        <span>{{ $kpi['lunas_count'] }} Faktur Lunas • Rata-rata {{ $fmtJt($avgTicket) }}</span>
                        <span class="text-tertiary font-bold uppercase">Kas Terkunci</span>
                    </div>
                </div>
            </div>

            {{-- ============ FILTER BAR & TABEL RIWAYAT (PERATAAN SELARAS DENGAN BARANG MASUK) ============ --}}
            <div class="relative z-10 flex flex-col gap-space-md">
                <form method="GET" action="{{ route('outgoing-goods.index') }}" id="filterForm"
                    class="p-space-md rounded-2xl bg-surface-container-low/90 backdrop-blur-md border border-white/5 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-space-md shadow-sm">
                    <div class="relative flex-1">
                        <span
                            class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                        <input name="search" value="{{ request('search') }}" oninput="debouncedFilter()"
                            placeholder="Cari No. Invoice / SKU / Nama Barang / Pelanggan..."
                            class="w-full h-10 pl-10 pr-space-md rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/60 font-body-sm text-body-sm border border-white/5 focus:outline-none focus:border-primary" />
                    </div>

                    <div class="flex items-center gap-space-xs flex-wrap shrink-0">
                        <button type="submit" name="status" value="SEMUA"
                            class="h-9 px-space-md rounded-xl font-headline-sm text-body-sm flex items-center gap-1.5 transition-all cursor-pointer
                        {{ $status === 'SEMUA' ? 'bg-surface-container-high text-on-surface shadow-sm font-bold border border-white/10' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                            <span>Semua ({{ $kpi['total_count'] }})</span>
                        </button>
                        <button type="submit" name="status" value="LUNAS"
                            class="h-9 px-space-md rounded-xl font-body-sm text-body-sm flex items-center gap-1.5 transition-all cursor-pointer
                        {{ $status === 'LUNAS' ? 'bg-primary text-on-primary shadow-sm font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                            <span>Lunas ({{ $kpi['lunas_all'] }})</span>
                        </button>
                        <button type="submit" name="status" value="TEMPO"
                            class="h-9 px-space-md rounded-xl font-body-sm text-body-sm flex items-center gap-1.5 transition-all cursor-pointer
                        {{ $status === 'TEMPO' ? 'bg-error text-on-error shadow-sm font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ $status === 'TEMPO' ? 'bg-on-error' : 'bg-error' }}"></span>
                            <span>Tempo ({{ $kpi['tempo_all'] }})</span>
                        </button>
                        <a href="{{ route('outgoing-goods.index') }}"
                            class="h-9 w-9 rounded-xl bg-surface-container hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant transition-colors"
                            title="Reset Filter">
                            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                        </a>
                    </div>
                </form>

                <div
                    class="rounded-2xl bg-surface-container-low/90 backdrop-blur-md border border-white/5 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="border-b border-white/5 bg-surface-container font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">
                                    <th class="py-space-md px-space-md">No. Invoice & Waktu</th>
                                    <th class="py-space-md px-space-md">Pelanggan / Mitra</th>
                                    <th class="py-space-md px-space-md">Nama Barang & SKU</th>
                                    <th class="py-space-md px-space-md text-center">Keluar</th>
                                    <th class="py-space-md px-space-md text-center">Harga Modal → Jual</th>
                                    <th class="py-space-md px-space-md text-center">Keuntungan Bersih</th>
                                    <th class="py-space-md px-space-md text-center">Total Faktur</th>
                                    <th class="py-space-md px-space-md text-center">Status & Tempo</th>
                                    <th class="py-space-md px-space-md text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 font-body-sm text-body-sm text-on-surface">
                                @forelse ($transactions as $trx)
                                    @php
                                        $isLunas = $trx->payment_status === 'lunas';
                                        $paidPct = $trx->paid_pct;
                                        $detailPayload = [
                                            'no_invoice' => $trx->no_invoice,
                                            'customer' => $trx->customer->name,
                                            'customer_type' => $trx->customer->type ?? 'Mitra Grosir',
                                            'sold_at' => $trx->sold_at->format('d F Y • \P\u\k\u\l H:i') . ' WIB',
                                            'status' => $isLunas ? 'LUNAS (KAS MASUK)' : 'TEMPO / PIUTANG',
                                            'is_lunas' => $isLunas,
                                            'due_date' => $trx->due_date?->format('d F Y') ?? 'Lunas Tunai / Transfer',
                                            'sku' => $trx->product->sku,
                                            'ratio' => $trx->product->box_pcs_ratio,
                                            'product' => $trx->product->name,
                                            'qty' => $num($trx->box_qty) . ' Box (' . $num($trx->total_pcs) . ' Pcs)',
                                            'hpp_box' => $fmt($trx->hpp_per_box) . ' / Box',
                                            'sell_box' => $fmt($trx->sell_per_box) . ' / Box',
                                            'subtotal' => $fmt($trx->subtotal),
                                            'profit' => ($trx->profit >= 0 ? '+' : '-') . $fmt(abs($trx->profit)),
                                            'print_url' => route('outgoing-goods.receipt', $trx),
                                        ];
                                        $rollbackPayload = [
                                            'no_invoice' => $trx->no_invoice,
                                            'customer' => $trx->customer->name,
                                            'product' => $trx->product->name,
                                            'qty' =>
                                                '+' . $num($trx->total_pcs) . ' Pcs (' . $num($trx->box_qty) . ' Box)',
                                            'subtotal' => $fmt($trx->subtotal),
                                            'status' => strtoupper($trx->payment_status),
                                        ];
                                    @endphp
                                    <tr class="hover:bg-surface-container/60 transition-colors group">
                                        <td class="py-space-md px-space-md whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span
                                                    class="font-label-md text-label-md text-on-surface font-bold">{{ $trx->no_invoice }}</span>
                                                <span class="font-label-sm text-label-sm text-on-surface-variant mt-0.5">
                                                    {{ $trx->sold_at->format('d M Y • H:i') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-space-md px-space-md">
                                            <div class="flex flex-col">
                                                <span
                                                    class="font-headline-sm text-body-md text-on-surface font-bold group-hover:text-primary transition-colors">{{ $trx->customer->name }}</span>
                                                <span
                                                    class="font-label-sm text-label-sm text-on-surface-variant">{{ $trx->customer->type ?? 'Mitra Grosir' }}</span>
                                            </div>
                                        </td>
                                        <td class="py-space-md px-space-md">
                                            <div class="flex flex-col">
                                                <span class="text-on-surface font-bold">{{ $trx->product->name }}</span>
                                                <span
                                                    class="font-label-sm text-label-sm text-tertiary font-semibold mt-0.5">
                                                    {{ $trx->product->sku }} • {{ $trx->product->box_pcs_ratio }} Pcs/Box
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-space-md px-space-md text-center whitespace-nowrap">
                                            <div class="flex flex-col items-center">
                                                <span
                                                    class="font-headline-sm text-body-md font-bold text-on-surface">{{ $num($trx->box_qty) }}
                                                    Box</span>
                                                <span
                                                    class="font-label-sm text-label-sm text-primary font-bold">-{{ $num($trx->total_pcs) }}
                                                    Pcs</span>
                                            </div>
                                        </td>
                                        <td class="py-space-md px-space-md text-center whitespace-nowrap">
                                            <div class="flex flex-col items-center font-label-md text-label-md">
                                                <span class="text-on-surface-variant">HPP:
                                                    {{ $fmt($trx->hpp_per_box) }}<span
                                                        class="text-label-sm">/Box</span></span>
                                                <span class="text-on-surface font-bold">Jual:
                                                    {{ $fmt($trx->sell_per_box) }}<span
                                                        class="text-label-sm font-normal text-on-surface-variant">/Box</span></span>
                                            </div>
                                        </td>
                                        <td class="py-space-md px-space-md text-center whitespace-nowrap">
                                            <div class="flex flex-col items-center">
                                                <div
                                                    class="inline-flex items-center gap-1 px-space-sm py-0.5 rounded-md {{ $trx->profit >= 0 ? 'bg-primary/15 text-primary' : 'bg-error/15 text-error' }} font-label-md text-label-md font-bold">
                                                    <span>{{ $trx->profit >= 0 ? '+' : '-' }}{{ $fmt(abs($trx->profit)) }}</span>
                                                </div>
                                                <span class="font-label-sm text-label-sm text-on-surface-variant mt-0.5">
                                                    Margin
                                                    {{ $trx->hpp_per_box > 0 ? round((($trx->sell_per_box - $trx->hpp_per_box) / $trx->hpp_per_box) * 100, 1) : 0 }}%
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-space-md px-space-md text-center whitespace-nowrap">
                                            <span
                                                class="font-headline-sm text-body-md font-extrabold text-on-surface">{{ $fmt($trx->subtotal) }}</span>
                                        </td>
                                        <td class="py-space-md px-space-md text-center whitespace-nowrap">
                                            @if ($isLunas)
                                                <div
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/15 text-primary font-label-sm text-label-sm font-bold">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                                    <span>Lunas</span>
                                                </div>
                                            @else
                                                <div class="flex flex-col items-center">
                                                    <div
                                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-error/15 text-error font-label-sm text-label-sm font-bold">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                                                        <span>Tempo {{ $trx->due_date?->format('d M') }}</span>
                                                    </div>
                                                    <span
                                                        class="font-label-sm text-label-sm text-error font-semibold mt-0.5">
                                                        {{ $paidPct > 0 ? "Terbayar {$paidPct}%" : 'Belum Dibayar' }}
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-space-md px-space-md text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button type="button" data-detail="{{ json_encode($detailPayload) }}"
                                                    onclick="openInvoiceDetail(this)"
                                                    class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-bright text-on-surface flex items-center justify-center transition-colors cursor-pointer"
                                                    title="Lihat Detail Faktur">
                                                    <span class="material-symbols-outlined text-[17px]">visibility</span>
                                                </button>
                                                <a href="{{ route('outgoing-goods.receipt', $trx) }}" target="_blank"
                                                    class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-bright text-on-surface flex items-center justify-center transition-colors"
                                                    title="Cetak Invoice">
                                                    <span class="material-symbols-outlined text-[17px]">print</span>
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('outgoing-goods.rollback', $trx) }}"
                                                    id="rollback-form-{{ $trx->id }}" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="button"
                                                        data-form-id="rollback-form-{{ $trx->id }}"
                                                        data-rollback="{{ json_encode($rollbackPayload) }}"
                                                        onclick="confirmRollback(this)"
                                                        class="w-8 h-8 rounded-lg bg-surface-container hover:bg-error/20 text-on-surface-variant hover:text-error flex items-center justify-center transition-colors cursor-pointer"
                                                        title="Batalkan / Rollback Faktur">
                                                        <span class="material-symbols-outlined text-[17px]">delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-space-xl text-center text-on-surface-variant">
                                            <div class="flex flex-col items-center gap-2">
                                                <span
                                                    class="material-symbols-outlined text-[40px] text-surface-container-highest">receipt_long</span>
                                                <span>Belum ada transaksi penjualan yang cocok.</span>
                                                <button type="button" onclick="openSaleModal()"
                                                    class="mt-1 px-space-md py-1.5 rounded-xl bg-primary/15 text-primary font-headline-sm text-body-sm hover:bg-primary/25 transition-colors cursor-pointer">
                                                    + Input Penjualan Pertama
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="px-space-lg py-space-md bg-surface-container border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-space-sm font-label-sm text-label-sm text-on-surface-variant">
                        <div class="flex items-center gap-space-sm">
                            @if ($transactions->total() > 0)
                                <span>Menampilkan <strong>{{ $transactions->firstItem() }} -
                                        {{ $transactions->lastItem() }}</strong> dari
                                    <strong>{{ $transactions->total() }}</strong> Faktur Keluar</span>
                                <span>•</span>
                                <span>Halaman {{ $transactions->currentPage() }} dari
                                    {{ $transactions->lastPage() }}</span>
                                <span>•</span>
                                <span class="text-primary font-semibold">Laba Batch: +{{ $fmt($batchProfit) }}</span>
                            @else
                                <span>Belum ada data</span>
                            @endif
                        </div>
                        <div>{{ $transactions->links() }}</div>
                    </div>
                </div>
            </div>

            {{-- ============ FOOTER CALLOUT (TANPA TOMBOL GANDA) ============ --}}
            <div
                class="relative z-10 mt-space-lg p-space-md rounded-2xl bg-primary/5 border border-primary/20 flex items-center gap-space-md">
                <div class="w-10 h-10 rounded-xl bg-primary/15 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px]">info</span>
                </div>
                <div class="flex flex-col">
                    <h3 class="font-headline-sm text-body-md text-on-surface font-bold">Aturan Stok & Kunci Laba Historis
                    </h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant max-w-3xl">
                        Harga modal per Box otomatis terkunci mengikuti transaksi Barang Masuk terakhir saat nota dibuat,
                        dan mengurangi stok fisik gudang. Membatalkan faktur akan mengembalikan stok barang secara otomatis.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @include('outgoing-goods.partials.modals')
@endsection

@push('scripts')
    <script>
        (function() {
            const $ = id => document.getElementById(id);
            const fmtRp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
            const parseDigits = val => parseInt(String(val || '').replace(/\D/g, ''), 10) || 0;
            const formatThousands = num => num > 0 ? Math.round(num).toLocaleString('id-ID') : '';
            let pendingConfirmCallback = null;

            /* ============ JAM & TANGGAL REALTIME (WIB) ============ */
            function updateRealtimeClocks() {
                const now = new Date();
                const pad = n => String(n).padStart(2, '0');

                const shortDateTime =
                    `${pad(now.getDate())}/${pad(now.getMonth() + 1)}/${now.getFullYear()} • ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())} WIB`;
                const saleInput = $('liveSaleDateTime');
                if (saleInput) saleInput.textContent = shortDateTime;

                const dailyClock = $('liveDailyProfitClock');
                if (dailyClock) {
                    const fullDate = now.toLocaleDateString('id-ID', {
                        weekday: 'long',
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric',
                    });
                    dailyClock.textContent =
                        `${fullDate} • Pukul ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())} WIB`;
                }
            }

            window.openModal = id => {
                updateRealtimeClocks();
                $(id)?.classList.remove('hidden');
            };
            window.closeModal = id => $(id)?.classList.add('hidden');

            /* ============ INLINE TAMBAH PELANGGAN (SEPERTI BARANG MASUK) ============ */
            window.toggleQuickCustomerBox = function(forceState) {
                const box = $('inlineCustomerBox');
                if (!box) return;
                if (typeof forceState === 'boolean') {
                    box.classList.toggle('hidden', !forceState);
                } else {
                    box.classList.toggle('hidden');
                }
                if (!box.classList.contains('hidden')) {
                    $('quickCustName')?.focus();
                }
            };

            /* ============ FORMAT HARGA JUAL RIBUAN OTOMATIS ============ */
            window.onSellBoxDisplayInput = function(el) {
                const raw = parseDigits(el.value);
                el.value = formatThousands(raw);
                if ($('sellBoxInput')) {
                    $('sellBoxInput').value = raw;
                }
                calculateProfit();
            };

            function syncSellBoxDisplay(amount) {
                const raw = Math.max(0, Math.round(Number(amount) || 0));
                if ($('sellBoxInput')) $('sellBoxInput').value = raw;
                if ($('sellBoxDisplay')) $('sellBoxDisplay').value = formatThousands(raw);
            }

            /* ============ MODAL KONFIRMASI UNIVERSAL (GAYA BARANG MASUK) ============ */
            window.showConfirmModal = function({
                title = 'Apakah Anda yakin?',
                message = '',
                details = [],
                variant = 'danger',
                confirmLabel = 'Ya, Lanjutkan',
                confirmIcon = 'check_circle',
                onConfirm = null,
            }) {
                const iconWrap = $('confirmIconWrap');
                const iconEl = $('confirmIcon');
                const proceedBtn = $('confirmProceedBtn');

                $('confirmTitle').textContent = title;
                $('confirmMessage').textContent = message;
                $('confirmProceedLabel').textContent = confirmLabel;
                $('confirmProceedIcon').textContent = confirmIcon;

                if (variant === 'primary') {
                    iconWrap.className =
                        'w-11 h-11 rounded-xl bg-primary/15 text-primary flex items-center justify-center shrink-0';
                    iconEl.textContent = 'point_of_sale';
                    proceedBtn.className =
                        'h-10 px-5 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-body-sm font-bold inline-flex items-center gap-1.5 transition-all cursor-pointer';
                } else if (variant === 'warning') {
                    iconWrap.className =
                        'w-11 h-11 rounded-xl bg-secondary-container/30 text-secondary flex items-center justify-center shrink-0';
                    iconEl.textContent = 'restart_alt';
                    proceedBtn.className =
                        'h-10 px-5 rounded-xl bg-secondary hover:bg-secondary/90 text-on-secondary font-headline-sm text-body-sm font-bold inline-flex items-center gap-1.5 transition-all cursor-pointer';
                } else {
                    iconWrap.className =
                        'w-11 h-11 rounded-xl bg-error/15 text-error flex items-center justify-center shrink-0';
                    iconEl.textContent = 'delete_forever';
                    proceedBtn.className =
                        'h-10 px-5 rounded-xl bg-error hover:bg-error/90 text-on-error font-headline-sm text-body-sm font-bold inline-flex items-center gap-1.5 transition-all cursor-pointer';
                }

                const detailBox = $('confirmDetailBox');
                if (details.length > 0) {
                    detailBox.innerHTML = details.map(row => `
                <div class="flex items-center justify-between gap-3 py-1">
                    <span class="text-on-surface-variant">${row.label}</span>
                    <strong class="${row.colorClass || 'text-on-surface'} text-right">${row.value}</strong>
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

            $('confirmProceedBtn')?.addEventListener('click', function() {
                const cb = pendingConfirmCallback;
                pendingConfirmCallback = null;
                closeModal('confirmActionModal');
                if (typeof cb === 'function') cb();
            });

            window.confirmRollback = function(btn) {
                const formId = btn.dataset.formId;
                const data = JSON.parse(btn.dataset.rollback);
                showConfirmModal({
                    title: 'Konfirmasi Batalkan Faktur Keluar',
                    message: 'Tindakan ini akan membatalkan faktur penjualan dan mengembalikan stok fisik ke gudang.',
                    variant: 'danger',
                    confirmLabel: 'Ya, Batalkan Faktur',
                    confirmIcon: 'delete',
                    details: [{
                            label: 'No. Faktur',
                            value: data.no_invoice
                        },
                        {
                            label: 'Produk Terdampak',
                            value: data.product
                        },
                        {
                            label: 'Pengembalian Stok',
                            value: data.qty,
                            colorClass: 'text-primary'
                        },
                    ],
                    onConfirm: () => document.getElementById(formId)?.submit(),
                });
            };

            window.confirmSubmitSale = function() {
                const form = $('saleForm');
                if (!form || !form.reportValidity()) return;

                const d = getProductData();
                if (!d) return;

                const boxQty = parseInt($('qtyInput').value, 10) || 0;
                if (boxQty <= 0 || boxQty > d.stockBox) return;

                const sellBox = parseFloat($('sellBoxInput').value) || 0;
                if (sellBox <= 0) {
                    $('sellBoxDisplay')?.focus();
                    return;
                }

                const totalPcs = boxQty * d.pcsPerBox;
                const omzet = sellBox * boxQty;
                const profit = omzet - (d.hpp * boxQty);
                const status = $('paymentStatusInput').value === 'lunas' ? 'LUNAS → Kas Masuk' :
                    'TEMPO → Buku Tagihan';
                const custSelect = $('customerSelect');
                const custName = custSelect?.options[custSelect.selectedIndex]?.text || 'Mitra';

                showConfirmModal({
                    title: 'Konfirmasi Posting Penjualan',
                    message: 'Stok fisik akan dipotong otomatis dan jurnal transaksi akan diteruskan sesuai metode pembayaran.',
                    variant: profit < 0 ? 'warning' : 'primary',
                    confirmLabel: 'Ya, Simpan Penjualan',
                    confirmIcon: 'check_circle',
                    details: [{
                            label: 'Pelanggan / Mitra',
                            value: custName
                        },
                        {
                            label: 'Barang & Kuantitas',
                            value: `${d.name} (${boxQty} Box / ${totalPcs.toLocaleString('id-ID')} Pcs)`
                        },
                        {
                            label: 'Total Tagihan',
                            value: fmtRp(omzet)
                        },
                        {
                            label: profit >= 0 ? 'Keuntungan Bersih' : 'Estimasi Rugi',
                            value: (profit >= 0 ? '+' : '-') + fmtRp(Math.abs(profit)),
                            colorClass: profit >= 0 ? 'text-primary' : 'text-error'
                        },
                        {
                            label: 'Alur Pembayaran',
                            value: status
                        },
                    ],
                    onConfirm: () => form.submit(),
                });
            };

            window.confirmResetSaleForm = function() {
                showConfirmModal({
                    title: 'Reset Form Penjualan?',
                    message: 'Semua isian kuantitas Box, harga jual, dan metode pembayaran yang belum disimpan akan dikembalikan ke awal.',
                    variant: 'warning',
                    confirmLabel: 'Ya, Reset Form',
                    confirmIcon: 'restart_alt',
                    onConfirm: () => {
                        $('qtyInput').value = 1;
                        setPaymentMode('lunas');
                        toggleQuickCustomerBox(false);
                        onProductChange(true);
                    },
                });
            };

            window.openSaleModal = function() {
                openModal('saleFormModal');
                if ($('productSelect')) {
                    onProductChange(false);
                }
            };

            window.openInvoiceDetail = function(btn) {
                const data = JSON.parse(btn.dataset.detail);
                $('mdInvNo').textContent = data.no_invoice;
                $('mdSoldAt').textContent = data.sold_at;
                $('mdCustomer').textContent = data.customer;
                $('mdCustomerType').textContent = data.customer_type;
                $('mdStatus').textContent = data.status;
                $('mdStatus').className = data.is_lunas ? 'text-primary font-extrabold uppercase' :
                    'text-error font-extrabold uppercase';
                $('mdDueDate').textContent = data.due_date;
                $('mdSku').textContent = data.sku;
                $('mdProduct').textContent = data.product;
                $('mdRatio').textContent = `Aturan Konversi: 1 Box = ${data.ratio} Pcs`;
                $('mdQty').textContent = data.qty;
                $('mdHppBox').textContent = data.hpp_box;
                $('mdSellBox').textContent = data.sell_box;
                $('mdSubtotal').textContent = data.subtotal;
                $('mdProfit').textContent = data.profit;
                $('mdPrintLink').href = data.print_url;
                openModal('invoiceDetailModal');
            };

            window.submitQuickCustomer = async function() {
                const nameInput = $('quickCustName');
                const typeInput = $('quickCustType');
                const phoneInput = $('quickCustPhone');
                const errEl = $('quickCustomerError');
                const btn = $('quickCustomerSubmit');

                if (!nameInput?.value.trim()) {
                    nameInput?.focus();
                    return;
                }

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
                        body: JSON.stringify({
                            name: nameInput.value.trim(),
                            type: typeInput?.value || 'Toko Kelontong',
                            phone: phoneInput?.value.trim() || null,
                        }),
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
                    nameInput.value = '';
                    if (phoneInput) phoneInput.value = '';
                    toggleQuickCustomerBox(false);
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
                    name: opt.dataset.name,
                    sku: opt.dataset.sku,
                    hpp: parseInt(opt.dataset.hpp || 0, 10),
                    pcsPerBox: Math.max(1, parseInt(opt.dataset.pcsPerBox || 1, 10)),
                    stock: parseInt(opt.dataset.stock || 0, 10),
                    stockBox: parseInt(opt.dataset.stockBox || 0, 10),
                };
            }

            window.onProductChange = function(forceSuggest = true) {
                const d = getProductData();
                if (!d) return;

                $('hppBoxHint').textContent = 'Modal HPP: ' + fmtRp(d.hpp);
                $('hppPerBoxInput').value = d.hpp;

                const currentSell = parseFloat($('sellBoxInput').value) || 0;
                if (forceSuggest || currentSell <= 0) {
                    syncSellBoxDisplay(Math.round((d.hpp * 1.063) / 500) * 500);
                } else {
                    syncSellBoxDisplay(currentSell);
                }

                $('qtyInput').setAttribute('max', Math.max(1, d.stockBox));
                if (parseInt($('qtyInput').value, 10) > d.stockBox) {
                    $('qtyInput').value = Math.max(1, d.stockBox);
                }

                calculateProfit();
            };

            window.calculateProfit = function() {
                const d = getProductData();
                if (!d) return;

                const boxQty = parseInt($('qtyInput').value, 10) || 0;
                const sellBox = parseFloat($('sellBoxInput').value) || 0;
                const totalPcs = boxQty * d.pcsPerBox;
                const hppTotal = d.hpp * boxQty;
                const omzet = sellBox * boxQty;
                const profit = omzet - hppTotal;
                const marginPct = omzet > 0 ? ((profit / omzet) * 100).toFixed(1) : 0;
                const sellPcs = sellBox / d.pcsPerBox;

                $('stockDisplay').textContent = `${d.stockBox} Box (${d.stock.toLocaleString('id-ID')} Pcs)`;
                $('totalPcsDisplay').textContent = `${totalPcs.toLocaleString('id-ID')} Pcs`;
                $('sellPcsDisplay').textContent = fmtRp(sellPcs);

                $('calcOmzet').textContent = fmtRp(omzet);
                $('footerLockHint').textContent =
                    `Modal HPP ${fmtRp(d.hpp)}/Box • Jual ${fmtRp(sellBox)}/Box (${fmtRp(sellPcs)}/Pcs).`;

                const belowWarn = $('belowCostWarning');
                const calcProfitEl = $('calcProfit');
                const calcMarginEl = $('calcMarginPct');

                if (sellBox > 0 && sellBox < d.hpp) {
                    belowWarn?.classList.remove('hidden');
                    calcProfitEl.className =
                        'font-display-lg text-headline-md text-error font-extrabold tracking-tight';
                    calcProfitEl.textContent = '-' + fmtRp(Math.abs(profit));
                    calcMarginEl.className = 'font-label-sm text-label-sm text-error font-bold';
                    calcMarginEl.textContent = `(Rugi ${marginPct}%)`;
                } else {
                    belowWarn?.classList.add('hidden');
                    calcProfitEl.className =
                        'font-display-lg text-headline-md text-primary font-extrabold tracking-tight';
                    calcProfitEl.textContent = '+' + fmtRp(Math.max(0, profit));
                    calcMarginEl.className = 'font-label-sm text-label-sm text-primary font-bold';
                    calcMarginEl.textContent = `(Margin ${marginPct}%)`;
                }

                const errBanner = $('stockErrorBanner');
                const submitBtn = $('submitSaleBtn');

                if (boxQty > d.stockBox || boxQty <= 0) {
                    errBanner.classList.remove('hidden');
                    $('stockErrorMsg').textContent =
                        `Permintaan ${boxQty} Box melebihi sisa stok fisik (${d.stockBox} Box).`;
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
                }
            };

            window.adjustQty = function(delta) {
                const input = $('qtyInput');
                const current = parseInt(input.value, 10) || 0;
                input.value = Math.max(1, current + delta);
                calculateProfit();
            };

            window.resetQtyToMax = function() {
                const d = getProductData();
                if (!d) return;
                $('qtyInput').value = Math.max(1, d.stockBox);
                calculateProfit();
            };

            window.setPaymentMode = function(mode) {
                const inputStatus = $('paymentStatusInput');
                if (!inputStatus) return;

                const lunas = $('pay-lunas-btn');
                const tempo = $('pay-tempo-btn');
                const picker = $('tempo-due-picker');
                const cashAccountPicker = $('cash-account-picker');
                const statusBadge = $('paymentStatusBadge');
                const inputDue = $('dueDateInput');

                if (mode === 'lunas') {
                    lunas.className =
                        'h-10 rounded-xl font-headline-sm text-body-sm flex items-center justify-center gap-1.5 transition-all bg-primary text-on-primary shadow-sm font-bold cursor-pointer';
                    tempo.className =
                        'h-10 rounded-xl font-headline-sm text-body-sm flex items-center justify-center gap-1.5 transition-all bg-surface-container text-on-surface-variant hover:text-on-surface cursor-pointer';
                    picker.classList.add('hidden');
                    cashAccountPicker.classList.remove('hidden');
                    if (statusBadge) {
                        statusBadge.textContent = 'LUNAS';
                        statusBadge.className = 'font-label-sm text-label-sm text-primary font-extrabold uppercase';
                    }
                    inputStatus.value = 'lunas';
                    if (inputDue) inputDue.required = false;
                } else {
                    tempo.className =
                        'h-10 rounded-xl font-headline-sm text-body-sm flex items-center justify-center gap-1.5 transition-all bg-error text-on-error shadow-sm font-bold cursor-pointer';
                    lunas.className =
                        'h-10 rounded-xl font-headline-sm text-body-sm flex items-center justify-center gap-1.5 transition-all bg-surface-container text-on-surface-variant hover:text-on-surface cursor-pointer';
                    picker.classList.remove('hidden');
                    cashAccountPicker.classList.add('hidden');
                    if (statusBadge) {
                        statusBadge.textContent = 'TEMPO';
                        statusBadge.className = 'font-label-sm text-label-sm text-error font-extrabold uppercase';
                    }
                    inputStatus.value = 'tempo';
                    if (inputDue) inputDue.required = true;
                }
            };

            let filterTimer;
            window.debouncedFilter = function() {
                clearTimeout(filterTimer);
                filterTimer = setTimeout(() => $('filterForm').submit(), 500);
            };

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    const topModals = ['confirmActionModal', 'invoiceDetailModal', 'dailyProfitModal',
                        'saleFormModal'
                    ];
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
                updateRealtimeClocks();
                setInterval(updateRealtimeClocks, 1000);

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
