{{-- Lonceng notifikasi. Data dari Alpine.store('wsNotif') di public/js/workspace/notifications.js. --}}
<div x-data="wsNotificationBell"
     {{ $attributes->class(['relative']) }}
     x-on:click.outside="close()"
     x-on:keydown.escape.window="escape()">
    <button type="button" class="ws-icon-btn" x-ref="trigger" x-on:click="toggle()" :aria-expanded="isOpen.toString()" aria-haspopup="true" aria-label="Notifikasi">
        <i class="fas fa-bell text-lg"></i>
        <span x-cloak x-show="$store.wsNotif.unread > 0" class="ws-count-badge" x-text="$store.wsNotif.badgeText()"></span>
    </button>

    <div x-cloak x-show="isOpen" x-ref="panel"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="ws-dropdown ws-font">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <h2 class="text-sm font-bold text-gray-800">Notifikasi</h2>
                <span x-show="$store.wsNotif.unread > 0" class="text-[11px] font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full"
                      x-text="$store.wsNotif.unread + ' baru'"></span>
            </div>
            <button type="button" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 disabled:text-slate-300 disabled:cursor-not-allowed"
                    x-on:click="$store.wsNotif.markAllRead()" :disabled="$store.wsNotif.unread === 0">
                Tandai semua dibaca
            </button>
        </div>

        <div class="max-h-[420px] overflow-y-auto">
            {{-- Memuat --}}
            <template x-if="!$store.wsNotif.loaded && !$store.wsNotif.error">
                <div class="p-4 space-y-3" aria-busy="true">
                    <template x-for="i in 3" :key="i">
                        <div class="flex gap-3 animate-pulse">
                            <div class="w-9 h-9 rounded-xl bg-slate-100"></div>
                            <div class="flex-1 space-y-2 py-1">
                                <div class="h-3 bg-slate-100 rounded w-3/4"></div>
                                <div class="h-3 bg-slate-100 rounded w-1/2"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Gagal --}}
            <template x-if="$store.wsNotif.error && !$store.wsNotif.loaded">
                <div class="px-4 py-8 text-center text-sm text-slate-500">
                    <i class="fas fa-plug-circle-exclamation text-xl text-slate-300 mb-2"></i>
                    <p>Notifikasi gagal dimuat.</p>
                    <button type="button" class="mt-2 text-xs font-semibold text-indigo-600" x-on:click="$store.wsNotif.refresh().catch(() => {})">Coba lagi</button>
                </div>
            </template>

            {{-- Kosong --}}
            <template x-if="$store.wsNotif.loaded && $store.wsNotif.items.length === 0">
                <div class="px-4 py-10 text-center text-sm text-slate-500">
                    <i class="fas fa-bell-slash text-xl text-slate-300 mb-2"></i>
                    <p>Belum ada notifikasi.</p>
                </div>
            </template>

            {{-- Daftar --}}
            <template x-for="item in $store.wsNotif.items" :key="item.id">
                <button type="button"
                        class="w-full flex items-start gap-3 px-4 py-3 text-left border-b border-slate-50 hover:bg-slate-50 transition-colors"
                        :class="{ 'bg-indigo-50/40': !item.read_at }"
                        x-on:click="$store.wsNotif.open(item)">
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                          :style="{ background: $store.wsNotif.meta(item.type).bg, color: $store.wsNotif.meta(item.type).color }">
                        <i class="fas" :class="$store.wsNotif.meta(item.type).icon"></i>
                    </span>
                    <span class="flex-1 min-w-0">
                        <span class="block text-[11px] font-bold uppercase tracking-wide" :style="{ color: $store.wsNotif.meta(item.type).color }" x-text="$store.wsNotif.meta(item.type).label"></span>
                        <span class="block text-sm text-gray-800 line-clamp-2" :class="item.read_at ? 'font-medium' : 'font-bold'" x-text="$store.wsNotif.title(item)"></span>
                        <span x-show="item.body" class="block text-xs text-slate-500 mt-0.5 line-clamp-2" x-text="item.body"></span>
                        <span class="block text-[11px] ws-text-faint mt-1" x-text="relative(item.created_at)"></span>
                    </span>
                    <span x-show="!item.read_at" class="w-2 h-2 rounded-full bg-indigo-500 mt-2 shrink-0" aria-label="Belum dibaca"></span>
                </button>
            </template>
        </div>

        @if (Route::has('notifications.index'))
            <div class="flex border-t border-slate-100">
                <a href="{{ route('notifications.index') }}" class="flex-1 text-center text-sm font-semibold text-indigo-600 hover:bg-slate-50 px-4 py-3">
                    Lihat semua notifikasi
                </a>
                @if (Route::has('notifications.settings'))
                    <a href="{{ route('notifications.settings') }}" class="px-4 py-3 text-slate-600 hover:bg-slate-50 hover:text-indigo-700 border-l border-slate-100"
                       title="Pengaturan notifikasi" aria-label="Pengaturan notifikasi"><i class="fas fa-gear"></i></a>
                @endif
            </div>
        @endif
    </div>
</div>
