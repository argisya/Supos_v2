@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $fmt    = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $fmtJt  = function ($n) {
        $n = (float) $n;
        if (abs($n) >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 2, ',', '.') . ' M';
        if (abs($n) >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 2, ',', '.') . ' Jt';
        if (abs($n) >= 1_000)         return 'Rp ' . number_format($n / 1_000, 1, ',', '.') . ' Rb';
        return 'Rp ' . number_format((int) $n, 0, ',', '.');
    };
    $num = fn ($n) => number_format((int) $n, 0, ',', '.');
@endphp

@section('content')
<style>
@media print {
    body, html { background: #ffffff !important; color: #111827 !important; }
    aside, nav, .print\:hidden { display: none !important; }
    main { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    .bg-surface-container-low,
    .bg-surface-container,
    .bg-surface-container-lowest\/50,
    .bg-surface-container-lowest\/60,
    .bg-surface-container\/50 {
        background: #f9fafb !important;
        border: 1px solid #e5e7eb !important;
        box-shadow: none !important;
        color: #111827 !important;
    }
    .text-on-surface { color: #111827 !important; }
    .text-on-surface-variant, .text-outline { color: #4b5563 !important; }
    .text-primary { color: #059669 !important; }
    .text-tertiary { color: #d97706 !important; }
    .text-secondary { color: #2563eb !important; }
    .text-error { color: #dc2626 !important; }
}
</style>

<div class="relative w-full px-space-lg py-space-md">
<div class="absolute top-2 right-1/4 w-96 h-96 bg-tertiary-container/10 rounded-full blur-3xl pointer-events-none -z-10 print:hidden"></div>
<div class="absolute top-64 left-10 w-96 h-96 bg-primary-container/10 rounded-full blur-3xl pointer-events-none -z-10 print:hidden"></div>

{{-- ============ HEADER ============ --}}
<header class="flex flex-col xl:flex-row xl:items-end justify-between gap-space-md pb-space-lg">
    <div class="flex flex-col gap-space-xs">
        <nav class="flex items-center gap-space-xs text-on-surface-variant font-label-md text-label-md">
            <span>SUPOS | Sinar Utama</span>
            <span class="material-symbols-outlined text-[14px] text-outline">chevron_right</span>
            <span>Manajemen Eksekutif</span>
            <span class="material-symbols-outlined text-[14px] text-outline">chevron_right</span>
            <span class="text-primary font-semibold">Dashboard Overview</span>
        </nav>
        <div class="flex flex-wrap items-center gap-space-sm pt-0.5">
            <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Executive Overview & Analisis Kinerja</h1>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container-high text-primary font-label-sm text-label-sm shadow-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-primary animate-ping"></span>
                Sinkron: {{ now()->diffForHumans() }} • Multi-Gudang Terhubung
            </span>
        </div>
    </div>

    {{-- TOOLBAR TOMBOL HEADER SEJAJAR PRESISI (h-10) --}}
    <div class="flex flex-wrap items-center gap-2.5 flex-shrink-0 print:hidden">
        {{-- Segmented Period Switcher --}}
        <div class="inline-flex items-center h-10 bg-surface-container-low p-1 rounded-lg shadow-sm border border-surface-container-highest/50">
            @foreach(['today' => 'Hari Ini', '7d' => '7 Hari', 'month' => 'Bulan Ini'] as $key => $label)
                @php $isActive = $period === $key; @endphp
                <a href="{{ route('dashboard.index', ['period' => $key]) }}"
                   class="h-8 px-3 rounded-md inline-flex items-center justify-center font-label-md text-label-md whitespace-nowrap transition-all
                   {{ $isActive ? 'bg-primary-container text-on-primary-container font-bold shadow-sm' : 'text-on-surface-variant hover:text-on-surface' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Tombol Unduh PDF --}}
        <button onclick="window.print()" type="button"
                class="h-10 px-4 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface font-label-md text-label-md inline-flex items-center justify-center gap-2 whitespace-nowrap shadow-sm cursor-pointer transition-colors">
            <span class="material-symbols-outlined text-tertiary text-[18px]">picture_as_pdf</span>
            <span>Unduh PDF</span>
        </button>

        {{-- Tombol Primary: Transaksi Baru --}}
        <a href="{{ route('outgoing-goods.index') }}"
           class="h-10 px-4 rounded-lg bg-primary-container text-on-primary-container hover:brightness-110 font-label-md text-label-md font-bold inline-flex items-center justify-center gap-2 whitespace-nowrap shadow-md transition-all">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            <span>Transaksi Baru</span>
        </a>
    </div>
</header>

{{-- ============ 4 KPI CARDS ============ --}}
<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-space-md mb-space-lg">

    {{-- KPI 1: Omzet --}}
    <div class="bg-surface-container-low rounded-xl p-space-md shadow-md flex flex-col justify-between">
        <div class="flex items-start justify-between">
            <div class="flex flex-col">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-outline">TOTAL OMZET {{ strtoupper($periodLabel) }}</span>
                <span class="font-headline-lg text-headline-lg text-on-surface mt-1 tracking-tight">{{ $fmt($periodRevenue) }}</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[22px]">payments</span>
            </div>
        </div>
        <div class="py-space-sm">
            <svg class="w-full h-9 text-primary overflow-visible" fill="none" viewBox="0 0 200 36">
                <path d="M0 28 L30 22 L60 26 L90 14 L120 18 L150 8 L180 12 L200 4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"></path>
                <path d="M0 28 L30 22 L60 26 L90 14 L120 18 L150 8 L180 12 L200 4 L200 36 L0 36 Z" fill="currentColor" fill-opacity="0.12"></path>
            </svg>
        </div>
        <div class="flex items-center justify-between pt-space-xs bg-surface-container-lowest/50 -mx-space-md -mb-space-md p-space-md rounded-b-xl">
            <div class="flex items-center gap-1 text-primary font-label-md text-label-md">
                <span class="material-symbols-outlined text-[16px]">trending_up</span>
                <span>+{{ $marginPct }}% margin</span>
            </div>
            <span class="font-label-sm text-label-sm text-on-surface-variant">Hari ini: <strong class="text-on-surface">{{ $fmtJt($todayRevenue) }}</strong> ({{ $num($todayBoxQty) }} Box)</span>
        </div>
    </div>

    {{-- KPI 2: Laba --}}
    <div class="bg-surface-container-low rounded-xl p-space-md shadow-md flex flex-col justify-between">
        <div class="flex items-start justify-between">
            <div class="flex flex-col">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-outline">LABA KOTOR (LOCKED HPP)</span>
                <span class="font-headline-lg text-headline-lg text-tertiary mt-1 tracking-tight">{{ $fmt($periodProfit) }}</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-tertiary">
                <span class="material-symbols-outlined text-[22px]">account_balance</span>
            </div>
        </div>
        <div class="py-space-sm">
            <svg class="w-full h-9 text-tertiary overflow-visible" fill="none" viewBox="0 0 200 36">
                <path d="M0 30 L30 25 L65 24 L100 16 L130 19 L165 9 L200 6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"></path>
                <path d="M0 30 L30 25 L65 24 L100 16 L130 19 L165 9 L200 6 L200 36 L0 36 Z" fill="currentColor" fill-opacity="0.12"></path>
            </svg>
        </div>
        <div class="flex items-center justify-between pt-space-xs bg-surface-container-lowest/50 -mx-space-md -mb-space-md p-space-md rounded-b-xl">
            <div class="flex items-center gap-1 text-tertiary font-label-md text-label-md" title="Laba dari transaksi yang sudah lunas">
                <span class="material-symbols-outlined text-[16px]">verified</span>
                <span>Lunas: {{ $fmtJt($periodRealizedProfit) }}</span>
            </div>
            <span class="font-label-sm text-label-sm text-on-surface-variant">Hari Ini: <strong class="text-on-surface">{{ $fmtJt($todayProfit) }}</strong> ({{ $todayMargin }}%)</span>
        </div>
    </div>

    {{-- KPI 3: Arus Kas --}}
    <div class="bg-surface-container-low rounded-xl p-space-md shadow-md flex flex-col justify-between">
        <div class="flex items-start justify-between">
            <div class="flex flex-col">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-outline">ARUS KAS BERSIH (NET FLOW)</span>
                <span class="font-headline-lg text-headline-lg {{ $netCash >= 0 ? 'text-primary' : 'text-error' }} mt-1 tracking-tight">
                    {{ $netCash >= 0 ? '+' : '' }}{{ $fmt($netCash) }}
                </span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary-fixed">
                <span class="material-symbols-outlined text-[22px]">price_change</span>
            </div>
        </div>
        <div class="py-space-sm flex flex-col gap-1 text-body-sm">
            <div class="flex justify-between items-center text-on-surface-variant">
                <span>Kas Masuk (In):</span>
                <span class="font-label-md text-label-md text-primary font-semibold">{{ $fmt($periodCashIn) }}</span>
            </div>
            <div class="flex justify-between items-center text-on-surface-variant">
                <span>Kas Keluar (Out):</span>
                <span class="font-label-md text-label-md text-error font-semibold">{{ $fmt($periodCashOut) }}</span>
            </div>
        </div>
        <div class="flex items-center justify-between pt-space-xs bg-surface-container-lowest/50 -mx-space-md -mb-space-md p-space-md rounded-b-xl">
            <span class="font-label-sm text-label-sm text-on-surface-variant">Likuiditas Kas + Bank:</span>
            <span class="font-label-md text-label-md text-on-surface font-bold">{{ $fmt($cashBalance) }}</span>
        </div>
    </div>

    {{-- KPI 4: Aset --}}
    <div class="bg-surface-container-low rounded-xl p-space-md shadow-md flex flex-col justify-between">
        <div class="flex items-start justify-between">
            <div class="flex flex-col">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-outline">TOTAL ASET FISIK & PIUTANG</span>
                <span class="font-headline-lg text-headline-lg text-secondary mt-1 tracking-tight">{{ $fmt($totalAssets) }}</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined text-[22px]">inventory</span>
            </div>
        </div>
        <div class="py-space-sm flex flex-col gap-1 text-body-sm">
            <div class="flex justify-between items-center text-on-surface-variant">
                <span>Stok Fisik ({{ $num(floor($stockBoxCount)) }} Box):</span>
                <span class="font-label-md text-label-md text-on-surface font-semibold">{{ $fmt($stockValue) }}</span>
            </div>
            <div class="flex justify-between items-center text-on-surface-variant">
                <span>Piutang Aktif ({{ $piutangCount }} Faktur):</span>
                <span class="font-label-md text-label-md text-secondary-fixed-dim font-semibold">{{ $fmt($piutangTotal) }}</span>
            </div>
        </div>
        <div class="flex items-center justify-between pt-space-xs bg-surface-container-lowest/50 -mx-space-md -mb-space-md p-space-md rounded-b-xl">
            <span class="font-label-sm text-label-sm text-on-surface-variant">Kolektibilitas Tagihan:</span>
            <span class="font-label-md text-label-md text-primary font-bold">{{ $collectRate }}% Sehat</span>
        </div>
    </div>
</section>

{{-- ============ RADAR KRITIS ============ --}}
<section class="mb-space-lg">
    <div class="flex items-center justify-between mb-space-sm">
        <div class="flex items-center gap-space-xs">
            <span class="material-symbols-outlined text-error text-[20px] animate-pulse">crisis_alert</span>
            <span class="font-headline-sm text-headline-sm text-on-surface">Radar Perhatian Operasional Kritis</span>
            @php $alertCount = ($habis + $menipis > 0 ? 1 : 0) + ($overdueCount > 0 ? 1 : 0) + ($upcomingPayables->count() > 0 ? 1 : 0); @endphp
            <span class="px-2 py-0.5 rounded-full bg-error-container/80 text-on-error-container font-label-sm font-bold">{{ $alertCount }} Peringatan Aktif</span>
        </div>
        <span class="font-label-sm text-label-sm text-on-surface-variant">Membutuhkan Otorisasi Eksekutif</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">

        {{-- Radar 1: Stok --}}
        <div class="bg-surface-container-low rounded-xl p-space-md shadow-md flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-error-container text-on-error-container font-label-sm font-bold">
                        <span class="material-symbols-outlined text-[14px]">warning</span>
                        {{ $habis + $menipis }} SKU BUTUH KULAKAN
                    </span>
                    <span class="font-label-md text-label-md text-error font-semibold">{{ $habis }} SKU Kosong</span>
                </div>
                <p class="font-body-md text-on-surface mt-space-sm font-semibold">Stok Utama Habis & Menipis Cepat</p>
                <div class="mt-space-xs space-y-1">
                    @forelse($criticalStock as $p)
                        @php
                            $box = $p->stock_pcs / max(1, (int) $p->box_pcs_ratio);
                            $isEmpty = (int) $p->stock_pcs === 0;
                        @endphp
                        <div class="flex items-center justify-between text-body-sm text-on-surface-variant bg-surface-container px-2 py-1 rounded">
                            <span class="truncate">{{ $p->name }}</span>
                            <span class="font-label-sm font-bold ml-2 {{ $isEmpty ? 'text-error' : 'text-primary' }}">
                                {{ $isEmpty ? '0 Box' : floor($box) . ' Box' }}
                            </span>
                        </div>
                    @empty
                        <div class="text-body-sm text-primary py-1">✓ Semua stok aman</div>
                    @endforelse
                </div>
            </div>
            <a href="{{ route('incoming-goods.index') }}" class="mt-space-md w-full h-9 px-3 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface font-label-md text-label-md flex items-center justify-center gap-1.5 transition-colors shadow-sm print:hidden">
                <span class="material-symbols-outlined text-[16px] text-tertiary">add_shopping_cart</span>
                <span>Buat PO Kulakan Cepat</span>
            </a>
        </div>

        {{-- Radar 2: Piutang Overdue --}}
        <div class="bg-surface-container-low rounded-xl p-space-md shadow-md flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-surface-variant text-on-surface-variant font-label-sm font-bold">
                        <span class="material-symbols-outlined text-[14px] text-error">schedule</span>
                        {{ $overdueCount }} MITRA TERTUNDA
                    </span>
                    <span class="font-label-md text-label-md text-error font-semibold">{{ $fmt($overdueTotal) }}</span>
                </div>
                <p class="font-body-md text-on-surface mt-space-sm font-semibold">Piutang Toko Melebihi Batas Tempo</p>
                <div class="mt-space-xs space-y-1">
                    @forelse($overdueReceivables as $r)
                        @php
                            $days = (int) \Illuminate\Support\Carbon::parse($r->due_date)->diffInDays(now()->startOfDay());
                            $remaining = max(0, (float) $r->subtotal - (float) $r->paid_amount);
                        @endphp
                        <div class="flex items-center justify-between text-body-sm text-on-surface-variant bg-surface-container px-2 py-1 rounded">
                            <span class="truncate">{{ $r->customer?->name ?? 'Pelanggan' }} ({{ $r->no_invoice }})</span>
                            <span class="font-label-sm text-error font-bold ml-2">{{ $fmtJt($remaining) }} (+{{ $days }} hr)</span>
                        </div>
                    @empty
                        <div class="text-body-sm text-primary py-1">✓ Tidak ada piutang overdue</div>
                    @endforelse
                </div>
            </div>
            <a href="{{ route('receivables.index', ['filter' => 'overdue']) }}" class="mt-space-md w-full h-9 px-3 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface font-label-md text-label-md flex items-center justify-center gap-1.5 transition-colors shadow-sm print:hidden">
                <span class="material-symbols-outlined text-[16px] text-primary">chat</span>
                <span>Tagih Semua via WhatsApp</span>
            </a>
        </div>

        {{-- Radar 3: Hutang Supplier --}}
        <div class="bg-surface-container-low rounded-xl p-space-md shadow-md flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-surface-variant text-on-surface-variant font-label-sm font-bold">
                        <span class="material-symbols-outlined text-[14px] text-tertiary">event_upcoming</span>
                        JATUH TEMPO &lt; 5 HARI
                    </span>
                    <span class="font-label-md text-label-md text-tertiary font-semibold">{{ $fmt($upcomingTotal) }}</span>
                </div>
                <p class="font-body-md text-on-surface mt-space-sm font-semibold">Hutang Distributor Principal Siap Bayar</p>
                <div class="mt-space-xs space-y-1">
                    @forelse($upcomingPayables as $u)
                        @php
                            $days = (int) now()->startOfDay()->diffInDays(\Illuminate\Support\Carbon::parse($u->due_date), false);
                            $remaining = max(0, (float) $u->subtotal - (float) $u->paid_amount);
                        @endphp
                        <div class="flex items-center justify-between text-body-sm text-on-surface-variant bg-surface-container px-2 py-1 rounded">
                            <span class="truncate">{{ $u->supplier?->name ?? 'Supplier' }}</span>
                            <span class="font-label-sm font-bold ml-2 {{ $days <= 1 ? 'text-error' : 'text-on-surface' }}">
                                {{ $fmtJt($remaining) }} ({{ $days < 0 ? 'Lewat' : ($days == 0 ? 'Hari Ini' : $days . ' hr') }})
                            </span>
                        </div>
                    @empty
                        <div class="text-body-sm text-primary py-1">✓ Tidak ada hutang jatuh tempo</div>
                    @endforelse
                </div>
            </div>
            <a href="{{ route('cash-outs.index') }}" class="mt-space-md w-full h-9 px-3 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface font-label-md text-label-md flex items-center justify-center gap-1.5 transition-colors shadow-sm print:hidden">
                <span class="material-symbols-outlined text-[16px] text-primary">outbox</span>
                <span>Proses Kas Keluar</span>
            </a>
        </div>
    </div>
</section>

{{-- ============ CHART & TOP 5 SKU ============ --}}
<section class="grid grid-cols-1 lg:grid-cols-2 gap-space-md mb-space-lg">

    {{-- LEFT: Chart 7 hari --}}
    <div class="bg-surface-container-low rounded-xl p-space-lg shadow-md flex flex-col justify-between">
        <div>
            <div class="flex flex-wrap items-center justify-between gap-space-xs pb-space-sm">
                <div class="flex flex-col">
                    <span class="font-headline-sm text-on-surface">Arus Kas & Omzet 7 Hari Terakhir</span>
                    <span class="font-body-sm text-on-surface-variant">Komparasi Omzet Penjualan vs Kas Masuk & Pengeluaran</span>
                </div>
                <div class="flex items-center gap-space-sm text-label-sm">
                    <span class="flex items-center gap-1.5 text-primary"><span class="w-2.5 h-2.5 rounded-full bg-primary"></span>Omzet</span>
                    <span class="flex items-center gap-1.5 text-tertiary"><span class="w-2.5 h-2.5 rounded-full bg-tertiary"></span>Kas In</span>
                    <span class="flex items-center gap-1.5 text-error"><span class="w-2.5 h-2.5 rounded-full bg-error"></span>Kas Out</span>
                </div>
            </div>

            <div class="w-full h-52 mt-space-md bg-surface-container-lowest/60 rounded-lg p-space-sm flex flex-col justify-between">
                <div class="w-full flex-1 flex items-end justify-between gap-2 px-2 pt-2">
                    @foreach($chartDays as $day)
                        <div class="flex-1 flex flex-col items-center gap-1 h-full justify-end group {{ $day['is_today'] ? 'bg-surface-container/60 rounded p-0.5' : '' }}">
                            <div class="w-full flex items-end justify-center gap-1 h-36">
                                <div class="w-2 bg-primary rounded-t transition-all" style="height: {{ $day['omzet_h'] }}%" title="Omzet {{ $fmtJt($day['omzet']) }}"></div>
                                <div class="w-2 bg-tertiary rounded-t transition-all" style="height: {{ $day['cash_in_h'] }}%" title="Kas In {{ $fmtJt($day['cash_in']) }}"></div>
                                <div class="w-2 bg-error rounded-t transition-all" style="height: {{ $day['cash_out_h'] }}%" title="Kas Out {{ $fmtJt($day['cash_out']) }}"></div>
                            </div>
                            <span class="font-label-sm text-label-sm {{ $day['is_today'] ? 'text-primary font-bold' : 'text-on-surface-variant' }}">
                                {{ $day['is_today'] ? 'Hari Ini' : $day['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Breakdown Kas --}}
            <div class="mt-space-md grid grid-cols-3 gap-space-xs text-center">
                <div class="bg-surface-container p-2.5 rounded-lg">
                    <span class="font-label-sm text-on-surface-variant block">Tunai Langsung</span>
                    <span class="font-headline-sm text-primary font-bold">{{ $kasTunaiPct }}%</span>
                    <span class="font-label-sm text-on-surface-variant">{{ $fmtJt($kasTunai) }}</span>
                </div>
                <div class="bg-surface-container p-2.5 rounded-lg">
                    <span class="font-label-sm text-on-surface-variant block">Pelunasan Piutang</span>
                    <span class="font-headline-sm text-tertiary font-bold">{{ $kasPiutangPct }}%</span>
                    <span class="font-label-sm text-on-surface-variant">{{ $fmtJt($kasPiutang) }}</span>
                </div>
                <div class="bg-surface-container p-2.5 rounded-lg">
                    <span class="font-label-sm text-on-surface-variant block">Non-Operasional</span>
                    <span class="font-headline-sm text-secondary font-bold">{{ $kasLainPct }}%</span>
                    <span class="font-label-sm text-on-surface-variant">{{ $fmtJt($kasLain) }}</span>
                </div>
            </div>
        </div>
        <div class="mt-space-md pt-space-sm bg-surface-container/50 -mx-space-lg -mb-space-lg px-space-lg pb-space-md rounded-b-xl flex items-center justify-between">
            <div class="flex items-center gap-space-xs text-primary font-label-md">
                <span class="material-symbols-outlined text-[18px]">verified</span>
                <span>Zero Leakage • Double-Entry Audit Terkunci</span>
            </div>
            <span class="font-label-sm text-on-surface-variant">Integritas Kas 100%</span>
        </div>
    </div>

    {{-- RIGHT: Health & Top 5 --}}
    <div class="bg-surface-container-low rounded-xl p-space-lg shadow-md flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-space-sm">
                <div class="flex flex-col">
                    <span class="font-headline-sm text-on-surface">Kesehatan Inventori & Top 5 SKU</span>
                    <span class="font-body-sm text-on-surface-variant">Kontribusi Margin Terbesar</span>
                </div>
                <span class="px-2.5 py-1 rounded bg-surface-container font-label-md text-primary font-semibold">{{ $totalSku }} SKU Aktif</span>
            </div>

            {{-- Health bar --}}
            <div class="mt-space-xs bg-surface-container-lowest/60 rounded-lg p-space-sm">
                <div class="flex items-center justify-between text-label-sm mb-1.5">
                    <span class="text-on-surface">Komposisi Ketersediaan Stok</span>
                    <span class="text-on-surface-variant">{{ $aman }} Aman • {{ $menipis }} Tipis • {{ $habis }} Habis</span>
                </div>
                <div class="w-full h-3 rounded-full bg-surface-container overflow-hidden flex">
                    <div class="h-full bg-primary" style="width: {{ $amanPct }}%"></div>
                    <div class="h-full bg-tertiary-container" style="width: {{ $menipisPct }}%"></div>
                    <div class="h-full bg-error" style="width: {{ $habisPct }}%"></div>
                </div>
                <div class="flex justify-between items-center mt-2 text-label-sm text-on-surface-variant">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-primary"></span>Aman ({{ $amanPct }}%)</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-tertiary-container"></span>Tipis ({{ $menipisPct }}%)</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-error"></span>Habis ({{ $habisPct }}%)</span>
                </div>
            </div>

            {{-- Top 5 --}}
            <div class="mt-space-md space-y-2">
                @forelse($topProducts as $i => $tp)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-surface-container hover:bg-surface-bright transition-colors">
                        <div class="flex items-center gap-space-sm min-w-0">
                            <span class="w-6 h-6 rounded {{ $i === 0 ? 'bg-primary-container text-on-primary-container' : 'bg-surface-container-high text-on-surface' }} font-label-sm flex items-center justify-center font-bold">{{ $i + 1 }}</span>
                            <div class="flex flex-col min-w-0">
                                <span class="font-headline-sm text-body-md text-on-surface truncate font-semibold">{{ $tp->product?->name ?? 'Produk Dihapus' }}</span>
                                <span class="font-label-sm text-on-surface-variant">{{ $tp->product?->sku ?? '-' }} • {{ $num($tp->total_box) }} Box Terjual</span>
                            </div>
                        </div>
                        <div class="flex flex-col text-right pl-2">
                            <span class="font-label-md text-primary font-bold">+{{ $fmt($tp->total_profit) }}</span>
                            <span class="font-label-sm text-on-surface-variant">Omzet {{ $fmtJt($tp->total_omzet) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-space-md text-center text-on-surface-variant text-sm">
                        Belum ada data penjualan.
                    </div>
                @endforelse
            </div>
        </div>
        <div class="mt-space-md pt-space-sm bg-surface-container/50 -mx-space-lg -mb-space-lg px-space-lg pb-space-md rounded-b-xl flex items-center justify-between">
            <span class="font-label-sm text-on-surface-variant">Top 5 SKU menyumbang {{ $topPct }}% dari total laba</span>
            <a href="{{ route('products.index') }}" class="font-label-sm text-primary hover:underline flex items-center gap-1 print:hidden">
                Lihat Katalog
                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
            </a>
        </div>
    </div>
</section>

{{-- ============ AKTIVITAS TERKINI ============ --}}
<section class="bg-surface-container-low rounded-xl p-space-md shadow-md mb-space-lg">
    <div class="flex flex-wrap items-center justify-between gap-space-sm pb-space-md">
        <div class="flex items-center gap-space-xs">
            <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[18px]">receipt_long</span>
            </div>
            <div class="flex flex-col">
                <span class="font-headline-sm text-on-surface">Aktivitas Transaksi Lintas Modul Terkini</span>
                <span class="font-body-sm text-on-surface-variant">Audit real-time: Penjualan, Pelunasan Piutang, PO Kulakan & Beban Kas</span>
            </div>
        </div>
        <div class="flex items-center gap-space-xs">
            <span class="inline-flex items-center gap-1 text-primary font-label-md bg-surface-container px-2.5 py-1 rounded">
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                Live Stream
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left font-body-md">
            <thead>
                <tr class="bg-surface-container text-on-surface-variant font-label-sm uppercase tracking-wider">
                    <th class="py-2.5 px-3 rounded-l">Waktu & Ref ID</th>
                    <th class="py-2.5 px-3">Tipe & Modul</th>
                    <th class="py-2.5 px-3">Entitas</th>
                    <th class="py-2.5 px-3">Rincian</th>
                    <th class="py-2.5 px-3 text-right">Dampak Finansial</th>
                    <th class="py-2.5 px-3 rounded-r text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-transparent">
                @forelse($activities as $a)
                    <tr class="hover:bg-surface-container/60 transition-colors group">
                        <td class="py-3 px-3">
                            <div class="flex flex-col">
                                <span class="font-label-md text-on-surface font-bold">{{ $a['at']->format('H:i:s') }} WIB</span>
                                <span class="font-label-sm text-outline font-mono">{{ $a['ref'] }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded {{ $a['type_class'] }} font-label-sm font-semibold">
                                <span class="material-symbols-outlined text-[14px]">{{ $a['icon'] }}</span>
                                {{ $a['type'] }}
                            </span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex flex-col">
                                <span class="font-headline-sm text-body-md text-on-surface font-semibold">{{ $a['entity'] }}</span>
                                <span class="font-label-sm text-on-surface-variant">{{ $a['entity_sub'] }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            <span class="text-body-sm text-on-surface-variant truncate max-w-xs block">{{ $a['detail'] }}</span>
                        </td>
                        <td class="py-3 px-3 text-right">
                            <span class="font-label-md {{ $a['amount_class'] }} font-bold">{{ $a['amount'] }}</span>
                            <span class="block font-label-sm text-outline">{{ $a['amount_sub'] }}</span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-surface-container {{ $a['status_class'] }} font-label-sm">
                                <span class="material-symbols-outlined text-[12px]">{{ $a['status_icon'] }}</span>
                                {{ $a['status'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-space-xl text-center text-on-surface-variant">
                            Belum ada aktivitas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-space-md pt-space-xs flex items-center justify-between text-on-surface-variant font-label-md">
        <span>Menampilkan {{ $activities->count() }} mutasi terkini</span>
        <a href="{{ route('cash-ins.index') }}" class="text-primary hover:underline flex items-center gap-1 print:hidden">
            Buka Buku Jurnal Lengkap
            <span class="material-symbols-outlined text-[16px]">open_in_new</span>
        </a>
    </div>
</section>

{{-- ============ FOOTER ============ --}}
<footer class="bg-surface-container-low rounded-xl p-space-md shadow-sm flex flex-col md:flex-row items-center justify-between gap-space-md">
    <div class="flex items-center gap-space-md">
        <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary">
            <span class="material-symbols-outlined text-[24px]">security</span>
        </div>
        <div class="flex flex-col">
            <span class="font-headline-sm text-on-surface leading-tight">Protokol Keandalan Sistem SUPOS | Sinar Utama</span>
            <span class="font-body-sm text-on-surface-variant">Setiap box dan pcs terkunci secara matematis • Audit trail tidak dapat diedit manual</span>
        </div>
    </div>
    <div class="flex items-center gap-space-sm font-label-sm text-outline">
        <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-primary"></span>Box-Pcs Engine: V2.4 Active</span>
        <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-tertiary"></span>Realtime Sync</span>
        <span class="inline-flex items-center gap-1 text-on-surface font-semibold">Lisensi Enterprise: Aktif</span>
    </div>
</footer>
</div>
@endsection