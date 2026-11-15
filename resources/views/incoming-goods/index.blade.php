@extends('layouts.app')

@section('title', 'Barang Masuk')

@php
    $fmt = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $fmtJt = function ($n) {
        if ($n >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 2, ',', '.') . ' M';
        if ($n >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 2, ',', '.') . ' Jt';
        if ($n >= 1_000)         return 'Rp ' . number_format($n / 1_000, 0, ',', '.') . ' Rb';
        return 'Rp ' . number_format((int) $n, 0, ',', '.');
    };
    $num = fn ($n) => number_format((int) $n, 0, ',', '.');
@endphp

@section('content')
<div class="flex flex-col w-full">
<div class="relative w-full overflow-hidden pb-space-xl">
    <div class="absolute -top-32 -right-24 w-96 h-96 rounded-full bg-primary/10 blur-[120px] pointer-events-none"></div>
    <div class="absolute top-96 -left-32 w-80 h-80 rounded-full bg-tertiary/10 blur-[100px] pointer-events-none"></div>

    {{-- ==================== HEADER ==================== --}}
    <div class="pt-space-md pb-space-lg flex flex-col gap-space-sm">
        <div class="flex items-center gap-space-xs text-on-surface-variant font-label-md text-label-md">
            <span class="hover:text-on-surface transition-colors cursor-pointer">Sistem Grosir</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="hover:text-on-surface transition-colors cursor-pointer">Operasional Stok</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-primary font-bold">Barang Masuk & Input Modal Box</span>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-md">
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-center gap-space-sm flex-wrap">
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight font-extrabold">
                        Barang Masuk & Input Modal Box
                    </h1>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-primary/15 text-primary text-label-sm font-label-sm uppercase tracking-wider shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary animate-ping"></span>
                        <span>Kunci Acuan HPP / Modal Baru</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-md bg-surface-container-high text-on-surface-variant text-label-sm font-label-sm">
                        SYNC ENGINE ACTIVE
                    </span>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-3xl">
                    Pencatatan faktur kulakan supplier. Harga modal per Box yang dicatat di sini otomatis menjadi basis perhitungan keuntungan di menu
                    <span class="text-tertiary font-semibold">Barang Keluar</span> & menambah saldo fisik gudang.
                </p>
            </div>

            <div class="flex items-center gap-space-xs shrink-0 flex-wrap">
                <button type="button" onclick="document.getElementById('fakturInputSection').scrollIntoView({behavior:'smooth'}); document.getElementById('noFakturInput').focus();"
                        class="h-10 px-space-md rounded-xl bg-primary text-on-primary font-headline-sm text-body-md font-bold flex items-center gap-space-xs hover:bg-primary-fixed shadow-[0_0_24px_rgba(78,222,163,0.35)] transition-all active:scale-95">
                    <span class="material-symbols-outlined text-[20px]">add_box</span>
                    <span>+ Input Faktur Masuk</span>
                </button>
                <button class="h-10 px-space-md rounded-xl bg-surface-container-high text-on-surface hover:bg-surface-bright font-body-md text-body-md flex items-center gap-space-xs transition-colors" type="button">
                    <span class="material-symbols-outlined text-[18px] text-tertiary">document_scanner</span>
                    <span>Scan Faktur / SJ</span>
                </button>
                <button class="h-10 px-space-md rounded-xl bg-surface-container-high text-on-surface hover:bg-surface-bright font-body-md text-body-md flex items-center gap-space-xs transition-colors" type="button">
                    <span class="material-symbols-outlined text-[18px]">file_download</span>
                    <span>Export Rekap PO</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ==================== KPI CARDS ==================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-md mb-space-lg">
        {{-- KPI 1 --}}
        <div class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-md flex flex-col justify-between hover:bg-surface-container transition-all group">
            <div class="flex items-start justify-between">
                <div class="flex flex-col">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Total Belanja Bulan Ini</span>
                    <span class="font-display-lg text-display-lg text-on-surface font-extrabold mt-1 tracking-tight">{{ $fmtJt($kpi['month_total']) }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary-container/20 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[22px]">shopping_cart_checkout</span>
                </div>
            </div>
            <div class="mt-space-md pt-space-xs flex items-center justify-between">
                <div class="flex items-center gap-1 font-label-md text-label-md text-primary">
                    <span class="material-symbols-outlined text-[14px]">inventory</span>
                    <span>{{ $num($kpi['month_box']) }} Box</span>
                    <span class="text-on-surface-variant font-normal">/ {{ $num($kpi['month_pcs']) }} Pcs</span>
                </div>
                <span class="px-2 py-0.5 rounded text-label-sm font-label-sm bg-surface-container-high text-on-surface-variant">
                    {{ $kpi['total_count'] }} Faktur
                </span>
            </div>
        </div>

        {{-- KPI 2 --}}
        <div class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-md flex flex-col justify-between hover:bg-surface-container transition-all group">
            <div class="flex items-start justify-between">
                <div class="flex flex-col">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Transaksi Lunas</span>
                    <span class="font-display-lg text-display-lg text-primary font-extrabold mt-1 tracking-tight">{{ $fmtJt($kpi['lunas_total']) }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-surface-container-high flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[22px]">payments</span>
                </div>
            </div>
            <div class="mt-space-md pt-space-xs flex items-center justify-between">
                <span class="font-label-md text-label-md text-on-surface-variant">Kas Keluar Otomatis Terhubung</span>
                <span class="font-label-sm text-label-sm text-primary font-bold">{{ $kpi['lunas_count'] }} Faktur</span>
            </div>
        </div>

        {{-- KPI 3 --}}
        <div class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-md flex flex-col justify-between hover:bg-surface-container transition-all group relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-error/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-start justify-between">
                <div class="flex flex-col">
                    <span class="font-label-sm text-label-sm text-error uppercase tracking-wider flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-error animate-pulse"></span>
                        Tempo Belum Lunas
                    </span>
                    <span class="font-display-lg text-display-lg text-on-surface font-extrabold mt-1 tracking-tight">{{ $fmtJt($kpi['tempo_total']) }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-error/15 flex items-center justify-center text-error group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[22px]">pending_actions</span>
                </div>
            </div>
            <div class="mt-space-md pt-space-xs flex items-center justify-between">
                <span class="font-label-md text-label-md text-error">{{ $kpi['tempo_count'] }} Faktur Tempo Aktif</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $kpi['tempo_soon'] }} Faktur &lt; 7 Hari</span>
            </div>
        </div>

        {{-- KPI 4 --}}
        <div class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-md flex flex-col justify-between hover:bg-surface-container transition-all group">
            <div class="flex items-start justify-between">
                <div class="flex flex-col">
                    <span class="font-label-sm text-label-sm text-tertiary uppercase tracking-wider">Update Acuan Modal Bulan Ini</span>
                    <span class="font-display-lg text-display-lg text-on-surface font-extrabold mt-1 tracking-tight">{{ $kpi['sku_updated'] }} SKU</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-tertiary/15 flex items-center justify-center text-tertiary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[22px]">price_change</span>
                </div>
            </div>
            <div class="mt-space-md pt-space-xs flex items-center justify-between">
                <div class="flex items-center gap-1 font-label-sm text-label-sm text-on-surface-variant truncate max-w-[60%]">
                    <span>{{ $kpi['sku_names'] ?: '—' }}</span>
                </div>
                <span class="font-label-sm text-label-sm text-tertiary uppercase">HPP Terkunci</span>
            </div>
        </div>
    </div>

    {{-- ==================== FORM INPUT FAKTUR ==================== --}}
    <div id="fakturInputSection" class="p-space-lg rounded-2xl bg-surface-container-low/95 backdrop-blur-2xl shadow-2xl mb-space-lg">

        <div class="flex items-center justify-between pb-space-md">
            <div class="flex items-center gap-space-sm">
                <div class="w-8 h-8 rounded-lg bg-primary-container/20 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[20px]">receipt</span>
                </div>
                <div>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Input Faktur Penerimaan Barang (PO Kulakan)</h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Kalkulator Box-to-Pcs otomatis menghitung harga modal dasar.</p>
                </div>
            </div>
            <button type="button" onclick="resetInputForm()"
                    class="px-space-sm py-1 rounded-lg text-on-surface-variant hover:text-on-surface text-body-sm flex items-center gap-1 hover:bg-surface-container transition-colors">
                <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                <span>Reset Form</span>
            </button>
        </div>

        {{-- Error validation --}}
        @if ($errors->any())
            <div class="mb-space-md p-space-sm rounded-xl bg-error/10 border border-error/30 text-error font-body-sm">
                <strong class="flex items-center gap-1 mb-1"><span class="material-symbols-outlined text-[16px]">error</span> Periksa kembali:</strong>
                <ul class="list-disc list-inside ml-1">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('incoming-goods.store') }}" id="fakturForm">
            @csrf
            <input type="hidden" name="payment_status" id="paymentStatusInput" value="{{ old('payment_status', 'lunas') }}">

            {{-- Baris 1: Supplier / No Faktur / Tanggal / Metode Bayar --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-md pt-space-md">
                <div class="lg:col-span-4 flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase flex items-center justify-between">
                        <span>Pemasok / Supplier</span>
                        <span class="text-primary text-[10px] lowercase font-normal">*wajib dipilih</span>
                    </label>
                    <div class="relative">
                        <select name="supplier_id" required
                                class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-md text-body-md focus:outline-none focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" @selected(old('supplier_id', $suppliers->first()?->id) == $s->id)>
                                    {{ $s->name }}@if($s->type) ({{ $s->type }})@endif
                                </option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[18px]">expand_more</span>
                    </div>
                </div>

                <div class="lg:col-span-3 flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase">No. Faktur / Surat Jalan (SJ)</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">pin</span>
                        <input name="no_faktur" id="noFakturInput" type="text" required
                               value="{{ old('no_faktur', 'DO-' . now()->format('Y/m') . '/' . rand(1000, 9999)) }}"
                               class="w-full h-11 pl-10 pr-space-md rounded-xl bg-surface-container text-on-surface font-label-md text-body-md focus:outline-none focus:ring-1 focus:ring-primary uppercase tracking-wider" />
                    </div>
                </div>

                <div class="lg:col-span-2 flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase">Tgl Masuk Gudang</label>
                    <input name="received_at" type="date" required
                           value="{{ old('received_at', now()->format('Y-m-d')) }}"
                           class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-md text-body-md focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer" />
                </div>

                <div class="lg:col-span-3 flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase flex justify-between">
                        <span>Metode Bayar</span>
                        <span id="tempoBadgeLabel" class="text-label-sm font-label-sm text-primary">Lunas Kas Toko</span>
                    </label>
                    <div class="h-11 p-1 bg-surface-container rounded-xl flex items-center gap-1">
                        <button type="button" onclick="setPaymentStatus('lunas')" id="btnStatusLunas"
                                class="flex-1 h-full rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all bg-primary-container text-on-primary-container font-semibold shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">check_circle</span>
                            <span>Lunas</span>
                        </button>
                        <button type="button" onclick="setPaymentStatus('tempo')" id="btnStatusTempo"
                                class="flex-1 h-full rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all text-on-surface-variant hover:text-on-surface">
                            <span class="material-symbols-outlined text-[16px]">schedule</span>
                            <span>Tempo / Hutang</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tempo block --}}
            <div id="tempoDetailsWrapper" class="hidden mt-space-sm p-space-md rounded-xl bg-surface-container flex flex-col md:flex-row items-center justify-between gap-space-md">
                <div class="flex items-center gap-space-sm text-error">
                    <span class="material-symbols-outlined text-[20px]">warning</span>
                    <span class="font-body-sm text-body-sm">Pembayaran Kredit Supplier: Otomatis masuk ke <strong>Buku Tagihan (Hutang Dagang)</strong>.</span>
                </div>
                <div class="flex items-center gap-space-sm w-full md:w-auto">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase whitespace-nowrap">Jatuh Tempo:</label>
                    <input name="due_date" id="inputJatuhTempo" type="date"
                           value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}"
                           class="h-9 px-space-md rounded-lg bg-surface-container-high text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-error" />
                </div>
            </div>

            {{-- Line Builder --}}
            <div class="mt-space-md p-space-md rounded-xl bg-surface-container-lowest/80 backdrop-blur-md">
                <div class="flex items-center justify-between mb-space-sm">
                    <span class="font-label-md text-label-md text-on-surface uppercase tracking-wider flex items-center gap-1">
                        <span class="material-symbols-outlined text-primary text-[18px]">inventory_2</span>
                        Katalog Barang & Konversi Fisik
                    </span>
                    <span class="text-label-sm font-label-sm text-on-surface-variant">Konversi otomatis berdasar Master Barang</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-space-md items-end">
                    <div class="md:col-span-4 flex flex-col gap-1.5">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Pilih Master Barang</label>
                        <select name="product_id" id="productSelect" onchange="onProductChange()" required
                                class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-headline-sm text-body-md focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer">
                            @foreach($products as $p)
                                <option value="{{ $p->id }}"
                                        data-ratio="{{ $p->box_pcs_ratio }}"
                                        data-name="{{ $p->name }}"
                                        data-last-price="{{ (int) ($p->last_price ?? 0) }}"
                                        @selected(old('product_id', $products->first()?->id) == $p->id)>
                                    {{ $p->name }} (1 Box = {{ $p->box_pcs_ratio }} Pcs)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 flex flex-col gap-1.5">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Jumlah Box Masuk</label>
                        <div class="relative">
                            <input name="box_qty" id="inputBoxQty" type="number" min="1" required
                                   value="{{ old('box_qty', 25) }}" oninput="calculateRealtime()"
                                   class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-headline-sm text-headline-sm focus:outline-none focus:ring-1 focus:ring-primary text-center font-bold" />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 font-label-sm text-label-sm text-on-surface-variant uppercase">BOX</span>
                        </div>
                    </div>

                    <div class="md:col-span-3 flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Harga Modal / Box</label>
                            <span class="text-[10px] text-tertiary" id="hppLaluLabel">Modal Lalu: —</span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 font-label-md text-label-md text-on-surface-variant">Rp</span>
                            <input name="price_per_box" id="inputHargaBox" type="number" step="500" required
                                   value="{{ old('price_per_box', '') }}" oninput="calculateRealtime()"
                                   class="w-full h-11 pl-10 pr-space-md rounded-xl bg-surface-container text-primary font-headline-sm text-headline-sm focus:outline-none focus:ring-1 focus:ring-primary font-bold" />
                        </div>
                    </div>

                    <div class="md:col-span-3 flex items-center justify-between p-space-sm bg-surface-container rounded-xl">
                        <div class="flex flex-col">
                            <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Hitungan Pcs Didapat</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="material-symbols-outlined text-primary text-[18px]">all_inbox</span>
                                <span id="outputTotalPcs" class="font-headline-sm text-headline-sm text-on-surface font-extrabold">0 Pcs</span>
                            </div>
                        </div>
                        <div class="h-8 w-px bg-surface-container-highest"></div>
                        <div class="flex flex-col text-right">
                            <span class="font-label-sm text-label-sm text-tertiary uppercase">Modal Dasar / Pcs</span>
                            <span id="outputModalPcs" class="font-headline-sm text-headline-sm text-primary font-extrabold mt-0.5">Rp 0</span>
                        </div>
                    </div>
                </div>

                {{-- Summary + Submit --}}
                <div class="mt-space-md pt-space-sm flex flex-col sm:flex-row items-center justify-between gap-space-md">
                    <div class="flex items-center gap-space-sm">
                        <div class="px-3 py-1.5 rounded-lg bg-surface-container flex items-center gap-2">
                            <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Subtotal Belanja:</span>
                            <span id="outputTotalBelanja" class="font-headline-md text-headline-md text-on-surface font-black">Rp 0</span>
                        </div>
                        <div class="hidden xl:flex items-center gap-1 text-[11px] text-on-surface-variant">
                            <span class="material-symbols-outlined text-[14px] text-primary">lock_clock</span>
                            <span id="lockInfo">Harga per Pcs akan mengunci acuan margin laba Barang Keluar.</span>
                        </div>
                    </div>
                    <button type="submit"
                            class="w-full sm:w-auto h-11 px-space-lg rounded-xl bg-primary-container text-on-primary-container font-headline-sm text-body-md font-bold flex items-center justify-center gap-space-xs hover:bg-primary transition-all shadow-[0_0_20px_rgba(16,185,129,0.35)] active:scale-95">
                        <span class="material-symbols-outlined text-[20px]">save</span>
                        <span>Simpan & Tambah Stok Gudang</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ==================== TABEL RIWAYAT ==================== --}}
    <div class="flex flex-col rounded-2xl bg-surface-container-low/90 backdrop-blur-xl shadow-xl overflow-hidden">

        <form method="GET" action="{{ route('incoming-goods.index') }}" id="filterForm" class="p-space-md flex flex-col lg:flex-row lg:items-center justify-between gap-space-md">
            <div class="flex flex-1 items-center gap-space-sm flex-wrap">
                <div class="relative flex-1 min-w-[240px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input name="search" value="{{ request('search') }}" oninput="debouncedFilterSubmit()"
                           placeholder="Cari No. Faktur / SKU / Supplier..." type="text"
                           class="w-full h-10 pl-9 pr-space-md rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/50 font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary transition-all" />
                </div>

                <div class="flex items-center p-1 bg-surface-container rounded-xl">
                    <button type="submit" name="status" value="SEMUA"
                            class="px-3 py-1 rounded-lg text-body-sm transition-all {{ $status === 'SEMUA' ? 'bg-surface-container-high text-on-surface font-semibold' : 'text-on-surface-variant hover:text-on-surface' }}">
                        Semua ({{ $kpi['total_count'] }})
                    </button>
                    <button type="submit" name="status" value="LUNAS"
                            class="px-3 py-1 rounded-lg text-body-sm transition-all {{ $status === 'LUNAS' ? 'bg-surface-container-high text-on-surface font-semibold' : 'text-on-surface-variant hover:text-on-surface' }}">
                        Lunas ({{ $kpi['lunas_all'] }})
                    </button>
                    <button type="submit" name="status" value="TEMPO"
                            class="px-3 py-1 rounded-lg text-body-sm transition-all flex items-center gap-1 {{ $status === 'TEMPO' ? 'bg-surface-container-high text-on-surface font-semibold' : 'text-on-surface-variant hover:text-error' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                        <span>Tempo ({{ $kpi['tempo_all'] }})</span>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-space-xs shrink-0">
                <div class="flex items-center gap-1.5 px-space-sm h-10 rounded-xl bg-surface-container text-on-surface-variant font-body-sm text-body-sm">
                    <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                    <span>{{ now()->translatedFormat('M Y') }}</span>
                </div>
                <a href="{{ route('incoming-goods.index') }}"
                   class="w-10 h-10 rounded-xl bg-surface-container hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:text-primary transition-colors"
                   title="Reset Filter">
                    <span class="material-symbols-outlined text-[18px]">sync</span>
                </a>
            </div>
        </form>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container text-on-surface-variant font-label-md text-label-md uppercase tracking-wider select-none">
                        <th class="py-3 px-space-md font-semibold">No. Faktur & Waktu</th>
                        <th class="py-3 px-space-md font-semibold">Supplier</th>
                        <th class="py-3 px-space-md font-semibold">Nama Barang & SKU</th>
                        <th class="py-3 px-space-md font-semibold text-center">Masuk Box & Pcs</th>
                        <th class="py-3 px-space-md font-semibold text-right">Modal / Box → Pcs</th>
                        <th class="py-3 px-space-md font-semibold text-right">Total Transaksi</th>
                        <th class="py-3 px-space-md font-semibold text-center">Status & Tempo</th>
                        <th class="py-3 px-space-md font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container font-body-sm text-body-sm">
                    @forelse($transactions as $trx)
                        @php
                            $isTempo = $trx->payment_status === 'tempo';
                            $sisaHari = $trx->due_date ? max(0, (int) now()->diffInDays($trx->due_date, false)) : 0;
                            $isUrgent = $isTempo && $trx->due_date && $sisaHari <= 7;
                        @endphp
                        <tr class="hover:bg-surface-container-high/40 transition-colors group {{ $isUrgent ? 'bg-error/5' : '' }}">
                            <td class="py-3.5 px-space-md">
                                <div class="flex flex-col">
                                    <span class="font-label-md text-label-md text-on-surface font-bold group-hover:text-primary transition-colors">{{ $trx->no_faktur }}</span>
                                    <span class="text-[11px] text-on-surface-variant font-label-sm">
                                        {{ $trx->received_at->translatedFormat('d M Y') }} • {{ $trx->created_at->format('H:i') }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-space-md">
                                <div class="flex items-center gap-space-xs">
                                    <div class="w-7 h-7 rounded-lg bg-surface-container flex items-center justify-center text-on-surface-variant text-label-sm uppercase font-bold">
                                        {{ $trx->supplier->initials ?? '—' }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-on-surface">{{ $trx->supplier->name }}</span>
                                        <span class="text-[10px] text-on-surface-variant">{{ $trx->supplier->type }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-space-md">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-headline-sm text-body-md font-bold text-on-surface">{{ $trx->product->name }}</span>
                                        <span class="px-1.5 py-0.2 rounded bg-surface-container-highest text-[9px] font-label-sm text-on-surface-variant">BOX</span>
                                    </div>
                                    <span class="text-label-sm font-label-sm text-tertiary">{{ $trx->product->sku }} • 1 Box = {{ $trx->product->box_pcs_ratio }} Pcs</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-space-md text-center">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-container">
                                    <span class="font-bold text-on-surface font-headline-sm text-body-md">{{ $num($trx->box_qty) }} Box</span>
                                    <span class="text-on-surface-variant text-[11px]">→</span>
                                    <span class="font-bold text-primary font-label-md text-label-md">{{ $num($trx->total_pcs) }} Pcs</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-space-md text-right">
                                <div class="flex flex-col items-end">
                                    <span class="font-bold text-on-surface font-label-md text-label-md">
                                        {{ $fmt($trx->price_per_box) }} <span class="text-on-surface-variant text-[10px] font-normal">/Box</span>
                                    </span>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <span class="px-1.5 py-0.5 rounded bg-primary/10 text-primary text-[10px] font-bold">{{ $fmt($trx->price_per_pcs) }}/Pcs</span>
                                        <span class="material-symbols-outlined text-[13px] text-primary" title="HPP Terkunci">verified</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-space-md text-right">
                                <span class="font-headline-sm text-body-md font-bold text-on-surface">{{ $fmt($trx->subtotal) }}</span>
                            </td>
                            <td class="py-3.5 px-space-md text-center">
                                @if($trx->payment_status === 'lunas')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/15 text-primary text-label-sm font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                        Lunas
                                    </span>
                                @else
                                    <div class="inline-flex flex-col items-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-error/20 text-error text-[10px] font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-error animate-ping"></span>
                                            Tempo {{ $trx->due_date?->translatedFormat('d M') }}
                                        </span>
                                        <span class="text-[9px] text-error/80 mt-0.5">Sisa {{ $sisaHari }} hari</span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-space-md text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if($trx->payment_status === 'tempo')
                                        <form method="POST" action="{{ route('incoming-goods.pay', $trx) }}" class="inline"
                                              onsubmit="return confirm('Tandai faktur {{ $trx->no_faktur }} sebagai LUNAS?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="px-2 py-1 rounded-lg bg-primary-container text-on-primary-container text-[11px] font-bold flex items-center gap-1"
                                                    title="Bayar Sekarang">
                                                <span class="material-symbols-outlined text-[14px]">price_check</span>
                                                <span>Bayar</span>
                                            </button>
                                        </form>
                                    @endif
                                    <button class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:text-on-surface" title="Detail" type="button">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                    </button>
                                    <form method="POST" action="{{ route('incoming-goods.rollback', $trx) }}" class="inline"
                                          onsubmit="return confirm('KONFIRMASI ROLLBACK:\n\nBatalkan faktur {{ $trx->no_faktur }} ({{ $trx->product->name }})?\n\n- Stok akan dikurangi kembali.\n- Catatan kas/hutang akan ditarik balik.\n- Riwayat acuan HPP dikembalikan.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 rounded-lg bg-surface-container hover:bg-error/20 flex items-center justify-center text-on-surface-variant hover:text-error"
                                                title="Rollback">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-space-xl text-center text-on-surface-variant">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="material-symbols-outlined text-[40px] text-surface-container-highest">inbox</span>
                                    <span>Belum ada transaksi yang cocok dengan filter.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="p-space-md bg-surface-container flex flex-col sm:flex-row items-center justify-between gap-space-sm font-body-sm text-body-sm text-on-surface-variant">
            <div class="flex items-center gap-2">
                @if($transactions->total() > 0)
                    <span>Menampilkan <strong>{{ $transactions->firstItem() }} - {{ $transactions->lastItem() }}</strong> dari <strong>{{ $transactions->total() }} Faktur Masuk</strong></span>
                    <span class="w-1 h-1 rounded-full bg-surface-container-highest"></span>
                    <span>Halaman {{ $transactions->currentPage() }} dari {{ $transactions->lastPage() }}</span>
                @else
                    <span>Belum ada data</span>
                @endif
            </div>
            <div>{{ $transactions->links() }}</div>
        </div>
    </div>

    {{-- ==================== BANNER EDUKASI ==================== --}}
    <div class="mt-space-lg p-space-md rounded-2xl bg-surface-container-low border-l-4 border-l-primary shadow-lg flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md">
        <div class="flex items-start gap-space-sm">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0 mt-0.5">
                <span class="material-symbols-outlined text-[24px]">balance</span>
            </div>
            <div class="flex flex-col">
                <span class="font-headline-sm text-body-md text-on-surface font-bold flex items-center gap-2">
                    Integritas HPP & Siklus Transaksi Grosir
                    <span class="px-2 py-0.2 rounded bg-surface-container-high text-[10px] font-label-sm text-tertiary">STANDAR AKUNTANSI PERDAGANGAN</span>
                </span>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                    <strong>Harga Modal Bersifat Historis:</strong> Perubahan harga modal hari ini tidak mengubah keuntungan transaksi penjualan kemarin. Hapus transaksi akan otomatis me-rollback saldo stok gudang dan membatalkan pencatatan kas/hutang terkait.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-space-xs shrink-0">
            <a href="#" class="px-space-md py-2 rounded-xl bg-surface-container text-tertiary hover:bg-surface-container-high font-body-sm text-body-sm flex items-center gap-1 transition-colors">
                <span class="material-symbols-outlined text-[16px]">menu_book</span>
                <span>Panduan SOP Modal Box</span>
            </a>
        </div>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const productSelect   = document.getElementById('productSelect');
    const inputBoxQty     = document.getElementById('inputBoxQty');
    const inputHargaBox   = document.getElementById('inputHargaBox');
    const hppLaluLabel    = document.getElementById('hppLaluLabel');
    const outputTotalPcs  = document.getElementById('outputTotalPcs');
    const outputModalPcs  = document.getElementById('outputModalPcs');
    const outputTotal     = document.getElementById('outputTotalBelanja');
    const lockInfo        = document.getElementById('lockInfo');
    const paymentStatus   = document.getElementById('paymentStatusInput');
    const tempoWrapper    = document.getElementById('tempoDetailsWrapper');
    const tempoBadgeLabel = document.getElementById('tempoBadgeLabel');
    const btnLunas        = document.getElementById('btnStatusLunas');
    const btnTempo        = document.getElementById('btnStatusTempo');
    const noFakturInput   = document.getElementById('noFakturInput');

    const fmtRp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

    function onProductChange() {
        const opt = productSelect.options[productSelect.selectedIndex];
        if (!opt) return;
        const lastPrice = parseInt(opt.dataset.lastPrice || 0, 10);
        hppLaluLabel.textContent = 'Modal Lalu: ' + (lastPrice > 0 ? fmtRp(lastPrice) : '—');

        // Isi harga otomatis HANYA jika input kosong (bukan override input user / old value)
        if (!inputHargaBox.value || inputHargaBox.value === '0') {
            inputHargaBox.value = lastPrice || 0;
        }
        calculateRealtime();
    }

    function calculateRealtime() {
        const opt        = productSelect.options[productSelect.selectedIndex];
        const ratio      = parseInt(opt?.dataset.ratio || 1, 10);
        const boxQty     = parseFloat(inputBoxQty.value) || 0;
        const hargaBox   = parseFloat(inputHargaBox.value) || 0;

        const totalPcs   = Math.round(boxQty * ratio);
        const modalPcs   = totalPcs > 0 ? Math.round((boxQty * hargaBox) / totalPcs) : 0;
        const subtotal   = Math.round(boxQty * hargaBox);

        outputTotalPcs.textContent = totalPcs.toLocaleString('id-ID') + ' Pcs';
        outputModalPcs.textContent = fmtRp(modalPcs);
        outputTotal.textContent    = fmtRp(subtotal);
        lockInfo.textContent       = `Harga ${fmtRp(modalPcs)} / Pcs akan mengunci acuan margin laba Barang Keluar.`;
    }

    function setPaymentStatus(status) {
        paymentStatus.value = status;
        const inputDue = document.getElementById('inputJatuhTempo');

        if (status === 'lunas') {
            btnLunas.className = 'flex-1 h-full rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all bg-primary-container text-on-primary-container font-semibold shadow-sm';
            btnTempo.className = 'flex-1 h-full rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all text-on-surface-variant hover:text-on-surface';
            tempoWrapper.classList.add('hidden');
            tempoBadgeLabel.textContent = 'Lunas Kas Toko';
            tempoBadgeLabel.className = 'text-label-sm font-label-sm text-primary';
            if (inputDue) inputDue.required = false;
        } else {
            btnTempo.className = 'flex-1 h-full rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all bg-error/25 text-error font-semibold shadow-sm';
            btnLunas.className = 'flex-1 h-full rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all text-on-surface-variant hover:text-on-surface';
            tempoWrapper.classList.remove('hidden');
            tempoBadgeLabel.textContent = 'Kredit / Tempo Supplier';
            tempoBadgeLabel.className = 'text-label-sm font-label-sm text-error';
            if (inputDue) inputDue.required = true;
        }
    }

    function resetInputForm() {
        inputBoxQty.value = 10;
        if (noFakturInput) noFakturInput.value = 'DO-' + Math.floor(1000 + Math.random() * 9000);
        setPaymentStatus('lunas');
        onProductChange();
    }

    let filterTimer;
    function debouncedFilterSubmit() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
    }

    // Expose to global (dipanggil dari atribut onclick)
    window.onProductChange     = onProductChange;
    window.calculateRealtime   = calculateRealtime;
    window.setPaymentStatus    = setPaymentStatus;
    window.resetInputForm      = resetInputForm;
    window.debouncedFilterSubmit = debouncedFilterSubmit;

    // Init on load
    document.addEventListener('DOMContentLoaded', () => {
        if (paymentStatus.value === 'tempo') setPaymentStatus('tempo');
        onProductChange();
    });
})();
</script>
@endpush