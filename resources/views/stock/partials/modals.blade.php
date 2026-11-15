{{-- ==================== MODAL 1: ATUR BATAS ALERT ==================== --}}
<div id="modalAlertThreshold" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-surface-container-lowest/80 backdrop-blur-md">
    <div class="w-full max-w-lg bg-surface-container-low rounded-2xl border border-outline-variant/30 shadow-2xl overflow-hidden">
        <div class="px-space-lg py-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest">
            <div class="flex items-center gap-space-sm">
                <div class="w-10 h-10 rounded-xl bg-tertiary/15 text-tertiary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">tune</span>
                </div>
                <div>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Atur Batas Peringatan Stok (Alert)</h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Tentukan ambang batas minimum (Box) sebelum status berubah menjadi Menipis.</p>
                </div>
            </div>
            <button type="button" data-action="close-modal" data-target="modalAlertThreshold" class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright flex items-center justify-center text-on-surface-variant hover:text-on-surface cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('stock.alert-threshold') }}" class="p-space-lg flex flex-col gap-space-md">
            @csrf
            <div class="flex flex-col gap-1.5">
                <label class="font-label-md text-label-md text-on-surface-variant uppercase">Cakupan Pengaturan</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="p-3 rounded-xl bg-surface-container border border-outline-variant/20 flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="scope" value="single" id="alertScopeSingle" checked class="accent-primary">
                        <span class="text-body-sm font-semibold text-on-surface">Per Produk Tertentu</span>
                    </label>
                    <label class="p-3 rounded-xl bg-surface-container border border-outline-variant/20 flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="scope" value="all" id="alertScopeAll" class="accent-primary">
                        <span class="text-body-sm font-semibold text-on-surface">Semua Produk Aktif</span>
                    </label>
                </div>
            </div>

            <div id="alertProductWrapper" class="flex flex-col gap-1.5">
                <label class="font-label-md text-label-md text-on-surface-variant uppercase">Pilih Produk</label>
                <select name="product_id" id="alertProductSelect"
                        class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-md text-body-md focus:outline-none focus:ring-1 focus:ring-primary">
                    @foreach($productOptions as $opt)
                        <option value="{{ $opt->id }}" data-threshold="{{ $opt->min_stock_box ?? 10 }}">
                            {{ $opt->sku }} — {{ $opt->name }} (Saat ini: {{ $opt->min_stock_box ?? 10 }} Box)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="font-label-md text-label-md text-on-surface-variant uppercase">Batas Minimum Stok (Dalam Satuan Box)</label>
                <div class="relative">
                    <input type="number" name="min_stock_box" id="alertMinBoxInput" min="1" max="10000" value="10" required
                           class="w-full h-11 px-space-md rounded-xl bg-surface-container text-tertiary font-headline-sm text-headline-sm font-bold focus:outline-none focus:ring-1 focus:ring-tertiary" />
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-label-sm text-on-surface-variant font-bold">BOX</span>
                </div>
            </div>

            <div class="pt-space-sm border-t border-surface-container-highest flex items-center justify-end gap-space-sm">
                <button type="button" data-action="close-modal" data-target="modalAlertThreshold" class="h-10 px-space-md rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-body-sm cursor-pointer">Batal</button>
                <button type="submit" class="h-10 px-space-lg rounded-xl bg-primary-container text-on-primary-container hover:bg-primary font-headline-sm text-body-sm font-bold flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    <span>Simpan Batas Alert</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ==================== MODAL 2: OPNAME STOK FISIK ==================== --}}
