{{-- Wadah toast global. Dipasang sekali di layout; panggil dari JS: WS.toast('Pesan', 'success' | 'error' | 'info'). --}}
<div class="ws-toasts ws-font" x-data aria-live="polite">
    <template x-for="t in $store.wsToast.items" :key="t.id">
        <div class="ws-toast"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">
            <i class="fas mt-0.5"
               :class="{
                   'fa-circle-check text-emerald-500': t.type === 'success',
                   'fa-circle-exclamation text-red-500': t.type === 'error',
                   'fa-spinner fa-spin text-indigo-500': t.type === 'loading',
                   'fa-circle-info text-indigo-500': t.type !== 'success' && t.type !== 'error' && t.type !== 'loading'
               }"></i>
            <p class="flex-1 text-gray-700" x-text="t.message"></p>
            <button type="button" class="ws-text-faint hover:text-slate-600" x-on:click="$store.wsToast.dismiss(t.id)" aria-label="Tutup">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
    </template>
</div>
