{{-- ==================== MODAL DETAIL FAKTUR ==================== --}}
<div id="modalDetailFaktur" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-surface-container-lowest/80 backdrop-blur-md hidden">
    <div class="w-full max-w-lg max-h-[90vh] bg-surface-container-low rounded-2xl border border-outline-variant/30 shadow-2xl flex flex-col overflow-hidden">
        <div class="px-space-lg py-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-tertiary text-[22px]">receipt_long</span>
                <div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold" id="detNoFaktur">—</h3>
                    <span class="text-label-sm text-on-surface-variant" id="detWaktu">—</span>
                </div>
            </div>
            <button type="button" onclick="closeDetailFakturModal()" class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:text-on-surface cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <div class="p-space-lg flex flex-col gap-space-md overflow-y-auto font-body-sm">
            <div class="grid grid-cols-2 gap-space-sm p-space-md rounded-xl bg-surface-container">
                <div>
                    <span class="text-label-sm text-on-surface-variant uppercase block">Supplier</span>
                    <span class="font-bold text-on-surface" id="detSupplier">—</span>
                    <span class="text-[11px] text-on-surface-variant block" id="detSupplierType">—</span>
                </div>
                <div class="text-right">
                    <span class="text-label-sm text-on-surface-variant uppercase block">Status Pembayaran</span>
                    <span class="font-bold text-primary uppercase" id="detStatus">—</span>
                    <span class="text-[11px] text-on-surface-variant block">Jatuh Tempo: <strong id="detDueDate">—</strong></span>
                </div>
            </div>

            <div class="p-space-md rounded-xl bg-surface-container-lowest border border-outline-variant/20">
                <span class="text-label-sm text-tertiary font-bold uppercase" id="detSku">—</span>
                <h4 class="font-headline-sm text-body-md font-bold text-on-surface mt-0.5" id="detProduk">—</h4>
                <span class="text-[11px] text-on-surface-variant" id="detRatio">—</span>

                <div class="mt-3 pt-3 border-t border-surface-container grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-label-sm text-on-surface-variant block">Kuantitas Masuk</span>
                        <span class="font-bold text-on-surface" id="detQty">—</span>
                    </div>
                    <div class="text-right">
                        <span class="text-label-sm text-on-surface-variant block">Stok Gudang Saat Ini</span>
                        <span class="font-bold text-primary" id="detCurrentStock">—</span>
                    </div>
                    <div>
                        <span class="text-label-sm text-on-surface-variant block">Modal / Box</span>
                        <span class="font-bold text-on-surface" id="detPriceBox">—</span>
                    </div>
                    <div class="text-right">
                        <span class="text-label-sm text-on-surface-variant block">Modal Dasar / Pcs</span>
                        <span class="font-bold text-tertiary" id="detPricePcs">—</span>
                    </div>
                </div>
            </div>

            <div class="p-space-md rounded-xl bg-primary/10 border border-primary/25 flex items-center justify-between">
                <span class="font-label-md uppercase text-on-surface font-semibold">Total Nilai Faktur</span>
                <span class="font-headline-md text-headline-md text-primary font-black" id="detSubtotal">Rp 0</span>
            </div>
        </div>
    </div>
</div>

{{-- ==================== MODAL SCAN FAKTUR / BARCODE ==================== --}}
<div id="modalScanFaktur" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-surface-container-lowest/80 backdrop-blur-md hidden">
    <div class="w-full max-w-md bg-surface-container-low rounded-2xl border border-outline-variant/30 shadow-2xl p-space-lg">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-tertiary text-[24px]">document_scanner</span>
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Scan Barcode Produk / Faktur</h3>
            </div>
            <button type="button" onclick="closeScanFakturModal()" class="text-on-surface-variant hover:text-on-surface cursor-pointer">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <p class="text-body-sm text-on-surface-variant mb-4">
            Pindai <strong>Barcode/SKU produk</strong> untuk otomatis memilihnya di form input kulakan, atau masukkan <strong>No. Faktur</strong> untuk mencari di riwayat.
        </p>
        <div class="flex flex-col gap-3">
            <input id="scanFakturInput" type="text" autocomplete="off"
                   placeholder="Scan barcode produk atau ketik No. Faktur..."
                   class="w-full h-12 px-4 rounded-xl bg-surface-container border border-tertiary/40 focus:border-tertiary text-on-surface font-mono text-base focus:outline-none" />
            <div class="flex justify-end gap-2 mt-1">
                <button type="button" onclick="closeScanFakturModal()" class="h-10 px-4 rounded-xl bg-surface-container text-on-surface-variant hover:text-on-surface font-label-md cursor-pointer">Batal</button>
                <button type="button" onclick="processScanInput()" class="h-10 px-5 rounded-xl bg-tertiary text-on-tertiary font-headline-sm font-bold cursor-pointer">Terapkan</button>
            </div>
        </div>
    </div>
</div>

{{-- ==================== MODAL SOP MODAL BOX ==================== --}}
<div id="modalSopModalBox" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-surface-container-lowest/80 backdrop-blur-md hidden">
    <div class="w-full max-w-lg max-h-[88vh] bg-surface-container-low rounded-2xl border border-outline-variant/30 shadow-2xl flex flex-col overflow-hidden">
        <div class="px-space-lg py-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">menu_book</span>
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">SOP Pencatatan Barang Masuk & Modal Box</h3>
            </div>
            <button type="button" onclick="closeSopModal()" class="w-8 h-8 rounded-lg bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:text-on-surface cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <div class="p-space-lg flex flex-col gap-3 overflow-y-auto font-body-sm text-on-surface-variant leading-relaxed">
            <div class="p-3 rounded-xl bg-surface-container">
                <strong class="text-on-surface block mb-1">1. Wajib Input Barang Masuk Sebelum Penjualan</strong>
                Produk baru di Master Barang tidak dapat dijual di menu Barang Keluar sebelum tercatat minimal satu kali di Barang Masuk sebagai acuan harga modal.
            </div>
            <div class="p-3 rounded-xl bg-surface-container">
                <strong class="text-on-surface block mb-1">2. Harga Modal Bersifat Historis (Permanen)</strong>
                Harga modal per Box terakhir menjadi acuan laba untuk penjualan berikutnya, tanpa mengubah catatan keuntungan transaksi penjualan yang sudah terjadi sebelumnya.
            </div>
            <div class="p-3 rounded-xl bg-surface-container">
                <strong class="text-on-surface block mb-1">3. Pembelian Tempo & Pelunasan</strong>
                Pilih metode <em>Tempo / Hutang</em> beserta tanggal jatuh tempo untuk faktur kredit supplier. Saat tagihan dibayar, klik tombol <strong>Bayar</strong> pada tabel riwayat.
            </div>
            <div class="p-3 rounded-xl bg-surface-container">
                <strong class="text-on-surface block mb-1">4. Aturan Rollback Transaksi</strong>
                Penghapusan faktur akan mengurangi saldo stok fisik. Sistem otomatis menolak rollback jika sebagian stok dari faktur tersebut sudah terjual di Barang Keluar.
            </div>
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div id="toastContainer" class="fixed bottom-5 right-5 z-[70] flex flex-col gap-2 pointer-events-none"></div>