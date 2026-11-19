{{-- ============ FLOATING BULK ACTION BAR ============ --}}
<div id="bulkActionBar"
    class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 hidden items-center gap-4 px-5 py-3 rounded-2xl bg-surface-container-high border border-primary/40 shadow-2xl backdrop-blur-xl">
    <span class="text-body-sm text-on-surface font-semibold">
        <span id="bulkSelectedCount" class="text-primary font-bold">0</span> produk dipilih
    </span>
    <div class="h-5 w-px bg-outline-variant/40"></div>
    <form id="bulkActionForm" method="POST" action="{{ route('products.bulk-delete') }}" class="flex items-center gap-2">
        @csrf
        <div id="bulkHiddenInputs"></div>
        <button type="submit" data-confirm-title="Hapus produk terpilih?"
            data-confirm-message="Semua produk terpilih yang belum memiliki riwayat transaksi akan dihapus permanen."
            data-confirm-confirm-label="Ya, hapus" data-confirm-variant="danger"
            class="px-3.5 py-1.5 rounded-xl bg-error/15 hover:bg-error/25 text-error font-label-md font-semibold flex items-center gap-1 cursor-pointer transition-colors">
            <span class="material-symbols-outlined text-[16px]">delete</span> Hapus Terpilih
        </button>
    </form>
</div>

{{-- ============ MODAL IMPORT CSV ============ --}}
<div id="modalImportCsv"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-surface-container-lowest/80 backdrop-blur-md hidden">
    <div
        class="w-full max-w-md bg-surface-container-low rounded-2xl border border-outline-variant/30 shadow-2xl p-space-lg">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-tertiary text-[22px]">upload_file</span>
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Import Master Barang (CSV)</h3>
            </div>
            <button type="button" onclick="document.getElementById('modalImportCsv').classList.add('hidden')"
                class="text-on-surface-variant hover:text-on-surface cursor-pointer">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <p class="text-body-sm text-on-surface-variant mb-4 leading-relaxed">
            Unggah file <strong>.CSV</strong> dengan urutan kolom: <br>
            <code class="text-xs text-primary bg-surface-container px-1.5 py-0.5 rounded">SKU, Nama Produk, Varian,
                Kategori, Isi Per Box</code>
        </p>
        <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data"
            class="flex flex-col gap-4" data-confirm-title="Mulai impor CSV?"
            data-confirm-message="File akan diproses untuk menambahkan produk baru. SKU duplikat akan dilewati."
            data-confirm-confirm-label="Ya, mulai impor" data-confirm-variant="tertiary">
            @csrf
            <input type="file" name="file" accept=".csv,.txt" required
                class="w-full text-body-sm text-on-surface file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-tertiary/15 file:text-tertiary file:font-semibold hover:file:bg-tertiary/25 bg-surface-container rounded-xl p-2 cursor-pointer" />
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalImportCsv').classList.add('hidden')"
                    class="h-10 px-4 rounded-xl bg-surface-container text-on-surface-variant font-label-md cursor-pointer">Batal</button>
                <button type="submit"
                    class="h-10 px-5 rounded-xl bg-tertiary text-on-tertiary font-headline-sm font-bold cursor-pointer">Mulai
                    Import</button>
            </div>
        </form>
    </div>
</div>