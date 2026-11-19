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
                    <span>Master Data</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="text-primary font-semibold">Katalog Produk</span>
                </nav>
                <div
                    class="flex items-center gap-space-xs bg-surface-container-high px-space-md py-1 rounded-full border border-outline-variant/30">
                    <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                    <span class="font-label-md text-label-md text-on-surface font-semibold tracking-wide">
                        {{ $stats['total_sku'] }} Produk Terdaftar
                    </span>
                </div>
            </div>

            <div class="flex items-start justify-between flex-wrap gap-space-lg mt-space-xs">
                <div class="flex flex-col max-w-2xl">
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">
                        Master Barang & Konversi Box
                    </h1>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-1 leading-relaxed">
                        Aturan isi per box (<span
                            class="font-label-md text-on-surface text-label-sm px-1.5 py-0.5 bg-surface-container-highest rounded">1
                            BOX = X PCS</span>) berlaku otomatis untuk seluruh transaksi Barang Masuk, Barang Keluar, dan
                        Stok.
                    </p>
                </div>
                <div class="flex items-center gap-space-sm flex-wrap">
                    <a id="productExportLink" href="{{ route('products.export', request()->query()) }}"
                        class="h-10 px-space-md rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/20 text-on-surface font-body-sm text-body-sm flex items-center gap-space-xs transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px] text-tertiary">download</span>
                        <span>Export CSV</span>
                    </a>
                    <button type="button" onclick="document.getElementById('modalImportCsv').classList.remove('hidden')"
                        class="h-10 px-space-md rounded-xl bg-surface-container-low hover:bg-surface-container border border-outline-variant/20 hover:border-tertiary/40 text-on-surface font-body-sm text-body-sm flex items-center gap-space-xs transition-all shadow-sm cursor-pointer">
                        <span class="material-symbols-outlined text-[18px] text-tertiary">upload_file</span>
                        <span>Import CSV</span>
                    </button>
                    <button id="btnOpenModal"
                        class="h-10 px-space-lg rounded-xl bg-primary hover:bg-primary/90 text-on-primary font-headline-sm text-headline-sm font-bold flex items-center gap-space-xs shadow-md hover:shadow-lg active:scale-95 transition-all cursor-pointer"
                        type="button">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        <span>Tambah Barang Baru</span>
                    </button>
                </div>
            </div>
        </header>

        {{-- ============ RINGKASAN KATALOG ============ --}}
        <section class="mb-space-lg grid grid-cols-2 lg:grid-cols-4 gap-space-sm">
            <div
                class="bg-surface-container-low border border-outline-variant/15 p-space-md rounded-xl flex items-center justify-between">
                <div>
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Total Produk</span>
                    <div class="flex items-baseline gap-1.5 mt-1">
                        <span
                            class="font-display-lg text-headline-lg text-primary leading-none">{{ $stats['total_sku'] }}</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">SKU</span>
                    </div>
                </div>
                <span class="material-symbols-outlined text-primary/40 text-[28px]">inventory_2</span>
            </div>
            <div
                class="bg-surface-container-low border border-outline-variant/15 p-space-md rounded-xl flex items-center justify-between">
                <div>
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Rata-rata Isi</span>
                    <div class="flex items-baseline gap-1.5 mt-1">
                        <span
                            class="font-display-lg text-headline-lg text-tertiary leading-none">{{ $stats['avg_ratio'] }}</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">Pcs/Box</span>
                    </div>
                </div>
                <span class="material-symbols-outlined text-tertiary/40 text-[28px]">package_2</span>
            </div>
            <div
                class="bg-surface-container-low border border-outline-variant/15 p-space-md rounded-xl flex items-center justify-between">
                <div>
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Jumlah Kategori</span>
                    <div class="flex items-baseline gap-1.5 mt-1">
                        <span
                            class="font-display-lg text-headline-lg text-secondary leading-none">{{ $stats['total_categories'] }}</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">Kategori</span>
                    </div>
                </div>
                <span class="material-symbols-outlined text-secondary/40 text-[28px]">category</span>
            </div>
            <div
                class="bg-surface-container-low border border-outline-variant/15 p-space-md rounded-xl flex items-center justify-between">
                <div>
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block">Update Terakhir</span>
                    <div class="flex flex-col mt-1">
                        <span
                            class="font-label-md text-label-md text-on-surface font-semibold leading-tight">{{ $stats['last_sync_label'] ?? 'Hari ini' }}</span>
                        <span class="font-label-sm text-label-sm text-primary">{{ $stats['last_sync'] }}</span>
                    </div>
                </div>
                <span class="material-symbols-outlined text-on-surface-variant/40 text-[28px]">schedule</span>
            </div>
        </section>

        {{-- ============ FILTER ============ --}}
        <form method="GET" action="{{ route('products.index') }}" id="filterForm"
            class="flex flex-col gap-space-md mb-space-md">
            <div class="flex items-center justify-between flex-wrap gap-space-sm">
                <div class="relative flex-1 min-w-[260px] max-w-xl">
                    <span
                        class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                    <input name="search" value="{{ request('search') }}" oninput="debouncedSearch()"
                        class="w-full h-11 pl-11 pr-4 rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/60 font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-high transition-all shadow-inner"
                        placeholder="Cari nama barang, kode SKU, atau varian..." type="text" />
                </div>

                <div class="flex items-center gap-space-sm flex-wrap">
                    <input type="hidden" name="category" id="filterCategoryInput" value="{{ $catSlug }}">

                    <div class="flex items-center gap-space-xs bg-surface-container px-space-sm py-1.5 rounded-xl">
                        <span class="material-symbols-outlined text-on-surface-variant text-[18px]">filter_alt</span>
                        <select name="box_size"
                            class="bg-transparent text-on-surface font-label-md text-label-md focus:outline-none cursor-pointer">
                            <option class="bg-surface-container-high" value="all" @selected($boxSize === 'all')>Semua
                                Ukuran Box</option>
                            <option class="bg-surface-container-high" value="compact" @selected($boxSize === 'compact')>Box Kecil
                                (1 - 12 Pcs)</option>
                            <option class="bg-surface-container-high" value="medium" @selected($boxSize === 'medium')>Box Sedang
                                (13 - 39 Pcs)</option>
                            <option class="bg-surface-container-high" value="large" @selected($boxSize === 'large')>Box Besar
                                (40+ Pcs)</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-space-xs bg-surface-container px-space-sm py-1.5 rounded-xl">
                        <span class="material-symbols-outlined text-on-surface-variant text-[18px]">sort</span>
                        <select name="sort"
                            class="bg-transparent text-on-surface font-label-md text-label-md focus:outline-none cursor-pointer">
                            <option class="bg-surface-container-high" value="latest" @selected(request('sort', 'latest') === 'latest')>Urutkan:
                                Terbaru</option>
                            <option class="bg-surface-container-high" value="name" @selected(request('sort') === 'name')>Nama (A -
                                Z)</option>
                            <option class="bg-surface-container-high" value="ratio_desc" @selected(request('sort') === 'ratio_desc')>Isi
                                Terbanyak</option>
                            <option class="bg-surface-container-high" value="category" @selected(request('sort') === 'category')>Kategori
                            </option>
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

            {{-- Bar Filter Kategori Tunggal + Navigasi Geser & Lompat Cepat --}}
            <div class="flex min-w-0 items-center gap-1.5">
                <button type="button" onclick="scrollCategoryPills(-240)" title="Geser kategori ke kiri"
                    class="shrink-0 w-8 h-8 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface flex items-center justify-center border border-outline-variant/20 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                </button>

                <div id="categoryPills"
                    class="flex min-w-0 flex-1 items-center gap-space-xs overflow-x-auto py-1 scroll-smooth scrollbar-none">
                    <button type="submit" name="category" value="all"
                        class="category-pill shrink-0 px-space-md py-1.5 rounded-full text-label-md font-label-md flex items-center gap-1.5 transition-all cursor-pointer
                        {{ $catSlug === 'all'
                            ? 'active-pill bg-primary text-on-primary font-bold shadow-sm'
                            : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                        <span>Semua</span>
                        <span
                            class="{{ $catSlug === 'all' ? 'bg-on-primary/20 text-on-primary' : 'bg-surface-container-highest text-on-surface-variant' }} px-1.5 py-0.2 rounded-full text-label-sm">{{ $catalogTotal }}</span>
                    </button>
                    @foreach ($categories as $c)
                        <button type="submit" name="category" value="{{ $c->slug }}"
                            class="category-pill shrink-0 px-space-md py-1.5 rounded-full text-label-md font-label-md flex items-center gap-1.5 transition-all cursor-pointer
                            {{ $catSlug === $c->slug
                                ? 'active-pill bg-primary text-on-primary font-bold shadow-sm'
                                : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                            <span>{{ $c->name }}</span>
                            <span
                                class="{{ $catSlug === $c->slug ? 'bg-on-primary/20 text-on-primary' : 'bg-surface-container-highest text-on-surface-variant' }} px-1.5 py-0.2 rounded-full text-label-sm">{{ $c->products_count }}</span>
                        </button>
                    @endforeach
                </div>

                <button type="button" onclick="scrollCategoryPills(240)" title="Geser kategori ke kanan"
                    class="shrink-0 w-8 h-8 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface flex items-center justify-center border border-outline-variant/20 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                </button>

                <button type="button" id="btnOpenCategoryModal"
                    class="shrink-0 px-space-md py-1.5 rounded-full text-label-md font-label-md flex items-center gap-1 border border-dashed border-outline-variant hover:border-primary text-on-surface-variant hover:text-primary transition-all cursor-pointer ml-1">
                    <span class="material-symbols-outlined text-[16px]">tune</span>
                    <span>Kategori</span>
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
                            <th class="py-space-md pl-space-md pr-2 w-10 text-center">
                                <input type="checkbox" id="selectAllRows"
                                    class="w-4 h-4 rounded border-outline-variant bg-surface-container text-primary focus:ring-primary cursor-pointer" />
                            </th>
                            <th class="py-space-md px-3 font-semibold text-center">Kode SKU</th>
                            <th class="py-space-md px-3 font-semibold text-center">Nama Produk & Varian</th>
                            <th class="py-space-md px-3 font-semibold text-center">Kategori</th>
                            <th class="py-space-md px-3 font-semibold text-center">Isi per Box</th>
                            <th class="py-space-md px-3 font-semibold text-center">Status</th>
                            <th
                                class="sticky right-0 z-10 bg-surface-container py-space-md px-space-md font-semibold text-center shadow-[-6px_0_12px_-6px_rgba(0,0,0,0.35)]">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-highest/20 font-body-sm text-body-sm text-on-surface"
                        id="catalogTableBody">
                        @forelse($products as $product)
                            @include('products.partials.table-row', ['product' => $product])
                        @empty
                            <tr>
                                <td colspan="7" class="py-space-xl text-center text-on-surface-variant">
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
                <div
                    class="md:col-span-2 xl:col-span-4 py-space-xl text-center text-on-surface-variant bg-surface-container-low rounded-2xl">
                    Tidak ada produk yang cocok dengan filter.
                </div>
            @endforelse
        </div>

        {{-- ============ PAGINATION ============ --}}
        <footer class="mt-space-lg flex items-center justify-between flex-wrap gap-space-md">
            <div class="flex items-center gap-space-sm text-on-surface-variant font-label-md text-label-md">
                <span>Menampilkan <strong class="text-on-surface">{{ $products->firstItem() ?? 0 }} -
                        {{ $products->lastItem() ?? 0 }}</strong> dari <strong
                        class="text-on-surface">{{ $products->total() }}</strong> produk</span>
            </div>
            <div class="flex items-center gap-space-xs">{{ $products->links() }}</div>
        </footer>
    </div>

    {{-- ============ MODALS ============ --}}
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
                toast.className =
                    'fixed bottom-6 right-6 z-[100] flex max-w-[min(90vw,28rem)] items-center gap-2 rounded-xl px-space-lg py-space-md font-headline-sm text-body-md shadow-2xl ' +
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

            window.copySKU = async function(sku, btn) {
                try {
                    await navigator.clipboard.writeText(sku);
                    const icon = btn?.querySelector('.material-symbols-outlined');
                    if (icon) {
                        const prev = icon.textContent;
                        icon.textContent = 'check';
                        setTimeout(() => {
                            icon.textContent = prev;
                        }, 1200);
                    }
                    showToast(`Kode ${sku} disalin ke clipboard.`);
                } catch (e) {
                    showToast('Gagal menyalin kode SKU.', 'warning');
                }
            };

            window.scrollCategoryPills = function(offset) {
                const container = document.getElementById('categoryPills');
                if (container) container.scrollBy({
                    left: offset,
                    behavior: 'smooth'
                });
            };

            function centerActiveCategoryPill() {
                const container = document.getElementById('categoryPills');
                const active = container?.querySelector('.active-pill');
                if (active) {
                    active.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'center'
                    });
                }
            }
            centerActiveCategoryPill();

            /* ---------- Card Simulator ---------- */
            document.addEventListener('input', event => {
                if (!event.target.matches('.sim-input')) return;

                const ratio = parseInt(event.target.dataset.ratio, 10) || 1;
                const quantity = parseInt(event.target.value, 10) || 0;
                event.target.parentElement.querySelector('.sim-result').textContent = (quantity * ratio) +
                    ' Pcs';
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
            document.getElementById('btnOpenModal').addEventListener('click', () => {
                modal.classList.remove('hidden');
                syncSkuByCategory();
            });
            document.getElementById('btnCloseModal').addEventListener('click', () => modal.classList.add('hidden'));
            document.getElementById('btnCancelModal').addEventListener('click', () => modal.classList.add('hidden'));
            modal.addEventListener('click', e => {
                if (e.target === modal) modal.classList.add('hidden');
            });

            /* ---------- Modal Box-Pcs Stepper ---------- */
            const pcsInput = document.getElementById('inputModalPcs');
            const simOut = document.getElementById('modalSimOutput');
            const updateSim = () => simOut.textContent = (10 * (parseInt(pcsInput.value, 10) || 1)) + ' Pcs';
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

            /* ---------- Auto-Generate SKU Otomatis Saat Kategori Dipilih ---------- */
            const catSelect = document.getElementById('selectKategori');
            const skuPreview = document.getElementById('skuPreviewBadge');

            async function syncSkuByCategory() {
                if (!catSelect || !skuPreview || catSelect.selectedIndex < 0) return;
                const catSlug = catSelect.options[catSelect.selectedIndex].dataset.slug || 'gen';
                try {
                    const res = await fetch(
                        `{{ route('products.generate-sku') }}?category_slug=${encodeURIComponent(catSlug)}`);
                    if (!res.ok) return;
                    const json = await res.json();
                    skuPreview.textContent = json.sku;
                } catch (e) {}
            }

            catSelect?.addEventListener('change', syncSkuByCategory);

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
                            entry.className =
                                'flex flex-wrap items-center justify-between gap-2 py-2 border-b border-surface-container-highest/40 last:border-0';

                            const reference = document.createElement('div');
                            reference.className = 'min-w-0';

                            const title = document.createElement('strong');
                            title.className = 'block text-on-surface font-label-sm';
                            title.textContent = `${transaction.type} · ${transaction.reference}`;

                            const detail = document.createElement('span');
                            detail.className = 'block text-label-sm text-on-surface-variant';
                            detail.textContent = `${transaction.date} · ${transaction.box_qty} Box`;

                            const quantity = document.createElement('span');
                            quantity.className =
                                'text-label-sm text-primary font-semibold whitespace-nowrap';
                            quantity.textContent =
                                `${Number(transaction.total_pcs).toLocaleString('id-ID')} Pcs`;

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
                    editRatioInput.value = p.box_pcs_ratio;
                    editRatioInput.readOnly = p.has_transactions;
                    editRatioInput.setAttribute('aria-readonly', String(p.has_transactions));
                    updateEditSim();

                    const warnEl = document.getElementById('editTransactionWarning');
                    warnEl.classList.toggle('hidden', !p.has_transactions);

                    editModal.classList.remove('hidden');
                } catch (e) {
                    showToast('Gagal memuat data untuk diedit.', 'warning');
                }
            };

            /* ---------- Modal Kelola Kategori ---------- */
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
                confirmationIcon.className =
                    'material-symbols-outlined flex h-10 w-10 shrink-0 items-center justify-center rounded-full ' +
                    iconClasses.join(' ');
                confirmActionButton.className =
                    'h-10 rounded-xl px-space-lg font-label-md font-bold transition-colors cursor-pointer ' +
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

                const {
                    form,
                    submitter
                } = pendingConfirmation;
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
                    bulkBar.classList.toggle('hidden', checked.length === 0);
                    bulkBar.classList.toggle('flex', checked.length > 0);
                }
            }

            selectAll?.addEventListener('change', function() {
                document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
                syncBulkState();
            });

            document.addEventListener('change', e => {
                if (e.target.classList?.contains('row-checkbox')) syncBulkState();
            });

            /* ---------- Live AJAX Filter & Search Tanpa Reload Halaman ---------- */
            const filterForm = document.getElementById('filterForm');
            let searchTimer;

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
                        centerActiveCategoryPill();
                    }
                    if (newExportLink) document.getElementById('productExportLink').href = newExportLink.href;
                    if (newFooter && document.querySelector('footer')) document.querySelector('footer').innerHTML =
                        newFooter.innerHTML;

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
                        const hiddenCat = document.getElementById('filterCategoryInput');
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