<div id="modalOpnameStok" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-surface-container-lowest/80 backdrop-blur-md">
    <div class="w-full max-w-xl bg-surface-container-low rounded-2xl border border-outline-variant/30 shadow-2xl overflow-hidden">
        <div class="px-space-lg py-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest">
            <div class="flex items-center gap-space-sm">
                <div class="w-10 h-10 rounded-xl bg-primary/15 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">inventory</span>
                </div>
                <div>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Opname & Penyesuaian Stok Fisik</h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Input hasil hitung fisik nyata di gudang (Box Utuh + Sisa Eceran Pcs).</p>
                </div>
            </div>
            <button type="button" data-action="close-modal" data-target="modalOpnameStok" class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright flex items-center justify-center text-on-surface-variant hover:text-on-surface cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('stock.opname') }}" id="opnameForm" class="p-space-lg flex flex-col gap-space-md">
            @csrf
            <div class="flex flex-col gap-1.5">
                <label class="font-label-md text-label-md text-on-surface-variant uppercase">Pilih Produk yang Diopname</label>
                <select name="product_id" id="opnameProductSelect" required
                        class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-md text-body-md focus:outline-none focus:ring-1 focus:ring-primary">
                    @foreach($productOptions as $opt)
                        @php $r = max(1, (int) $opt->box_pcs_ratio); @endphp
                        <option value="{{ $opt->id }}"
                                data-ratio="{{ $r }}"
                                data-stock-pcs="{{ (int) $opt->stock_pcs }}"
                                data-box="{{ intdiv((int) $opt->stock_pcs, $r) }}"
                                data-rem="{{ (int) $opt->stock_pcs % $r }}">
                            {{ $opt->sku }} — {{ $opt->name }} (Sistem: {{ intdiv((int) $opt->stock_pcs, $r) }} Box + {{ (int) $opt->stock_pcs % $r }} Pcs)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-space-md">
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase">Fisik Box Utuh</label>
                    <input type="number" name="physical_box" id="opnamePhysicalBox" min="0" step="1" value="0" required
                           class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-headline-sm text-headline-sm font-bold text-center focus:outline-none focus:ring-1 focus:ring-primary" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase">Fisik Eceran (Pcs)</label>
                    <input type="number" name="physical_pcs" id="opnamePhysicalPcs" min="0" step="1" value="0" required
                           class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-headline-sm text-headline-sm font-bold text-center focus:outline-none focus:ring-1 focus:ring-primary" />
                </div>
            </div>

            <div class="p-space-md rounded-xl bg-surface-container border border-outline-variant/15 flex items-center justify-between text-body-sm">
                <div>
                    <div class="text-on-surface-variant text-label-sm">Stok Tercatat di Sistem</div>
                    <div id="opnameSystemLabel" class="font-bold text-on-surface">0 Pcs</div>
                </div>
                <div class="text-center">
                    <div class="text-on-surface-variant text-label-sm">Total Fisik Baru</div>
                    <div id="opnameNewTotalLabel" class="font-extrabold text-primary">0 Pcs</div>
                </div>
                <div class="text-right">
                    <div class="text-on-surface-variant text-label-sm">Selisih Penyesuaian</div>
                    <div id="opnameDiffLabel" class="font-extrabold text-tertiary">0 Pcs</div>
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="font-label-md text-label-md text-on-surface-variant uppercase">Catatan Audit (Opsional)</label>
                <input type="text" name="notes" placeholder="Contoh: Hasil stok opname mingguan rak A2 / kemasan rusak 2 pcs"
                       class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
            </div>

            <div class="pt-space-sm border-t border-surface-container-highest flex items-center justify-end gap-space-sm">
                <button type="button" data-action="close-modal" data-target="modalOpnameStok" class="h-10 px-space-md rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-body-sm cursor-pointer">Batal</button>
                <button type="submit" class="h-10 px-space-lg rounded-xl bg-primary-container text-on-primary-container hover:bg-primary font-headline-sm text-body-sm font-bold flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    <span>Simpan Hasil Opname</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ==================== MODAL 3: RIWAYAT MUTASI (KARTU STOK) ==================== --}}
<div id="modalMutationHistory" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-surface-container-lowest/80 backdrop-blur-md">
    <div class="w-full max-w-3xl max-h-[88vh] bg-surface-container-low rounded-2xl border border-outline-variant/30 shadow-2xl flex flex-col overflow-hidden">
        <div class="px-space-lg py-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest shrink-0">
            <div class="flex items-center gap-space-sm">
                <div class="w-10 h-10 rounded-xl bg-primary/15 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">history</span>
                </div>
                <div>
                    <h2 id="mutProductName" class="font-headline-sm text-headline-sm text-on-surface font-bold">Kartu Stok & Riwayat Mutasi</h2>
                    <p id="mutProductMeta" class="font-body-sm text-body-sm text-on-surface-variant">Memuat riwayat transaksi masuk & keluar...</p>
                </div>
            </div>
            <button type="button" data-action="close-modal" data-target="modalMutationHistory" class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright flex items-center justify-center text-on-surface-variant hover:text-on-surface cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <div class="p-space-lg overflow-y-auto flex-1">
            <div id="mutLoadingState" class="py-10 text-center text-on-surface-variant text-body-sm">
                Mengambil data mutasi gudang...
            </div>
            <div id="mutTableWrapper" class="hidden overflow-x-auto rounded-xl border border-surface-container-highest">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container text-on-surface-variant font-label-sm text-label-sm uppercase">
                            <th class="py-2.5 px-3">Tanggal</th>
                            <th class="py-2.5 px-3">Arus</th>
                            <th class="py-2.5 px-3">No. Faktur / Ref</th>
                            <th class="py-2.5 px-3">Supplier / Pelanggan</th>
                            <th class="py-2.5 px-3 text-right">Kuantitas</th>
                            <th class="py-2.5 px-3 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody id="mutTableBody" class="divide-y divide-surface-container font-body-sm text-body-sm"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>