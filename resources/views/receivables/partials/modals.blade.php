{{-- ============ 1. MODAL CATAT PELUNASAN TAGIHAN ============ --}}
<div id="paymentModal"
    class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-space-md overflow-y-auto">
    <div
        class="w-full max-w-xl my-auto rounded-2xl bg-surface-container-low shadow-[0_20px_50px_rgba(0,0,0,0.8)] overflow-hidden flex flex-col">
        <div class="p-space-md bg-surface-container flex items-center justify-between">
            <div class="flex items-center gap-space-xs">
                <div class="w-8 h-8 rounded-lg bg-primary/20 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">add_card</span>
                </div>
                <div class="flex flex-col">
                    <span class="font-headline-sm text-body-md font-bold text-on-surface">Form Catat Pelunasan
                        Tagihan</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Buku Piutang • Auto Posting Kas
                        Masuk</span>
                </div>
            </div>
            <button type="button" onclick="closePaymentModal()"
                class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        @if ($allTempoTrx->isNotEmpty())
            @php
                $defaultTrxId = session('failed_trx_id') ?? $allTempoTrx->first()->id;
                $defaultTrx = $allTempoTrx->firstWhere('id', $defaultTrxId) ?? $allTempoTrx->first();
            @endphp
            <form id="modalPayForm" action="{{ route('receivables.pay', $defaultTrx) }}" method="POST"
                enctype="multipart/form-data" class="p-space-lg flex flex-col gap-space-md">
                @csrf

                @if ($errors->any())
                    <div class="p-space-sm rounded-xl bg-error/10 border border-error/30 text-error font-body-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold">Pilih
                            Faktur Tagihan Mitra</label>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">Sisa: <strong
                                id="modalRemainingInfo"
                                class="text-error">{{ $fmt($defaultTrx->remaining) }}</strong></span>
                    </div>
                    <select id="modalTrxSelect" onchange="updateModalAction()"
                        class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        @foreach ($allTempoTrx as $t)
                            <option value="{{ $t->id }}" data-action="{{ route('receivables.pay', $t) }}"
                                data-remaining="{{ (int) $t->remaining }}" @selected($defaultTrx->id === $t->id)>
                                {{ $t->no_invoice }} — {{ $t->customer->name }} (Sisa: {{ $fmt($t->remaining) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center justify-between gap-2">
                            <label for="modalAmount"
                                class="font-label-sm text-label-sm font-bold uppercase text-on-surface-variant">
                                Nominal Pembayaran (Rp)
                            </label>
                            <span id="modalAmountFormatted" class="font-label-sm text-label-sm font-bold text-primary">
                                Terbaca: Rp 0
                            </span>
                        </div>
                        <input type="number" id="modalAmount" name="amount" min="1" required
                            oninput="syncModalAmountPreview()"
                            class="w-full h-11 px-space-sm rounded-xl bg-surface-container text-on-surface font-headline-sm text-body-md font-bold focus:outline-none focus:ring-2 focus:ring-primary" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold">Tanggal
                            Pembayaran Diterima</label>
                        <input name="paid_at" type="datetime-local" required
                            value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}"
                            class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold">Tujuan
                        Rekening Kas Toko</label>
                    <select id="modalAccountSelect" name="account" onchange="syncPaymentProofLabels()"
                        class="w-full h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="Transfer Rekening BCA" @selected(old('account') === 'Transfer Rekening BCA')>Transfer Rekening BCA
                        </option>
                        <option value="Kas Tunai Kasir Toko" @selected(old('account') === 'Kas Tunai Kasir Toko')>Kas Tunai Kasir Toko</option>
                        <option value="Transfer Rekening Mandiri" @selected(old('account') === 'Transfer Rekening Mandiri')>Transfer Rekening Mandiri
                        </option>
                    </select>
                </div>

                <div class="flex flex-col gap-2">
                    <label id="proofReferenceLabel"
                        class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold">
                        Catatan Bukti Transfer / No. Referensi
                    </label>
                    <input id="proofReferenceInput" name="reference_no" type="text"
                        value="{{ old('reference_no') }}" placeholder="Contoh: Bukti Transfer via M-BCA Ref: 9811202"
                        class="w-full h-10 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm focus:outline-none focus:ring-1 focus:ring-primary" />

                    {{-- Unggah Gambar Bukti Pembayaran / Transfer --}}
                    <div class="mt-1">
                        <input id="proofImageInput" name="proof_image" type="file"
                            accept="image/jpeg,image/png,image/webp" onchange="handleProofImageChange(this)"
                            class="hidden" />

                        <label id="proofImageDropzone" for="proofImageInput"
                            class="flex items-center justify-between gap-space-sm p-space-sm px-space-md rounded-xl border border-dashed border-surface-container-highest bg-surface-container/60 hover:bg-surface-container hover:border-primary/60 cursor-pointer transition-colors">
                            <div class="flex items-center gap-space-sm min-w-0">
                                <div
                                    class="w-9 h-9 rounded-lg bg-primary/15 text-primary flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined text-[20px]">add_photo_alternate</span>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span id="proofDropzoneTitle"
                                        class="font-headline-sm text-body-sm font-semibold text-on-surface truncate">
                                        Lampirkan Gambar Bukti Transfer (Opsional)
                                    </span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant">
                                        Format JPG, PNG, atau WEBP • Maks. 5 MB
                                    </span>
                                </div>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-lg bg-surface-container-high text-primary font-label-sm text-label-sm font-bold whitespace-nowrap">
                                Pilih Gambar
                            </span>
                        </label>

                        {{-- Pratinjau Gambar Sebelum Unggah --}}
                        <div id="proofImagePreviewWrap"
                            class="hidden mt-2 p-2.5 rounded-xl bg-surface-container border border-surface-container-highest flex items-center justify-between gap-space-sm">
                            <div class="flex items-center gap-space-sm min-w-0">
                                <img id="proofImagePreview" src="" alt="Pratinjau Bukti Pembayaran"
                                    onclick="openProofLightbox(this.src, document.getElementById('proofImageFileName').textContent)"
                                    class="w-14 h-14 rounded-lg object-cover border border-surface-container-highest cursor-zoom-in flex-shrink-0" />
                                <div class="flex flex-col min-w-0">
                                    <span id="proofImageFileName"
                                        class="font-headline-sm text-body-sm font-semibold text-on-surface truncate"></span>
                                    <span id="proofImageFileSize"
                                        class="font-label-sm text-label-sm text-primary"></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <label for="proofImageInput"
                                    class="h-8 px-2.5 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface font-label-sm text-label-sm font-semibold flex items-center cursor-pointer">
                                    Ganti
                                </label>
                                <button type="button" onclick="clearProofImage()"
                                    class="h-8 w-8 rounded-lg bg-error/15 hover:bg-error text-error hover:text-on-error flex items-center justify-center transition-colors"
                                    title="Hapus lampiran gambar">
                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-space-sm rounded-xl bg-surface-container flex items-center gap-space-sm">
                    <span class="material-symbols-outlined text-primary text-[20px]">task_alt</span>
                    <span class="font-body-sm text-body-sm text-on-surface-variant">Status tagihan otomatis berganti ke
                        <strong class="text-primary">Lunas Penuh</strong> saat sisa tagihan mencapai Rp 0.</span>
                </div>

                <div
                    class="pt-space-sm flex items-center justify-end gap-space-sm border-t border-surface-container-highest">
                    <button type="button" onclick="closePaymentModal()"
                        class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm">Batal</button>
                    <button type="button" onclick="confirmModalPayment()"
                        class="h-10 px-space-lg rounded-xl bg-primary-container hover:bg-primary text-on-primary-container font-headline-sm text-body-sm font-bold flex items-center gap-space-xs shadow-md">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        <span>Simpan & Posting Kas Masuk</span>
                    </button>
                </div>
            </form>
        @else
            <div class="p-space-xl text-center text-on-surface-variant flex flex-col items-center gap-2">
                <span class="material-symbols-outlined text-[48px] text-primary/40">task_alt</span>
                <p class="font-body-md">Seluruh tagihan tempo sudah lunas. Tidak ada piutang aktif.</p>
                <button type="button" onclick="closePaymentModal()"
                    class="mt-2 h-9 px-space-md rounded-xl bg-surface-container-high text-on-surface font-headline-sm text-body-sm">Tutup</button>
            </div>
        @endif
    </div>
</div>

{{-- ============ 2. MODAL RIWAYAT CICILAN & NOTA ============ --}}
<div id="historyModal"
    class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-space-md">
    <div
        class="w-full max-w-2xl rounded-2xl bg-surface-container-low shadow-[0_20px_50px_rgba(0,0,0,0.8)] overflow-hidden flex flex-col">
        <div class="p-space-md bg-surface-container flex items-center justify-between">
            <div class="flex items-center gap-space-xs">
                <div class="w-8 h-8 rounded-lg bg-tertiary/20 text-tertiary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">history</span>
                </div>
                <div class="flex flex-col">
                    <span class="font-headline-sm text-body-md font-bold text-on-surface">Riwayat Pembayaran Cicilan
                        Faktur</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant"><span id="histInvoice"
                            class="text-primary font-bold"></span> • <span id="histCustomer"></span></span>
                </div>
            </div>
            <button type="button" onclick="closeHistoryModal()"
                class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <div class="p-space-lg flex flex-col gap-space-md">
            <div class="grid grid-cols-3 gap-space-sm">
                <div class="p-space-sm rounded-xl bg-surface-container flex flex-col">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Total Faktur</span>
                    <span id="histSubtotal" class="font-headline-sm text-body-md font-extrabold text-on-surface">Rp
                        0</span>
                </div>
                <div class="p-space-sm rounded-xl bg-surface-container flex flex-col">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Total Terbayar</span>
                    <span id="histPaid" class="font-headline-sm text-body-md font-extrabold text-primary">Rp 0</span>
                </div>
                <div class="p-space-sm rounded-xl bg-surface-container flex flex-col">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Sisa Piutang</span>
                    <span id="histRemaining" class="font-headline-sm text-body-md font-extrabold text-error">Rp
                        0</span>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-surface-container-highest/40">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container text-on-surface-variant font-label-sm text-label-sm uppercase">
                            <th class="py-2 px-3">#</th>
                            <th class="py-2 px-3">Waktu Bayar</th>
                            <th class="py-2 px-3">Nominal</th>
                            <th class="py-2 px-3">Akun & Referensi</th>
                            <th class="py-2 px-3 text-center">Bukti</th>
                            <th class="py-2 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="histPaymentsBody" class="divide-y divide-surface-container-highest/20"></tbody>
                </table>
            </div>

            <div class="flex items-center justify-between pt-space-xs border-t border-surface-container-highest">
                <a id="histReceiptLink" href="#" target="_blank"
                    class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px] text-primary">print</span>
                    <span>Cetak Nota Penjualan</span>
                </a>
                <button type="button" onclick="closeHistoryModal()"
                    class="h-10 px-space-lg rounded-xl bg-primary-container text-on-primary-container font-headline-sm text-body-sm font-bold">
                    Selesai
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============ 3. MODAL PENGINGAT WA PRIORITAS (OVERDUE & DUE SOON) ============ --}}
<div id="waReminderModal"
    class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-space-md">
    <div
        class="w-full max-w-xl rounded-2xl bg-surface-container-low shadow-[0_20px_50px_rgba(0,0,0,0.8)] overflow-hidden flex flex-col">
        <div class="p-space-md bg-surface-container flex items-center justify-between">
            <div class="flex items-center gap-space-xs">
                <div class="w-8 h-8 rounded-lg bg-primary/20 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">chat</span>
                </div>
                <div class="flex flex-col">
                    <span class="font-headline-sm text-body-md font-bold text-on-surface">Antrean Pengingat WA
                        Prioritas</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Faktur Overdue & Jatuh Tempo &le;
                        3 Hari</span>
                </div>
            </div>
            <button type="button" onclick="closeWaReminderModal()"
                class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <div class="p-space-lg max-h-[65vh] overflow-y-auto flex flex-col gap-space-sm">
            @forelse($priorityWaTrx as $t)
                @php
                    $isOv = $t->aging_status === 'overdue';
                    $waText =
                        'Halo ' .
                        ($t->customer->pic_name ?? $t->customer->name) .
                        ', mengingatkan tagihan ' .
                        $t->no_invoice .
                        ' sebesar Rp ' .
                        number_format($t->remaining, 0, ',', '.') .
                        ' jatuh tempo ' .
                        ($t->due_date?->format('d M Y') ?? '-') .
                        '. Terima kasih.';
                @endphp
                <div class="p-space-sm rounded-xl bg-surface-container flex items-center justify-between gap-space-sm">
                    <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-2">
                            <span
                                class="font-headline-sm text-body-sm font-bold text-on-surface truncate">{{ $t->customer->name }}</span>
                            <span
                                class="px-1.5 py-0.2 rounded font-label-sm text-[10px] font-bold {{ $isOv ? 'bg-error text-on-error' : 'bg-tertiary text-on-tertiary' }}">
                                {{ $isOv ? 'OVERDUE' : 'H-' . max(0, $t->days_to_due) }}
                            </span>
                        </div>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $t->no_invoice }} • Jatuh
                            tempo: {{ $t->due_date?->format('d M Y') ?? '-' }}</span>
                        <span class="font-label-md text-label-md text-error font-bold">Sisa:
                            {{ $fmt($t->remaining) }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <button type="button" data-wa-text="{{ $waText }}" onclick="copyWaText(this)"
                            class="h-9 px-2.5 rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface-variant font-label-sm text-label-sm font-semibold flex items-center gap-1"
                            title="Salin teks pengingat">
                            <span class="material-symbols-outlined text-[16px]">content_copy</span>
                            <span>Salin</span>
                        </button>
                        @if ($t->customer->whatsapp_number)
                            <a href="https://wa.me/{{ $t->customer->whatsapp_number }}?text={{ urlencode($waText) }}"
                                target="_blank"
                                class="h-9 px-space-md rounded-xl bg-primary-container hover:bg-primary text-on-primary-container font-headline-sm text-body-sm font-bold flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">send</span>
                                <span>Kirim WA</span>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-on-surface-variant flex flex-col items-center gap-2">
                    <span class="material-symbols-outlined text-[40px] text-primary/40">check_circle</span>
                    <p class="font-body-sm">Tidak ada tagihan overdue atau jatuh tempo dalam 3 hari ke depan.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ============ 4. MODAL KONFIRMASI UNIVERSAL ============ --}}
