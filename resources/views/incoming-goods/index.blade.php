@extends('layouts.app')

@section('title', 'Barang Masuk')

@php
    $fmt = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $fmtJt = function ($n) {
        if ($n >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 2, ',', '.') . ' M';
        if ($n >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 2, ',', '.') . ' Jt';
        if ($n >= 1_000)         return 'Rp ' . number_format($n / 1_000, 0, ',', '.') . ' Rb';
        return 'Rp ' . number_format((int) $n, 0, ',', '.');
    };
    $num = fn ($n) => number_format((int) $n, 0, ',', '.');
@endphp

@push('styles')
<style>
    .spotlight-card {
        position: relative;
        overflow: hidden;
    }
    .spotlight-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(520px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(78, 222, 163, 0.09), transparent 45%);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    .spotlight-card:hover::before {
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="flex flex-col w-full">
<div class="relative w-full overflow-hidden pb-space-xl">
    <div class="absolute -top-32 -right-24 w-96 h-96 rounded-full bg-primary/10 blur-[120px] pointer-events-none"></div>
    <div class="absolute top-96 -left-32 w-80 h-80 rounded-full bg-tertiary/10 blur-[100px] pointer-events-none"></div>

    {{-- ==================== HEADER ==================== --}}
    <div class="pt-space-md pb-space-lg flex flex-col gap-space-sm">
        <div class="flex items-center gap-space-xs text-on-surface-variant font-label-md text-label-md">
            <span class="hover:text-on-surface transition-colors cursor-pointer">Sistem Grosir</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="hover:text-on-surface transition-colors cursor-pointer">Operasional Stok</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-primary font-bold">Barang Masuk & Input Modal Box</span>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-md">
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-center gap-space-sm flex-wrap">
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight font-extrabold">
                        Barang Masuk & Input Modal Box
                    </h1>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-primary/15 text-primary text-label-sm font-label-sm uppercase tracking-wider shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary animate-ping"></span>
                        <span>Kunci Acuan HPP / Modal Baru</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-md bg-surface-container-high text-on-surface-variant text-label-sm font-label-sm">
                        BOX-PCS ENGINE
                    </span>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-3xl">
                    Pencatatan faktur kulakan supplier. Harga modal per Box yang dicatat di sini otomatis menjadi basis perhitungan keuntungan di menu
                    <a href="{{ route('outgoing-goods.index') }}" class="text-tertiary font-semibold hover:underline">Barang Keluar</a> & menambah saldo fisik gudang.
                </p>
            </div>

            <div class="flex items-center gap-space-xs shrink-0 flex-wrap">
                <button type="button" onclick="openInputFakturModal()"
                        class="h-10 px-space-md rounded-xl bg-primary text-on-primary font-headline-sm text-body-md font-bold flex items-center gap-space-xs hover:bg-primary-fixed shadow-[0_0_24px_rgba(78,222,163,0.35)] transition-all active:scale-95 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">add_box</span>
                    <span>Input Faktur Masuk</span>
                </button>
                <button type="button" onclick="openScanFakturModal()"
                        class="h-10 px-space-md rounded-xl bg-surface-container-high text-on-surface hover:bg-surface-bright font-body-md text-body-md flex items-center gap-space-xs transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[18px] text-tertiary">document_scanner</span>
                    <span>Scan Faktur / SJ</span>
                </button>
                <a href="{{ route('incoming-goods.export', request()->query()) }}"
                   class="h-10 px-space-md rounded-xl bg-surface-container-high text-on-surface hover:bg-surface-bright font-body-md text-body-md flex items-center gap-space-xs transition-colors">
                    <span class="material-symbols-outlined text-[18px]">file_download</span>
                    <span>Export Rekap PO</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ==================== KPI CARDS ==================== --}}
    @include('incoming-goods.partials.kpi-cards')

    {{-- ==================== TABEL RIWAYAT ==================== --}}
    <div class="flex flex-col rounded-2xl bg-surface-container-low/90 backdrop-blur-xl shadow-xl overflow-hidden">
        <form method="GET" action="{{ route('incoming-goods.index') }}" id="filterForm" class="p-space-md flex flex-col lg:flex-row lg:items-center justify-between gap-space-md">
            <div class="flex flex-1 items-center gap-space-sm flex-wrap">
                <div class="relative flex-1 min-w-[240px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input name="search" id="searchHistoryInput" value="{{ request('search') }}" oninput="debouncedFilterSubmit()"
                           placeholder="Cari No. Faktur / SKU / Nama Barang / Supplier... (Ctrl + K)" type="text"
                           class="w-full h-10 pl-9 pr-9 rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/50 font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary transition-all" />
                    @if(request('search'))
                        <a href="{{ route('incoming-goods.index', ['status' => $status !== 'SEMUA' ? $status : null]) }}"
                           class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface" title="Hapus pencarian">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </a>
                    @endif
                </div>

                <div class="flex items-center p-1 bg-surface-container rounded-xl">
                    <button type="submit" name="status" value="SEMUA"
                            class="px-3 py-1 rounded-lg text-body-sm transition-all cursor-pointer {{ $status === 'SEMUA' ? 'bg-surface-container-high text-on-surface font-semibold' : 'text-on-surface-variant hover:text-on-surface' }}">
                        Semua ({{ $kpi['total_count'] }})
                    </button>
                    <button type="submit" name="status" value="LUNAS"
                            class="px-3 py-1 rounded-lg text-body-sm transition-all cursor-pointer {{ $status === 'LUNAS' ? 'bg-surface-container-high text-on-surface font-semibold' : 'text-on-surface-variant hover:text-on-surface' }}">
                        Lunas ({{ $kpi['lunas_all'] }})
                    </button>
                    <button type="submit" name="status" value="TEMPO"
                            class="px-3 py-1 rounded-lg text-body-sm transition-all flex items-center gap-1 cursor-pointer {{ $status === 'TEMPO' ? 'bg-surface-container-high text-on-surface font-semibold' : 'text-on-surface-variant hover:text-error' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                        <span>Tempo ({{ $kpi['tempo_all'] }})</span>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-space-xs shrink-0">
                <div class="flex items-center gap-1.5 px-space-sm h-10 rounded-xl bg-surface-container text-on-surface-variant font-body-sm text-body-sm">
                    <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                    <span>{{ now('Asia/Jakarta')->translatedFormat('M Y') }}</span>
                </div>
                <a href="{{ route('incoming-goods.index') }}"
                   class="w-10 h-10 rounded-xl bg-surface-container hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:text-primary transition-colors"
                   title="Reset Filter">
                    <span class="material-symbols-outlined text-[18px]">sync</span>
                </a>
            </div>
        </form>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container text-on-surface-variant font-label-md text-label-md uppercase tracking-wider select-none">
                        <th class="py-3 px-space-md font-semibold">No. Faktur & Waktu</th>
                        <th class="py-3 px-space-md font-semibold">Supplier</th>
                        <th class="py-3 px-space-md font-semibold">Nama Barang & SKU</th>
                        <th class="py-3 px-space-md font-semibold text-center">Masuk Box & Pcs</th>
                        <th class="py-3 px-space-md font-semibold text-right">Modal / Box → Pcs</th>
                        <th class="py-3 px-space-md font-semibold text-right">Total Transaksi</th>
                        <th class="py-3 px-space-md font-semibold text-center">Status & Tempo</th>
                        <th class="py-3 px-space-md font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container font-body-sm text-body-sm">
                    @forelse($transactions as $trx)
                        @include('incoming-goods.partials.table-row', ['trx' => $trx])
                    @empty
                        <tr>
                            <td colspan="8" class="py-space-xl text-center text-on-surface-variant">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="material-symbols-outlined text-[40px] text-surface-container-highest">inbox</span>
                                    <span>Belum ada transaksi yang cocok dengan filter.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="p-space-md bg-surface-container flex flex-col sm:flex-row items-center justify-between gap-space-sm font-body-sm text-body-sm text-on-surface-variant">
            <div class="flex items-center gap-2">
                @if($transactions->total() > 0)
                    <span>Menampilkan <strong>{{ $transactions->firstItem() }} - {{ $transactions->lastItem() }}</strong> dari <strong>{{ $transactions->total() }} Faktur Masuk</strong></span>
                    <span class="w-1 h-1 rounded-full bg-surface-container-highest"></span>
                    <span>Halaman {{ $transactions->currentPage() }} dari {{ $transactions->lastPage() }}</span>
                @else
                    <span>Belum ada data</span>
                @endif
            </div>
            <div>{{ $transactions->links() }}</div>
        </div>
    </div>

    {{-- ==================== BANNER EDUKASI (DENGAN SPOTLIGHT) ==================== --}}
    <div id="sopSpotlightBanner" class="spotlight-card mt-space-lg p-space-md rounded-2xl bg-surface-container-low border-l-4 border-l-primary shadow-lg flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md">
        <div class="flex items-start gap-space-sm relative z-10">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0 mt-0.5">
                <span class="material-symbols-outlined text-[24px]">balance</span>
            </div>
            <div class="flex flex-col">
                <span class="font-headline-sm text-body-md text-on-surface font-bold flex items-center gap-2">
                    Integritas HPP & Siklus Transaksi Grosir
                    <span class="px-2 py-0.2 rounded bg-surface-container-high text-[10px] font-label-sm text-tertiary">STANDAR AKUNTANSI PERDAGANGAN</span>
                </span>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                    <strong>Harga Modal Bersifat Historis:</strong> Perubahan harga modal hari ini tidak mengubah keuntungan transaksi penjualan kemarin. Hapus transaksi akan otomatis me-rollback saldo stok gudang dan membatalkan pencatatan terkait.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-space-xs shrink-0 relative z-10">
            <button type="button" onclick="openSopModal()"
                    class="px-space-md py-2 rounded-xl bg-surface-container text-tertiary hover:bg-surface-container-high font-body-sm text-body-sm flex items-center gap-1 transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">menu_book</span>
                <span>Panduan SOP Modal Box</span>
            </button>
        </div>
    </div>
