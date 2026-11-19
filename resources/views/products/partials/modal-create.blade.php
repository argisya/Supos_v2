<div id="modalTambahBarang"
    class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-space-md bg-surface-container-lowest/80 backdrop-blur-md {{ $errors->any() ? '' : 'hidden' }} transition-opacity">
    <div
        class="relative w-full max-w-2xl max-h-[92vh] bg-surface-container-low rounded-2xl shadow-[0_25px_50px_-12px_rgba(0,0,0,0.7)] overflow-hidden flex flex-col border border-outline-variant/30">

        {{-- Header Modal --}}
        <div
            class="px-space-lg py-space-md bg-surface-container flex items-center justify-between border-b border-surface-container-highest shrink-0">
            <div class="flex items-center gap-space-sm">
                <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[22px]">add_box</span>
                </div>
                <div class="flex flex-col">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Tambah Barang Baru</h3>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Tentukan kategori, nama produk, dan
                        isi per box</span>
                </div>
            </div>
            <button id="btnCloseModal"
                class="w-8 h-8 rounded-lg bg-surface-container-high hover:bg-surface-bright flex items-center justify-center text-on-surface-variant hover:text-on-surface cursor-pointer"
                type="button">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        {{-- Body Form --}}
        <form id="formTambahBarang" action="{{ route('products.store') }}" method="POST"
            class="flex flex-col flex-1 min-h-0">
            @csrf

            <div class="p-space-lg flex flex-col gap-space-md overflow-y-auto flex-1">
                @if ($errors->any())
                    <div
                        class="p-space-sm rounded-xl bg-error/10 border border-error/30 text-error font-body-sm text-body-sm">
                        <strong>Periksa kembali input berikut:</strong>
                        <ul class="mt-1 list-disc list-inside">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="font-label-md text-label-md text-on-surface font-semibold">
                            Nama Produk <span class="text-error">*</span>
                        </label>
                        <input name="name" value="{{ old('name') }}" required maxlength="255"
                            class="h-11 px-space-md rounded-xl bg-surface-container border {{ $errors->has('name') ? 'border-error' : 'border-transparent' }} text-on-surface placeholder:text-on-surface-variant/50 font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary"
                            placeholder="Contoh: Biskuit Roma Kelapa 300g" type="text" />
                        @error('name')
                            <span class="text-error text-label-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="font-label-md text-label-md text-on-surface font-semibold">
                            Varian / Keterangan Kemasan <span
                                class="text-on-surface-variant font-normal">(opsional)</span>
                        </label>
                        <input name="variant" value="{{ old('variant') }}" maxlength="255"
                            class="h-11 px-space-md rounded-xl bg-surface-container text-on-surface placeholder:text-on-surface-variant/50 font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary"
                            placeholder="Contoh: Refill 2 Liter - Karton Segel" type="text" />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-md text-label-md text-on-surface font-semibold">Kategori <span
                                class="text-error">*</span></label>
                        <select name="category_id" id="selectKategori" required
                            class="h-11 px-space-md rounded-xl bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer">
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}" data-slug="{{ $c->slug }}"
                                    @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <span class="text-error text-label-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Pratinjau Kode SKU Otomatis (Bukan Input Ketik) --}}
                    <div class="flex flex-col gap-1.5">
                        <span class="font-label-md text-label-md text-on-surface font-semibold">Kode SKU
                            (Otomatis)</span>
                        <div
                            class="h-11 px-space-md rounded-xl bg-surface-container-highest/50 border border-outline-variant/25 flex items-center justify-between">
                            <span id="skuPreviewBadge"
                                class="font-label-md text-body-md font-bold text-primary tracking-wide">
                                {{ $nextSku ?? 'SKU-SMB-01' }}
                            </span>
                            <span class="text-[11px] text-on-surface-variant">Otomatis dari sistem</span>
                        </div>
                    </div>
                </div>

                {{-- Isi Per Box --}}
                <div
                    class="p-space-md rounded-2xl bg-surface-container/60 border border-primary/20 flex flex-col gap-space-sm mt-space-xs">
                    <div class="flex items-center gap-space-xs">
                        <span class="material-symbols-outlined text-primary text-[20px]">package_2</span>
                        <span class="font-headline-sm text-body-md text-on-surface font-bold">Isi dalam 1 Box</span>
                    </div>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Masukkan jumlah satuan terkecil (Pcs /
                        Bungkus / Botol) dalam 1 box utuh:</p>

                    <div class="flex items-center gap-space-md flex-wrap mt-1">
                        <div
                            class="flex items-center gap-space-xs bg-surface-container-lowest p-1.5 rounded-xl border border-surface-container-highest">
                            <span
                                class="px-space-sm py-1 font-label-md text-label-md font-bold text-secondary bg-secondary-container/40 rounded-lg">1
                                BOX =</span>
                            <div class="flex items-center">
                                <button type="button" id="btnDecPcs"
                                    class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-bold flex items-center justify-center cursor-pointer">-</button>
                                <input name="box_pcs_ratio" id="inputModalPcs" value="{{ old('box_pcs_ratio', 24) }}"
                                    min="1" required
                                    class="w-16 h-8 bg-transparent text-center font-display-lg text-headline-sm text-primary font-extrabold focus:outline-none"
                                    type="number" />
                                <button type="button" id="btnIncPcs"
                                    class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-bold flex items-center justify-center cursor-pointer">+</button>
                            </div>
                            <span class="px-space-sm py-1 font-label-md text-label-md font-bold text-primary">PCS</span>
                        </div>

                        <div class="flex items-center gap-1 flex-wrap">
                            <span class="font-label-sm text-label-sm text-on-surface-variant mr-1">Pilihan Cepat:</span>
                            @foreach ([6, 12, 24, 40, 120] as $preset)
                                <button type="button"
                                    class="preset-btn px-2.5 py-1 rounded-lg {{ old('box_pcs_ratio', 24) == $preset ? 'bg-surface-container-highest text-primary font-bold' : 'bg-surface-container hover:bg-surface-container-high text-on-surface' }} font-label-sm text-label-sm cursor-pointer"
                                    data-val="{{ $preset }}">{{ $preset }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div
                        class="mt-space-sm p-space-sm rounded-xl bg-surface-container-lowest flex items-center justify-between flex-wrap gap-2">
                        <span class="text-on-surface-variant font-body-sm text-body-sm">Contoh: <strong>10 Box</strong>
                            =</span>
                        <span id="modalSimOutput"
                            class="font-label-md text-label-md text-primary font-bold bg-primary/10 px-3 py-1 rounded-lg">
                            {{ old('box_pcs_ratio', 24) * 10 }} Pcs
                        </span>
                    </div>
                </div>
            </div>

            {{-- Footer Modal --}}
            <div
                class="px-space-lg py-space-md bg-surface-container flex items-center justify-end gap-space-sm border-t border-surface-container-highest shrink-0">
                <button type="button" id="btnCancelModal"
                    class="h-10 px-space-lg rounded-xl bg-surface-container-high hover:bg-surface-bright text-on-surface font-headline-sm text-body-md transition-colors cursor-pointer">Batal</button>
                <button type="submit"
                    class="h-10 px-space-xl rounded-xl bg-primary hover:bg-primary/90 text-on-primary font-headline-sm text-headline-sm font-bold shadow-md hover:shadow-lg transition-all cursor-pointer">
                    Simpan Barang
                </button>
            </div>
        </form>
    </div>
</div>