<div id="confirmActionModal"
    class="fixed inset-0 z-[60] bg-black/75 backdrop-blur-sm hidden items-center justify-center p-space-md">
    <div
        class="w-full max-w-md rounded-2xl bg-surface-container-low shadow-[0_24px_60px_rgba(0,0,0,0.85)] overflow-hidden flex flex-col">
        <div class="p-space-md bg-surface-container flex items-center gap-space-xs">
            <div class="w-8 h-8 rounded-lg bg-primary/20 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">help</span>
            </div>
            <span id="confirmTitle" class="font-headline-sm text-body-md font-bold text-on-surface">Konfirmasi
                Tindakan</span>
        </div>
        <div class="p-space-lg flex flex-col gap-space-md">
            <p id="confirmMessage" class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed"></p>
            <div
                class="flex items-center justify-end gap-space-sm pt-space-xs border-t border-surface-container-highest">
                <button type="button" onclick="closeConfirmModal()"
                    class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-sm">Batal</button>
                <button type="button" id="confirmSubmitBtn"
                    class="h-10 px-space-lg rounded-xl bg-primary-container hover:bg-primary text-on-primary-container font-headline-sm text-body-sm font-bold">Ya,
                    Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

{{-- ============ 5. MODAL LIGHTBOX PRATINJAU BUKTI TRANSFER ============ --}}
<div id="proofLightboxModal"
    class="fixed inset-0 z-[70] bg-black/85 backdrop-blur-sm hidden items-center justify-center p-space-md">
    <div
        class="w-full max-w-2xl rounded-2xl bg-surface-container-low shadow-[0_24px_60px_rgba(0,0,0,0.9)] overflow-hidden flex flex-col">
        <div class="p-space-md bg-surface-container flex items-center justify-between gap-space-sm">
            <div class="flex items-center gap-space-xs min-w-0">
                <span class="material-symbols-outlined text-primary text-[20px]">image</span>
                <span id="lightboxCaption"
                    class="font-headline-sm text-body-md font-bold text-on-surface truncate">Bukti Transfer</span>
            </div>
            <div class="flex items-center gap-space-xs flex-shrink-0">
                <a id="lightboxNewTab" href="#" target="_blank"
                    class="h-8 px-3 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface font-label-sm text-label-sm font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                    <span>Buka Penuh</span>
                </a>
                <button type="button" onclick="closeProofLightbox()"
                    class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright text-on-surface-variant flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
        </div>
        <div class="p-space-md bg-black/40 flex items-center justify-center min-h-[240px] max-h-[75vh] overflow-auto">
            <img id="lightboxImage" src="" alt="Bukti Transfer" onerror="handleLightboxImageError()"
                class="max-h-[68vh] w-auto rounded-xl object-contain shadow-lg" />
            <div id="lightboxErrorFallback"
                class="hidden flex-col items-center justify-center text-center p-space-lg gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-[42px] text-error">broken_image</span>
                <p class="font-headline-sm text-body-md font-bold text-on-surface">Gambar bukti pembayaran tidak dapat
                    dimuat</p>
                <p class="font-body-sm text-body-sm max-w-md">Pastikan file gambar masih tersedia dan perintah <code
                        class="px-1.5 py-0.5 rounded bg-surface-container text-primary">php artisan storage:link</code>
                    sudah dijalankan di server.</p>
            </div>
        </div>
    </div>
</div>
