@extends('layouts.app')

@section('title', 'Stok Barang')

@php
    $fmt = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $fmtJt = function ($n) {
        if ($n >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 2, ',', '.') . ' M';
        if ($n >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 2, ',', '.') . ' Jt';
        if ($n >= 1_000)         return 'Rp ' . number_format($n / 1_000, 0, ',', '.') . ' Rb';
        return 'Rp ' . number_format((int) $n, 0, ',', '.');
    };
    $num = fn ($n) => number_format((int) $n, 0, ',', '.');
    $pct = fn ($a, $b) => $b > 0 ? round(($a / $b) * 100, 1) : 0;

    $totalSku   = $kpi['total_sku'];
    $amanPct    = $pct($kpi['aman'], $totalSku);
    $menipisPct = $pct($kpi['menipis'], $totalSku);
    $habisPct   = $pct($kpi['habis'], $totalSku);
    $availPct   = $pct(max(0, $totalSku - $kpi['habis']), $totalSku);
    $netDelta   = $kpi['today_in_box'] - $kpi['today_out_box'];
@endphp

@push('styles')
<style>
    .spotlight-card { position: relative; overflow: hidden; }
    .spotlight-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(520px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(78, 222, 163, 0.09), transparent 45%);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    .spotlight-card:hover::before { opacity: 1; }
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
            <span class="text-primary font-bold">Monitoring Stok Barang Realtime</span>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-md">
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-center gap-space-sm flex-wrap">
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight font-extrabold">
                        Monitoring Stok Barang Realtime
                    </h1>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-primary/15 text-primary text-label-sm font-label-sm uppercase tracking-wider shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary animate-ping"></span>
                        <span>Sinkron Gudang Otomatis</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-md bg-surface-container-high text-on-surface-variant text-label-sm font-label-sm">
                        BOX-PCS ENGINE
                    </span>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-3xl">
                    Pantau ketersediaan fisik gudang dalam satuan Box & Pcs secara presisi. Terhubung otomatis dengan
                    <a href="{{ route('incoming-goods.index') }}" class="text-primary font-semibold hover:underline">Barang Masuk</a> &
                    <a href="{{ route('outgoing-goods.index') }}" class="text-tertiary font-semibold hover:underline">Barang Keluar</a>.
                </p>
            </div>

            <div class="flex items-center gap-space-xs shrink-0 flex-wrap">
                <button type="button" data-action="open-alert"
                        class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-body-md text-body-md flex items-center gap-space-xs transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-tertiary text-[18px]">tune</span>
                    <span>Atur Batas Alert</span>
                </button>
                <button type="button" data-action="open-opname"
                        class="h-10 px-space-md rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-body-md text-body-md flex items-center gap-space-xs transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-primary text-[18px]">inventory</span>
                    <span>Opname Stok Fisik</span>
                </button>
                <a href="{{ route('stock.export', request()->query()) }}"
                   class="h-10 px-space-md rounded-xl bg-primary text-on-primary font-headline-sm text-body-md font-bold flex items-center gap-space-xs hover:bg-primary-fixed shadow-[0_0_24px_rgba(78,222,163,0.35)] transition-all active:scale-95">
                    <span class="material-symbols-outlined text-[18px]">file_download</span>
                    <span>Export Laporan (CSV)</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ==================== KPI CARDS ==================== --}}
    @include('stock.partials.kpi-cards')

    {{-- ==================== TABEL MONITORING STOK ==================== --}}
    <div class="flex flex-col rounded-2xl bg-surface-container-low/90 backdrop-blur-xl shadow-xl overflow-hidden">
        <form method="GET" action="{{ route('stock.index') }}" id="stockFilterForm" class="p-space-md flex flex-col gap-space-md">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-md">
                <div class="flex flex-1 items-center gap-space-sm flex-wrap">
                    <div class="relative flex-1 min-w-[240px]">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                        <input name="search" id="stockSearchInput" value="{{ request('search') }}"
                               placeholder="Cari Nama Barang / SKU / Barcode... (Ctrl + K)" type="text"
                               class="w-full h-10 pl-9 pr-9 rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/50 font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary transition-all" />
                        @if(request('search'))
                            <a href="{{ route('stock.index', ['status' => $status !== 'SEMUA' ? $status : null, 'category' => $cat !== 'all' ? $cat : null, 'sort' => $sort]) }}"
                               class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface" title="Hapus pencarian">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center p-1 bg-surface-container rounded-xl flex-wrap">
                        <button type="submit" name="status" value="SEMUA"
                                class="px-3 py-1 rounded-lg text-body-sm transition-all cursor-pointer {{ $status === 'SEMUA' ? 'bg-surface-container-high text-on-surface font-semibold' : 'text-on-surface-variant hover:text-on-surface' }}">
                            Semua ({{ $kpi['total_sku'] }})
                        </button>
                        <button type="submit" name="status" value="AMAN"
                                class="px-3 py-1 rounded-lg text-body-sm transition-all flex items-center gap-1 cursor-pointer {{ $status === 'AMAN' ? 'bg-surface-container-high text-primary font-semibold' : 'text-on-surface-variant hover:text-primary' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                            <span>Aman ({{ $kpi['aman'] }})</span>
                        </button>
                        <button type="submit" name="status" value="MENIPIS"
                                class="px-3 py-1 rounded-lg text-body-sm transition-all flex items-center gap-1 cursor-pointer {{ $status === 'MENIPIS' ? 'bg-surface-container-high text-tertiary font-semibold' : 'text-on-surface-variant hover:text-tertiary' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-tertiary"></span>
                            <span>Menipis ({{ $kpi['menipis'] }})</span>
                        </button>
                        <button type="submit" name="status" value="HABIS"
                                class="px-3 py-1 rounded-lg text-body-sm transition-all flex items-center gap-1 cursor-pointer {{ $status === 'HABIS' ? 'bg-surface-container-high text-error font-semibold' : 'text-on-surface-variant hover:text-error' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                            <span>Habis ({{ $kpi['habis'] }})</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-space-xs flex-wrap shrink-0">
                    <select name="category" onchange="this.form.submit()"
                            class="h-10 px-3 rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer">
                        <option value="all" @selected($cat === 'all')>Semua Kategori</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->slug }}" @selected($cat === $c->slug)>{{ $c->name }} ({{ $c->products_count }})</option>
                        @endforeach
                    </select>

                    <select name="sort" onchange="this.form.submit()"
                            class="h-10 px-3 rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer">
                        <option value="stok_asc" @selected($sort === 'stok_asc')>Urut: Stok Terkecil</option>
                        <option value="stok_desc" @selected($sort === 'stok_desc')>Urut: Stok Terbanyak</option>
                        <option value="nilai_desc" @selected($sort === 'nilai_desc')>Urut: Nilai Aset Tertinggi</option>
                        <option value="mutasi" @selected($sort === 'mutasi')>Urut: Mutasi Terakhir</option>
                        <option value="nama_asc" @selected($sort === 'nama_asc')>Urut: Nama A–Z</option>
                    </select>

                    <div class="flex items-center p-1 bg-surface-container rounded-xl" id="unitToggleGroup">
                        <button type="button" data-action="set-unit" data-unit="dual"
                                class="unit-btn px-2.5 py-1 rounded-lg text-label-sm font-semibold bg-primary/20 text-primary cursor-pointer">
                            Box + Pcs
                        </button>
                        <button type="button" data-action="set-unit" data-unit="box"
                                class="unit-btn px-2.5 py-1 rounded-lg text-label-sm text-on-surface-variant hover:text-on-surface cursor-pointer">
                            Hanya Box
                        </button>
                        <button type="button" data-action="set-unit" data-unit="pcs"
                                class="unit-btn px-2.5 py-1 rounded-lg text-label-sm text-on-surface-variant hover:text-on-surface cursor-pointer">
                            Hanya Pcs
                        </button>
                    </div>

                    <a href="{{ route('stock.index') }}"
                       class="w-10 h-10 rounded-xl bg-surface-container hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:text-primary transition-colors"
                       title="Reset Filter">
                        <span class="material-symbols-outlined text-[18px]">sync</span>
                    </a>
                </div>
            </div>
        </form>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container text-on-surface-variant font-label-md text-label-md uppercase tracking-wider select-none">
                        <th class="py-3 px-space-md font-semibold">SKU & Nama Barang</th>
                        <th class="py-3 px-space-md font-semibold text-center">Konversi</th>
                        <th class="py-3 px-space-md font-semibold text-right">Sisa Stok Realtime</th>
                        <th class="py-3 px-space-md font-semibold text-center">Batas Alert</th>
                        <th class="py-3 px-space-md font-semibold text-right">HPP Terakhir / Box</th>
                        <th class="py-3 px-space-md font-semibold text-right">Estimasi Nilai Aset</th>
                        <th class="py-3 px-space-md font-semibold text-center">Status</th>
                        <th class="py-3 px-space-md font-semibold text-center">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container font-body-sm text-body-sm">
                    @forelse($products as $p)
                        @include('stock.partials.table-row', ['p' => $p])
                    @empty
                        <tr>
                            <td colspan="8" class="py-space-xl text-center text-on-surface-variant">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="material-symbols-outlined text-[40px] text-surface-container-highest">inventory_2</span>
                                    <span>Tidak ada data stok barang yang cocok dengan filter.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-space-md bg-surface-container flex flex-col sm:flex-row items-center justify-between gap-space-sm font-body-sm text-body-sm text-on-surface-variant">
            <div class="flex items-center gap-2">
                @if($products->total() > 0)
                    <span>Menampilkan <strong>{{ $products->firstItem() }} - {{ $products->lastItem() }}</strong> dari <strong>{{ $products->total() }} Produk Aktif</strong></span>
                    <span class="w-1 h-1 rounded-full bg-surface-container-highest"></span>
                    <span>Halaman {{ $products->currentPage() }} dari {{ $products->lastPage() }}</span>
                @else
                    <span>Belum ada data produk</span>
                @endif
            </div>
            <div>{{ $products->links() }}</div>
        </div>
    </div>

    {{-- ==================== BANNER EDUKASI (SPOTLIGHT) ==================== --}}
    <div id="stockSpotlightBanner" class="spotlight-card mt-space-lg p-space-md rounded-2xl bg-surface-container-low border-l-4 border-l-primary shadow-lg flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md">
        <div class="flex items-start gap-space-sm relative z-10">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0 mt-0.5">
                <span class="material-symbols-outlined text-[24px]">verified_user</span>
            </div>
            <div class="flex flex-col">
                <span class="font-headline-sm text-body-md text-on-surface font-bold flex items-center gap-2">
                    Sinkronisasi Multi-Satuan (Box ↔ Pcs) & Valuasi Aset
                    <span class="px-2 py-0.2 rounded bg-surface-container-high text-[10px] font-label-sm text-tertiary">REALTIME LEDGER</span>
                </span>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                    Saldo stok tersimpan dalam satuan terkecil (<strong>Pcs</strong>) untuk mencegah pembulatan saat penjualan eceran maupun grosir Box. Gunakan <strong>Opname Stok Fisik</strong> hanya jika ditemukan selisih fisik nyata saat audit gudang.
                </p>
            </div>
        </div>
    </div>
