{{-- ==================== MODAL INPUT FAKTUR BARANG MASUK ==================== --}}
<div id="modalInputFaktur"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-surface-container-lowest/80 backdrop-blur-md {{ $errors->any() ? '' : 'hidden' }}">
    <div class="w-full max-w-4xl max-h-[92vh] bg-surface-container-low rounded-2xl border border-outline-variant/30 shadow-2xl flex flex-col overflow-hidden">

        {{-- Header Modal --}}
        <div class="px-space-lg py-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest shrink-0">
            <div class="flex items-center gap-space-sm">
                <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[22px]">receipt</span>
                </div>
                <div>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Input Faktur Penerimaan Barang (PO Kulakan)</h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Kalkulator Box-to-Pcs otomatis menghitung harga modal dasar per satuan.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="resetInputForm()"
                        class="px-space-sm py-1.5 rounded-lg bg-surface-container-high text-on-surface-variant hover:text-on-surface text-body-sm flex items-center gap-1 transition-colors cursor-pointer"
                        title="Reset isian form">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    <span class="hidden sm:inline">Reset</span>
                </button>
                <button type="button" onclick="closeInputFakturModal()"
                        class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright flex items-center justify-center text-on-surface-variant hover:text-on-surface cursor-pointer"
                        title="Tutup Modal (ESC)">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
        </div>

        {{-- Body Form (Scrollable) --}}
        <form method="POST" action="{{ route('incoming-goods.store') }}" id="fakturForm" class="p-space-lg flex flex-col gap-space-md overflow-y-auto">
            @csrf
            <input type="hidden" name="payment_status" id="paymentStatusInput" value="{{ old('payment_status', 'lunas') }}">

            {{-- Error validation --}}
            @if ($errors->any())
                <div class="p-space-sm rounded-xl bg-error/10 border border-error/30 text-error font-body-sm">
                    <strong class="flex items-center gap-1 mb-1">
                        <span class="material-symbols-outlined text-[16px]">error</span> Periksa kembali:
                    </strong>
                    <ul class="list-disc list-inside ml-1">
                        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            {{-- Baris 1: Supplier / No Faktur / Tanggal / Metode Bayar --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-space-md">
                <div class="md:col-span-6 flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase flex items-center justify-between">
                        <span>Pemasok / Supplier</span>
                        <span class="text-primary text-[10px] lowercase font-normal">*wajib dipilih</span>
                    </label>
                    <div class="relative">
                        <select name="supplier_id" id="supplierSelect" required
                                class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-md text-body-md focus:outline-none focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" @selected(old('supplier_id', $suppliers->first()?->id) == $s->id)>
                                    {{ $s->name }}@if($s->type) ({{ $s->type }})@endif
                                </option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[18px]">expand_more</span>
                    </div>
                </div>

                <div class="md:col-span-6 flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase">No. Faktur / Surat Jalan (SJ)</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">pin</span>
                        <input name="no_faktur" id="noFakturInput" type="text" required
                               value="{{ old('no_faktur', 'DO-' . now('Asia/Jakarta')->format('Y/m') . '/' . rand(1000, 9999)) }}"
                               class="w-full h-11 pl-10 pr-space-md rounded-xl bg-surface-container text-on-surface font-label-md text-body-md focus:outline-none focus:ring-1 focus:ring-primary uppercase tracking-wider" />
                    </div>
                </div>

                <div class="md:col-span-5 flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase">Tgl Masuk Gudang</label>
                    <input name="received_at" id="receivedAtInput" type="date" required
                           value="{{ old('received_at', now('Asia/Jakarta')->format('Y-m-d')) }}"
                           class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-md text-body-md focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer" />
                </div>

                <div class="md:col-span-7 flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase flex justify-between">
                        <span>Metode Bayar</span>
                        <span id="tempoBadgeLabel" class="text-label-sm font-label-sm text-primary">Lunas Kas Toko</span>
                    </label>
                    <div class="h-11 p-1 bg-surface-container rounded-xl flex items-center gap-1">
                        <button type="button" onclick="setPaymentStatus('lunas')" id="btnStatusLunas"
                                class="flex-1 h-full rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all bg-primary-container text-on-primary-container font-semibold shadow-sm cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">check_circle</span>
                            <span>Lunas</span>
                        </button>
                        <button type="button" onclick="setPaymentStatus('tempo')" id="btnStatusTempo"
                                class="flex-1 h-full rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all text-on-surface-variant hover:text-on-surface cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">schedule</span>
                            <span>Tempo / Hutang</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tempo block --}}
            <div id="tempoDetailsWrapper" class="hidden p-space-md rounded-xl bg-error/10 border border-error/25 flex flex-col md:flex-row items-center justify-between gap-space-md">
                <div class="flex items-center gap-space-sm text-error">
                    <span class="material-symbols-outlined text-[20px]">warning</span>
                    <span class="font-body-sm text-body-sm">Pembayaran Kredit Supplier: Tercatat sebagai <strong>Utang Supplier</strong> & muncul di pengingat jatuh tempo Dashboard.</span>
                </div>
                <div class="flex items-center gap-space-sm w-full md:w-auto">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase whitespace-nowrap">Jatuh Tempo:</label>
                    <input name="due_date" id="inputJatuhTempo" type="date"
                           value="{{ old('due_date', now('Asia/Jakarta')->addDays(30)->format('Y-m-d')) }}"
                           class="h-9 px-space-md rounded-lg bg-surface-container-high text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-error" />
                </div>
            </div>

            {{-- Line Builder --}}
            <div class="p-space-md rounded-xl bg-surface-container-lowest/80 border border-outline-variant/15">
                <div class="flex items-center justify-between mb-space-sm">
                    <span class="font-label-md text-label-md text-on-surface uppercase tracking-wider flex items-center gap-1">
                        <span class="material-symbols-outlined text-primary text-[18px]">inventory_2</span>
                        Katalog Barang & Konversi Fisik
                    </span>
                    <span class="text-label-sm font-label-sm text-on-surface-variant">Konversi otomatis berdasar Master Barang</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-space-md items-end">
                    <div class="md:col-span-12 flex flex-col gap-1.5">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Pilih Master Barang</label>
                        <div class="relative">
                            <select name="product_id" id="productSelect" onchange="onProductChange(true)" required
                                    class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-headline-sm text-body-md focus:outline-none focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}"
                                            data-ratio="{{ $p->box_pcs_ratio }}"
                                            data-sku="{{ $p->sku }}"
                                            data-barcode="{{ $p->barcode }}"
                                            data-name="{{ $p->name }}"
                                            data-last-price="{{ (int) ($p->last_price ?? 0) }}"
                                            @selected(old('product_id', $products->first()?->id) == $p->id)>
                                        {{ $p->sku }} — {{ $p->name }} (1 Box = {{ $p->box_pcs_ratio }} Pcs)
                                    </option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[18px]">expand_more</span>
                        </div>
                    </div>

                    <div class="md:col-span-3 flex flex-col gap-1.5">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Jumlah Box Masuk</label>
                        <div class="relative">
                            <input name="box_qty" id="inputBoxQty" type="number" min="1" step="1" required
                                   value="{{ old('box_qty', 25) }}" oninput="calculateRealtime()"
                                   class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-headline-sm text-headline-sm focus:outline-none focus:ring-1 focus:ring-primary text-center font-bold" />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 font-label-sm text-label-sm text-on-surface-variant uppercase">BOX</span>
                        </div>
                    </div>

                    <div class="md:col-span-4 flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Harga Modal / Box</label>
                            <span class="text-[10px] text-tertiary" id="hppLaluLabel">Modal Lalu: —</span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 font-label-md text-label-md text-on-surface-variant">Rp</span>
                            <input name="price_per_box" id="inputHargaBox" type="number" step="1" min="1" required
                                   placeholder="Contoh: 145000"
                                   value="{{ old('price_per_box', '') }}" oninput="calculateRealtime()"
                                   class="w-full h-11 pl-10 pr-space-md rounded-xl bg-surface-container text-primary font-headline-sm text-headline-sm focus:outline-none focus:ring-1 focus:ring-primary font-bold" />
                        </div>
                    </div>

                    <div class="md:col-span-5 flex items-center justify-between p-space-sm bg-surface-container rounded-xl h-11">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-primary text-[18px]">all_inbox</span>
                            <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Total:</span>
                            <span id="outputTotalPcs" class="font-headline-sm text-body-md text-on-surface font-extrabold">0 Pcs</span>
                        </div>
                        <div class="h-5 w-px bg-surface-container-highest"></div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-label-sm text-label-sm text-tertiary uppercase">Modal/Pcs:</span>
                            <span id="outputModalPcs" class="font-headline-sm text-body-md text-primary font-extrabold">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary + Footer Actions --}}
            <div class="pt-space-md border-t border-surface-container-highest flex flex-col sm:flex-row items-center justify-between gap-space-md">
                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <div class="px-3.5 py-2 rounded-xl bg-surface-container flex items-center justify-between sm:justify-start gap-2">
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Subtotal Belanja:</span>
                        <span id="outputTotalBelanja" class="font-headline-md text-headline-md text-on-surface font-black">Rp 0</span>
                    </div>
                    <div class="flex items-center gap-1 text-[11px] text-on-surface-variant">
                        <span class="material-symbols-outlined text-[14px] text-primary">lock_clock</span>
                        <span id="lockInfo">Harga per Box akan mengunci acuan margin laba Barang Keluar.</span>
                    </div>
                </div>

                <div class="flex items-center gap-space-sm w-full sm:w-auto justify-end">
                    <button type="button" onclick="closeInputFakturModal()"
                            class="h-11 px-space-lg rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-headline-sm text-body-md transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="button" onclick="confirmSubmitFaktur()"
                            class="h-11 px-space-lg rounded-xl bg-primary-container text-on-primary-container font-headline-sm text-body-md font-bold flex items-center justify-center gap-space-xs hover:bg-primary transition-all shadow-[0_0_20px_rgba(16,185,129,0.35)] active:scale-95 cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">save</span>
                        <span>Simpan & Tambah Stok</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>