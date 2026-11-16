@extends('layouts.app')

@section('title', 'Kas Keluar')

@php
    $fmt    = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $fmtJt  = function ($n) {
        if ($n >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 2, ',', '.') . ' M';
        if ($n >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 2, ',', '.') . ' Jt';
        if ($n >= 1_000)         return 'Rp ' . number_format($n / 1_000, 0, ',', '.') . ' Rb';
        return 'Rp ' . number_format((int) $n, 0, ',', '.');
    };
    $catMaster  = \App\Models\CashOut::CATEGORIES;
    $acctMaster = \App\Models\CashIn::ACCOUNTS;
@endphp

@section('content')
<div class="flex flex-col w-full pb-16 space-y-space-lg">

    {{-- ============ BREADCRUMB ============ --}}
    <div class="flex flex-wrap items-center justify-between gap-space-sm pt-space-xs">
        <nav class="flex items-center gap-space-xs text-on-surface-variant font-label-md text-label-md">
            <span>Sistem Grosir</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span>Manajemen Finansial</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-primary font-bold">Kas Keluar (Pengeluaran & Hutang Supplier)</span>
        </nav>
        <div class="flex items-center gap-space-xs px-space-sm py-1 rounded-full bg-surface-container-high text-primary font-label-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
            <span>SINKRONISASI ARUS KAS KELUAR REALTIME • {{ now()->translatedFormat('d M Y • H:i') }} WIB</span>
        </div>
    </div>

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-space-md">
        <div class="flex flex-col gap-1 max-w-3xl">
            <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight flex items-center gap-space-sm">
                <span>💸 Kas Keluar & Pembayaran Hutang Supplier</span>
            </h1>
            <p class="font-body-md text-on-surface-variant">
                Kelola arus kas keluar untuk pelunasan faktur tempo kulakan dari modul Barang Masuk, pembayaran tunai pengadaan barang, serta pencatatan biaya operasional toko dan gudang.
            </p>
        </div>
        <div class="flex items-center flex-wrap gap-space-sm">
            <button class="h-10 px-space-md rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface font-body-sm flex items-center gap-space-xs" type="button">
                <span class="material-symbols-outlined text-primary text-[18px]">calendar_today</span>
                <span>Hari Ini: {{ now()->translatedFormat('d M Y') }}</span>
            </button>
            <button onclick="document.getElementById('inputFormSection').scrollIntoView({behavior:'smooth'})" type="button"
                    class="h-10 px-space-lg rounded-xl bg-primary-container text-on-primary-container font-headline-sm font-bold flex items-center gap-space-xs shadow-[0_4px_16px_rgba(16,185,129,0.35)]">
                <span class="material-symbols-outlined text-[20px]">add_circle</span>
                <span>+ Catat Pengeluaran Baru</span>
            </button>
        </div>
    </div>

    {{-- ============ BANNER ============ --}}
    <div class="w-full p-space-md rounded-xl bg-surface-container-low backdrop-blur-md flex flex-col md:flex-row md:items-center justify-between gap-space-md shadow-md">
        <div class="flex items-start md:items-center gap-space-md">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
                <span class="material-symbols-outlined text-[24px]">verified_user</span>
            </div>
            <div class="flex flex-col">
                <div class="flex items-center gap-space-xs flex-wrap">
                    <span class="font-headline-sm text-on-surface font-semibold">Status Verifikasi Kas Keluar: 100% Terverifikasi (ZERO LEAKAGE)</span>
                </div>
                <p class="font-body-sm text-on-surface-variant mt-0.5">
                    Total <span class="text-error font-semibold">{{ $fmt($todayTotal) }}</span> keluar hari ini:
                    <span class="text-on-surface font-medium">{{ $fmt($todaySupplier) }}</span> untuk Kulakan Supplier dan
                    <span class="text-on-surface font-medium">{{ $fmt($todayOps) }}</span> untuk Biaya Operasional.
                </p>
            </div>
        </div>
    </div>

    {{-- ============ KPI ============ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-space-md">

        {{-- KPI 1 --}}
        <div class="p-space-lg rounded-xl bg-surface-container-low flex flex-col justify-between shadow-md">
            <div class="flex items-start justify-between">
                <span class="font-label-md text-on-surface-variant uppercase tracking-wider">Total Kas Keluar Bulan Ini</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-error">
                    <span class="material-symbols-outlined text-[18px]">payments</span>
                </div>
            </div>
            <div class="my-space-sm">
                <span class="font-display-lg text-on-surface font-bold tracking-tight">{{ $fmt($monthTotal) }}</span>
            </div>
            <div class="flex items-center gap-space-xs text-error font-body-sm">
                <span class="material-symbols-outlined text-[16px]">trending_up</span>
                <span class="font-semibold">{{ $monthCount }} Mutasi</span>
                <span class="text-on-surface-variant">bulan ini</span>
            </div>
        </div>

        {{-- KPI 2 --}}
        <div class="p-space-lg rounded-xl bg-surface-container-low flex flex-col justify-between shadow-md">
            <div class="flex items-start justify-between">
                <span class="font-label-md text-on-surface-variant uppercase tracking-wider">Pengeluaran Hari Ini</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                </div>
            </div>
            <div class="my-space-sm">
                <span class="font-display-lg text-error font-bold tracking-tight">{{ $fmt($todayTotal) }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="font-body-sm text-on-surface-variant">{{ $fmtJt($todaySupplier) }} Supplier + {{ $fmtJt($todayOps) }} Operasional</span>
                <div class="inline-flex items-center gap-1.5 text-primary font-label-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                    <span>{{ $todayCount }} TRANSAKSI POSTING HARI INI</span>
                </div>
            </div>
        </div>

        {{-- KPI 3: Saldo per akun --}}
        <div class="p-space-lg rounded-xl bg-surface-container-low flex flex-col justify-between shadow-md">
            <div class="flex items-start justify-between mb-2">
                <span class="font-label-md text-on-surface-variant uppercase tracking-wider">Saldo Rekening Aktif</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined text-[18px]">account_balance</span>
                </div>
            </div>
            <div class="flex flex-col gap-space-xs">
                @foreach($acctMaster as $key => $meta)
                    <div class="flex items-center justify-between text-body-sm">
                        <span class="text-on-surface-variant">{{ $meta['label'] }}:</span>
                        <span class="font-label-md text-on-surface font-semibold">{{ $fmt($balances[$key] ?? 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- KPI 4: Dominan --}}
        <div class="p-space-lg rounded-xl bg-surface-container-low flex flex-col justify-between shadow-md">
            <div class="flex items-start justify-between mb-2">
                <span class="font-label-md text-on-surface-variant uppercase tracking-wider">Alokasi Dominan</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined text-[18px]">pie_chart</span>
                </div>
            </div>

            @php
                $sorted = $catBreakdown->sortByDesc('total')->take(3);
                $colors = ['primary', 'tertiary', 'secondary'];
                // Konversi ke array of [key, data] agar index warna & key kategori sinkron
                $sortedList = $sorted->map(fn ($data, $key) => ['key' => $key, 'data' => $data])->values();
            @endphp

            <div class="w-full flex h-2 rounded-full overflow-hidden bg-surface-container-highest my-2">
                @foreach($sortedList as $i => $item)
                    <div class="bg-{{ $colors[$i] ?? 'primary' }} h-full"
                        style="width: {{ $catTotal > 0 ? round(($item['data']['total'] / $catTotal) * 100) : 0 }}%"></div>
                @endforeach
            </div>

            <div class="flex flex-col gap-1 font-label-sm">
                @forelse($sortedList as $i => $item)
                    @php
                        $catKey = $item['key'];
                        $meta   = $catMaster[$catKey] ?? ['label' => ucfirst($catKey)];
                        $pct    = $catTotal > 0 ? round(($item['data']['total'] / $catTotal) * 100) : 0;
                    @endphp
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-1.5 text-on-surface">
                            <span class="w-2 h-2 rounded-full bg-{{ $colors[$i] ?? 'primary' }}"></span>
                            {{ $pct }}% {{ $meta['label'] }}
                        </span>
                        <span class="text-on-surface-variant">{{ $fmtJt($item['data']['total']) }}</span>
                    </div>
                @empty
                    <span class="text-on-surface-variant text-xs">Belum ada pengeluaran bulan ini</span>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ============ FORM + WIDGETS ============ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg">

        {{-- LEFT: Form dengan tab switcher --}}
        <div id="inputFormSection" class="lg:col-span-7 flex flex-col p-space-lg rounded-xl bg-surface-container-low shadow-md">

            {{-- Tab switcher --}}
            <div class="flex items-center p-1 rounded-xl bg-surface-container mb-space-md">
                <button type="button" onclick="switchTab('supplier')" id="tabSupplierBtn"
                        class="flex-1 py-space-xs rounded-lg bg-primary-container text-on-primary-container font-headline-sm text-body-sm font-semibold flex items-center justify-center gap-space-xs shadow-sm transition-all">
                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                    <span>Bayar Hutang Supplier (PO)</span>
                </button>
                <button type="button" onclick="switchTab('ops')" id="tabOpsBtn"
                        class="flex-1 py-space-xs rounded-lg text-on-surface-variant hover:text-on-surface font-headline-sm text-body-sm font-medium flex items-center justify-center gap-space-xs transition-all">
                    <span class="material-symbols-outlined text-[18px]">local_shipping</span>
                    <span>Catat Biaya Operasional</span>
                </button>
            </div>

            @if ($errors->any())
                <div class="mb-space-md p-space-sm rounded-xl bg-error/10 border border-error/30 text-error font-body-sm">
                    <ul class="list-disc list-inside ml-1">
                        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            {{-- ========== TAB 1: BAYAR HUTANG SUPPLIER ========== --}}
            <div id="tabSupplierForm">
                @if($openTempo->isEmpty())
                    <div class="p-space-lg text-center text-on-surface-variant">
                        <span class="material-symbols-outlined text-[48px] text-surface-container-highest">task_alt</span>
                        <p class="font-body-md mt-2">Tidak ada hutang supplier yang menunggu pembayaran.</p>
                    </div>
                @else
                    <form method="POST" id="paySupplierForm" action="{{ route('cash-outs.pay-supplier', $openTempo->first()) }}" class="flex flex-col gap-space-md">
                        @csrf
                        {{-- Pilih Faktur --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-on-surface-variant uppercase tracking-wider flex items-center justify-between">
                                <span>Pilih Faktur Tempo Barang Masuk *</span>
                                <a href="{{ route('incoming-goods.index', ['status' => 'TEMPO']) }}" class="text-primary hover:underline text-xs">Lihat Buku Hutang</a>
                            </label>
                            <div class="relative">
                                <select id="openTempoSelect" onchange="updateSupplierForm()"
                                        class="w-full h-11 pl-space-md pr-10 rounded-xl bg-surface-container text-on-surface font-body-sm appearance-none focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer">
                                    @foreach($openTempo as $t)
                                        <option value="{{ $t->id }}"
                                                data-action="{{ route('cash-outs.pay-supplier', $t) }}"
                                                data-remaining="{{ (int) $t->remaining }}"
                                                @selected($loop->first)>
                                            {{ $t->supplier->name }} - Faktur {{ $t->no_faktur }} (Sisa: {{ $fmt($t->remaining) }}, JT: {{ $t->due_date?->format('d M Y') ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[20px]">expand_more</span>
                            </div>
                        </div>

                        {{-- Akun sumber --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Sumber Dana / Rekening *</label>
                            <div class="grid grid-cols-3 gap-space-xs">
                                @foreach($acctMaster as $key => $meta)
                                    <label class="flex items-center gap-space-xs p-space-sm rounded-xl cursor-pointer text-on-surface transition-colors
                                        {{ old('account', 'bca') === $key ? 'bg-primary-container/20 ring-1 ring-primary' : 'bg-surface-container-low hover:bg-surface-container' }}">
                                        <input type="radio" name="account" value="{{ $key }}" class="accent-primary" @checked(old('account', 'bca') === $key) />
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-headline-sm text-body-sm truncate">{{ $meta['label'] }}</span>
                                            <span class="font-label-sm text-on-surface-variant">Sisa {{ $fmtJt($balances[$key] ?? 0) }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Nominal --}}
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Jumlah Pembayaran *</label>
                                <div class="flex items-center gap-1">
                                    <button type="button" onclick="setPayAmount('50')" class="px-2 py-0.5 rounded bg-surface-container-high text-primary text-label-sm">[50%]</button>
                                    <button type="button" onclick="setPayAmount('100')" class="px-2 py-0.5 rounded bg-surface-container-high text-primary text-label-sm">[100%]</button>
                                </div>
                            </div>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-headline-sm text-primary font-bold">Rp</span>
                                <input name="amount" id="payAmountInput" type="number" min="1" required
                                       value="{{ old('amount', (int) $openTempo->first()->remaining) }}"
                                       class="w-full h-11 pl-12 pr-space-md rounded-xl bg-surface-container font-label-lg text-headline-sm text-on-surface font-bold focus:outline-none focus:ring-1 focus:ring-primary" />
                            </div>
                        </div>

                        {{-- Tanggal --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Tanggal Pembayaran</label>
                            <input name="paid_at" type="datetime-local" required value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}"
                                   class="w-full h-10 px-space-md rounded-xl bg-surface-container font-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                        </div>

                        <input type="hidden" name="payment_method" value="Transfer Bank">

                        {{-- Notes --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Catatan / Referensi</label>
                            <input name="notes" type="text" value="{{ old('notes') }}" placeholder="Contoh: Pembayaran termin 1 kulakan Sunlight PO-9921"
                                   class="w-full h-10 px-space-md rounded-xl bg-surface-container font-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                        </div>

                        <button type="submit" class="w-full h-12 rounded-xl bg-primary-container text-on-primary-container font-headline-sm font-bold flex items-center justify-center gap-space-xs shadow-[0_4px_18px_rgba(16,185,129,0.35)]">
                            <span class="material-symbols-outlined text-[20px]">bolt</span>
                            <span>⚡ Simpan & Posting Pelunasan Hutang</span>
                        </button>
                    </form>
                @endif
            </div>

            {{-- ========== TAB 2: BIAYA OPERASIONAL ========== --}}
            <div id="tabOpsForm" class="hidden">
                <form method="POST" action="{{ route('cash-outs.store') }}" class="flex flex-col gap-space-md">
                    @csrf
                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Kategori Pengeluaran *</label>
                        <select name="category" required class="w-full h-11 px-space-md rounded-xl bg-surface-container font-body-sm focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="">— Pilih Kategori —</option>
                            <option value="biaya_operasional">Biaya Operasional Umum</option>
                            <option value="biaya_listrik">Listrik & Utilitas</option>
                            <option value="gaji_staff">Gaji & Kasbon Staff</option>
                            <option value="logistik_armada">Logistik & Armada</option>
                            <option value="bensin_distribusi">Bensin & Tol Distribusi</option>
                            <option value="lain_lain">Lain-lain</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Sumber Dana *</label>
                        <div class="grid grid-cols-3 gap-space-xs">
                            @foreach($acctMaster as $key => $meta)
                                <label class="flex items-center gap-space-xs p-space-sm rounded-xl cursor-pointer bg-surface-container-low hover:bg-surface-container">
                                    <input type="radio" name="account" value="{{ $key }}" class="accent-primary" @checked($loop->first || old('account') === $key) />
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-headline-sm text-body-sm truncate">{{ $meta['label'] }}</span>
                                        <span class="font-label-sm text-on-surface-variant">Sisa {{ $fmtJt($balances[$key] ?? 0) }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Nominal *</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-headline-sm text-primary font-bold">Rp</span>
                            <input name="amount" type="number" min="1" required value="{{ old('amount') }}"
                                   class="w-full h-11 pl-12 pr-space-md rounded-xl bg-surface-container font-headline-sm font-bold focus:outline-none focus:ring-1 focus:ring-primary" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Tanggal</label>
                        <input name="paid_at" type="datetime-local" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}"
                               class="w-full h-10 px-space-md rounded-xl bg-surface-container font-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Keterangan *</label>
                        <input name="notes" type="text" required value="{{ old('notes') }}" placeholder="Contoh: Tagihan listrik & cold storage November"
                               class="w-full h-10 px-space-md rounded-xl bg-surface-container font-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                    </div>

                    <button type="submit" class="w-full h-12 rounded-xl bg-primary-container text-on-primary-container font-headline-sm font-bold flex items-center justify-center gap-space-xs shadow-[0_4px_18px_rgba(16,185,129,0.35)]">
                        <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                        <span>Simpan & Posting Pengeluaran</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- RIGHT: Widget Hutang + Biaya Rutin --}}
        <div class="lg:col-span-5 flex flex-col gap-space-md">

            {{-- Widget 1: Hutang < 5 hari --}}
            <div class="p-space-lg rounded-xl bg-surface-container-low shadow-md flex flex-col gap-space-md">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-space-xs">
                        <span class="material-symbols-outlined text-error text-[20px]">alarm</span>
                        <h3 class="font-headline-sm text-on-surface">Jatuh Tempo Supplier Segera (&lt; 5 Hari)</h3>
                    </div>
                    <span class="px-2 py-0.5 rounded-full bg-error-container/40 text-error font-label-sm font-semibold">{{ $upcomingDue->count() }} Tagihan</span>
                </div>
                <div class="flex flex-col gap-space-sm">
                    @forelse($upcomingDue as $t)
                        @php $days = now()->startOfDay()->diffInDays($t->due_date, false); @endphp
                        <div class="p-space-md rounded-xl bg-surface-container flex flex-col gap-space-xs">
                            <div class="flex items-start justify-between">
                                <div class="flex flex-col">
                                    <span class="font-headline-sm text-body-md text-on-surface font-semibold">{{ $t->supplier->name }}</span>
                                    <span class="font-label-sm text-on-surface-variant">Faktur {{ $t->no_faktur }} • {{ $t->product->name }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded {{ $days <= 1 ? 'bg-error/10 text-error' : 'bg-tertiary-container/30 text-tertiary' }} font-label-sm font-bold">
                                    {{ $days < 0 ? 'Terlambat ' . abs($days) . ' hari' : ($days == 0 ? 'Hari ini' : 'Sisa ' . $days . ' hari') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between pt-1">
                                <div class="flex flex-col">
                                    <span class="font-headline-sm text-body-md {{ $days <= 1 ? 'text-error' : 'text-on-surface' }} font-bold">{{ $fmt($t->remaining) }}</span>
                                    <span class="font-label-sm text-on-surface-variant">JT: {{ $t->due_date->format('d M Y') }}</span>
                                </div>
                                <button type="button" onclick="payFromWidget({{ $t->id }})"
                                        class="h-8 px-space-md rounded-lg bg-primary hover:bg-primary-fixed text-on-primary font-headline-sm text-body-sm font-semibold">
                                    Bayar Sekarang
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-space-md text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-[32px] text-primary">check_circle</span>
                            <p class="font-body-sm mt-1">Tidak ada tagihan jatuh tempo < 5 hari.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Widget 2: Biaya Rutin Terjadwal --}}
            <div class="p-space-lg rounded-xl bg-surface-container-low shadow-md flex flex-col gap-space-md">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-space-xs">
                        <span class="material-symbols-outlined text-tertiary text-[20px]">calendar_month</span>
                        <h3 class="font-headline-sm text-on-surface">Biaya Rutin Operasional Terakhir</h3>
                    </div>
                    <span class="font-label-sm text-on-surface-variant">Siklus Bulanan</span>
                </div>
                <div class="flex flex-col gap-space-xs">
                    @foreach($recurringCats as $rc)
                        @php
                            $last = $recurringCosts[$rc] ?? null;
                            $meta = $catMaster[$rc] ?? ['label' => ucfirst($rc)];
                        @endphp
                        <div class="p-space-sm rounded-xl bg-surface-container flex items-center justify-between">
                            <div class="flex items-center gap-space-sm">
                                <div class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-tertiary">
                                    <span class="material-symbols-outlined text-[18px]">
                                        {{ $rc === 'biaya_listrik' ? 'bolt' : ($rc === 'logistik_armada' ? 'local_shipping' : 'local_gas_station') }}
                                    </span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-headline-sm text-body-sm text-on-surface font-medium">{{ $meta['label'] }}</span>
                                    <span class="font-label-sm text-on-surface-variant">
                                        {{ $last ? 'Terakhir: ' . $last->paid_at->translatedFormat('d M Y') : 'Belum ada' }}
                                    </span>
                                </div>
                            </div>
                            <span class="font-label-md text-on-surface font-bold">{{ $last ? $fmt($last->amount) : '—' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ============ TABEL JURNAL ============ --}}
    <div class="flex flex-col p-space-lg rounded-xl bg-surface-container-low shadow-md">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-space-md mb-space-md">
            <div class="flex flex-col">
                <div class="flex items-center gap-space-xs">
                    <div class="w-2.5 h-2.5 rounded-full bg-primary animate-pulse"></div>
                    <h2 class="font-headline-lg text-headline-md text-on-surface">Jurnal Buku Kas Keluar Realtime</h2>
                </div>
                <p class="font-body-sm text-on-surface-variant mt-0.5">Mutasi uang keluar dari modul Barang Masuk, pelunasan hutang, dan operasional.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('cash-outs.index') }}" id="filterForm" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-space-md mb-space-md">
            <div class="flex items-center gap-1 p-1 rounded-xl bg-surface-container overflow-x-auto">
                @php
                    $tabs = [
                        ['value' => 'all',         'label' => "Semua ({$tabCounts['all']})"],
                        ['value' => 'supplier',    'label' => "Pelunasan Hutang ({$tabCounts['supplier']})"],
                        ['value' => 'operasional', 'label' => "Operasional ({$tabCounts['operasional']})"],
                        ['value' => 'manual',      'label' => "Manual ({$tabCounts['manual']})"],
                    ];
                @endphp
                @foreach($tabs as $t)
                    @php $active = $cat === $t['value']; @endphp
                    <button type="submit" name="category" value="{{ $t['value'] }}"
                            class="px-space-md py-1.5 rounded-lg whitespace-nowrap transition-all text-body-sm
                            {{ $active ? 'bg-primary-container text-on-primary-container font-bold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface font-semibold' }}">
                        {{ $t['label'] }}
                    </button>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-space-sm flex-1 max-w-xl lg:justify-end">
                <div class="relative flex-1 min-w-[240px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input name="search" value="{{ request('search') }}" oninput="debouncedFilter()"
                           placeholder="Cari No. KK / Supplier / Keterangan..."
                           class="w-full h-9 pl-9 pr-space-md rounded-lg bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                </div>
                <select name="account" onchange="this.form.submit()" class="h-9 px-3 rounded-lg bg-surface-container text-body-sm">
                    <option value="all" @selected($acc === 'all')>Semua Akun</option>
                    @foreach($acctMaster as $k => $m)
                        <option value="{{ $k }}" @selected($acc === $k)>{{ $m['label'] }}</option>
                    @endforeach
                </select>
                <select name="sort" onchange="this.form.submit()" class="h-9 px-3 rounded-lg bg-surface-container text-body-sm">
                    <option value="latest"      @selected($sort === 'latest')>Terbaru</option>
                    <option value="amount_desc" @selected($sort === 'amount_desc')>Nominal Tertinggi</option>
                    <option value="amount_asc"  @selected($sort === 'amount_asc')>Nominal Terendah</option>
                </select>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl bg-surface-container-lowest">
            <table class="w-full text-left font-body-sm border-collapse">
                <thead>
                    <tr class="bg-surface-container font-label-md text-on-surface-variant uppercase tracking-wider">
                        <th class="py-space-md px-space-md">No. Kas Keluar & Waktu</th>
                        <th class="py-space-md px-space-md">Tujuan / Penerima</th>
                        <th class="py-space-md px-space-md">Kategori & Rincian</th>
                        <th class="py-space-md px-space-md">Akun Sumber</th>
                        <th class="py-space-md px-space-md text-right">Jumlah</th>
                        <th class="py-space-md px-space-md text-center">Status</th>
                        <th class="py-space-md px-space-md text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-highest">
                    @forelse($cashOuts as $co)
                        @php
                            $catMeta = $catMaster[$co->category] ?? ['label' => ucfirst($co->category), 'color' => 'primary'];
                            $accMeta = $acctMaster[$co->account] ?? ['label' => ucfirst($co->account), 'color' => 'primary'];
                            $isAuto = $co->is_auto;
                        @endphp
                        <tr class="hover:bg-surface-container-high/40 transition-colors">
                            <td class="py-space-md px-space-md">
                                <div class="flex flex-col">
                                    <span class="font-mono font-bold text-primary">{{ $co->code }}</span>
                                    <span class="font-label-sm text-on-surface-variant">{{ $co->paid_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                    <span class="text-[10px] {{ $isAuto ? 'text-primary' : 'text-tertiary' }}">
                                        {{ $isAuto ? 'Auto-Journal' : 'Manual Input' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="flex flex-col">
                                    <span class="font-headline-sm text-body-md text-on-surface font-semibold">{{ $co->source?->supplier?->name ?? 'Pengeluaran Manual' }}</span>
                                    @if($co->reference_no)
                                        <span class="font-label-sm text-on-surface-variant">Ref: {{ $co->reference_no }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="flex flex-col items-start gap-1">
                                    <span class="px-2 py-0.5 rounded bg-{{ $catMeta['color'] }}/10 text-{{ $catMeta['color'] }} font-label-sm uppercase font-bold">{{ $catMeta['label'] }}</span>
                                    <span class="text-xs text-on-surface-variant">{{ Str::limit($co->notes, 60) }}</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-{{ $accMeta['color'] }}"></span>
                                    <span class="font-mono text-xs text-on-surface font-semibold">{{ $accMeta['label'] }}</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md text-right">
                                <div class="flex items-center justify-end gap-1 text-error font-mono font-extrabold text-body-lg">
                                    <span class="material-symbols-outlined text-[16px]">arrow_outward</span>
                                    -{{ $fmt($co->amount) }}
                                </div>
                            </td>
                            <td class="py-space-md px-space-md text-center">
                                <span class="px-2 py-0.5 rounded-full bg-primary-container/20 text-primary font-label-sm font-semibold">
                                    {{ $isAuto ? 'TERPOSTING' : 'TERVERIFIKASI' }}
                                </span>
                            </td>
                            <td class="py-space-md px-space-md text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" class="w-8 h-8 rounded-lg hover:bg-surface-container text-on-surface-variant flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </button>
                                    <button type="button" class="w-8 h-8 rounded-lg hover:bg-surface-container text-on-surface-variant flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[18px]">print</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-space-xl text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[40px] text-surface-container-highest">payments</span>
                                <p>Belum ada mutasi kas keluar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-space-sm pt-space-md text-body-sm text-on-surface-variant">
            @if($cashOuts->total() > 0)
                <span>Menampilkan {{ $cashOuts->firstItem() }}-{{ $cashOuts->lastItem() }} dari {{ $cashOuts->total() }} mutasi | Total halaman: <span class="font-bold text-error">{{ $fmt($cashOuts->getCollection()->sum('amount')) }}</span></span>
            @endif
            <div>{{ $cashOuts->links() }}</div>
        </div>
    </div>

    {{-- ============ FOOTER ============ --}}
    <div class="p-space-lg rounded-xl bg-surface-container-low flex flex-col md:flex-row md:items-center justify-between gap-space-md shadow-md">
        <div class="flex items-start md:items-center gap-space-md">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
                <span class="material-symbols-outlined text-[28px]">lock_reset</span>
            </div>
            <div class="flex flex-col max-w-3xl">
                <h4 class="font-headline-sm text-on-surface font-semibold">Prinsip Integritas Kas Keluar GrosirBox PRO</h4>
                <p class="font-body-sm text-on-surface-variant mt-1">
                    Setiap pengeluaran untuk kulakan terikat pada nomor faktur di modul <span class="text-primary font-medium">Barang Masuk</span>. Sistem mencegah pembayaran ganda (<span class="text-on-surface font-medium">Double-Payment Prevention</span>) dan setiap voucher memiliki audit trail otomatis.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    window.switchTab = function (tab) {
        const supplierForm = document.getElementById('tabSupplierForm');
        const opsForm = document.getElementById('tabOpsForm');
        const supplierBtn = document.getElementById('tabSupplierBtn');
        const opsBtn = document.getElementById('tabOpsBtn');

        if (tab === 'supplier') {
            supplierForm.classList.remove('hidden');
            opsForm.classList.add('hidden');
            supplierBtn.className = 'flex-1 py-space-xs rounded-lg bg-primary-container text-on-primary-container font-headline-sm text-body-sm font-semibold flex items-center justify-center gap-space-xs shadow-sm transition-all';
            opsBtn.className = 'flex-1 py-space-xs rounded-lg text-on-surface-variant hover:text-on-surface font-headline-sm text-body-sm font-medium flex items-center justify-center gap-space-xs transition-all';
        } else {
            supplierForm.classList.add('hidden');
            opsForm.classList.remove('hidden');
            supplierBtn.className = 'flex-1 py-space-xs rounded-lg text-on-surface-variant hover:text-on-surface font-headline-sm text-body-sm font-medium flex items-center justify-center gap-space-xs transition-all';
            opsBtn.className = 'flex-1 py-space-xs rounded-lg bg-primary-container text-on-primary-container font-headline-sm text-body-sm font-semibold flex items-center justify-center gap-space-xs shadow-sm transition-all';
        }
    };

    window.updateSupplierForm = function () {
        const sel = document.getElementById('openTempoSelect');
        if (!sel) return;
        const opt = sel.options[sel.selectedIndex];
        const form = document.getElementById('paySupplierForm');
        if (form) form.action = opt.dataset.action;
        const amt = document.getElementById('payAmountInput');
        if (amt) {
            amt.value = opt.dataset.remaining;
            amt.max = opt.dataset.remaining;
        }
    };

    window.setPayAmount = function (pct) {
        const sel = document.getElementById('openTempoSelect');
        const amt = document.getElementById('payAmountInput');
        if (!sel || !amt) return;
        const remaining = parseInt(sel.options[sel.selectedIndex].dataset.remaining || 0, 10);
        amt.value = pct === '100' ? remaining : Math.floor(remaining / 2);
    };

    window.payFromWidget = function (trxId) {
        const sel = document.getElementById('openTempoSelect');
        if (!sel) return;
        const opts = Array.from(sel.options);
        const target = opts.find(o => o.value == trxId);
        if (target) {
            sel.value = trxId;
            updateSupplierForm();
        }
        switchTab('supplier');
        document.getElementById('inputFormSection').scrollIntoView({ behavior: 'smooth' });
    };

    let filterTimer;
    window.debouncedFilter = function () {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
    };
})();
</script>
@endpush