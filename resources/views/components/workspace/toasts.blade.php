{{-- Wadah toast global. Dipasang sekali di layout; panggil dari JS: WS.toast('Pesan', 'success' | 'error' | 'info'). --}}
{{-- Toast notifikasi (type 'notif') berupa kartu yang bisa diklik; dibuat oleh store wsNotif saat ada notifikasi baru. --}}
<div class="ws-toasts ws-font" x-data aria-live="polite">
    <template x-for="t in $store.wsToast.items" :key="t.id">
        <div class="ws-toast"
             :class="{ 'ws-toast--notif': t.type === 'notif' }"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">
            <template x-if="t.type === 'notif'">
                <div class="ws-toast__notif" role="link" tabindex="0"
                     x-on:click="$store.wsToast.dismiss(t.id); $store.wsNotif.open(t.notif)"
                     x-on:keydown.enter="$store.wsToast.dismiss(t.id); $store.wsNotif.open(t.notif)">
                    <span class="ws-toast__ico" :style="{ background: t.meta.bg, color: t.meta.color }">
                        <i class="fas" :class="t.meta.icon"></i>
                    </span>
                    <span class="ws-toast__txt">
                        <span class="ws-toast__eyebrow" :style="{ color: t.meta.color }" x-text="t.meta.label"></span>
                        <span class="ws-toast__title" x-text="$store.wsNotif.title(t.notif)"></span>
                        <span class="ws-toast__body" x-show="t.notif.body" x-text="t.notif.body"></span>
                    </span>
                    <button type="button" class="ws-text-faint hover:text-slate-600" x-on:click.stop="$store.wsToast.dismiss(t.id)" aria-label="Tutup">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            </template>
            <template x-if="t.type !== 'notif'">
                <div class="ws-toast__plain">
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
    </template>
</div>
