{{--
    Rekap tugas Workspace di laporan akhir intern (PRD Fase 3: "rekap tugas ke laporan akhir").
    mode="form" (default, di dalam <form>): mengirim field `include_task_recap` (1/0). Tombol "Isi ke kolom Kegiatan" menulis ringkasan ke
    textarea activities[0][description] di form yang sama. Data: WS.api.recap.get() (GET /workspace/recap).
    Tidak dirender selama modul Workspace belum aktif.
--}}
@props(['checked' => true, 'mode' => 'form'])
{{-- mode="view": hanya ringkasan & daftar (bagian tampilan laporan yang sudah dikirim). --}}

@if (Route::has('workspace.index'))
    @once
        @push('scripts')
            <script src="{{ asset('js/workspace/dashboard.js') }}?v={{ @filemtime(public_path('js/workspace/dashboard.js')) }}"></script>
        @endpush
    @endonce

    <section {{ $attributes->class(['ws-font ws-widget mb-6']) }} x-data="wsTaskRecap({})" aria-labelledby="ws-recap-head-{{ $mode }}">
        <div class="ws-widget__head">
            <span class="ws-widget__icon" style="--w-c:#059669;--w-bg:#ecfdf5;"><i class="fas fa-list-check"></i></span>
            <h3 id="ws-recap-head-{{ $mode }}" class="ws-widget__title">Rekap Tugas Workspace</h3>
            @if (Route::has('workspace.recap'))
                <a class="ws-widget__more" href="{{ route('workspace.recap') }}" target="_blank" rel="noopener">Lihat & cetak <i class="fas fa-up-right-from-square text-[10px]"></i></a>
            @endif
        </div>

        @include('workspace.partials.widget-states', ['rows' => 3])

        <template x-if="!loading && !error && recap">
            <div>
                <dl class="ws-it-stats !grid-cols-3">
                    <div><dt>Tugas selesai</dt><dd x-text="recap.summary.done"></dd></div>
                    <div><dt>Tepat waktu</dt><dd x-text="onTimePct === null ? '–' : onTimePct + '%'"></dd></div>
                    <div><dt>Rata-rata nilai</dt><dd x-text="recap.summary.avg_score ?? '–'"></dd></div>
                </dl>
                <div class="ws-widget__body">
                    <p class="ws-widget__empty" x-show="!recap.tasks.length"><i class="fas fa-inbox"></i> Belum ada tugas yang selesai dinilai mentor.</p>
                    <ul x-show="recap.tasks.length">
                        <template x-for="t in shown" :key="t.id">
                            <li class="ws-widget__row">
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-gray-800 truncate"><span class="ws-mono ws-text-faint font-medium mr-1" x-text="t.code"></span><span x-text="t.title"></span></span>
                                    <span class="block text-xs text-slate-500 truncate" x-text="t.workspace.name + ' · selesai ' + date(t.completed_at) + (t.on_time ? '' : ' · terlambat')"></span>
                                </span>
                                <span class="ws-recap-score" x-text="t.score ?? '–'" :aria-label="'Nilai ' + t.score"></span>
                            </li>
                        </template>
                    </ul>
                    <button type="button" class="text-xs font-semibold text-indigo-600 hover:underline px-2 py-1" x-show="recap.tasks.length > 5"
                            x-on:click="showAll = !showAll" x-text="showAll ? 'Tampilkan 5 terbaru' : 'Tampilkan semua (' + recap.tasks.length + ')'"></button>
                </div>
                @if ($mode === 'form')
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-4 pb-4">
                    <label class="flex items-center gap-2 text-sm text-gray-800 cursor-pointer">
                        <input type="hidden" name="include_task_recap" value="0">
                        <input type="checkbox" name="include_task_recap" value="1" class="w-4 h-4 accent-indigo-600" @checked(old('include_task_recap', $checked))>
                        Lampirkan rekap tugas ke laporan akhir
                    </label>
                    <button type="button" class="ws-btn ws-btn--soft ws-btn--sm sm:ml-auto" x-on:click="fillActivities()" :disabled="!recap.tasks.length">
                        <i class="fas fa-pen-to-square"></i> Isi ke kolom Kegiatan
                    </button>
                </div>
                @endif
            </div>
        </template>
    </section>
@endif
<x-workspace.page-css />
