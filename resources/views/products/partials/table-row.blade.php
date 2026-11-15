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
<tr class="hover:bg-surface-container/60 transition-colors group {{ $isArchived ? 'opacity-70' : '' }}">
    {{-- Checkbox Bulk Action --}}
    <td class="py-space-md pl-space-lg pr-2 whitespace-nowrap w-10">
        <input type="checkbox" value="{{ $product->id }}"
               class="row-checkbox w-4 h-4 rounded border-outline-variant bg-surface-container text-primary focus:ring-primary cursor-pointer" />
    </td>

    <td class="py-space-md px-space-md whitespace-nowrap">
        <div class="flex flex-col">
            <div class="flex items-center gap-1">
                <span onclick="openProductDetail({{ $product->id }})" class="font-label-md text-label-md font-bold text-primary group-hover:underline cursor-pointer">{{ $product->sku }}</span>
                <button class="text-on-surface-variant hover:text-on-surface cursor-pointer" onclick="copySKU('{{ $product->sku }}', this)" title="Salin SKU" type="button">
                    <span class="material-symbols-outlined text-[14px]">content_copy</span>
                </button>
            </div>
            <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $product->barcode ?? '—' }}</span>
        </div>
    </td>

    <td class="py-space-md px-space-md">
        <div class="flex items-center gap-space-sm">
            <div class="w-9 h-9 rounded-lg bg-surface-container-high flex items-center justify-center text-primary shrink-0">
                <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
            </div>
            <div class="flex flex-col min-w-0">
                <span class="font-headline-sm text-body-md text-on-surface font-semibold truncate group-hover:text-primary transition-colors">{{ $product->name }}</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $product->variant ?: 'Tanpa keterangan varian' }}</span>
            </div>
        </div>
    </td>

    <td class="py-space-md px-space-md whitespace-nowrap">
        <span class="px-2.5 py-1 rounded-full font-label-sm text-label-sm {{ $product->category->badgeClass() }} font-semibold">
            {{ $product->category->name }}
        </span>
    </td>

    <td class="py-space-md px-space-md whitespace-nowrap text-center">
        <div class="inline-flex items-center rounded-lg overflow-hidden shadow-sm bg-surface-container-lowest">
            <span class="px-2.5 py-1 bg-secondary-container/50 text-secondary font-label-md text-label-md font-bold">1 BOX</span>
            <span class="px-1.5 py-1 text-on-surface-variant text-[12px] bg-surface-container">=</span>
            <span class="px-2.5 py-1 bg-primary/20 text-primary font-label-md text-label-md font-bold">{{ $product->box_pcs_ratio }} PCS</span>
        </div>
    </td>

    <td class="py-space-md px-space-md whitespace-nowrap">
        <div class="flex items-center gap-space-xs bg-surface-container px-2 py-1 rounded-lg w-max">
            <input class="sim-input w-12 bg-surface-container-lowest text-center rounded text-on-surface font-label-md text-label-md py-0.5 focus:outline-none focus:ring-1 focus:ring-primary"
                   data-ratio="{{ $product->box_pcs_ratio }}" min="1" type="number" value="2" />
            <span class="font-label-sm text-label-sm text-on-surface-variant">Box →</span>
            <span class="sim-result font-label-md text-label-md text-primary font-bold px-1">{{ $product->box_pcs_ratio * 2 }} Pcs</span>
        </div>
    </td>

    <td class="py-space-md px-space-md whitespace-nowrap">
        @if($isArchived)
            <span class="inline-flex items-center gap-1 font-label-sm text-label-sm text-on-surface-variant bg-surface-container-highest px-2.5 py-0.5 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-on-surface-variant"></span> Diarsipkan
            </span>
        @else
            <div class="flex flex-col gap-1 items-start">
                <span class="inline-flex items-center gap-1 font-label-sm text-label-sm text-primary bg-primary/10 px-2.5 py-0.5 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span> Aktif Baku
                </span>
                @if(isset($product->has_modal))
                    @if($product->has_modal)
                        <span class="text-[11px] text-tertiary flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined text-[13px]">check_circle</span> Siap Jual
                        </span>
                    @else
                        <span class="text-[11px] text-amber-400/90 flex items-center gap-1 font-medium" title="Belum ada riwayat Barang Masuk (harga modal belum tercatat)">
                            <span class="material-symbols-outlined text-[13px]">schedule</span> Menunggu Modal
                        </span>
                    @endif
                @endif
            </div>
        @endif
    </td>

    <td class="py-space-md px-space-lg whitespace-nowrap text-right">
        <div class="flex items-center justify-end gap-space-xs">
            <button onclick="openProductDetail({{ $product->id }})"
                    class="p-1.5 rounded-lg hover:bg-surface-container-highest text-on-surface-variant hover:text-tertiary transition-colors cursor-pointer"
                    title="Lihat Detail & Histori" type="button">
                <span class="material-symbols-outlined text-[18px]">visibility</span>
            </button>

            <button onclick="openProductEdit({{ $product->id }})"
                    class="p-1.5 rounded-lg hover:bg-surface-container-highest text-on-surface-variant hover:text-primary transition-colors cursor-pointer"
                    title="Edit Produk & Ubah Rasio" type="button">
                <span class="material-symbols-outlined text-[18px]">edit_note</span>
            </button>

            @if($isArchived)
                <form action="{{ route('products.restore', $product) }}" method="POST" class="inline"
                      data-confirm-title="Pulihkan produk?"
                      data-confirm-message="{{ 'Pulihkan produk ' . $product->name . ' ke katalog aktif?' }}"
                      data-confirm-confirm-label="Ya, pulihkan"
                      data-confirm-variant="primary">
                    @csrf
                    @method('PATCH')
                    <button class="p-1.5 rounded-lg hover:bg-primary/20 text-on-surface-variant hover:text-primary transition-colors cursor-pointer"
                            title="Pulihkan Produk" type="submit">
                        <span class="material-symbols-outlined text-[18px]">unarchive</span>
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
                            title="Arsipkan Produk" type="submit">
                        <span class="material-symbols-outlined text-[18px]">archive</span>
                    </button>
                </form>
            @endif
        </div>
    </td>
</tr>