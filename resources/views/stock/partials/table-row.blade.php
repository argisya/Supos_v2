@php
    $ratio     = max(1, (int) $p->box_pcs_ratio);
    $threshold = max(1, (int) ($p->min_stock_box ?? 10));
    $boxUtuh   = intdiv((int) $p->stock_pcs, $ratio);
    $sisaPcs   = (int) $p->stock_pcs % $ratio;
    $boxFloat  = $p->stock_pcs / $ratio;
    $hppBox    = (float) ($p->last_price_box ?? 0);
    $assetVal  = (int) round($boxFloat * $hppBox);

    $isHabis   = (int) $p->stock_pcs <= 0;
    $isMenipis = ! $isHabis && ($boxFloat <= $threshold);
@endphp

<tr class="hover:bg-surface-container/60 transition-colors">
    <td class="py-3.5 px-space-md">
        <div class="flex flex-col">
            <span class="font-headline-sm text-body-md text-on-surface font-bold">{{ $p->name }}</span>
            <div class="flex items-center gap-2 mt-0.5 text-label-sm text-on-surface-variant">
                <span class="font-mono text-primary">{{ $p->sku }}</span>
                @if($p->category)
                    <span>•</span>
                    <span>{{ $p->category->name }}</span>
                @endif
            </div>
        </div>
    </td>

    <td class="py-3.5 px-space-md text-center">
        <span class="px-2.5 py-1 rounded-lg bg-surface-container text-on-surface-variant text-label-sm font-label-sm">
            1 Box = {{ $ratio }} Pcs
        </span>
    </td>

    <td class="py-3.5 px-space-md text-right stock-cell"
        data-box="{{ $boxUtuh }}"
        data-rem="{{ $sisaPcs }}"
        data-total-pcs="{{ (int) $p->stock_pcs }}">
        <div class="unit-view-dual">
            <span class="font-headline-sm text-body-md font-extrabold {{ $isHabis ? 'text-error' : ($isMenipis ? 'text-tertiary' : 'text-on-surface') }}">
                {{ $num($boxUtuh) }} Box
            </span>
            @if($sisaPcs > 0)
                <span class="text-primary font-semibold">+ {{ $num($sisaPcs) }} Pcs</span>
            @endif
            <div class="text-[11px] text-on-surface-variant">Total: {{ $num($p->stock_pcs) }} Pcs</div>
        </div>
        <div class="unit-view-box hidden font-headline-sm text-body-md font-extrabold text-on-surface">
            {{ number_format($boxFloat, $sisaPcs > 0 ? 1 : 0, ',', '.') }} Box
        </div>
        <div class="unit-view-pcs hidden font-headline-sm text-body-md font-extrabold text-primary">
            {{ $num($p->stock_pcs) }} Pcs
        </div>
    </td>

    <td class="py-3.5 px-space-md text-center">
        <button type="button"
                data-action="open-alert"
                data-product-id="{{ $p->id }}"
                data-threshold="{{ $threshold }}"
                class="px-2.5 py-1 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-tertiary text-label-sm font-label-sm transition-colors cursor-pointer"
                title="Klik untuk ubah batas alert produk ini">
            ≤ {{ $num($threshold) }} Box
        </button>
    </td>

    <td class="py-3.5 px-space-md text-right font-semibold text-on-surface">
        {{ $hppBox > 0 ? $fmt($hppBox) : '—' }}
    </td>

    <td class="py-3.5 px-space-md text-right font-headline-sm font-extrabold text-primary">
        {{ $fmt($assetVal) }}
    </td>

    <td class="py-3.5 px-space-md text-center">
        @if($isHabis)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-error/15 text-error text-label-sm font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-error animate-pulse"></span> HABIS
            </span>
        @elseif($isMenipis)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-tertiary/15 text-tertiary text-label-sm font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-tertiary"></span> MENIPIS
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/15 text-primary text-label-sm font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span> AMAN
            </span>
        @endif
    </td>

    <td class="py-3.5 px-space-md">
        <div class="flex items-center justify-center gap-1.5 flex-wrap">
            {{-- Tombol Riwayat Mutasi --}}
            <button type="button"
                    data-action="open-mutation"
                    data-url="{{ route('stock.mutations', $p) }}"
                    class="h-8 px-2.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface text-label-sm flex items-center gap-1 transition-colors cursor-pointer"
                    title="Lihat Kartu Stok / Riwayat Mutasi">
                <span class="material-symbols-outlined text-[16px]">history</span>
                <span>Mutasi</span>
            </button>

            {{-- Tombol Opname Per Baris --}}
            <button type="button"
                    data-action="open-opname"
                    data-product-id="{{ $p->id }}"
                    data-box="{{ $boxUtuh }}"
                    data-rem="{{ $sisaPcs }}"
                    class="h-8 px-2.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-primary text-label-sm flex items-center gap-1 transition-colors cursor-pointer"
                    title="Sesuaikan Stok Fisik (Opname)">
                <span class="material-symbols-outlined text-[16px]">fact_check</span>
                <span>Opname</span>
            </button>

            {{-- Tombol Restock ke Barang Masuk --}}
            @if($isHabis)
                <a href="{{ route('incoming-goods.index', ['product_id' => $p->id]) }}"
                   class="h-8 px-3 rounded-lg bg-error text-on-error hover:opacity-90 text-label-sm font-bold flex items-center gap-1 shadow-sm transition-all">
                    <span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>
                    <span>+ Restock Segera</span>
                </a>
            @elseif($isMenipis)
                <a href="{{ route('incoming-goods.index', ['product_id' => $p->id]) }}"
                   class="h-8 px-3 rounded-lg bg-tertiary/20 text-tertiary hover:bg-tertiary/30 text-label-sm font-bold flex items-center gap-1 transition-all">
                    <span class="material-symbols-outlined text-[16px]">add_box</span>
                    <span>+ Kulakan</span>
                </a>
            @else
                <a href="{{ route('incoming-goods.index', ['product_id' => $p->id]) }}"
                   class="h-8 px-2.5 rounded-lg bg-surface-container hover:bg-primary/20 text-on-surface-variant hover:text-primary text-label-sm flex items-center gap-1 transition-colors"
                   title="Tambah Faktur Masuk untuk barang ini">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    <span>Kulakan</span>
                </a>
            @endif
        </div>
    </td>
</tr>