@extends('layouts.app')

@section('title', 'Stok Barang')

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

    $amanPct    = $pct($aman, $totalProducts);
    $menipisPct = $pct($menipis, $totalProducts);
    $habisPct   = $pct($habis, $totalProducts);
    $availPct   = $pct($totalProducts - $habis, $totalProducts);
    $netDelta   = $todayInBox - $todayOutBox;
@endphp

@section('content')
<div class="flex flex-col w-full">
<div class="relative w-full overflow-hidden pb-12">
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-tertiary-container/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-80 -left-20 w-80 h-80 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>

    {{-- ============ PAGE HEADER ============ --}}
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-space-md py-space-lg">
        <div class="flex flex-col max-w-3xl">
            <div class="flex items-center gap-space-xs mb-1">
                <span class="font-label-sm text-label-sm text-primary uppercase tracking-wider bg-surface-container-high px-2 py-0.5 rounded-full">Automasi Gudang Terpadu</span>
                <span class="text-outline-variant">•</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant">Update Sinkron: {{ now()->diffForHumans() }}</span>
            </div>
            <h1 class="font-display-lg text-display-lg text-on-surface tracking-tight">Monitoring Stok Barang Realtime</h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1 leading-relaxed">
                Pantau ketersediaan stok fisik gudang dalam satuan Box & Pcs secara presisi. Terhubung otomatis dengan pencatatan Barang Masuk & Barang Keluar tanpa selisih.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-space-sm self-start xl:self-center">
            <button class="h-10 px-space-md rounded-xl bg-surface-container-low hover:bg-surface-container-high text-on-surface font-body-sm text-body-sm flex items-center gap-space-xs shadow-sm transition-all active:scale-95" type="button">
                <span class="material-symbols-outlined text-tertiary text-[18px]">tune</span>
                <span>Atur Batas Alert</span>
            </button>
            <button class="h-10 px-space-md rounded-xl bg-surface-container-low hover:bg-surface-container-high text-on-surface font-body-sm text-body-sm flex items-center gap-space-xs shadow-sm transition-all active:scale-95" type="button">
                <span class="material-symbols-outlined text-secondary text-[18px]">inventory</span>
                <span>Opname Stok Fisik</span>
            </button>
            <button class="h-10 px-space-md rounded-xl bg-primary hover:bg-primary-fixed text-on-primary font-body-sm text-body-sm flex items-center gap-space-xs shadow-md shadow-primary/20 transition-all active:scale-95" type="button">
                <span class="material-symbols-outlined text-[18px]">download</span>
                <span>Export Laporan (Excel/PDF)</span>
            </button>
        </div>
    </div>

    {{-- ============ ALERT BANNER ============ --}}
    @if($habis + $menipis > 0)
        <div class="mb-space-lg p-space-md rounded-xl bg-surface-container-low flex flex-col md:flex-row md:items-center justify-between gap-space-md shadow-sm relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-error"></div>
            <div class="flex items-center gap-space-md pl-1">
                <div class="w-10 h-10 rounded-xl bg-error-container/40 flex items-center justify-center text-error flex-shrink-0">
                    <span class="material-symbols-outlined text-[24px]">crisis_alert</span>
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center gap-space-xs">
                        <span class="font-headline-sm text-headline-sm text-on-surface">Peringatan: {{ $habis + $menipis }} SKU Membutuhkan Tindakan Pengadaan</span>
                        <span class="font-label-sm text-label-sm bg-error-container text-on-error-container px-1.5 py-0.5 rounded uppercase">Kritis</span>
                    </div>
                    <span class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                        {{ $habis }} SKU berstatus <span class="text-error font-semibold">Habis (0 Box)</span> dan {{ $menipis }} SKU <span class="text-tertiary font-semibold">Menipis (≤ 10 Box)</span>. Segera buat faktur penerimaan kulakan untuk menjaga kontinuitas order grosir.
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-space-sm pl-12 md:pl-0 flex-shrink-0">
                <a href="{{ route('incoming-goods.index') }}" class="h-9 px-space-md rounded-xl bg-error-container hover:bg-error text-on-error-container hover:text-on-error font-label-md text-label-md flex items-center gap-space-xs transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>
                    <span>+ Buat PO Kulakan di Barang Masuk</span>
                </a>
            </div>
        </div>
    @endif

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-space-md mb-space-lg">

        {{-- KPI 1: Nilai Aset --}}
        <div class="p-space-md rounded-xl bg-surface-container-low flex flex-col justify-between shadow-sm relative overflow-hidden group hover:bg-surface-container transition-all">
            <div class="flex items-center justify-between mb-space-sm">
                <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Total Aset Nilai Stok</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                </div>
            </div>
            <div class="flex flex-col">
                <div class="flex items-baseline gap-space-xs">
                    <span class="font-display-lg text-display-lg text-on-surface">{{ $fmtJt($totalAssetValue) }}</span>
                </div>
                <span class="font-body-sm text-body-sm text-on-surface-variant mt-1">Akumulasi sisa Box × HPP terakhir</span>
            </div>
            <div class="mt-space-md pt-space-xs flex items-center justify-between">
                <span class="font-label-sm text-label-sm text-primary flex items-center gap-0.5">
                    <span class="material-symbols-outlined text-[14px]">inventory</span> Basis HPP Historis
                </span>
                <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $totalProducts }} Master SKU</span>
            </div>
        </div>

        {{-- KPI 2: Volume Fisik --}}
        <div class="p-space-md rounded-xl bg-surface-container-low flex flex-col justify-between shadow-sm relative overflow-hidden group hover:bg-surface-container transition-all">
            <div class="flex items-center justify-between mb-space-sm">
                <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Total Volume Fisik</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined text-[18px]">inventory_2</span>
                </div>
            </div>
            <div class="flex flex-col">
                <div class="flex items-baseline gap-space-xs">
                    <span class="font-display-lg text-display-lg text-on-surface">{{ $num(floor($totalVolumeBox)) }}</span>
                    <span class="font-headline-sm text-headline-sm text-tertiary">Box</span>
                </div>
                <span class="font-body-sm text-body-sm text-on-surface-variant mt-1">Terkonversi: <span class="text-on-surface font-semibold">{{ $num($totalVolumePcs) }} Pcs</span> terdata</span>
            </div>
            <div class="mt-space-md pt-space-xs flex items-center justify-between">
                <span class="font-label-sm text-label-sm text-on-surface-variant">Rata-rata per SKU</span>
                <span class="font-label-sm text-label-sm text-on-surface font-semibold">{{ $totalProducts > 0 ? round($totalVolumeBox / $totalProducts, 1) : 0 }} Box</span>
            </div>
        </div>

        {{-- KPI 3: Health Distribution --}}
        <div class="p-space-md rounded-xl bg-surface-container-low flex flex-col justify-between shadow-sm relative overflow-hidden group hover:bg-surface-container transition-all">
            <div class="flex items-center justify-between mb-space-sm">
                <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Kesehatan Inventori</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined text-[18px]">health_and_safety</span>
                </div>
            </div>
            <div class="flex flex-col gap-1.5 my-1">
                <div class="flex h-2.5 w-full rounded-full overflow-hidden bg-surface-container-highest">
                    <div class="bg-primary h-full" style="width: {{ $amanPct }}%"></div>
                    <div class="bg-tertiary h-full" style="width: {{ $menipisPct }}%"></div>
                    <div class="bg-error h-full" style="width: {{ $habisPct }}%"></div>
                </div>
                <div class="grid grid-cols-3 gap-1 pt-1">
                    <div class="flex flex-col">
                        <span class="font-label-sm text-label-sm text-primary flex items-center gap-1 font-semibold">● {{ $aman }} Aman</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">&gt;10 Box</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-label-sm text-label-sm text-tertiary flex items-center gap-1 font-semibold">● {{ $menipis }} Menipis</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">1-10 Box</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-label-sm text-label-sm text-error flex items-center gap-1 font-semibold">● {{ $habis }} Habis</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">0 Box</span>
                    </div>
                </div>
            </div>
            <div class="mt-space-xs flex items-center justify-between">
                <span class="font-label-sm text-label-sm text-on-surface-variant">Tingkat Ketersediaan</span>
                <span class="font-label-sm text-label-sm text-primary font-semibold">{{ $availPct }}% Siap Jual</span>
            </div>
        </div>

        {{-- KPI 4: Mutasi Hari Ini --}}
        <div class="p-space-md rounded-xl bg-surface-container-low flex flex-col justify-between shadow-sm relative overflow-hidden group hover:bg-surface-container transition-all">
            <div class="flex items-center justify-between mb-space-sm">
                <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Mutasi Hari Ini</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">sync_alt</span>
                </div>
            </div>
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between p-2 rounded-lg bg-surface-container-high">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[18px]">arrow_circle_down</span>
                        <span class="font-body-sm text-body-sm text-on-surface">Masuk (Supplier)</span>
                    </div>
                    <span class="font-label-lg text-label-lg text-primary font-bold">+{{ $num($todayInBox) }} Box</span>
                </div>
                <div class="flex items-center justify-between p-2 rounded-lg bg-surface-container-high">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-error text-[18px]">arrow_circle_up</span>
                        <span class="font-body-sm text-body-sm text-on-surface">Keluar (Grosir/Sales)</span>
                    </div>
                    <span class="font-label-lg text-label-lg text-error font-bold">-{{ $num($todayOutBox) }} Box</span>
                </div>
            </div>
            <div class="mt-space-xs flex items-center justify-between">
                <span class="font-label-sm text-label-sm text-on-surface-variant">Net Delta Harian</span>
                <span class="font-label-sm text-label-sm {{ $netDelta >= 0 ? 'text-primary' : 'text-error' }} font-semibold">
                    {{ $netDelta >= 0 ? '+' : '' }}{{ $num($netDelta) }} Box
                </span>
            </div>
        </div>
    </div>

    {{-- ============ FILTER BAR ============ --}}
    <form method="GET" action="{{ route('stock.index') }}" id="filterForm" class="flex flex-col gap-space-sm mb-space-md">

        {{-- Status Tabs --}}
        <div class="flex flex-wrap items-center justify-between gap-space-sm bg-surface-container-low p-space-sm rounded-xl">
            <div class="flex items-center gap-1.5 overflow-x-auto">
                @php
                    $tabs = [
                        ['value' => 'SEMUA',   'label' => 'Semua Stok',         'count' => $totalProducts, 'dot' => null,      'count_class' => 'bg-primary/30 text-on-primary-container'],
                        ['value' => 'AMAN',    'label' => 'Aman (>10 Box)',     'count' => $aman,          'dot' => 'primary', 'count_class' => 'bg-surface-container-highest text-on-surface-variant'],
                        ['value' => 'MENIPIS', 'label' => 'Menipis (1-10 Box)', 'count' => $menipis,       'dot' => 'tertiary','count_class' => 'bg-tertiary/20 text-tertiary'],
                        ['value' => 'HABIS',   'label' => 'Habis (0 Box)',      'count' => $habis,         'dot' => 'error',   'count_class' => 'bg-error/20 text-error'],
                    ];
                @endphp
                @foreach($tabs as $tab)
                    @php $active = $status === $tab['value']; @endphp
                    <button type="submit" name="status" value="{{ $tab['value'] }}"
                            class="px-space-md py-1.5 rounded-lg font-label-md text-label-md flex items-center gap-1.5 transition-all
                            {{ $active
                                ? 'bg-primary-container text-on-primary-container shadow-sm'
                                : 'bg-surface-container-high hover:bg-surface-bright text-on-surface' }}">
                        @if($tab['dot'])
                            <span class="w-2 h-2 rounded-full bg-{{ $tab['dot'] }} {{ $tab['dot'] === 'error' ? 'animate-pulse' : '' }}"></span>
                        @endif
                        <span>{{ $tab['label'] }}</span>
                        <span class="px-1.5 py-0.2 rounded-full font-semibold {{ $tab['count_class'] }}">{{ $tab['count'] }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Display format toggle --}}
            <div class="flex items-center gap-2">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Format Tampilan:</span>
                <div class="inline-flex rounded-lg bg-surface-container-highest p-0.5" id="formatToggle">
                    <button type="button" data-format="both"  class="format-btn px-2.5 py-1 rounded-md bg-surface-container text-on-surface font-label-sm text-label-sm font-semibold">Box + Pcs (Dual)</button>
                    <button type="button" data-format="box"   class="format-btn px-2.5 py-1 rounded-md text-on-surface-variant hover:text-on-surface font-label-sm text-label-sm">Hanya Box</button>
                    <button type="button" data-format="pcs"   class="format-btn px-2.5 py-1 rounded-md text-on-surface-variant hover:text-on-surface font-label-sm text-label-sm">Hanya Pcs</button>
                </div>
            </div>
        </div>

        {{-- Search + Category + Sort --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-space-sm items-center">
            <div class="md:col-span-5 relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                <input name="search" value="{{ request('search') }}" oninput="debouncedFilterSubmit()"
                       placeholder="Cari Nama Barang, SKU Kode, atau Barcode..."
                       class="w-full h-10 pl-10 pr-space-lg rounded-xl bg-surface-container-low text-on-surface placeholder:text-on-surface-variant/60 font-body-sm text-body-sm focus:outline-none focus:bg-surface-container transition-all" />
                <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-on-surface-variant hover:text-primary transition-colors" title="Scan Barcode">
                    <span class="material-symbols-outlined text-[18px]">barcode_scanner</span>
                </button>
            </div>

            <div class="md:col-span-4">
                <select name="category" onchange="this.form.submit()"
                        class="w-full h-10 px-space-md rounded-xl bg-surface-container-low text-on-surface font-body-sm text-body-sm focus:outline-none cursor-pointer">
                    <option value="all" @selected($cat === 'all')>Semua Kategori ({{ $categories->count() }})</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->slug }}" @selected($cat === $c->slug)>{{ $c->name }} ({{ $c->products_count }})</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3 flex items-center gap-space-xs">
                <select name="sort" onchange="this.form.submit()"
                        class="w-full h-10 px-space-md rounded-xl bg-surface-container-low text-on-surface font-body-sm text-body-sm focus:outline-none cursor-pointer">
                    <option value="stok_asc"    @selected($sort === 'stok_asc')>Urut: Stok Paling Sedikit</option>
                    <option value="stok_desc"   @selected($sort === 'stok_desc')>Urut: Stok Paling Banyak</option>
                    <option value="nilai_desc"  @selected($sort === 'nilai_desc')>Urut: Nilai Aset Tertinggi</option>
                    <option value="nama_asc"    @selected($sort === 'nama_asc')>Urut: Abjad Nama (A-Z)</option>
                    <option value="mutasi"      @selected($sort === 'mutasi')>Urut: Mutasi Terakhir</option>
                </select>
                <a href="{{ route('stock.index') }}"
                   class="h-10 w-10 flex-shrink-0 rounded-xl bg-surface-container-low hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:text-primary transition-colors" title="Reset Filter">
                    <span class="material-symbols-outlined text-[20px]">refresh</span>
                </a>
            </div>
        </div>
    </form>

    {{-- ============ TABEL ============ --}}
    <div class="rounded-xl bg-surface-container-low overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-high text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">
                        <th class="py-3 px-4 font-semibold">SKU & Barcode</th>
                        <th class="py-3 px-4 font-semibold">Nama Produk & Kategori</th>
                        <th class="py-3 px-4 font-semibold">Rasio Konversi</th>
                        <th class="py-3 px-4 font-semibold">Sisa Stok Realtime</th>
                        <th class="py-3 px-4 font-semibold">Estimasi Nilai Aset</th>
                        <th class="py-3 px-4 font-semibold">Status Kesehatan</th>
                        <th class="py-3 px-4 font-semibold">Mutasi Terakhir</th>
                        <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-highest/40 font-body-sm text-body-sm">
                    @forelse($products as $p)
                        @php
                            $ratio    = max(1, $p->box_pcs_ratio);
                            $stockBox = $p->stock_pcs / $ratio;
                            $hpp      = (float) ($p->last_hpp ?? 0);
                            $asset    = $stockBox * $hpp;
                            $isHabis    = $p->stock_pcs === 0;
                            $isMenipis  = ! $isHabis && $stockBox <= 10;
                            $isAman     = ! $isHabis && ! $isMenipis;

                            // Warna & status berdasarkan kondisi
                            $statusColor  = $isHabis ? 'error' : ($isMenipis ? 'tertiary' : 'primary');
                            $rowBgClass   = $isHabis ? 'bg-error/5' : ($isMenipis ? 'bg-tertiary/5' : '');
                            $skuColorClass = $isHabis ? 'text-error' : ($isMenipis ? 'text-tertiary' : 'text-primary');

                            // Last mutation
                            $lastIn  = $p->last_incoming_at ? \Illuminate\Support\Carbon::parse($p->last_incoming_at) : null;
                            $lastOut = $p->last_outgoing_at ? \Illuminate\Support\Carbon::parse($p->last_outgoing_at) : null;
                            $lastIsIn = $lastIn && (! $lastOut || $lastIn->gt($lastOut));
                            $lastAt = $lastIsIn ? $lastIn : $lastOut;
                            $lastPcs = $lastIsIn ? (int) $p->last_incoming_pcs : (int) $p->last_outgoing_pcs;
                            $lastBox = $ratio > 0 ? round($lastPcs / $ratio, 1) : 0;

                            // Progress bar (skala 60 box = 100%)
                            $progressPct = min(100, ($stockBox / 60) * 100);
                        @endphp
                        <tr class="hover:bg-surface-container transition-colors group {{ $rowBgClass }}">
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-label-md text-label-md {{ $skuColorClass }} font-bold">{{ $p->sku }}</span>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $p->sku }}')"
                                                class="text-on-surface-variant/60 hover:{{ $skuColorClass }} transition-colors" title="Salin SKU">
                                            <span class="material-symbols-outlined text-[14px]">content_copy</span>
                                        </button>
                                    </div>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant font-mono">{{ $p->barcode ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg {{ $isHabis ? 'bg-error-container/20 text-error' : 'bg-surface-container-highest text-on-surface-variant' }} flex-shrink-0 flex items-center justify-center overflow-hidden">
                                        <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-headline-sm text-body-md text-on-surface font-semibold group-hover:{{ $skuColorClass }} transition-colors">{{ $p->name }}</span>
                                        <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $p->category->name }}{{ $p->variant ? ' • ' . $p->variant : '' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="inline-flex items-center rounded-lg bg-surface-container-high px-2 py-1 gap-1.5">
                                    <span class="font-label-sm text-label-sm text-secondary font-bold">1 BOX</span>
                                    <span class="material-symbols-outlined text-[12px] text-on-surface-variant">arrow_forward</span>
                                    <span class="font-label-sm text-label-sm text-primary font-bold">{{ $ratio }} PCS</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 stock-cell" data-box="{{ floor($stockBox) }}" data-pcs="{{ $p->stock_pcs }}">
                                <div class="flex flex-col">
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="stock-box font-headline-md text-headline-md {{ $isHabis ? 'text-error' : ($isMenipis ? 'text-tertiary' : 'text-on-surface') }} font-extrabold leading-none">{{ $num(floor($stockBox)) }}</span>
                                        <span class="font-label-sm text-label-sm {{ $isHabis ? 'text-error' : ($isMenipis ? 'text-tertiary' : 'text-primary') }} font-bold uppercase">Box</span>
                                    </div>
                                    <span class="stock-pcs font-label-sm text-label-sm text-on-surface-variant mt-0.5">
                                        @if($isHabis)
                                            <span class="text-error font-semibold">= 0 Pcs (KOSONG TOTAL)</span>
                                        @else
                                            = {{ $num($p->stock_pcs) }} Pcs Fisik
                                        @endif
                                    </span>
                                    <div class="w-24 h-1 bg-surface-container-highest rounded-full overflow-hidden mt-1.5">
                                        <div class="bg-{{ $statusColor }} h-full rounded-full" style="width: {{ $progressPct }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col">
                                    <span class="font-label-lg text-label-lg {{ $isHabis ? 'text-on-surface-variant' : 'text-on-surface' }} font-semibold">{{ $fmt($asset) }}</span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant">HPP @ {{ $hpp > 0 ? $fmt($hpp) . '/Box' : '—' }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col gap-1 items-start">
                                    @if($isHabis)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-error-container text-on-error-container font-label-sm text-label-sm font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-error animate-ping"></span>
                                            🔴 HABIS
                                        </span>
                                        <span class="font-label-sm text-label-sm text-error pl-1 font-semibold">Perlu Kulakan Darurat</span>
                                    @elseif($isMenipis)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-tertiary/10 text-tertiary font-label-sm text-label-sm font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-tertiary"></span>
                                            🟡 MENIPIS
                                        </span>
                                        <span class="font-label-sm text-label-sm text-tertiary pl-1 font-semibold">Sisa {{ $num($p->stock_pcs) }} Pcs! Kulakan</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-primary/10 text-primary font-label-sm text-label-sm font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                                            🟢 AMAN
                                        </span>
                                        <span class="font-label-sm text-label-sm text-on-surface-variant pl-1">Target Min: 10 Box</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($lastAt)
                                    <div class="flex flex-col">
                                        <span class="font-body-sm text-body-sm {{ $lastIsIn ? 'text-primary' : 'text-on-surface' }} {{ $isHabis && ! $lastIsIn ? 'text-error font-medium' : '' }}">
                                            {{ $lastIsIn ? 'Masuk' : 'Keluar' }} {{ $lastIsIn ? '+' : '-' }}{{ $num($lastBox) }} Box
                                        </span>
                                        <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $lastAt->diffForHumans() }}</span>
                                    </div>
                                @else
                                    <span class="font-label-sm text-label-sm text-on-surface-variant/60">Belum ada mutasi</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" class="p-1.5 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant hover:text-on-surface transition-colors" title="Riwayat Mutasi">
                                        <span class="material-symbols-outlined text-[18px]">history</span>
                                    </button>
                                    @if($isHabis)
                                        <a href="{{ route('incoming-goods.index') }}"
                                           class="px-2.5 py-1.5 rounded-lg bg-error hover:bg-error-container text-on-error hover:text-on-error-container font-label-sm text-label-sm font-bold transition-colors flex items-center gap-1 shadow-sm">
                                            <span class="material-symbols-outlined text-[14px]">bolt</span>
                                            <span>+ Restock Segera</span>
                                        </a>
                                    @elseif($isMenipis)
                                        <a href="{{ route('incoming-goods.index') }}"
                                           class="px-2.5 py-1.5 rounded-lg bg-tertiary hover:bg-tertiary-fixed text-on-tertiary font-label-sm text-label-sm font-bold transition-colors flex items-center gap-1 shadow-sm">
                                            <span class="material-symbols-outlined text-[14px]">shopping_cart</span>
                                            <span>+ Kulakan</span>
                                        </a>
                                    @else
                                        <a href="{{ route('incoming-goods.index') }}"
                                           class="px-2.5 py-1.5 rounded-lg bg-surface-container-high hover:bg-primary hover:text-on-primary text-on-surface font-label-sm text-label-sm font-semibold transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">add</span>
                                            <span>PO</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-space-xl text-center text-on-surface-variant">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="material-symbols-outlined text-[40px] text-surface-container-highest">inventory</span>
                                    <span>Belum ada produk yang cocok dengan filter.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="p-space-md bg-surface-container-high flex flex-col sm:flex-row items-center justify-between gap-space-sm">
            <div class="flex items-center gap-space-sm">
                @if($products->total() > 0)
                    <span class="font-body-sm text-body-sm text-on-surface-variant">Menampilkan <span class="font-semibold text-on-surface">{{ $products->firstItem() }} - {{ $products->lastItem() }}</span> dari <span class="font-semibold text-on-surface">{{ $products->total() }}</span> Master Item</span>
                    <span class="text-outline-variant">•</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Halaman {{ $products->currentPage() }} / {{ $products->lastPage() }}</span>
                @else
                    <span class="font-body-sm text-body-sm text-on-surface-variant">Belum ada data</span>
                @endif
            </div>
            <div>{{ $products->links() }}</div>
        </div>
    </div>

    {{-- ============ EDUKASI CARD ============ --}}
    <div class="mt-space-lg p-space-lg rounded-xl bg-surface-container-low relative overflow-hidden shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-lg">
            <div class="flex items-start gap-space-md">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
                    <span class="material-symbols-outlined text-[28px]">verified</span>
                </div>
                <div class="flex flex-col max-w-2xl">
                    <span class="font-label-sm text-label-sm text-primary uppercase font-bold tracking-wider">Garansi Integritas Data Multi-Satuan</span>
                    <h3 class="font-headline-md text-headline-md text-on-surface mt-0.5">Sistem Stok Box-Pcs Terhubung Tanpa Hitung Manual</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-1.5 leading-relaxed">
                        Di GrosirBox PRO, setiap kali nota penjualan diposting pada modul <strong class="text-on-surface">Barang Keluar</strong>, saldo fisik otomatis terpotong hingga desimal pecahan satuan Pcs. Begitu pula saat supplier mengirim barang di modul <strong class="text-on-surface">Barang Masuk</strong>, stok Box & Pcs ter-update seketika secara serentak tanpa perlu opname manual harian.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-space-sm flex-shrink-0">
                <div class="p-space-sm rounded-xl bg-surface-container-high flex flex-col gap-1 text-center min-w-[130px]">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Selisih Fisik</span>
                    <span class="font-headline-md text-headline-md text-primary font-bold">0.00%</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Zero Deviation</span>
                </div>
                <div class="p-space-sm rounded-xl bg-surface-container-high flex flex-col gap-1 text-center min-w-[130px]">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Audit Trail</span>
                    <span class="font-headline-md text-headline-md text-tertiary font-bold">100%</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Terekam Lengkap</span>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const fmtNum = n => Math.round(n).toLocaleString('id-ID');

    // ============ Debounced filter submit ============
    let filterTimer;
    window.debouncedFilterSubmit = function () {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
    };

    // ============ Display format toggle ============
    function applyFormat(format) {
        document.querySelectorAll('.stock-cell').forEach(cell => {
            const boxEl = cell.querySelector('.stock-box');
            const pcsEl = cell.querySelector('.stock-pcs');
            const box = parseInt(cell.dataset.box, 10) || 0;
            const pcs = parseInt(cell.dataset.pcs, 10) || 0;

            if (format === 'box') {
                boxEl.classList.remove('hidden');
                pcsEl.classList.add('hidden');
                boxEl.textContent = fmtNum(box);
                if (boxEl.nextElementSibling) boxEl.nextElementSibling.style.display = '';
            } else if (format === 'pcs') {
                boxEl.classList.add('hidden');
                pcsEl.classList.remove('hidden');
                pcsEl.textContent = '= ' + fmtNum(pcs) + ' Pcs';
            } else {
                boxEl.classList.remove('hidden');
                pcsEl.classList.remove('hidden');
                boxEl.textContent = fmtNum(box);
                pcsEl.textContent = pcs > 0 ? '= ' + fmtNum(pcs) + ' Pcs Fisik' : '= 0 Pcs (KOSONG TOTAL)';
            }
        });

        // Update active state
        document.querySelectorAll('.format-btn').forEach(btn => {
            if (btn.dataset.format === format) {
                btn.classList.add('bg-surface-container', 'text-on-surface', 'font-semibold');
                btn.classList.remove('text-on-surface-variant', 'hover:text-on-surface');
            } else {
                btn.classList.remove('bg-surface-container', 'text-on-surface', 'font-semibold');
                btn.classList.add('text-on-surface-variant', 'hover:text-on-surface');
            }
        });
    }

    document.querySelectorAll('.format-btn').forEach(btn => {
        btn.addEventListener('click', () => applyFormat(btn.dataset.format));
    });
})();
</script>
@endpush