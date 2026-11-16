<header class="fixed top-0 left-0 lg:left-72 right-0 h-16 bg-surface/80 backdrop-blur-xl z-40 px-margin flex items-center justify-between gap-space-sm shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
    <button id="mobileNavToggle" type="button" aria-label="Buka menu navigasi" aria-expanded="false"
            class="lg:hidden w-9 h-9 shrink-0 rounded-xl bg-surface-container-low hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant">
        <span class="material-symbols-outlined text-[20px]">menu</span>
    </button>
    <div class="hidden sm:flex items-center gap-space-md flex-1 max-w-xl">
        <div class="relative w-full">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
            <input class="w-full h-10 pl-10 pr-space-lg rounded-xl bg-surface-container-low text-on-surface placeholder:text-on-surface-variant/60 font-body-sm text-body-sm focus:outline-none focus:bg-surface-container focus:ring-1 focus:ring-primary transition-all"
                   placeholder="Cari Cepat SKU / Produk / Supplier [Ctrl + K]..." type="text" />
        </div>
    </div>
    <div class="flex items-center gap-space-xs sm:gap-space-md">
        <div class="hidden md:flex items-center gap-space-xs px-space-sm py-space-xs rounded-full bg-surface-container-low">
            <div class="w-2 h-2 rounded-full bg-primary shadow-[0_0_8px_rgba(16,185,129,0.8)]"></div>
            <span class="font-label-md text-label-md text-on-surface">Semua Server Sinkron</span>
        </div>
        <button id="themeToggle"
                type="button"
                class="w-9 h-9 rounded-xl bg-surface-container-low hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors"
                title="Toggle Light/Dark Mode"
                aria-label="Toggle theme">
            <span id="themeIcon" class="material-symbols-outlined text-[20px]">dark_mode</span>
        </button>
        <button class="relative w-9 h-9 rounded-xl bg-surface-container-low hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant transition-colors">
            <span class="material-symbols-outlined text-[20px]">notifications</span>
            <span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full ring-2 ring-surface"></span>
        </button>
        <div class="hidden sm:block h-6 w-px bg-surface-container-highest"></div>
        <div class="flex items-center gap-space-sm cursor-pointer group">
            <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-on-primary text-[18px]">person</span>
            </div>
            <div class="flex flex-col text-left">
                <span class="font-headline-sm text-body-sm text-on-surface group-hover:text-primary transition-colors leading-tight">Admin Pusat</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Superuser</span>
            </div>
        </div>
    </div>
</header>