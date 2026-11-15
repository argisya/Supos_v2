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
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-md">
            <div>
                <div class="flex items-center gap-space-sm">
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Barang Keluar & Kasir Grosir</h1>
                    <span class="inline-flex items-center gap-1 px-space-sm py-0.5 rounded-full bg-primary/15 text-primary font-label-sm text-label-sm border border-primary/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                        Auto-Profit Lock
                    </span>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1 max-w-2xl">
                    Kalkulator laba otomatis berbasis HPP terakhir. Pembayaran lunas terhubung ke Kas Masuk, transaksi tempo langsung masuk ke Buku Tagihan.
                </p>
            </div>
            <div class="flex items-center gap-space-sm flex-wrap">
                <button class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-body-sm text-body-sm flex items-center gap-space-xs transition-colors shadow-sm" type="button">
                    <span class="material-symbols-outlined text-[18px] text-tertiary">analytics</span>
                    <span>Rekap Laba Hari Ini</span>
                </button>
                <button class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-body-sm text-body-sm flex items-center gap-space-xs transition-colors shadow-sm" type="button">
                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                    <span>Cetak Invoice / Surat Jalan</span>
                </button>
                <a href="#quick-sale-panel" class="h-10 px-space-md rounded-xl bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-body-sm flex items-center gap-space-xs transition-all shadow-[0_0_20px_rgba(16,185,129,0.35)]">
                    <span class="material-symbols-outlined text-[20px]">add_shopping_cart</span>
                    <span>+ Input Penjualan Baru</span>
                </a>
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

        {{-- KPI 2: Estimasi Laba Bersih (highlighted) --}}
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

    {{-- ============ FORM KASIR ============ --}}
    <div class="relative z-10 mb-space-lg rounded-2xl bg-surface-container-low/95 backdrop-blur-xl border border-white/10 shadow-[0_12px_40px_rgba(0,0,0,0.45)] overflow-hidden" id="quick-sale-panel">
        <div class="px-space-lg py-space-md border-b border-white/5 bg-surface-container flex flex-col md:flex-row items-start md:items-center justify-between gap-space-sm">
            <div class="flex items-center gap-space-sm">
                <div class="w-8 h-8 rounded-lg bg-primary-container/20 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">calculate</span>
                </div>
                <div>
                    <h2 class="font-headline-md text-headline-sm text-on-surface">Kasir & Input Penjualan Grosir</h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Validasi stok fisik Box × Pcs dan estimasi margin real-time sebelum diposting.</p>
                </div>
            </div>
            <div class="flex items-center gap-space-sm">
                <span class="font-label-sm text-label-sm px-space-sm py-1 rounded bg-surface-container-high text-on-surface-variant">Validasi Stok: <strong class="text-primary font-bold">Ketat (Box Locked)</strong></span>
                <button type="button" onclick="resetSaleForm()" class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors" title="Reset Form">
                    <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                </button>
            </div>
        </div>

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="m-space-md p-space-sm mx-space-lg mt-space-md rounded-xl bg-error/10 border border-error/30 text-error font-body-sm">
                <strong class="flex items-center gap-1 mb-1"><span class="material-symbols-outlined text-[16px]">error</span> Periksa kembali:</strong>
                <ul class="list-disc list-inside ml-1">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        @if ($products->isEmpty())
            <div class="p-space-lg text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-[40px] text-surface-container-highest">inventory_2</span>
                <p class="font-body-md mt-2">Belum ada produk dengan stok tersedia. Silakan input <a href="{{ route('incoming-goods.index') }}" class="text-primary underline">Barang Masuk</a> terlebih dahulu.</p>
            </div>
        @else
            <form method="POST" action="{{ route('outgoing-goods.store') }}" id="saleForm">
                @csrf
                <input type="hidden" name="payment_status" id="paymentStatusInput" value="lunas">
                <input type="hidden" name="hpp_per_box" id="hppPerBoxInput" value="0">

                <div class="p-space-lg flex flex-col gap-space-lg">

                    {{-- Metadata Row --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-space-md">
                        {{-- Customer --}}
                        <div class="md:col-span-4 flex flex-col gap-space-xs">
                            <label class="font-label-md text-label-md text-on-surface-variant uppercase flex items-center justify-between">
                                <span>Pelanggan / Toko Mitra</span>
                                <button type="button" class="text-primary hover:underline lowercase font-body-sm text-body-sm">+ Pelanggan Baru</button>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">storefront</span>
                                <select name="customer_id" required
                                        class="w-full h-11 pl-10 pr-8 rounded-xl bg-surface-container text-on-surface font-body-md text-body-md border border-white/5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                                    @foreach ($customers as $c)
                                        <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>
                                            {{ $c->name }}@if($c->type) ({{ $c->type }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        {{-- Invoice (readonly display) --}}
                        <div class="md:col-span-3 flex flex-col gap-space-xs">
                            <label class="font-label-md text-label-md text-on-surface-variant uppercase">No. Faktur / Invoice</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">tag</span>
                                <input type="text" readonly value="{{ $suggestedInvoice }}"
                                       class="w-full h-11 pl-10 pr-space-md rounded-xl bg-surface-container/60 text-primary font-label-md text-label-md border border-white/5 cursor-not-allowed" />
                            </div>
                        </div>

                        {{-- Sold at --}}
                        <div class="md:col-span-2 flex flex-col gap-space-xs">
                            <label class="font-label-md text-label-md text-on-surface-variant uppercase">Tanggal & Waktu</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">schedule</span>
                                <input type="text" readonly value="{{ now()->format('d/m/Y H:i') }}"
                                       class="w-full h-11 pl-10 pr-space-sm rounded-xl bg-surface-container/60 text-on-surface font-label-md text-label-md border border-white/5" />
                            </div>
                        </div>

                        {{-- Payment toggle --}}
                        <div class="md:col-span-3 flex flex-col gap-space-xs">
                            <label class="font-label-md text-label-md text-on-surface-variant uppercase">Alur Pembayaran</label>
                            <div class="grid grid-cols-2 gap-1 p-1 rounded-xl bg-surface-container border border-white/5">
                                <button type="button" onclick="setPaymentMode('lunas')" id="pay-lunas-btn"
                                        class="h-9 rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all bg-primary-container text-on-primary-container shadow-sm font-semibold">
                                    <span class="material-symbols-outlined text-[16px]">payments</span>
                                    <span>Lunas</span>
                                </button>
                                <button type="button" onclick="setPaymentMode('tempo')" id="pay-tempo-btn"
                                        class="h-9 rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all text-on-surface-variant hover:text-on-surface">
                                    <span class="material-symbols-outlined text-[16px]">event_repeat</span>
                                    <span>Tempo</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Tempo due picker --}}
                    <div id="tempo-due-picker" class="hidden p-space-sm rounded-xl bg-secondary-container/20 border border-secondary-container/40 flex flex-col sm:flex-row items-center justify-between gap-space-sm">
                        <div class="flex items-center gap-space-sm text-secondary">
                            <span class="material-symbols-outlined text-[20px]">notification_important</span>
                            <span class="font-body-sm text-body-sm">Status Tempo: Faktur ini akan diteruskan ke <strong>Buku Tagihan Piutang</strong>.</span>
                        </div>
                        <div class="flex items-center gap-space-sm">
                            <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Jatuh Tempo:</label>
                            <input name="due_date" id="dueDateInput" type="date"
                                   value="{{ now()->addDays(14)->format('Y-m-d') }}"
                                   class="h-9 px-space-md rounded-lg bg-surface-container-high text-on-surface font-label-md text-label-md border border-white/10 focus:outline-none focus:ring-1 focus:ring-secondary" />
                        </div>
                    </div>

                    {{-- Product & Qty --}}
                    <div class="p-space-md rounded-xl bg-surface-container border border-white/5 flex flex-col gap-space-md">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-md items-end">

                            {{-- Product Select --}}
                            <div class="lg:col-span-5 flex flex-col gap-space-xs">
                                <label class="font-label-md text-label-md text-on-surface-variant uppercase flex items-center justify-between">
                                    <span>Pilih Barang Grosir (SKU)</span>
                                    <span class="text-tertiary font-label-sm" id="barcodeLabel">Barcode: —</span>
                                </label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">inventory_2</span>
                                    <select name="product_id" id="productSelect" onchange="onProductChange()" required
                                            class="w-full h-11 pl-10 pr-8 rounded-xl bg-surface-container-high text-on-surface font-headline-sm text-body-md border border-white/10 focus:outline-none focus:border-tertiary focus:ring-1 focus:ring-tertiary appearance-none cursor-pointer">
                                        @foreach ($products as $p)
                                            @php $ratio = $p->box_pcs_ratio; @endphp
                                            <option value="{{ $p->id }}"
                                                    data-sku="{{ $p->sku }}"
                                                    data-barcode="{{ $p->barcode }}"
                                                    data-hpp="{{ (int) ($p->last_hpp ?? 0) }}"
                                                    data-pcs-per-box="{{ $ratio }}"
                                                    data-stock="{{ $p->stock_pcs }}"
                                                    data-stock-box="{{ floor($p->stock_pcs / $ratio) }}"
                                                    @selected(old('product_id') == $p->id)>
                                                {{ $p->name }} (1 Box = {{ $ratio }} Pcs)
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] pointer-events-none">expand_more</span>
                                </div>
                            </div>

                            {{-- Stock Display --}}
                            <div class="lg:col-span-3 flex flex-col justify-end">
                                <div class="h-11 px-space-md rounded-xl bg-surface-container-low flex items-center justify-between border border-white/5">
                                    <div class="flex items-center gap-space-xs">
                                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                                        <div class="flex flex-col">
                                            <span class="font-label-sm text-label-sm text-on-surface-variant leading-none">Sisa Stok Fisik:</span>
                                            <span class="font-headline-sm text-body-sm text-on-surface font-bold leading-tight" id="stockDisplay">—</span>
                                        </div>
                                    </div>
                                    <span id="stockBadge" class="font-label-sm text-label-sm px-1.5 py-0.5 rounded bg-primary/20 text-primary font-bold">AMAN</span>
                                </div>
                            </div>

                            {{-- Qty --}}
                            <div class="lg:col-span-4 flex flex-col gap-space-xs">
                                <label class="font-label-md text-label-md text-on-surface-variant uppercase flex items-center justify-between">
                                    <span>Jumlah Box Terjual</span>
                                    <span class="text-tertiary font-label-md" id="pcsCounter">= 0 Pcs</span>
                                </label>
                                <div class="flex items-center gap-space-xs">
                                    <div class="relative flex-1">
                                        <input name="box_qty" id="qtyInput" type="number" min="1" value="{{ old('box_qty', 1) }}" required
                                               oninput="calculateProfit()"
                                               class="w-full h-11 px-space-md rounded-xl bg-surface-container-high text-on-surface font-headline-md text-headline-sm font-bold border border-white/10 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-center" />
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 font-label-md text-label-md text-on-surface-variant pointer-events-none">BOX</span>
                                    </div>
                                    <button type="button" onclick="adjustQty(-1)" class="w-11 h-11 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">remove</span>
                                    </button>
                                    <button type="button" onclick="adjustQty(1)" class="w-11 h-11 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">add</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Pricing fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-space-md pt-space-sm border-t border-white/5">
                            <div class="flex flex-col gap-space-xs">
                                <label class="font-label-sm text-label-sm text-on-surface-variant uppercase flex items-center gap-1">
                                    <span>Harga Modal Terakhir (HPP)</span>
                                    <span class="material-symbols-outlined text-primary text-[14px]" title="Snapshot dari data Barang Masuk">lock</span>
                                </label>
                                <div class="h-11 px-space-md rounded-xl bg-surface-container-low flex items-center justify-between border border-white/5">
                                    <span class="font-headline-sm text-body-md text-on-surface font-semibold" id="hppBoxDisplay">—</span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant">/ Box</span>
                                </div>
                                <span class="font-label-sm text-label-sm text-on-surface-variant/80">Snapshot saat transaksi</span>
                            </div>

                            <div class="flex flex-col gap-space-xs">
                                <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">HPP Satuan Terkecil</label>
                                <div class="h-11 px-space-md rounded-xl bg-surface-container-low flex items-center justify-between border border-white/5">
                                    <span class="font-headline-sm text-body-md text-on-surface-variant font-semibold" id="hppPcsDisplay">—</span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant">/ Pcs</span>
                                </div>
                                <span class="font-label-sm text-label-sm text-tertiary" id="ratioInfo">—</span>
                            </div>

                            <div class="flex flex-col gap-space-xs">
                                <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Harga Jual per Box</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 font-label-md text-label-md text-on-surface-variant">Rp</span>
                                    <input name="sell_per_box" id="sellBoxInput" type="number" value="{{ old('sell_per_box', 0) }}" required
                                           oninput="calculateProfit()"
                                           class="w-full h-11 pl-9 pr-space-md rounded-xl bg-surface-container-high text-on-surface font-headline-sm text-body-md font-bold border border-white/10 focus:outline-none focus:border-tertiary focus:ring-1 focus:ring-tertiary" />
                                </div>
                                <span class="font-label-sm text-label-sm text-primary font-semibold" id="marginBoxDisplay">—</span>
                            </div>

                            <div class="flex flex-col gap-space-xs">
                                <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Setara Harga per Pcs</label>
                                <div class="h-11 px-space-md rounded-xl bg-surface-container-low flex items-center justify-between border border-white/5">
                                    <span class="font-headline-sm text-body-md text-on-surface font-semibold" id="sellPcsDisplay">—</span>
                                    <span class="font-label-sm text-label-sm text-tertiary font-bold" id="marginPcsDisplay">—</span>
                                </div>
                                <span class="font-label-sm text-label-sm text-on-surface-variant/80">Retail Rekomendasi: <span id="retailRec">—</span></span>
                            </div>
                        </div>
                    </div>

                    {{-- Profit Calculator Banner --}}
                    <div class="p-space-md rounded-xl bg-linear-to-r from-surface-container via-surface-container-high to-surface-container border border-primary/30 shadow-[0_8px_32px_rgba(0,0,0,0.35)] flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-space-md relative overflow-hidden">
                        <div class="flex items-center gap-space-md">
                            <div class="w-12 h-12 rounded-xl bg-primary-container text-on-primary-container flex items-center justify-center shrink-0 shadow-[0_0_16px_rgba(16,185,129,0.4)]">
                                <span class="material-symbols-outlined text-[28px]">query_stats</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="font-label-sm text-label-sm text-primary uppercase font-bold tracking-wider flex items-center gap-1">
                                    <span>Mesin Kalkulasi Laba Grosir Real-Time</span>
                                    <span class="material-symbols-outlined text-[14px]">bolt</span>
                                </span>
                                <div class="flex items-baseline gap-space-sm mt-0.5 flex-wrap">
                                    <span class="font-headline-sm text-body-md text-on-surface-variant">Omzet Total:</span>
                                    <span class="font-headline-md text-headline-md text-on-surface font-bold tracking-tight" id="calcOmzet">Rp 0</span>
                                    <span class="text-on-surface-variant text-body-sm font-label-md" id="calcBoxInfo">(0 Box / 0 Pcs)</span>
                                </div>
                                <div class="flex items-center gap-space-md mt-1 text-label-md font-label-md text-on-surface-variant">
                                    <span>HPP Terkunci: <strong class="text-on-surface" id="calcHpp">Rp 0</strong></span>
                                    <span>•</span>
                                    <span>Status Routing: <strong class="text-primary" id="calcRouting">Kas Masuk Toko (Lunas)</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-space-md self-end lg:self-auto">
                            <div class="px-space-md py-space-sm rounded-xl bg-primary/10 border border-primary/30 flex flex-col items-center sm:items-end justify-center min-w-[200px]">
                                <span class="font-label-sm text-label-sm text-primary uppercase font-bold tracking-wider">Keuntungan Bersih (Locked)</span>
                                <div class="font-display-lg text-display-lg text-primary font-black tracking-tight leading-none mt-1" id="calcProfit">+Rp 0</div>
                                <span class="font-label-md text-label-md text-primary font-semibold mt-1" id="calcMarginPct">Margin: 0%</span>
                            </div>
                            <button type="submit"
                                    class="w-full sm:w-auto h-14 px-space-lg rounded-xl bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-headline-sm flex items-center justify-center gap-space-sm transition-all shadow-[0_0_24px_rgba(16,185,129,0.45)] hover:scale-[1.01] active:scale-[0.98]">
                                <span class="material-symbols-outlined text-[24px]">verified</span>
                                <span>Simpan Penjualan & Potong Stok Box</span>
                            </button>
                        </div>
                    </div>

                    {{-- Stock error --}}
                    <div id="stockErrorBanner" class="hidden p-space-md rounded-xl bg-error-container/40 border border-error/40 flex items-center justify-between text-on-error-container">
                        <div class="flex items-center gap-space-sm">
                            <span class="material-symbols-outlined text-[24px] text-error">gpp_maybe</span>
                            <div class="flex flex-col">
                                <strong class="font-headline-sm text-body-md text-error">Permintaan Melebihi Sisa Stok Fisik!</strong>
                                <span class="font-body-sm text-body-sm" id="stockErrorMsg">Jumlah melebihi sisa stok. Transaksi diblokir sistem pencegah selisih.</span>
                            </div>
                        </div>
                        <button type="button" onclick="resetQtyToMax()"
                                class="px-space-md py-1.5 rounded-lg bg-error text-on-error font-headline-sm text-body-sm">
                            Set ke Maksimal
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>

    {{-- ============ TABEL RIWAYAT ============ --}}
    <div class="relative z-10 flex flex-col gap-space-md">
        <form method="GET" action="{{ route('outgoing-goods.index') }}" id="filterForm"
              class="p-space-md rounded-2xl bg-surface-container-low/90 backdrop-blur-md border border-white/5 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-space-md shadow-sm">
            <div class="flex items-center gap-space-xs flex-wrap">
                <button type="submit" name="status" value="SEMUA"
                        class="h-9 px-space-md rounded-xl font-headline-sm text-body-sm flex items-center gap-1 transition-all
                        {{ $status === 'SEMUA' ? 'bg-primary text-on-primary shadow-sm font-semibold' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-bright hover:text-on-surface' }}">
                    <span>Semua</span>
                    <span class="w-5 h-5 rounded-full {{ $status === 'SEMUA' ? 'bg-on-primary/20 text-on-primary' : 'bg-surface-container text-on-surface-variant' }} text-label-sm font-bold flex items-center justify-center">{{ $kpi['total_count'] }}</span>
                </button>
                <button type="submit" name="status" value="LUNAS"
                        class="h-9 px-space-md rounded-xl font-body-sm text-body-sm flex items-center gap-1 transition-all
                        {{ $status === 'LUNAS' ? 'bg-primary text-on-primary shadow-sm font-semibold' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-bright hover:text-on-surface' }}">
                    <span>Lunas (Kas Masuk)</span>
                    <span class="w-5 h-5 rounded-full {{ $status === 'LUNAS' ? 'bg-on-primary/20 text-on-primary' : 'bg-surface-container text-tertiary' }} text-label-sm font-bold flex items-center justify-center">{{ $kpi['lunas_all'] }}</span>
                </button>
                <button type="submit" name="status" value="TEMPO"
                        class="h-9 px-space-md rounded-xl font-body-sm text-body-sm flex items-center gap-1 transition-all
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
                <a href="{{ route('outgoing-goods.index') }}" class="h-9 w-9 rounded-xl bg-surface-container hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant transition-colors" title="Reset">
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
                            @php $isLunas = $trx->payment_status === 'lunas'; @endphp
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
                                        <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $trx->customer->type }}</span>
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
                                        <button type="button" class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-bright text-on-surface flex items-center justify-center transition-colors" title="Lihat Faktur">
                                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        </button>
                                        <button type="button" class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-bright text-on-surface flex items-center justify-center transition-colors" title="Cetak Nota">
                                            <span class="material-symbols-outlined text-[16px]">print</span>
                                        </button>
                                        <form method="POST" action="{{ route('outgoing-goods.rollback', $trx) }}" class="inline"
                                              onsubmit="return confirm('KONFIRMASI ROLLBACK:\n\nBatalkan faktur {{ $trx->no_invoice }} ({{ $trx->product->name }})?\n\n- Stok fisik akan dikembalikan.\n- Piutang/kas terkait akan ditarik balik.')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="w-8 h-8 rounded-lg bg-surface-container hover:bg-error/20 text-on-surface-variant hover:text-error flex items-center justify-center transition-colors" title="Rollback">
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
@endsection

@push('scripts')
<script>
(function () {
    let currentMode = 'lunas';

    const $ = id => document.getElementById(id);
    const fmtRp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

    function getProductData() {
        const sel = $('productSelect');
        if (!sel || !sel.options[sel.selectedIndex]) return null;
        const opt = sel.options[sel.selectedIndex];
        return {
            sku:         opt.dataset.sku,
            barcode:     opt.dataset.barcode,
            hpp:         parseInt(opt.dataset.hpp || 0, 10),
            pcsPerBox:   parseInt(opt.dataset.pcsPerBox || 1, 10),
            stock:       parseInt(opt.dataset.stock || 0, 10),
            stockBox:    parseInt(opt.dataset.stockBox || 0, 10),
        };
    }

    window.onProductChange = function () {
        const d = getProductData();
        if (!d) return;

        $('barcodeLabel').textContent = 'Barcode: ' + (d.barcode || '—');
        $('hppBoxDisplay').textContent = fmtRp(d.hpp);
        $('hppPcsDisplay').textContent = 'Rp ' + (d.hpp / d.pcsPerBox).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        $('ratioInfo').textContent = 'Otomatis / ' + d.pcsPerBox + ' Pcs';
        $('hppPerBoxInput').value = d.hpp;

        // Suggest selling price: HPP + 6.3%
        if (!parseFloat($('sellBoxInput').value) || parseFloat($('sellBoxInput').value) === 0) {
            $('sellBoxInput').value = Math.round(d.hpp * 1.063 / 500) * 500;
        }

        // Set max qty
        $('qtyInput').setAttribute('max', Math.max(1, d.stockBox));
        if (parseInt($('qtyInput').value, 10) > d.stockBox) {
            $('qtyInput').value = Math.max(1, d.stockBox);
        }

        calculateProfit();
    };

    window.calculateProfit = function () {
        const d = getProductData();
        if (!d) return;

        const boxQty = parseInt($('qtyInput').value, 10) || 0;
        const sellBox = parseFloat($('sellBoxInput').value) || 0;

        const totalPcs = boxQty * d.pcsPerBox;
        const hppTotal = d.hpp * boxQty;
        const omzet = sellBox * boxQty;
        const profit = omzet - hppTotal;
        const marginPct = omzet > 0 ? ((profit / omzet) * 100).toFixed(2) : 0;
        const marginBox = sellBox - d.hpp;
        const marginPctBox = d.hpp > 0 ? ((marginBox / d.hpp) * 100).toFixed(1) : 0;
        const sellPcs = d.pcsPerBox > 0 ? sellBox / d.pcsPerBox : 0;
        const hppPcs = d.pcsPerBox > 0 ? d.hpp / d.pcsPerBox : 0;

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

        // Stock validation
        const errBanner = $('stockErrorBanner');
        const stockBadge = $('stockBadge');
        if (boxQty > d.stockBox) {
            errBanner.classList.remove('hidden');
            $('stockErrorMsg').textContent = `Permintaan ${boxQty} Box melebihi sisa fisik ${d.stockBox} Box. Transaksi diblokir sistem.`;
            stockBadge.textContent = 'OVER';
            stockBadge.className = 'font-label-sm text-label-sm px-1.5 py-0.5 rounded bg-error/20 text-error font-bold';
        } else if (boxQty > d.stockBox * 0.8) {
            errBanner.classList.add('hidden');
            stockBadge.textContent = 'LOW';
            stockBadge.className = 'font-label-sm text-label-sm px-1.5 py-0.5 rounded bg-tertiary/20 text-tertiary font-bold';
        } else {
            errBanner.classList.add('hidden');
            stockBadge.textContent = 'AMAN';
            stockBadge.className = 'font-label-sm text-label-sm px-1.5 py-0.5 rounded bg-primary/20 text-primary font-bold';
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
        currentMode = mode;
        const lunas = $('pay-lunas-btn');
        const tempo = $('pay-tempo-btn');
        const picker = $('tempo-due-picker');
        const routing = $('calcRouting');
        const inputStatus = $('paymentStatusInput');
        const inputDue = $('dueDateInput');

        if (mode === 'lunas') {
            lunas.className = 'h-9 rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all bg-primary-container text-on-primary-container shadow-sm font-semibold';
            tempo.className = 'h-9 rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all text-on-surface-variant hover:text-on-surface';
            picker.classList.add('hidden');
            routing.textContent = 'Kas Masuk Toko (Lunas)';
            routing.className = 'text-primary font-bold';
            inputStatus.value = 'lunas';
            if (inputDue) inputDue.required = false;
        } else {
            tempo.className = 'h-9 rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all bg-secondary text-on-secondary shadow-sm font-semibold';
            lunas.className = 'h-9 rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all text-on-surface-variant hover:text-on-surface';
            picker.classList.remove('hidden');
            routing.textContent = 'Buku Tagihan (Piutang Tempo)';
            routing.className = 'text-secondary font-bold';
            inputStatus.value = 'tempo';
            if (inputDue) inputDue.required = true;
        }
    };

    window.resetSaleForm = function () {
        if (confirm('Reset semua field form ke kondisi awal?')) {
            $('qtyInput').value = 1;
            $('sellBoxInput').value = 0;
            setPaymentMode('lunas');
            onProductChange();
        }
    };

    let filterTimer;
    window.debouncedFilter = function () {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => $('filterForm').submit(), 500);
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('productSelect')) {
            onProductChange();
        }
    });
})();
</script>
@endpush