</div>
</div>

{{-- ==================== PARTIAL MODALS ==================== --}}
@include('stock.partials.modals')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fmtRp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        if (!container) return;
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
    @if($errors->any())
        showToast(@json($errors->first()), 'error');
    @endif

    function openModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('hidden');
        el.classList.remove('flex');
    }

    /* ---------- Spotlight Banner ---------- */
    const banner = document.getElementById('stockSpotlightBanner');
    banner?.addEventListener('mousemove', e => {
        const rect = banner.getBoundingClientRect();
        banner.style.setProperty('--mouse-x', `${e.clientX - rect.left}px`);
        banner.style.setProperty('--mouse-y', `${e.clientY - rect.top}px`);
    });

    /* ---------- Alert Modal Logic ---------- */
    const alertSelect = document.getElementById('alertProductSelect');
    const alertInput  = document.getElementById('alertMinBoxInput');

    function syncAlertScope() {
        const isAll = document.getElementById('alertScopeAll')?.checked;
        document.getElementById('alertProductWrapper')?.classList.toggle('hidden', Boolean(isAll));
    }

    function syncAlertValue() {
        const opt = alertSelect?.options[alertSelect.selectedIndex];
        if (opt?.dataset.threshold && alertInput) {
            alertInput.value = opt.dataset.threshold;
        }
    }

    document.getElementById('alertScopeSingle')?.addEventListener('change', syncAlertScope);
    document.getElementById('alertScopeAll')?.addEventListener('change', syncAlertScope);
    alertSelect?.addEventListener('change', syncAlertValue);

    /* ---------- Opname Modal Logic ---------- */
    const opnameSelect = document.getElementById('opnameProductSelect');
    const opnameBoxIn  = document.getElementById('opnamePhysicalBox');
    const opnamePcsIn  = document.getElementById('opnamePhysicalPcs');

    function calculateOpnameDiff() {
        const opt = opnameSelect?.options[opnameSelect.selectedIndex];
        if (!opt) return;
        const ratio    = parseInt(opt.dataset.ratio || 1, 10);
        const sysPcs   = parseInt(opt.dataset.stockPcs || 0, 10);
        const physBox  = parseInt(opnameBoxIn?.value || 0, 10);
        const physPcs  = parseInt(opnamePcsIn?.value || 0, 10);
        const newTotal = (physBox * ratio) + physPcs;
        const diff     = newTotal - sysPcs;

        document.getElementById('opnameSystemLabel').textContent   = `${sysPcs.toLocaleString('id-ID')} Pcs`;
        document.getElementById('opnameNewTotalLabel').textContent = `${newTotal.toLocaleString('id-ID')} Pcs`;

        const diffEl = document.getElementById('opnameDiffLabel');
        diffEl.textContent = `${diff >= 0 ? '+' : ''}${diff.toLocaleString('id-ID')} Pcs`;
        diffEl.className   = diff === 0
            ? 'font-extrabold text-on-surface-variant'
            : (diff > 0 ? 'font-extrabold text-primary' : 'font-extrabold text-error');
    }

    function syncOpnameProduct() {
        const opt = opnameSelect?.options[opnameSelect.selectedIndex];
        if (!opt) return;
        if (opnameBoxIn) opnameBoxIn.value = opt.dataset.box || 0;
        if (opnamePcsIn) opnamePcsIn.value = opt.dataset.rem || 0;
        calculateOpnameDiff();
    }

    opnameSelect?.addEventListener('change', syncOpnameProduct);
    opnameBoxIn?.addEventListener('input', calculateOpnameDiff);
    opnamePcsIn?.addEventListener('input', calculateOpnameDiff);

    /* ---------- Mutation History Logic ---------- */
    async function loadMutations(url) {
        const loading = document.getElementById('mutLoadingState');
        const wrapper = document.getElementById('mutTableWrapper');
        const tbody   = document.getElementById('mutTableBody');

        loading.classList.remove('hidden');
        loading.textContent = 'Mengambil data mutasi gudang...';
        wrapper.classList.add('hidden');
        tbody.innerHTML = '';
        openModal('modalMutationHistory');

        try {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Gagal memuat riwayat mutasi.');
            const data = await res.json();

            document.getElementById('mutProductName').textContent = `${data.product.sku} — ${data.product.name}`;
            document.getElementById('mutProductMeta').textContent = `Saldo Saat Ini: ${data.product.stock_box.toLocaleString('id-ID')} Box + ${data.product.rem_pcs} Pcs (${data.product.stock_pcs.toLocaleString('id-ID')} Pcs) • 1 Box = ${data.product.ratio} Pcs`;

            if (!data.mutations.length) {
                loading.textContent = 'Belum ada riwayat transaksi masuk maupun keluar untuk produk ini.';
                return;
            }

            tbody.innerHTML = data.mutations.map(m => {
                const isIn = m.type === 'MASUK';
                const badge = isIn
                    ? '<span class="px-2 py-0.5 rounded bg-primary/15 text-primary text-[11px] font-bold">+ MASUK</span>'
                    : '<span class="px-2 py-0.5 rounded bg-error/15 text-error text-[11px] font-bold">- KELUAR</span>';
                const sign = isIn ? '+' : '-';
                const qtyColor = isIn ? 'text-primary font-bold' : 'text-error font-bold';

                return `
                    <tr class="hover:bg-surface-container/50">
                        <td class="py-2.5 px-3 text-on-surface-variant whitespace-nowrap">${m.date}</td>
                        <td class="py-2.5 px-3">${badge}</td>
                        <td class="py-2.5 px-3 font-mono text-on-surface font-semibold">${m.ref_no}</td>
                        <td class="py-2.5 px-3 text-on-surface">${m.party}</td>
                        <td class="py-2.5 px-3 text-right ${qtyColor}">${sign}${Number(m.total_pcs).toLocaleString('id-ID')} Pcs</td>
                        <td class="py-2.5 px-3 text-right text-on-surface font-semibold">${fmtRp(m.subtotal)}</td>
                    </tr>
                `;
            }).join('');

            loading.classList.add('hidden');
            wrapper.classList.remove('hidden');
        } catch (err) {
            loading.textContent = err.message || 'Terjadi kesalahan saat mengambil data.';
        }
    }

    /* ---------- Event Delegation untuk Semua Tombol ---------- */
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-action]');
        if (trigger) {
            const action = trigger.dataset.action;

            if (action === 'open-alert') {
                const singleRadio = document.getElementById('alertScopeSingle');
                if (singleRadio) singleRadio.checked = true;
                syncAlertScope();
                if (trigger.dataset.productId && alertSelect) {
                    alertSelect.value = trigger.dataset.productId;
                }
                if (trigger.dataset.threshold && alertInput) {
                    alertInput.value = trigger.dataset.threshold;
                } else {
                    syncAlertValue();
                }
                openModal('modalAlertThreshold');
                return;
            }

            if (action === 'open-opname') {
                if (trigger.dataset.productId && opnameSelect) {
                    opnameSelect.value = trigger.dataset.productId;
                }
                syncOpnameProduct();
                if (trigger.dataset.box !== undefined && opnameBoxIn) {
                    opnameBoxIn.value = trigger.dataset.box;
                }
                if (trigger.dataset.rem !== undefined && opnamePcsIn) {
                    opnamePcsIn.value = trigger.dataset.rem;
                }
                calculateOpnameDiff();
                openModal('modalOpnameStok');
                setTimeout(() => opnameBoxIn?.focus(), 50);
                return;
            }

            if (action === 'open-mutation' && trigger.dataset.url) {
                loadMutations(trigger.dataset.url);
                return;
            }

            if (action === 'close-modal' && trigger.dataset.target) {
                closeModal(trigger.dataset.target);
                return;
            }

            if (action === 'set-unit' && trigger.dataset.unit) {
                const mode = trigger.dataset.unit;
                document.querySelectorAll('.unit-btn').forEach(btn => {
                    const active = btn.dataset.unit === mode;
                    btn.className = active
                        ? 'unit-btn px-2.5 py-1 rounded-lg text-label-sm font-semibold bg-primary/20 text-primary cursor-pointer'
                        : 'unit-btn px-2.5 py-1 rounded-lg text-label-sm text-on-surface-variant hover:text-on-surface cursor-pointer';
                });
                document.querySelectorAll('.stock-cell').forEach(cell => {
                    cell.querySelector('.unit-view-dual')?.classList.toggle('hidden', mode !== 'dual');
                    cell.querySelector('.unit-view-box')?.classList.toggle('hidden', mode !== 'box');
                    cell.querySelector('.unit-view-pcs')?.classList.toggle('hidden', mode !== 'pcs');
                });
                return;
            }
        }

        // Tutup modal jika klik di luar card (backdrop)
        ['modalAlertThreshold', 'modalOpnameStok', 'modalMutationHistory'].forEach(id => {
            if (e.target && e.target.id === id) closeModal(id);
        });
    });

    /* ---------- Search Debounce & Shortcuts ---------- */
    const searchInput = document.getElementById('stockSearchInput');
    let searchTimer;
    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            sessionStorage.setItem('stockSearchFocus', '1');
            document.getElementById('stockFilterForm')?.submit();
        }, 450);
    });

    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            searchInput?.focus();
            searchInput?.select();
        }
        if (e.key === 'Escape') {
            closeModal('modalAlertThreshold');
            closeModal('modalOpnameStok');
            closeModal('modalMutationHistory');
        }
    });

    if (sessionStorage.getItem('stockSearchFocus') === '1' && searchInput) {
        sessionStorage.removeItem('stockSearchFocus');
        searchInput.focus();
        const len = searchInput.value.length;
        searchInput.setSelectionRange(len, len);
    }
});
</script>
@endsection