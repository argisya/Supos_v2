@extends('layouts.app')

@section('title', 'Master Barang')

@section('content')
    <div class="flex flex-col w-full pb-16">

        {{-- Ambient lights --}}
        <div class="relative w-full">
            <div
                class="absolute -top-10 right-20 w-96 h-96 bg-secondary-container/15 rounded-full blur-3xl pointer-events-none -z-10">
            </div>
            <div class="absolute top-48 left-10 w-80 h-80 bg-primary/10 rounded-full blur-3xl pointer-events-none -z-10">
            </div>
        </div>

        {{-- ============ HEADER SECTION ============ --}}
        <header class="flex flex-col gap-space-sm pt-space-md mb-space-lg">
            <div class="flex items-center justify-between flex-wrap gap-space-sm">
                <nav class="flex items-center gap-space-xs text-on-surface-variant font-label-md text-label-md">
                    <span class="hover:text-on-surface transition-colors cursor-pointer">Sistem Grosir</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="hover:text-on-surface transition-colors cursor-pointer">Master Data</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="text-primary font-semibold">Katalog Produk</span>
                </nav>
                <div
                    class="flex items-center gap-space-xs bg-surface-container-high px-space-md py-1 rounded-full border border-outline-variant/30">
                    <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                    <span class="font-label-md text-label-md text-on-surface font-semibold tracking-wide">
                        {{ $stats['total_sku'] }} Item Aktif Terdaftar
                    </span>
                </div>
            </div>

            <div class="flex items-start justify-between flex-wrap gap-space-lg mt-space-xs">
                <div class="flex flex-col max-w-2xl">
                    <h1
                        class="font-headline-lg text-headline-lg text-on-surface tracking-tight flex items-center gap-space-sm">
                        Master Barang & Standardisasi Box
                        <span
                            class="bg-primary/10 text-primary text-label-sm font-label-sm px-space-xs py-0.5 rounded border border-primary/20">RULE
                            ENGINE</span>
                    </h1>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-1 leading-relaxed">
                        Kamus referensi konversi baku <span
                            class="font-label-md text-on-surface text-label-sm px-1.5 py-0.5 bg-surface-container-highest rounded">1
                            BOX = X PCS</span>. Seluruh modul inventori, kasir, dan gudang mengacu pada master data ini
                        tanpa perlu kalkulasi manual staf.
                    </p>
                </div>
                <div class="flex items-center gap-space-sm flex-wrap">
                    <a id="productExportLink" href="{{ route('products.export', request()->query()) }}"
                        class="h-10 px-space-md rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface font-body-sm text-body-sm flex items-center gap-space-xs transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px] text-tertiary">download</span>
                        <span>Export CSV</span>
                    </a>
                    <button type="button" onclick="document.getElementById('modalImportCsv').classList.remove('hidden')"
                        class="h-10 px-space-md rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/20 hover:border-tertiary/40 text-on-surface font-body-sm text-body-sm flex items-center gap-space-xs transition-all shadow-sm cursor-pointer">
                        <span class="material-symbols-outlined text-[18px] text-tertiary">upload_file</span>
                        <span>Import CSV</span>
                    </button>
                    <button id="btnOpenScanner"
                        class="h-10 px-space-md rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface font-body-sm text-body-sm flex items-center gap-space-xs transition-colors shadow-sm cursor-pointer"
                        type="button">
                        <span class="material-symbols-outlined text-[18px] text-secondary">qr_code_scanner</span>
                        <span>Scan Barcode SKU</span>
                    </button>
                    <button id="btnOpenModal"
                        class="h-10 px-space-lg rounded-xl bg-linear-to-r from-secondary-container via-primary-container to-primary text-on-primary-container font-headline-sm text-headline-sm flex items-center gap-space-xs shadow-[0_8px_24px_rgba(16,185,129,0.35)] hover:shadow-[0_8px_32px_rgba(16,185,129,0.5)] active:scale-95 transition-all cursor-pointer"
                        type="button">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        <span>Tambah Barang Baru</span>
                    </button>
                </div>
            </div>
        </header>

        {{-- ============ BANNER INFO ============ --}}
        <section class="mb-space-lg rounded-xl bg-surface-container-low/70 backdrop-blur-xl p-space-lg shadow-md">
            <div class="flex flex-col lg:flex-row items-stretch justify-between gap-space-lg">
                <div class="flex items-start gap-space-md flex-1">
                    <div
                        class="w-12 h-12 rounded-xl bg-tertiary/15 flex items-center justify-center shrink-0 text-tertiary shadow-[0_0_16px_rgba(76,215,246,0.2)]">
                        <span class="material-symbols-outlined text-[26px]">verified</span>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-space-xs">
                            <span class="font-headline-sm text-headline-sm text-on-surface">Pemisahan Domain: Master
                                Spesifikasi Murni</span>
                            <span
                                class="font-label-sm text-label-sm text-tertiary uppercase tracking-wider bg-tertiary/10 px-2 py-0.5 rounded-full">Kaidah
                                Akuntansi</span>
                        </div>
                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 leading-relaxed">
                            Halaman ini <strong class="text-on-surface font-semibold">TIDAK</strong> mengikat saldo modal
                            ataupun kuantitas fisik gudang. Harga modal dicatat pada Barang Masuk, harga jual di Barang
                            Keluar, dan saldo fisik di Stok Barang.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-space-sm shrink-0 lg:w-5/12">
                    <div class="bg-surface-container p-space-sm rounded-xl flex flex-col justify-between">
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Total SKU Aktif</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span
                                class="font-display-lg text-headline-lg text-primary leading-none">{{ $stats['total_sku'] }}</span>
                            <span class="font-label-sm text-label-sm text-on-surface-variant">Produk</span>
                        </div>
                    </div>
                    <div class="bg-surface-container p-space-sm rounded-xl flex flex-col justify-between">
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Rata2 Konversi</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span
                                class="font-display-lg text-headline-lg text-tertiary leading-none">{{ $stats['avg_ratio'] }}</span>
                            <span class="font-label-sm text-label-sm text-on-surface-variant">Pcs/Box</span>
                        </div>
                    </div>
                    <div class="bg-surface-container p-space-sm rounded-xl flex flex-col justify-between">
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Kategori Master</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span
                                class="font-display-lg text-headline-lg text-secondary leading-none">{{ $stats['total_categories'] }}</span>
                            <span class="font-label-sm text-label-sm text-on-surface-variant">Grup</span>
                        </div>
                    </div>
                    {{-- PERBAIKAN POIN 4: Menampilkan waktu update katalog aktual dari database --}}
                    <div class="bg-surface-container p-space-sm rounded-xl flex flex-col justify-between">
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Update Katalog</span>
                        <div class="flex flex-col mt-1">
                            <span
                                class="font-label-md text-label-md text-on-surface font-semibold leading-tight">{{ $stats['last_sync_label'] ?? 'Hari ini' }}</span>
                            <span class="font-label-sm text-label-sm text-primary">{{ $stats['last_sync'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============ FILTER ============ --}}
        <form method="GET" action="{{ route('products.index') }}" id="filterForm"
            class="flex flex-col gap-space-md mb-space-md">
            <div class="flex items-center justify-between flex-wrap gap-space-sm">
                <div class="relative flex-1 min-w-[320px] max-w-xl">
                    <span
                        class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                    <input name="search" value="{{ request('search') }}" oninput="debouncedSearch()"
                        class="w-full h-11 pl-11 pr-10 rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/60 font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-high transition-all shadow-inner"
                        placeholder="Cari nama barang, kode SKU, atau barcode..." type="text" />
                    <span
                        class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant/70 text-[18px]">barcode_scanner</span>
                </div>

                <div class="flex items-center gap-space-sm flex-wrap">
                    {{-- Hidden fields untuk filter lain --}}
                    <input type="hidden" name="category" value="{{ $catSlug }}">

                    {{-- Filter Status Aktif / Arsip --}}
                    <div class="flex items-center gap-space-xs bg-surface-container px-space-sm py-1.5 rounded-xl">
                        <span class="material-symbols-outlined text-primary text-[18px]">verified_user</span>
                        <select name="status" onchange="this.form.submit()"
                            class="bg-transparent text-on-surface font-label-md text-label-md focus:outline-none cursor-pointer">
                            <option class="bg-surface-container-high" value="active" @selected(request('status', 'active') === 'active')>Status:
                                Aktif Baku</option>
                            <option class="bg-surface-container-high" value="archived" @selected(request('status') === 'archived')>Status:
                                Diarsipkan</option>
                            <option class="bg-surface-container-high" value="all" @selected(request('status') === 'all')>Semua
                                Status</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-space-xs bg-surface-container px-space-sm py-1.5 rounded-xl">
                        <span class="material-symbols-outlined text-on-surface-variant text-[18px]">filter_alt</span>
                        <select name="box_size" onchange="this.form.submit()"
                            class="bg-transparent text-on-surface font-label-md text-label-md focus:outline-none cursor-pointer">
                            <option class="bg-surface-container-high" value="all" @selected($boxSize === 'all')>Semua
                                Ukuran Box</option>
                            <option class="bg-surface-container-high" value="compact" @selected($boxSize === 'compact')>Box
                                Kecil
                                (1 - 12 Pcs)</option>
                            <option class="bg-surface-container-high" value="medium" @selected($boxSize === 'medium')>Box
                                Sedang
                                (13 - 39 Pcs)</option>
                            <option class="bg-surface-container-high" value="large" @selected($boxSize === 'large')>Box
                                Besar
                                (40+ Pcs)</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-space-xs bg-surface-container px-space-sm py-1.5 rounded-xl">
                        <span class="material-symbols-outlined text-on-surface-variant text-[18px]">sort</span>
                        <select name="sort" onchange="this.form.submit()"
                            class="bg-transparent text-on-surface font-label-md text-label-md focus:outline-none cursor-pointer">
                            <option class="bg-surface-container-high" value="latest" @selected(request('sort', 'latest') === 'latest')>Urutkan:
                                SKU Terbaru</option>
                            <option class="bg-surface-container-high" value="name" @selected(request('sort') === 'name')>Nama
                                Produk (A - Z)</option>
                            <option class="bg-surface-container-high" value="ratio_desc" @selected(request('sort') === 'ratio_desc')>Isi
                                Terbanyak (Pcs)</option>
                            <option class="bg-surface-container-high" value="category" @selected(request('sort') === 'category')>
                                Kategori Produk</option>
                        </select>
                    </div>

                    <div class="flex items-center bg-surface-container p-1 rounded-xl">
                        <button type="button" id="btnViewTable"
                            class="px-space-sm py-1 rounded-lg bg-surface-container-high text-primary font-label-md text-label-md flex items-center gap-1 shadow-sm transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-[18px]">table_rows</span>
                            <span class="hidden sm:inline">Tabel</span>
                        </button>
                        <button type="button" id="btnViewCard"
                            class="px-space-sm py-1 rounded-lg text-on-surface-variant hover:text-on-surface font-label-md text-label-md flex items-center gap-1 transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-[18px]">grid_view</span>
                            <span class="hidden sm:inline">Kartu</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Kategori Pills --}}
            <div class="flex min-w-0 items-center gap-space-xs">
                <div id="categoryPills"
                    class="flex min-w-0 flex-1 items-center gap-space-xs overflow-x-auto pb-1 scrollbar-none">
                    <button type="submit" name="category" value="all"
                        class="category-pill shrink-0 px-space-md py-1.5 rounded-full text-label-md font-label-md flex items-center gap-1.5 transition-all cursor-pointer
                        {{ $catSlug === 'all'
                            ? 'bg-primary text-on-primary font-bold shadow-[0_0_12px_rgba(78,222,163,0.35)]'
                            : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                        <span>Semua</span>
                        <span
                            class="bg-on-primary/20 text-on-primary px-1.5 py-0.2 rounded-full text-label-sm">{{ $catalogTotal }}</span>
                    </button>
                    @foreach ($categories as $c)
                        <button type="submit" name="category" value="{{ $c->slug }}"
                            class="category-pill shrink-0 px-space-md py-1.5 rounded-full text-label-md font-label-md flex items-center gap-1.5 transition-all cursor-pointer
                            {{ $catSlug === $c->slug
                                ? 'bg-primary text-on-primary font-bold shadow-[0_0_12px_rgba(78,222,163,0.35)]'
                                : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                            <span>{{ $c->name }}</span>
                            <span
                                class="bg-surface-container-highest text-on-surface-variant px-1.5 py-0.2 rounded-full text-label-sm">{{ $c->products_count }}</span>
                        </button>
                    @endforeach
                </div>
                <button type="button" id="btnOpenCategoryModal"
                    class="shrink-0 px-space-md py-1.5 rounded-full text-label-md font-label-md flex items-center gap-1 border border-dashed border-outline-variant hover:border-primary text-on-surface-variant hover:text-primary transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    <span>Kelola Kategori</span>
                </button>
            </div>
        </form>

        {{-- ============ TABLE VIEW ============ --}}
        <div id="tableViewContainer" class="w-full rounded-2xl bg-surface-container-low shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-surface-container font-label-md text-label-md text-on-surface-variant uppercase tracking-wider select-none">
                            <th class="py-space-md pl-space-lg pr-2 w-10">
                                <input type="checkbox" id="selectAllRows"
                                    class="w-4 h-4 rounded border-outline-variant bg-surface-container text-primary focus:ring-primary cursor-pointer" />
                            </th>
                            <th class="py-space-md px-space-lg font-semibold">SKU & Barcode</th>
                            <th class="py-space-md px-space-md font-semibold">Nama Produk & Varian</th>
                            <th class="py-space-md px-space-md font-semibold">Kategori</th>
                            <th class="py-space-md px-space-md font-semibold text-center">Aturan Baku Kemasan</th>
                            <th class="py-space-md px-space-md font-semibold">Simulator Cepat</th>
                            <th class="py-space-md px-space-md font-semibold">Status</th>
                            <th class="py-space-md px-space-lg font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-highest/20 font-body-sm text-body-sm text-on-surface"
                        id="catalogTableBody">
                        @forelse($products as $product)
                            @include('products.partials.table-row', ['product' => $product])
                        @empty
                            <tr>
                                <td colspan="8" class="py-space-xl text-center text-on-surface-variant">
                                    Tidak ada produk yang cocok dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============ CARD VIEW ============ --}}
        <div id="cardViewContainer" class="hidden grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-space-md">
            @forelse ($products as $product)
                @include('products.partials.card-item', ['product' => $product])
            @empty
                <div class="md:col-span-2 xl:col-span-4 py-space-xl text-center text-on-surface-variant bg-surface-container-low rounded-2xl">
                    Tidak ada produk yang cocok dengan filter.
                </div>
            @endforelse
        </div>

        {{-- ============ PAGINATION ============ --}}
        <footer class="mt-space-lg flex items-center justify-between flex-wrap gap-space-md">
            <div class="flex items-center gap-space-sm text-on-surface-variant font-label-md text-label-md">
                <span>Menampilkan <strong class="text-on-surface">{{ $products->firstItem() ?? 0 }} -
                        {{ $products->lastItem() ?? 0 }}</strong> dari total <strong
                        class="text-on-surface">{{ $products->total() }}</strong> Master Produk</span>
                <span class="text-outline-variant">•</span>
                {{-- PERBAIKAN POIN 4: Mengganti klaim Auto-Sync Transaksi dengan deskripsi Rule Engine aktual --}}
                <span class="flex items-center gap-1 text-primary">
                    <span class="material-symbols-outlined text-[14px]">rule</span>
                    Rule Engine Aktif (1 Box = X Pcs)
                </span>
            </div>
            <div class="flex items-center gap-space-xs">{{ $products->links() }}</div>
        </footer>
    </div>

    {{-- ============ MODAL TAMBAH BARANG ============ --}}
    @include('products.partials.modal-create')
    @include('products.partials.modal-edit-detail')
    @include('products.partials.modal-phase3')
    @include('products.partials.modal-phase4')
    @include('products.partials.modal-confirmation')

@endsection

@push('scripts')
    <script>
        (function() {
            function showToast(message, type = 'success') {
                const toast = document.createElement('div');
                const isWarning = type === 'warning';
                toast.setAttribute('role', isWarning ? 'alert' : 'status');
                toast.className = 'fixed bottom-6 right-6 z-[100] flex max-w-[min(90vw,28rem)] items-center gap-2 rounded-xl px-space-lg py-space-md font-headline-sm text-body-md shadow-2xl ' +
                    (isWarning ? 'bg-error-container text-on-error-container' : 'bg-primary text-on-primary');

                const icon = document.createElement('span');
                icon.className = 'material-symbols-outlined';
                icon.textContent = isWarning ? 'warning' : 'check_circle';

                const text = document.createElement('span');
                text.textContent = message;
                toast.append(icon, text);
                document.body.append(toast);
                setTimeout(() => toast.remove(), 4000);
            }

            /* ---------- Row Simulator ---------- */
            document.addEventListener('input', event => {
                if (!event.target.matches('.sim-input')) return;

                const ratio = parseInt(event.target.dataset.ratio, 10) || 1;
                const quantity = parseInt(event.target.value, 10) || 0;
                event.target.parentElement.querySelector('.sim-result').textContent = (quantity * ratio) + ' Pcs';
            });

            /* ---------- View Switcher ---------- */
            const btnT = document.getElementById('btnViewTable');
            const btnC = document.getElementById('btnViewCard');
            const tV = document.getElementById('tableViewContainer');
            const cV = document.getElementById('cardViewContainer');
            btnT.addEventListener('click', () => {
                btnT.classList.add('bg-surface-container-high', 'text-primary');
                btnT.classList.remove('text-on-surface-variant');
                btnC.classList.remove('bg-surface-container-high', 'text-primary');
                btnC.classList.add('text-on-surface-variant');
                tV.classList.remove('hidden');
                cV.classList.add('hidden');
            });
            btnC.addEventListener('click', () => {
                btnC.classList.add('bg-surface-container-high', 'text-primary');
                btnC.classList.remove('text-on-surface-variant');
                btnT.classList.remove('bg-surface-container-high', 'text-primary');
                btnT.classList.add('text-on-surface-variant');
                tV.classList.add('hidden');
                cV.classList.remove('hidden');
            });

            /* ---------- Modal Open/Close ---------- */
            const modal = document.getElementById('modalTambahBarang');
            document.getElementById('btnOpenModal').addEventListener('click', () => modal.classList.remove('hidden'));
            document.getElementById('btnCloseModal').addEventListener('click', () => modal.classList.add('hidden'));
            document.getElementById('btnCancelModal').addEventListener('click', () => modal.classList.add('hidden'));
            modal.addEventListener('click', e => {
                if (e.target === modal) modal.classList.add('hidden');
            });

            /* ---------- Modal Box-Pcs Stepper ---------- */
            const pcsInput = document.getElementById('inputModalPcs');
            const simOut = document.getElementById('modalSimOutput');
            const updateSim = () => simOut.textContent = 'Otomatis Dihitung: ' + (10 * (parseInt(pcsInput.value, 10) ||
                1)) + ' Pcs';
            document.getElementById('btnIncPcs').addEventListener('click', () => {
                pcsInput.value = (parseInt(pcsInput.value, 10) || 0) + 1;
                updateSim();
            });
            document.getElementById('btnDecPcs').addEventListener('click', () => {
                const c = parseInt(pcsInput.value, 10) || 1;
                if (c > 1) pcsInput.value = c - 1;
                updateSim();
            });
            pcsInput.addEventListener('input', updateSim);

            document.querySelectorAll('.preset-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.preset-btn').forEach(b => {
                        b.classList.remove('bg-surface-container-highest', 'text-primary',
                            'font-bold');
                        b.classList.add('bg-surface-container', 'text-on-surface');
                    });
                    this.classList.remove('bg-surface-container', 'text-on-surface');
                    this.classList.add('bg-surface-container-highest', 'text-primary', 'font-bold');
                    pcsInput.value = this.dataset.val;
                    updateSim();
                });
            });

            /* ---------- Auto-Generate SKU (server-side) ---------- */
            document.getElementById('btnGenSKU').addEventListener('click', async function() {
                const catSelect = document.getElementById('selectKategori');
                const catSlug = catSelect.options[catSelect.selectedIndex].dataset.slug || 'gen';
                const res = await fetch(
                    `{{ route('products.generate-sku') }}?category_slug=${encodeURIComponent(catSlug)}`
                );
                const json = await res.json();
                document.getElementById('inputSKU').value = json.sku;
            });

            /* ---------- Debounced Search ---------- */
            let searchTimer;
            window.debouncedSearch = () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 400);
            };

            /* ---------- Modal Detail & Edit Produk ---------- */
            const detailModal = document.getElementById('modalDetailProduk');
            const editModal = document.getElementById('modalEditProduk');

            window.closeDetailModal = () => detailModal?.classList.add('hidden');
            window.closeEditModal = () => editModal?.classList.add('hidden');

            window.openProductDetail = async function(productId) {
                try {
                    const res = await fetch(`/products/${productId}`);
                    if (!res.ok) throw new Error();
                    const p = await res.json();

                    document.getElementById('detailName').textContent = p.name;
                    document.getElementById('detailSkuBadge').textContent = p.sku;
                    document.getElementById('detailCategory').textContent = p.category_name;
                    document.getElementById('detailBarcode').textContent = p.barcode || 'Tidak ada barcode';
                    document.getElementById('detailVariant').textContent = p.variant || '—';
                    document.getElementById('detailRatio').textContent = `1 BOX = ${p.box_pcs_ratio} PCS`;
                    document.getElementById('detailStock').textContent = p.stock_preview;
                    document.getElementById('detailModalPrice').textContent = p.last_modal_box;
                    document.getElementById('detailCreated').textContent = p.created_at;
                    document.getElementById('detailUpdated').textContent = p.updated_at;

                    const historyEntries = document.getElementById('detailHistoryEntries');
                    historyEntries.replaceChildren();
                    if (p.recent_transactions.length === 0) {
                        const emptyState = document.createElement('p');
                        emptyState.className = 'text-label-sm text-on-surface-variant';
                        emptyState.textContent = 'Belum ada transaksi untuk produk ini.';
                        historyEntries.append(emptyState);
                    } else {
                        p.recent_transactions.forEach(transaction => {
                            const entry = document.createElement('div');
                            entry.className = 'flex flex-wrap items-center justify-between gap-2 py-2 border-b border-surface-container-highest/40 last:border-0';

                            const reference = document.createElement('div');
                            reference.className = 'min-w-0';

                            const title = document.createElement('strong');
                            title.className = 'block text-on-surface font-label-sm';
                            title.textContent = `${transaction.type} · ${transaction.reference}`;

                            const detail = document.createElement('span');
                            detail.className = 'block text-label-sm text-on-surface-variant';
                            detail.textContent = `${transaction.date} · ${transaction.box_qty} Box`;

                            const quantity = document.createElement('span');
                            quantity.className = 'text-label-sm text-primary font-semibold whitespace-nowrap';
                            quantity.textContent = `${Number(transaction.total_pcs).toLocaleString('id-ID')} Pcs`;

                            reference.append(title, detail);
                            entry.append(reference, quantity);
                            historyEntries.append(entry);
                        });
                    }

                    detailModal.classList.remove('hidden');
                } catch (e) {
                    showToast('Gagal memuat detail produk.', 'warning');
                }
            };

            const editForm = document.getElementById('formEditProduk');
            const editRatioInput = document.getElementById('editRatio');
            const editSimPreview = document.getElementById('editSimPreview');
            const updateEditSim = () => {
                const val = parseInt(editRatioInput.value, 10) || 1;
                editSimPreview.textContent = `Simulasi 10 Box = ${(val * 10).toLocaleString('id-ID')} Pcs`;
            };
            editRatioInput?.addEventListener('input', updateEditSim);

            window.openProductEdit = async function(productId) {
                try {
                    const res = await fetch(`/products/${productId}`);
                    if (!res.ok) throw new Error();
                    const p = await res.json();

                    editForm.action = `/products/${p.id}`;
                    document.getElementById('editName').value = p.name;
                    document.getElementById('editVariant').value = p.variant || '';
                    document.getElementById('editCategory').value = p.category_id;
                    document.getElementById('editSku').value = p.sku;
                    document.getElementById('editBarcode').value = p.barcode || '';
                    editRatioInput.value = p.box_pcs_ratio;
                    editRatioInput.readOnly = p.has_transactions;
                    editRatioInput.setAttribute('aria-readonly', String(p.has_transactions));
                    updateEditSim();
                    updateEditSim();

                    const warnEl = document.getElementById('editTransactionWarning');
                    if (p.has_transactions) {
                        warnEl.classList.remove('hidden');
                    } else {
                        warnEl.classList.add('hidden');
                    }

                    editModal.classList.remove('hidden');
                } catch (e) {
                    showToast('Gagal memuat data untuk diedit.', 'warning');
                }
            };

            /* ---------- Modal Scan Barcode & Kelola Kategori ---------- */
            const scanModal = document.getElementById('modalScanBarcode');
            const scanInput = document.getElementById('scannerInput');
            window.closeScannerModal = () => scanModal?.classList.add('hidden');

            document.getElementById('btnOpenScanner')?.addEventListener('click', () => {
                scanModal?.classList.remove('hidden');
                setTimeout(() => scanInput?.focus(), 60);
            });
            scanModal?.addEventListener('click', e => {
                if (e.target === scanModal) closeScannerModal();
            });

            const catModal = document.getElementById('modalKelolaKategori');
            window.closeCategoryModal = () => catModal?.classList.add('hidden');

            document.addEventListener('click', event => {
                if (event.target.closest('#btnOpenCategoryModal')) {
                    catModal?.classList.remove('hidden');
                }
            });
            catModal?.addEventListener('click', e => {
                if (e.target === catModal) closeCategoryModal();
            });

            /* ---------- Themed Action Confirmation Modal ---------- */
            const actionConfirmationModal = document.getElementById('modalActionConfirmation');
            const confirmationTitle = document.getElementById('actionConfirmationTitle');
            const confirmationMessage = document.getElementById('actionConfirmationMessage');
            const confirmationIcon = document.getElementById('actionConfirmationIcon');
            const cancelConfirmationButton = document.getElementById('btnCancelActionConfirmation');
            const confirmActionButton = document.getElementById('btnConfirmActionConfirmation');
            let pendingConfirmation = null;

            function closeActionConfirmation(restoreFocus = true) {
                actionConfirmationModal.classList.add('hidden');
                actionConfirmationModal.classList.remove('flex');
                const trigger = pendingConfirmation?.trigger;
                pendingConfirmation = null;
                if (restoreFocus && trigger?.isConnected) trigger.focus();
            }

            function getConfirmationOptions(form, submitter) {
                const source = submitter?.dataset.confirmMessage ? submitter : form;
                if (!source.dataset.confirmMessage) return null;

                return {
                    title: source.dataset.confirmTitle || 'Konfirmasi tindakan',
                    message: source.dataset.confirmMessage,
                    confirmLabel: source.dataset.confirmConfirmLabel || 'Lanjutkan',
                    variant: source.dataset.confirmVariant || 'danger',
                };
            }

            document.addEventListener('submit', event => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) return;
                if (form.dataset.confirmationApproved === 'true') {
                    delete form.dataset.confirmationApproved;
                    return;
                }

                const options = getConfirmationOptions(form, event.submitter);
                if (!options) return;

                event.preventDefault();
                pendingConfirmation = {
                    form,
                    submitter: event.submitter,
                    trigger: event.submitter || document.activeElement,
                };
                confirmationTitle.textContent = options.title;
                confirmationMessage.textContent = options.message;
                confirmActionButton.textContent = options.confirmLabel;

                const iconStyles = {
                    danger: ['warning', 'bg-error/15', 'text-error'],
                    primary: ['check_circle', 'bg-primary/15', 'text-primary'],
                    tertiary: ['upload_file', 'bg-tertiary/15', 'text-tertiary'],
                };
                const buttonStyles = {
                    danger: ['bg-error', 'text-on-error', 'hover:bg-error/90'],
                    primary: ['bg-primary', 'text-on-primary', 'hover:bg-primary/90'],
                    tertiary: ['bg-tertiary', 'text-on-tertiary', 'hover:bg-tertiary/90'],
                };
                const [icon, ...iconClasses] = iconStyles[options.variant] || iconStyles.danger;
                confirmationIcon.textContent = icon;
                confirmationIcon.className = 'material-symbols-outlined flex h-10 w-10 shrink-0 items-center justify-center rounded-full ' +
                    iconClasses.join(' ');
                confirmActionButton.className = 'h-10 rounded-xl px-space-lg font-label-md font-bold transition-colors cursor-pointer ' +
                    (buttonStyles[options.variant] || buttonStyles.danger).join(' ');

                actionConfirmationModal.classList.remove('hidden');
                actionConfirmationModal.classList.add('flex');
                cancelConfirmationButton.focus();
            });

            cancelConfirmationButton.addEventListener('click', () => closeActionConfirmation());
            actionConfirmationModal.addEventListener('click', event => {
                if (event.target === actionConfirmationModal) closeActionConfirmation();
            });
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && !actionConfirmationModal.classList.contains('hidden')) {
                    closeActionConfirmation();
                }
            });
            confirmActionButton.addEventListener('click', () => {
                if (!pendingConfirmation) return;

                const { form, submitter } = pendingConfirmation;
                pendingConfirmation = null;
                actionConfirmationModal.classList.add('hidden');
                actionConfirmationModal.classList.remove('flex');
                form.dataset.confirmationApproved = 'true';
                if (submitter instanceof HTMLElement && submitter.form === form) {
                    form.requestSubmit(submitter);
                } else {
                    form.requestSubmit();
                }
            });

            @if (session('error'))
                showToast(@json(session('error')), 'warning');
            @endif
            @if ($errors->has('file'))
                document.getElementById('modalImportCsv')?.classList.remove('hidden');
                showToast(@json($errors->first('file')), 'warning');
            @endif
            @if ($errors->has('box_pcs_ratio'))
                showToast(@json($errors->first('box_pcs_ratio')), 'warning');
            @endif

            /* ---------- Bulk Checkbox Selection ---------- */
            const selectAll = document.getElementById('selectAllRows');
            const bulkBar = document.getElementById('bulkActionBar');
            const bulkCount = document.getElementById('bulkSelectedCount');
            const bulkInputs = document.getElementById('bulkHiddenInputs');

            function syncBulkState() {
                const checked = Array.from(document.querySelectorAll('.row-checkbox:checked'));
                if (bulkCount) bulkCount.textContent = checked.length;
                if (bulkInputs) {
                    bulkInputs.innerHTML = checked.map(cb => `<input type="hidden" name="ids[]" value="${cb.value}">`)
                        .join('');
                }
                if (bulkBar) {
                    if (checked.length > 0) {
                        bulkBar.classList.remove('hidden');
                        bulkBar.classList.add('flex');
                    } else {
                        bulkBar.classList.add('hidden');
                        bulkBar.classList.remove('flex');
                    }
                }
            }

            selectAll?.addEventListener('change', function() {
                document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
                syncBulkState();
            });

            document.addEventListener('change', (e) => {
                if (e.target.classList?.contains('row-checkbox')) syncBulkState();
            });

            /* ---------- Live AJAX Filter & Search Tanpa Reload Halaman ---------- */
            const filterForm = document.getElementById('filterForm');
            async function fetchCatalogAjax(url) {
                const tableBody = document.getElementById('catalogTableBody');
                const cardWrap = document.getElementById('cardViewContainer');
                if (tableBody) tableBody.style.opacity = '0.45';
                if (cardWrap) cardWrap.style.opacity = '0.45';

                try {
                    const res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');

                    const newTbody = doc.getElementById('catalogTableBody');
                    const newCards = doc.getElementById('cardViewContainer');
                    const newCategoryPills = doc.getElementById('categoryPills');
                    const newExportLink = doc.getElementById('productExportLink');
                    const newFooter = doc.querySelector('footer');

                    if (newTbody && tableBody) tableBody.innerHTML = newTbody.innerHTML;
                    if (newCards && cardWrap) cardWrap.innerHTML = newCards.innerHTML;
                    if (newCategoryPills) {
                        document.getElementById('categoryPills').innerHTML = newCategoryPills.innerHTML;
                    }
                    if (newExportLink) {
                        document.getElementById('productExportLink').href = newExportLink.href;
                    }
                    if (newFooter && document.querySelector('footer')) {
                        document.querySelector('footer').innerHTML = newFooter.innerHTML;
                    }

                    window.history.replaceState({}, '', url);
                    if (selectAll) selectAll.checked = false;
                    syncBulkState();
                } catch (err) {
                    window.location.href = url;
                } finally {
                    if (tableBody) tableBody.style.opacity = '1';
                    if (cardWrap) cardWrap.style.opacity = '1';
                }
            }

            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const params = new URLSearchParams(new FormData(filterForm));
                    const submitter = e.submitter;
                    if (submitter && submitter.name) {
                        params.set(submitter.name, submitter.value);
                        const hiddenCat = filterForm.querySelector('input[name="category"]');
                        if (hiddenCat && submitter.name === 'category') hiddenCat.value = submitter.value;
                    }
                    fetchCatalogAjax(`${filterForm.action}?${params.toString()}`);
                });

                filterForm.querySelectorAll('select').forEach(sel => {
                    sel.onchange = () => {
                        const params = new URLSearchParams(new FormData(filterForm));
                        fetchCatalogAjax(`${filterForm.action}?${params.toString()}`);
                    };
                });

                window.debouncedSearch = () => {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => {
                        const params = new URLSearchParams(new FormData(filterForm));
                        fetchCatalogAjax(`${filterForm.action}?${params.toString()}`);
                    }, 350);
                };
            }
        })();
    </script>
@endpush
