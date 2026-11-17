{{-- ============ RECONCILIATION BANNER ============ --}}
<div class="relative overflow-hidden rounded-xl bg-surface-container-low p-space-md shadow-md">
    <div class="absolute inset-0 bg-linear-to-r from-primary/10 via-tertiary/5 to-transparent pointer-events-none"></div>
    <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-space-md">
        <div class="flex items-center gap-space-md">
            <div class="w-12 h-12 rounded-xl {{ $reconOK ? 'bg-primary-container/20 text-primary' : 'bg-error/20 text-error' }} flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' 1;">{{ $reconOK ? 'account_balance' : 'warning' }}</span>
            </div>
            <div class="flex flex-col">
                <div class="flex items-center gap-space-xs flex-wrap">
                    <span class="font-headline-sm text-body-md font-bold text-on-surface">
                        Pemeriksaan Total Jurnal Bulan Ini: {{ $reconOK ? 'Total Cocok' : 'Ada Selisih' }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full {{ $reconOK ? 'bg-primary/20 text-primary' : 'bg-error/20 text-error' }} font-label-sm text-label-sm uppercase font-bold tracking-wider">
                        {{ $reconOK ? 'Total Sesuai' : 'Perlu Diperiksa' }}
                    </span>
                </div>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                    Penjualan tunai tercatat <strong class="text-on-surface font-semibold">{{ $fmtJt($cashInFromSales) }}</strong> dan pelunasan piutang tercatat <strong class="text-on-surface font-semibold">{{ $fmtJt($cashInFromReceivable) }}</strong> dibandingkan sumber transaksi asal.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-space-sm self-end md:self-center shrink-0">
            <span class="font-label-sm text-label-sm text-on-surface-variant font-mono">Σ Sumber: {{ $fmtJt($reconTotal) }} | Σ Kas In: {{ $fmtJt($reconCashIn) }}</span>
        </div>
    </div>
</div>

{{-- ============ 3 KARTU AKUN PENAMPUNG ============ --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
    @foreach($acctStyles as $key => $meta)
        @php
            $balance   = $accountBalances[$key] ?? 0;
            $monthData = $accountBreakdown[$key] ?? ['total' => 0, 'count' => 0];
        @endphp
        <div class="rounded-xl bg-surface-container-low p-space-md flex flex-col justify-between hover:bg-surface-container transition-all shadow-sm">
            <div class="flex items-center justify-between gap-space-sm">
                <div class="flex items-center gap-space-md">
                    <div class="w-12 h-12 rounded-xl {{ $meta['box'] }} flex items-center justify-center font-bold font-mono text-base uppercase shrink-0">
                        {{ $meta['short'] }}
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-headline-sm text-body-md font-bold text-on-surface">{{ $meta['label'] }}</span>
                            <span class="px-2 py-0.5 rounded-full {{ $meta['badge'] }} font-label-sm text-label-sm uppercase font-bold">{{ $key === 'kas_tunai' ? 'Tunai Toko' : 'Transfer Hub' }}</span>
                        </div>
                        <span class="font-mono font-body-sm text-body-sm text-on-surface-variant">Akun Internal SUPOS | Sinar Utama</span>
                    </div>
                </div>
            </div>
            <div class="mt-space-md pt-space-sm border-t border-surface-container-highest/30 flex items-end justify-between gap-2">
                <div class="flex flex-col">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Net Mutasi Tercatat</span>
                    <span class="font-headline-md text-headline-md font-extrabold text-on-surface font-mono">{{ $fmt($balance) }}</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Belum termasuk saldo awal rekening</span>
                </div>
                <div class="text-right">
                    <span class="flex items-center justify-end gap-1 font-body-sm text-body-sm text-primary font-bold">
                        <span class="material-symbols-outlined text-[16px]">arrow_downward</span>
                        +{{ $fmtJt($monthData['total']) }}
                    </span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $monthData['count'] }} Transaksi bln ini</span>
                </div>
            </div>
        </div>
    @endforeach
</div>