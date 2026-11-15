{{-- ============ MODAL SCAN BARCODE CEPAT ============ --}}
<div id="modalScanBarcode" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-surface-container-lowest/80 backdrop-blur-md hidden">
    <div class="w-full max-w-md bg-surface-container-low rounded-2xl border border-outline-variant/30 shadow-2xl p-space-lg">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-[24px]">qr_code_scanner</span>
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Scan / Cari Barcode SKU</h3>
            </div>
            <button type="button" onclick="closeScannerModal()" class="text-on-surface-variant hover:text-on-surface cursor-pointer">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <p class="text-body-sm text-on-surface-variant mb-4">
            Arahkan alat <em>barcode scanner</em> ke kemasan produk atau ketik kode barcode/SKU lalu tekan <strong>Enter</strong>.
        </p>
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-col gap-3">
            <input type="hidden" name="category" value="{{ $catSlug }}">
            <input type="hidden" name="status" value="{{ request('status', 'active') }}">
            <input type="hidden" name="box_size" value="{{ $boxSize }}">
            <input type="hidden" name="sort" value="{{ request('sort', 'latest') }}">
            <input id="scannerInput" name="search" type="text" autocomplete="off"
                   placeholder="Menunggu hasil scan barcode..."
                   class="w-full h-12 px-4 rounded-xl bg-surface-container border border-secondary/40 focus:border-secondary text-on-surface font-mono text-base focus:outline-none" />
            <div class="flex justify-end gap-2 mt-2">
                <button type="button" onclick="closeScannerModal()" class="h-10 px-4 rounded-xl bg-surface-container text-on-surface-variant hover:text-on-surface font-label-md cursor-pointer">Batal</button>
                <button type="submit" class="h-10 px-5 rounded-xl bg-secondary text-on-secondary font-headline-sm font-bold cursor-pointer">Cari Produk</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ MODAL KELOLA KATEGORI CEPAT ============ --}}
<div id="modalKelolaKategori" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-surface-container-lowest/80 backdrop-blur-md hidden">
    <div class="w-full max-w-md max-h-[88vh] bg-surface-container-low rounded-2xl border border-outline-variant/30 shadow-2xl flex flex-col overflow-hidden">
        <div class="px-space-lg py-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest shrink-0">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">category</span>
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Manajemen Kategori</h3>
            </div>
            <button type="button" onclick="closeCategoryModal()" class="text-on-surface-variant hover:text-on-surface cursor-pointer">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="p-space-lg flex flex-col gap-space-md overflow-y-auto">
            {{-- Form Tambah Kategori --}}
            <form method="POST" action="{{ route('categories.store') }}" class="flex gap-2">
                @csrf
                <input name="name" type="text" required maxlength="100"
                       placeholder="Nama kategori baru (misal: Snack & Biskuit)..."
                       class="flex-1 h-10 px-3.5 rounded-xl bg-surface-container text-on-surface text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                <button type="submit" class="h-10 px-4 rounded-xl bg-primary text-on-primary font-label-md font-bold hover:opacity-90 cursor-pointer shrink-0">
                    + Tambah
                </button>
            </form>

            {{-- Daftar Kategori Eksisting --}}
            <div class="flex flex-col gap-2">
                <span class="text-label-sm text-on-surface-variant uppercase font-semibold">Daftar Kategori Terdaftar</span>
                <div class="divide-y divide-surface-container-highest/40 rounded-xl bg-surface-container overflow-hidden">
                    @foreach($categories as $cat)
                        <div class="px-3.5 py-2.5 flex items-center justify-between text-body-sm">
                            <div class="flex items-center gap-2">
                                <span class="text-on-surface font-medium">{{ $cat->name }}</span>
                                <span class="text-label-sm text-on-surface-variant bg-surface-container-highest px-2 py-0.5 rounded-full">
                                    {{ $cat->products_count }} SKU
                                </span>
                            </div>
                            @if($cat->products_count === 0)
                                <form method="POST" action="{{ route('categories.destroy', $cat) }}"
                                      data-confirm-title="Hapus kategori?"
                                      data-confirm-message="{{ 'Hapus kategori ' . $cat->name . '?' }}"
                                      data-confirm-confirm-label="Ya, hapus"
                                      data-confirm-variant="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-on-surface-variant hover:text-error p-1 rounded cursor-pointer" title="Hapus kategori kosong">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </form>
                            @else
                                <span class="text-label-sm text-on-surface-variant/50" title="Tidak dapat dihapus karena memiliki produk">Dipakai</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>