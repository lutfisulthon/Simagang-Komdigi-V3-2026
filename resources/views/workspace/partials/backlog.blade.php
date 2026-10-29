{{--
    Tab Backlog (public/js/workspace/backlog.js): sprint berjalan, sprint terencana, lalu Backlog.
    Mentor/admin: seret tugas antar bagian, buat/mulai/selesaikan sprint. Intern: hanya membaca.
    Variabel: $can (manage_sprints, create_task)
--}}
@php($canManage = $can['manage_sprints'] ?? false)

<div x-data="wsBacklog(@js(['canManage' => $canManage]))" class="ws-backlog">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <p class="text-sm text-slate-600">
            @if ($canManage)
                Susun tugas ke sprint. Seret baris <i class="fas fa-grip-vertical text-xs" aria-hidden="true"></i> atau pakai menu <i class="fas fa-ellipsis text-xs" aria-hidden="true"></i>.
                Ketua tim membagi tugas sprint ke anggota di <button type="button" class="font-semibold text-indigo-600 hover:underline" x-on:click="$dispatch('ws-select-tab', 'board')">Board</button>.
            @else
                Rencana tugas kelompok dari mentor. Kerjakan tugas sprint berjalan di <button type="button" class="font-semibold text-indigo-600 hover:underline" x-on:click="$dispatch('ws-select-tab', 'board')">Board</button>.
            @endif
        </p>
        @if ($canManage)
            <button type="button" class="ws-btn ws-btn--soft ws-btn--sm" x-on:click="$dispatch('ws-sprint-form', {})">
                <i class="fas fa-plus"></i> Buat sprint
            </button>
        @endif
    </div>

    <div class="space-y-4">
        <template x-for="sec in sections" :key="sec.key">
            <section class="ws-bl-section" :class="sec.sprint && sec.sprint.status === 'active' && 'ws-bl-section--active'"
                     :aria-label="sec.sprint ? sec.sprint.name : 'Backlog'">
                {{-- Kepala bagian --}}
                <header class="ws-bl-head">
                    <button type="button" class="ws-bl-head__toggle" x-on:click="toggle(sec.key)" :aria-expanded="(!collapsed[sec.key]).toString()">
                        <i class="fas fa-chevron-down text-xs transition-transform" :class="collapsed[sec.key] && '-rotate-90'" aria-hidden="true"></i>
                        <span class="font-bold text-gray-800" x-text="sec.sprint ? sec.sprint.name : 'Backlog'"></span>
                        <span class="ws-badge ws-status--in_progress !text-[11px] !px-2 !py-0" x-show="sec.sprint && sec.sprint.status === 'active'">Berjalan</span>
                        <span class="text-xs text-slate-500" x-show="sec.sprint && dateRange(sec.sprint)" x-text="sec.sprint ? dateRange(sec.sprint) : ''"></span>
                        <span class="text-xs text-slate-500" x-text="'(' + counts(sec.id).total + ' tugas)'"></span>
                    </button>
                    <span class="flex flex-wrap items-center gap-1.5">
                        <span class="ws-leader-chip" x-show="sec.sprint && sec.sprint.leader" :title="sec.sprint && sec.sprint.leader ? 'Ketua tim: ' + sec.sprint.leader.name : ''">
                            <i class="fas fa-crown text-[10px] text-amber-500" aria-hidden="true"></i>
                            <span x-text="sec.sprint && sec.sprint.leader ? 'Ketua: ' + sec.sprint.leader.name.split(' ')[0] : ''"></span>
                        </span>
                        <span class="ws-leader-chip ws-leader-chip--warn" x-show="sec.sprint && sec.sprint.status !== 'completed' && !sec.sprint.leader">
                            <i class="fas fa-crown text-[10px]" aria-hidden="true"></i> Ketua belum dipilih
                        </span>
                        <span class="ws-undivided" x-show="sec.sprint && $store.wsTasks.undivided(sec.id)" x-text="$store.wsTasks.undivided(sec.id) + ' belum dibagi'"></span>
                    </span>

                    <div class="ws-bl-head__side">
                        <span class="ws-bl-counts" :aria-label="counts(sec.id).todo + ' to do, ' + counts(sec.id).progress + ' dikerjakan, ' + counts(sec.id).done + ' selesai'">
                            <span class="ws-bl-count ws-bl-count--todo" x-text="counts(sec.id).todo" title="To Do"></span>
                            <span class="ws-bl-count ws-bl-count--progress" x-text="counts(sec.id).progress" title="Dikerjakan (In Progress, Review, Revisi)"></span>
                            <span class="ws-bl-count ws-bl-count--done" x-text="counts(sec.id).done" title="Selesai"></span>
                        </span>
                        <span class="text-xs font-semibold text-indigo-700" x-show="sec.sprint && sec.sprint.status === 'active'" x-text="sec.sprint ? remaining(sec.sprint) : ''"></span>
                        @if ($canManage)
                            <template x-if="sec.sprint && sec.sprint.status === 'planned'">
                                <button type="button" class="ws-btn ws-btn--soft ws-btn--sm" :disabled="hasActive"
                                        :title="hasActive ? 'Selesaikan sprint yang sedang berjalan terlebih dahulu' : ''"
                                        x-on:click="$dispatch('ws-sprint-start', { sprint: sec.sprint })">Mulai sprint</button>
                            </template>
                            <template x-if="sec.sprint && sec.sprint.status === 'active'">
                                <button type="button" class="ws-btn ws-btn--soft ws-btn--sm" x-on:click="$dispatch('ws-sprint-complete', { sprint: sec.sprint })">Selesaikan sprint</button>
                            </template>
                            <template x-if="sec.sprint">
                                <div class="relative" x-data="{ menu: false }" x-on:click.outside="menu = false" x-on:keydown.escape="menu = false">
                                    <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-on:click="menu = !menu" :aria-expanded="menu.toString()" :aria-label="'Opsi ' + sec.sprint.name">
                                        <i class="fas fa-ellipsis"></i>
                                    </button>
                                    <div class="ws-menu" x-show="menu" x-cloak x-transition.opacity>
                                        <button type="button" class="ws-menu__item" x-on:click="menu = false; $dispatch('ws-sprint-form', { sprint: sec.sprint })">
                                            <i class="fas fa-pen text-xs ws-text-faint"></i> Ubah sprint
                                        </button>
                                        <button type="button" class="ws-menu__item text-red-700" x-show="sec.sprint.status === 'planned'"
                                                x-on:click="menu = false; $dispatch('ws-sprint-delete', { sprint: sec.sprint })">
                                            <i class="fas fa-trash text-xs"></i> Hapus sprint
                                        </button>
                                    </div>
                                </div>
                            </template>
                        @endif
                    </div>
                </header>
                <p class="ws-bl-goal" x-show="sec.sprint && sec.sprint.goal && !collapsed[sec.key]">
                    <i class="fas fa-bullseye text-xs" aria-hidden="true"></i> <span x-text="sec.sprint ? sec.sprint.goal : ''"></span>
                </p>

                {{-- Baris tugas --}}
                <div x-show="!collapsed[sec.key]">
                    <div class="ws-bl-list" :data-sprint-list="sec.id === null ? 'backlog' : sec.id">
                        <template x-for="t in tasks(sec.id)" :key="t.id">
                            <div class="ws-bl-row" :data-id="t.id" :class="$store.wsTasks.busy[t.id] && 'opacity-60'">
                                @if ($canManage)
                                    <span class="ws-bl-row__handle" aria-hidden="true" title="Seret untuk memindahkan"><i class="fas fa-grip-vertical"></i></span>
                                @endif
                                <button type="button" class="ws-bl-row__main" x-on:click="open(t)">
                                    <span class="ws-mono text-xs ws-text-faint shrink-0 w-11" x-text="t.code"></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-medium text-gray-800 truncate" x-text="t.title"></span>
                                        <span class="ws-bl-row__labels" x-show="t.labels.length">
                                            <template x-for="label in t.labels" :key="label.id">
                                                <span class="ws-label-chip"><span class="ws-label-chip__dot" :style="{ background: label.color }"></span><span x-text="label.name"></span></span>
                                            </template>
                                        </span>
                                    </span>
                                </button>
                                <div class="ws-bl-row__meta">
                                    <span class="ws-badge !text-[11px] !px-2 !py-0" :class="'ws-status--' + t.status"><span x-text="WS.STATUS[t.status]"></span></span>
                                    <span class="ws-bl-row__prio" :class="'ws-priority--' + t.priority" :title="'Prioritas ' + WS.PRIORITY[t.priority]">
                                        <i class="fas" :class="priorityIcon(t.priority)" aria-hidden="true"></i><span class="sr-only" x-text="'Prioritas ' + WS.PRIORITY[t.priority]"></span>
                                    </span>
                                    <span class="ws-due ws-bl-row__due" :class="'ws-due--' + due(t).state" :title="'Deadline ' + due(t).label" x-text="due(t).label"></span>
                                    <span class="ws-avatar-stack" x-show="t.assignees.length">
                                        <template x-for="a in t.assignees" :key="a.id"><x-workspace.avatar-js user="a" size="xs" /></template>
                                    </span>
                                    <span class="ws-bl-unassigned" x-show="!t.assignees.length" title="Belum dibagi — ketua tim membaginya di Board"><i class="fas fa-user" aria-hidden="true"></i><span class="sr-only">Belum dibagi</span></span>
                                    @if ($canManage)
                                        <div class="relative" x-data="{ menu: false }" x-on:click.outside="menu = false" x-on:keydown.escape="menu = false" data-no-drag>
                                            <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-on:click="menu = !menu" :aria-expanded="menu.toString()" :aria-label="'Pindahkan ' + t.code">
                                                <i class="fas fa-ellipsis"></i>
                                            </button>
                                            <div class="ws-menu" x-show="menu" x-cloak x-transition.opacity>
                                                <p class="ws-menu__label">Pindah ke</p>
                                                <template x-for="target in targets(t)" :key="target.key">
                                                    <button type="button" class="ws-menu__item" x-on:click="menu = false; move(t, target.id)">
                                                        <i class="fas fa-arrow-right text-[10px] ws-text-faint"></i>
                                                        <span x-text="target.sprint ? target.sprint.name : 'Backlog'"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </template>
                    </div>
                    <p class="ws-bl-empty" x-show="!tasks(sec.id).length"
                       x-text="sec.sprint ? ({{ $canManage ? 'true' : 'false' }} ? 'Sprint masih kosong. Seret tugas dari Backlog ke sini.' : 'Belum ada tugas di sprint ini.') : 'Backlog kosong.'"></p>
                    @if ($can['create_task'] ?? false)
                        <button type="button" class="ws-bl-create" x-on:click="$dispatch('ws-open-task-form', { sprint_id: sec.id })">
                            <i class="fas fa-plus text-xs"></i> Buat tugas
                        </button>
                    @endif
                </div>
            </section>
        </template>
    </div>

    {{-- Sprint selesai (riwayat, hanya baca) --}}
    <div class="mt-6" x-show="completed.length">
        <button type="button" class="ws-section-label inline-flex items-center gap-2 py-1" x-on:click="showCompleted = !showCompleted" :aria-expanded="showCompleted.toString()">
            <i class="fas fa-chevron-down text-[10px] transition-transform" :class="!showCompleted && '-rotate-90'" aria-hidden="true"></i>
            <span x-text="'Sprint selesai (' + completed.length + ')'"></span>
        </button>
        <ul class="mt-3 space-y-2" x-show="showCompleted" x-cloak>
            <template x-for="sp in completed" :key="sp.id">
                <li class="ws-bl-done">
                    <span class="font-semibold text-gray-800" x-text="sp.name"></span>
                    <span class="text-xs text-slate-500" x-text="dateRange(sp)"></span>
                    <span class="text-xs text-slate-600 sm:ml-auto" x-text="counts(sp.id).done + '/' + counts(sp.id).total + ' tugas selesai'"></span>
                </li>
            </template>
        </ul>
    </div>
</div>
