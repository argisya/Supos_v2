@extends('layouts.app')

@section('title', 'Master Barang')

@section('content')
<div class="flex flex-col w-full pb-16">

    {{-- Ambient lights --}}
    <div class="relative w-full">
        <div class="absolute -top-10 right-20 w-96 h-96 bg-secondary-container/15 rounded-full blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute top-48 left-10 w-80 h-80 bg-primary/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
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
            <div class="flex items-center gap-space-xs bg-surface-container-high px-space-md py-1 rounded-full border border-outline-variant/30">
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                <span class="font-label-md text-label-md text-on-surface font-semibold tracking-wide">
                    {{ $stats['total_sku'] }} Item Aktif Terdaftar
                </span>
            </div>
        </div>

        <div class="flex items-start justify-between flex-wrap gap-space-lg mt-space-xs">
            <div class="flex flex-col max-w-2xl">
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight flex items-center gap-space-sm">
                    Master Barang & Standardisasi Box
                    <span class="bg-primary/10 text-primary text-label-sm font-label-sm px-space-xs py-0.5 rounded border border-primary/20">RULE ENGINE</span>
                </h1>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1 leading-relaxed">
                    Kamus referensi konversi baku <span class="font-label-md text-on-surface text-label-sm px-1.5 py-0.5 bg-surface-container-highest rounded">1 BOX = X PCS</span>. Seluruh modul inventori, kasir, dan gudang mengacu pada master data ini tanpa perlu kalkulasi manual staf.
                </p>
            </div>
            <div class="flex items-center gap-space-sm flex-wrap">
                <button class="h-10 px-space-md rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface font-body-sm text-body-sm flex items-center gap-space-xs transition-colors shadow-sm" type="button">
                    <span class="material-symbols-outlined text-[18px] text-tertiary">download</span>
                    <span>Export (CSV/Excel)</span>
                </button>
                <button class="h-10 px-space-md rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface font-body-sm text-body-sm flex items-center gap-space-xs transition-colors shadow-sm" type="button">
                    <span class="material-symbols-outlined text-[18px] text-secondary">qr_code_scanner</span>
                    <span>Scan Barcode SKU</span>
                </button>
                <button id="btnOpenModal"
                        class="h-10 px-space-lg rounded-xl bg-linear-to-r from-secondary-container via-primary-container to-primary text-on-primary-container font-headline-sm text-headline-sm flex items-center gap-space-xs shadow-[0_8px_24px_rgba(16,185,129,0.35)] hover:shadow-[0_8px_32px_rgba(16,185,129,0.5)] active:scale-95 transition-all" type="button">
                    <span class="material-symbols-outlined text-[20px]">add_circle</span>
                    <span>+ Tambah Barang Baru</span>
                </button>
            </div>
        </div>
    </header>

    {{-- ============ BANNER INFO ============ --}}
    <section class="mb-space-lg rounded-xl bg-surface-container-low/70 backdrop-blur-xl p-space-lg shadow-md">
        <div class="flex flex-col lg:flex-row items-stretch justify-between gap-space-lg">
            <div class="flex items-start gap-space-md flex-1">
                <div class="w-12 h-12 rounded-xl bg-tertiary/15 flex items-center justify-center shrink-0 text-tertiary shadow-[0_0_16px_rgba(76,215,246,0.2)]">
                    <span class="material-symbols-outlined text-[26px]">verified</span>
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center gap-space-xs">
                        <span class="font-headline-sm text-headline-sm text-on-surface">Pemisahan Domain: Master Spesifikasi Murni</span>
                        <span class="font-label-sm text-label-sm text-tertiary uppercase tracking-wider bg-tertiary/10 px-2 py-0.5 rounded-full">Kaidah Akuntansi</span>
                    </div>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 leading-relaxed">
                        Halaman ini <strong class="text-on-surface font-semibold">TIDAK</strong> mengikat saldo modal ataupun kuantitas fisik gudang. Harga modal dicatat pada Barang Masuk, harga jual di Barang Keluar, dan saldo fisik di Stok Barang.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-space-sm shrink-0 lg:w-5/12">
                <div class="bg-surface-container p-space-sm rounded-xl flex flex-col justify-between">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Total SKU Aktif</span>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="font-display-lg text-headline-lg text-primary leading-none">{{ $stats['total_sku'] }}</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">Produk</span>
                    </div>
                </div>
                <div class="bg-surface-container p-space-sm rounded-xl flex flex-col justify-between">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Rata2 Konversi</span>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="font-display-lg text-headline-lg text-tertiary leading-none">{{ $stats['avg_ratio'] }}</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">Pcs/Box</span>
                    </div>
                </div>
                <div class="bg-surface-container p-space-sm rounded-xl flex flex-col justify-between">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Kategori Master</span>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="font-display-lg text-headline-lg text-secondary leading-none">{{ $stats['total_categories'] }}</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">Grup</span>
                    </div>
                </div>
                <div class="bg-surface-container p-space-sm rounded-xl flex flex-col justify-between">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Sinkronisasi</span>
                    <div class="flex flex-col mt-1">
                        <span class="font-label-md text-label-md text-on-surface font-semibold leading-tight">Hari ini</span>
                        <span class="font-label-sm text-label-sm text-primary">{{ $stats['last_sync'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ FILTER ============ --}}
    <form method="GET" action="{{ route('products.index') }}" id="filterForm" class="flex flex-col gap-space-md mb-space-md">
        <div class="flex items-center justify-between flex-wrap gap-space-sm">
            <div class="relative flex-1 min-w-[320px] max-w-xl">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                <input name="search" value="{{ request('search') }}"
                       oninput="debouncedSearch()"
                       class="w-full h-11 pl-11 pr-10 rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/60 font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-high transition-all shadow-inner"
                       placeholder="Cari nama barang, kode SKU, atau barcode..." type="text" />
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant/70 text-[18px]">barcode_scanner</span>
            </div>

            <div class="flex items-center gap-space-sm flex-wrap">
                {{-- Hidden fields untuk filter lain --}}
                <input type="hidden" name="category" value="{{ $catSlug }}">

                <div class="flex items-center gap-space-xs bg-surface-container px-space-sm py-1.5 rounded-xl">
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px]">filter_alt</span>
                    <select name="box_size" onchange="this.form.submit()"
                            class="bg-transparent text-on-surface font-label-md text-label-md focus:outline-none cursor-pointer">
                        <option class="bg-surface-container-high" value="all"     @selected($boxSize==='all')>Semua Ukuran Box</option>
                        <option class="bg-surface-container-high" value="compact" @selected($boxSize==='compact')>Box Kecil (1 - 12 Pcs)</option>
                        <option class="bg-surface-container-high" value="medium"  @selected($boxSize==='medium')>Box Sedang (13 - 39 Pcs)</option>
                        <option class="bg-surface-container-high" value="large"   @selected($boxSize==='large')>Box Besar (40+ Pcs)</option>
                    </select>
                </div>

                <div class="flex items-center gap-space-xs bg-surface-container px-space-sm py-1.5 rounded-xl">
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px]">sort</span>
                    <select name="sort" onchange="this.form.submit()"
                            class="bg-transparent text-on-surface font-label-md text-label-md focus:outline-none cursor-pointer">
                        <option class="bg-surface-container-high" value="latest"     @selected(request('sort','latest')==='latest')>Urutkan: SKU Terbaru</option>
                        <option class="bg-surface-container-high" value="name"       @selected(request('sort')==='name')>Nama Produk (A - Z)</option>
                        <option class="bg-surface-container-high" value="ratio_desc" @selected(request('sort')==='ratio_desc')>Isi Terbanyak (Pcs)</option>
                        <option class="bg-surface-container-high" value="category"   @selected(request('sort')==='category')>Kategori Produk</option>
                    </select>
                </div>

                <div class="flex items-center bg-surface-container p-1 rounded-xl">
                    <button type="button" id="btnViewTable"
                            class="px-space-sm py-1 rounded-lg bg-surface-container-high text-primary font-label-md text-label-md flex items-center gap-1 shadow-sm transition-all">
                        <span class="material-symbols-outlined text-[18px]">table_rows</span>
                        <span class="hidden sm:inline">Tabel</span>
                    </button>
                    <button type="button" id="btnViewCard"
                            class="px-space-sm py-1 rounded-lg text-on-surface-variant hover:text-on-surface font-label-md text-label-md flex items-center gap-1 transition-all">
                        <span class="material-symbols-outlined text-[18px]">grid_view</span>
                        <span class="hidden sm:inline">Kartu</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Kategori Pills --}}
        <div class="flex items-center gap-space-xs overflow-x-auto pb-1 scrollbar-none">
            <button type="submit" name="category" value="all"
                    class="category-pill shrink-0 px-space-md py-1.5 rounded-full text-label-md font-label-md flex items-center gap-1.5 transition-all
                    {{ $catSlug === 'all'
                        ? 'bg-primary text-on-primary font-bold shadow-[0_0_12px_rgba(78,222,163,0.35)]'
                        : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                <span>Semua</span>
                <span class="bg-on-primary/20 text-on-primary px-1.5 py-0.2 rounded-full text-label-sm">{{ $stats['total_sku'] }}</span>
            </button>
            @foreach($categories as $c)
                <button type="submit" name="category" value="{{ $c->slug }}"
                        class="category-pill shrink-0 px-space-md py-1.5 rounded-full text-label-md font-label-md flex items-center gap-1.5 transition-all
                        {{ $catSlug === $c->slug
                            ? 'bg-primary text-on-primary font-bold shadow-[0_0_12px_rgba(78,222,163,0.35)]'
                            : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                    <span>{{ $c->name }}</span>
                    <span class="bg-surface-container-highest text-on-surface-variant px-1.5 py-0.2 rounded-full text-label-sm">{{ $c->products_count }}</span>
                </button>
            @endforeach
        </div>
    </form>

    {{-- ============ TABLE VIEW ============ --}}
    <div id="tableViewContainer" class="w-full rounded-2xl bg-surface-container-low shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container font-label-md text-label-md text-on-surface-variant uppercase tracking-wider select-none">
                        <th class="py-space-md px-space-lg font-semibold">SKU & Barcode</th>
                        <th class="py-space-md px-space-md font-semibold">Nama Produk & Varian</th>
                        <th class="py-space-md px-space-md font-semibold">Kategori</th>
                        <th class="py-space-md px-space-md font-semibold text-center">Aturan Baku Kemasan</th>
                        <th class="py-space-md px-space-md font-semibold">Simulator Cepat</th>
                        <th class="py-space-md px-space-md font-semibold">Status</th>
                        <th class="py-space-md px-space-lg font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-highest/20 font-body-sm text-body-sm text-on-surface" id="catalogTableBody">
                    @forelse($products as $product)
                        @include('products.partials.table-row', ['product' => $product])
                    @empty
                        <tr><td colspan="7" class="py-space-xl text-center text-on-surface-variant">
                            Tidak ada produk yang cocok dengan filter.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============ CARD VIEW ============ --}}
    <div id="cardViewContainer" class="hidden grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-space-md">
        @foreach($products as $product)
            @include('products.partials.card-item', ['product' => $product])
        @endforeach
    </div>

    {{-- ============ PAGINATION ============ --}}
    <footer class="mt-space-lg flex items-center justify-between flex-wrap gap-space-md">
        <div class="flex items-center gap-space-sm text-on-surface-variant font-label-md text-label-md">
            <span>Menampilkan <strong class="text-on-surface">{{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}</strong> dari total <strong class="text-on-surface">{{ $products->total() }}</strong> Master Produk</span>
            <span class="text-outline-variant">•</span>
            <span class="flex items-center gap-1 text-primary">
                <span class="material-symbols-outlined text-[14px]">bolt</span>
                Auto-Sync Transaksi Aktif
            </span>
        </div>
        <div class="flex items-center gap-space-xs">{{ $products->links() }}</div>
    </footer>
</div>

{{-- ============ MODAL TAMBAH BARANG ============ --}}
@include('products.partials.modal-create')
@endsection

@push('scripts')
<script>
(function () {
    /* ---------- Row Simulator ---------- */
    document.querySelectorAll('.sim-input').forEach(input => {
        input.addEventListener('input', function () {
            const ratio = parseInt(this.dataset.ratio, 10) || 1;
            const val   = parseInt(this.value, 10) || 0;
            this.parentElement.querySelector('.sim-result').textContent = (val * ratio) + ' Pcs';
        });
    });

    /* ---------- View Switcher ---------- */
    const btnT = document.getElementById('btnViewTable');
    const btnC = document.getElementById('btnViewCard');
    const tV   = document.getElementById('tableViewContainer');
    const cV   = document.getElementById('cardViewContainer');
    btnT.addEventListener('click', () => {
        btnT.classList.add('bg-surface-container-high','text-primary'); btnT.classList.remove('text-on-surface-variant');
        btnC.classList.remove('bg-surface-container-high','text-primary'); btnC.classList.add('text-on-surface-variant');
        tV.classList.remove('hidden'); cV.classList.add('hidden');
    });
    btnC.addEventListener('click', () => {
        btnC.classList.add('bg-surface-container-high','text-primary'); btnC.classList.remove('text-on-surface-variant');
        btnT.classList.remove('bg-surface-container-high','text-primary'); btnT.classList.add('text-on-surface-variant');
        tV.classList.add('hidden'); cV.classList.remove('hidden');
    });

    /* ---------- Modal Open/Close ---------- */
    const modal = document.getElementById('modalTambahBarang');
    document.getElementById('btnOpenModal').addEventListener('click', () => modal.classList.remove('hidden'));
    document.getElementById('btnCloseModal').addEventListener('click', () => modal.classList.add('hidden'));
    document.getElementById('btnCancelModal').addEventListener('click', () => modal.classList.add('hidden'));
    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });

    /* ---------- Modal Box-Pcs Stepper ---------- */
    const pcsInput = document.getElementById('inputModalPcs');
    const simOut   = document.getElementById('modalSimOutput');
    const updateSim = () => simOut.textContent = 'Otomatis Dihitung: ' + (10 * (parseInt(pcsInput.value, 10) || 1)) + ' Pcs';
    document.getElementById('btnIncPcs').addEventListener('click', () => { pcsInput.value = (parseInt(pcsInput.value,10)||0)+1; updateSim(); });
    document.getElementById('btnDecPcs').addEventListener('click', () => { const c = parseInt(pcsInput.value,10)||1; if (c > 1) pcsInput.value = c-1; updateSim(); });
    pcsInput.addEventListener('input', updateSim);

    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.preset-btn').forEach(b => {
                b.classList.remove('bg-surface-container-highest','text-primary','font-bold');
                b.classList.add('bg-surface-container','text-on-surface');
            });
            this.classList.remove('bg-surface-container','text-on-surface');
            this.classList.add('bg-surface-container-highest','text-primary','font-bold');
            pcsInput.value = this.dataset.val; updateSim();
        });
    });

    /* ---------- Auto-Generate SKU (server-side) ---------- */
    document.getElementById('btnGenSKU').addEventListener('click', async function () {
        const catSelect = document.getElementById('selectKategori');
        const catSlug   = catSelect.options[catSelect.selectedIndex].dataset.slug || 'gen';
        const res = await fetch(`{{ route('products.generate-sku') }}?category_slug=${encodeURIComponent(catSlug)}`);
        const json = await res.json();
        document.getElementById('inputSKU').value = json.sku;
    });

    /* ---------- Debounced Search ---------- */
    let searchTimer;
    window.debouncedSearch = () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 400);
    };
})();
</script>
@endpush