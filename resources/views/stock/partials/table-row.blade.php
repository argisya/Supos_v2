@php
    $ratio = max(1, (int) $p->box_pcs_ratio);
    $threshold = max(1, (int) ($p->min_stock_box ?? 10));
    $boxUtuh = intdiv((int) $p->stock_pcs, $ratio);
    $sisaPcs = (int) $p->stock_pcs % $ratio;
    $boxFloat = $p->stock_pcs / $ratio;
    $boxText = number_format($boxFloat, $sisaPcs > 0 ? 1 : 0, ',', '.');
    $hppBox = (float) ($p->last_price_box ?? 0);
    $assetVal = (int) round($boxFloat * $hppBox);

    $isHabis = (int) $p->stock_pcs <= 0;
    $isMenipis = !$isHabis && $boxFloat <= $threshold;

    $stockColor = $isHabis ? 'text-error' : ($isMenipis ? 'text-tertiary' : 'text-on-surface');
    $pcsColor = $isHabis ? 'text-error' : ($isMenipis ? 'text-tertiary' : 'text-primary');
@endphp

<tr class="hover:bg-surface-container/50 transition-colors">
    {{-- Kolom 1: SKU & Nama Barang --}}
    <td class="py-3.5 px-space-md">
        <div class="flex flex-col">
            <span class="font-headline-sm text-body-md text-on-surface font-bold leading-snug">{{ $p->name }}</span>
            <div class="flex items-center gap-1.5 mt-0.5 text-label-sm text-on-surface-variant flex-wrap">
                <span class="font-mono text-primary font-semibold whitespace-nowrap">{{ $p->sku }}</span>
                @if ($p->category)
                    <span>•</span>
                    <span>{{ $p->category->name }}</span>
                @endif
            </div>
        </div>
    </td>

    {{-- Kolom 2: Konversi --}}
    <td class="py-3.5 px-space-sm text-center">
        <span
            class="inline-block px-2 py-1 rounded-lg bg-surface-container text-on-surface-variant text-label-sm font-label-sm whitespace-nowrap">
            1 Box = {{ $ratio }} Pcs
        </span>
    </td>

    {{-- Kolom 3: Sisa Stok Realtime + Tombol Batas Alert --}}
    <td class="py-3.5 px-space-sm text-right stock-cell">
        {{-- Mode 1: Box + Pcs (Default) --}}
        <div class="unit-view-dual">
            <span class="font-headline-sm text-body-md font-extrabold {{ $stockColor }}">
                {{ $num($boxUtuh) }} Box
            </span>
            @if ($sisaPcs > 0)
                <span class="text-primary font-semibold text-label-sm">+ {{ $num($sisaPcs) }} Pcs</span>
            @endif
        </div>

        {{-- Mode 2: Hanya Box --}}
        <div class="unit-view-box hidden font-headline-sm text-body-md font-extrabold {{ $stockColor }}">
            {{ $boxText }} Box
        </div>

        {{-- Mode 3: Hanya Pcs --}}
        <div class="unit-view-pcs hidden font-headline-sm text-body-md font-extrabold {{ $pcsColor }}">
            {{ $num($p->stock_pcs) }} Pcs
        </div>

        {{-- Sub-baris Info Konversi & Batas Alert (beradaptasi sesuai mode satuan) --}}
        <div class="flex items-center justify-end gap-1.5 mt-0.5 text-[11px] text-on-surface-variant">
            <span class="unit-sub-dual">Total: {{ $num($p->stock_pcs) }} Pcs</span>
            <span class="unit-sub-pcs hidden">Setara {{ $boxText }} Box</span>
            <span class="unit-sub-sep">•</span>
            <button type="button" data-action="open-alert" data-product-id="{{ $p->id }}"
                data-threshold="{{ $threshold }}"
                class="text-on-surface-variant hover:text-tertiary underline decoration-dotted cursor-pointer whitespace-nowrap"
                title="Klik untuk ubah batas alert produk ini">
                Min {{ $num($threshold) }} Box
            </button>
        </div>
    </td>

    {{-- Kolom 4: HPP Terakhir / Box --}}
    <td class="py-3.5 px-space-sm text-right whitespace-nowrap font-semibold text-on-surface">
        {{ $hppBox > 0 ? $fmt($hppBox) : '—' }}
    </td>

    {{-- Kolom 5: Estimasi Nilai Aset --}}
    <td class="py-3.5 px-space-sm text-right whitespace-nowrap font-headline-sm font-extrabold text-primary">
        {{ $fmt($assetVal) }}
    </td>

    {{-- Kolom 6: Status --}}
    <td class="py-3.5 px-space-sm text-center whitespace-nowrap">
        @if ($isHabis)
            <span
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-error/15 text-error text-label-sm font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-error animate-pulse"></span> HABIS
            </span>
        @elseif($isMenipis)
            <span
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-tertiary/15 text-tertiary text-label-sm font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-tertiary"></span> MENIPIS
            </span>
        @else
            <span
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/15 text-primary text-label-sm font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span> AMAN
            </span>
        @endif
    </td>

    {{-- Kolom 7: Aksi Cepat --}}
    <td class="py-3.5 px-space-md whitespace-nowrap">
        <div class="flex items-center justify-center gap-1.5">
            <button type="button" data-action="open-mutation" data-url="{{ route('stock.mutations', $p) }}"
                class="h-8 px-2.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface text-label-sm flex items-center gap-1 transition-colors cursor-pointer"
                title="Lihat Kartu Stok / Riwayat Mutasi">
                <span class="material-symbols-outlined text-[16px]">history</span>
                <span>Mutasi</span>
            </button>

            @if ($isHabis)
                <a href="{{ route('incoming-goods.index', ['product_id' => $p->id]) }}"
                    class="h-8 px-2.5 rounded-lg bg-error text-on-error hover:opacity-90 text-label-sm font-bold flex items-center gap-1 shadow-xs transition-all">
                    <span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>
                    <span>Restock</span>
                </a>
            @elseif($isMenipis)
                <a href="{{ route('incoming-goods.index', ['product_id' => $p->id]) }}"
                    class="h-8 px-2.5 rounded-lg bg-tertiary/20 text-tertiary hover:bg-tertiary/30 text-label-sm font-bold flex items-center gap-1 transition-all">
                    <span class="material-symbols-outlined text-[16px]">add_box</span>
                    <span>Kulakan</span>
                </a>
            @else
                <a href="{{ route('incoming-goods.index', ['product_id' => $p->id]) }}"
                    class="h-8 px-2.5 rounded-lg bg-surface-container hover:bg-primary/20 text-on-surface-variant hover:text-primary text-label-sm flex items-center gap-1 transition-colors"
                    title="Tambah Faktur Masuk untuk barang ini">
                    <span class="material-symbols-outlined text-[16px]">shopping_bag</span>
                    <span>Kulakan</span>
                </a>
            @endif
        </div>
    </td>
</tr>