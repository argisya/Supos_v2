<div id="modalTambahBarang" class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-surface-container-lowest/80 backdrop-blur-md hidden transition-opacity">
    <div class="relative w-full max-w-2xl bg-surface-container-low rounded-2xl shadow-[0_25px_50px_-12px_rgba(0,0,0,0.7)] overflow-hidden flex flex-col border border-outline-variant/30">

        <div class="px-space-lg py-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest">
            <div class="flex items-center gap-space-sm">
                <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[22px]">add_box</span>
                </div>
                <div class="flex flex-col">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Standardisasi Barang Baru</h3>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Definisikan SKU & Rasio Konversi Satuan Dus</span>
                </div>
            </div>
            <button id="btnCloseModal" class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright flex items-center justify-center text-on-surface-variant hover:text-on-surface" type="button">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form id="formTambahBarang" action="{{ route('products.store') }}" method="POST" class="p-space-lg flex flex-col gap-space-md overflow-y-auto max-h-[768px]">
            @csrf

            @if ($errors->any())
                <div class="p-space-sm rounded-xl bg-error/10 border border-error/30 text-error font-body-sm text-body-sm">
                    <strong>Periksa kembali:</strong>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <div class="p-space-sm rounded-xl bg-tertiary-container/10 flex items-start gap-space-sm border border-tertiary/20">
                <span class="material-symbols-outlined text-tertiary text-[20px] shrink-0 mt-0.5">info</span>
                <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                    <strong class="text-tertiary">Pemberitahuan:</strong> Master Barang murni mendata nama, varian, dan rasio konversi [1 Box = X Pcs]. Tidak perlu input harga atau kuantitas.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                <div class="flex flex-col gap-1.5 md:col-span-2">
                    <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center justify-between">
                        <span>Nama Produk Baku <span class="text-error">*</span></span>
                        <span class="text-on-surface-variant font-normal">Contoh: Biskuit Roma Kelapa 300g</span>
                    </label>
                    <input name="name" value="{{ old('name') }}" required maxlength="255"
                           class="h-11 px-space-md rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/50 font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary"
                           placeholder="Ketik nama lengkap produk..." type="text" />
                </div>

                <div class="flex flex-col gap-1.5 md:col-span-2">
                    <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center justify-between">
                        <span>Varian / Deskripsi (opsional)</span>
                    </label>
                    <input name="variant" value="{{ old('variant') }}" maxlength="255"
                           class="h-11 px-space-md rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/50 font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary"
                           placeholder="Contoh: Refill 2 Liter - Karton Segel" type="text" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface font-semibold">Kategori Master <span class="text-error">*</span></label>
                    <select name="category_id" id="selectKategori" required
                            class="h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer">
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" data-slug="{{ $c->slug }}" @selected(old('category_id')==$c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center justify-between">
                        <span>Kode SKU Baku <span class="text-error">*</span></span>
                        <button type="button" id="btnGenSKU" class="text-primary text-[11px] hover:underline flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-[13px]">autorenew</span> Auto-generate
                        </button>
                    </label>
                    <div class="relative">
                        <input name="sku" id="inputSKU" value="{{ old('sku', 'SKU-SMB-09') }}" required maxlength="50"
                               class="w-full h-11 pl-space-md pr-10 rounded-xl bg-surface-container text-primary font-label-md text-label-md font-bold focus:outline-none focus:ring-1 focus:ring-primary uppercase" type="text" />
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">vpn_key</span>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5 md:col-span-2">
                    <label class="font-label-md text-label-md text-on-surface font-semibold">Barcode (opsional)</label>
                    <input name="barcode" value="{{ old('barcode') }}" maxlength="50"
                           class="h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary"
                           placeholder="899xxxxxxxxxx" type="text" />
                </div>
            </div>

            {{-- Box Ratio --}}
            <div class="p-space-md rounded-2xl bg-surface-container/60 border border-primary/20 flex flex-col gap-space-sm mt-space-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-space-xs">
                        <span class="material-symbols-outlined text-primary text-[20px]">package_2</span>
                        <span class="font-headline-sm text-body-md text-on-surface font-bold">Standardisasi Rasio Kemasan</span>
                    </div>
                    <span class="font-label-sm text-label-sm text-primary uppercase font-bold tracking-wider">Formula Baku</span>
                </div>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Tentukan jumlah pasti unit terkecil (PCS / Sachet / Botol) di dalam 1 Dus utuh:</p>

                <div class="flex items-center gap-space-md flex-wrap mt-1">
                    <div class="flex items-center gap-space-xs bg-surface-container-lowest p-1.5 rounded-xl border border-surface-container-highest">
                        <span class="px-space-sm py-1 font-label-md text-label-md font-bold text-secondary bg-secondary-container/40 rounded-lg">1 BOX =</span>
                        <div class="flex items-center">
                            <button type="button" id="btnDecPcs" class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-bold flex items-center justify-center">-</button>
                            <input name="box_pcs_ratio" id="inputModalPcs" value="{{ old('box_pcs_ratio', 24) }}" min="1" required
                                   class="w-16 h-8 bg-transparent text-center font-display-lg text-headline-sm text-primary font-extrabold focus:outline-none" type="number" />
                            <button type="button" id="btnIncPcs" class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-bold flex items-center justify-center">+</button>
                        </div>
                        <span class="px-space-sm py-1 font-label-md text-label-md font-bold text-primary">PCS</span>
                    </div>

                    <div class="flex items-center gap-1 flex-wrap">
                        <span class="font-label-sm text-label-sm text-on-surface-variant mr-1">Preset:</span>
                        @foreach([6, 12, 24, 40, 120] as $preset)
                            <button type="button" class="preset-btn px-2.5 py-1 rounded-lg {{ $preset==24 ? 'bg-surface-container-highest text-primary font-bold' : 'bg-surface-container hover:bg-surface-container-high text-on-surface' }} font-label-sm text-label-sm" data-val="{{ $preset }}">{{ $preset }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="mt-space-sm p-space-sm rounded-xl bg-surface-container-lowest flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-space-xs text-on-surface-variant font-body-sm text-body-sm">
                        <span class="material-symbols-outlined text-[16px] text-tertiary">calculate</span>
                        <span>Uji Simulasi: Jika staf input <strong class="text-on-surface">10 Box</strong> →</span>
                    </div>
                    <div id="modalSimOutput" class="font-label-md text-label-md text-primary font-bold bg-primary/10 px-3 py-1 rounded-lg">
                        Otomatis Dihitung: {{ old('box_pcs_ratio', 24) * 10 }} Pcs
                    </div>
                </div>
            </div>

            <div class="pt-space-md flex items-center justify-end gap-space-sm border-t border-surface-container-highest">
                <button type="button" id="btnCancelModal" class="h-11 px-space-lg rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-headline-sm text-body-md transition-colors">Batalkan</button>
                <button type="submit" class="h-11 px-space-xl rounded-xl bg-linear-to-r from-primary-container to-primary text-on-primary-container font-headline-sm text-headline-sm font-bold shadow-[0_4px_20px_rgba(16,185,129,0.35)] hover:shadow-[0_4px_28px_rgba(16,185,129,0.5)] transition-all">
                    Simpan Standar ke Katalog
                </button>
            </div>
        </form>
    </div>
</div>