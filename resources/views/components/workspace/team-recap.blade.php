{{--
    Rekap tugas per tim untuk admin (PRD Fitur 2 "Admin: rekap per tim"): jumlah per status, terlambat,
    dan mentor yang belum memberi tugas minggu ini. Data dari store wsProgress (GET /workspace/progress).
    Tidak dirender selama modul Workspace belum aktif.
--}}
@if (Route::has('workspace.index'))
    @once
        @push('scripts')
            <script src="{{ asset('js/workspace/dashboard.js') }}?v={{ @filemtime(public_path('js/workspace/dashboard.js')) }}"></script>
        @endpush
    @endonce

    <section {{ $attributes->class(['ws-font ws-widget']) }} x-data data-ws-team-recap aria-labelledby="ws-recap-title">
        <div class="ws-widget__head">
            <span class="ws-widget__icon" style="--w-c:#2d3ecb;--w-bg:#eef2ff;"><i class="fas fa-layer-group"></i></span>
            <h2 id="ws-recap-title" class="ws-widget__title">Rekap Tugas per Tim</h2>
            <a class="ws-widget__more" href="{{ route('workspace.index') }}">Semua kelompok <i class="fas fa-arrow-right text-[10px]"></i></a>
        </div>
        <div class="ws-widget__body !p-0">
            <template x-if="!$store.wsProgress.loaded && !$store.wsProgress.error">
                <div class="p-5 space-y-3" aria-busy="true">
                    <span class="block h-3 rounded bg-slate-100 animate-pulse w-2/3"></span>
                    <span class="block h-3 rounded bg-slate-100 animate-pulse w-1/2"></span>
                </div>
            </template>
            <template x-if="$store.wsProgress.error">
                <div class="ws-widget__empty">
                    <i class="fas fa-plug-circle-exclamation"></i>
                    <span>Rekap tugas gagal dimuat.</span>
                    <button type="button" class="font-semibold text-indigo-600 hover:underline" x-on:click="$store.wsProgress.retry()">Coba lagi</button>
                </div>
            </template>
            <template x-if="$store.wsProgress.loaded && !$store.wsProgress.teams.length">
                <p class="ws-widget__empty"><i class="fas fa-circle-info"></i> Belum ada kelompok aktif.</p>
            </template>
            <div class="overflow-x-auto" x-show="$store.wsProgress.loaded && $store.wsProgress.teams.length">
                <table class="ws-recap">
                    <thead>
                        <tr>
                            <th scope="col">Tim</th>
                            <th scope="col">To Do</th>
                            <th scope="col">In Progress</th>
                            <th scope="col">Review</th>
                            <th scope="col">Revisi</th>
                            <th scope="col">Selesai</th>
                            <th scope="col">Terlambat</th>
                            <th scope="col">Belum memberi tugas minggu ini</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="r in $store.wsProgress.teams" :key="r.team.id">
                            <tr>
                                <th scope="row"><a :href="r.workspace_url" class="font-semibold text-indigo-700 hover:underline" x-text="r.team.name"></a></th>
                                <td x-text="r.stats.todo"></td>
                                <td x-text="r.stats.in_progress"></td>
                                <td x-text="r.stats.review"></td>
                                <td x-text="r.stats.revision"></td>
                                <td x-text="r.stats.done"></td>
                                <td><span :class="r.stats.late ? 'ws-badge ws-badge--late !py-0' : 'text-slate-500'" x-text="r.stats.late"></span></td>
                                <td>
                                    <span class="text-sm text-slate-500" x-show="!r.idle_mentors.length">Semua aktif</span>
                                    <span class="flex flex-wrap gap-1" x-show="r.idle_mentors.length">
                                        <template x-for="m in r.idle_mentors" :key="m.id">
                                            <span class="ws-badge !bg-amber-50 !text-amber-800 !border-amber-200 !py-0" x-text="m.name"></span>
                                        </template>
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endif
<x-workspace.page-css />
