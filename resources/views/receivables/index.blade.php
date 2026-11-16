@extends('layouts.app')

@section('title', 'Buku Tagihan')

@php
    $fmt    = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $fmtJt  = function ($n) {
        if ($n >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 2, ',', '.') . ' M';
        if ($n >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 2, ',', '.') . ' Jt';
        if ($n >= 1_000)         return 'Rp ' . number_format($n / 1_000, 0, ',', '.') . ' Rb';
        return 'Rp ' . number_format((int) $n, 0, ',', '.');
    };
    $num    = fn ($n) => number_format((int) $n, 0, ',', '.');
@endphp

@section('content')
<div class="flex flex-col w-full pb-16">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-col gap-space-sm mb-space-lg">
        <div class="flex items-center gap-space-xs font-label-md text-label-md text-on-surface-variant">
            <span class="hover:text-primary cursor-pointer">Sistem Grosir</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="hover:text-primary cursor-pointer">Manajemen Finansial</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-primary font-semibold">Buku Tagihan (Piutang Pelanggan)</span>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-space-md">
            <div class="flex flex-col gap-1 max-w-3xl">
                <div class="flex items-center gap-space-sm flex-wrap">
                    <h1 class="font-headline-lg text-headline-lg text-on-surface font-extrabold tracking-tight">
                        Buku Tagihan & Piutang Pelanggan
                    </h1>
                    <div class="flex items-center gap-1.5 px-space-sm py-0.5 rounded-full bg-primary/10 text-primary">
                        <span class="w-2 h-2 rounded-full bg-primary animate-ping"></span>
                        <span class="font-label-sm text-label-sm uppercase tracking-wider font-bold">Sinkronisasi Kas Masuk Aktif</span>
                    </div>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                    Pantau tagihan transaksi penjualan tempo dari modul Barang Keluar. Pelunasan tagihan otomatis tercatat ke <span class="text-primary font-medium">Kas Masuk</span> dan memperbarui plafon kredit mitra secara seketika.
                </p>
            </div>
            <div class="flex items-center gap-space-sm flex-wrap">
                <a href="{{ route('receivables.export', request()->only(['filter', 'search', 'sort'])) }}"
                   class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm flex items-center gap-space-xs transition-colors shadow-sm whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px] text-tertiary">download</span>
                    <span>Export Laporan (CSV)</span>
                </a>
                <button type="button" onclick="openWaReminderModal()"
                        class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm flex items-center gap-space-xs transition-colors shadow-sm whitespace-nowrap relative">
                    <span class="material-symbols-outlined text-[18px] text-primary">chat</span>
                    <span>Pengingat WA Prioritas</span>
                    @if($priorityWaTrx->count() > 0)
                        <span class="px-1.5 py-0.2 rounded-full bg-error text-on-error font-label-sm text-[11px] font-bold">
                            {{ $priorityWaTrx->count() }}
                        </span>
                    @endif
                </button>
                <button type="button" onclick="openPaymentModal()"
                        class="h-10 px-space-lg rounded-xl bg-primary-container text-on-primary-container font-headline-sm text-body-sm font-bold flex items-center gap-space-xs shadow-[0_4px_20px_rgba(16,185,129,0.35)] hover:bg-primary transition-all active:scale-95 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[20px]">add_card</span>
                    <span>Catat Pelunasan Tagihan</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ============ FLASH ALERT ============ --}}
    @if(session('success'))
        <div class="mb-space-lg p-space-md rounded-xl bg-primary/15 border border-primary/30 text-on-surface flex items-center justify-between gap-space-sm">
            <div class="flex items-center gap-space-sm">
                <span class="material-symbols-outlined text-primary text-[22px]">check_circle</span>
                <span class="font-body-sm text-body-sm font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-on-surface-variant hover:text-on-surface">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endif

    {{-- ============ OVERDUE BANNER ============ --}}
    @if($overdueCount > 0)
        <div class="p-space-md rounded-xl bg-linear-to-r from-error-container/40 via-surface-container-high to-surface-container-low mb-space-lg flex flex-wrap items-center justify-between gap-space-md shadow-md">
            <div class="flex items-center gap-space-md min-w-0">
                <div class="w-10 h-10 rounded-xl bg-error/20 flex items-center justify-center text-error flex-shrink-0">
                    <span class="material-symbols-outlined text-[24px]">warning</span>
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center gap-space-xs flex-wrap">
                        <span class="font-headline-sm text-body-md font-bold text-on-surface">Peringatan Jatuh Tempo: {{ $overdueCount }} Tagihan Melewati Batas Tempo (Overdue)</span>
                        <span class="px-2 py-0.5 rounded-full bg-error text-on-error font-label-sm text-label-sm font-bold uppercase">Prioritas Koleksi</span>
                    </div>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">
                        Total tagihan macet berisiko <span class="font-label-md text-label-md font-bold text-error">{{ $fmt($overdue) }}</span>. Plafon kredit mitra terkait dibekukan sementara hingga pembayaran diterima.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-space-sm">
                <a href="{{ route('receivables.index', ['filter' => 'overdue']) }}"
                   class="h-9 px-space-md rounded-lg bg-error hover:bg-error/90 text-on-error font-headline-sm text-body-sm font-semibold flex items-center gap-1.5 transition-all shadow-sm whitespace-nowrap">
                    <span class="material-symbols-outlined text-[16px]">outgoing_mail</span>
                    <span>Follow-up ({{ $overdueCustomers->count() }} Toko)</span>
                </a>
            </div>
        </div>
    @endif

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-space-md mb-space-lg">
        {{-- KPI 1 --}}
        <div class="p-space-md rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex flex-col justify-between shadow-sm relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-28 h-28 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/10"></div>
            <div class="flex items-center justify-between mb-space-xs">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-bold">Total Piutang Berjalan</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                </div>
            </div>
            <div class="my-space-xs">
                <span class="font-display-lg text-display-lg text-on-surface font-extrabold tracking-tight">{{ $fmt($totalReceivable) }}</span>
            </div>
            <div class="flex items-center justify-between pt-space-xs">
                <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $totalInvoices }} Faktur Penjualan Tempo</span>
                <span class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded-full">{{ $fmtJt($totalReceivable) }}</span>
            </div>
        </div>

        {{-- KPI 2 --}}
        <div class="p-space-md rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex flex-col justify-between shadow-sm relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-28 h-28 bg-tertiary/5 rounded-full blur-2xl group-hover:bg-tertiary/10"></div>
            <div class="flex items-center justify-between mb-space-xs">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-bold">Tagihan Jatuh Tempo (&lt; 3 Hari)</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined text-[18px]">schedule</span>
                </div>
            </div>
            <div class="my-space-xs">
                <span class="font-display-lg text-display-lg text-tertiary font-extrabold tracking-tight">{{ $fmt($dueSoon) }}</span>
            </div>
            <div class="flex items-center justify-between pt-space-xs">
                <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $dueSoonCustomers }} Mitra Toko perlu konfirmasi</span>
                <span class="font-label-sm text-label-sm text-tertiary font-bold bg-tertiary/10 px-2 py-0.5 rounded-full">Koleksi Minggu Ini</span>
            </div>
        </div>

        {{-- KPI 3 --}}
        <div class="p-space-md rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex flex-col justify-between shadow-sm relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-28 h-28 bg-error/5 rounded-full blur-2xl group-hover:bg-error/10"></div>
            <div class="flex items-center justify-between mb-space-xs">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-bold">Piutang Overdue (Lewat Tempo)</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-error">
                    <span class="material-symbols-outlined text-[18px]">error</span>
                </div>
            </div>
            <div class="my-space-xs">
                <span class="font-display-lg text-display-lg text-error font-extrabold tracking-tight">{{ $fmt($overdue) }}</span>
            </div>
            <div class="flex items-center justify-between pt-space-xs">
                <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $overdueCount }} Faktur tertunda</span>
                <span class="font-label-sm text-label-sm text-error font-bold bg-error/10 px-2 py-0.5 rounded-full">Plafon Dikunci</span>
            </div>
        </div>

        {{-- KPI 4 --}}
        <div class="p-space-md rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex flex-col justify-between shadow-sm relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-28 h-28 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/10"></div>
            <div class="flex items-center justify-between mb-space-xs">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-bold">Pelunasan Minggu Ini (Kas Masuk)</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                </div>
            </div>
            <div class="my-space-xs">
                <span class="font-display-lg text-display-lg text-primary font-extrabold tracking-tight">{{ $fmt($paidThisWeek) }}</span>
            </div>
            <div class="flex items-center justify-between pt-space-xs">
                <span class="font-body-sm text-body-sm text-on-surface-variant">Terposting ke Kas Tunai & Bank</span>
                <span class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded-full">{{ $paidWeekCnt }} Faktur</span>
            </div>
        </div>
    </div>

    {{-- ============ RADAR MITRA PIUTANG + AGING SCHEDULE ============ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-md mb-space-lg">

        {{-- LEFT: Top 5 Mitra Piutang Terbesar (Klik untuk Filter) --}}
        <div class="lg:col-span-5 flex flex-col justify-between p-space-md rounded-xl bg-surface-container-low shadow-sm">
            <div class="flex items-center justify-between pb-space-sm">
                <div class="flex items-center gap-space-xs">
                    <span class="material-symbols-outlined text-tertiary text-[20px]">storefront</span>
                    <span class="font-headline-sm text-body-md font-bold text-on-surface">Top Mitra Piutang Berjalan</span>
                </div>
                <span class="px-2 py-0.5 rounded-md bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm font-semibold">
                    {{ $topDebtors->count() }} Mitra Utama
                </span>
            </div>

            @if($topDebtors->isNotEmpty())
                <div class="flex flex-col gap-space-xs my-auto">
                    @foreach($topDebtors as $debtor)
                        @php
                            $cust      = $debtor->customer;
                            $limit     = max(1, (float) ($cust->credit_limit ?? 0));
                            $usagePct  = $cust && $cust->credit_limit > 0
                                ? min(100, round(((float) $debtor->total_due / $limit) * 100))
                                : 0;
                            $barColor  = $usagePct >= 90 ? 'bg-error' : ($usagePct >= 70 ? 'bg-tertiary' : 'bg-primary');
                        @endphp
                        <a href="{{ route('receivables.index', ['search' => $cust->name ?? '']) }}"
                           class="p-space-xs px-space-sm rounded-xl bg-surface-container hover:bg-surface-container-high/80 transition-colors flex items-center justify-between gap-space-sm group"
                           title="Klik untuk memfilter faktur milik {{ $cust->name ?? 'Mitra' }}">
                            <div class="flex flex-col min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-headline-sm text-body-sm font-bold text-on-surface group-hover:text-primary transition-colors truncate">{{ $cust->name ?? 'Mitra' }}</span>
                                    <span class="font-label-md text-label-md font-extrabold text-on-surface whitespace-nowrap">{{ $fmt($debtor->total_due) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-label-sm text-on-surface-variant mt-0.5">
                                    <span>{{ $debtor->invoice_count }} Faktur • Tempo terdekat {{ $debtor->nearest_due ? \Carbon\Carbon::parse($debtor->nearest_due)->format('d M') : '-' }}</span>
                                    @if($cust && $cust->credit_limit > 0)
                                        <span>Kuota {{ $usagePct }}%</span>
                                    @endif
                                </div>
                                @if($cust && $cust->credit_limit > 0)
                                    <div class="w-full h-1.5 rounded-full bg-surface-container-highest overflow-hidden mt-1.5">
                                        <div class="h-full {{ $barColor }} transition-all" style="width: {{ $usagePct }}%"></div>
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="p-space-lg text-center text-on-surface-variant flex flex-col items-center justify-center my-auto">
                    <span class="material-symbols-outlined text-[42px] text-primary/40">task_alt</span>
                    <p class="font-body-sm mt-2">Tidak ada mitra dengan saldo piutang berjalan.</p>
                </div>
            @endif
        </div>

        {{-- RIGHT: Aging Schedule --}}
        <div class="lg:col-span-7 flex flex-col justify-between p-space-md rounded-xl bg-surface-container-low shadow-sm">
            <div class="flex items-center justify-between mb-space-xs">
                <div class="flex flex-col">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold tracking-wider">Distribusi Umur Piutang (Aging Schedule)</span>
                    <span class="font-headline-sm text-body-md font-bold text-on-surface">Analisis Risiko Piutang Dagang Grosir</span>
                </div>
                <div class="flex items-center gap-1 font-label-sm text-label-sm text-on-surface-variant">
                    <span class="w-2 h-2 rounded-full bg-primary"></span> 0-14 Hari
                    <span class="w-2 h-2 rounded-full bg-tertiary ml-2"></span> 15-30 Hari
                    <span class="w-2 h-2 rounded-full bg-error ml-2"></span> &gt; 30 Hari
                </div>
            </div>

            <div class="flex flex-col gap-2 my-space-xs">
                <div class="w-full h-3 rounded-full bg-surface-container-highest overflow-hidden flex">
                    <div class="h-full bg-primary transition-all" style="width: {{ $aging['lancar_pct'] }}%"></div>
                    <div class="h-full bg-tertiary transition-all" style="width: {{ $aging['warning_pct'] }}%"></div>
                    <div class="h-full bg-error transition-all" style="width: {{ $aging['critical_pct'] }}%"></div>
                </div>
                <div class="grid grid-cols-3 gap-space-sm pt-space-xs">
                    <div class="p-space-xs rounded-lg bg-surface-container flex flex-col">
                        <span class="font-label-sm text-label-sm text-primary font-bold">1-14 HARI (LANCAR)</span>
                        <span class="font-label-md text-label-md text-on-surface font-bold">{{ $fmt($aging['lancar']) }}</span>
                        <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $aging['lancar_count'] }} Faktur Aktif</span>
                    </div>
                    <div class="p-space-xs rounded-lg bg-surface-container flex flex-col">
                        <span class="font-label-sm text-label-sm text-tertiary font-bold">15-30 HARI (PERINGATAN)</span>
                        <span class="font-label-md text-label-md text-on-surface font-bold">{{ $fmt($aging['warning']) }}</span>
                        <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $aging['warning_count'] }} Faktur Perlu Follow-up</span>
                    </div>
                    <div class="p-space-xs rounded-lg bg-surface-container flex flex-col">
                        <span class="font-label-sm text-label-sm text-error font-bold">&gt; 30 HARI (KRITIS)</span>
                        <span class="font-label-md text-label-md text-on-surface font-bold">{{ $fmt($aging['critical']) }}</span>
                        <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $aging['critical_count'] }} Faktur Ditangguhkan</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-space-xs pt-space-xs">
                <div class="flex flex-col p-2 rounded-lg bg-surface-container-high/50">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Rata-rata Tempo</span>
                    <span class="font-headline-sm text-body-md font-bold text-on-surface">{{ $avgTempoDays }} Hari</span>
                </div>
                <div class="flex flex-col p-2 rounded-lg bg-surface-container-high/50">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Tingkat Kolektibilitas</span>
                    <span class="font-headline-sm text-body-md font-bold text-primary">{{ $totalReceivable > 0 ? round(($paidThisWeek / max($paidThisWeek + $totalReceivable, 1)) * 100, 1) : 100 }}%</span>
                </div>
                <div class="flex flex-col p-2 rounded-lg bg-surface-container-high/50">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Estimasi Cair H+3</span>
                    <span class="font-headline-sm text-body-md font-bold text-tertiary">{{ $fmt($dueSoon) }}</span>
                </div>
                <div class="flex flex-col p-2 rounded-lg bg-surface-container-high/50">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Plafon Terkunci</span>
                    <span class="font-headline-sm text-body-md font-bold text-error">{{ $overdueCustomers->count() }} Mitra</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ FILTER TABS ============ --}}
    <div class="flex flex-col gap-space-sm mb-space-md">
        <div class="flex items-center gap-space-xs overflow-x-auto pb-1">
            @php
                $tabs = [
                    ['value' => 'all',      'label' => 'Semua Piutang',                   'count' => $totalInvoices, 'count_class' => 'bg-on-primary-container text-primary-container'],
                    ['value' => 'not_due',  'label' => 'Belum Jatuh Tempo',               'count' => $notDueCount,   'count_class' => 'bg-surface-container-highest text-on-surface'],
                    ['value' => 'due_soon', 'label' => 'Mendekati Jatuh Tempo (1-3 Hari)', 'count' => $dueSoonCount,  'count_class' => 'bg-tertiary/20 text-tertiary font-bold'],
                    ['value' => 'overdue',  'label' => 'Lewat Jatuh Tempo / Overdue',     'count' => $overdueCount,  'count_class' => 'bg-error/20 text-error font-bold'],
                    ['value' => 'paid',     'label' => 'Riwayat Lunas Bulan Ini',         'count' => $paidMonthCnt,  'count_class' => 'bg-primary/20 text-primary'],
                ];
            @endphp
            @foreach($tabs as $t)
                @php $active = $filter === $t['value']; @endphp
                <a href="{{ route('receivables.index', ['filter' => $t['value'], 'search' => request('search'), 'sort' => request('sort')]) }}"
                   class="px-space-md py-space-xs rounded-xl font-headline-sm text-body-sm flex items-center gap-space-xs whitespace-nowrap transition-colors
                   {{ $active ? 'bg-primary-container text-on-primary-container font-bold shadow-sm' : 'bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface' }}">
                    <span>{{ $t['label'] }}</span>
                    <span class="px-1.5 py-0.2 rounded-full {{ $t['count_class'] }} font-label-sm text-label-sm">{{ $t['count'] }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('receivables.index') }}" id="filterForm"
              class="flex flex-wrap items-center justify-between gap-space-sm bg-surface-container-low p-space-sm rounded-xl shadow-sm">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div class="flex items-center gap-space-sm flex-1 min-w-[320px]">
                <div class="relative w-full max-w-lg">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                    <input name="search" value="{{ request('search') }}" oninput="debouncedFilter()"
                           placeholder="Cari No. Faktur, Nama Toko Mitra, atau Kontak PIC..."
                           class="w-full h-10 pl-10 pr-space-md rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/60 font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                </div>
            </div>
            <div class="flex items-center gap-space-sm">
                <div class="flex items-center gap-1 font-body-sm text-body-sm text-on-surface-variant">
                    <span>Urutkan:</span>
                    <select name="sort" onchange="this.form.submit()"
                            class="h-10 px-space-sm rounded-xl bg-surface-container text-on-surface font-headline-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="due_asc"        @selected(request('sort', 'due_asc') === 'due_asc')>Jatuh Tempo Terdekat</option>
                        <option value="remaining_desc" @selected(request('sort') === 'remaining_desc')>Sisa Nominal Tertinggi</option>
                        <option value="amount_desc"    @selected(request('sort') === 'amount_desc')>Nominal Tagihan Terbesar</option>
                        <option value="oldest"         @selected(request('sort') === 'oldest')>Terlama (Overdue Kritis)</option>
                    </select>
                </div>
                <a href="{{ route('receivables.index') }}" class="h-10 w-10 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface flex items-center justify-center" title="Reset">
                    <span class="material-symbols-outlined text-[20px]">refresh</span>
                </a>
            </div>
        </form>
    </div>

    {{-- ============ TABEL ============ --}}
    <div class="w-full overflow-hidden rounded-xl bg-surface-container-low shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">
                        <th class="py-space-md px-space-md font-bold">No. Faktur & Tgl</th>
                        <th class="py-space-md px-space-md font-bold">Pelanggan / Mitra Grosir</th>
                        <th class="py-space-md px-space-md font-bold">Detail Barang Keluar</th>
                        <th class="py-space-md px-space-md font-bold">Nilai Tagihan & Sisa</th>
                        <th class="py-space-md px-space-md font-bold">Jatuh Tempo & Status</th>
                        <th class="py-space-md px-space-md font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-highest/20 font-body-sm text-body-sm">
                    @forelse($transactions as $trx)
                        @php
                            $aging     = $trx->aging_status;
                            $days      = $trx->days_to_due;
                            $isPartial = $trx->paid_amount > 0 && $trx->remaining > 0;
                            $isLunas   = $trx->payment_status === 'lunas';
                            $isOverdue = $aging === 'overdue';
                            $termDays  = $trx->due_date
                                ? max(1, $trx->sold_at->startOfDay()->diffInDays($trx->due_date->startOfDay()))
                                : null;

                            $rowBg    = $isOverdue ? 'bg-error-container/10' : ($isPartial ? 'bg-surface-container-low/30' : '');
                            $skuColor = $isOverdue ? 'text-error' : 'text-primary';
                            $statusBadge = match ($aging) {
                                'overdue'  => ['bg' => 'bg-error text-on-error',       'text' => 'Terlambat ' . abs($days) . ' Hari (Overdue)',  'dot' => 'bg-error'],
                                'due_soon' => ['bg' => 'bg-tertiary text-on-tertiary', 'text' => 'Jatuh Tempo Hari Ini / Sisa ' . $days . ' Hari', 'dot' => 'bg-tertiary animate-ping'],
                                'warning'  => ['bg' => 'bg-tertiary/10 text-tertiary', 'text' => 'Sisa ' . $days . ' Hari (Warning)',            'dot' => 'bg-tertiary'],
                                'lunas'    => ['bg' => 'bg-primary/10 text-primary',   'text' => 'Lunas',                                       'dot' => 'bg-primary'],
                                default    => ['bg' => 'bg-primary/10 text-primary',   'text' => 'Sisa ' . $days . ' Hari (Lancar)',             'dot' => 'bg-primary'],
                            };

                            $paymentsPayload = $trx->payments->map(fn ($p) => [
                                'id'          => $p->id,
                                'paid_at'     => $p->paid_at?->format('d M Y, H:i') ?? '-',
                                'amount'      => (int) $p->amount,
                                'account'     => $p->account ?? $p->payment_method,
                                'reference'   => $p->reference_no ?? '-',
                                'notes'       => $p->notes ?? '-',
                                'destroy_url' => route('receivables.payments.destroy', $p),
                            ])->values();

                            $historyPayload = [
                                'no_invoice'  => $trx->no_invoice,
                                'customer'    => $trx->customer->name,
                                'subtotal'    => (int) $trx->subtotal,
                                'paid'        => (int) $trx->paid_amount,
                                'remaining'   => (int) $trx->remaining,
                                'receipt_url' => route('outgoing-goods.receipt', $trx),
                                'payments'    => $paymentsPayload,
                            ];
                        @endphp
                        <tr class="hover:bg-surface-container/60 transition-colors {{ $rowBg }}">
                            <td class="py-space-md px-space-md align-top">
                                <div class="flex flex-col">
                                    <span class="font-label-md text-label-md font-bold {{ $skuColor }}">{{ $trx->no_invoice }}</span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $trx->sold_at->format('d M Y, H:i') }}</span>
                                    @if($termDays)
                                        <span class="mt-1 inline-flex items-center gap-1 font-label-sm text-label-sm text-on-surface-variant/80">
                                            <span class="material-symbols-outlined text-[13px]">event_repeat</span> Termin {{ $termDays }} Hari
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-headline-sm text-body-md font-bold text-on-surface">{{ $trx->customer->name }}</span>
                                    <span class="font-body-sm text-body-sm text-on-surface-variant">
                                        PIC: {{ $trx->customer->pic_name ?? '—' }} • {{ $trx->customer->phone ?? '—' }}
                                    </span>
                                    <div class="flex items-center gap-space-xs mt-1 flex-wrap">
                                        <span class="px-2 py-0.5 rounded-full bg-primary/10 text-primary font-label-sm text-label-sm font-semibold">Plafon: {{ $fmtJt($trx->customer->credit_limit) }}</span>
                                        <span class="font-label-sm text-label-sm text-on-surface-variant">Sisa Kuota: {{ $fmtJt($trx->customer->available_credit) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <div class="flex flex-col gap-1 max-w-xs">
                                    <div class="flex items-center gap-space-xs">
                                        <span class="font-label-sm text-label-sm bg-secondary-container/30 text-secondary font-bold px-1.5 py-0.5 rounded">{{ $num($trx->box_qty) }} BOX</span>
                                        <span class="font-body-sm text-body-sm text-on-surface font-medium truncate">{{ $trx->product->name }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 font-label-sm text-label-sm text-on-surface-variant">
                                        <span>1 Box = {{ $trx->product->box_pcs_ratio }} Pcs</span>
                                        <span>•</span>
                                        <span class="text-primary font-semibold">Total {{ $num($trx->total_pcs) }} Pcs</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <div class="flex flex-col">
                                    <span class="font-label-md text-label-md font-extrabold text-on-surface">{{ $fmt($trx->subtotal) }}</span>
                                    <div class="flex items-center gap-1 font-label-sm text-label-sm {{ $trx->remaining > 0 ? 'text-error' : 'text-primary' }} mt-0.5 font-bold">
                                        <span>Sisa: {{ $fmt($trx->remaining) }}</span>
                                    </div>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant/70">Terbayar: {{ $fmt($trx->paid_amount) }} ({{ $trx->paid_pct }}%)</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full {{ $statusBadge['dot'] }}"></span>
                                        <span class="font-label-md text-label-md font-bold {{ $isOverdue ? 'text-error' : 'text-on-surface' }}">
                                            {{ $trx->due_date?->format('d M Y') ?? '—' }}
                                        </span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-md {{ $statusBadge['bg'] }} font-label-sm text-label-sm font-bold w-fit">
                                        {{ $statusBadge['text'] }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md align-top text-center">
                                <div class="flex items-center justify-center gap-space-xs">
                                    @if(! $isLunas)
                                        <button type="button"
                                                onclick="openPaymentModal({{ $trx->id }})"
                                                class="h-8 px-space-sm rounded-lg bg-primary-container text-on-primary-container hover:bg-primary font-headline-sm text-body-sm font-bold flex items-center gap-1 transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">payments</span>
                                            <span>Bayar</span>
                                        </button>
                                    @endif
                                    @if($trx->customer->whatsapp_number && ! $isLunas)
                                        <a href="https://wa.me/{{ $trx->customer->whatsapp_number }}?text={{ urlencode('Halo ' . ($trx->customer->pic_name ?? $trx->customer->name) . ', mengingatkan tagihan ' . $trx->no_invoice . ' sebesar Rp ' . number_format($trx->remaining, 0, ',', '.') . ' jatuh tempo ' . ($trx->due_date?->format('d M Y') ?? '-') . '. Terima kasih.') }}"
                                           target="_blank"
                                           class="h-8 w-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-primary flex items-center justify-center transition-colors" title="Kirim Pengingat WhatsApp">
                                            <span class="material-symbols-outlined text-[18px]">chat</span>
                                        </a>
                                    @endif
                                    <button type="button"
                                            onclick='openHistoryModal(@json($historyPayload))'
                                            class="h-8 w-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors relative" title="Riwayat Cicilan & Nota">
                                        <span class="material-symbols-outlined text-[18px]">history</span>
                                        @if($trx->payments->count() > 0)
                                            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-primary text-on-primary text-[10px] font-bold flex items-center justify-center">
                                                {{ $trx->payments->count() }}
                                            </span>
                                        @endif
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-space-xl text-center text-on-surface-variant">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="material-symbols-outlined text-[40px] text-surface-container-highest">receipt_long</span>
                                    <span>Tidak ada tagihan yang cocok dengan filter ini.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-space-md bg-surface-container flex flex-wrap items-center justify-between gap-space-sm">
            <div class="flex items-center gap-space-sm font-body-sm text-body-sm text-on-surface-variant">
                @if($transactions->total() > 0)
                    <span>Menampilkan <strong class="text-on-surface">{{ $transactions->firstItem() }}–{{ $transactions->lastItem() }}</strong> dari <strong class="text-on-surface">{{ $transactions->total() }}</strong> Faktur Piutang</span>
                    <span class="h-4 w-px bg-surface-container-highest"></span>
                    <span>Total di Halaman: <strong class="text-primary font-label-md">{{ $fmt($transactions->getCollection()->sum('remaining')) }}</strong></span>
                @else
                    <span>Belum ada data</span>
                @endif
            </div>
            <div>{{ $transactions->links() }}</div>
        </div>
    </div>

    {{-- ============ SOP BANNER ============ --}}
    <div class="mt-space-lg p-space-md rounded-xl bg-surface-container-low flex flex-col md:flex-row items-center justify-between gap-space-md shadow-sm">
        <div class="flex items-center gap-space-md">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary flex-shrink-0 shadow-[0_0_16px_rgba(16,185,129,0.25)]">
                <span class="material-symbols-outlined text-[28px]">verified_user</span>
            </div>
            <div class="flex flex-col">
                <span class="font-headline-sm text-body-md font-bold text-on-surface">Sistem Proteksi Piutang Terintegrasi SUPOS | Sinar Utama</span>
                <p class="font-body-sm text-body-sm text-on-surface-variant max-w-3xl leading-relaxed">
                    Setiap transaksi tempo yang tercatat dari menu <strong class="text-on-surface">Barang Keluar</strong> langsung membentuk saldo tagihan real-time. Begitu staf mencatat pelunasan di menu ini, sistem seketika memposting mutasi ke modul <strong class="text-primary">Kas Masuk</strong> tanpa entri manual ganda.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-space-sm flex-shrink-0">
            <div class="px-space-sm py-1 rounded-lg bg-surface-container flex items-center gap-1.5 font-label-sm">
                <span class="material-symbols-outlined text-primary text-[16px]">check</span>
                <span>Zero Double-Entry</span>
            </div>
            <div class="px-space-sm py-1 rounded-lg bg-surface-container flex items-center gap-1.5 font-label-sm">
                <span class="material-symbols-outlined text-primary text-[16px]">history_edu</span>
                <span>Audit Trail 100%</span>
            </div>
        </div>
    </div>
</div>

{{-- ============ MODALS PARTIAL ============ --}}
@include('receivables.partials.modals')

@endsection

@push('scripts')
<script>
(function () {
    const paymentModal = document.getElementById('paymentModal');
    const historyModal = document.getElementById('historyModal');
    const waModal      = document.getElementById('waReminderModal');
    const confirmModal = document.getElementById('confirmActionModal');

    const payForm      = document.getElementById('modalPayForm');
    const trxSelect    = document.getElementById('modalTrxSelect');
    const modalAmount  = document.getElementById('modalAmount');

    // ---- Modal Pelunasan (1 Pintu Terpusat) ----
    window.openPaymentModal = function (trxId = null, preserveOldAmount = false) {
        if (!paymentModal) return;
        if (trxId && trxSelect) {
            trxSelect.value = String(trxId);
        }
        updateModalAction(preserveOldAmount);
        paymentModal.classList.remove('hidden');
        paymentModal.classList.add('flex');
    };
    window.closePaymentModal = function () {
        if (!paymentModal) return;
        paymentModal.classList.add('hidden');
        paymentModal.classList.remove('flex');
    };
    window.updateModalAction = function (preserveOldAmount = false) {
        if (!trxSelect || trxSelect.options.length === 0) return;
        const opt = trxSelect.options[trxSelect.selectedIndex];
        payForm.action  = opt.dataset.action;
        modalAmount.max = opt.dataset.remaining;
        if (!preserveOldAmount) {
            modalAmount.value = opt.dataset.remaining;
        }
        const remainingBadge = document.getElementById('modalRemainingInfo');
        if (remainingBadge) {
            remainingBadge.textContent = 'Rp ' + Number(opt.dataset.remaining || 0).toLocaleString('id-ID');
        }
    };
    window.setModalPayRatio = function (ratio) {
        if (!trxSelect || trxSelect.options.length === 0) return;
        const opt = trxSelect.options[trxSelect.selectedIndex];
        const rem = Number(opt.dataset.remaining || 0);
        modalAmount.value = Math.max(1, Math.round(rem * ratio));
    };

    // ---- Modal Riwayat Cicilan ----
    window.openHistoryModal = function (data) {
        if (!historyModal) return;
        const fmt = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');

        document.getElementById('histInvoice').textContent   = data.no_invoice;
        document.getElementById('histCustomer').textContent  = data.customer;
        document.getElementById('histSubtotal').textContent  = fmt(data.subtotal);
        document.getElementById('histPaid').textContent      = fmt(data.paid);
        document.getElementById('histRemaining').textContent = fmt(data.remaining);
        document.getElementById('histReceiptLink').href      = data.receipt_url;

        const tbody = document.getElementById('histPaymentsBody');
        tbody.innerHTML = '';

        if (!data.payments || data.payments.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="py-6 text-center text-on-surface-variant font-body-sm">Belum ada riwayat cicilan / pembayaran untuk faktur ini.</td></tr>`;
        } else {
            data.payments.forEach((p, idx) => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-surface-container/60';
                tr.innerHTML = `
                    <td class="py-2.5 px-3 font-label-sm">${idx + 1}</td>
                    <td class="py-2.5 px-3 font-body-sm">${p.paid_at}</td>
                    <td class="py-2.5 px-3 font-label-md font-bold text-primary">${fmt(p.amount)}</td>
                    <td class="py-2.5 px-3 font-body-sm">
                        <div>${p.account}</div>
                        <div class="text-label-sm text-on-surface-variant">Ref: ${p.reference}</div>
                    </td>
                    <td class="py-2.5 px-3 text-right">
                        <form action="${p.destroy_url}" method="POST" id="delPayForm_${p.id}" class="inline">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="button"
                                onclick="openConfirmModal({
                                    title: 'Batalkan Cicilan Pembayaran?',
                                    message: 'Pembayaran sebesar ${fmt(p.amount)} pada ${data.no_invoice} akan dihapus dan mutasi Kas Masuk terkait otomatis dibatalkan.',
                                    confirmText: 'Ya, Batalkan Cicilan',
                                    variant: 'danger',
                                    onConfirm: () => document.getElementById('delPayForm_${p.id}').submit()
                                })"
                                class="h-7 px-2.5 rounded-lg bg-error/15 hover:bg-error text-error hover:text-on-error font-label-sm font-bold transition-colors">
                                Batal
                            </button>
                        </form>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        historyModal.classList.remove('hidden');
        historyModal.classList.add('flex');
    };
    window.closeHistoryModal = function () {
        if (!historyModal) return;
        historyModal.classList.add('hidden');
        historyModal.classList.remove('flex');
    };

    // ---- Modal Pengingat WA ----
    window.openWaReminderModal = function () {
        if (!waModal) return;
        waModal.classList.remove('hidden');
        waModal.classList.add('flex');
    };
    window.closeWaReminderModal = function () {
        if (!waModal) return;
        waModal.classList.add('hidden');
        waModal.classList.remove('flex');
    };
    window.copyWaText = function (btn) {
        const text = btn.dataset.waText || '';
        navigator.clipboard?.writeText(text);
        const orig = btn.innerHTML;
        btn.innerHTML = '<span class="material-symbols-outlined text-[16px]">check</span><span>Tersalin</span>';
        setTimeout(() => { btn.innerHTML = orig; }, 1800);
    };

    // ---- Modal Konfirmasi Universal ----
    let pendingConfirmCallback = null;
    window.openConfirmModal = function ({ title, message, confirmText = 'Ya, Lanjutkan', variant = 'primary', onConfirm }) {
        document.getElementById('confirmTitle').textContent   = title;
        document.getElementById('confirmMessage').textContent = message;
        const btn = document.getElementById('confirmSubmitBtn');
        btn.textContent = confirmText;

        if (variant === 'danger') {
            btn.className = 'h-10 px-space-lg rounded-xl bg-error hover:bg-error/90 text-on-error font-headline-sm text-body-sm font-bold transition-colors';
        } else {
            btn.className = 'h-10 px-space-lg rounded-xl bg-primary-container hover:bg-primary text-on-primary-container font-headline-sm text-body-sm font-bold transition-colors';
        }

        pendingConfirmCallback = onConfirm;
        confirmModal.classList.remove('hidden');
        confirmModal.classList.add('flex');
    };
    window.closeConfirmModal = function () {
        confirmModal.classList.add('hidden');
        confirmModal.classList.remove('flex');
        pendingConfirmCallback = null;
    };
    document.getElementById('confirmSubmitBtn')?.addEventListener('click', () => {
        if (typeof pendingConfirmCallback === 'function') {
            pendingConfirmCallback();
        }
        closeConfirmModal();
    });

    window.confirmModalPayment = function () {
        if (!trxSelect || trxSelect.options.length === 0) return;
        const opt = trxSelect.options[trxSelect.selectedIndex];
        const amt = Number(modalAmount.value || 0);
        if (amt <= 0) return;

        openConfirmModal({
            title: 'Simpan & Posting ke Kas Masuk',
            message: `Terbitkan kwitansi pelunasan sebesar Rp ${amt.toLocaleString('id-ID')} untuk ${opt.text.trim()}?`,
            confirmText: 'Simpan & Posting',
            variant: 'primary',
            onConfirm: () => payForm.submit()
        });
    };

    [paymentModal, historyModal, waModal, confirmModal].forEach((m) => {
        m?.addEventListener('click', (e) => {
            if (e.target === m) {
                m.classList.add('hidden');
                m.classList.remove('flex');
            }
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (confirmModal && !confirmModal.classList.contains('hidden')) return closeConfirmModal();
        if (paymentModal && !paymentModal.classList.contains('hidden')) return closePaymentModal();
        if (historyModal && !historyModal.classList.contains('hidden')) return closeHistoryModal();
        if (waModal && !waModal.classList.contains('hidden')) return closeWaReminderModal();
    });

    let filterTimer;
    window.debouncedFilter = () => {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
    };

    @if($errors->any())
        openPaymentModal({{ session('failed_trx_id') ?? 'null' }}, true);
    @endif
})();
</script>
@endpush