</div>
</div>

{{-- ==================== CUSTOM MODAL KONFIRMASI AKSI ==================== --}}
<div id="modalKonfirmasiAksi" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 bg-surface-container-lowest/80 backdrop-blur-md">
    <div class="w-full max-w-md bg-surface-container-low rounded-2xl border border-outline-variant/30 shadow-2xl overflow-hidden animate-[fadeIn_0.15s_ease-out]">
        <div class="p-space-lg flex flex-col gap-space-md">
            <div class="flex items-start gap-space-sm">
                <div id="confirmIconWrapper" class="w-11 h-11 rounded-xl bg-primary/15 text-primary flex items-center justify-center shrink-0">
                    <span id="confirmIcon" class="material-symbols-outlined text-[24px]">verified</span>
                </div>
                <div class="flex flex-col gap-1">
                    <h3 id="confirmTitle" class="font-headline-sm text-headline-sm text-on-surface font-bold">Konfirmasi Simpan Faktur</h3>
                    <p id="confirmSubtitle" class="font-body-sm text-body-sm text-on-surface-variant">Pastikan rincian harga modal & kuantitas sudah sesuai.</p>
                </div>
            </div>

            <div id="confirmSummaryBox" class="p-space-md rounded-xl bg-surface-container border border-outline-variant/15 flex flex-col gap-2 text-body-sm">
                {{-- Diisi dinamis oleh JS --}}
            </div>

            <div class="flex items-center justify-end gap-space-sm pt-2">
                <button type="button" onclick="closeConfirmModal()"
                        class="h-10 px-space-md rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-headline-sm text-body-sm transition-colors cursor-pointer">
                    Periksa Kembali
                </button>
                <button type="button" id="confirmExecuteBtn"
                        class="h-10 px-space-lg rounded-xl bg-primary-container text-on-primary-container hover:bg-primary font-headline-sm text-body-sm font-bold flex items-center gap-1.5 transition-all shadow-lg cursor-pointer">
                    <span id="confirmBtnIcon" class="material-symbols-outlined text-[18px]">check_circle</span>
                    <span id="confirmBtnText">Ya, Simpan & Kunci HPP</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ==================== MODALS & TOAST ==================== --}}
