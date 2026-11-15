<div id="modalActionConfirmation"
     class="fixed inset-0 z-[60] hidden items-center justify-center p-4 bg-surface-container-lowest/80 backdrop-blur-md"
     role="alertdialog" aria-modal="true" aria-labelledby="actionConfirmationTitle"
     aria-describedby="actionConfirmationMessage">
    <div class="w-full max-w-md overflow-hidden rounded-2xl border border-outline-variant/30 bg-surface-container-low shadow-2xl">
        <div class="flex items-center gap-3 border-b border-surface-container-highest bg-surface-container px-space-lg py-space-md">
            <span id="actionConfirmationIcon"
                  class="material-symbols-outlined flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-error/15 text-error">warning</span>
            <h3 id="actionConfirmationTitle" class="font-headline-sm text-headline-sm font-bold text-on-surface">Konfirmasi tindakan</h3>
        </div>
        <div class="p-space-lg">
            <p id="actionConfirmationMessage" class="text-body-sm leading-relaxed text-on-surface-variant"></p>
        </div>
        <div class="flex justify-end gap-space-sm border-t border-surface-container-highest bg-surface-container px-space-lg py-space-md">
            <button id="btnCancelActionConfirmation" type="button"
                    class="h-10 rounded-xl bg-surface-container-high px-space-lg font-label-md text-on-surface-variant transition-colors hover:text-on-surface cursor-pointer">
                Batal
            </button>
            <button id="btnConfirmActionConfirmation" type="button"
                    class="h-10 rounded-xl bg-error px-space-lg font-label-md font-bold text-on-error transition-colors hover:bg-error/90 cursor-pointer">
                Lanjutkan
            </button>
        </div>
    </div>
</div>
