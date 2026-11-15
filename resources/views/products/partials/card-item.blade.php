@php
    $icons = [
        'SKU-SMB' => 'oil_barrel',
        'SKU-MKN' => 'ramen_dining',
        'SKU-BVR' => 'local_cafe',
        'SKU-HSE' => 'cleaning_services',
        'SKU-SNK' => 'cookie',
    ];
    $prefix = substr($product->sku, 0, 7);
    $icon   = $icons[$prefix] ?? 'inventory_2';
    $isArchived = $product->status === 'archived';
@endphp

<div class="bg-surface-container-low p-space-md rounded-2xl flex flex-col justify-between shadow-md hover:shadow-xl transition-all border border-transparent hover:border-tertiary/25 group {{ $isArchived ? 'opacity-70' : '' }}">
    <div>
        {{-- Baris Atas: SKU + Copy & Badge Kategori --}}
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-1">
                <span onclick="openProductDetail({{ $product->id }})"
                      class="font-label-md text-label-md font-bold text-primary hover:underline cursor-pointer">
                    {{ $product->sku }}
                </span>
                <button type="button"
                        onclick="copySKU('{{ $product->sku }}', this)"
                        title="Salin SKU"
                        class="text-on-surface-variant hover:text-on-surface cursor-pointer">
                    <span class="material-symbols-outlined text-[14px]">content_copy</span>
                </button>
            </div>
            <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ $product->category->badgeClass() }} font-semibold">
                {{ $product->category->name }}
            </span>
        </div>

        {{-- Info Produk --}}
        <div class="mt-space-sm flex items-start gap-space-sm">
            <div class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center text-primary shrink-0">
                <span class="material-symbols-outlined text-[22px]">{{ $icon }}</span>
            </div>
            <div class="min-w-0">
                <h4 onclick="openProductDetail({{ $product->id }})"
                    class="font-headline-sm text-body-md text-on-surface font-semibold line-clamp-1 group-hover:text-primary transition-colors cursor-pointer">
                    {{ $product->name }}
                </h4>
                <span class="font-label-sm text-label-sm text-on-surface-variant line-clamp-1">
                    {{ $product->variant ?: 'Tanpa keterangan varian' }}
                </span>
                <span class="font-label-sm text-label-sm text-on-surface-variant font-mono">
                    {{ $product->barcode ?: 'Tanpa barcode' }}
                </span>
            </div>
        </div>

        {{-- Rasio Konversi --}}
        <div class="mt-space-md p-space-sm bg-surface-container rounded-xl flex items-center justify-between">
            <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Rasio Satuan</span>
            <div class="flex items-center gap-1 font-label-md text-label-md font-bold">
                <span class="text-secondary bg-secondary-container/40 px-2 py-0.5 rounded">1 BOX</span>
                <span class="text-on-surface-variant">=</span>
                <span class="text-primary bg-primary/20 px-2 py-0.5 rounded">{{ $product->box_pcs_ratio }} PCS</span>
            </div>
        </div>
        <div class="mt-space-sm p-space-sm bg-surface-container rounded-xl flex items-center gap-space-xs text-label-sm">
            <input class="sim-input w-14 bg-surface-container-lowest text-center rounded text-on-surface py-1 focus:outline-none focus:ring-1 focus:ring-primary"
                   data-ratio="{{ $product->box_pcs_ratio }}" min="1" type="number" value="2" aria-label="Jumlah box untuk simulasi" />
            <span class="text-on-surface-variant">Box =</span>
            <span class="sim-result text-primary font-bold">{{ $product->box_pcs_ratio * 2 }} Pcs</span>
        </div>
    </div>

    {{-- Footer Kartu: Status & Tombol Aksi --}}
    <div class="mt-space-md pt-space-sm border-t border-surface-container flex items-center justify-between">
        @if($isArchived)
            <span class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-on-surface-variant"></span>Diarsipkan
            </span>
        @else
            <span class="font-label-sm text-label-sm text-primary flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>Aktif Baku
            </span>
        @endif

        <div class="flex items-center gap-1">
            {{-- Lihat Detail & Histori --}}
            <button onclick="openProductDetail({{ $product->id }})"
                    class="p-1.5 rounded-lg hover:bg-surface-container text-on-surface-variant hover:text-tertiary transition-colors cursor-pointer"
                    title="Lihat Detail & Histori"
                    type="button">
                <span class="material-symbols-outlined text-[17px]">visibility</span>
            </button>

            {{-- Edit & Ubah Rasio --}}
            <button onclick="openProductEdit({{ $product->id }})"
                    class="p-1.5 rounded-lg hover:bg-surface-container text-on-surface-variant hover:text-primary transition-colors cursor-pointer"
                    title="Edit Produk & Ubah Rasio"
                    type="button">
                <span class="material-symbols-outlined text-[17px]">edit_note</span>
            </button>

            {{-- Arsipkan / Pulihkan --}}
            @if($isArchived)
                <form action="{{ route('products.restore', $product) }}" method="POST" class="inline"
                      data-confirm-title="Pulihkan produk?"
                      data-confirm-message="{{ 'Pulihkan produk ' . $product->name . ' ke katalog aktif?' }}"
                      data-confirm-confirm-label="Ya, pulihkan"
                      data-confirm-variant="primary">
                    @csrf
                    @method('PATCH')
                    <button class="p-1.5 rounded-lg hover:bg-primary/20 text-on-surface-variant hover:text-primary transition-colors cursor-pointer"
                            title="Pulihkan Produk"
                            type="submit">
                        <span class="material-symbols-outlined text-[17px]">unarchive</span>
                    </button>
                </form>
            @else
                <form action="{{ route('products.archive', $product) }}" method="POST" class="inline"
                      data-confirm-title="Arsipkan produk?"
                      data-confirm-message="{{ 'Arsipkan produk ' . $product->name . '?' }}"
                      data-confirm-confirm-label="Ya, arsipkan"
                      data-confirm-variant="danger">
                    @csrf
                    @method('PATCH')
                    <button class="p-1.5 rounded-lg hover:bg-error/20 text-on-surface-variant hover:text-error transition-colors cursor-pointer"
                            title="Arsipkan Produk"
                            type="submit">
                        <span class="material-symbols-outlined text-[17px]">archive</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>