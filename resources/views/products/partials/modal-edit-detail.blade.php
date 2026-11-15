{{-- ============ MODAL DETAIL & HISTORI PRODUK ============ --}}
<div id="modalDetailProduk" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-space-md bg-surface-container-lowest/80 backdrop-blur-md hidden">
    <div class="relative w-full max-w-lg max-h-[90vh] bg-surface-container-low rounded-2xl shadow-2xl overflow-hidden flex flex-col border border-outline-variant/30">
        <div class="px-space-lg py-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest shrink-0">
            <div class="flex items-center gap-space-sm">
                <div class="w-9 h-9 rounded-xl bg-tertiary/15 flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined text-[20px]">inventory</span>
                </div>
                <div>
                    <h3 id="detailName" class="font-headline-sm text-headline-sm text-on-surface font-bold">Memuat...</h3>
                    <span id="detailSkuBadge" class="font-label-sm text-primary font-bold">SKU-...</span>
                </div>
            </div>
            <button type="button" onclick="closeDetailModal()" class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright flex items-center justify-center text-on-surface-variant hover:text-on-surface cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <div class="p-space-lg overflow-y-auto flex flex-col gap-space-md text-body-sm">
            <div class="grid grid-cols-2 gap-space-sm">
                <div class="p-3 rounded-xl bg-surface-container">
                    <span class="text-label-sm text-on-surface-variant block">Kategori</span>
                    <strong id="detailCategory" class="text-on-surface mt-0.5 block">-</strong>
                </div>
                <div class="p-3 rounded-xl bg-surface-container">
                    <span class="text-label-sm text-on-surface-variant block">Barcode</span>
                    <strong id="detailBarcode" class="text-on-surface font-mono mt-0.5 block">-</strong>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-surface-container">
                <span class="text-label-sm text-on-surface-variant block">Varian / Kemasan</span>
                <span id="detailVariant" class="text-on-surface mt-0.5 block">-</span>
            </div>

            <div class="p-4 rounded-xl bg-primary/10 border border-primary/25 flex items-center justify-between">
                <span class="font-label-md text-on-surface font-semibold">Aturan Konversi Baku</span>
                <span id="detailRatio" class="font-headline-sm text-primary font-extrabold">1 BOX = 0 PCS</span>
            </div>

            {{-- Pratinjau Lintas Modul (Read-Only) --}}
            <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/20 flex flex-col gap-2">
                <span class="text-label-sm text-tertiary uppercase font-bold tracking-wider">Pratinjau Stok & Modal (Read-Only)</span>
                <div class="flex justify-between items-center">
                    <span class="text-on-surface-variant">Estimasi Sisa Stok:</span>
                    <strong id="detailStock" class="text-on-surface">0 Box, 0 Pcs</strong>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-on-surface-variant">Harga Modal Terakhir:</span>
                    <strong id="detailModalPrice" class="text-on-surface">-</strong>
                </div>
            </div>

            <section class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/20 flex flex-col gap-2">
                <h4 class="text-label-sm text-tertiary uppercase font-bold tracking-wider">Histori Transaksi Terbaru</h4>
                <div id="detailHistoryEntries" class="divide-y divide-surface-container-highest/40">
                    <p class="text-label-sm text-on-surface-variant">Memuat histori transaksi...</p>
                </div>
            </section>

            <div class="flex justify-between text-label-sm text-on-surface-variant pt-2 border-t border-surface-container-highest">
                <span>Dibuat: <span id="detailCreated">-</span></span>
                <span>Diubah: <span id="detailUpdated">-</span></span>
            </div>
        </div>

        <div class="px-space-lg py-space-md bg-surface-container flex justify-end gap-2 border-t border-surface-container-highest shrink-0">
            <button type="button" onclick="closeDetailModal()" class="h-10 px-5 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-label-md cursor-pointer">Tutup</button>
        </div>
    </div>
</div>

