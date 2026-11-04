{{--
    Tab Timeline (roadmap gaya Jira): jalur sprint + tugas per sprint sebagai batang tanggal.
    Komponen: wsTimeline (public/js/workspace/timeline.js). Data dari store wsTasks (hanya kelompok ini).
    Mentor/admin: tombol perpanjang deadline per baris. Anak magang: hanya baca, tugas sendiri disorot.
--}}
<div x-data="wsTimeline" class="ws-tl space-y-4" :class="zoom === 'quarters' && 'ws-tl--quarters'">
    {{-- Filter & tampilan --}}
    <div class="ws-panel !p-4 flex flex-col lg:flex-row lg:items-center gap-3">
        <div class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
            <label class="relative w-full sm:w-56">
                <span class="sr-only">Cari tugas</span>
                <i class="fas fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 ws-text-faint text-sm" aria-hidden="true"></i>
                <input type="search" x-model.debounce.150ms="q" class="ws-input !pl-10 !py-2" placeholder="Cari judul atau kode…">
            </label>

            <div class="ws-avatar-filter" role="group" aria-label="Filter anggota" x-show="!isIntern && interns.length > 1" x-cloak>
                <template x-for="i in interns" :key="i.id">
                    <button type="button" class="ws-avatar-filter__btn" :aria-pressed="who.includes(i.id).toString()"
                            :aria-label="'Tugas ' + i.name" :title="i.name" x-on:click="toggleWho(i.id)">
                        <x-workspace.avatar-js user="i" size="sm" />
                    </button>
                </template>
            </div>

            <label>
                <span class="sr-only">Filter status</span>
                <select x-model="status" class="ws-input !py-2 !w-auto">
                    <option value="">Semua status</option>
                    <template x-for="s in statuses" :key="s[0]"><option :value="s[0]" x-text="s[1]"></option></template>
                </select>
            </label>

            <button type="button" class="ws-btn ws-btn--ghost ws-btn--sm" x-show="filtering" x-cloak x-on:click="reset()"><i class="fas fa-xmark" aria-hidden="true"></i> Reset</button>
        </div>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-600">
            <label class="inline-flex items-center gap-2 cursor-pointer" x-show="isIntern" x-cloak>
                <input type="checkbox" class="h-4 w-4 accent-indigo-600" x-model="mine"> Hanya tugas saya
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" class="h-4 w-4 accent-indigo-600" x-model="showDone"> Tampilkan yang selesai
            </label>
            <div class="ws-seg" role="group" aria-label="Skala waktu">
                <template x-for="z in zooms" :key="z.key">
                    <button type="button" class="ws-seg__item" :aria-pressed="(zoom === z.key).toString()" x-on:click="setZoom(z.key)" x-text="z.label"></button>
                </template>
            </div>
            <button type="button" class="ws-btn ws-btn--soft ws-btn--sm" x-on:click="scrollToToday()"><i class="far fa-calendar" aria-hidden="true"></i> Hari ini</button>
        </div>
    </div>

    <div class="ws-panel !p-0 overflow-hidden">
        <div class="ws-tl__scroll" x-ref="scroll" role="region" aria-label="Timeline tugas. Geser ke samping untuk melihat tanggal lain." tabindex="0">
            <div class="ws-tl__canvas" :style="{ width: 'calc(var(--tl-left) + ' + totalW + 'px)' }">

                {{-- Header skala waktu --}}
                <div class="ws-tl__head">
                    <div class="ws-tl__corner">Tugas</div>
                    <div class="ws-tl__bands" :style="{ width: totalW + 'px' }">
                        <div class="ws-tl__band">
                            <template x-for="b in bands.top" :key="b.key">
                                <div class="ws-tl__cell ws-tl__cell--top" :style="{ left: b.left + 'px', width: b.width + 'px' }"><span x-text="b.label"></span></div>
                            </template>
                        </div>
                        <div class="ws-tl__band">
                            <template x-for="b in bands.bottom" :key="b.key">
                                <div class="ws-tl__cell" :class="{ 'ws-tl__cell--weekend': b.weekend, 'ws-tl__cell--today': b.today }" :style="{ left: b.left + 'px', width: b.width + 'px' }">
                                    <span class="ws-tl__day" x-text="b.label"></span>
                                    <span class="ws-tl__dow" x-show="b.sub" x-text="b.sub"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="ws-tl__body">
                    {{-- Garis kolom (di bawah batang) --}}
                    <div class="ws-tl__grid" :style="{ left: 'var(--tl-left)', width: totalW + 'px' }" aria-hidden="true">
                        <template x-for="b in bands.bottom" :key="b.key">
                            <div class="ws-tl__col" :class="{ 'ws-tl__col--weekend': b.weekend, 'ws-tl__col--strong': b.strong }" :style="{ left: b.left + 'px', width: b.width + 'px' }"></div>
                        </template>
                    </div>

                    {{-- Jalur sprint --}}
                    <div class="ws-tl__row ws-tl__row--lane" x-show="lane.length" x-cloak>
                        <div class="ws-tl__left ws-tl__left--lane">Sprints</div>
                        <div class="ws-tl__track" :style="{ width: totalW + 'px' }">
                            <template x-for="sp in lane" :key="sp.id">
                                <div class="ws-tl__sprint" :class="'ws-tl__sprint--' + sp.status + (sp.planned ? ' ws-tl__sprint--planned' : '')"
                                     :style="{ left: sp.left + 'px', width: sp.width + 'px' }" :title="sp.title"><span x-text="sp.name"></span></div>
                            </template>
                        </div>
                    </div>

                    <template x-for="sec in sections" :key="sec.key">
                        <div class="ws-tl__section">
                            {{-- Judul bagian (sprint / Backlog) --}}
                            <div class="ws-tl__row ws-tl__row--section">
                                <div class="ws-tl__left ws-tl__left--section">
                                    <button type="button" class="ws-tl__toggle" x-on:click="toggle(sec)" :aria-expanded="isOpen(sec).toString()">
                                        <i class="fas fa-chevron-down text-[10px] transition-transform" :class="!isOpen(sec) && '-rotate-90'" aria-hidden="true"></i>
                                        <span class="font-bold text-gray-800 truncate" x-text="sec.name"></span>
                                    </button>
                                    <span class="ws-tl__chip" :class="'ws-tl__chip--' + sec.status" x-show="sec.status === 'active' || sec.status === 'completed'"
                                          x-text="sec.status === 'active' ? 'Berjalan' : 'Selesai'"></span>
                                    <span class="ws-tl__count" x-text="sec.done + '/' + sec.total"></span>
                                </div>
                                <div class="ws-tl__track"></div>
                            </div>

                            <template x-for="row in sec.rows" :key="row.card.id">
                                <div class="ws-tl__row ws-tl__row--task" x-show="isOpen(sec)">
                                    <div class="ws-tl__left">
                                        <button type="button" class="ws-tl__task" x-on:click="openTask(row.card)" :aria-label="'Buka ' + row.aria">
                                            <span class="ws-tl__code" x-text="row.card.code"></span>
                                            <span class="ws-tl__title" x-text="row.card.title"></span>
                                        </button>
                                        <span class="ws-badge ws-tl__status" :class="'ws-status--' + row.card.status" x-text="statusLabel(row.card.status)"></span>
                                        <button type="button" class="ws-icon-btn ws-icon-btn--sm ws-tl__extend" x-show="row.card.can.extend_deadline"
                                                x-on:click="$dispatch('ws-open-deadline', { task: row.card })"
                                                :aria-label="'Perpanjang deadline ' + row.card.code" title="Perpanjang deadline">
                                            <i class="fas fa-calendar-plus" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div class="ws-tl__track" :style="{ width: totalW + 'px' }">
                                        <div class="ws-tl__bar" :class="['ws-tl__bar--' + row.tone, row.est && 'ws-tl__bar--est', row.mine && isIntern && 'ws-tl__bar--mine']"
                                             :style="{ left: row.left + 'px', width: row.width + 'px', '--p': row.progress + '%' }"
                                             :title="row.title" x-on:click="openTask(row.card)"></div>
                                        <span class="ws-tl__people ws-avatar-stack" :style="{ left: (row.left + row.width + 6) + 'px' }">
                                            <template x-for="a in row.card.assignees.slice(0, 3)" :key="a.id"><x-workspace.avatar-js user="a" size="xs" /></template>
                                        </span>
                                    </div>
                                </div>
                            </template>

                            <div class="ws-tl__row ws-tl__row--task" x-show="isOpen(sec) && !sec.rows.length">
                                <div class="ws-tl__left ws-tl__left--empty">Belum ada tugas</div>
                                <div class="ws-tl__track"></div>
                            </div>
                        </div>
                    </template>

                    <div class="ws-tl__row" x-show="empty && filtering" x-cloak>
                        <div class="ws-tl__left ws-tl__left--empty">Tidak ada tugas yang cocok dengan filter.</div>
                        <div class="ws-tl__track"></div>
                    </div>

                    {{-- Garis hari ini (di bawah kolom nama tugas yang sticky) --}}
                    <div class="ws-tl__today-wrap" :style="{ left: 'var(--tl-left)', width: totalW + 'px' }" aria-hidden="true">
                        <div class="ws-tl__today" :style="{ left: todayX + 'px' }"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <ul class="ws-tl__legend" aria-label="Keterangan warna batang">
        <li><i class="ws-tl__swatch ws-tl__bar--todo"></i>To Do</li>
        <li><i class="ws-tl__swatch ws-tl__bar--in_progress"></i>In Progress</li>
        <li><i class="ws-tl__swatch ws-tl__bar--review"></i>Review</li>
        <li><i class="ws-tl__swatch ws-tl__bar--revision"></i>Revisi</li>
        <li><i class="ws-tl__swatch ws-tl__bar--done"></i>Selesai</li>
        <li><i class="ws-tl__swatch ws-tl__bar--late"></i>Lewat deadline</li>
        <li class="ws-text-faint">Bagian gelap = progres checklist. Tanda putus-putus di kiri = tanggal mulai perkiraan.</li>
    </ul>
</div>
