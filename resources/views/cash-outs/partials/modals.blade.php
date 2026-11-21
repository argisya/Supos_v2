{{-- ============ 1. MODAL INPUT KAS KELUAR (SUPPLIER & OPERASIONAL) ============ --}}
<div id="cashOutInputModal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="cashOutInputTitle"
    tabindex="-1" onclick="if(event.target===this) closeCashOutInputModal()"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-space-md overflow-y-auto">
    <div
        class="w-full max-w-2xl rounded-2xl bg-surface-container-low shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div
            class="p-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest/30">
            <div class="flex items-center gap-space-xs">
                <div class="w-8 h-8 rounded-lg bg-primary-container/20 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">payments</span>
                </div>
                <div class="flex flex-col">
                    <span id="cashOutInputTitle" class="font-headline-sm text-body-md font-bold text-on-surface">Catat
                        Kas Keluar Baru</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Pelunasan Hutang
                        Supplier &amp; Biaya Operasional</span>
                </div>
            </div>
            <button type="button" onclick="closeCashOutInputModal()"
                class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant flex items-center justify-center cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        @php
            $firstAvailableAccount = collect($acctStyles)->keys()->first(fn($k) => ($balances[$k] ?? 0) > 0) ?? 'bca';
            $defaultAccount = old('account', $firstAvailableAccount);
        @endphp

        <div class="p-space-lg overflow-y-auto">
            {{-- Tab Switcher --}}
            <div class="flex items-center p-1 rounded-xl bg-surface-container mb-space-md">
                <button type="button" onclick="switchCashOutTab('supplier')" id="tabSupplierBtn"
                    class="flex-1 py-space-xs rounded-lg bg-primary text-on-primary font-headline-sm text-body-sm font-semibold flex items-center justify-center gap-space-xs shadow-sm transition-all cursor-pointer">
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
                    @php
                        $selectedTrx = $openTempo->firstWhere('id', old('selected_trx_id')) ?? $openTempo->first();
                    @endphp
                    <form method="POST" id="paySupplierForm"
                        action="{{ route('cash-outs.pay-supplier', $selectedTrx) }}"
                        onsubmit="if(!validateSupplierBalance()){ event.preventDefault(); return false; } const btn = document.getElementById('supSubmitBtn'); if(btn){ btn.disabled = true; btn.classList.add('opacity-60', 'cursor-not-allowed'); }"
                        class="flex flex-col space-y-space-md">
                        @csrf
                        <input type="hidden" name="active_tab" value="supplier">
                        <input type="hidden" name="selected_trx_id" id="selectedTrxIdInput"
                            value="{{ $selectedTrx->id }}">

                        <div class="flex flex-col gap-1.5">
                            <label
                                class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold flex items-center justify-between">
                                <span>Pilih Faktur Tempo Barang Masuk <span class="text-error">*</span></span>
                                <a href="{{ route('incoming-goods.index', ['status' => 'TEMPO']) }}"
                                    class="text-primary hover:underline text-xs font-semibold">Lihat Buku Hutang</a>
                            </label>
                            <div class="relative">
                                <select id="openTempoSelect" onchange="updateSupplierForm(false)"
                                    class="w-full h-11 pl-space-md pr-10 rounded-xl bg-surface-container text-on-surface font-body-sm appearance-none focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer">
                                    @foreach ($openTempo as $t)
                                        <option value="{{ $t->id }}"
                                            data-action="{{ route('cash-outs.pay-supplier', $t) }}"
                                            data-remaining="{{ (int) $t->remaining }}" @selected((string) $selectedTrx->id === (string) $t->id)>
                                            {{ $t->supplier?->name }} — Faktur {{ $t->no_faktur }} (Sisa:
                                            {{ $fmt($t->remaining) }}, JT: {{ $t->due_date?->format('d M Y') ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                <span
                                    class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[20px]">expand_more</span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label
                                class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">Sumber
                                Dana / Rekening <span class="text-error">*</span></label>
                            <div class="grid grid-cols-3 gap-space-xs">
                                @foreach ($acctStyles as $key => $meta)
                                    @php
                                        $accBal = (float) ($balances[$key] ?? 0);
                                        $isChecked = $defaultAccount === $key;
                                    @endphp
                                    <label
                                        class="co-sup-account-card flex flex-col items-center justify-center p-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high cursor-pointer transition-all border {{ $isChecked ? 'border-primary bg-primary-container/10' : 'border-transparent' }} {{ $accBal <= 0 ? 'opacity-60' : '' }}">
                                        <input type="radio" name="account" value="{{ $key }}"
                                            data-balance="{{ (int) $accBal }}" class="hidden"
                                            @checked($isChecked)
                                            onchange="selectCashOutAccount(this, '.co-sup-account-card'); validateSupplierBalance();" />
                                        <span
                                            class="font-headline-sm text-body-sm font-bold text-on-surface">{{ $meta['short'] }}</span>
                                        <span
                                            class="font-label-sm text-label-sm text-on-surface-variant truncate">{{ $meta['label'] }}</span>
                                        <span
                                            class="font-label-sm text-label-sm {{ $accBal <= 0 ? 'text-error font-semibold' : 'text-primary font-semibold' }}">
                                            {{ $accBal <= 0 ? 'Saldo Kosong' : 'Sisa ' . $fmtJt($accBal) }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <label
                                    class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">Jumlah
                                    Pembayaran (Rp) <span class="text-error">*</span></label>
                                <div class="flex items-center gap-1.5">
                                    <span id="supNominalPreview"
                                        class="font-label-sm text-label-sm text-primary font-mono font-bold mr-1">Rp
                                        0</span>
                                    <button type="button" onclick="setPayAmount('50')"
                                        class="px-2.5 py-0.5 rounded-lg bg-surface-container hover:bg-surface-bright text-primary text-label-sm font-bold cursor-pointer">50%</button>
                                    <button type="button" onclick="setPayAmount('100')"
                                        class="px-2.5 py-0.5 rounded-lg bg-surface-container hover:bg-surface-bright text-primary text-label-sm font-bold cursor-pointer">100%
                                        Lunas</button>
                                </div>
                            </div>
                            <div class="relative">
                                <span
                                    class="absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant font-mono font-bold text-sm">Rp</span>
                                <input name="amount" id="payAmountInput" type="number" step="1" min="1"
                                    required
                                    onkeydown="if(['e','E','+','-','.',','].includes(event.key)) event.preventDefault();"
                                    oninput="updateCoNominalPreview('payAmountInput', 'supNominalPreview'); validateSupplierBalance();"
                                    value="{{ old('active_tab') === 'supplier' ? old('amount', (int) $selectedTrx->remaining) : (int) $selectedTrx->remaining }}"
                                    class="w-full h-11 pl-11 pr-space-md rounded-xl bg-surface-container font-mono text-headline-md text-on-surface font-bold focus:outline-none focus:ring-1 focus:ring-primary" />
                            </div>
                            <p id="supBalanceWarning"
                                class="hidden font-label-sm text-label-sm text-error font-semibold flex items-center gap-1 mt-0.5">
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-sm">
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">Tanggal
                                    &amp; Waktu (WIB) <span class="text-error">*</span></label>
                                <input name="paid_at" id="supPaidAtInput" type="datetime-local" required
                                    value="{{ old('active_tab') === 'supplier' ? old('paid_at', now('Asia/Jakarta')->format('Y-m-d\TH:i')) : now('Asia/Jakarta')->format('Y-m-d\TH:i') }}"
                                    class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">Catatan
                                    / Referensi</label>
                                <input name="notes" type="text"
                                    value="{{ old('active_tab') === 'supplier' ? old('notes') : '' }}"
                                    placeholder="Contoh: Termin 1 PO-9921"
                                    class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                            </div>
                        </div>

                        <div
                            class="pt-2 flex items-center justify-end gap-space-sm border-t border-surface-container-highest/30">
                            <button type="button" onclick="closeCashOutInputModal()"
                                class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm font-semibold cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" id="supSubmitBtn"
                                class="h-10 px-space-lg rounded-xl bg-primary hover:bg-primary/90 text-on-primary font-headline-sm text-body-sm font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                <span>Simpan &amp; Posting</span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            {{-- TAB 2: BIAYA OPERASIONAL --}}
            <div id="tabOpsForm" class="hidden">
                <form method="POST" id="opsExpenseForm" action="{{ route('cash-outs.store') }}"
                    onsubmit="if(!validateOperationalBalance()){ event.preventDefault(); return false; } const btn = document.getElementById('opsSubmitBtn'); if(btn){ btn.disabled = true; btn.classList.add('opacity-60', 'cursor-not-allowed'); }"
                    class="flex flex-col space-y-space-md">
                    @csrf
                    <input type="hidden" name="active_tab" value="ops">

                    <div class="flex flex-col gap-1.5">
                        <label
                            class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">Kategori
                            Pengeluaran <span class="text-error">*</span></label>
                        <select name="category" id="opsCategorySelect" required
                            class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="">— Pilih Kategori —</option>
                            <option value="biaya_operasional" @selected(old('active_tab') === 'ops' && old('category') === 'biaya_operasional')>Biaya Operasional Umum
                            </option>
                            <option value="biaya_listrik" @selected(old('active_tab') === 'ops' && old('category') === 'biaya_listrik')>Listrik &amp; Utilitas</option>
                            <option value="gaji_staff" @selected(old('active_tab') === 'ops' && old('category') === 'gaji_staff')>Gaji &amp; Kasbon Staff</option>
                            <option value="logistik_armada" @selected(old('active_tab') === 'ops' && old('category') === 'logistik_armada')>Logistik &amp; Armada</option>
                            <option value="bensin_distribusi" @selected(old('active_tab') === 'ops' && old('category') === 'bensin_distribusi')>Bensin &amp; Tol Distribusi
                            </option>
                            <option value="lain_lain" @selected(old('active_tab') === 'ops' && old('category') === 'lain_lain')>Pengeluaran Lain-lain</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label
                            class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">Sumber
                            Dana / Rekening <span class="text-error">*</span></label>
                        <div class="grid grid-cols-3 gap-space-xs">
                            @foreach ($acctStyles as $key => $meta)
                                @php
                                    $accBal = (float) ($balances[$key] ?? 0);
                                    $isChecked = $defaultAccount === $key;
                                @endphp
                                <label
                                    class="co-ops-account-card flex flex-col items-center justify-center p-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high cursor-pointer transition-all border {{ $isChecked ? 'border-primary bg-primary-container/10' : 'border-transparent' }} {{ $accBal <= 0 ? 'opacity-60' : '' }}">
                                    <input type="radio" name="account" value="{{ $key }}"
                                        data-balance="{{ (int) $accBal }}" class="hidden"
                                        @checked($isChecked)
                                        onchange="selectCashOutAccount(this, '.co-ops-account-card'); validateOperationalBalance();" />
                                    <span
                                        class="font-headline-sm text-body-sm font-bold text-on-surface">{{ $meta['short'] }}</span>
                                    <span
                                        class="font-label-sm text-label-sm text-on-surface-variant truncate">{{ $meta['label'] }}</span>
                                    <span
                                        class="font-label-sm text-label-sm {{ $accBal <= 0 ? 'text-error font-semibold' : 'text-primary font-semibold' }}">
                                        {{ $accBal <= 0 ? 'Saldo Kosong' : 'Sisa ' . $fmtJt($accBal) }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <div class="flex justify-between items-center">
                            <label
                                class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">Nominal
                                Pengeluaran (Rp) <span class="text-error">*</span></label>
                            <span id="opsNominalPreview"
                                class="font-label-sm text-label-sm text-primary font-mono font-bold">Rp 0</span>
                        </div>
                        <div class="relative">
                            <span
                                class="absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant font-mono font-bold text-sm">Rp</span>
                            <input name="amount" id="opsAmountInput" type="number" step="1" min="1"
                                required
                                onkeydown="if(['e','E','+','-','.',','].includes(event.key)) event.preventDefault();"
                                oninput="updateCoNominalPreview('opsAmountInput', 'opsNominalPreview'); validateOperationalBalance();"
                                value="{{ old('active_tab') === 'ops' ? old('amount') : '' }}" placeholder="0"
                                class="w-full h-11 pl-11 pr-space-md rounded-xl bg-surface-container text-on-surface font-headline-md text-headline-md font-bold focus:outline-none focus:ring-1 focus:ring-primary font-mono" />
                        </div>
                        <p id="opsBalanceWarning"
                            class="hidden font-label-sm text-label-sm text-error font-semibold flex items-center gap-1 mt-0.5">
                        </p>
                        <div class="flex flex-wrap items-center gap-space-xs pt-1">
                            @foreach ([100000, 250000, 500000, 1000000, 5000000] as $v)
                                <button type="button" onclick="setOpsNominal({{ $v }})"
                                    class="px-2.5 py-1 rounded-lg bg-surface-container hover:bg-surface-bright font-mono font-label-sm text-label-sm text-on-surface-variant hover:text-on-surface transition-colors cursor-pointer">
                                    {{ $v >= 1_000_000 ? 'Rp ' . $v / 1_000_000 . ' Jt' : 'Rp ' . $v / 1000 . ' Rb' }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-sm">
                        <div class="flex flex-col gap-1.5">
                            <label
                                class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">Tanggal
                                &amp; Waktu (WIB) <span class="text-error">*</span></label>
                            <input name="paid_at" id="opsPaidAtInput" type="datetime-local" required
                                value="{{ old('active_tab') === 'ops' ? old('paid_at', now('Asia/Jakarta')->format('Y-m-d\TH:i')) : now('Asia/Jakarta')->format('Y-m-d\TH:i') }}"
                                class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label
                                class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">Keterangan
                                Pengeluaran <span class="text-error">*</span></label>
                            <input name="notes" type="text" required
                                value="{{ old('active_tab') === 'ops' ? old('notes') : '' }}"
                                placeholder="Contoh: Tagihan listrik &amp; cold storage"
                                class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                        </div>
                    </div>

                    <div
                        class="pt-2 flex items-center justify-end gap-space-sm border-t border-surface-container-highest/30">
                        <button type="button" onclick="closeCashOutInputModal()"
                            class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm font-semibold cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" id="opsSubmitBtn"
                            class="h-10 px-space-lg rounded-xl bg-primary hover:bg-primary/90 text-on-primary font-headline-sm text-body-sm font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-[18px]">check_circle</span>
                            <span>Simpan &amp; Posting</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ============ 2. MODAL DETAIL BUKTI KAS KELUAR (SIMETRIS DENGAN KAS MASUK) ============ --}}
<div id="cashOutDetailModal" role="dialog" aria-modal="true" aria-hidden="true"
    aria-labelledby="cashOutDetailTitle" tabindex="-1" onclick="if(event.target===this) closeCashOutDetail()"
    class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm items-center justify-center p-space-md">
    <div class="w-full max-w-lg rounded-2xl bg-surface-container-low shadow-2xl overflow-hidden flex flex-col">
        <div
            class="p-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest/30">
            <div class="flex items-center gap-space-xs">
                <div class="w-8 h-8 rounded-lg bg-error/15 text-error flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                </div>
                <div class="flex flex-col">
                    <span id="cashOutDetailTitle"
                        class="font-headline-sm text-body-md font-bold text-on-surface">Detail Mutasi Kas Keluar</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Rincian Pencatatan Jurnal
                        Pengeluaran</span>
                </div>
            </div>
            <button type="button" onclick="closeCashOutDetail()"
                class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant flex items-center justify-center cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <div class="p-space-lg flex flex-col gap-space-sm font-body-sm text-body-sm">
            <div class="p-space-md rounded-xl bg-surface-container flex items-center justify-between">
                <span class="text-on-surface-variant">Nominal Dikeluarkan</span>
                <span id="coModalAmount"
                    class="font-headline-md text-headline-md font-extrabold text-error font-mono">-Rp 0</span>
            </div>

            <div class="grid grid-cols-2 gap-space-sm pt-1">
                <div class="p-space-sm rounded-xl bg-surface-container/60">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Tanggal &amp;
                        Waktu</span>
                    <span id="coModalDate" class="text-on-surface font-semibold">-</span>
                </div>
                <div class="p-space-sm rounded-xl bg-surface-container/60">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Akun
                        Sumber</span>
                    <span id="coModalAccount" class="text-on-surface font-semibold">-</span>
                </div>
                <div class="p-space-sm rounded-xl bg-surface-container/60">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Kategori</span>
                    <span id="coModalCategory" class="text-on-surface font-semibold">-</span>
                </div>
                <div class="p-space-sm rounded-xl bg-surface-container/60">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">No.
                        Referensi</span>
                    <span id="coModalRef" class="font-mono text-tertiary font-semibold">-</span>
                </div>
            </div>

            <div class="p-space-sm rounded-xl bg-surface-container/60">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Tujuan /
                    Penerima</span>
                <span id="coModalRecipient" class="text-on-surface font-semibold">-</span>
            </div>

            <div class="p-space-sm rounded-xl bg-surface-container/60">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Catatan /
                    Keterangan</span>
                <p id="coModalNotes" class="text-on-surface mt-0.5 leading-relaxed">-</p>
            </div>
        </div>

        <div
            class="p-space-md bg-surface-container flex items-center justify-end gap-space-sm flex-wrap border-t border-surface-container-highest/30">
            <a id="coModalIncomingGoodLink" href="#"
                class="hidden h-10 px-space-md rounded-xl bg-secondary/15 hover:bg-secondary/25 text-secondary font-headline-sm text-body-sm font-bold items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-[18px]">inventory_2</span>
                <span id="coModalIncomingGoodLabel">Lihat Faktur Tempo</span>
            </a>
            <button type="button" onclick="closeCashOutDetail()"
                class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm font-semibold cursor-pointer">Tutup</button>
            <a id="coModalReceiptLink" href="#" target="_blank"
                class="h-10 px-space-lg rounded-xl bg-primary hover:bg-primary/90 text-on-primary font-headline-sm text-body-sm font-bold flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-[18px]">print</span>
                <span>Cetak Bukti</span>
            </a>
        </div>
    </div>
</div>

{{-- ============ 3. MODAL KONFIRMASI HAPUS ENTRI MANUAL ============ --}}
<div id="deleteCashOutModal" role="dialog" aria-modal="true" aria-hidden="true"
    aria-labelledby="deleteCashOutTitle" tabindex="-1" onclick="if(event.target===this) closeDeleteCashOut()"
    class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm items-center justify-center p-space-md">
    <div class="w-full max-w-md rounded-2xl bg-surface-container-low shadow-2xl overflow-hidden flex flex-col">
        <div class="p-space-lg flex flex-col gap-space-sm">
            <div class="w-11 h-11 rounded-xl bg-error/20 text-error flex items-center justify-center">
                <span class="material-symbols-outlined text-[24px]">delete_forever</span>
            </div>
            <h3 id="deleteCashOutTitle" class="font-headline-sm text-body-lg font-bold text-on-surface">Hapus Mutasi
                Kas Keluar Manual?</h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant">
                Anda akan menghapus entri kas keluar manual ini secara permanen. Saldo akun terkait akan otomatis
                dikembalikan.
            </p>
        </div>
        <form id="deleteCashOutForm" method="POST"
            class="p-space-md bg-surface-container flex items-center justify-end gap-space-sm border-t border-surface-container-highest/30">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteCashOut()"
                class="h-9 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm font-semibold cursor-pointer">Batal</button>
            <button type="submit"
                class="h-9 px-space-md rounded-xl bg-error hover:bg-error/90 text-on-error font-headline-sm text-body-sm font-bold cursor-pointer">Ya,
                Hapus Permanen</button>
        </form>
    </div>
</div>

<script>
    (function() {
        var filterTimer = null;

        function showModal(el) {
            if (!el) return;
            el.classList.remove('hidden');
            el.classList.add('flex');
            el.setAttribute('aria-hidden', 'false');
        }

        function hideModal(el) {
            if (!el) return;
            el.classList.add('hidden');
            el.classList.remove('flex');
            el.setAttribute('aria-hidden', 'true');
        }

        function fmtRp(n) {
            return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
        }

        function getCheckedAccountBalance(formEl) {
            if (!formEl) return 0;
            var checked = formEl.querySelector('input[name="account"]:checked');
            if (!checked) return 0;
            return parseFloat(checked.getAttribute('data-balance') || '0') || 0;
        }

        function setSubmitBtnDisabled(btn, disabled) {
            if (!btn) return;
            btn.disabled = disabled;
            btn.classList.toggle('opacity-50', disabled);
            btn.classList.toggle('cursor-not-allowed', disabled);
        }

        function clearValidationArtifacts() {
            var banner = document.getElementById('coValidationBanner');
            if (banner) banner.remove();
        }

        window.validateSupplierBalance = function() {
            var formEl = document.getElementById('paySupplierForm');
            var amountEl = document.getElementById('payAmountInput');
            var warnEl = document.getElementById('supBalanceWarning');
            var submitEl = document.getElementById('supSubmitBtn');
            var selEl = document.getElementById('openTempoSelect');

            if (!formEl) return false;

            var balance = getCheckedAccountBalance(formEl);
            var amount = parseFloat(amountEl ? amountEl.value : '0') || 0;
            var remaining = 0;
            if (selEl && selEl.selectedIndex >= 0) {
                remaining = parseFloat(selEl.options[selEl.selectedIndex].dataset.remaining || '0') || 0;
            }

            var errorMsg = '';
            if (balance <= 0) {
                errorMsg = 'Saldo rekening terpilih Rp 0. Silakan pilih rekening lain yang memiliki saldo.';
            } else if (amount > balance) {
                errorMsg = 'Nominal pembayaran (' + fmtRp(amount) + ') melebihi saldo rekening (' + fmtRp(
                    balance) + ').';
            } else if (remaining > 0 && amount > remaining) {
                errorMsg = 'Nominal pembayaran melebihi sisa tagihan faktur (' + fmtRp(remaining) + ').';
            }

            if (warnEl) {
                if (errorMsg) {
                    warnEl.textContent = errorMsg;
                    warnEl.classList.remove('hidden');
                } else {
                    warnEl.textContent = '';
                    warnEl.classList.add('hidden');
                }
            }

            var isInvalid = balance <= 0 || amount <= 0 || amount > balance || (remaining > 0 && amount >
                remaining);
            setSubmitBtnDisabled(submitEl, isInvalid);
            return !isInvalid;
        };

        window.validateOperationalBalance = function() {
            var formEl = document.getElementById('opsExpenseForm');
            var amountEl = document.getElementById('opsAmountInput');
            var warnEl = document.getElementById('opsBalanceWarning');
            var submitEl = document.getElementById('opsSubmitBtn');

            if (!formEl) return false;

            var balance = getCheckedAccountBalance(formEl);
            var amount = parseFloat(amountEl ? amountEl.value : '0') || 0;

            var errorMsg = '';
            if (balance <= 0) {
                errorMsg = 'Saldo rekening terpilih Rp 0. Silakan pilih rekening lain yang memiliki saldo.';
            } else if (amount > balance) {
                errorMsg = 'Nominal pengeluaran (' + fmtRp(amount) + ') melebihi saldo rekening (' + fmtRp(
                    balance) + ').';
            }

            if (warnEl) {
                if (errorMsg) {
                    warnEl.textContent = errorMsg;
                    warnEl.classList.remove('hidden');
                } else {
                    warnEl.textContent = '';
                    warnEl.classList.add('hidden');
                }
            }

            var isInvalid = balance <= 0 || amount <= 0 || amount > balance;
            setSubmitBtnDisabled(submitEl, isInvalid);
            return !isInvalid;
        };

        window.updateCoNominalPreview = function(inputId, previewId) {
            var input = document.getElementById(inputId);
            var preview = document.getElementById(previewId);
            if (!input || !preview) return;
            var val = parseFloat(input.value) || 0;
            preview.textContent = fmtRp(val);
        };

        window.setOpsNominal = function(val) {
            var input = document.getElementById('opsAmountInput');
            if (!input) return;
            input.value = val;
            updateCoNominalPreview('opsAmountInput', 'opsNominalPreview');
            validateOperationalBalance();
        };

        window.selectCashOutAccount = function(radio, cardSelector) {
            document.querySelectorAll(cardSelector).forEach(function(card) {
                card.classList.remove('border-primary', 'bg-primary-container/10');
                card.classList.add('border-transparent');
            });
            var active = radio.closest(cardSelector);
            if (active) {
                active.classList.remove('border-transparent');
                active.classList.add('border-primary', 'bg-primary-container/10');
            }
        };

        window.selectCashOutCategory = function(val) {
            var input = document.getElementById('coCategoryInput');
            var form = document.getElementById('coFilterForm');
            if (input && form) {
                input.value = val;
                form.submit();
            }
        };

        window.prepareCashOutDateRange = function() {
            var fromEl = document.getElementById('coDateFrom');
            var toEl = document.getElementById('coDateTo');
            if (!fromEl || !toEl) return;

            if (fromEl.value && toEl.value && fromEl.value > toEl.value) {
                var tmp = fromEl.value;
                fromEl.value = toEl.value;
                toEl.value = tmp;
            }
        };

        window.submitCashOutDateFilter = function() {
            var form = document.getElementById('coFilterForm');
            if (!form) return;
            prepareCashOutDateRange();
            form.submit();
        };

        window.setCashOutQuickDate = function(mode) {
            var nowWib = new Date(new Date().toLocaleString('en-US', {
                timeZone: 'Asia/Jakarta'
            }));
            var pad = function(n) {
                return String(n).padStart(2, '0');
            };
            var todayStr = nowWib.getFullYear() + '-' + pad(nowWib.getMonth() + 1) + '-' + pad(nowWib
                .getDate());
            var fromEl = document.getElementById('coDateFrom');
            var toEl = document.getElementById('coDateTo');
            var form = document.getElementById('coFilterForm');
            if (!fromEl || !toEl || !form) return;

            if (mode === 'today') {
                fromEl.value = todayStr;
                toEl.value = todayStr;
            } else if (mode === '7days') {
                var past = new Date(nowWib.getTime() - (6 * 86400000));
                fromEl.value = past.getFullYear() + '-' + pad(past.getMonth() + 1) + '-' + pad(past.getDate());
                toEl.value = todayStr;
            } else if (mode === 'month') {
                fromEl.value = nowWib.getFullYear() + '-' + pad(nowWib.getMonth() + 1) + '-01';
                toEl.value = todayStr;
            }
            form.submit();
        };

        var searchEl = document.getElementById('coSearchInput');
        if (searchEl) {
            searchEl.addEventListener('input', function() {
                clearTimeout(filterTimer);
                filterTimer = setTimeout(function() {
                    var f = document.getElementById('coFilterForm');
                    if (f) f.submit();
                }, 500);
            });
            searchEl.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    clearTimeout(filterTimer);
                }
            });
        }

        window.openCashOutInputModal = function(tab, preserveValidationBanner, presetCategory) {
            var modal = document.getElementById('cashOutInputModal');
            var targetTab = tab || @json($defaultInputTab);
            showModal(modal);
            switchCashOutTab(targetTab, Boolean(preserveValidationBanner));

            if (targetTab === 'ops' && presetCategory) {
                var catEl = document.getElementById('opsCategorySelect');
                if (catEl) catEl.value = presetCategory;
            }

            setTimeout(function() {
                if (targetTab === 'ops') {
                    var catEl = document.getElementById('opsCategorySelect');
                    if (catEl) catEl.focus();
                } else {
                    var selEl = document.getElementById('openTempoSelect');
                    if (selEl) selEl.focus();
                }
            }, 50);
        };

        window.closeCashOutInputModal = function() {
            clearValidationArtifacts();
            var supWarn = document.getElementById('supBalanceWarning');
            var opsWarn = document.getElementById('opsBalanceWarning');
            if (supWarn) {
                supWarn.textContent = '';
                supWarn.classList.add('hidden');
            }
            if (opsWarn) {
                opsWarn.textContent = '';
                opsWarn.classList.add('hidden');
            }

            var opsForm = document.getElementById('opsExpenseForm');
            if (opsForm) {
                opsForm.reset();
                updateCoNominalPreview('opsAmountInput', 'opsNominalPreview');
            }

            var supForm = document.getElementById('paySupplierForm');
            if (supForm) {
                supForm.reset();
                updateSupplierForm(false);
            }

            hideModal(document.getElementById('cashOutInputModal'));
        };

        window.switchCashOutTab = function(tab, preserveValidationBanner) {
            var supplierForm = document.getElementById('tabSupplierForm');
            var opsForm = document.getElementById('tabOpsForm');
            var supplierBtn = document.getElementById('tabSupplierBtn');
            var opsBtn = document.getElementById('tabOpsBtn');
            if (!supplierForm || !opsForm) return;

            if (!preserveValidationBanner) {
                clearValidationArtifacts();
            }

            if (tab === 'supplier') {
                supplierForm.classList.remove('hidden');
                opsForm.classList.add('hidden');
                supplierBtn.className =
                    'flex-1 py-space-xs rounded-lg bg-primary text-on-primary font-headline-sm text-body-sm font-semibold flex items-center justify-center gap-space-xs shadow-sm transition-all cursor-pointer';
                opsBtn.className =
                    'flex-1 py-space-xs rounded-lg text-on-surface-variant hover:text-on-surface font-headline-sm text-body-sm font-medium flex items-center justify-center gap-space-xs transition-all cursor-pointer';
                validateSupplierBalance();
            } else {
                supplierForm.classList.add('hidden');
                opsForm.classList.remove('hidden');
                supplierBtn.className =
                    'flex-1 py-space-xs rounded-lg text-on-surface-variant hover:text-on-surface font-headline-sm text-body-sm font-medium flex items-center justify-center gap-space-xs transition-all cursor-pointer';
                opsBtn.className =
                    'flex-1 py-space-xs rounded-lg bg-primary text-on-primary font-headline-sm text-body-sm font-semibold flex items-center justify-center gap-space-xs shadow-sm transition-all cursor-pointer';
                validateOperationalBalance();
            }
        };

        window.updateSupplierForm = function(preserveAmount) {
            var sel = document.getElementById('openTempoSelect');
            if (!sel || sel.selectedIndex < 0) return;
            var opt = sel.options[sel.selectedIndex];
            var form = document.getElementById('paySupplierForm');
            var trxIn = document.getElementById('selectedTrxIdInput');
            if (form) form.action = opt.dataset.action;
            if (trxIn) trxIn.value = opt.value;
            var amt = document.getElementById('payAmountInput');
            if (amt) {
                amt.max = opt.dataset.remaining;
                if (!preserveAmount) {
                    amt.value = opt.dataset.remaining;
                }
                updateCoNominalPreview('payAmountInput', 'supNominalPreview');
            }
            validateSupplierBalance();
        };

        window.setPayAmount = function(pct) {
            var sel = document.getElementById('openTempoSelect');
            var amt = document.getElementById('payAmountInput');
            if (!sel || !amt || sel.selectedIndex < 0) return;
            var remaining = parseInt(sel.options[sel.selectedIndex].dataset.remaining || 0, 10);
            amt.value = pct === '100' ? remaining : Math.floor(remaining / 2);
            updateCoNominalPreview('payAmountInput', 'supNominalPreview');
            validateSupplierBalance();
        };

        window.payFromWidget = function(trxId) {
            openCashOutInputModal('supplier');
            var sel = document.getElementById('openTempoSelect');
            if (!sel) return;
            var target = Array.from(sel.options).find(function(o) {
                return o.value == trxId;
            });
            if (target) {
                sel.value = trxId;
                updateSupplierForm(false);
            }
        };

        window.openCashOutDetail = function(btn) {
            var raw = btn.getAttribute('data-detail');
            if (!raw) return;
            var d = JSON.parse(raw);

            document.getElementById('coModalAmount').textContent = d.amount;
            document.getElementById('coModalDate').textContent = d.paid_at;
            document.getElementById('coModalAccount').textContent = d.account;
            document.getElementById('coModalCategory').textContent = d.category;
            document.getElementById('coModalRef').textContent = d.reference_no;
            document.getElementById('coModalRecipient').textContent = d.recipient + ' (' + d.source_type + ')';
            document.getElementById('coModalNotes').textContent = d.notes;

            var receiptLink = document.getElementById('coModalReceiptLink');
            if (receiptLink && d.receipt_url) {
                receiptLink.href = d.receipt_url;
            }

            var incomingLink = document.getElementById('coModalIncomingGoodLink');
            var incomingLabel = document.getElementById('coModalIncomingGoodLabel');
            if (incomingLink) {
                if (d.is_auto && d.incoming_good_url) {
                    incomingLink.href = d.incoming_good_url;
                    if (incomingLabel) {
                        incomingLabel.textContent = d.incoming_good_label || 'Lihat Faktur Tempo';
                    }
                    incomingLink.classList.remove('hidden');
                    incomingLink.classList.add('inline-flex');
                } else {
                    incomingLink.href = '#';
                    incomingLink.classList.add('hidden');
                    incomingLink.classList.remove('inline-flex');
                }
            }

            showModal(document.getElementById('cashOutDetailModal'));
        };

        window.closeCashOutDetail = function() {
            hideModal(document.getElementById('cashOutDetailModal'));
        };

        window.confirmDeleteCashOut = function(btn) {
            if (!btn || !btn.dataset) return;
            document.getElementById('deleteCashOutForm').action = btn.dataset.url;
            showModal(document.getElementById('deleteCashOutModal'));
        };

        window.closeDeleteCashOut = function() {
            hideModal(document.getElementById('deleteCashOutModal'));
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === '/' && searchEl && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document
                    .activeElement?.tagName)) {
                e.preventDefault();
                searchEl.focus();
                searchEl.select();
            }
            if (e.key === 'Escape') {
                closeCashOutInputModal();
                closeCashOutDetail();
                closeDeleteCashOut();
            }
        });

        updateSupplierForm({{ old('active_tab') === 'supplier' && old('amount') !== null ? 'true' : 'false' }});
        updateCoNominalPreview('opsAmountInput', 'opsNominalPreview');
        validateSupplierBalance();
        validateOperationalBalance();
    })();
</script>