{{-- ============ MODAL EDIT PRODUK & UBAH RASIO ============ --}}
<div id="modalEditProduk" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-space-md bg-surface-container-lowest/80 backdrop-blur-md hidden">
    <div class="relative w-full max-w-2xl max-h-[92vh] bg-surface-container-low rounded-2xl shadow-2xl overflow-hidden flex flex-col border border-outline-variant/30">
        <div class="px-space-lg py-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest shrink-0">
            <div class="flex items-center gap-space-sm">
                <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[22px]">edit_note</span>
                </div>
                <div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Edit Master Barang & Rasio</h3>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Perbarui spesifikasi atau aturan konversi kemasan</span>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright flex items-center justify-center text-on-surface-variant hover:text-on-surface cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form id="formEditProduk" method="POST" class="flex flex-col flex-1 min-h-0">
            @csrf
            @method('PATCH')

            <div class="p-space-lg flex flex-col gap-space-md overflow-y-auto flex-1">
                <div id="editTransactionWarning" class="hidden p-space-sm rounded-xl bg-amber-500/10 border border-amber-400/30 text-amber-300 font-body-sm text-body-sm flex items-start gap-2">
                    <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5">warning</span>
                    <span><strong>Rasio terkunci:</strong> Produk ini sudah memiliki riwayat transaksi. Untuk jenis kemasan baru, buat SKU baru agar konversi transaksi lama tetap akurat.</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="font-label-md text-label-md text-on-surface font-semibold">Nama Produk Baku <span class="text-error">*</span></label>
                        <input id="editName" name="name" required maxlength="255"
                               class="h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" type="text" />
                    </div>

                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="font-label-md text-label-md text-on-surface font-semibold">Varian / Deskripsi (opsional)</label>
                        <input id="editVariant" name="variant" maxlength="255"
                               class="h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" type="text" />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-md text-label-md text-on-surface font-semibold">Kategori Master <span class="text-error">*</span></label>
                        <select id="editCategory" name="category_id" required
                                class="h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer">
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-md text-label-md text-on-surface font-semibold">Kode SKU Baku <span class="text-error">*</span></label>
                        <input id="editSku" name="sku" required maxlength="50"
                               class="w-full h-11 px-space-md rounded-xl bg-surface-container text-primary font-label-md text-label-md font-bold focus:outline-none focus:ring-1 focus:ring-primary uppercase" type="text" />
                    </div>

                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="font-label-md text-label-md text-on-surface font-semibold">Barcode (opsional — unik)</label>
                        <input id="editBarcode" name="barcode" maxlength="50"
                               class="h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" type="text" />
                    </div>
                </div>

                {{-- Ubah Rasio Box --}}
                <div class="p-space-md rounded-2xl bg-surface-container/60 border border-primary/20 flex flex-col gap-space-sm">
                    <span class="font-headline-sm text-body-md text-on-surface font-bold">Aturan Rasio Kemasan</span>
                    <div class="flex items-center gap-space-md flex-wrap">
                        <div class="flex items-center gap-space-xs bg-surface-container-lowest p-1.5 rounded-xl border border-surface-container-highest">
                            <span class="px-space-sm py-1 font-label-md text-label-md font-bold text-secondary bg-secondary-container/40 rounded-lg">1 BOX =</span>
                            <input id="editRatio" name="box_pcs_ratio" min="1" required
                                   class="w-20 h-8 bg-transparent text-center font-display-lg text-headline-sm text-primary font-extrabold focus:outline-none read-only:cursor-not-allowed read-only:opacity-60" type="number" />
                            <span class="px-space-sm py-1 font-label-md text-label-md font-bold text-primary">PCS</span>
                        </div>
                        <span id="editSimPreview" class="font-label-md text-primary bg-primary/10 px-3 py-1.5 rounded-lg">
                            Simulasi 10 Box = 240 Pcs
                        </span>
                    </div>
                </div>
            </div>

            <div class="px-space-lg py-space-md bg-surface-container flex items-center justify-end gap-space-sm border-t border-surface-container-highest shrink-0">
                <button type="button" onclick="closeEditModal()" class="h-10 px-space-lg rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-md cursor-pointer">Batalkan</button>
                <button type="submit" class="h-10 px-space-xl rounded-xl bg-linear-to-r from-secondary-container via-primary-container to-primary text-on-primary-container font-headline-sm text-headline-sm font-bold shadow-lg cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>