{{-- ============ 3 KARTU AKUN PENAMPUNG (KLIK UNTUK FILTER) ============ --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
    @foreach ($acctStyles as $key => $meta)
        @php
            $balance = $accountBalances[$key] ?? 0;
            $monthData = $accountBreakdown[$key] ?? ['total' => 0, 'count' => 0];
            $isSelected = $acc === $key;
        @endphp
        <a href="{{ route('cash-ins.index', array_merge(request()->except('page'), ['account' => $isSelected ? 'all' : $key])) }}"
            title="{{ $isSelected ? 'Klik untuk menampilkan semua akun' : 'Klik untuk memfilter mutasi ' . $meta['label'] }}"
            class="group rounded-xl bg-surface-container-low p-space-md flex flex-col justify-between hover:bg-surface-container transition-all shadow-sm border {{ $isSelected ? 'border-primary ring-2 ring-primary/30 bg-surface-container' : 'border-transparent' }}">
            <div class="flex items-center justify-between gap-space-sm">
                <div class="flex items-center gap-space-md">
                    <div
                        class="w-12 h-12 rounded-xl {{ $meta['box'] }} flex items-center justify-center font-bold font-mono text-base uppercase shrink-0">
                        {{ $meta['short'] }}
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span
                                class="font-headline-sm text-body-md font-bold text-on-surface">{{ $meta['label'] }}</span>
                            <span
                                class="px-2 py-0.5 rounded-full {{ $meta['badge'] }} font-label-sm text-label-sm uppercase font-bold">{{ $key === 'kas_tunai' ? 'Tunai Toko' : 'Transfer Bank' }}</span>
                        </div>
                        <span class="font-body-sm text-body-sm text-on-surface-variant">
                            {{ $isSelected ? 'Sedang menampilkan mutasi akun ini' : 'Klik kartu untuk filter mutasi akun' }}
                        </span>
                    </div>
                </div>
                @if ($isSelected)
                    <span
                        class="px-2 py-1 rounded-lg bg-primary text-on-primary font-label-sm text-label-sm font-bold flex items-center gap-1 shrink-0">
                        <span class="material-symbols-outlined text-[14px]">filter_alt</span>
                        Aktif
                    </span>
                @else
                    <span
                        class="material-symbols-outlined text-[18px] text-on-surface-variant/40 group-hover:text-primary transition-colors shrink-0">
                        filter_list
                    </span>
                @endif
            </div>
            <div
                class="mt-space-md pt-space-sm border-t border-surface-container-highest/30 flex items-end justify-between gap-2">
                <div class="flex flex-col">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Akumulasi Kas
                        Masuk</span>
                    <span
                        class="font-headline-md text-headline-md font-extrabold text-on-surface font-mono">{{ $fmt($balance) }}</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Total dana masuk tercatat</span>
                </div>
                <div class="text-right">
                    <span class="flex items-center justify-end gap-1 font-body-sm text-body-sm text-primary font-bold">
                        <span class="material-symbols-outlined text-[16px]">arrow_downward</span>
                        +{{ $fmtJt($monthData['total']) }}
                    </span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $monthData['count'] }}
                        Transaksi bln ini</span>
                </div>
            </div>
        </a>
    @endforeach
</div>