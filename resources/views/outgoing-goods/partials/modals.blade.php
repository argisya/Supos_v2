{{-- MODAL 0: KASIR & INPUT PENJUALAN GROSIR --}}
<div id="saleFormModal" onclick="if (event.target === this) closeModal('saleFormModal')"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm overflow-y-auto">
    <div class="w-full max-w-5xl my-auto rounded-2xl bg-surface-container-low border border-white/10 shadow-[0_24px_64px_rgba(0,0,0,0.65)] overflow-hidden">
        {{-- Modal Header --}}
        <div class="px-space-lg py-space-md border-b border-white/5 bg-surface-container flex items-center justify-between gap-space-sm">
            <div class="flex items-center gap-space-sm">
                <div class="w-9 h-9 rounded-xl bg-primary-container/20 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">calculate</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="font-headline-md text-headline-sm text-on-surface font-bold">Kasir & Input Penjualan Grosir</h2>
                        <span class="hidden sm:inline-block font-label-sm text-label-sm px-2 py-0.5 rounded bg-surface-container-high text-on-surface-variant">
                            Validasi Stok: <strong class="text-primary font-bold">Ketat (Box Locked)</strong>
                        </span>
                    </div>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Validasi stok fisik Box × Pcs dan estimasi margin real-time sebelum diposting.</p>
                </div>
            </div>
            <div class="flex items-center gap-space-xs">
                @if (!$products->isEmpty())
                    <button type="button" onclick="confirmResetSaleForm()"
                            class="w-9 h-9 rounded-xl bg-surface-container-high hover:bg-surface-bright flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors cursor-pointer" title="Reset Form">
                        <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                    </button>
                @endif
                <button type="button" onclick="closeModal('saleFormModal')"
                        class="w-9 h-9 rounded-xl bg-surface-container-high hover:bg-error/20 flex items-center justify-center text-on-surface-variant hover:text-error transition-colors cursor-pointer" title="Tutup (ESC)">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mx-space-lg mt-space-md p-space-sm rounded-xl bg-error/10 border border-error/30 text-error font-body-sm">
                <strong class="flex items-center gap-1 mb-1"><span class="material-symbols-outlined text-[16px]">error</span> Periksa kembali:</strong>
                <ul class="list-disc list-inside ml-1">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        @if ($products->isEmpty())
            <div class="p-space-xl text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-[44px] text-surface-container-highest">inventory_2</span>
                <p class="font-body-md mt-2">Belum ada produk dengan stok tersedia. Silakan input <a href="{{ route('incoming-goods.index') }}" class="text-primary underline font-semibold">Barang Masuk</a> terlebih dahulu.</p>
                <button type="button" onclick="closeModal('saleFormModal')" class="mt-space-md h-10 px-space-lg rounded-xl bg-surface-container-high text-on-surface font-semibold cursor-pointer">Tutup</button>
            </div>
        @else
            <form method="POST" action="{{ route('outgoing-goods.store') }}" id="saleForm" class="max-h-[82vh] overflow-y-auto">
                @csrf
                <input type="hidden" name="payment_status" id="paymentStatusInput" value="{{ old('payment_status', 'lunas') }}">
                <input type="hidden" name="hpp_per_box" id="hppPerBoxInput" value="0">

                <div class="p-space-lg flex flex-col gap-space-md">

                    {{-- Row 1: Pelanggan, Invoice, Waktu, Alur Pembayaran --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-space-md">
                        <div class="md:col-span-4 flex flex-col gap-space-xs">
                            <label class="font-label-md text-label-md text-on-surface-variant uppercase flex items-center justify-between">
                                <span>Pelanggan / Toko Mitra</span>
                                <button type="button" onclick="openModal('newCustomerModal')" class="text-primary hover:underline lowercase font-body-sm text-body-sm cursor-pointer">+ Pelanggan Baru</button>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">storefront</span>
                                <select name="customer_id" id="customerSelect" required
                                        class="w-full h-11 pl-10 pr-8 rounded-xl bg-surface-container text-on-surface font-body-md text-body-md border border-white/10 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                                    @foreach ($customers as $c)
                                        <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>
                                            {{ $c->name }}@if($c->type) ({{ $c->type }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <div class="md:col-span-3 flex flex-col gap-space-xs">
                            <label class="font-label-md text-label-md text-on-surface-variant uppercase">No. Faktur / Invoice</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">tag</span>
                                <input type="text" readonly value="{{ $suggestedInvoice }}"
                                       class="w-full h-11 pl-10 pr-space-md rounded-xl bg-surface-container/60 text-primary font-label-md text-label-md border border-white/5 cursor-not-allowed" />
                            </div>
                        </div>

                        <div class="md:col-span-2 flex flex-col gap-space-xs">
                            <label class="font-label-md text-label-md text-on-surface-variant uppercase">Tanggal & Waktu</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">schedule</span>
                                <input type="text" readonly value="{{ now()->format('d/m/Y H:i') }}"
                                       class="w-full h-11 pl-10 pr-space-sm rounded-xl bg-surface-container/60 text-on-surface font-label-md text-label-md border border-white/5" />
                            </div>
                        </div>

                        <div class="md:col-span-3 flex flex-col gap-space-xs">
                            <label class="font-label-md text-label-md text-on-surface-variant uppercase">Alur Pembayaran</label>
                            <div class="grid grid-cols-2 gap-1 p-1 rounded-xl bg-surface-container border border-white/5">
                                <button type="button" onclick="setPaymentMode('lunas')" id="pay-lunas-btn"
                                        class="h-9 rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all bg-primary-container text-on-primary-container shadow-sm font-semibold cursor-pointer">
                                    <span class="material-symbols-outlined text-[16px]">payments</span>
                                    <span>Lunas</span>
                                </button>
                                <button type="button" onclick="setPaymentMode('tempo')" id="pay-tempo-btn"
                                        class="h-9 rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all text-on-surface-variant hover:text-on-surface cursor-pointer">
                                    <span class="material-symbols-outlined text-[16px]">event_repeat</span>
                                    <span>Tempo</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="cash-account-picker" class="p-space-sm rounded-xl bg-primary-container/10 border border-primary/20 flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
                        <div class="flex flex-col">
                            <label for="paymentAccountSelect" class="font-label-sm text-on-surface-variant uppercase">Kas / Rekening Penerimaan</label>
                            <span class="font-body-sm text-on-surface-variant">Jurnal penjualan lunas akan masuk ke akun ini.</span>
                        </div>
                        <select name="payment_account" id="paymentAccountSelect"
                                class="h-10 w-full sm:max-w-xs px-space-md rounded-lg bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary">
                            @foreach(\App\Models\CashIn::ACCOUNTS as $accountKey => $account)
                                <option value="{{ $accountKey }}" @selected(old('payment_account', 'kas_tunai') === $accountKey)>
                                    {{ $account['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tempo Due Picker --}}
                    <div id="tempo-due-picker" class="hidden p-space-sm rounded-xl bg-secondary-container/20 border border-secondary-container/40 flex flex-col sm:flex-row items-center justify-between gap-space-sm">
                        <div class="flex items-center gap-space-sm text-secondary">
                            <span class="material-symbols-outlined text-[20px]">notification_important</span>
                            <span class="font-body-sm text-body-sm">Status Tempo: Faktur ini akan diteruskan ke <strong>Buku Tagihan Piutang</strong>.</span>
                        </div>
                        <div class="flex items-center gap-space-sm">
                            <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Jatuh Tempo:</label>
                            <input name="due_date" id="dueDateInput" type="date"
                                   value="{{ old('due_date', now()->addDays(14)->format('Y-m-d')) }}"
                                   class="h-9 px-space-md rounded-lg bg-surface-container-high text-on-surface font-label-md text-label-md border border-white/10 focus:outline-none focus:ring-1 focus:ring-secondary" />
                        </div>
                    </div>

                    {{-- Row 2: Produk, Stok, Qty, dan Harga --}}
                    <div class="p-space-md rounded-xl bg-surface-container border border-white/5 flex flex-col gap-space-md">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-md items-end">
                            <div class="lg:col-span-5 flex flex-col gap-space-xs">
                                <label class="font-label-md text-label-md text-on-surface-variant uppercase flex items-center justify-between">
                                    <span>Pilih Barang Grosir (SKU)</span>
                                    <span class="text-tertiary font-label-sm" id="barcodeLabel">Barcode: —</span>
                                </label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">inventory_2</span>
                                    <select name="product_id" id="productSelect" onchange="onProductChange(true)" required
                                            class="w-full h-11 pl-10 pr-8 rounded-xl bg-surface-container-high text-on-surface font-headline-sm text-body-md border border-white/10 focus:outline-none focus:border-tertiary focus:ring-1 focus:ring-tertiary appearance-none cursor-pointer">
                                        @foreach ($products as $p)
                                            @php $ratio = max(1, (int) $p->box_pcs_ratio); @endphp
                                            <option value="{{ $p->id }}"
                                                    data-name="{{ $p->name }}"
                                                    data-sku="{{ $p->sku }}"
                                                    data-barcode="{{ $p->barcode }}"
                                                    data-hpp="{{ (int) ($p->last_hpp ?? 0) }}"
                                                    data-pcs-per-box="{{ $ratio }}"
                                                    data-stock="{{ $p->stock_pcs }}"
                                                    data-stock-box="{{ floor($p->stock_pcs / $ratio) }}"
                                                    @selected(old('product_id') == $p->id)>
                                                {{ $p->name }} (1 Box = {{ $ratio }} Pcs)
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] pointer-events-none">expand_more</span>
                                </div>
                            </div>

                            <div class="lg:col-span-3 flex flex-col justify-end">
                                <div class="h-11 px-space-md rounded-xl bg-surface-container-low flex items-center justify-between border border-white/5">
                                    <div class="flex items-center gap-space-xs">
                                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                                        <div class="flex flex-col">
                                            <span class="font-label-sm text-label-sm text-on-surface-variant leading-none">Sisa Stok Fisik:</span>
                                            <span class="font-headline-sm text-body-sm text-on-surface font-bold leading-tight" id="stockDisplay">—</span>
                                        </div>
                                    </div>
                                    <span id="stockBadge" class="font-label-sm text-label-sm px-1.5 py-0.5 rounded bg-primary/20 text-primary font-bold">AMAN</span>
                                </div>
                            </div>

                            <div class="lg:col-span-4 flex flex-col gap-space-xs">
                                <label class="font-label-md text-label-md text-on-surface-variant uppercase flex items-center justify-between">
                                    <span>Jumlah Box Terjual</span>
                                    <span class="text-tertiary font-label-md" id="pcsCounter">= 0 Pcs</span>
                                </label>
                                <div class="flex items-center gap-space-xs">
                                    <div class="relative flex-1">
                                        <input name="box_qty" id="qtyInput" type="number" min="1" value="{{ old('box_qty', 1) }}" required
                                               oninput="calculateProfit()"
                                               class="w-full h-11 px-space-md rounded-xl bg-surface-container-high text-on-surface font-headline-md text-headline-sm font-bold border border-white/10 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-center" />
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 font-label-md text-label-md text-on-surface-variant pointer-events-none">BOX</span>
                                    </div>
                                    <button type="button" onclick="adjustQty(-1)" class="w-11 h-11 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface flex items-center justify-center transition-colors cursor-pointer">
                                        <span class="material-symbols-outlined text-[20px]">remove</span>
                                    </button>
                                    <button type="button" onclick="adjustQty(1)" class="w-11 h-11 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface flex items-center justify-center transition-colors cursor-pointer">
                                        <span class="material-symbols-outlined text-[20px]">add</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-space-md pt-space-sm border-t border-white/5">
                            <div class="flex flex-col gap-space-xs">
                                <label class="font-label-sm text-label-sm text-on-surface-variant uppercase flex items-center gap-1">
                                    <span>Harga Modal Terakhir (HPP)</span>
                                    <span class="material-symbols-outlined text-primary text-[14px]" title="Snapshot dari data Barang Masuk">lock</span>
                                </label>
                                <div class="h-11 px-space-md rounded-xl bg-surface-container-low flex items-center justify-between border border-white/5">
                                    <span class="font-headline-sm text-body-md text-on-surface font-semibold" id="hppBoxDisplay">—</span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant">/ Box</span>
                                </div>
                                <span class="font-label-sm text-label-sm text-on-surface-variant/80">Snapshot saat transaksi</span>
                            </div>

                            <div class="flex flex-col gap-space-xs">
                                <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">HPP Satuan Terkecil</label>
                                <div class="h-11 px-space-md rounded-xl bg-surface-container-low flex items-center justify-between border border-white/5">
                                    <span class="font-headline-sm text-body-md text-on-surface-variant font-semibold" id="hppPcsDisplay">—</span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant">/ Pcs</span>
                                </div>
                                <span class="font-label-sm text-label-sm text-tertiary" id="ratioInfo">—</span>
                            </div>

                            <div class="flex flex-col gap-space-xs">
                                <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Harga Jual per Box</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 font-label-md text-label-md text-on-surface-variant">Rp</span>
                                    <input name="sell_per_box" id="sellBoxInput" type="number" value="{{ old('sell_per_box', 0) }}" required
                                           oninput="calculateProfit()"
                                           class="w-full h-11 pl-9 pr-space-md rounded-xl bg-surface-container-high text-on-surface font-headline-sm text-body-md font-bold border border-white/10 focus:outline-none focus:border-tertiary focus:ring-1 focus:ring-tertiary" />
                                </div>
                                <span class="font-label-sm text-label-sm text-primary font-semibold" id="marginBoxDisplay">—</span>
                            </div>

                            <div class="flex flex-col gap-space-xs">
                                <label class="font-label-sm text-label-sm text-on-surface-variant uppercase">Setara Harga per Pcs</label>
                                <div class="h-11 px-space-md rounded-xl bg-surface-container-low flex items-center justify-between border border-white/5">
                                    <span class="font-headline-sm text-body-md text-on-surface font-semibold" id="sellPcsDisplay">—</span>
                                    <span class="font-label-sm text-label-sm text-tertiary font-bold" id="marginPcsDisplay">—</span>
                                </div>
                                <span class="font-label-sm text-label-sm text-on-surface-variant/80">Retail Rekomendasi: <span id="retailRec">—</span></span>
                            </div>
                        </div>
                    </div>

                    {{-- Row 3: Profit Calculator Banner --}}
                    <div class="p-space-md rounded-xl bg-linear-to-r from-surface-container via-surface-container-high to-surface-container border border-primary/30 shadow-md flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-space-md">
                        <div class="flex items-center gap-space-md">
                            <div class="w-11 h-11 rounded-xl bg-primary-container text-on-primary-container flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[24px]">query_stats</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="font-label-sm text-label-sm text-primary uppercase font-bold tracking-wider flex items-center gap-1">
                                    <span>Mesin Kalkulasi Laba Grosir Real-Time</span>
                                    <span class="material-symbols-outlined text-[14px]">bolt</span>
                                </span>
                                <div class="flex items-baseline gap-space-sm mt-0.5 flex-wrap">
                                    <span class="font-headline-sm text-body-md text-on-surface-variant">Omzet Total:</span>
                                    <span class="font-headline-md text-headline-md text-on-surface font-bold tracking-tight" id="calcOmzet">Rp 0</span>
                                    <span class="text-on-surface-variant text-body-sm font-label-md" id="calcBoxInfo">(0 Box / 0 Pcs)</span>
                                </div>
                                <div class="flex items-center gap-space-md mt-1 text-label-md font-label-md text-on-surface-variant">
                                    <span>HPP Terkunci: <strong class="text-on-surface" id="calcHpp">Rp 0</strong></span>
                                    <span>•</span>
                                    <span>Status Routing: <strong class="text-primary" id="calcRouting">Kas Masuk Toko (Lunas)</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="px-space-md py-space-sm rounded-xl bg-primary/10 border border-primary/30 flex flex-col items-center sm:items-end justify-center min-w-[200px]">
                            <span class="font-label-sm text-label-sm text-primary uppercase font-bold tracking-wider">Keuntungan Bersih (Locked)</span>
                            <div class="font-display-lg text-headline-lg text-primary font-black tracking-tight leading-none mt-1" id="calcProfit">+Rp 0</div>
                            <span class="font-label-md text-label-md text-primary font-semibold mt-1" id="calcMarginPct">Margin: 0%</span>
                        </div>
                    </div>

                    {{-- Stock Error Banner --}}
                    <div id="stockErrorBanner" class="hidden p-space-md rounded-xl bg-error-container/40 border border-error/40 flex items-center justify-between text-on-error-container">
                        <div class="flex items-center gap-space-sm">
                            <span class="material-symbols-outlined text-[24px] text-error">gpp_maybe</span>
                            <div class="flex flex-col">
                                <strong class="font-headline-sm text-body-md text-error">Permintaan Melebihi Sisa Stok Fisik!</strong>
                                <span class="font-body-sm text-body-sm" id="stockErrorMsg">Jumlah melebihi sisa stok. Transaksi diblokir sistem pencegah selisih.</span>
                            </div>
                        </div>
                        <button type="button" onclick="resetQtyToMax()"
                                class="px-space-md py-1.5 rounded-lg bg-error text-on-error font-headline-sm text-body-sm cursor-pointer">
                            Set ke Maksimal
                        </button>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-space-lg py-space-md bg-surface-container border-t border-white/5 flex flex-col-reverse sm:flex-row items-center justify-end gap-space-sm">
                    <button type="button" onclick="closeModal('saleFormModal')"
                            class="w-full sm:w-auto h-11 px-space-lg rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-body-sm font-semibold transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="button" id="submitSaleBtn" onclick="confirmSubmitSale()"
                            class="w-full sm:w-auto h-11 px-space-lg rounded-xl bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-body-md font-bold flex items-center justify-center gap-space-sm transition-all shadow-[0_0_20px_rgba(16,185,129,0.4)] cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">verified</span>
                        <span>Simpan Penjualan & Potong Stok Box</span>
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

{{-- MODAL 1: DETAIL FAKTUR --}}
<div id="invoiceDetailModal" onclick="if (event.target === this) closeModal('invoiceDetailModal')"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
    <div class="w-full max-w-lg rounded-2xl bg-surface-container-low border border-white/10 shadow-2xl overflow-hidden">
        <div class="px-space-lg py-space-md bg-surface-container border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">receipt_long</span>
                <h3 class="font-headline-sm text-body-lg text-on-surface font-bold" id="mdInvNo">Detail Faktur</h3>
            </div>
            <button type="button" onclick="closeModal('invoiceDetailModal')" class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant flex items-center justify-center cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <div class="p-space-lg flex flex-col gap-space-md font-body-sm text-body-sm">
            <div class="grid grid-cols-2 gap-space-sm p-space-md rounded-xl bg-surface-container">
                <div>
                    <span class="text-label-sm text-on-surface-variant uppercase block">Pelanggan / Mitra</span>
                    <strong class="text-on-surface text-body-md" id="mdCustomer">—</strong>
                </div>
                <div class="text-right">
                    <span class="text-label-sm text-on-surface-variant uppercase block">Waktu Transaksi</span>
                    <strong class="text-on-surface" id="mdSoldAt">—</strong>
                </div>
                <div>
                    <span class="text-label-sm text-on-surface-variant uppercase block">Status Pembayaran</span>
                    <strong id="mdStatus">—</strong>
                </div>
                <div class="text-right">
                    <span class="text-label-sm text-on-surface-variant uppercase block">Jatuh Tempo</span>
                    <strong class="text-on-surface" id="mdDueDate">—</strong>
                </div>
            </div>

            <div class="p-space-md rounded-xl bg-surface-container border border-white/5 flex flex-col gap-2">
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Produk</span>
                    <strong class="text-on-surface" id="mdProduct">—</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Kuantitas Keluar</span>
                    <strong class="text-tertiary" id="mdQty">—</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">HPP Terkunci / Box</span>
                    <span class="text-on-surface-variant" id="mdHppBox">—</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Harga Jual / Box</span>
                    <strong class="text-on-surface" id="mdSellBox">—</strong>
                </div>
                <div class="pt-2 border-t border-white/5 flex justify-between text-body-md">
                    <span class="font-semibold text-on-surface">Total Tagihan Faktur</span>
                    <strong class="text-on-surface font-extrabold" id="mdSubtotal">—</strong>
                </div>
                <div class="flex justify-between text-body-md">
                    <span class="font-semibold text-primary">Keuntungan Bersih (Locked)</span>
                    <strong class="text-primary font-extrabold" id="mdProfit">—</strong>
                </div>
            </div>
        </div>
        <div class="px-space-lg py-space-md bg-surface-container border-t border-white/5 flex items-center justify-end gap-2">
            <button type="button" onclick="closeModal('invoiceDetailModal')" class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface cursor-pointer">Tutup</button>
            <a id="mdPrintLink" href="#" target="_blank" class="h-10 px-space-md rounded-xl bg-primary hover:bg-primary-container text-on-primary font-semibold flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">print</span>
                <span>Cetak Invoice / Surat Jalan</span>
            </a>
        </div>
    </div>
</div>

{{-- MODAL 2: REKAP LABA HARI INI --}}
<div id="dailyProfitModal" onclick="if (event.target === this) closeModal('dailyProfitModal')"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl bg-surface-container-low border border-white/10 shadow-2xl overflow-hidden">
        <div class="px-space-lg py-space-md bg-surface-container border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-tertiary">analytics</span>
                <h3 class="font-headline-sm text-body-lg text-on-surface font-bold">Rekap Laba Hari Ini ({{ now()->format('d M Y') }})</h3>
            </div>
            <button type="button" onclick="closeModal('dailyProfitModal')" class="w-8 h-8 rounded-lg bg-surface-container-high text-on-surface-variant flex items-center justify-center cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <div class="p-space-lg flex flex-col gap-3 font-body-sm">
            <div class="flex justify-between p-3 rounded-xl bg-surface-container">
                <span class="text-on-surface-variant">Total Transaksi & Volume</span>
                <strong class="text-on-surface">{{ $kpi['today_count'] }} Faktur ({{ $num($kpi['today_box']) }} Box)</strong>
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
                <span class="text-on-surface-variant">Realisasi Lunas (Masuk Kas)</span>
                <strong class="text-tertiary">{{ $fmt($kpi['lunas_total']) }}</strong>
            </div>
            <div class="flex justify-between p-4 rounded-xl bg-primary/15 border border-primary/30 text-body-md">
                <span class="font-bold text-primary">Total Keuntungan Bersih</span>
                <strong class="font-extrabold text-primary">+{{ $fmt($kpi['today_profit']) }}</strong>
            </div>
        </div>
        <div class="px-space-lg py-space-md bg-surface-container border-t border-white/5 flex justify-end">
            <button type="button" onclick="closeModal('dailyProfitModal')" class="h-10 px-space-md rounded-xl bg-primary text-on-primary font-semibold cursor-pointer">Selesai</button>
        </div>
    </div>
</div>

{{-- MODAL 3: TAMBAH PELANGGAN MITRA CEPAT --}}
<div id="newCustomerModal" onclick="if (event.target === this) closeModal('newCustomerModal')"
     class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl bg-surface-container-low border border-white/10 shadow-2xl overflow-hidden">
        <div class="px-space-lg py-space-md bg-surface-container border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">storefront</span>
                <h3 class="font-headline-sm text-body-lg text-on-surface font-bold">Tambah Pelanggan / Toko Mitra</h3>
            </div>
            <button type="button" onclick="closeModal('newCustomerModal')" class="w-8 h-8 rounded-lg bg-surface-container-high text-on-surface-variant flex items-center justify-center cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <form id="quickCustomerForm" onsubmit="submitQuickCustomer(event)" class="p-space-lg flex flex-col gap-space-md">
            <div>
                <label class="font-label-sm text-label-sm text-on-surface-variant uppercase block mb-1">Nama Toko / Pelanggan *</label>
                <input type="text" name="name" required placeholder="Contoh: Toko Berkah Makmur"
                       class="w-full h-10 px-3 rounded-xl bg-surface-container text-on-surface border border-white/10 focus:outline-none focus:border-primary" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-label-sm text-label-sm text-on-surface-variant uppercase block mb-1">Kategori Mitra</label>
                    <select name="type" class="w-full h-10 px-3 rounded-xl bg-surface-container text-on-surface border border-white/10">
                        <option value="Toko Kelontong">Toko Kelontong</option>
                        <option value="Minimarket">Minimarket</option>
                        <option value="Agen / Reseller">Agen / Reseller</option>
                        <option value="Warung">Warung</option>
                    </select>
                </div>
                <div>
                    <label class="font-label-sm text-label-sm text-on-surface-variant uppercase block mb-1">No. HP / WhatsApp</label>
                    <input type="text" name="phone" placeholder="0812..."
                           class="w-full h-10 px-3 rounded-xl bg-surface-container text-on-surface border border-white/10" />
                </div>
            </div>
            <div>
                <label class="font-label-sm text-label-sm text-on-surface-variant uppercase block mb-1">Alamat Singkat</label>
                <input type="text" name="address" placeholder="Jl. Raya..."
                       class="w-full h-10 px-3 rounded-xl bg-surface-container text-on-surface border border-white/10" />
            </div>
            <p id="quickCustomerError" class="hidden text-error font-body-sm"></p>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('newCustomerModal')" class="h-10 px-4 rounded-xl bg-surface-container-high text-on-surface cursor-pointer">Batal</button>
                <button type="submit" id="quickCustomerSubmit" class="h-10 px-4 rounded-xl bg-primary text-on-primary font-semibold cursor-pointer">Simpan Mitra</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 4: KONFIRMASI AKSI UNIVERSAL (ROLLBACK, SIMPAN PENJUALAN, RESET FORM) --}}
<div id="confirmActionModal" onclick="if (event.target === this) closeModal('confirmActionModal')"
     class="hidden fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl bg-surface-container-low border border-white/10 shadow-[0_24px_64px_rgba(0,0,0,0.75)] overflow-hidden">
        <div class="p-space-lg flex flex-col gap-space-md">
            <div class="flex items-start gap-space-md">
                <div id="confirmIconWrap" class="w-11 h-11 rounded-xl bg-error/15 text-error flex items-center justify-center shrink-0">
                    <span id="confirmIcon" class="material-symbols-outlined text-[24px]">warning</span>
                </div>
                <div class="flex flex-col">
                    <span id="confirmBadge" class="font-label-sm text-label-sm uppercase tracking-wider text-error font-bold">Konfirmasi Tindakan</span>
                    <h3 id="confirmTitle" class="font-headline-sm text-body-lg text-on-surface font-bold mt-0.5">Konfirmasi</h3>
                </div>
            </div>

            <p id="confirmMessage" class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed"></p>

            <div id="confirmDetailBox" class="hidden p-space-md rounded-xl bg-surface-container border border-white/5 flex flex-col gap-1.5 font-label-md text-label-md"></div>
        </div>

        <div class="px-space-lg py-space-md bg-surface-container border-t border-white/5 flex items-center justify-end gap-space-sm">
            <button type="button" onclick="closeModal('confirmActionModal')"
                    class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-body-sm font-semibold transition-colors cursor-pointer">
                Batal
            </button>
            <button type="button" id="confirmProceedBtn"
                    class="h-10 px-space-lg rounded-xl bg-error hover:bg-error/90 text-on-error font-headline-sm text-body-sm font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                <span id="confirmProceedIcon" class="material-symbols-outlined text-[18px]">check</span>
                <span id="confirmProceedLabel">Ya, Lanjutkan</span>
            </button>
        </div>
    </div>
</div>