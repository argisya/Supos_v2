@php
    $icons = [
        'SKU-SMB' => 'oil_barrel', 'SKU-MKN' => 'ramen_dining',
        'SKU-BVR' => 'local_cafe', 'SKU-HSE' => 'cleaning_services',
        'SKU-SNK' => 'cookie',
    ];
    $prefix = substr($product->sku, 0, 7);
    $icon   = $icons[$prefix] ?? 'inventory_2';
@endphp
<tr class="hover:bg-surface-container/60 transition-colors group">
    <td class="py-space-md px-space-lg whitespace-nowrap">
        <div class="flex flex-col">
            <div class="flex items-center gap-1">
                <span class="font-label-md text-label-md font-bold text-primary group-hover:underline cursor-pointer">{{ $product->sku }}</span>
                <button class="text-on-surface-variant hover:text-on-surface" onclick="navigator.clipboard.writeText('{{ $product->sku }}')" title="Salin SKU" type="button">
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
                <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $product->variant }}</span>
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
        <span class="inline-flex items-center gap-1 font-label-sm text-label-sm text-primary bg-primary/10 px-2 py-0.5 rounded-full">
            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span> Aktif Baku
        </span>
    </td>
    <td class="py-space-md px-space-lg whitespace-nowrap text-right">
        <div class="flex items-center justify-end gap-space-xs">
            <button class="p-1.5 rounded-lg hover:bg-surface-container-highest text-on-surface-variant hover:text-on-surface" title="Lihat Histori" type="button">
                <span class="material-symbols-outlined text-[18px]">visibility</span>
            </button>
            <button class="p-1.5 rounded-lg hover:bg-surface-container-highest text-on-surface-variant hover:text-primary" title="Ubah Rasio" type="button">
                <span class="material-symbols-outlined text-[18px]">edit_note</span>
            </button>
            <button class="p-1.5 rounded-lg hover:bg-error/20 text-on-surface-variant hover:text-error" title="Arsipkan" type="button">
                <span class="material-symbols-outlined text-[18px]">archive</span>
            </button>
        </div>
    </td>
</tr>