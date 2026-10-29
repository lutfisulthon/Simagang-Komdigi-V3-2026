{{--
    Tab Laporan: kartu grafik gaya Jira. Komponen wsReports (public/js/workspace/reports.js), data dari store wsTasks.
    Mentor/admin: seluruh tugas kelompok (+ beban per anggota). Anak magang: hanya tugas miliknya.
--}}
@php($isIntern = auth()->user()->isIntern())

<div x-data="wsReports" class="space-y-4">
    <p class="text-sm text-slate-600 flex items-center gap-2">
        <i class="fas {{ $isIntern ? 'fa-user' : 'fa-users' }} text-indigo-500" aria-hidden="true"></i>
        {{ $isIntern ? 'Grafik ini hanya memakai tugas yang di-assign kepada Anda.' : 'Grafik memakai seluruh tugas kelompok ini.' }}
    </p>

    <div class="grid gap-4 xl:grid-cols-3">
        {{-- ================= Burndown ================= --}}
        <section class="ws-panel ws-rp xl:col-span-2" aria-labelledby="ws-rp-burn">
            <header class="ws-rp__head">
                <div class="min-w-0">
                    <h2 id="ws-rp-burn" class="ws-rp__title">Burndown sprint</h2>
                    <p class="ws-rp__desc">Sisa tugas di sprint dibanding rencana ideal (garis putus-putus). Garis ungu di bawah garis ideal berarti pekerjaan lebih cepat dari rencana.</p>
                </div>
                <label class="shrink-0" x-show="sprintOptions.length > 1" x-cloak>
                    <span class="sr-only">Pilih sprint</span>
                    <select class="ws-input !py-1.5 !text-sm !w-auto" x-model.number="sprintId">
                        <template x-for="sp in sprintOptions" :key="sp.id"><option :value="sp.id" x-text="sp.name + (sp.status === 'active' ? ' (berjalan)' : '')"></option></template>
                    </select>
                </label>
            </header>

            <template x-if="burndown.empty">
                <p class="ws-rp__empty"><i class="fas fa-chart-line" aria-hidden="true"></i><span x-text="burndown.reason"></span></p>
            </template>
            <template x-if="!burndown.empty">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="ws-rp__kpi"><strong x-text="burndown.remaining"></strong> tugas tersisa dari <strong x-text="burndown.total"></strong></span>
                    <span class="ws-rp__state" :class="'ws-rp__state--' + burndown.state" x-text="burndown.summary"></span>
                </div>
            </template>

            <div x-ref="burn" class="ws-rp__chart" x-show="!burndown.empty" x-html="burndown.empty ? '' : burndown.svg"></div>
            <ul class="ws-rp__keys" x-show="!burndown.empty">
                <li><i class="ws-rp__line ws-rp__line--actual" aria-hidden="true"></i>Sisa tugas</li>
                <li><i class="ws-rp__line ws-rp__line--ideal" aria-hidden="true"></i>Rencana ideal</li>
            </ul>

            <details class="ws-rp__data" x-show="!burndown.empty">
                <summary>Lihat data</summary>
                <table>
                    <thead><tr><th scope="col">Tanggal</th><th scope="col">Sisa tugas</th></tr></thead>
                    <tbody>
                        <template x-for="r in (burndown.rows || [])" :key="r.label"><tr><td x-text="r.label"></td><td x-text="r.value"></td></tr></template>
                    </tbody>
                </table>
            </details>
        </section>

        {{-- ================= Tugas per status ================= --}}
        <section class="ws-panel ws-rp" aria-labelledby="ws-rp-status">
            <header class="ws-rp__head">
                <div>
                    <h2 id="ws-rp-status" class="ws-rp__title">Tugas per status</h2>
                    <p class="ws-rp__desc">Sebaran tugas berdasarkan status saat ini.</p>
                </div>
            </header>
            @include('workspace.partials.report-donut', ['expr' => 'statusDonut', 'center' => 'statusDonut.total', 'caption' => 'tugas'])
        </section>

        {{-- ================= Selesai per sprint ================= --}}
        <section class="ws-panel ws-rp" aria-labelledby="ws-rp-velocity">
            <header class="ws-rp__head">
                <div>
                    <h2 id="ws-rp-velocity" class="ws-rp__title">Tugas selesai per sprint</h2>
                    <p class="ws-rp__desc">Jumlah tugas yang selesai di tiap sprint. Berguna untuk memperkirakan kapasitas sprint berikutnya.</p>
                </div>
            </header>
            <template x-if="velocity.cols.length">
                <div>@include('workspace.partials.report-bars', ['expr' => 'velocity', 'label' => 'Tugas selesai per sprint'])</div>
            </template>
            <p class="ws-rp__empty" x-show="!velocity.cols.length" x-cloak><i class="fas fa-chart-column" aria-hidden="true"></i>Belum ada sprint yang dimulai.</p>
        </section>

        {{-- ================= Selesai per minggu ================= --}}
        <section class="ws-panel ws-rp" aria-labelledby="ws-rp-weekly">
            <header class="ws-rp__head">
                <div>
                    <h2 id="ws-rp-weekly" class="ws-rp__title">Tugas selesai per minggu</h2>
                    <p class="ws-rp__desc">Delapan minggu terakhir (label = hari Senin tiap minggu).</p>
                </div>
            </header>
            @include('workspace.partials.report-bars', ['expr' => 'weekly', 'label' => 'Tugas selesai per minggu'])
        </section>

        {{-- ================= Ketepatan waktu ================= --}}
        <section class="ws-panel ws-rp" aria-labelledby="ws-rp-ontime">
            <header class="ws-rp__head">
                <div>
                    <h2 id="ws-rp-ontime" class="ws-rp__title">Ketepatan waktu</h2>
                    <p class="ws-rp__desc">Dari tugas yang sudah selesai, berapa yang rampung sebelum deadline.</p>
                </div>
            </header>
            @include('workspace.partials.report-donut', ['expr' => 'ontime', 'center' => "ontime.pct === null ? '–' : ontime.pct + '%'", 'caption' => 'tepat waktu'])
        </section>

        @unless ($isIntern)
            {{-- ================= Beban per anggota ================= --}}
            <section class="ws-panel ws-rp xl:col-span-3" aria-labelledby="ws-rp-load">
                <header class="ws-rp__head">
                    <div>
                        <h2 id="ws-rp-load" class="ws-rp__title">Beban per anggota</h2>
                        <p class="ws-rp__desc">Jumlah tugas tiap anggota menurut status. "Belum dibagi" = tugas tanpa penanggung jawab.</p>
                    </div>
                </header>
                <ul class="ws-wl">
                    <template x-for="r in workload.rows" :key="r.label">
                        <li class="ws-wl__row">
                            <span class="ws-wl__name">
                                <template x-if="r.intern"><x-workspace.avatar-js user="r.intern" size="xs" /></template>
                                <template x-if="!r.intern"><span class="ws-bl-unassigned" aria-hidden="true"><i class="fas fa-user"></i></span></template>
                                <span class="truncate" x-text="r.label"></span>
                            </span>
                            <span class="ws-wl__bar" role="img" :aria-label="r.label + ': ' + (r.total ? r.segs.filter((s) => s.count).map((s) => s.count + ' ' + s.label).join(', ') : 'belum ada tugas')">
                                <template x-for="s in r.segs" :key="s.key">
                                    <span class="ws-wl__seg" x-show="s.count" :style="{ width: s.pct + '%', background: s.color }" :title="s.label + ': ' + s.count"></span>
                                </template>
                            </span>
                            <span class="ws-wl__total" x-text="r.total"></span>
                        </li>
                    </template>
                </ul>
                <ul class="ws-rp__keys">
                    <template x-for="l in workload.legend" :key="l.key">
                        <li><i class="ws-rp__swatch" :style="{ background: l.color }" aria-hidden="true"></i><span x-text="l.label"></span></li>
                    </template>
                </ul>
            </section>
        @endunless
    </div>
</div>
