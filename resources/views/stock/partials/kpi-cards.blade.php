<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-space-md mb-space-lg">
    {{-- Card 1: Estimasi Nilai Aset --}}
    <div class="p-space-md rounded-2xl bg-surface-container-low/90 border border-outline-variant/15 shadow-lg flex flex-col justify-between gap-space-sm">
        <div class="flex items-center justify-between">
            <span class="font-label-md text-label-md text-on-surface-variant uppercase">Estimasi Nilai Aset Gudang</span>
            <div class="w-9 h-9 rounded-xl bg-primary/15 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
            </div>
        </div>
        <div>
            <div class="font-headline-lg text-headline-lg text-on-surface font-black">{{ $fmtJt($kpi['total_asset']) }}</div>
            <div class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">{{ $fmt($kpi['total_asset']) }} (Basis HPP Terakhir)</div>
        </div>
    </div>

    {{-- Card 2: Total Volume Fisik --}}
    <div class="p-space-md rounded-2xl bg-surface-container-low/90 border border-outline-variant/15 shadow-lg flex flex-col justify-between gap-space-sm">
        <div class="flex items-center justify-between">
            <span class="font-label-md text-label-md text-on-surface-variant uppercase">Total Volume Fisik</span>
            <div class="w-9 h-9 rounded-xl bg-tertiary/15 text-tertiary flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">warehouse</span>
            </div>
        </div>
        <div>
            <div class="font-headline-lg text-headline-lg text-on-surface font-black">{{ $num($kpi['total_volume_box']) }} <span class="text-body-md font-semibold text-on-surface-variant">Box Utuh</span></div>
            <div class="font-body-sm text-body-sm text-tertiary font-semibold mt-0.5">Setara {{ $num($kpi['total_volume_pcs']) }} Pcs total di rak</div>
        </div>
    </div>

    {{-- Card 3: Kesehatan Inventori --}}
    <div class="p-space-md rounded-2xl bg-surface-container-low/90 border border-outline-variant/15 shadow-lg flex flex-col justify-between gap-space-sm">
        <div class="flex items-center justify-between">
            <span class="font-label-md text-label-md text-on-surface-variant uppercase">Kesehatan Inventori ({{ $totalSku }} SKU)</span>
            <span class="px-2 py-0.5 rounded-full bg-primary/15 text-primary text-label-sm font-bold">{{ $availPct }}% Ready</span>
        </div>
        <div class="flex flex-col gap-1.5">
            <div class="w-full h-2 rounded-full bg-surface-container-highest overflow-hidden flex">
                <div class="bg-primary h-full" style="width: {{ $amanPct }}%"></div>
                <div class="bg-tertiary h-full" style="width: {{ $menipisPct }}%"></div>
                <div class="bg-error h-full" style="width: {{ $habisPct }}%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] font-label-sm">
                <span class="text-primary">● Aman: {{ $kpi['aman'] }}</span>
                <span class="text-tertiary">● Menipis: {{ $kpi['menipis'] }}</span>
                <span class="text-error">● Habis: {{ $kpi['habis'] }}</span>
            </div>
        </div>
    </div>

    {{-- Card 4: Pergerakan Hari Ini --}}
    <div class="p-space-md rounded-2xl bg-surface-container-low/90 border border-outline-variant/15 shadow-lg flex flex-col justify-between gap-space-sm">
        <div class="flex items-center justify-between">
            <span class="font-label-md text-label-md text-on-surface-variant uppercase">Arus Stok Hari Ini</span>
            <span class="px-2 py-0.5 rounded-md bg-surface-container-high text-on-surface-variant text-[10px] font-label-sm">
                Net: {{ $netDelta >= 0 ? '+' . $num($netDelta) : $num($netDelta) }} Box
            </span>
        </div>
        <div class="flex items-center justify-between gap-2">
            <div>
                <div class="text-[11px] text-on-surface-variant">Masuk Gudang</div>
                <div class="font-headline-sm text-headline-sm text-primary font-extrabold">+{{ $num($kpi['today_in_box']) }} Box</div>
            </div>
            <div class="h-8 w-px bg-surface-container-highest"></div>
            <div class="text-right">
                <div class="text-[11px] text-on-surface-variant">Keluar / Terjual</div>
                <div class="font-headline-sm text-headline-sm text-error font-extrabold">-{{ $num($kpi['today_out_box']) }} Box</div>
            </div>
        </div>
    </div>
</div>