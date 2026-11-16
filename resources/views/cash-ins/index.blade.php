@extends('layouts.app')

@section('title', 'Kas Masuk')

@php
    $fmt    = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $fmtJt  = function ($n) {
        if ($n >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 2, ',', '.') . ' M';
        if ($n >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 2, ',', '.') . ' Jt';
        if ($n >= 1_000)         return 'Rp ' . number_format($n / 1_000, 0, ',', '.') . ' Rb';
        return 'Rp ' . number_format((int) $n, 0, ',', '.');
    };
    $num  = fn ($n) => number_format((int) $n, 0, ',', '.');
    $catMaster   = \App\Models\CashIn::CATEGORIES;
    $acctMaster  = \App\Models\CashIn::ACCOUNTS;
    $totalSource = max(0.01, array_sum(array_column($catBreakdown->toArray(), 'total')));
@endphp

@section('content')
<div class="flex flex-col w-full pb-16 space-y-space-lg">

    {{-- ============ BREADCRUMB & LIVE SYNC ============ --}}
    <div class="flex flex-wrap items-center justify-between gap-space-sm pt-space-xs">
        <div class="flex items-center gap-space-xs font-body-sm text-on-surface-variant">
            <span class="hover:text-primary cursor-pointer">Sistem Grosir</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="hover:text-primary cursor-pointer">Manajemen Finansial</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-on-surface font-semibold">Kas Masuk (Penerimaan Tunai & Bank)</span>
        </div>
        <div class="inline-flex items-center gap-space-xs px-space-md py-1 rounded-full bg-surface-container-low border border-primary/20 shadow-[0_0_12px_rgba(16,185,129,0.15)]">
            <div class="w-2 h-2 rounded-full bg-primary animate-ping"></div>
            <span class="font-label-sm text-primary uppercase tracking-wider">SINKRONISASI ARUS KAS REALTIME</span>
            <span class="font-label-sm text-on-surface-variant font-mono pl-1">{{ now()->translatedFormat('d M Y • H:i') }} WIB</span>
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
                Rekapitulasi seluruh aliran dana masuk dari penjualan tunai langsung, pelunasan piutang toko mitra, serta penerimaan kas lainnya secara otomatis tanpa selisih.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-space-sm">
            <button type="button" class="px-space-md py-2.5 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-body-sm font-medium flex items-center gap-space-xs shadow-sm">
                <span class="material-symbols-outlined text-[18px] text-tertiary">download</span>
                <span>Cetak Buku Kas Masuk</span>
            </button>
            <button type="button" class="px-space-md py-2.5 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-body-sm font-medium flex items-center gap-space-xs shadow-sm">
                <span class="material-symbols-outlined text-[18px] text-on-surface-variant">calendar_today</span>
                <span>Hari Ini: {{ now()->translatedFormat('d M Y') }}</span>
            </button>
            <button type="button" onclick="document.getElementById('quick-income-form').scrollIntoView({behavior:'smooth'})"
                    class="px-space-lg py-2.5 rounded-xl bg-primary-container hover:bg-primary-fixed-dim text-on-primary-container font-headline-sm font-semibold flex items-center gap-space-xs shadow-[0_4px_20px_rgba(16,185,129,0.35)]">
                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">add_circle</span>
                <span>+ Catat Penerimaan Non-Penjualan</span>
            </button>
        </div>
    </div>

    {{-- ============ RECONCILIATION BANNER ============ --}}
    <div class="relative overflow-hidden rounded-xl bg-surface-container-low p-space-md shadow-md">
        <div class="absolute inset-0 bg-linear-to-r from-primary/10 via-tertiary/5 to-transparent pointer-events-none"></div>
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-space-md">
            <div class="flex items-center gap-space-md">
                <div class="w-12 h-12 rounded-xl {{ $reconOK ? 'bg-primary-container/20 text-primary' : 'bg-error/20 text-error' }} flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' 1;">{{ $reconOK ? 'verified_user' : 'warning' }}</span>
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center gap-space-xs">
                        <span class="font-headline-sm text-body-lg font-bold text-on-surface">
                            Status Rekonsiliasi Kas: {{ $reconOK ? '100% Cocok' : 'Ada Selisih' }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full {{ $reconOK ? 'bg-primary/20 text-primary' : 'bg-error/20 text-error' }} font-label-sm uppercase font-bold tracking-wider">
                            {{ $reconOK ? 'Zero Unmatched' : 'Perlu Cek' }}
                        </span>
                    </div>
                    <p class="font-body-sm text-on-surface-variant mt-0.5">
                        Total <strong class="text-on-surface font-semibold">{{ $fmtJt($cashInFromSales) }}</strong> dari Barang Keluar dan <strong class="text-on-surface font-semibold">{{ $fmtJt($cashInFromReceivable) }}</strong> dari Buku Tagihan telah terverifikasi masuk ke rekening tujuan.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-space-sm self-end md:self-center shrink-0">
                <span class="font-label-sm text-on-surface-variant font-mono">Σ Sumber: {{ $fmtJt($reconTotal) }} | Σ Kas In: {{ $fmtJt($reconCashIn) }}</span>
            </div>
        </div>
    </div>

    {{-- ============ KPI ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-md">

        {{-- Total bulan ini --}}
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

        {{-- Hari ini --}}
        <div class="relative overflow-hidden rounded-xl bg-surface-container-low p-space-md shadow-sm">
            <div class="flex items-center justify-between text-on-surface-variant">
                <span class="font-label-sm uppercase tracking-wider">PENERIMAAN HARI INI</span>
                <div class="w-8 h-8 rounded-lg bg-primary-container/20 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">today</span>
                </div>
            </div>
            <div class="mt-space-sm">
                <span class="font-headline-md font-extrabold text-primary tracking-tight">{{ $fmt($todayTotal) }}</span>
                <div class="mt-1 font-body-sm text-on-surface-variant truncate">
                    Dari {{ $todayCount }} transaksi hari ini
                </div>
            </div>
        </div>

        {{-- Akun --}}
        <div class="relative overflow-hidden rounded-xl bg-surface-container-low p-space-md shadow-sm">
            <div class="flex items-center justify-between text-on-surface-variant">
                <span class="font-label-sm uppercase tracking-wider">DISTRIBUSI SALDO KAS AKTIF</span>
                <div class="w-8 h-8 rounded-lg bg-secondary-container/30 flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined text-[18px]">account_balance</span>
                </div>
            </div>
            <div class="mt-space-sm flex flex-col space-y-1">
                @foreach($acctMaster as $key => $meta)
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-on-surface-variant font-body-sm">{{ $meta['label'] }}</span>
                        <span class="font-mono text-on-surface font-semibold text-xs">{{ $fmt($accountBalances[$key] ?? 0) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-2.5 pt-2 border-t border-surface-container-highest flex items-center justify-between">
                <span class="font-label-sm text-secondary uppercase font-bold">{{ count($accountBalances) }} Akun Terhubung</span>
                <span class="font-mono text-xs text-on-surface font-bold">Total: {{ $fmt(array_sum($accountBalances)) }}</span>
            </div>
        </div>

        {{-- Sumber dominan --}}
        <div class="relative overflow-hidden rounded-xl bg-surface-container-low p-space-md shadow-sm">
            <div class="flex items-center justify-between text-on-surface-variant">
                <span class="font-label-sm uppercase tracking-wider">SUMBER DANA DOMINAN</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined text-[18px]">pie_chart</span>
                </div>
            </div>
            <div class="mt-space-sm flex flex-col space-y-1.5">
                @foreach($catMaster as $key => $meta)
                    @php
                        $total = $catBreakdown[$key]['total'] ?? 0;
                        if ($total <= 0) continue;
                        $pct = round(($total / $totalSource) * 100);
                    @endphp
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-on-surface font-medium flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-{{ $meta['color'] }}"></span>
                            {{ $meta['label'] }}
                        </span>
                        <span class="font-mono font-bold text-{{ $meta['color'] }}">{{ $pct }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============ FORM MANUAL + ACCOUNT CARDS ============ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg">

        {{-- Left: Form input manual --}}
        <div class="lg:col-span-5 flex flex-col rounded-xl bg-surface-container-low p-space-lg shadow-sm" id="quick-income-form">
            <div class="flex items-center justify-between pb-space-sm border-b border-surface-container-highest">
                <div class="flex items-center gap-space-sm">
                    <div class="w-8 h-8 rounded-lg bg-primary-container/20 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-[18px]">add_box</span>
                    </div>
                    <div>
                        <h3 class="font-headline-sm font-bold text-on-surface">Input Kas Masuk Non-Penjualan</h3>
                        <span class="font-label-sm text-on-surface-variant uppercase">Penerimaan Lain & Retur Supplier</span>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded bg-surface-container-high text-on-surface-variant font-mono text-xs">Manual Entry</span>
            </div>

            @if ($errors->any())
                <div class="mt-space-sm p-space-sm rounded-xl bg-error/10 border border-error/30 text-error font-body-sm">
                    <strong class="flex items-center gap-1 mb-1"><span class="material-symbols-outlined text-[16px]">error</span> Periksa kembali:</strong>
                    <ul class="list-disc list-inside ml-1">
                        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('cash-ins.store') }}" class="mt-space-md flex flex-col space-y-space-md">
                @csrf
                {{-- Kategori --}}
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-sm text-on-surface uppercase tracking-wider">Kategori Penerimaan Kas</label>
                    <div class="relative">
                        <select name="category" required class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm appearance-none focus:outline-none focus:ring-1 focus:ring-primary pr-10">
                            <option value="">— Pilih Kategori —</option>
                            <option value="refund_supplier"    @selected(old('category')==='refund_supplier')>Refund Supplier (Retur Rusak)</option>
                            <option value="modal_pemilik"      @selected(old('category')==='modal_pemilik')>Setoran Modal Pemilik</option>
                            <option value="penjualan_palet"    @selected(old('category')==='penjualan_palet')>Penjualan Palet / Krat Bekas</option>
                            <option value="pendapatan_non_ops" @selected(old('category')==='pendapatan_non_ops')>Pendapatan Non-Operasional & Bunga</option>
                            <option value="deposit_kembali"    @selected(old('category')==='deposit_kembali')>Pengembalian Deposit Toko</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[18px]">expand_more</span>
                    </div>
                </div>

                {{-- Akun tujuan --}}
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-sm text-on-surface uppercase tracking-wider">Masuk ke Rekening / Kas Tujuan</label>
                    <div class="grid grid-cols-3 gap-space-xs">
                        @foreach($acctMaster as $key => $meta)
                            <label class="flex flex-col items-center justify-center p-2 rounded-xl bg-surface-container hover:bg-surface-container-high cursor-pointer transition-all border {{ old('account', 'bca')===$key ? 'border-primary bg-primary-container/10' : 'border-transparent' }}">
                                <input type="radio" name="account" value="{{ $key }}" class="hidden" @checked(old('account', 'bca')===$key) />
                                <span class="font-headline-sm text-xs font-bold text-on-surface">{{ $meta['short'] }}</span>
                                <span class="font-label-sm text-on-surface-variant text-[10px]">{{ $meta['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Nominal --}}
                <div class="flex flex-col gap-1.5">
                    <div class="flex justify-between items-center">
                        <label class="font-label-sm text-on-surface uppercase tracking-wider">Jumlah Nominal (Rp)</label>
                        <span class="font-label-sm text-primary font-mono font-semibold" id="nominalPreview">IDR 0</span>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant font-mono font-bold text-sm">Rp</span>
                        <input name="amount" id="nominalInput" type="number" min="1" required
                               value="{{ old('amount') }}" placeholder="0"
                               class="w-full h-11 pl-11 pr-space-md rounded-xl bg-surface-container text-on-surface font-headline-md font-bold focus:outline-none focus:ring-1 focus:ring-primary font-mono" />
                    </div>
                    <div class="flex items-center gap-space-xs pt-1">
                        @foreach([500000, 1000000, 5000000, 10000000] as $v)
                            <button type="button" onclick="setNominal({{ $v }})"
                                    class="px-2.5 py-1 rounded-lg bg-surface-container hover:bg-surface-bright text-xs font-mono text-on-surface-variant hover:text-on-surface transition-colors">
                                +{{ $v >= 1_000_000 ? 'Rp ' . ($v/1_000_000) . ' Jt' : 'Rp ' . ($v/1000) . 'rb' }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Keterangan --}}
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-sm text-on-surface uppercase tracking-wider">Keterangan & Catatan Mutasi <span class="text-error">*</span></label>
                    <input name="notes" type="text" required value="{{ old('notes') }}"
                           placeholder="Contoh: Refund minyak goreng bocor 3 dus dari PT Indomarco"
                           class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary placeholder:text-on-surface-variant/40" />
                </div>

                {{-- Tanggal (opsional, default now) --}}
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-sm text-on-surface uppercase tracking-wider">Tanggal Penerimaan (kosong = sekarang)</label>
                    <input name="received_at" type="datetime-local" value="{{ old('received_at') }}"
                           class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                </div>

                <button type="submit" class="w-full h-11 rounded-xl bg-primary-container hover:bg-primary-fixed-dim text-on-primary-container font-headline-sm font-bold flex items-center justify-center gap-space-xs shadow-[0_4px_16px_rgba(16,185,129,0.3)] mt-2">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    <span>Simpan & Posting ke Buku Kas</span>
                </button>
            </form>
        </div>

        {{-- Right: Account cards --}}
        <div class="lg:col-span-7 flex flex-col space-y-space-md">
            <div class="flex items-center justify-between pb-1">
                <div>
                    <h3 class="font-headline-sm font-bold text-on-surface">Arus Kas Masuk Menurut Akun Penampung</h3>
                    <p class="font-body-sm text-on-surface-variant">Monitoring saldo berjalan dan akumulasi dana masuk per rekening resmi.</p>
                </div>
            </div>

            @foreach($acctMaster as $key => $meta)
                @php
                    $balance   = $accountBalances[$key] ?? 0;
                    $monthData = $accountBreakdown[$key] ?? ['total' => 0, 'count' => 0];
                @endphp
                <div class="rounded-xl bg-surface-container-low p-space-md flex flex-col justify-between hover:bg-surface-container transition-all shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
                        <div class="flex items-center gap-space-md">
                            <div class="w-12 h-12 rounded-xl bg-{{ $meta['color'] }}-container/20 flex items-center justify-center text-{{ $meta['color'] }} font-bold font-mono text-base uppercase">
                                {{ $meta['short'] }}
                            </div>
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span class="font-headline-sm text-headline-sm font-bold text-on-surface">{{ $meta['label'] }}</span>
                                    <span class="px-2 py-0.5 rounded-full bg-{{ $meta['color'] }}/20 text-{{ $meta['color'] }} font-label-sm uppercase font-bold">{{ $key === 'kas_tunai' ? 'Tunai Toko' : 'Transfer Hub' }}</span>
                                </div>
                                <span class="font-mono text-body-sm text-on-surface-variant">Akun Internal GrosirBox PRO</span>
                            </div>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="font-label-sm text-on-surface-variant uppercase">Saldo Realtime</span>
                            <div class="font-headline-md font-extrabold text-on-surface font-mono">{{ $fmt($balance) }}</div>
                        </div>
                    </div>
                    <div class="mt-space-md pt-space-sm border-t border-surface-container-highest flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-space-sm">
                            <span class="flex items-center gap-1 font-body-sm text-primary font-bold">
                                <span class="material-symbols-outlined text-[16px]">arrow_downward</span>
                                +{{ $fmt($monthData['total']) }} Bulan Ini
                            </span>
                            <span class="text-on-surface-variant font-body-sm">• {{ $monthData['count'] }} Transaksi</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ============ TABEL JURNAL ============ --}}
    <div class="flex flex-col rounded-xl bg-surface-container-low shadow-sm overflow-hidden">
        <div class="p-space-lg flex flex-col space-y-space-md border-b border-surface-container-highest">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
                <div>
                    <h2 class="font-headline-md font-extrabold text-on-surface flex items-center gap-space-xs">
                        Jurnal Buku Kas Masuk Realtime
                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                    </h2>
                    <p class="font-body-sm text-on-surface-variant mt-0.5">Daftar mutasi penerimaan kas terintegrasi langsung dengan Barang Keluar dan Buku Tagihan.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('cash-ins.index') }}" id="filterForm" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-space-md">
                <div class="flex items-center gap-1 p-1 rounded-xl bg-surface-container overflow-x-auto">
                    @php
                        $tabs = [
                            ['value' => 'all',               'label' => "Semua ({$tabCounts['all']})",                 'count' => null],
                            ['value' => 'penjualan_tunai',   'label' => "Penjualan Tunai ({$tabCounts['penjualan_tunai']})", 'count' => null],
                            ['value' => 'pelunasan_piutang', 'label' => "Pelunasan Piutang ({$tabCounts['pelunasan_piutang']})", 'count' => null],
                            ['value' => 'manual',            'label' => "Penerimaan Lain ({$tabCounts['manual']})",   'count' => null],
                        ];
                    @endphp
                    @foreach($tabs as $t)
                        @php $active = $cat === $t['value']; @endphp
                        <button type="submit" name="category" value="{{ $t['value'] }}"
                                class="px-space-md py-1.5 rounded-lg font-headline-sm text-body-sm whitespace-nowrap transition-all
                                {{ $active ? 'bg-primary-container text-on-primary-container font-bold shadow-sm' : 'hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface font-semibold' }}">
                            {{ $t['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center gap-space-sm flex-1 max-w-xl lg:justify-end">
                    <input type="hidden" name="category" value="{{ $cat }}">
                    <div class="relative flex-1 min-w-[240px]">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                        <input name="search" value="{{ request('search') }}" oninput="debouncedFilter()"
                               placeholder="Cari No. KM, Faktur, Keterangan..."
                               class="w-full h-9 pl-9 pr-space-md rounded-lg bg-surface-container text-on-surface font-body-sm placeholder:text-on-surface-variant/50 focus:outline-none focus:ring-1 focus:ring-primary" />
                    </div>
                    <select name="account" onchange="this.form.submit()"
                            class="h-9 pl-3 pr-8 rounded-lg bg-surface-container text-on-surface font-body-sm text-xs focus:outline-none">
                        <option value="all" @selected($acc === 'all')>Semua Akun</option>
                        @foreach($acctMaster as $k => $m)
                            <option value="{{ $k }}" @selected($acc === $k)>{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                    <select name="sort" onchange="this.form.submit()"
                            class="h-9 pl-3 pr-8 rounded-lg bg-surface-container text-on-surface font-body-sm text-xs focus:outline-none">
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
                            $catMeta = $catMaster[$ci->category] ?? ['label' => ucfirst($ci->category), 'color' => 'primary'];
                            $accMeta = $acctMaster[$ci->account] ?? ['label' => ucfirst($ci->account), 'short' => '?', 'color' => 'primary'];
                            $isAuto  = $ci->is_auto;
                        @endphp
                        <tr class="hover:bg-surface-container transition-colors group">
                            <td class="py-space-md px-space-md">
                                <div class="flex flex-col">
                                    <span class="font-mono font-bold text-on-surface group-hover:text-primary transition-colors">{{ $ci->code }}</span>
                                    <span class="font-label-sm text-on-surface-variant mt-0.5">{{ $ci->received_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                    <span class="text-[10px] text-{{ $isAuto ? $catMeta['color'] : 'tertiary' }}/90 font-mono flex items-center gap-0.5 mt-0.5">
                                        <span class="material-symbols-outlined text-[12px]">{{ $isAuto ? 'link' : 'edit_note' }}</span>
                                        {{ $isAuto ? 'Auto-Journal' : 'Manual Input' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="flex flex-col">
                                    <span class="font-headline-sm text-body-md font-bold text-on-surface">{{ $ci->source?->customer?->name ?? $ci->source?->product?->name ?? 'Penerimaan Manual' }}</span>
                                    @if($ci->source_type)
                                        <span class="font-label-sm text-on-surface-variant uppercase font-mono">{{ class_basename($ci->source_type) }}</span>
                                    @endif
                                    @if($ci->reference_no)
                                        <span class="font-mono text-xs text-tertiary mt-0.5">Ref: {{ $ci->reference_no }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="flex flex-col items-start gap-1">
                                    <span class="px-2 py-0.5 rounded-full bg-{{ $catMeta['color'] }}/10 text-{{ $catMeta['color'] }} font-label-sm uppercase font-bold">
                                        {{ $catMeta['label'] }}
                                    </span>
                                    <span class="text-xs text-on-surface-variant">{{ Str::limit($ci->notes, 60) }}</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-{{ $accMeta['color'] }}"></span>
                                    <span class="font-mono text-xs text-on-surface font-semibold">{{ $accMeta['label'] }}</span>
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
                                        Terverifikasi
                                    </span>
                                @endif
                            </td>
                            <td class="py-space-md px-space-md text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" class="w-8 h-8 rounded-lg hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors" title="Lihat Detail">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </button>
                                    <button type="button" class="w-8 h-8 rounded-lg hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors" title="Cetak Kwitansi">
                                        <span class="material-symbols-outlined text-[18px]">print</span>
                                    </button>
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
            <div class="flex items-center gap-space-sm font-body-sm text-on-surface-variant text-xs">
                @if($cashIns->total() > 0)
                    <span>Menampilkan <strong>{{ $cashIns->firstItem() }} - {{ $cashIns->lastItem() }}</strong> dari <strong>{{ $cashIns->total() }}</strong> mutasi</span>
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
                    <h4 class="font-headline-sm text-body-lg font-bold text-on-surface">Prinsip Integritas Arus Kas Masuk GrosirBox PRO</h4>
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
                    <span class="material-symbols-outlined text-[14px]">lock_reset</span> Terverifikasi 100%
                </span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const input   = document.getElementById('nominalInput');
    const preview = document.getElementById('nominalPreview');

    window.setNominal = function (val) {
        if (!input) return;
        input.value = val;
        updatePreview();
    };

    function updatePreview() {
        if (!preview || !input) return;
        const v = parseFloat(input.value) || 0;
        preview.textContent = 'IDR ' + v.toLocaleString('id-ID');
    }

    if (input) input.addEventListener('input', updatePreview);

    let filterTimer;
    window.debouncedFilter = function () {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
    };
})();
</script>
@endpush