@include('incoming-goods.partials.form-faktur')
@include('incoming-goods.partials.modals')
@endsection

@push('scripts')
<script>
(function () {
    const fakturForm      = document.getElementById('fakturForm');
    const productSelect   = document.getElementById('productSelect');
    const supplierSelect  = document.getElementById('supplierSelect');
    const inputBoxQty     = document.getElementById('inputBoxQty');
    const inputHargaBox   = document.getElementById('inputHargaBox');
    const hppLaluLabel    = document.getElementById('hppLaluLabel');
    const outputTotalPcs  = document.getElementById('outputTotalPcs');
    const outputModalPcs  = document.getElementById('outputModalPcs');
    const outputTotal     = document.getElementById('outputTotalBelanja');
    const lockInfo        = document.getElementById('lockInfo');
    const paymentStatus   = document.getElementById('paymentStatusInput');
    const tempoWrapper    = document.getElementById('tempoDetailsWrapper');
    const tempoBadgeLabel = document.getElementById('tempoBadgeLabel');
    const btnLunas        = document.getElementById('btnStatusLunas');
    const btnTempo        = document.getElementById('btnStatusTempo');
    const noFakturInput   = document.getElementById('noFakturInput');
    const searchInput     = document.getElementById('searchHistoryInput');

    const fmtRp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        const border = type === 'error' ? 'border-error/40' : 'border-primary/40';
        const icon   = type === 'error' ? 'error' : 'check_circle';
        const color  = type === 'error' ? 'text-error' : 'text-primary';

        toast.className = `pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-xl border ${border} bg-surface-container-high text-on-surface shadow-2xl text-body-sm`;
        toast.innerHTML = `<span class="material-symbols-outlined ${color} text-[20px]">${icon}</span><span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    }

    @if(session('success'))
        showToast(@json(session('success')), 'success');
    @endif

    /* ---------- Mouse-Tracking Spotlight pada Banner ---------- */
    const spotlightBanner = document.getElementById('sopSpotlightBanner');
    spotlightBanner?.addEventListener('mousemove', e => {
        const rect = spotlightBanner.getBoundingClientRect();
        spotlightBanner.style.setProperty('--mouse-x', `${e.clientX - rect.left}px`);
        spotlightBanner.style.setProperty('--mouse-y', `${e.clientY - rect.top}px`);
    });

    /* ---------- Kalkulator Box-to-Pcs ---------- */
    function onProductChange(forceUpdatePrice = false) {
        const opt = productSelect?.options[productSelect.selectedIndex];
        if (!opt) return;
        const lastPrice = parseInt(opt.dataset.lastPrice || 0, 10);
        hppLaluLabel.textContent = 'Modal Lalu: ' + (lastPrice > 0 ? fmtRp(lastPrice) : '—');

        if (forceUpdatePrice || !inputHargaBox.value || inputHargaBox.value === '0') {
            inputHargaBox.value = lastPrice > 0 ? lastPrice : '';
        }
        calculateRealtime();
    }

    function calculateRealtime() {
        const opt      = productSelect?.options[productSelect.selectedIndex];
        const ratio    = parseInt(opt?.dataset.ratio || 1, 10);
        const boxQty   = parseFloat(inputBoxQty.value) || 0;
        const hargaBox = parseFloat(inputHargaBox.value) || 0;

        const totalPcs = Math.round(boxQty * ratio);
        const modalPcs = totalPcs > 0 ? Math.round((boxQty * hargaBox) / totalPcs) : 0;
        const subtotal = Math.round(boxQty * hargaBox);

        outputTotalPcs.textContent = totalPcs.toLocaleString('id-ID') + ' Pcs';
        outputModalPcs.textContent = fmtRp(modalPcs);
        outputTotal.textContent    = fmtRp(subtotal);
        lockInfo.textContent       = `Harga ${fmtRp(hargaBox)} / Box (${fmtRp(modalPcs)} / Pcs) akan mengunci acuan margin laba Barang Keluar.`;
    }

    function setPaymentStatus(status) {
        paymentStatus.value = status;
        const inputDue = document.getElementById('inputJatuhTempo');

        if (status === 'lunas') {
            btnLunas.className = 'flex-1 h-full rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all bg-primary-container text-on-primary-container font-semibold shadow-sm cursor-pointer';
            btnTempo.className = 'flex-1 h-full rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all text-on-surface-variant hover:text-on-surface cursor-pointer';
            tempoWrapper.classList.add('hidden');
            tempoBadgeLabel.textContent = 'Lunas Kas Toko';
            tempoBadgeLabel.className = 'text-label-sm font-label-sm text-primary';
            if (inputDue) inputDue.required = false;
        } else {
            btnTempo.className = 'flex-1 h-full rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all bg-error/25 text-error font-semibold shadow-sm cursor-pointer';
            btnLunas.className = 'flex-1 h-full rounded-lg font-headline-sm text-body-sm flex items-center justify-center gap-1 transition-all text-on-surface-variant hover:text-on-surface cursor-pointer';
            tempoWrapper.classList.remove('hidden');
            tempoBadgeLabel.textContent = 'Kredit / Tempo Supplier';
            tempoBadgeLabel.className = 'text-label-sm font-label-sm text-error';
            if (inputDue) inputDue.required = true;
        }
    }

    function resetInputForm() {
        const now = new Date();
        const ym  = now.getFullYear() + '/' + String(now.getMonth() + 1).padStart(2, '0');
        inputBoxQty.value = 10;
        inputHargaBox.value = '';
        if (noFakturInput) noFakturInput.value = `DO-${ym}/${Math.floor(1000 + Math.random() * 9000)}`;
        setPaymentStatus('lunas');
        onProductChange(true);
    }

    /* ---------- Custom Confirmation Modal Engine ---------- */
    const modalConfirm     = document.getElementById('modalKonfirmasiAksi');
    const confirmIconWrap  = document.getElementById('confirmIconWrapper');
    const confirmIcon      = document.getElementById('confirmIcon');
    const confirmTitle     = document.getElementById('confirmTitle');
    const confirmSubtitle  = document.getElementById('confirmSubtitle');
    const confirmSummary   = document.getElementById('confirmSummaryBox');
    const confirmExecBtn   = document.getElementById('confirmExecuteBtn');
    const confirmBtnIcon   = document.getElementById('confirmBtnIcon');
    const confirmBtnText   = document.getElementById('confirmBtnText');
    let pendingConfirmAction = null;

    function openConfirmModal({ variant = 'primary', icon, title, subtitle, rows = [], btnIcon, btnText, onConfirm }) {
        const isDanger = variant === 'danger';
        confirmIconWrap.className = isDanger
            ? 'w-11 h-11 rounded-xl bg-error/15 text-error flex items-center justify-center shrink-0'
            : 'w-11 h-11 rounded-xl bg-primary/15 text-primary flex items-center justify-center shrink-0';

        confirmExecBtn.className = isDanger
            ? 'h-10 px-space-lg rounded-xl bg-error text-on-error hover:opacity-90 font-headline-sm text-body-sm font-bold flex items-center gap-1.5 transition-all shadow-lg cursor-pointer'
            : 'h-10 px-space-lg rounded-xl bg-primary-container text-on-primary-container hover:bg-primary font-headline-sm text-body-sm font-bold flex items-center gap-1.5 transition-all shadow-lg cursor-pointer';

        confirmIcon.textContent     = icon;
        confirmTitle.textContent    = title;
        confirmSubtitle.textContent = subtitle;
        confirmBtnIcon.textContent  = btnIcon;
        confirmBtnText.textContent  = btnText;

        confirmSummary.innerHTML = rows.map(r => `
            <div class="flex items-center justify-between gap-2 ${r.divider ? 'pt-2 border-t border-surface-container-highest' : ''}">
                <span class="text-on-surface-variant">${r.label}</span>
                <span class="${r.highlight ? (isDanger ? 'text-error font-bold' : 'text-primary font-extrabold') : 'text-on-surface font-semibold'} text-right">${r.value}</span>
            </div>
        `).join('');

        pendingConfirmAction = onConfirm;
        modalConfirm.classList.remove('hidden');
    }

    window.closeConfirmModal = function () {
        modalConfirm.classList.add('hidden');
        pendingConfirmAction = null;
    };

    confirmExecBtn?.addEventListener('click', () => {
        if (typeof pendingConfirmAction === 'function') {
            pendingConfirmAction();
        }
        closeConfirmModal();
    });

    modalConfirm?.addEventListener('click', e => { if (e.target === modalConfirm) closeConfirmModal(); });

    /* 1. Konfirmasi Simpan Faktur */
    window.confirmSubmitFaktur = function () {
        if (!fakturForm.checkValidity()) {
            fakturForm.reportValidity();
            return;
        }

        const prodOpt   = productSelect.options[productSelect.selectedIndex];
        const suppOpt   = supplierSelect.options[supplierSelect.selectedIndex];
        const ratio     = parseInt(prodOpt?.dataset.ratio || 1, 10);
        const boxQty    = parseInt(inputBoxQty.value || 0, 10);
        const hargaBox  = parseInt(inputHargaBox.value || 0, 10);
        const totalPcs  = boxQty * ratio;
        const modalPcs  = totalPcs > 0 ? Math.round((boxQty * hargaBox) / totalPcs) : 0;
        const subtotal  = boxQty * hargaBox;
        const isTempo   = paymentStatus.value === 'tempo';

        openConfirmModal({
            variant: 'primary',
            icon: 'verified',
            title: 'Konfirmasi Simpan Faktur & Kunci HPP',
            subtitle: 'Saldo stok akan bertambah dan harga modal ini terkunci permanen untuk perhitungan laba.',
            rows: [
                { label: 'No. Faktur', value: (noFakturInput.value || '').toUpperCase() },
                { label: 'Supplier', value: suppOpt?.text.trim() || '-' },
                { label: 'Barang', value: prodOpt?.dataset.name || '-' },
                { label: 'Penambahan Stok', value: `${boxQty.toLocaleString('id-ID')} Box → +${totalPcs.toLocaleString('id-ID')} Pcs`, highlight: true },
                { label: 'Kunci Modal', value: `${fmtRp(hargaBox)} /Box (${fmtRp(modalPcs)} /Pcs)` },
                { label: 'Status Bayar', value: isTempo ? 'TEMPO / HUTANG SUPPLIER' : 'LUNAS KAS TOKO' },
                { label: 'Total Transaksi', value: fmtRp(subtotal), highlight: true, divider: true },
            ],
            btnIcon: 'lock',
            btnText: 'Ya, Simpan & Kunci HPP',
            onConfirm: () => fakturForm.submit(),
        });
    };

    /* 2. Konfirmasi Pelunasan Tempo */
    window.confirmPayAction = function (formId, noFaktur, supplierName, totalFormatted) {
        openConfirmModal({
            variant: 'primary',
            icon: 'price_check',
            title: 'Konfirmasi Pelunasan Utang Supplier',
            subtitle: 'Status faktur akan diperbarui menjadi LUNAS dan dihapus dari daftar tagihan jatuh tempo.',
            rows: [
                { label: 'No. Faktur', value: noFaktur },
                { label: 'Supplier', value: supplierName },
                { label: 'Nominal Pelunasan', value: totalFormatted, highlight: true, divider: true },
            ],
            btnIcon: 'check_circle',
            btnText: 'Ya, Tandai Lunas',
            onConfirm: () => document.getElementById(formId)?.submit(),
        });
    };

    /* 3. Konfirmasi Rollback Transaksi */
    window.confirmRollbackAction = function (formId, noFaktur, productName, totalPcsFormatted) {
        openConfirmModal({
            variant: 'danger',
            icon: 'delete_forever',
            title: 'Konfirmasi Rollback Faktur Masuk',
            subtitle: 'Tindakan ini akan membatalkan faktur dan menarik kembali stok fisik dari gudang.',
            rows: [
                { label: 'No. Faktur', value: noFaktur },
                { label: 'Produk Terdampak', value: productName },
                { label: 'Pengurangan Stok', value: `-${totalPcsFormatted} Pcs`, highlight: true, divider: true },
            ],
            btnIcon: 'undo',
            btnText: 'Ya, Rollback Faktur',
            onConfirm: () => document.getElementById(formId)?.submit(),
        });
    };

    /* ---------- Debounced Search + Keep Cursor Focus ---------- */
    let filterTimer;
    function debouncedFilterSubmit() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => {
            sessionStorage.setItem('incomingGoodsSearchFocus', '1');
            document.getElementById('filterForm').submit();
        }, 450);
    }

    /* ---------- Modal Input Faktur ---------- */
    const modalInputFaktur = document.getElementById('modalInputFaktur');
    window.openInputFakturModal = function (focusTarget = noFakturInput) {
        modalInputFaktur?.classList.remove('hidden');
        setTimeout(() => focusTarget?.focus(), 60);
    };
    window.closeInputFakturModal = () => modalInputFaktur?.classList.add('hidden');
    modalInputFaktur?.addEventListener('click', e => { if (e.target === modalInputFaktur) closeInputFakturModal(); });

    /* ---------- Modal Detail Faktur ---------- */
    const modalDetail = document.getElementById('modalDetailFaktur');
    window.openDetailFakturModal = function (d) {
        document.getElementById('detNoFaktur').textContent     = d.no_faktur;
        document.getElementById('detWaktu').textContent        = `${d.received_at} • Pukul ${d.created_time}`;
        document.getElementById('detSupplier').textContent     = d.supplier_name;
        document.getElementById('detSupplierType').textContent = d.supplier_type;
        document.getElementById('detStatus').textContent       = d.payment_status === 'lunas' ? 'LUNAS' : 'TEMPO / KREDIT';
        document.getElementById('detStatus').className         = `font-bold uppercase ${d.payment_status === 'lunas' ? 'text-primary' : 'text-error'}`;
        document.getElementById('detDueDate').textContent      = d.due_date;
        document.getElementById('detSku').textContent          = d.product_sku;
        document.getElementById('detProduk').textContent       = d.product_name;
        document.getElementById('detRatio').textContent        = `Aturan Konversi: 1 Box = ${d.ratio} Pcs`;
        document.getElementById('detQty').textContent          = `${Number(d.box_qty).toLocaleString('id-ID')} Box (${Number(d.total_pcs).toLocaleString('id-ID')} Pcs)`;
        document.getElementById('detCurrentStock').textContent = `${Number(d.current_stock).toLocaleString('id-ID')} Pcs`;
        document.getElementById('detPriceBox').textContent     = fmtRp(d.price_per_box) + ' / Box';
        document.getElementById('detPricePcs').textContent     = fmtRp(d.price_per_pcs) + ' / Pcs';
        document.getElementById('detSubtotal').textContent     = fmtRp(d.subtotal);
        modalDetail.classList.remove('hidden');
    };
    window.closeDetailFakturModal = () => modalDetail.classList.add('hidden');
    modalDetail?.addEventListener('click', e => { if (e.target === modalDetail) closeDetailFakturModal(); });

    /* ---------- Modal Scan Faktur / Barcode ---------- */
    const modalScan = document.getElementById('modalScanFaktur');
    const scanInput = document.getElementById('scanFakturInput');
    window.openScanFakturModal  = () => { modalScan.classList.remove('hidden'); setTimeout(() => scanInput?.focus(), 60); };
    window.closeScanFakturModal = () => modalScan.classList.add('hidden');
    modalScan?.addEventListener('click', e => { if (e.target === modalScan) closeScanFakturModal(); });

    window.processScanInput = function () {
        const val = (scanInput.value || '').trim().toUpperCase();
        if (!val) return;

        const options = Array.from(productSelect.options);
        const matched = options.find(o =>
            (o.dataset.sku && o.dataset.sku.toUpperCase() === val) ||
            (o.dataset.barcode && o.dataset.barcode.toUpperCase() === val)
        );

        closeScanFakturModal();
        scanInput.value = '';

        if (matched) {
            productSelect.value = matched.value;
            onProductChange(true);
            openInputFakturModal(inputBoxQty);
            showToast(`Produk ${matched.dataset.name} dipilih otomatis.`);
        } else {
            searchInput.value = val;
            sessionStorage.setItem('incomingGoodsSearchFocus', '1');
            document.getElementById('filterForm').submit();
        }
    };
    scanInput?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); processScanInput(); } });

    /* ---------- Modal SOP ---------- */
    const modalSop = document.getElementById('modalSopModalBox');
    window.openSopModal  = () => modalSop.classList.remove('hidden');
    window.closeSopModal = () => modalSop.classList.add('hidden');
    modalSop?.addEventListener('click', e => { if (e.target === modalSop) closeSopModal(); });

    /* ---------- Keyboard Shortcuts (ESC & Ctrl+K) ---------- */
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            searchInput?.focus();
            searchInput?.select();
        }
        if (e.key === 'Escape') {
            if (!modalConfirm.classList.contains('hidden')) {
                closeConfirmModal();
                return;
            }
            closeInputFakturModal();
            closeDetailFakturModal();
            closeScanFakturModal();
            closeSopModal();
        }
    });

    window.onProductChange       = onProductChange;
    window.calculateRealtime     = calculateRealtime;
    window.setPaymentStatus      = setPaymentStatus;
    window.resetInputForm        = resetInputForm;
    window.debouncedFilterSubmit = debouncedFilterSubmit;

    document.addEventListener('DOMContentLoaded', () => {
        if (paymentStatus.value === 'tempo') setPaymentStatus('tempo');
        onProductChange(false);

        if (sessionStorage.getItem('incomingGoodsSearchFocus') === '1' && searchInput) {
            sessionStorage.removeItem('incomingGoodsSearchFocus');
            searchInput.focus();
            const len = searchInput.value.length;
            searchInput.setSelectionRange(len, len);
        }
    });
})();
</script>
@endpush