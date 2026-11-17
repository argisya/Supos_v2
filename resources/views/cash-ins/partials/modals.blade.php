{{-- ============ MODAL INPUT KAS MASUK MANUAL ============ --}}
<div id="createCashInModal" data-cash-in-modal role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="createCashInTitle" tabindex="-1"
     onclick="if(event.target === this) closeCreateCashInModal()"
     class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm items-center justify-center p-space-md">
    <div class="w-full max-w-lg rounded-2xl bg-surface-container-low shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest/30">
            <div class="flex items-center gap-space-xs">
                <div class="w-8 h-8 rounded-lg bg-primary-container/20 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">add_box</span>
                </div>
                <div class="flex flex-col">
                    <span id="createCashInTitle" class="font-headline-sm text-body-md font-bold text-on-surface">Input Kas Masuk Non-Penjualan</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Modal, Refund Supplier & Pendapatan Lain</span>
                </div>
            </div>
            <button type="button" onclick="closeCreateCashInModal()" class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant flex items-center justify-center cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('cash-ins.store') }}" class="p-space-lg overflow-y-auto flex flex-col space-y-space-md">
            @csrf
            <div class="flex flex-col gap-1.5">
                <label class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">Kategori Penerimaan Kas <span class="text-error">*</span></label>
                <select name="category" required class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="">— Pilih Kategori —</option>
                    <option value="refund_supplier"    @selected(old('category')==='refund_supplier')>Refund Supplier (Retur Rusak)</option>
                    <option value="modal_pemilik"      @selected(old('category')==='modal_pemilik')>Setoran Modal Pemilik</option>
                    <option value="penjualan_palet"    @selected(old('category')==='penjualan_palet')>Penjualan Palet / Krat Bekas</option>
                    <option value="pendapatan_non_ops" @selected(old('category')==='pendapatan_non_ops')>Pendapatan Non-Operasional & Bunga</option>
                    <option value="deposit_kembali"    @selected(old('category')==='deposit_kembali')>Pengembalian Deposit Toko</option>
                </select>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">Masuk ke Rekening / Kas Tujuan <span class="text-error">*</span></label>
                <div class="grid grid-cols-3 gap-space-xs">
                    @foreach($acctStyles as $key => $meta)
                        @php $isChecked = old('account', 'bca') === $key; @endphp
                        <label class="ci-account-card flex flex-col items-center justify-center p-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high cursor-pointer transition-all border {{ $isChecked ? 'border-primary bg-primary-container/10' : 'border-transparent' }}">
                            <input type="radio" name="account" value="{{ $key }}" class="hidden" @checked($isChecked) onchange="selectCashInAccount(this)" />
                            <span class="font-headline-sm text-body-sm font-bold text-on-surface">{{ $meta['short'] }}</span>
                            <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $meta['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <div class="flex justify-between items-center">
                    <label class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">Jumlah Nominal (Rp) <span class="text-error">*</span></label>
                    <span class="font-label-sm text-label-sm text-primary font-mono font-bold" id="nominalPreview">IDR 0</span>
                </div>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant font-mono font-bold text-sm">Rp</span>
                    <input name="amount" id="nominalInput" type="number" min="1" required
                           oninput="updateNominalPreview()"
                           value="{{ old('amount') }}" placeholder="0"
                           class="w-full h-11 pl-11 pr-space-md rounded-xl bg-surface-container text-on-surface font-headline-md text-headline-md font-bold focus:outline-none focus:ring-1 focus:ring-primary font-mono" />
                </div>
                <div class="flex flex-wrap items-center gap-space-xs pt-1">
                    @foreach([500000, 1000000, 5000000, 10000000] as $v)
                        <button type="button" onclick="setCashInNominal({{ $v }})"
                                class="px-2.5 py-1 rounded-lg bg-surface-container hover:bg-surface-bright font-mono font-label-sm text-label-sm text-on-surface-variant hover:text-on-surface transition-colors cursor-pointer">
                            +{{ $v >= 1_000_000 ? 'Rp ' . ($v/1_000_000) . ' Jt' : 'Rp ' . ($v/1000) . 'rb' }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-sm">
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">No. Referensi (Opsional)</label>
                    <input name="reference_no" type="text" value="{{ old('reference_no') }}"
                           placeholder="No. Bukti / Slip"
                           class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">Waktu Terima (Opsional)</label>
                    <input name="received_at" type="datetime-local" value="{{ old('received_at') }}"
                           class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="font-label-sm text-label-sm text-on-surface uppercase tracking-wider font-bold">Keterangan Audit Trail <span class="text-error">*</span></label>
                <input name="notes" type="text" required value="{{ old('notes') }}"
                       placeholder="Contoh: Refund minyak goreng bocor 3 dus dari PT Indomarco"
                       class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
            </div>

            <div class="pt-2 flex items-center justify-end gap-space-sm border-t border-surface-container-highest/30">
                <button type="button" onclick="closeCreateCashInModal()" class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm font-semibold cursor-pointer">Batal</button>
                <button type="submit" class="h-10 px-space-lg rounded-xl bg-primary-container hover:bg-primary text-on-primary-container font-headline-sm text-body-sm font-bold flex items-center gap-1.5 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    <span>Simpan & Posting</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============ MODAL DETAIL KAS MASUK ============ --}}
<div id="cashInDetailModal" data-cash-in-modal role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="cashInDetailTitle" tabindex="-1"
     onclick="if(event.target === this) closeCashInDetailModal()"
     class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm items-center justify-center p-space-md">
    <div class="w-full max-w-lg rounded-2xl bg-surface-container-low shadow-2xl overflow-hidden flex flex-col">
        <div class="p-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest/30">
            <div class="flex items-center gap-space-xs">
                <div class="w-8 h-8 rounded-lg bg-primary/20 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                </div>
                <div class="flex flex-col">
                    <span id="cashInDetailTitle" class="font-headline-sm text-body-md font-bold text-on-surface">Detail Mutasi Kas Masuk</span>
                    <span id="detCode" class="font-mono font-label-sm text-label-sm text-primary font-bold">KM-...</span>
                </div>
            </div>
            <button type="button" onclick="closeCashInDetailModal()" class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant flex items-center justify-center cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <div class="p-space-lg flex flex-col gap-space-sm font-body-sm text-body-sm">
            <div class="p-space-md rounded-xl bg-surface-container flex items-center justify-between">
                <span class="text-on-surface-variant">Nominal Diterima</span>
                <span id="detAmount" class="font-headline-md text-headline-md font-extrabold text-primary font-mono">Rp 0</span>
            </div>

            <div class="grid grid-cols-2 gap-space-sm pt-1">
                <div class="p-space-sm rounded-xl bg-surface-container/60">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Waktu Posting</span>
                    <span id="detTime" class="text-on-surface font-semibold">-</span>
                </div>
                <div class="p-space-sm rounded-xl bg-surface-container/60">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Akun Penampung</span>
                    <span id="detAccount" class="text-on-surface font-semibold">-</span>
                </div>
                <div class="p-space-sm rounded-xl bg-surface-container/60">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Kategori</span>
                    <span id="detCategory" class="text-on-surface font-semibold">-</span>
                </div>
                <div class="p-space-sm rounded-xl bg-surface-container/60">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">No. Referensi</span>
                    <span id="detRef" class="font-mono text-tertiary font-semibold">-</span>
                </div>
            </div>

            <div class="p-space-sm rounded-xl bg-surface-container/60">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Sumber Transaksi</span>
                <span id="detSource" class="text-on-surface font-semibold">-</span>
            </div>

            <div class="p-space-sm rounded-xl bg-surface-container/60">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Catatan / Keterangan Audit</span>
                <p id="detNotes" class="text-on-surface mt-0.5 leading-relaxed">-</p>
            </div>
        </div>

        <div class="p-space-md bg-surface-container flex items-center justify-end border-t border-surface-container-highest/30">
            <button type="button" onclick="closeCashInDetailModal()" class="h-9 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm font-semibold cursor-pointer">Tutup</button>
        </div>
    </div>
</div>

{{-- ============ MODAL BUKTI KAS MASUK (PRINT) ============ --}}
<div id="cashInReceiptModal" data-cash-in-modal role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="cashInReceiptTitle" tabindex="-1"
     onclick="if(event.target === this) closeCashInReceiptModal()"
     class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm items-center justify-center p-space-md">
    <div class="w-full max-w-md rounded-2xl bg-surface-container-low shadow-2xl overflow-hidden flex flex-col">
        <div class="p-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest/30">
            <span id="cashInReceiptTitle" class="font-headline-sm text-body-md font-bold text-on-surface flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary text-[20px]">print</span>
                Pratinjau Bukti Kas Masuk
            </span>
            <button type="button" onclick="closeCashInReceiptModal()" class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant flex items-center justify-center cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <div id="printableReceiptArea" class="p-space-lg bg-white text-slate-900 font-mono text-xs space-y-3">
            <div class="text-center border-b border-dashed border-slate-400 pb-2">
                <div class="font-bold text-sm uppercase">SUPOS | SINAR UTAMA</div>
                <div class="text-[11px] text-slate-600">BUKTI PENERIMAAN KAS (CASH IN SLIP)</div>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between"><span>No. Bukti:</span><strong id="recCode">-</strong></div>
                <div class="flex justify-between"><span>Tanggal:</span><span id="recTime">-</span></div>
                <div class="flex justify-between"><span>Akun Tujuan:</span><span id="recAccount">-</span></div>
                <div class="flex justify-between"><span>Kategori:</span><span id="recCategory">-</span></div>
                <div class="flex justify-between"><span>Dari / Sumber:</span><span id="recSource">-</span></div>
                <div class="flex justify-between"><span>No. Referensi:</span><span id="recRef">-</span></div>
            </div>
            <div class="border-y border-dashed border-slate-400 py-2 flex justify-between items-center text-sm font-bold">
                <span>TOTAL DITERIMA</span>
                <span id="recAmount">Rp 0</span>
            </div>
            <div>
                <span class="text-slate-600 block">Keterangan:</span>
                <p id="recNotes" class="mt-0.5">-</p>
            </div>
            <div class="pt-4 flex justify-between text-center text-[10px] text-slate-600">
                <div>Diserahkan Oleh,<br><br><br>( ____________ )</div>
                <div>Diterima Kasir/Admin,<br><br><br>( ____________ )</div>
            </div>
        </div>

        <div class="p-space-md bg-surface-container flex items-center justify-end gap-space-sm border-t border-surface-container-highest/30">
            <button type="button" onclick="closeCashInReceiptModal()" class="h-9 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm font-semibold cursor-pointer">Batal</button>
            <button type="button" onclick="printCashInSlip()" class="h-9 px-space-md rounded-xl bg-primary-container hover:bg-primary text-on-primary-container font-headline-sm text-body-sm font-bold flex items-center gap-1 cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">print</span>
                <span>Cetak Sekarang</span>
            </button>
        </div>
    </div>
</div>

{{-- ============ MODAL KONFIRMASI HAPUS MANUAL ============ --}}
<div id="deleteCashInModal" data-cash-in-modal role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="deleteCashInTitle" tabindex="-1"
     onclick="if(event.target === this) closeDeleteCashInModal()"
     class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm items-center justify-center p-space-md">
    <div class="w-full max-w-md rounded-2xl bg-surface-container-low shadow-2xl overflow-hidden flex flex-col">
        <div class="p-space-lg flex flex-col gap-space-sm">
            <div class="w-11 h-11 rounded-xl bg-error/20 text-error flex items-center justify-center">
                <span class="material-symbols-outlined text-[24px]">delete_forever</span>
            </div>
            <h3 id="deleteCashInTitle" class="font-headline-sm text-body-lg font-bold text-on-surface">Hapus Mutasi Kas Masuk Manual?</h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant">
                Anda akan menghapus entri manual <strong id="delCashInCode" class="text-on-surface font-mono">-</strong>. Saldo akun kas terkait akan otomatis disesuaikan.
            </p>
        </div>
        <form id="deleteCashInForm" method="POST" class="p-space-md bg-surface-container flex items-center justify-end gap-space-sm border-t border-surface-container-highest/30">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteCashInModal()" class="h-9 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm font-semibold cursor-pointer">Batal</button>
            <button type="submit" class="h-9 px-space-md rounded-xl bg-error hover:bg-error/90 text-on-error font-headline-sm text-body-sm font-bold cursor-pointer">Ya, Hapus Permanen</button>
        </form>
    </div>
</div>