{{-- MODAL 0: INPUT PENJUALAN GROSIR (DISELARASKAN DENGAN INPUT FAKTUR BARANG MASUK) --}}
<div id="saleFormModal" onclick="if (event.target === this) closeModal('saleFormModal')"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm overflow-y-auto">
    <div
        class="w-full max-w-3xl my-auto rounded-2xl bg-surface-container-low border border-white/10 shadow-[0_24px_64px_rgba(0,0,0,0.65)] overflow-hidden">
        {{-- Modal Header --}}
        <div
            class="px-space-lg py-space-md border-b border-white/5 bg-surface-container-low flex items-center justify-between gap-space-sm">
            <div class="flex items-center gap-space-sm">
                <div class="w-10 h-10 rounded-xl bg-primary/15 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px]">point_of_sale</span>
                </div>
                <div>
                    <h2 class="font-headline-md text-headline-sm text-on-surface font-bold">Input Faktur Penjualan Grosir
                    </h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Kalkulator Box-to-Pcs otomatis
                        menghitung total tagihan dan laba bersih terkunci.</p>
                </div>
            </div>
            <div class="flex items-center gap-space-xs">
                @if (!$products->isEmpty())
                    <button type="button" onclick="confirmResetSaleForm()"
                        class="h-9 px-3 rounded-xl bg-surface-container hover:bg-surface-container-high border border-white/10 inline-flex items-center gap-1.5 font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors cursor-pointer"
                        title="Reset Form">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        <span>Reset</span>
                    </button>
                @endif
                <button type="button" onclick="closeModal('saleFormModal')"
                    class="w-9 h-9 rounded-xl bg-surface-container hover:bg-error/15 flex items-center justify-center text-on-surface-variant hover:text-error transition-colors cursor-pointer"
                    title="Tutup (ESC)">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div
                class="mx-space-lg mt-space-md p-space-sm rounded-xl bg-error/10 border border-error/30 text-error font-body-sm">
                <strong class="flex items-center gap-1 mb-1"><span
                        class="material-symbols-outlined text-[16px]">error</span> Periksa kembali:</strong>
                <ul class="list-disc list-inside ml-1">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($products->isEmpty())
            <div class="p-space-xl text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-[44px] text-surface-container-highest">inventory_2</span>
                <p class="font-body-md mt-2">Belum ada produk dengan stok tersedia. Silakan input <a
                        href="{{ route('incoming-goods.index') }}" class="text-primary underline font-semibold">Barang
                        Masuk</a> terlebih dahulu.</p>
                <button type="button" onclick="closeModal('saleFormModal')"
                    class="mt-space-md h-10 px-space-lg rounded-xl bg-surface-container-high text-on-surface font-semibold cursor-pointer">Tutup</button>
            </div>
        @else
            <form method="POST" action="{{ route('outgoing-goods.store') }}" id="saleForm"
                class="max-h-[84vh] overflow-y-auto">
                @csrf
                <input type="hidden" name="payment_status" id="paymentStatusInput"
                    value="{{ old('payment_status', 'lunas') }}">
                <input type="hidden" name="hpp_per_box" id="hppPerBoxInput" value="0">

                <div class="p-space-lg flex flex-col gap-space-md">

                    {{-- Banner No. Faktur Otomatis (Persis Seperti Barang Masuk) --}}
                    <div
                        class="px-space-md py-3 rounded-xl bg-primary/5 border border-primary/25 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-space-sm">
                            <div
                                class="w-9 h-9 rounded-lg bg-primary/15 text-primary flex items-center justify-center font-bold shrink-0">
                                #</div>
                            <div>
                                <span
                                    class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider block">No.
                                    Invoice Otomatis (Mengikuti Bulan Transaksi)</span>
                                <span
                                    class="font-headline-sm text-body-md text-primary font-extrabold tracking-wide">{{ $suggestedInvoice }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 text-primary font-label-sm text-label-sm font-semibold">
                            <span class="material-symbols-outlined text-[16px]">auto_awesome</span>
                            <span>Otomatis & Anti-Duplikat</span>
                        </div>
                    </div>

                    {{-- Row 1: Pelanggan, Waktu Realtime, Metode Bayar --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-space-md items-end">
                        <div class="md:col-span-5 flex flex-col gap-space-xs">
                            <label
                                class="font-label-sm text-label-sm text-on-surface-variant uppercase flex items-center justify-between">
                                <span>Pelanggan / Mitra</span>
                                <button type="button" onclick="toggleQuickCustomerBox()"
                                    class="text-primary hover:underline font-bold cursor-pointer">+ Pelanggan
                                    Baru</button>
                            </label>
                            <div class="relative">
                                <select name="customer_id" id="customerSelect" required
                                    class="w-full h-11 px-space-md pr-8 rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm font-semibold border border-white/10 focus:outline-none focus:border-primary appearance-none cursor-pointer">
                                    <option value="" disabled @selected(!old('customer_id'))>-- Pilih Pelanggan /
                                        Mitra --</option>
                                    @foreach ($customers as $c)
                                        <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>
                                            {{ $c->name }}@if ($c->type)
                                                ({{ $c->type }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <span
                                    class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <div class="md:col-span-4 flex flex-col gap-space-xs">
                            <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Waktu Transaksi
                                (Realtime)</label>
                            <div
                                class="h-11 px-space-md rounded-xl bg-surface-container border border-white/10 flex items-center justify-between">
                                <span id="liveSaleDateTime"
                                    class="font-label-md text-label-md text-on-surface font-semibold">{{ now()->format('d/m/Y • H:i:s') }}
                                    WIB</span>
                                <span
                                    class="material-symbols-outlined text-on-surface-variant text-[18px]">schedule</span>
                            </div>
                        </div>

                        <div class="md:col-span-3 flex flex-col gap-space-xs">
                            <label
                                class="font-label-sm text-label-sm text-on-surface-variant uppercase flex items-center justify-between">
                                <span>Metode Bayar</span>
                                <span id="paymentStatusBadge"
                                    class="font-label-sm text-label-sm text-primary font-extrabold uppercase">LUNAS</span>
                            </label>
                            <div class="grid grid-cols-2 gap-1.5">
                                <button type="button" onclick="setPaymentMode('lunas')" id="pay-lunas-btn"
                                    class="h-10 rounded-xl font-headline-sm text-body-sm flex items-center justify-center gap-1.5 transition-all bg-primary text-on-primary shadow-sm font-bold cursor-pointer">
                                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                    <span>Lunas</span>
                                </button>
                                <button type="button" onclick="setPaymentMode('tempo')" id="pay-tempo-btn"
                                    class="h-10 rounded-xl font-headline-sm text-body-sm flex items-center justify-center gap-1.5 transition-all bg-surface-container text-on-surface-variant hover:text-on-surface cursor-pointer">
                                    <span class="material-symbols-outlined text-[16px]">schedule</span>
                                    <span>Tempo</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Inline Tambah Pelanggan Baru (Persis Seperti + Supplier Baru di Barang Masuk) --}}
                    <div id="inlineCustomerBox"
                        class="hidden p-space-md rounded-xl bg-primary/5 border border-primary/25 flex flex-col gap-space-sm">
                        <div class="flex items-center justify-between">
                            <span
                                class="font-label-sm text-label-sm text-primary font-extrabold uppercase flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">storefront</span>
                                Tambah Pelanggan / Mitra Baru
                            </span>
                            <button type="button" onclick="toggleQuickCustomerBox(false)"
                                class="text-on-surface-variant hover:text-on-surface cursor-pointer">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-center">
                            <input type="text" id="quickCustName" placeholder="Nama Toko / Pelanggan *"
                                class="sm:col-span-5 h-10 px-3 rounded-xl bg-surface-container-low text-on-surface font-body-sm border border-white/10 focus:outline-none focus:border-primary" />
                            <select id="quickCustType"
                                class="sm:col-span-3 h-10 px-3 rounded-xl bg-surface-container-low text-on-surface font-body-sm border border-white/10">
                                <option value="Toko Kelontong">Toko Kelontong</option>
                                <option value="Minimarket">Minimarket</option>
                                <option value="Agen / Reseller">Agen / Reseller</option>
                                <option value="Warung">Warung</option>
                            </select>
                            <input type="text" id="quickCustPhone" placeholder="0812..."
                                class="sm:col-span-2 h-10 px-3 rounded-xl bg-surface-container-low text-on-surface font-body-sm border border-white/10" />
                            <button type="button" id="quickCustomerSubmit" onclick="submitQuickCustomer()"
                                class="sm:col-span-2 h-10 px-3 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-body-sm font-bold inline-flex items-center justify-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-[16px]">check</span>
                                <span>Simpan</span>
                            </button>
                        </div>
                        <p id="quickCustomerError" class="hidden text-error font-body-sm"></p>
                    </div>

                    {{-- Banner Akun Kas (Lunas) --}}
                    <div id="cash-account-picker"
                        class="px-space-md py-3 rounded-xl bg-primary/5 border border-primary/25 flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
                        <div class="flex items-center gap-2 text-on-surface-variant font-body-sm">
                            <span
                                class="material-symbols-outlined text-primary text-[18px]">account_balance_wallet</span>
                            <span>Penjualan Lunas: Otomatis dicatat ke <strong class="text-on-surface">Kas
                                    Masuk</strong> pada akun yang dipilih.</span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <label for="paymentAccountSelect"
                                class="font-label-sm text-on-surface-variant uppercase">Akun Kas:</label>
                            <select name="payment_account" id="paymentAccountSelect"
                                class="h-9 px-3 rounded-lg bg-surface-container-low text-on-surface font-body-sm font-semibold border border-white/10 focus:outline-none focus:border-primary">
                                @foreach (\App\Models\CashIn::ACCOUNTS as $accountKey => $account)
                                    <option value="{{ $accountKey }}" @selected(old('payment_account', 'kas_tunai') === $accountKey)>
                                        {{ $account['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Banner Jatuh Tempo (Tempo) --}}
                    <div id="tempo-due-picker"
                        class="hidden px-space-md py-3 rounded-xl bg-error/10 border border-error/25 flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
                        <div class="flex items-center gap-2 text-error font-body-sm">
                            <span class="material-symbols-outlined text-[18px]">event_busy</span>
                            <span>Penjualan Tempo: Faktur otomatis masuk ke <strong>Buku Tagihan
                                    Piutang</strong>.</span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <label for="dueDateInput" class="font-label-sm text-on-surface-variant uppercase">Jatuh
                                Tempo:</label>
                            <input name="due_date" id="dueDateInput" type="date"
                                value="{{ old('due_date', now()->addDays(14)->format('Y-m-d')) }}"
                                class="h-9 px-3 rounded-lg bg-surface-container-low text-on-surface font-label-md text-label-md border border-white/10 focus:outline-none focus:border-error" />
                        </div>
                    </div>

                    {{-- Kartu Katalog Barang & Konversi Fisik (Persis Seperti Barang Masuk) --}}
                    <div
                        class="p-space-md rounded-xl bg-surface-container border border-white/5 flex flex-col gap-space-md">
                        <div class="flex items-center justify-between">
                            <span
                                class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-primary text-[16px]">inventory_2</span>
                                Katalog Barang & Konversi Fisik
                            </span>
                            <span class="font-label-sm text-label-sm text-on-surface-variant">
                                Sisa Stok: <strong id="stockDisplay" class="text-primary">—</strong>
                            </span>
                        </div>

                        <div class="flex flex-col gap-space-xs">
                            <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Pilih Master
                                Barang (SKU)</label>
                            <div class="relative">
                                <select name="product_id" id="productSelect" onchange="onProductChange(true)"
                                    required
                                    class="w-full h-11 px-space-md pr-8 rounded-xl bg-surface-container-low text-on-surface font-headline-sm text-body-sm font-bold border border-white/10 focus:outline-none focus:border-primary appearance-none cursor-pointer">
                                    @foreach ($products as $p)
                                        @php $ratio = max(1, (int) $p->box_pcs_ratio); @endphp
                                        <option value="{{ $p->id }}" data-name="{{ $p->name }}"
                                            data-sku="{{ $p->sku }}"
                                            data-hpp="{{ (int) ($p->last_hpp ?? 0) }}"
                                            data-pcs-per-box="{{ $ratio }}" data-stock="{{ $p->stock_pcs }}"
                                            data-stock-box="{{ floor($p->stock_pcs / $ratio) }}"
                                            @selected(old('product_id') == $p->id)>
                                            {{ $p->sku }} — {{ $p->name }} (1 Box = {{ $ratio }}
                                            Pcs)
                                        </option>
                                    @endforeach
                                </select>
                                <span
                                    class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-space-md items-end pt-1">
                            {{-- Stepper Jumlah Box Keluar --}}
                            <div class="md:col-span-4 flex flex-col gap-space-xs">
                                <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Jumlah Box
                                    Keluar</label>
                                <div
                                    class="h-11 px-2 rounded-xl bg-surface-container-low border border-primary/40 flex items-center justify-between">
                                    <button type="button" onclick="adjustQty(-1)"
                                        class="w-8 h-8 rounded-lg hover:bg-surface-container text-on-surface flex items-center justify-center cursor-pointer">
                                        <span class="material-symbols-outlined text-[18px]">remove</span>
                                    </button>
                                    <div class="flex items-center gap-1.5">
                                        <input name="box_qty" id="qtyInput" type="number" min="1"
                                            value="{{ old('box_qty', 1) }}" required oninput="calculateProfit()"
                                            class="w-14 bg-transparent text-on-surface font-headline-md text-body-lg font-extrabold text-center focus:outline-none" />
                                        <span class="font-label-sm text-label-sm text-on-surface-variant">BOX</span>
                                    </div>
                                    <button type="button" onclick="adjustQty(1)"
                                        class="w-8 h-8 rounded-lg bg-primary/15 hover:bg-primary/25 text-primary flex items-center justify-center cursor-pointer">
                                        <span class="material-symbols-outlined text-[18px]">add</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Input Harga Jual / Box --}}
                            <div class="md:col-span-4 flex flex-col gap-space-xs">
                                <label
                                    class="font-label-sm text-label-sm text-on-surface-variant uppercase flex items-center justify-between">
                                    <span>Harga Jual / Box</span>
                                    <span id="hppBoxHint" class="text-tertiary font-bold normal-case">Modal HPP:
                                        —</span>
                                </label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3.5 top-1/2 -translate-y-1/2 font-label-md text-label-md text-on-surface-variant">Rp</span>
                                    <input type="hidden" name="sell_per_box" id="sellBoxInput"
                                        value="{{ old('sell_per_box', 0) }}">
                                    <input type="text" id="sellBoxDisplay" inputmode="numeric" required
                                        value="{{ old('sell_per_box') ? number_format((int) old('sell_per_box'), 0, ',', '.') : '' }}"
                                        oninput="onSellBoxDisplayInput(this)" placeholder="Contoh: 126.000"
                                        class="w-full h-11 pl-10 pr-space-md rounded-xl bg-surface-container-low text-on-surface font-headline-md text-body-lg font-extrabold border border-white/10 focus:outline-none focus:border-primary" />
                                </div>
                            </div>

                            {{-- Ringkasan Total Fisik & Jual / Pcs --}}
                            <div
                                class="md:col-span-4 grid grid-cols-2 gap-2 h-11 items-center pl-2 border-l border-white/10">
                                <div>
                                    <span
                                        class="font-label-sm text-label-sm text-on-surface-variant uppercase block leading-none">Total
                                        Fisik</span>
                                    <strong id="totalPcsDisplay"
                                        class="font-headline-sm text-body-md text-on-surface font-extrabold leading-tight">0
                                        Pcs</strong>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="font-label-sm text-label-sm text-tertiary uppercase block leading-none">Jual
                                        / Pcs</span>
                                    <strong id="sellPcsDisplay"
                                        class="font-headline-sm text-body-md text-primary font-extrabold leading-tight">Rp
                                        0</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Peringatan Harga di Bawah HPP --}}
                    <div id="belowCostWarning"
                        class="hidden p-space-sm rounded-xl bg-secondary-container/25 border border-secondary/40 flex items-center gap-space-sm text-secondary">
                        <span class="material-symbols-outlined text-[20px] shrink-0">warning</span>
                        <span class="font-body-sm text-body-sm">
                            <strong>Perhatian:</strong> Harga jual per Box lebih rendah dari HPP modal terakhir.
                            Transaksi ini akan mencatat <strong>rugi bersih</strong> — pastikan angka sudah sesuai.
                        </span>
                    </div>

                    {{-- Stock Error Banner --}}
                    <div id="stockErrorBanner"
                        class="hidden p-space-sm rounded-xl bg-error/15 border border-error/40 flex items-center justify-between text-error">
                        <div class="flex items-center gap-space-sm">
                            <span class="material-symbols-outlined text-[20px]">gpp_maybe</span>
                            <span class="font-body-sm text-body-sm font-semibold" id="stockErrorMsg">Jumlah melebihi
                                sisa stok fisik.</span>
                        </div>
                        <button type="button" onclick="resetQtyToMax()"
                            class="px-3 py-1 rounded-lg bg-error text-on-error font-headline-sm text-label-md cursor-pointer">
                            Set ke Maksimal
                        </button>
                    </div>
                </div>

                {{-- Footer Modal Seragam dengan Barang Masuk --}}
                <div
                    class="px-space-lg py-space-md bg-surface-container-low border-t border-white/5 flex flex-col gap-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="px-space-md py-2.5 rounded-xl bg-surface-container flex items-center gap-space-lg">
                            <div>
                                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Total
                                    Tagihan:</span>
                                <strong id="calcOmzet"
                                    class="font-display-lg text-headline-md text-on-surface font-extrabold tracking-tight">Rp
                                    0</strong>
                            </div>
                            <div class="h-8 w-px bg-white/10"></div>
                            <div>
                                <span
                                    class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Keuntungan
                                    Bersih:</span>
                                <div class="flex items-baseline gap-1.5">
                                    <strong id="calcProfit"
                                        class="font-display-lg text-headline-md text-primary font-extrabold tracking-tight">+Rp
                                        0</strong>
                                    <span id="calcMarginPct"
                                        class="font-label-sm text-label-sm text-primary font-bold">(Margin 0%)</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-auto">
                            <button type="button" onclick="closeModal('saleFormModal')"
                                class="h-11 px-5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-body-sm font-semibold transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="button" id="submitSaleBtn" onclick="confirmSubmitSale()"
                                class="h-11 px-5 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-body-sm font-bold inline-flex items-center gap-2 transition-all shadow-[0_0_20px_rgba(16,185,129,0.35)] cursor-pointer">
                                <span class="material-symbols-outlined text-[18px]">save</span>
                                <span>Simpan & Potong Stok</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 font-label-sm text-label-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-primary text-[15px]">lock</span>
                        <span id="footerLockHint">Modal HPP Rp 0/Box akan mengunci acuan margin laba transaksi ini
                            secara permanen.</span>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>

{{-- MODAL 1: DETAIL FAKTUR (IDENTIK DENGAN BARANG MASUK) --}}
<div id="invoiceDetailModal" onclick="if (event.target === this) closeModal('invoiceDetailModal')"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
    <div
        class="w-full max-w-lg rounded-2xl bg-surface-container-low border border-white/10 shadow-2xl overflow-hidden">
        <div
            class="px-space-lg py-space-md bg-surface-container-low border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-tertiary/15 text-tertiary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">receipt_long</span>
                </div>
                <div>
                    <h3 class="font-headline-md text-headline-sm text-on-surface font-extrabold" id="mdInvNo">
                        INV-000</h3>
                    <p class="font-label-sm text-label-sm text-on-surface-variant" id="mdSoldAt">—</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('invoiceDetailModal')"
                class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant flex items-center justify-center cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <div class="p-space-lg flex flex-col gap-space-md font-body-sm text-body-sm">
            <div
                class="grid grid-cols-2 gap-space-sm p-space-md rounded-xl bg-surface-container border border-white/5">
                <div>
                    <span class="text-label-sm text-on-surface-variant uppercase block">Pelanggan / Mitra</span>
                    <strong class="text-on-surface text-body-md font-bold block mt-0.5" id="mdCustomer">—</strong>
                    <span class="text-label-sm text-on-surface-variant" id="mdCustomerType">—</span>
                </div>
                <div class="text-right">
                    <span class="text-label-sm text-on-surface-variant uppercase block">Status Pembayaran</span>
                    <strong id="mdStatus" class="block mt-0.5">—</strong>
                    <span class="text-label-sm text-on-surface-variant">Jatuh Tempo: <strong class="text-on-surface"
                            id="mdDueDate">—</strong></span>
                </div>
            </div>

            <div class="p-space-md rounded-xl bg-surface-container border border-white/5 flex flex-col gap-3">
                <div>
                    <span class="font-label-sm text-label-sm text-tertiary font-bold uppercase"
                        id="mdSku">SKU-000</span>
                    <strong class="text-on-surface text-body-md font-bold block" id="mdProduct">—</strong>
                    <span class="text-label-sm text-on-surface-variant" id="mdRatio">Aturan Konversi: 1 Box = 1
                        Pcs</span>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-white/5">
                    <div>
                        <span class="text-label-sm text-on-surface-variant block">Kuantitas Keluar</span>
                        <strong class="text-on-surface text-body-md font-bold" id="mdQty">—</strong>
                    </div>
                    <div class="text-right">
                        <span class="text-label-sm text-on-surface-variant block">Keuntungan Bersih</span>
                        <strong class="text-primary text-body-md font-extrabold" id="mdProfit">—</strong>
                    </div>
                    <div>
                        <span class="text-label-sm text-on-surface-variant block">Modal HPP / Box</span>
                        <strong class="text-on-surface" id="mdHppBox">—</strong>
                    </div>
                    <div class="text-right">
                        <span class="text-label-sm text-on-surface-variant block">Harga Jual / Box</span>
                        <strong class="text-tertiary" id="mdSellBox">—</strong>
                    </div>
                </div>
            </div>

            <div
                class="px-space-md py-3.5 rounded-xl bg-primary/10 border border-primary/25 flex items-center justify-between">
                <span class="font-label-md text-label-md text-on-surface font-bold uppercase tracking-wider">Total
                    Nilai Faktur</span>
                <strong class="font-display-lg text-headline-md text-primary font-extrabold" id="mdSubtotal">Rp
                    0</strong>
            </div>
        </div>

        <div
            class="px-space-lg py-space-md bg-surface-container border-t border-white/5 flex items-center justify-end gap-2">
            <button type="button" onclick="closeModal('invoiceDetailModal')"
                class="h-10 px-4 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-semibold cursor-pointer">Tutup</button>
            <a id="mdPrintLink" href="#" target="_blank"
                class="h-10 px-4 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-bold inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">print</span>
                <span>Cetak Invoice</span>
            </a>
        </div>
    </div>
</div>

{{-- MODAL 2: REKAP LABA HARI INI (DENGAN TANGGAL & JAM REALTIME) --}}
<div id="dailyProfitModal" onclick="if (event.target === this) closeModal('dailyProfitModal')"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
    <div
        class="w-full max-w-md rounded-2xl bg-surface-container-low border border-white/10 shadow-2xl overflow-hidden">
        <div
            class="px-space-lg py-space-md bg-surface-container border-b border-white/5 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-tertiary/15 text-tertiary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">analytics</span>
                </div>
                <div class="min-w-0">
                    <h3 class="font-headline-sm text-body-lg text-on-surface font-bold leading-tight">Rekap Laba Hari
                        Ini</h3>
                    <p id="liveDailyProfitClock"
                        class="font-label-sm text-label-sm text-tertiary font-semibold mt-0.5">
                        {{ now()->translatedFormat('l, d F Y • \P\u\k\u\l H:i:s') }} WIB
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeModal('dailyProfitModal')"
                class="w-8 h-8 rounded-lg bg-surface-container-high text-on-surface-variant flex items-center justify-center shrink-0 cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <div class="p-space-lg flex flex-col gap-2.5 font-body-sm">
            <div class="flex justify-between p-3 rounded-xl bg-surface-container">
                <span class="text-on-surface-variant">Total Transaksi & Volume</span>
                <strong class="text-on-surface">{{ $kpi['today_count'] }} Faktur ({{ $num($kpi['today_box']) }}
                    Box)</strong>
            </div>
            <div class="flex justify-between p-3 rounded-xl bg-surface-container">
                <span class="text-on-surface-variant">Omzet Kotor Hari Ini</span>
                <strong class="text-on-surface">{{ $fmt($kpi['today_sales']) }}</strong>
            </div>
            <div class="flex justify-between p-3 rounded-xl bg-surface-container">
                <span class="text-on-surface-variant">Total HPP Modal Terkunci</span>
                <strong class="text-on-surface-variant">{{ $fmt($kpi['today_hpp']) }}</strong>
            </div>
            <div class="flex justify-between p-3 rounded-xl bg-surface-container">
                <span class="text-on-surface-variant">Realisasi Lunas ({{ $kpi['lunas_count'] }} Trx)</span>
                <strong class="text-tertiary">{{ $fmt($kpi['lunas_total']) }}</strong>
            </div>
            <div class="flex justify-between p-3 rounded-xl bg-surface-container">
                <span class="text-on-surface-variant">Piutang Tempo Hari Ini</span>
                <strong class="text-error">{{ $fmt(max(0, $kpi['today_sales'] - $kpi['lunas_total'])) }}</strong>
            </div>
            <div class="flex justify-between p-4 rounded-xl bg-primary/10 border border-primary/25 text-body-md mt-1">
                <span class="font-bold text-primary">Total Keuntungan Bersih</span>
                <strong class="font-extrabold text-primary">+{{ $fmt($kpi['today_profit']) }}</strong>
            </div>
        </div>
        <div class="px-space-lg py-space-md bg-surface-container border-t border-white/5 flex justify-end">
            <button type="button" onclick="closeModal('dailyProfitModal')"
                class="h-10 px-5 rounded-xl bg-primary text-on-primary font-bold cursor-pointer">Selesai</button>
        </div>
    </div>
</div>

{{-- MODAL 3: KONFIRMASI AKSI UNIVERSAL (IDENTIK DENGAN BARANG MASUK) --}}
<div id="confirmActionModal" onclick="if (event.target === this) closeModal('confirmActionModal')"
    class="hidden fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
    <div
        class="w-full max-w-md rounded-2xl bg-surface-container-low border border-white/10 shadow-[0_24px_64px_rgba(0,0,0,0.75)] overflow-hidden">
        <div class="p-space-lg flex flex-col gap-space-md">
            <div class="flex items-start gap-space-md">
                <div id="confirmIconWrap"
                    class="w-11 h-11 rounded-xl bg-error/15 text-error flex items-center justify-center shrink-0">
                    <span id="confirmIcon" class="material-symbols-outlined text-[24px]">delete_forever</span>
                </div>
                <div class="flex flex-col">
                    <h3 id="confirmTitle" class="font-headline-sm text-body-lg text-on-surface font-bold">Konfirmasi
                    </h3>
                    <p id="confirmMessage"
                        class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed mt-1"></p>
                </div>
            </div>

            <div id="confirmDetailBox"
                class="hidden p-space-md rounded-xl bg-surface-container border border-white/5 divide-y divide-white/5 font-label-md text-label-md">
            </div>

            <div class="flex items-center justify-end gap-2 pt-1">
                <button type="button" onclick="closeModal('confirmActionModal')"
                    class="h-10 px-4 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-body-sm font-semibold transition-colors cursor-pointer">
                    Periksa Kembali
                </button>
                <button type="button" id="confirmProceedBtn"
                    class="h-10 px-5 rounded-xl bg-error hover:bg-error/90 text-on-error font-headline-sm text-body-sm font-bold inline-flex items-center gap-1.5 transition-all cursor-pointer">
                    <span id="confirmProceedIcon" class="material-symbols-outlined text-[18px]">delete</span>
                    <span id="confirmProceedLabel">Ya, Lanjutkan</span>
                </button>
            </div>
        </div>
    </div>
</div>