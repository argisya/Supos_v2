@php
    $icons = [
        'SKU-SMB' => 'oil_barrel',
        'SKU-MKN' => 'ramen_dining',
        'SKU-BVR' => 'local_cafe',
        'SKU-HSE' => 'cleaning_services',
        'SKU-SNK' => 'cookie',
    ];
    $prefix = substr($product->sku, 0, 7);
    $icon = $icons[$prefix] ?? 'inventory_2';
    $hasTransactions = ($product->has_modal ?? false) || ($product->has_outgoing ?? false);
@endphp

<div
    class="bg-surface-container-low p-space-md rounded-2xl flex flex-col justify-between shadow-md hover:shadow-xl transition-all border border-transparent hover:border-tertiary/25 group">
    <div>
        {{-- Baris Atas: SKU + Copy & Badge Kategori --}}
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-1">
                <span onclick="openProductDetail({{ $product->id }})"
                    class="font-label-md text-label-md font-bold text-primary hover:underline cursor-pointer">
                    {{ $product->sku }}
                </span>
                <button type="button" onclick="copySKU('{{ $product->sku }}', this)" title="Salin SKU"
                    class="text-on-surface-variant hover:text-on-surface cursor-pointer">
                    <span class="material-symbols-outlined text-[14px]">content_copy</span>
                </button>
            </div>
            <span
                class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ $product->category->badgeClass() }} font-semibold">
                {{ $product->category->name }}
            </span>
        </div>

        {{-- Info Produk --}}
        <div class="mt-space-sm flex items-start gap-space-sm">
            <div
                class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center text-primary shrink-0">
                <span class="material-symbols-outlined text-[22px]">{{ $icon }}</span>
            </div>
            <div class="min-w-0">
                <h4 onclick="openProductDetail({{ $product->id }})"
                    class="font-headline-sm text-body-md text-on-surface font-semibold line-clamp-1 group-hover:text-primary transition-colors cursor-pointer"
                    title="{{ $product->name }}">
                    {{ $product->name }}
                </h4>
                <span class="font-label-sm text-label-sm text-on-surface-variant line-clamp-1"
                    title="{{ $product->variant ?: 'Tanpa varian' }}">
                    {{ $product->variant ?: 'Tanpa varian' }}
                </span>
            </div>
        </div>

        {{-- Isi per Box --}}
        <div class="mt-space-md p-space-sm bg-surface-container rounded-xl flex items-center justify-between">
            <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Isi per Box</span>
            <div class="flex items-center gap-1 font-label-md text-label-md font-bold">
                <span class="text-secondary bg-secondary-container/40 px-2 py-0.5 rounded">1 BOX</span>
                <span class="text-on-surface-variant">=</span>
                <span class="text-primary bg-primary/20 px-2 py-0.5 rounded">{{ $product->box_pcs_ratio }} PCS</span>
            </div>
        </div>
    </div>

    {{-- Footer Kartu: Status & Tombol Aksi --}}
    <div class="mt-space-md pt-space-sm border-t border-surface-container flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="font-label-sm text-label-sm text-primary flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>Aktif
            </span>
            @if (isset($product->has_modal))
                @if ($product->has_modal)
                    <span class="text-[11px] text-tertiary flex items-center gap-0.5 font-medium">
                        <span class="material-symbols-outlined text-[13px]">check_circle</span> Siap Jual
                    </span>
                @else
                    <span class="text-[11px] text-amber-400/90 flex items-center gap-0.5 font-medium">
                        <span class="material-symbols-outlined text-[13px]">schedule</span> Belum Masuk Stok
                    </span>
                @endif
            @endif
        </div>

        <div class="flex items-center gap-1">
            <button onclick="openProductDetail({{ $product->id }})"
                class="p-1.5 rounded-lg hover:bg-surface-container text-on-surface-variant hover:text-tertiary transition-colors cursor-pointer"
                title="Detail & Histori" type="button">
                <span class="material-symbols-outlined text-[17px]">visibility</span>
            </button>

            <button onclick="openProductEdit({{ $product->id }})"
                class="p-1.5 rounded-lg hover:bg-surface-container text-on-surface-variant hover:text-primary transition-colors cursor-pointer"
                title="Edit Produk" type="button">
                <span class="material-symbols-outlined text-[17px]">edit_note</span>
            </button>

            @if ($hasTransactions)
                <button type="button" disabled class="p-1.5 rounded-lg text-on-surface-variant/35 cursor-not-allowed"
                    title="Terkunci: sudah dipakai di transaksi">
                    <span class="material-symbols-outlined text-[17px]">lock</span>
                </button>
            @else
                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline"
                    data-confirm-title="Hapus produk?"
                    data-confirm-message="{{ 'Hapus permanen produk ' . $product->name . ' (' . $product->sku . ')?' }}"
                    data-confirm-confirm-label="Ya, hapus" data-confirm-variant="danger">
                    @csrf
                    @method('DELETE')
                    <button
                        class="p-1.5 rounded-lg hover:bg-error/20 text-on-surface-variant hover:text-error transition-colors cursor-pointer"
                        title="Hapus Produk" type="submit">
                        <span class="material-symbols-outlined text-[17px]">delete</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>