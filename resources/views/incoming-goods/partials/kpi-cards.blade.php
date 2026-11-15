<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-md mb-space-lg">
    {{-- KPI 1 --}}
    <div class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-md flex flex-col justify-between hover:bg-surface-container transition-all group border border-transparent hover:border-primary/20">
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
    <div class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-md flex flex-col justify-between hover:bg-surface-container transition-all group border border-transparent hover:border-primary/20">
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
            <span class="font-label-md text-label-md text-on-surface-variant">Pembelian Tunai / Lunas</span>
            <span class="font-label-sm text-label-sm text-primary font-bold">{{ $kpi['lunas_count'] }} Faktur</span>
        </div>
    </div>

    {{-- KPI 3 --}}
    <div class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-md flex flex-col justify-between hover:bg-surface-container transition-all group relative overflow-hidden border border-transparent hover:border-error/25">
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
            <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $kpi['tempo_soon'] }} Faktur ≤ 7 Hari</span>
        </div>
    </div>

    {{-- KPI 4 --}}
    <div class="p-space-md rounded-xl bg-surface-container-low/90 backdrop-blur-md shadow-md flex flex-col justify-between hover:bg-surface-container transition-all group border border-transparent hover:border-tertiary/20">
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