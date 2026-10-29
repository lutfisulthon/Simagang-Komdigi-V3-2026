{{--
    Tab Deadline: agenda (dikelompokkan Lewat deadline / Hari ini / Besok / …) dan kalender bulanan.
    Hanya tugas kelompok ini (store wsTasks), jadi anak magang tidak melihat kelompok lain.
    Komponen: wsDeadlines (public/js/workspace/deadlines.js).
--}}
<div x-data="wsDeadlines" class="space-y-4">
    {{-- Filter & pengaturan tampilan --}}
    <div class="ws-panel !p-4 flex flex-col lg:flex-row lg:items-center gap-3">
        <div class="flex flex-wrap gap-2" role="group" aria-label="Filter deadline">
            <template x-for="f in filters" :key="f.key">
                <button type="button" class="ws-chip ws-chip--plain !py-1 !text-xs" :class="f.key === 'late' && count('late') > 0 && 'ws-chip--late'"
                        :aria-pressed="(filter === f.key).toString()" x-on:click="filter = f.key">
                    <span x-text="f.label"></span>
                    <span class="ws-dl-count" x-text="count(f.key)"></span>
                </button>
            </template>
        </div>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 lg:ml-auto text-sm text-slate-600">
            <label class="inline-flex items-center gap-2 cursor-pointer" x-show="isIntern">
                <input type="checkbox" class="h-4 w-4 accent-indigo-600" x-model="mine"> Hanya tugas saya
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" class="h-4 w-4 accent-indigo-600" x-model="showDone"> Tampilkan yang selesai
            </label>
            <div class="ws-seg" role="group" aria-label="Tampilan deadline">
                <button type="button" class="ws-seg__item" :aria-pressed="(view === 'agenda').toString()" x-on:click="setView('agenda')">
                    <i class="fas fa-list-check text-xs" aria-hidden="true"></i> Agenda
                </button>
                <button type="button" class="ws-seg__item" :aria-pressed="(view === 'calendar').toString()" x-on:click="setView('calendar')">
                    <i class="fas fa-calendar-days text-xs" aria-hidden="true"></i> Kalender
                </button>
            </div>
        </div>
    </div>

    {{-- ================= Agenda ================= --}}
    <div x-show="view === 'agenda'" class="space-y-4">
        <div class="ws-panel" x-show="!groups.length" x-cloak>
            <x-workspace.empty-state icon="fa-calendar-check" title="Tidak ada deadline di sini"
                                     description="Tidak ada tugas yang cocok dengan filter ini. Ubah filter atau tampilkan tugas yang sudah selesai." />
        </div>

        <template x-for="g in groups" :key="g.key">
            <section class="ws-panel !p-0 overflow-hidden" :aria-labelledby="'ws-dl-g-' + g.key">
                <header class="ws-dl-head" :class="'ws-dl-head--' + g.key">
                    <h2 :id="'ws-dl-g-' + g.key" class="font-bold text-sm" x-text="g.label"></h2>
                    <span class="ws-dl-count" x-text="g.items.length"></span>
                    <span class="text-xs font-medium opacity-80" x-show="g.hint" x-text="g.hint"></span>
                </header>
                <ul>
                    <template x-for="card in g.items" :key="card.id">
                        @include('workspace.partials.deadline-row')
                    </template>
                </ul>
            </section>
        </template>
    </div>

    {{-- ================= Kalender ================= --}}
    <div x-show="view === 'calendar'" x-cloak class="ws-panel">
        <div class="flex items-center gap-2 mb-4">
            <button type="button" class="ws-icon-btn" x-on:click="shift(-1)" aria-label="Bulan sebelumnya"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
            <button type="button" class="ws-icon-btn" x-on:click="shift(1)" aria-label="Bulan berikutnya"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
            <h2 class="font-bold text-gray-800 capitalize ml-1" x-text="monthLabel" aria-live="polite"></h2>
            <button type="button" class="ws-btn ws-btn--soft ws-btn--sm ml-auto" x-on:click="goToday()">Hari ini</button>
        </div>

        <table class="ws-cal">
            <thead>
                <tr>
                    <template x-for="d in weekdays" :key="d"><th scope="col" x-text="d"></th></template>
                </tr>
            </thead>
            <tbody>
                <template x-for="(week, wi) in weeks" :key="wi">
                    <tr>
                        <template x-for="d in week" :key="d.key">
                            <td :class="{ 'ws-cal__cell--out': !d.inMonth, 'ws-cal__cell--today': d.today, 'ws-cal__cell--picked': d.key === picked }">
                                <button type="button" class="ws-cal__btn" x-on:click="pick(d)" :aria-label="d.label" :aria-pressed="(d.key === picked).toString()">
                                    <span class="ws-cal__num" x-text="d.day"></span>
                                    <template x-if="d.items.length">
                                        <span class="ws-cal__items">
                                            <span class="ws-cal__chips">
                                                <template x-for="t in d.items.slice(0, 2)" :key="t.id">
                                                    <span class="ws-cal__chip" :class="'ws-cal__chip--' + tone(t)" x-text="t.code + ' ' + t.title"></span>
                                                </template>
                                                <span class="ws-cal__more" x-show="d.items.length > 2" x-text="'+' + (d.items.length - 2) + ' lagi'"></span>
                                            </span>
                                            <span class="ws-cal__dots" aria-hidden="true">
                                                <template x-for="t in d.items.slice(0, 3)" :key="t.id"><i class="ws-cal__dot" :class="'ws-cal__dot--' + tone(t)"></i></template>
                                            </span>
                                        </span>
                                    </template>
                                </button>
                            </td>
                        </template>
                    </tr>
                </template>
            </tbody>
        </table>

        <ul class="ws-cal__legend" aria-label="Keterangan warna">
            <li><i class="ws-cal__dot ws-cal__dot--late"></i>Lewat deadline</li>
            <li><i class="ws-cal__dot ws-cal__dot--soon"></i>Hari ini</li>
            <li><i class="ws-cal__dot ws-cal__dot--normal"></i>Terjadwal</li>
            <li><i class="ws-cal__dot ws-cal__dot--sent"></i>Menunggu review</li>
            <li><i class="ws-cal__dot ws-cal__dot--done"></i>Selesai</li>
        </ul>

        <div class="mt-4 pt-4 border-t border-slate-100">
            <h3 class="text-sm font-bold text-gray-800 mb-2" x-text="pickedLabel"></h3>
            <p class="text-sm text-slate-500" x-show="!pickedItems.length">Tidak ada deadline di tanggal ini.</p>
            <ul x-show="pickedItems.length">
                <template x-for="card in pickedItems" :key="card.id">
                    @include('workspace.partials.deadline-row')
                </template>
            </ul>
        </div>
    </div>
</div>
