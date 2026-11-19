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
<tr class="hover:bg-surface-container/60 transition-colors group">
    {{-- Checkbox Bulk Action --}}
    <td class="py-space-md pl-space-md pr-2 whitespace-nowrap w-10 text-center">
        <input type="checkbox" value="{{ $product->id }}"
            class="row-checkbox w-4 h-4 rounded border-outline-variant bg-surface-container text-primary focus:ring-primary cursor-pointer" />
    </td>

    <td class="py-space-md px-3 whitespace-nowrap text-center">
        <div class="inline-flex items-center justify-center gap-1">
            <span onclick="openProductDetail({{ $product->id }})"
                class="font-label-md text-label-md font-bold text-primary group-hover:underline cursor-pointer">{{ $product->sku }}</span>
            <button class="text-on-surface-variant hover:text-on-surface cursor-pointer"
                onclick="copySKU('{{ $product->sku }}', this)" title="Salin SKU" type="button">
                <span class="material-symbols-outlined text-[14px]">content_copy</span>
            </button>
        </div>
    </td>

    <td class="py-space-md px-3 max-w-[260px] text-left">
        <div class="flex items-center gap-2.5">
            <div
                class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-primary shrink-0">
                <span class="material-symbols-outlined text-[18px]">{{ $icon }}</span>
            </div>
            <div class="flex flex-col min-w-0">
                <span
                    class="font-headline-sm text-body-md text-on-surface font-semibold truncate group-hover:text-primary transition-colors"
                    title="{{ $product->name }}">{{ $product->name }}</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant truncate"
                    title="{{ $product->variant ?: 'Tanpa varian' }}">{{ $product->variant ?: 'Tanpa varian' }}</span>
            </div>
        </div>
    </td>

    <td class="py-space-md px-3 whitespace-nowrap text-center">
        <span
            class="px-2.5 py-1 rounded-full font-label-sm text-label-sm {{ $product->category->badgeClass() }} font-semibold">
            {{ $product->category->name }}
        </span>
    </td>

    <td class="py-space-md px-3 whitespace-nowrap text-center">
        <div class="inline-flex items-center rounded-lg overflow-hidden shadow-sm bg-surface-container-lowest">
            <span class="px-2 py-1 bg-secondary-container/50 text-secondary font-label-md text-label-sm font-bold">1
                BOX</span>
            <span class="px-1 py-1 text-on-surface-variant text-[11px] bg-surface-container">=</span>
            <span
                class="px-2 py-1 bg-primary/20 text-primary font-label-md text-label-sm font-bold">{{ $product->box_pcs_ratio }}
                PCS</span>
        </div>
    </td>

    <td class="py-space-md px-3 whitespace-nowrap text-center">
        <div class="inline-flex flex-col gap-0.5 items-center">
            <span
                class="inline-flex items-center gap-1 font-label-sm text-label-sm text-primary bg-primary/10 px-2 py-0.5 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span> Aktif
            </span>
            @if (isset($product->has_modal))
                @if ($product->has_modal)
                    <span class="text-[11px] text-tertiary flex items-center gap-1 font-medium">
                        <span class="material-symbols-outlined text-[13px]">check_circle</span> Siap Jual
                    </span>
                @else
                    <span class="text-[11px] text-amber-400/90 flex items-center gap-1 font-medium"
                        title="Belum ada riwayat Barang Masuk">
                        <span class="material-symbols-outlined text-[13px]">schedule</span> Belum Masuk Stok
                    </span>
                @endif
            @endif
        </div>
    </td>

    <td
        class="sticky right-0 z-10 bg-surface-container-low group-hover:bg-surface-container transition-colors py-space-md px-space-md whitespace-nowrap text-center shadow-[-6px_0_12px_-6px_rgba(0,0,0,0.35)]">
        <div class="inline-flex items-center justify-center gap-1">
            <button onclick="openProductDetail({{ $product->id }})"
                class="p-1.5 rounded-lg hover:bg-surface-container-highest text-on-surface-variant hover:text-tertiary transition-colors cursor-pointer"
                title="Detail & Histori" type="button">
                <span class="material-symbols-outlined text-[18px]">visibility</span>
            </button>

            <button onclick="openProductEdit({{ $product->id }})"
                class="p-1.5 rounded-lg hover:bg-surface-container-highest text-on-surface-variant hover:text-primary transition-colors cursor-pointer"
                title="Edit Produk" type="button">
                <span class="material-symbols-outlined text-[18px]">edit_note</span>
            </button>

            @if ($hasTransactions)
                <button type="button" disabled class="p-1.5 rounded-lg text-on-surface-variant/35 cursor-not-allowed"
                    title="Terkunci: sudah dipakai di transaksi">
                    <span class="material-symbols-outlined text-[18px]">lock</span>
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
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </form>
            @endif
        </div>
    </td>
</tr>