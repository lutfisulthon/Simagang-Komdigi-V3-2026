{{--
    Sel "Progres Tugas" per intern (Admin → Monitoring). Data dari store wsProgress (dashboard.js),
    yang mengumpulkan semua [data-ws-progress] di halaman lalu memuat GET /workspace/progress sekali.
    Tidak dirender selama modul Workspace belum aktif.
--}}
@props(['internId'])

@if (Route::has('workspace.index'))
    @once
        @push('scripts')
            <script src="{{ asset('js/workspace/dashboard.js') }}?v={{ @filemtime(public_path('js/workspace/dashboard.js')) }}"></script>
        @endpush
    @endonce

    <div class="ws-font ws-prog" data-ws-progress="{{ (int) $internId }}"
         x-data="{ id: {{ (int) $internId }}, get p() { return $store.wsProgress.interns[this.id] || null; } }">
        <template x-if="!$store.wsProgress.loaded && !$store.wsProgress.error">
            <span class="block h-2 w-28 rounded-full bg-slate-100 animate-pulse" aria-label="Memuat progres"></span>
        </template>
        <template x-if="$store.wsProgress.error">
            <span class="text-xs text-slate-500" title="Progres tugas gagal dimuat">–</span>
        </template>
        <template x-if="$store.wsProgress.loaded && (!p || !p.total)">
            <span class="text-xs text-slate-500">Belum ada tugas</span>
        </template>
        <template x-if="$store.wsProgress.loaded && p && p.total">
            <div class="min-w-[150px]">
                <div class="flex items-center justify-between gap-2 text-xs">
                    <span class="font-semibold text-gray-800" x-text="p.done + '/' + p.total + ' selesai'"></span>
                    <span class="text-slate-600" x-text="Math.round(p.done / p.total * 100) + '%'"></span>
                </div>
                <div class="ws-progress mt-1" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                     :aria-valuenow="Math.round(p.done / p.total * 100)" :aria-label="'Progres tugas ' + Math.round(p.done / p.total * 100) + '%'">
                    <div class="ws-progress__bar" :style="'width:' + Math.round(p.done / p.total * 100) + '%'"></div>
                </div>
                <div class="flex flex-wrap items-center gap-1 mt-1.5">
                    <span class="ws-badge ws-badge--late !text-[11px] !px-2 !py-0" x-show="p.late" x-text="p.late + ' terlambat'"></span>
                    <span class="ws-badge ws-status--review !text-[11px] !px-2 !py-0" x-show="p.review" x-text="p.review + ' review'"></span>
                    <a class="text-xs font-semibold text-indigo-600 hover:underline" x-show="p.group_url" :href="p.group_url">Kelompok</a>
                </div>
            </div>
        </template>
    </div>
@endif
<x-workspace.page-css />
