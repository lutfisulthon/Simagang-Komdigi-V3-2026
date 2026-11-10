{{--
    Referensi nilai tugas Workspace di form penilaian sertifikat (PRD Fase 3: "nilai tugas sebagai masukan sertifikat").
    Hanya informasi — tidak mengisi kolom nilai sertifikat, karena keputusan "apakah nilai tugas masuk
    perhitungan sertifikat" masih terbuka di PRD. Data: WS.api.recap.get({ intern_id }).
    Tidak dirender selama modul Workspace belum aktif atau intern belum dipilih.
--}}
@props(['internId' => null])

@if (Route::has('workspace.index') && $internId)
    @once
        @push('scripts')
            <script src="{{ asset('js/workspace/dashboard.js') }}?v={{ @filemtime(public_path('js/workspace/dashboard.js')) }}"></script>
        @endpush
    @endonce

    <section {{ $attributes->class(['ws-font ws-widget']) }} x-data="wsTaskRecap({ internId: {{ (int) $internId }} })" aria-labelledby="ws-score-ref">
        <div class="ws-widget__head">
            <span class="ws-widget__icon" style="--w-c:#7c3aed;--w-bg:#f5f3ff;"><i class="fas fa-chart-simple"></i></span>
            <h3 id="ws-score-ref" class="ws-widget__title">Referensi dari Nilai Tugas</h3>
            @if (Route::has('workspace.recap'))
                <a class="ws-widget__more" href="{{ route('workspace.recap', ['intern_id' => $internId]) }}" target="_blank" rel="noopener">Rekap lengkap <i class="fas fa-up-right-from-square text-[10px]"></i></a>
            @endif
        </div>

        @include('workspace.partials.widget-states', ['rows' => 2])

        <template x-if="!loading && !error && recap">
            <div>
                <dl class="ws-it-stats !grid-cols-2 sm:!grid-cols-4">
                    <div><dt>Rata-rata nilai</dt><dd x-text="recap.summary.avg_score ?? '–'"></dd></div>
                    <div><dt>Rentang</dt><dd x-text="recap.summary.min_score !== null ? recap.summary.min_score + '–' + recap.summary.max_score : '–'"></dd></div>
                    <div><dt>Tepat waktu</dt><dd x-text="onTimePct === null ? '–' : onTimePct + '%'"></dd></div>
                    <div><dt>Tugas selesai</dt><dd x-text="recap.summary.done + (recap.summary.active ? ' / ' + (recap.summary.done + recap.summary.active) : '')"></dd></div>
                </dl>
                <p class="px-4 py-3 text-xs text-slate-600 flex items-start gap-2">
                    <i class="fas fa-circle-info mt-0.5 text-violet-600" aria-hidden="true"></i>
                    <span>Hanya sebagai bahan pertimbangan. Nilai sertifikat tetap Anda isi sendiri pada komponen di bawah.</span>
                </p>
            </div>
        </template>
    </section>
@endif
<x-workspace.page-css />
