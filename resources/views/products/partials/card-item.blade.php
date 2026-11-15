<div class="bg-surface-container-low p-space-md rounded-2xl flex flex-col justify-between shadow-md hover:shadow-xl transition-all border border-transparent hover:border-tertiary/20">
    <div>
        <div class="flex items-center justify-between">
            <span class="font-label-md text-label-md font-bold text-primary">{{ $product->sku }}</span>
            <span class="px-2 py-0.5 rounded-full font-label-sm text-label-sm {{ $product->category->badgeClass() }}">{{ $product->category->name }}</span>
        </div>
        <div class="mt-space-sm flex items-start gap-space-sm">
            <div class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center text-primary shrink-0">
                <span class="material-symbols-outlined text-[22px]">inventory_2</span>
            </div>
            <div class="min-w-0">
                <h4 class="font-headline-sm text-body-md text-on-surface font-semibold line-clamp-1">{{ $product->name }}</h4>
                <span class="font-label-sm text-label-sm text-on-surface-variant line-clamp-1">{{ $product->variant }}</span>
            </div>
        </div>
        <div class="mt-space-md p-space-sm bg-surface-container rounded-xl flex items-center justify-between">
            <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Rasio Satuan</span>
            <div class="flex items-center gap-1 font-label-md text-label-md font-bold">
                <span class="text-secondary bg-secondary-container/40 px-2 py-0.5 rounded">1 BOX</span>
                <span class="text-on-surface-variant">=</span>
                <span class="text-primary bg-primary/20 px-2 py-0.5 rounded">{{ $product->box_pcs_ratio }} PCS</span>
            </div>
        </div>
    </div>
    <div class="mt-space-md pt-space-sm border-t border-surface-container flex items-center justify-between">
        <span class="font-label-sm text-label-sm text-primary flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>Aktif
        </span>
        <div class="flex items-center gap-1">
            <button class="p-1.5 rounded hover:bg-surface-container text-on-surface-variant hover:text-on-surface" type="button">
                <span class="material-symbols-outlined text-[16px]">visibility</span>
            </button>
            <button class="p-1.5 rounded hover:bg-surface-container text-on-surface-variant hover:text-primary" type="button">
                <span class="material-symbols-outlined text-[16px]">edit</span>
            </button>
        </div>
    </div>
</div>