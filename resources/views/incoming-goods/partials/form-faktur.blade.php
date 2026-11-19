{{-- ==================== MODAL INPUT FAKTUR BARANG MASUK ==================== --}}
<div id="modalInputFaktur"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-surface-container-lowest/80 backdrop-blur-md {{ $errors->any() ? '' : 'hidden' }}">
    <div
        class="w-full max-w-4xl max-h-[92vh] bg-surface-container-low rounded-2xl border border-outline-variant/30 shadow-2xl flex flex-col overflow-hidden">

        {{-- Header Modal --}}
        <div
            class="px-space-lg py-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest shrink-0">
            <div class="flex items-center gap-space-sm">
                <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[22px]">receipt</span>
                </div>
                <div>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Input Faktur Penerimaan
                        Barang (PO Kulakan)</h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Kalkulator Box-to-Pcs otomatis
                        menghitung harga modal dasar per satuan.</p>
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
        <form method="POST" action="{{ route('incoming-goods.store') }}" id="fakturForm"
            class="p-space-lg flex flex-col gap-space-md overflow-y-auto">
            @csrf
            <input type="hidden" name="payment_status" id="paymentStatusInput"
                value="{{ old('payment_status', 'lunas') }}">
            <input type="hidden" name="no_faktur" id="noFakturInput" value="{{ $nextNoFaktur }}">

            {{-- Error validation --}}
            @if ($errors->any())
                <div class="p-space-sm rounded-xl bg-error/10 border border-error/30 text-error font-body-sm">
                    <strong class="flex items-center gap-1 mb-1">
                        <span class="material-symbols-outlined text-[16px]">error</span> Periksa kembali:
                    </strong>
                    <ul class="list-disc list-inside ml-1">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Peringatan jika Master Barang kosong --}}
            @if ($products->isEmpty())
                <div
                    class="p-space-md rounded-xl bg-error/10 border border-error/30 text-error font-body-sm flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                        <span>Belum ada barang aktif di <strong>Master Barang</strong>. Tambahkan produk terlebih dahulu
                            sebelum mencatat kulakan.</span>
                    </div>
                    <a href="{{ route('products.index') }}"
                        class="px-3 py-1.5 rounded-lg bg-error text-on-error font-bold text-xs whitespace-nowrap">
                        Ke Master Barang
                    </a>
                </div>
            @endif

            {{-- Kartu Pratinjau Nomor Faktur Otomatis --}}
            <div
                class="px-space-md py-2.5 rounded-xl bg-surface-container-lowest/90 border border-primary/25 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-lg bg-primary/15 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[18px]">tag</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-label-sm text-on-surface-variant uppercase tracking-wider">No.
                            Faktur Otomatis (Mengikuti Bulan Tgl Masuk Gudang)</span>
                        <span id="noFakturPreview"
                            class="font-mono font-extrabold text-body-md text-primary tracking-wider">{{ $nextNoFaktur }}</span>
                    </div>
                </div>
                <span
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container text-[11px] text-on-surface-variant">
                    <span class="material-symbols-outlined text-[14px] text-primary">auto_awesome</span>
                    Otomatis & Anti-Duplikat
                </span>
            </div>

            {{-- Baris 1: Supplier / Tanggal / Metode Bayar --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-space-md">
                <div class="md:col-span-5 flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <label class="font-label-md text-label-md text-on-surface-variant uppercase">Pemasok /
                            Supplier</label>
                        <button type="button" onclick="toggleQuickSupplierBox()"
                            class="text-[11px] font-bold text-primary hover:underline flex items-center gap-0.5 cursor-pointer">
                            <span class="material-symbols-outlined text-[14px]">add</span>
                            <span>Supplier Baru</span>
                        </button>
                    </div>
                    <div class="relative">
                        <select name="supplier_id" id="supplierSelect" required
                            class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-md text-body-md focus:outline-none focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                            @if ($suppliers->isEmpty())
                                <option value="">-- Klik "+ Supplier Baru" di atas --</option>
                            @endif
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->id }}" @selected(old('supplier_id', $suppliers->first()?->id) == $s->id)>
                                    {{ $s->name }}@if ($s->type)
                                        ({{ $s->type }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <span
                            class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[18px]">expand_more</span>
                    </div>
                </div>

                <div class="md:col-span-3 flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase">Tgl Masuk
                        Gudang</label>
                    <input name="received_at" id="receivedAtInput" type="date" required
                        value="{{ old('received_at', now('Asia/Jakarta')->format('Y-m-d')) }}"
                        onchange="refreshNoFaktur()"
                        class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-md text-body-md focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer" />
                </div>

                <div class="md:col-span-4 flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface-variant uppercase flex justify-between">
                        <span>Metode Bayar</span>
                        <span id="tempoBadgeLabel" class="text-label-sm font-label-sm text-primary">Lunas</span>
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
                            <span>Tempo</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Inline Box Tambah Supplier Baru --}}
            <div id="quickSupplierBox"
                class="hidden p-space-md rounded-xl bg-surface-container-high/80 border border-primary/35 flex flex-col gap-space-sm">
                <div class="flex items-center justify-between">
                    <span
                        class="font-label-md text-label-md text-primary uppercase font-bold flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">local_shipping</span>
                        Tambah Supplier Baru
                    </span>
                    <button type="button" onclick="toggleQuickSupplierBox(false)"
                        class="text-on-surface-variant hover:text-on-surface cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-space-sm items-end">
                    <div class="sm:col-span-5 flex flex-col gap-1">
                        <label class="text-[11px] text-on-surface-variant">Nama Supplier / PT / CV *</label>
                        <input type="text" id="qsName" placeholder="Contoh: PT Sumber Makmur Jaya"
                            class="w-full h-10 px-3 rounded-lg bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                    </div>
                    <div class="sm:col-span-3 flex flex-col gap-1">
                        <label class="text-[11px] text-on-surface-variant">Jenis Supplier</label>
                        <select id="qsType"
                            class="w-full h-10 px-3 rounded-lg bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="Distributor Resmi">Distributor Resmi</option>
                            <option value="Agen Grosir">Agen Grosir</option>
                            <option value="Pabrik / Prinsipal">Pabrik / Prinsipal</option>
                            <option value="Supplier Umum">Supplier Umum</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2 flex flex-col gap-1">
                        <label class="text-[11px] text-on-surface-variant">No. HP / WA</label>
                        <input type="text" id="qsPhone" placeholder="0812..."
                            class="w-full h-10 px-3 rounded-lg bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                    </div>
                    <div class="sm:col-span-2">
                        <button type="button" id="qsSubmitBtn" onclick="submitQuickSupplier()"
                            class="w-full h-10 px-3 rounded-lg bg-primary-container text-on-primary-container hover:bg-primary font-headline-sm text-body-sm font-bold flex items-center justify-center gap-1 cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">check</span>
                            <span>Simpan</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Sumber Dana Lunas Block (Sinkron dengan CashOut::ACCOUNTS) --}}
            <div id="lunasAccountWrapper"
                class="p-space-md rounded-xl bg-primary/10 border border-primary/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-space-md">
                <div class="flex items-center gap-space-sm text-primary">
                    <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
                    <span class="font-body-sm text-body-sm text-on-surface">Pembelian Lunas: Otomatis dicatat ke
                        <strong>Kas Keluar</strong> dari akun yang dipilih.</span>
                </div>
                <div class="flex items-center gap-space-sm w-full sm:w-auto">
                    <label for="inputPaymentAccount"
                        class="font-label-md text-label-md text-on-surface-variant uppercase whitespace-nowrap">Akun
                        Kas:</label>
                    <select name="payment_account" id="inputPaymentAccount"
                        class="h-9 px-space-md rounded-lg bg-surface-container-high text-on-surface font-body-sm font-semibold focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer">
                        @foreach ($accounts as $accKey => $accInfo)
                            <option value="{{ $accKey }}" @selected(old('payment_account', 'kas_tunai') === $accKey)>
                                {{ $accInfo['label'] ?? ucfirst($accKey) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Tempo block --}}
            <div id="tempoDetailsWrapper"
                class="hidden p-space-md rounded-xl bg-error/10 border border-error/25 flex flex-col md:flex-row items-center justify-between gap-space-md">
                <div class="flex items-center gap-space-sm text-error">
                    <span class="material-symbols-outlined text-[20px]">warning</span>
                    <span class="font-body-sm text-body-sm">Pembayaran Kredit Supplier: Tercatat sebagai <strong>Utang
                            Supplier</strong> & muncul di pengingat jatuh tempo Dashboard.</span>
                </div>
                <div class="flex items-center gap-space-sm w-full md:w-auto">
                    <label
                        class="font-label-md text-label-md text-on-surface-variant uppercase whitespace-nowrap">Jatuh
                        Tempo:</label>
                    <input name="due_date" id="inputJatuhTempo" type="date"
                        value="{{ old('due_date', now('Asia/Jakarta')->addDays(30)->format('Y-m-d')) }}"
                        class="h-9 px-space-md rounded-lg bg-surface-container-high text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-error" />
                </div>
            </div>

            {{-- Line Builder --}}
            <div class="p-space-md rounded-xl bg-surface-container-lowest/80 border border-outline-variant/15">
                <div class="flex items-center justify-between mb-space-sm">
                    <span
                        class="font-label-md text-label-md text-on-surface uppercase tracking-wider flex items-center gap-1">
                        <span class="material-symbols-outlined text-primary text-[18px]">inventory_2</span>
                        Katalog Barang & Konversi Fisik
                    </span>
                    <span class="text-label-sm font-label-sm text-on-surface-variant">Konversi otomatis berdasar Master
                        Barang</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-space-md items-end">
                    <div class="md:col-span-12 flex flex-col gap-1.5">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Pilih Master
                            Barang</label>
                        <div class="relative">
                            <select name="product_id" id="productSelect" onchange="onProductChange(true)" required
                                class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-headline-sm text-body-md focus:outline-none focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                                @foreach ($products as $p)
                                    <option value="{{ $p->id }}" data-ratio="{{ $p->box_pcs_ratio }}"
                                        data-sku="{{ $p->sku }}" data-name="{{ $p->name }}"
                                        data-last-price="{{ (int) ($p->last_price ?? 0) }}"
                                        @selected(old('product_id', $selectedProductId ?? $products->first()?->id) == $p->id)>
                                        {{ $p->sku }} — {{ $p->name }} (1 Box = {{ $p->box_pcs_ratio }}
                                        Pcs)
                                    </option>
                                @endforeach
                            </select>
                            <span
                                class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[18px]">expand_more</span>
                        </div>
                    </div>

                    {{-- Stepper Jumlah Box Masuk (Tombol - / + Besar & Empuk Diklik) --}}
                    <div class="md:col-span-4 flex flex-col gap-1.5">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Jumlah Box
                            Masuk</label>
                        <div
                            class="flex items-center h-11 rounded-xl bg-surface-container p-1 gap-1 border border-transparent focus-within:border-primary transition-colors">
                            <button type="button" onclick="adjustBoxQty(-1)"
                                class="w-9 h-9 rounded-lg bg-surface-container-high hover:bg-surface-bright active:scale-95 text-on-surface flex items-center justify-center transition-all cursor-pointer shrink-0"
                                title="Kurangi 1 Box">
                                <span class="material-symbols-outlined text-[18px]">remove</span>
                            </button>
                            <div class="relative flex-1 flex items-center justify-center px-1 min-w-0">
                                <input name="box_qty" id="inputBoxQty" type="text" inputmode="numeric" required
                                    value="{{ old('box_qty', 10) }}" oninput="sanitizeBoxQty(this)"
                                    class="w-full h-9 bg-transparent text-on-surface font-headline-sm text-headline-sm text-center font-bold focus:outline-none" />
                                <span
                                    class="text-[10px] font-label-sm text-on-surface-variant uppercase pr-1 select-none shrink-0">BOX</span>
                            </div>
                            <button type="button" onclick="adjustBoxQty(1)"
                                class="w-9 h-9 rounded-lg bg-primary/15 hover:bg-primary/25 active:scale-95 text-primary flex items-center justify-center transition-all cursor-pointer shrink-0"
                                title="Tambah 1 Box">
                                <span class="material-symbols-outlined text-[18px]">add</span>
                            </button>
                        </div>
                    </div>

                    <div class="md:col-span-4 flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Harga Modal /
                                Box</label>
                            <span class="text-[10px] text-tertiary" id="hppLaluLabel">Modal Lalu: —</span>
                        </div>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 -translate-y-1/2 font-label-md text-label-md text-on-surface-variant">Rp</span>
                            <input name="price_per_box" id="inputHargaBox" type="text" inputmode="numeric"
                                required placeholder="Contoh: 145.000"
                                value="{{ old('price_per_box') ? number_format((int) preg_replace('/\D+/', '', old('price_per_box')), 0, ',', '.') : '' }}"
                                oninput="formatHargaBoxInput(this)"
                                class="w-full h-11 pl-10 pr-space-md rounded-xl bg-surface-container text-primary font-headline-sm text-headline-sm focus:outline-none focus:ring-1 focus:ring-primary font-bold" />
                        </div>
                    </div>

                    <div
                        class="md:col-span-4 flex items-center justify-between px-3 bg-surface-container rounded-xl h-11">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-label-sm text-on-surface-variant uppercase">Total Fisik</span>
                            <span id="outputTotalPcs"
                                class="font-headline-sm text-body-sm text-on-surface font-extrabold leading-tight">0
                                Pcs</span>
                        </div>
                        <div class="h-6 w-px bg-surface-container-highest"></div>
                        <div class="flex flex-col text-right">
                            <span class="text-[9px] font-label-sm text-tertiary uppercase">Modal / Pcs</span>
                            <span id="outputModalPcs"
                                class="font-headline-sm text-body-sm text-primary font-extrabold leading-tight">Rp
                                0</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary + Footer Actions --}}
            <div
                class="pt-space-md border-t border-surface-container-highest flex flex-col sm:flex-row items-center justify-between gap-space-md">
                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <div
                        class="px-3.5 py-2 rounded-xl bg-surface-container flex items-center justify-between sm:justify-start gap-2">
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Subtotal
                            Belanja:</span>
                        <span id="outputTotalBelanja"
                            class="font-headline-md text-headline-md text-on-surface font-black">Rp 0</span>
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
                    <button type="button" onclick="confirmSubmitFaktur()" @disabled($products->isEmpty())
                        class="h-11 px-space-lg rounded-xl bg-primary-container text-on-primary-container font-headline-sm text-body-md font-bold flex items-center justify-center gap-space-xs hover:bg-primary transition-all shadow-[0_0_20px_rgba(16,185,129,0.35)] active:scale-95 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="material-symbols-outlined text-[20px]">save</span>
                        <span>Simpan & Tambah Stok</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>