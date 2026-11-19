@php
    $isFilteredMonth = !empty($selectedMonth);
    $badgePeriode = $isFilteredMonth ? 'PERIODE ' . strtoupper($kpi['period_label']) : 'SEMUA PERIODE';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-md mb-space-lg">
    {{-- 1. Total Belanja --}}
    <div
        class="p-space-md rounded-2xl bg-surface-container-low border border-outline-variant/15 shadow-sm flex flex-col justify-between gap-space-sm">
        <div class="flex items-start justify-between gap-2">
            <div class="flex flex-col">
                <span class="font-label-sm text-[11px] text-on-surface-variant uppercase tracking-wider">
                    Total Belanja ({{ $badgePeriode }})
                </span>
                <span class="font-headline-lg text-headline-lg text-on-surface font-extrabold mt-1">
                    {{ $fmtJt($kpi['month_total']) }}
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-primary/15 text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
            </div>
        </div>
        <div class="flex items-center justify-between pt-2 border-t border-surface-container text-body-sm">
            <span class="text-on-surface-variant flex items-center gap-1">
                <span class="material-symbols-outlined text-[15px] text-primary">inventory_2</span>
                <strong>{{ $num($kpi['month_box']) }} Box</strong> / {{ $num($kpi['month_pcs']) }} Pcs
            </span>
            <span class="px-2 py-0.5 rounded-md bg-surface-container text-on-surface font-semibold text-xs">
                {{ $kpi['month_count'] }} Faktur
            </span>
        </div>
    </div>

    {{-- 2. Transaksi Lunas --}}
    <div
        class="p-space-md rounded-2xl bg-surface-container-low border border-outline-variant/15 shadow-sm flex flex-col justify-between gap-space-sm">
        <div class="flex items-start justify-between gap-2">
            <div class="flex flex-col">
                <span class="font-label-sm text-[11px] text-on-surface-variant uppercase tracking-wider">
                    Transaksi Lunas ({{ $badgePeriode }})
                </span>
                <span class="font-headline-lg text-headline-lg text-primary font-extrabold mt-1">
                    {{ $fmtJt($kpi['lunas_total']) }}
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-primary/15 text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[20px]">payments</span>
            </div>
        </div>
        <div class="flex items-center justify-between pt-2 border-t border-surface-container text-body-sm">
            <span class="text-on-surface-variant">Pembelian Tunai / Lunas</span>
            <span class="text-primary font-bold text-xs">{{ $kpi['lunas_count'] }} Faktur</span>
        </div>
    </div>

    {{-- 3. Tempo Belum Lunas (Seluruh Periode) --}}
    <div
        class="p-space-md rounded-2xl bg-error/5 border border-error/25 shadow-sm flex flex-col justify-between gap-space-sm">
        <div class="flex items-start justify-between gap-2">
            <div class="flex flex-col">
                <span
                    class="font-label-sm text-[11px] text-error uppercase tracking-wider flex items-center gap-1.5 font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                    Tempo Belum Lunas (Semua Periode)
                </span>
                <span class="font-headline-lg text-headline-lg text-on-surface font-extrabold mt-1">
                    {{ $fmtJt($kpi['tempo_total']) }}
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-error/15 text-error flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[20px]">event_busy</span>
            </div>
        </div>
        <div class="flex items-center justify-between pt-2 border-t border-error/15 text-body-sm">
            <span class="text-error font-bold text-xs">{{ $kpi['tempo_count'] }} Faktur Tempo Aktif</span>
            <span class="text-on-surface-variant text-xs">{{ $kpi['tempo_soon'] }} Faktur ≤ 7 Hari</span>
        </div>
    </div>

    {{-- 4. Update Acuan Modal --}}
    <div
        class="p-space-md rounded-2xl bg-surface-container-low border border-outline-variant/15 shadow-sm flex flex-col justify-between gap-space-sm">
        <div class="flex items-start justify-between gap-2">
            <div class="flex flex-col">
                <span class="font-label-sm text-[11px] text-on-surface-variant uppercase tracking-wider">
                    Update Acuan Modal ({{ $badgePeriode }})
                </span>
                <span class="font-headline-lg text-headline-lg text-on-surface font-extrabold mt-1">
                    {{ $kpi['sku_updated'] }} SKU
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-tertiary/15 text-tertiary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[20px]">price_change</span>
            </div>
        </div>
        <div class="flex items-center justify-between pt-2 border-t border-surface-container text-body-sm gap-2">
            <span class="text-on-surface-variant text-xs truncate"
                title="{{ $kpi['sku_names'] ?: 'Belum ada transaksi periode ini' }}">
                {{ $kpi['sku_names'] ?: 'Belum ada transaksi periode ini' }}
            </span>
            <span class="text-tertiary font-bold text-[11px] uppercase whitespace-nowrap">HPP Terkunci</span>
        </div>
    </div>
</div>