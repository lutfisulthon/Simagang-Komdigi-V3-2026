{{-- Top bar desktop (lg ke atas). Di HP, lonceng dan ikon gerigi ada di header mobile. --}}
@if ($wsEnabled)
    <header class="ws-topbar hidden lg:flex items-center justify-between px-6 shrink-0">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <i class="far fa-calendar ws-text-faint"></i>
            <span>{{ now()->locale('id')->translatedFormat('l, j F Y') }}</span>
        </div>
        <div class="flex items-center gap-3">
            @if ($wsMock)
                <span class="hidden xl:inline-flex items-center gap-1.5 text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full"
                      title="Data berasal dari public/js/workspace/mock.js, bukan dari server">
                    <i class="fas fa-flask"></i> Mode preview · data contoh
                </span>
            @endif
            {{-- Status realtime: hanya tampil bila realtime dikonfigurasi (Reverb). Tanpa Reverb, aplikasi memakai polling. --}}
            <span class="ws-rt" x-data="{ s: WS.realtime ? WS.realtime.status : 'off' }" x-on:ws-realtime-status.window="s = $event.detail"
                  x-show="s !== 'off'" x-cloak role="status"
                  :title="s === 'connected' ? 'Pembaruan realtime aktif' : s === 'connecting' ? 'Menghubungkan realtime…' : 'Realtime terputus — memakai polling'">
                <span class="ws-rt__dot" :class="'ws-rt__dot--' + s" aria-hidden="true"></span>
                <span x-text="s === 'connected' ? 'Realtime' : s === 'connecting' ? 'Menghubungkan…' : 'Polling'"></span>
            </span>
            <x-workspace.notification-bell />
            <x-theme-menu />
        </div>
    </header>
@else
    {{-- Peran tanpa workspace (institusi, industri): top bar sederhana agar ikon gerigi tetap ada di pojok kanan. --}}
    <header class="hidden lg:flex items-center justify-between px-6 shrink-0 h-14 bg-white border-b border-gray-100 relative z-40">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <i class="far fa-calendar"></i>
            <span>{{ now()->locale('id')->translatedFormat('l, j F Y') }}</span>
        </div>
        <x-theme-menu />
    </header>
@endif
