{{--
    Panel "Tugas di Workspace" di halaman detail intern (admin & mentor). PRD: tab "Tugas".
    Tab internal Aktif / Selesai / Semua. Data: WS.api.interns.tasks(id) (GET /workspace/interns/{intern}/tasks).
    Tidak dirender selama modul Workspace belum aktif.
--}}
@props(['internId'])

@if (Route::has('workspace.index'))
    @once
        @push('scripts')
            <script src="{{ asset('js/workspace/dashboard.js') }}?v={{ @filemtime(public_path('js/workspace/dashboard.js')) }}"></script>
        @endpush
    @endonce

    <section {{ $attributes->class(['ws-font ws-widget']) }} x-data="wsInternTasks({{ (int) $internId }})" aria-labelledby="ws-it-title">
        <div class="ws-widget__head">
            <span class="ws-widget__icon" style="--w-c:#2d3ecb;--w-bg:#eef2ff;"><i class="fas fa-clipboard-list"></i></span>
            <h2 id="ws-it-title" class="ws-widget__title">Tugas di Workspace</h2>
            <a class="ws-widget__more" x-show="data && data.group" :href="data && data.group ? data.group.url : '#'"><span x-text="data && data.group ? data.group.name : ''"></span> <i class="fas fa-arrow-right text-[10px]"></i></a>
        </div>

        @include('workspace.partials.widget-states', ['rows' => 4])

        <template x-if="!loading && !error && data">
            <div>
                {{-- Ringkasan --}}
                <dl class="ws-it-stats">
                    <div><dt>Aktif</dt><dd x-text="data.stats.todo + data.stats.in_progress + data.stats.review + data.stats.revision"></dd></div>
                    <div><dt>Review</dt><dd x-text="data.stats.review"></dd></div>
                    <div><dt>Revisi</dt><dd x-text="data.stats.revision"></dd></div>
                    <div><dt>Selesai</dt><dd x-text="data.stats.done"></dd></div>
                    <div><dt>Terlambat</dt><dd :class="data.stats.late && 'text-red-700'" x-text="data.stats.late"></dd></div>
                    <div><dt>Rata-rata nilai</dt><dd x-text="data.stats.avg_score ?? '–'"></dd></div>
                </dl>

                {{-- Tab --}}
                <div class="px-4 pt-3">
                    <div class="ws-tabs" role="tablist" aria-label="Filter tugas">
                        <template x-for="t in tabs" :key="t.key">
                            <button type="button" role="tab" class="ws-tab !py-1.5" :aria-selected="(tab === t.key).toString()" x-on:click="tab = t.key">
                                <span x-text="t.label"></span><span class="ws-tab__count" x-text="count(t.key)"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="ws-widget__body">
                    <p class="ws-widget__empty" x-show="!visible.length">
                        <i class="fas fa-inbox"></i>
                        <span x-text="data && !data.group ? 'Belum masuk kelompok. Tugas bisa diberikan setelah anak magang dimasukkan ke kelompok.' : tab === 'done' ? 'Belum ada tugas selesai.' : tab === 'active' ? 'Tidak ada tugas aktif.' : 'Belum ada tugas.'"></span>
                    </p>
                    <ul x-show="visible.length">
                        <template x-for="t in visible" :key="t.id">
                            <li>
                                <a :href="boardUrl(t)" class="ws-widget__row">
                                    <span class="ws-badge shrink-0" :class="'ws-status--' + t.status">
                                        <span class="ws-badge__dot"></span><span x-text="statusLabel(t.status)"></span>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-semibold text-gray-800 truncate">
                                            <span class="ws-mono ws-text-faint font-medium mr-1" x-text="t.code"></span><span x-text="t.title"></span>
                                        </span>
                                        <span class="block text-xs ws-text-faint truncate" x-text="t.workspace.name"></span>
                                    </span>
                                    <span class="ws-widget__due" x-show="t.status === 'done'" x-text="t.score !== null && t.score !== undefined ? 'Nilai ' + t.score : 'Selesai'"></span>
                                    <span class="ws-widget__due" x-show="t.status !== 'done'" :class="'ws-widget__due--' + due(t).state" :title="dateTime(t.due_at)" x-text="due(t).text"></span>
                                </a>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </template>
    </section>
@endif
<x-workspace.page-css />
