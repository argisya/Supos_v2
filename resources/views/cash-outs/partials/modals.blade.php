{{-- ============ 1. MODAL INPUT KAS KELUAR (SUPPLIER & OPERASIONAL) ============ --}}
<div id="cashOutInputModal" onclick="if(event.target===this) closeCashOutInputModal()"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
    <div class="w-full max-w-2xl rounded-2xl bg-surface-container-low border border-surface-container-highest shadow-2xl overflow-hidden my-auto">
        <div class="p-space-lg border-b border-surface-container-highest flex items-center justify-between">
            <div class="flex items-center gap-space-xs">
                <span class="material-symbols-outlined text-primary">payments</span>
                <h3 class="font-headline-sm text-on-surface font-bold">Catat Kas Keluar Baru</h3>
            </div>
            <button type="button" onclick="closeCashOutInputModal()"
                class="w-8 h-8 rounded-lg hover:bg-surface-container flex items-center justify-center text-on-surface-variant cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <div class="p-space-lg">
            {{-- Tab Switcher --}}
            <div class="flex items-center p-1 rounded-xl bg-surface-container mb-space-md">
                <button type="button" onclick="switchCashOutTab('supplier')" id="tabSupplierBtn"
                    class="flex-1 py-space-xs rounded-lg bg-primary-container text-on-primary-container font-headline-sm text-body-sm font-semibold flex items-center justify-center gap-space-xs shadow-sm transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                    <span>Bayar Hutang Supplier (PO)</span>
                </button>
                <button type="button" onclick="switchCashOutTab('ops')" id="tabOpsBtn"
                    class="flex-1 py-space-xs rounded-lg text-on-surface-variant hover:text-on-surface font-headline-sm text-body-sm font-medium flex items-center justify-center gap-space-xs transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">local_shipping</span>
                    <span>Catat Biaya Operasional</span>
                </button>
            </div>

            {{-- TAB 1: BAYAR HUTANG SUPPLIER --}}
            <div id="tabSupplierForm">
                @if ($openTempo->isEmpty())
                    <div class="p-space-lg text-center text-on-surface-variant">
                        <span class="material-symbols-outlined text-[48px] text-primary">task_alt</span>
                        <p class="font-body-md mt-2">Tidak ada faktur hutang supplier yang menunggu pembayaran.</p>
                    </div>
                @else
                    <form method="POST" id="paySupplierForm" action="{{ route('cash-outs.pay-supplier', $openTempo->first()) }}" class="flex flex-col gap-space-md">
                        @csrf
                        <input type="hidden" name="active_tab" value="supplier">

                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-on-surface-variant uppercase tracking-wider flex items-center justify-between">
                                <span>Pilih Faktur Tempo Barang Masuk *</span>
                                <a href="{{ route('incoming-goods.index', ['status' => 'TEMPO']) }}" class="text-primary hover:underline text-xs">Lihat Buku Hutang</a>
                            </label>
                            <div class="relative">
                                <select id="openTempoSelect" onchange="updateSupplierForm()"
                                    class="w-full h-11 pl-space-md pr-10 rounded-xl bg-surface-container text-on-surface font-body-sm appearance-none focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer">
                                    @foreach ($openTempo as $t)
                                        <option value="{{ $t->id }}"
                                            data-action="{{ route('cash-outs.pay-supplier', $t) }}"
                                            data-remaining="{{ (int) $t->remaining }}"
                                            data-label="{{ $t->supplier?->name }} — Faktur {{ $t->no_faktur }}"
                                            @selected($loop->first)>
                                            {{ $t->supplier?->name }} — Faktur {{ $t->no_faktur }} (Sisa: {{ $fmt($t->remaining) }}, JT: {{ $t->due_date?->format('d M Y') ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[20px]">expand_more</span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Sumber Dana / Rekening *</label>
                            <div class="grid grid-cols-3 gap-space-xs">
                                @foreach ($acctStyles as $key => $meta)
                                    <label class="flex items-center gap-space-xs p-space-sm rounded-xl cursor-pointer text-on-surface bg-surface-container hover:bg-surface-container-high transition-colors">
                                        <input type="radio" name="account" value="{{ $key }}" data-acc-label="{{ $meta['label'] }}" class="accent-primary" @checked(old('account', 'bca') === $key) />
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-headline-sm text-body-sm font-bold truncate">{{ $meta['label'] }}</span>
                                            <span class="font-label-sm text-on-surface-variant">Sisa {{ $fmtJt($balances[$key] ?? 0) }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Jumlah Pembayaran *</label>
                                <div class="flex items-center gap-1">
                                    <button type="button" onclick="setPayAmount('50')" class="px-2 py-0.5 rounded bg-surface-container-high text-primary text-label-sm font-bold cursor-pointer">50%</button>
                                    <button type="button" onclick="setPayAmount('100')" class="px-2 py-0.5 rounded bg-surface-container-high text-primary text-label-sm font-bold cursor-pointer">100% Lunas</button>
                                </div>
                            </div>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-headline-sm text-primary font-bold">Rp</span>
                                <input name="amount" id="payAmountInput" type="number" min="1" required
                                    value="{{ old('amount', (int) $openTempo->first()->remaining) }}"
                                    class="w-full h-11 pl-12 pr-space-md rounded-xl bg-surface-container font-mono text-headline-sm text-on-surface font-bold focus:outline-none focus:ring-1 focus:ring-primary" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-sm">
                            <div class="flex flex-col gap-1.5">
                                <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Tanggal Pembayaran *</label>
                                <input name="paid_at" type="datetime-local" required value="{{ old('paid_at', now('Asia/Jakarta')->format('Y-m-d\TH:i')) }}"
                                    class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Catatan / Referensi</label>
                                <input name="notes" type="text" value="{{ old('notes') }}" placeholder="Contoh: Termin 1 PO-9921"
                                    class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-space-sm pt-2">
                            <button type="button" onclick="closeCashOutInputModal()" class="h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm cursor-pointer">
                                Batal
                            </button>
                            <button type="button" onclick="askPostConfirmation('supplier')"
                                class="h-10 px-space-lg rounded-xl bg-primary-container hover:bg-primary text-on-primary-container font-headline-sm font-bold flex items-center gap-space-xs shadow-[0_4px_18px_rgba(16,185,129,0.35)] transition-all cursor-pointer">
                                <span class="material-symbols-outlined text-[20px]">bolt</span>
                                <span>Simpan &amp; Posting Pelunasan</span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            {{-- TAB 2: BIAYA OPERASIONAL --}}
            <div id="tabOpsForm" class="hidden">
                <form method="POST" id="opsExpenseForm" action="{{ route('cash-outs.store') }}" class="flex flex-col gap-space-md">
                    @csrf
                    <input type="hidden" name="active_tab" value="ops">

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Kategori Pengeluaran *</label>
                        <select name="category" id="opsCategorySelect" required class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="">— Pilih Kategori —</option>
                            <option value="biaya_operasional" @selected(old('category') === 'biaya_operasional')>Biaya Operasional Umum</option>
                            <option value="biaya_listrik" @selected(old('category') === 'biaya_listrik')>Listrik &amp; Utilitas</option>
                            <option value="gaji_staff" @selected(old('category') === 'gaji_staff')>Gaji &amp; Kasbon Staff</option>
                            <option value="logistik_armada" @selected(old('category') === 'logistik_armada')>Logistik &amp; Armada</option>
                            <option value="bensin_distribusi" @selected(old('category') === 'bensin_distribusi')>Bensin &amp; Tol Distribusi</option>
                            <option value="lain_lain" @selected(old('category') === 'lain_lain')>Pengeluaran Lain-lain</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Sumber Dana / Rekening *</label>
                        <div class="grid grid-cols-3 gap-space-xs">
                            @foreach ($acctStyles as $key => $meta)
                                <label class="flex items-center gap-space-xs p-space-sm rounded-xl cursor-pointer bg-surface-container hover:bg-surface-container-high transition-colors">
                                    <input type="radio" name="account" value="{{ $key }}" data-acc-label="{{ $meta['label'] }}" class="accent-primary" @checked($loop->first || old('account') === $key) />
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-headline-sm text-body-sm font-bold truncate">{{ $meta['label'] }}</span>
                                        <span class="font-label-sm text-on-surface-variant">Sisa {{ $fmtJt($balances[$key] ?? 0) }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-sm">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Nominal *</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-headline-sm text-primary font-bold">Rp</span>
                                <input name="amount" id="opsAmountInput" type="number" min="1" required value="{{ old('amount') }}"
                                    class="w-full h-11 pl-12 pr-space-md rounded-xl bg-surface-container text-on-surface font-mono font-bold focus:outline-none focus:ring-1 focus:ring-primary" />
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Tanggal Pengeluaran</label>
                            <input name="paid_at" type="datetime-local" value="{{ old('paid_at', now('Asia/Jakarta')->format('Y-m-d\TH:i')) }}"
                                class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-md text-on-surface-variant uppercase tracking-wider">Keterangan Pengeluaran *</label>
                        <input name="notes" type="text" required value="{{ old('notes') }}" placeholder="Contoh: Tagihan listrik &amp; cold storage bulan ini"
                            class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                    </div>

                    <div class="flex items-center justify-end gap-space-sm pt-2">
                        <button type="button" onclick="closeCashOutInputModal()" class="h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm cursor-pointer">
                            Batal
                        </button>
                        <button type="button" onclick="askPostConfirmation('ops')"
                            class="h-10 px-space-lg rounded-xl bg-primary-container hover:bg-primary text-on-primary-container font-headline-sm font-bold flex items-center gap-space-xs shadow-[0_4px_18px_rgba(16,185,129,0.35)] transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                            <span>Simpan &amp; Posting Pengeluaran</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ============ 2. MODAL KONFIRMASI DOUBLE-CHECK POSTING ============ --}}
<div id="confirmPostCashOutModal" onclick="if(event.target===this) closePostConfirmation()"
    class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-md rounded-2xl bg-surface-container-low border border-surface-container-highest shadow-2xl p-space-lg flex flex-col gap-space-md">
        <div class="flex items-center gap-space-sm text-primary">
            <span class="material-symbols-outlined text-[28px]">verified</span>
            <h3 class="font-headline-sm font-bold text-on-surface">Konfirmasi Posting Kas Keluar</h3>
        </div>
        <div class="p-space-md rounded-xl bg-surface-container flex flex-col gap-1.5 font-body-sm">
            <div class="flex justify-between gap-2">
                <span class="text-on-surface-variant shrink-0">Jenis / Tujuan:</span>
                <span id="confirmCoTarget" class="font-semibold text-on-surface text-right"></span>
            </div>
            <div class="flex justify-between gap-2">
                <span class="text-on-surface-variant shrink-0">Akun Sumber:</span>
                <span id="confirmCoAccount" class="font-semibold text-on-surface"></span>
            </div>
            <div class="flex justify-between pt-2 border-t border-surface-container-highest">
                <span class="text-on-surface-variant">Nominal Dana Keluar:</span>
                <span id="confirmCoAmount" class="font-mono font-extrabold text-error text-body-lg"></span>
            </div>
        </div>
        <div class="flex items-center justify-end gap-space-sm">
            <button type="button" onclick="closePostConfirmation()" class="h-9 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm cursor-pointer">
                Periksa Kembali
            </button>
            <button type="button" id="executePostCoBtn" class="h-9 px-space-lg rounded-xl bg-primary-container hover:bg-primary text-on-primary-container font-headline-sm font-bold cursor-pointer">
                Ya, Posting Sekarang
            </button>
        </div>
    </div>
</div>

{{-- ============ 3. MODAL DETAIL VOUCHER KAS KELUAR ============ --}}
<div id="cashOutDetailModal" onclick="if(event.target===this) closeCashOutDetail()"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-md rounded-2xl bg-surface-container-low border border-surface-container-highest shadow-2xl overflow-hidden">
        <div class="p-space-lg border-b border-surface-container-highest flex items-center justify-between">
            <div class="flex items-center gap-space-xs">
                <span class="material-symbols-outlined text-primary">receipt_long</span>
                <h3 class="font-headline-sm text-on-surface font-bold">Detail Bukti Kas Keluar</h3>
            </div>
            <button type="button" onclick="closeCashOutDetail()" class="w-8 h-8 rounded-lg hover:bg-surface-container flex items-center justify-center text-on-surface-variant cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <div class="p-space-lg flex flex-col gap-space-md font-body-sm">
            <div class="p-space-md rounded-xl bg-surface-container flex items-center justify-between">
                <div>
                    <span class="font-label-sm text-on-surface-variant uppercase">Nomor Voucher</span>
                    <p id="coModalCode" class="font-mono font-bold text-primary text-body-lg"></p>
                </div>
                <span id="coModalTypeBadge" class="px-2.5 py-1 rounded-full bg-primary/15 text-primary font-label-sm font-bold"></span>
            </div>

            <div class="grid grid-cols-2 gap-space-sm">
                <div>
                    <span class="font-label-sm text-on-surface-variant">Waktu Transaksi</span>
                    <p id="coModalDate" class="font-semibold text-on-surface"></p>
                </div>
                <div>
                    <span class="font-label-sm text-on-surface-variant">Akun Sumber</span>
                    <p id="coModalAccount" class="font-semibold text-on-surface"></p>
                </div>
                <div>
                    <span class="font-label-sm text-on-surface-variant">Penerima / Tujuan</span>
                    <p id="coModalRecipient" class="font-semibold text-on-surface"></p>
                </div>
                <div>
                    <span class="font-label-sm text-on-surface-variant">No. Referensi / Faktur</span>
                    <p id="coModalRef" class="font-mono font-semibold text-on-surface"></p>
                </div>
            </div>

            <div>
                <span class="font-label-sm text-on-surface-variant">Kategori Pengeluaran</span>
                <p id="coModalCategory" class="font-semibold text-on-surface"></p>
            </div>

            <div>
                <span class="font-label-sm text-on-surface-variant">Keterangan</span>
                <p id="coModalNotes" class="text-on-surface bg-surface-container p-space-sm rounded-xl mt-1"></p>
            </div>

            <div class="p-space-md rounded-xl bg-error/10 border border-error/20 flex items-center justify-between">
                <span class="font-label-md text-on-surface-variant uppercase font-bold">Total Dana Keluar</span>
                <span id="coModalAmount" class="font-mono font-extrabold text-error text-headline-sm"></span>
            </div>
        </div>

        <div class="p-space-md bg-surface-container flex items-center justify-end gap-space-sm">
            <button type="button" onclick="closeCashOutDetail()" class="h-9 px-space-md rounded-xl bg-surface-container-high text-on-surface font-body-sm cursor-pointer">
                Tutup
            </button>
            <button type="button" onclick="printCashOutSlip()" class="h-9 px-space-md rounded-xl bg-primary-container text-on-primary-container font-headline-sm font-bold flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">print</span>
                <span>Cetak Bukti</span>
            </button>
        </div>
    </div>
</div>

{{-- ============ 4. MODAL KONFIRMASI HAPUS ENTRI MANUAL ============ --}}
<div id="deleteCashOutModal" onclick="if(event.target===this) closeDeleteCashOut()"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-sm rounded-2xl bg-surface-container-low border border-surface-container-highest shadow-2xl p-space-lg flex flex-col gap-space-md">
        <div class="flex items-center gap-space-sm text-error">
            <span class="material-symbols-outlined text-[28px]">warning</span>
            <h3 class="font-headline-sm font-bold">Hapus Kas Keluar Manual?</h3>
        </div>
        <p class="font-body-sm text-on-surface-variant">
            Anda akan menghapus slip <span id="delCoCode" class="font-mono font-bold text-on-surface"></span> senilai
            <span id="delCoAmount" class="font-bold text-error"></span>. Saldo rekening terkait akan otomatis dikembalikan.
        </p>
        <div class="flex items-center justify-end gap-space-sm pt-2">
            <button type="button" onclick="closeDeleteCashOut()" class="h-9 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm cursor-pointer">
                Batal
            </button>
            <button type="button" id="confirmDeleteCoBtn" class="h-9 px-space-md rounded-xl bg-error text-on-error font-headline-sm font-bold cursor-pointer">
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    var pendingFormToSubmit = null;
    var activeDeleteFormId  = null;
    var activeDetailData    = null;
    var filterTimer         = null;

    function fmtRp(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    }

    function escapeHtml(str) {
        return String(str || '-')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ---- Filter Kategori & Preset Tanggal ----
    window.selectCashOutCategory = function (val) {
        var input = document.getElementById('coCategoryInput');
        var form  = document.getElementById('coFilterForm');
        if (input && form) {
            input.value = val;
            form.submit();
        }
    };

    window.setCashOutQuickDate = function (mode) {
        var now = new Date();
        var pad = function (n) { return String(n).padStart(2, '0'); };
        var todayStr = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate());
        var fromEl = document.getElementById('coDateFrom');
        var toEl   = document.getElementById('coDateTo');
        var form   = document.getElementById('coFilterForm');
        if (!fromEl || !toEl || !form) return;

        if (mode === 'today') {
            fromEl.value = todayStr;
            toEl.value   = todayStr;
        } else if (mode === '7days') {
            var past = new Date(now.getTime() - (6 * 86400000));
            fromEl.value = past.getFullYear() + '-' + pad(past.getMonth() + 1) + '-' + pad(past.getDate());
            toEl.value   = todayStr;
        } else if (mode === 'month') {
            fromEl.value = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-01';
            toEl.value   = todayStr;
        }
        form.submit();
    };

    var searchEl = document.getElementById('coSearchInput');
    if (searchEl) {
        searchEl.addEventListener('input', function () {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(function () {
                var f = document.getElementById('coFilterForm');
                if (f) f.submit();
            }, 500);
        });
    }

    // ---- Modal Input Kas Keluar ----
    window.openCashOutInputModal = function (tab) {
        var modal = document.getElementById('cashOutInputModal');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        switchCashOutTab(tab || 'supplier');
    };

    window.closeCashOutInputModal = function () {
        var modal = document.getElementById('cashOutInputModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    window.switchCashOutTab = function (tab) {
        var supplierForm = document.getElementById('tabSupplierForm');
        var opsForm      = document.getElementById('tabOpsForm');
        var supplierBtn  = document.getElementById('tabSupplierBtn');
        var opsBtn       = document.getElementById('tabOpsBtn');
        if (!supplierForm || !opsForm) return;

        if (tab === 'supplier') {
            supplierForm.classList.remove('hidden');
            opsForm.classList.add('hidden');
            supplierBtn.className = 'flex-1 py-space-xs rounded-lg bg-primary-container text-on-primary-container font-headline-sm text-body-sm font-semibold flex items-center justify-center gap-space-xs shadow-sm transition-all cursor-pointer';
            opsBtn.className      = 'flex-1 py-space-xs rounded-lg text-on-surface-variant hover:text-on-surface font-headline-sm text-body-sm font-medium flex items-center justify-center gap-space-xs transition-all cursor-pointer';
        } else {
            supplierForm.classList.add('hidden');
            opsForm.classList.remove('hidden');
            supplierBtn.className = 'flex-1 py-space-xs rounded-lg text-on-surface-variant hover:text-on-surface font-headline-sm text-body-sm font-medium flex items-center justify-center gap-space-xs transition-all cursor-pointer';
            opsBtn.className      = 'flex-1 py-space-xs rounded-lg bg-primary-container text-on-primary-container font-headline-sm text-body-sm font-semibold flex items-center justify-center gap-space-xs shadow-sm transition-all cursor-pointer';
        }
    };

    window.updateSupplierForm = function () {
        var sel = document.getElementById('openTempoSelect');
        if (!sel || sel.selectedIndex < 0) return;
        var opt  = sel.options[sel.selectedIndex];
        var form = document.getElementById('paySupplierForm');
        if (form) form.action = opt.dataset.action;
        var amt = document.getElementById('payAmountInput');
        if (amt) {
            amt.value = opt.dataset.remaining;
            amt.max   = opt.dataset.remaining;
        }
    };

    window.setPayAmount = function (pct) {
        var sel = document.getElementById('openTempoSelect');
        var amt = document.getElementById('payAmountInput');
        if (!sel || !amt || sel.selectedIndex < 0) return;
        var remaining = parseInt(sel.options[sel.selectedIndex].dataset.remaining || 0, 10);
        amt.value = pct === '100' ? remaining : Math.floor(remaining / 2);
    };

    window.payFromWidget = function (trxId) {
        openCashOutInputModal('supplier');
        var sel = document.getElementById('openTempoSelect');
        if (!sel) return;
        var target = Array.from(sel.options).find(function (o) { return o.value == trxId; });
        if (target) {
            sel.value = trxId;
            updateSupplierForm();
        }
    };

    // ---- Modal Konfirmasi Double-Check Posting ----
    window.askPostConfirmation = function (type) {
        var form = document.getElementById(type === 'supplier' ? 'paySupplierForm' : 'opsExpenseForm');
        if (!form || !form.reportValidity()) return;

        pendingFormToSubmit = form;
        var checkedAcc = form.querySelector('input[name="account"]:checked');
        var accLabel   = checkedAcc ? checkedAcc.dataset.accLabel : '-';

        if (type === 'supplier') {
            var sel    = document.getElementById('openTempoSelect');
            var target = sel && sel.selectedIndex >= 0 ? sel.options[sel.selectedIndex].dataset.label : '-';
            var amount = document.getElementById('payAmountInput') ? document.getElementById('payAmountInput').value : 0;
            document.getElementById('confirmCoTarget').textContent  = target;
            document.getElementById('confirmCoAccount').textContent = accLabel;
            document.getElementById('confirmCoAmount').textContent  = '-' + fmtRp(amount);
        } else {
            var catSel   = document.getElementById('opsCategorySelect');
            var catLabel = catSel && catSel.selectedIndex >= 0 ? catSel.options[catSel.selectedIndex].text : '-';
            var amount   = document.getElementById('opsAmountInput') ? document.getElementById('opsAmountInput').value : 0;
            document.getElementById('confirmCoTarget').textContent  = catLabel;
            document.getElementById('confirmCoAccount').textContent = accLabel;
            document.getElementById('confirmCoAmount').textContent  = '-' + fmtRp(amount);
        }

        var modal = document.getElementById('confirmPostCashOutModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closePostConfirmation = function () {
        pendingFormToSubmit = null;
        var modal = document.getElementById('confirmPostCashOutModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    var execBtn = document.getElementById('executePostCoBtn');
    if (execBtn) {
        execBtn.addEventListener('click', function () {
            if (pendingFormToSubmit) {
                pendingFormToSubmit.submit();
            }
        });
    }

    // ---- Modal Detail & Cetak Langsung ----
    window.openCashOutDetail = function (btn) {
        var raw = btn.getAttribute('data-detail');
        if (!raw) return;
        activeDetailData = JSON.parse(raw);

        document.getElementById('coModalCode').textContent      = activeDetailData.code;
        document.getElementById('coModalTypeBadge').textContent = activeDetailData.is_auto ? 'AUTO-JOURNAL' : 'MANUAL INPUT';
        document.getElementById('coModalDate').textContent      = activeDetailData.paid_at;
        document.getElementById('coModalAccount').textContent   = activeDetailData.account;
        document.getElementById('coModalRecipient').textContent = activeDetailData.recipient;
        document.getElementById('coModalRef').textContent       = activeDetailData.reference_no;
        document.getElementById('coModalCategory').textContent  = activeDetailData.category;
        document.getElementById('coModalNotes').textContent     = activeDetailData.notes;
        document.getElementById('coModalAmount').textContent    = '-' + activeDetailData.amount;

        var modal = document.getElementById('cashOutDetailModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closeCashOutDetail = function () {
        var modal = document.getElementById('cashOutDetailModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    window.printCashOutDirect = function (btn) {
        var raw = btn.getAttribute('data-detail');
        if (!raw) return;
        renderAndPrintVoucher(JSON.parse(raw));
    };

    window.printCashOutSlip = function () {
        if (!activeDetailData) return;
        renderAndPrintVoucher(activeDetailData);
    };

    function renderAndPrintVoucher(d) {
        var win = window.open('', '_blank', 'width=460,height=640');
        if (!win) {
            window.alert('Izinkan pop-up pada browser untuk mencetak bukti kas keluar.');
            return;
        }

        var html = '' +
            '<!doctype html><html><head><title>Bukti Kas Keluar ' + escapeHtml(d.code) + '<\/title>' +
            '<style>' +
            'body{font-family:Arial,Helvetica,sans-serif;padding:24px;color:#111827;font-size:13px;line-height:1.5;}' +
            '.header{border-bottom:2px solid #111827;padding-bottom:12px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;}' +
            '.brand{font-size:16px;font-weight:800;letter-spacing:0.5px;}' +
            '.sub{font-size:11px;color:#4b5563;}' +
            '.badge{font-size:10px;font-weight:700;padding:3px 8px;border:1px solid #111827;border-radius:4px;}' +
            '.code-box{background:#f3f4f6;padding:10px 12px;border-radius:6px;margin-bottom:14px;display:flex;justify-content:space-between;}' +
            'table{width:100%;border-collapse:collapse;margin-bottom:14px;}' +
            'td{padding:6px 0;vertical-align:top;}' +
            'td.lbl{color:#4b5563;width:38%;}' +
            'td.val{font-weight:600;text-align:right;}' +
            '.notes{background:#f9fafb;border:1px solid #e5e7eb;padding:10px;border-radius:6px;margin-bottom:16px;}' +
            '.total-box{border-top:2px dashed #111827;border-bottom:2px dashed #111827;padding:12px 0;display:flex;justify-content:space-between;align-items:center;font-size:15px;font-weight:800;margin-bottom:28px;}' +
            '.sign{display:flex;justify-content:space-between;text-align:center;font-size:11px;color:#4b5563;margin-top:24px;}' +
            '.sign div{width:42%;border-top:1px solid #9ca3af;padding-top:6px;}' +
            '@media print{body{padding:0;}}' +
            '<\/style><\/head><body onload="window.print();window.close();">' +
            '<div class="header">' +
                '<div><div class="brand">SUPOS | SINAR UTAMA<\/div><div class="sub">Bukti Pengeluaran Kas (Cash Out Voucher)<\/div><\/div>' +
                '<span class="badge">' + (d.is_auto ? 'AUTO-JOURNAL' : 'MANUAL') + '<\/span>' +
            '<\/div>' +
            '<div class="code-box"><span>No. Voucher<\/span><strong>' + escapeHtml(d.code) + '<\/strong><\/div>' +
            '<table>' +
                '<tr><td class="lbl">Waktu Transaksi<\/td><td class="val">' + escapeHtml(d.paid_at) + '<\/td><\/tr>' +
                '<tr><td class="lbl">Penerima / Tujuan<\/td><td class="val">' + escapeHtml(d.recipient) + '<\/td><\/tr>' +
                '<tr><td class="lbl">Kategori<\/td><td class="val">' + escapeHtml(d.category) + '<\/td><\/tr>' +
                '<tr><td class="lbl">Akun Sumber<\/td><td class="val">' + escapeHtml(d.account) + '<\/td><\/tr>' +
                '<tr><td class="lbl">No. Referensi / Faktur<\/td><td class="val">' + escapeHtml(d.reference_no) + '<\/td><\/tr>' +
            '<\/table>' +
            '<div class="notes"><div style="font-size:11px;color:#6b7280;margin-bottom:2px;">Keterangan:<\/div><div>' + escapeHtml(d.notes) + '<\/div><\/div>' +
            '<div class="total-box"><span>TOTAL DANA KELUAR<\/span><span>-' + escapeHtml(d.amount) + '<\/span><\/div>' +
            '<div class="sign"><div>Disetujui / Kasir<\/div><div>Penerima Dana<\/div><\/div>' +
            '<\/body><\/html>';

        win.document.open();
        win.document.write(html);
        win.document.close();
    }

    // ---- Modal Hapus Entri Manual ----
    window.confirmDeleteCashOut = function (btn) {
        activeDeleteFormId = btn.getAttribute('data-form-id');
        document.getElementById('delCoCode').textContent   = btn.getAttribute('data-code');
        document.getElementById('delCoAmount').textContent = btn.getAttribute('data-amount');

        var modal = document.getElementById('deleteCashOutModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closeDeleteCashOut = function () {
        activeDeleteFormId = null;
        var modal = document.getElementById('deleteCashOutModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    var delBtn = document.getElementById('confirmDeleteCoBtn');
    if (delBtn) {
        delBtn.addEventListener('click', function () {
            if (activeDeleteFormId) {
                var form = document.getElementById(activeDeleteFormId);
                if (form) form.submit();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closePostConfirmation();
            closeCashOutInputModal();
            closeCashOutDetail();
            closeDeleteCashOut();
        }
    });

    updateSupplierForm();
})();
</script>