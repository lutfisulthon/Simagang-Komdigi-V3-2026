{{--
    Board kanban. Data dari Alpine.store('wsTasks') (public/js/workspace/tasks.js).
    Variabel: $can (create_task, manage_sprints)
    Ada sprint berjalan → board hanya menampilkan tugas sprint itu (bilah sprint di atas).
    Assign dilakukan di sini: tombol avatar di kartu (mentor/admin).
--}}
@php
    $columns = [
        'todo' => ['To Do', '#94a3b8'],
        'in_progress' => ['In Progress', '#3b82f6'],
        'review' => ['Review', '#8b5cf6'],
        'revision' => ['Revisi', '#f97316'],
        'done' => ['Selesai', '#10b981'],
    ];
@endphp

{{-- Sprint berjalan --}}
<template x-if="$store.wsTasks.activeSprint">
    <div class="ws-sprint-bar" x-data="{ get sp() { return $store.wsTasks.activeSprint; }, get c() { return WS.sprints.counts($store.wsTasks.section(this.sp.id)); } }">
        <div class="min-w-0 flex-1">
            <p class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <span class="font-bold text-gray-800" x-text="sp.name"></span>
                <span class="text-xs text-slate-500" x-text="WS.sprints.dateRange(sp)"></span>
                <span class="text-xs font-semibold text-indigo-700" x-text="WS.sprints.remaining(sp)"></span>
                <span class="ws-leader-chip" x-show="sp && sp.leader" :title="sp && sp.leader ? 'Ketua tim: ' + sp.leader.name : ''">
                            <i class="fas fa-crown text-[10px] text-amber-500" aria-hidden="true"></i>
                            <span x-text="sp && sp.leader ? 'Ketua: ' + sp.leader.name.split(' ')[0] : ''"></span>
                        </span>
                <span class="ws-undivided" x-show="$store.wsTasks.undivided(sp.id)" x-text="$store.wsTasks.undivided(sp.id) + ' belum dibagi'"></span>
            </p>
            <p class="text-xs font-semibold text-violet-800 mt-1" x-show="$store.wsTasks.leads(sp)">
                <i class="fas fa-crown text-amber-500 mr-1" aria-hidden="true"></i>Anda ketua sprint ini. Bagi tugas lewat tombol <strong>Bagi</strong> atau avatar di kartu; satu tugas boleh untuk beberapa orang.
            </p>
            <p class="text-xs text-slate-600 mt-0.5 truncate" x-show="sp.goal"><i class="fas fa-bullseye mr-1" aria-hidden="true"></i><span x-text="sp.goal"></span></p>
            <div class="flex items-center gap-2 mt-2">
                <div class="ws-progress flex-1 max-w-xs" role="progressbar" aria-valuemin="0" :aria-valuemax="c.total" :aria-valuenow="c.done" aria-label="Progres sprint">
                    <div class="ws-progress__bar" :style="{ width: (c.total ? Math.round(c.done / c.total * 100) : 0) + '%' }"></div>
                </div>
                <span class="text-xs text-slate-600 whitespace-nowrap" x-text="c.done + '/' + c.total + ' selesai'"></span>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @unless (auth()->user()->isIntern())
                <button type="button" class="ws-btn ws-btn--ghost ws-btn--sm" x-on:click="$dispatch('ws-select-tab', 'backlog')"><i class="fas fa-layer-group"></i> Backlog</button>
            @endunless
            @if ($can['manage_sprints'] ?? false)
                <button type="button" class="ws-btn ws-btn--soft ws-btn--sm" x-on:click="$dispatch('ws-sprint-complete', { sprint: sp })">Selesaikan sprint</button>
            @endif
        </div>
    </div>
</template>
<template x-if="!$store.wsTasks.activeSprint && $store.wsTasks.sprints.length">
    <p class="ws-sprint-bar ws-sprint-bar--idle text-sm text-slate-600">
        <i class="fas fa-circle-info text-indigo-500" aria-hidden="true"></i>
        <span>Tidak ada sprint berjalan, jadi semua tugas ditampilkan.@unless (auth()->user()->isIntern()) Mulai sprint dari tab
            <button type="button" class="font-semibold text-indigo-600 hover:underline" x-on:click="$dispatch('ws-select-tab', 'backlog')">Backlog</button>.@endunless</span>
    </p>
</template>

{{-- HP: satu kolom per layar; pil ini melompat ke kolom & menandai kolom yang sedang terlihat. --}}
<nav class="ws-board-nav sm:hidden" aria-label="Kolom board"
     x-data="{
         active: 0,
         board() { return $root.nextElementSibling; },
         // Hanya board yang digeser ke samping; halaman tidak ikut menggulir.
         go(i) { const b = this.board(); b.scrollTo({ left: b.children[i].offsetLeft - b.firstElementChild.offsetLeft, behavior: WS.scrollBehavior() }); },
     }"
     x-init="$nextTick(() => { const b = board(); b.addEventListener('scroll', () => {
         const i = Math.round(b.scrollLeft / (b.firstElementChild.offsetWidth + 14));
         if (i === active) return;
         active = i;
         const pill = $root.children[i];
         if (pill) $root.scrollTo({ left: pill.offsetLeft - 16, behavior: WS.scrollBehavior() });
     }, { passive: true }); })">
    @foreach ($columns as $status => [$label, $color])
        <button type="button" class="ws-board-nav__item" :aria-current="active === {{ $loop->index }} ? 'true' : null"
                x-on:click="go({{ $loop->index }})">
            <span class="ws-column__dot" style="background: {{ $color }}" aria-hidden="true"></span>
            {{ $label }}
            <span class="ws-column__count" x-text="$store.wsTasks.count('{{ $status }}')"></span>
        </button>
    @endforeach
</nav>

<div x-data="wsBoard" class="ws-board" aria-label="Board tugas">
    @foreach ($columns as $status => [$label, $color])
        <section class="ws-column" :class="isDropTarget('{{ $status }}') && 'ws-column--target'" aria-labelledby="ws-col-{{ $status }}">
            <header class="ws-column__head">
                <span class="ws-column__dot" style="background: {{ $color }}"></span>
                <h2 id="ws-col-{{ $status }}">{{ $label }}</h2>
                <span class="ws-column__count" x-text="$store.wsTasks.count('{{ $status }}')"></span>
                @if ($status === 'todo' && $can['create_task'])
                    <button type="button" class="ws-icon-btn ws-icon-btn--sm ml-auto" x-on:click="$dispatch('ws-open-task-form', {})" aria-label="Buat tugas baru" title="Buat tugas">
                        <i class="fas fa-plus"></i>
                    </button>
                @endif
            </header>

            <div class="ws-column__body" data-column="{{ $status }}">
                <template x-for="card in $store.wsTasks.column('{{ $status }}')" :key="card.id">
                    <article class="ws-task-card" :data-id="card.id" tabindex="0"
                             :class="{ 'ws-task-card--locked': !card.can.move, 'opacity-60 pointer-events-none': $store.wsTasks.busy[card.id] }"
                             :aria-label="card.code + ' ' + card.title"
                             x-on:click="open(card)" x-on:keydown.enter.prevent="open(card)">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="ws-mono text-[11px] font-medium ws-text-faint whitespace-nowrap" x-text="card.code"></span>
                            <span class="ws-badge !text-[11px] !px-2 !py-0" :class="'ws-priority--' + card.priority">
                                <i class="fas text-[9px]" :class="WS.tasks.priorityIcon(card.priority)"></i>
                                <span x-text="WS.PRIORITY[card.priority]"></span>
                            </span>
                            <template x-if="card.is_late && card.status === 'review'">
                                <span class="ws-badge ws-badge--late !text-[11px] !px-2 !py-0"><i class="fas fa-triangle-exclamation text-[9px]"></i>Terlambat</span>
                            </template>

                            <div class="relative ml-auto" data-no-drag x-data="{ menu: false }" x-on:click.stop x-on:keydown.enter.stop
                                 x-on:click.outside="menu = false" x-on:keydown.escape="if (menu) { menu = false; $event.preventDefault(); $el.querySelector('button').focus(); }" x-show="targets(card).length || card.can.extend_deadline">
                                <button type="button" class="ws-icon-btn ws-icon-btn--sm -mr-1 -mt-1" x-on:click="menu = !menu" :aria-expanded="menu.toString()" aria-label="Pindah status">
                                    <i class="fas fa-ellipsis"></i>
                                </button>
                                <div class="ws-menu" x-show="menu" x-cloak x-transition.opacity>
                                    <p class="ws-menu__label">Pindah ke</p>
                                    <template x-for="s in targets(card)" :key="s">
                                        <button type="button" class="ws-menu__item" x-on:click="menu = false; $store.wsTasks.requestMove(card, s, 0)">
                                            <i class="fas fa-arrow-right text-[10px] ws-text-faint"></i>
                                            <span x-text="WS.STATUS[s]"></span>
                                        </button>
                                    </template>
                                    <template x-if="card.can.extend_deadline">
                                        <button type="button" class="ws-menu__item border-t border-slate-100 mt-1 pt-2" x-on:click="menu = false; $dispatch('ws-open-deadline', { task: card })">
                                            <i class="fas fa-calendar-plus text-[11px] text-amber-600"></i> Perpanjang deadline
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <p class="text-sm font-semibold text-gray-800 leading-snug line-clamp-2" x-text="card.title"></p>
                        <div class="ws-unfinished" x-show="WS.tasks.unfinished(card)">
                            <span class="ws-badge ws-badge--late !text-[11px] !px-2 !py-0"><i class="fas fa-hourglass-end text-[9px]"></i>Belum diselesaikan</span>
                            <span class="ws-unfinished__lock" x-show="WS.tasks.submitLocked(card)" :title="WS.tasks.LOCK_NOTE"><i class="fas fa-lock"></i>Upload dikunci</span>
                        </div>

                        <div class="flex flex-wrap gap-1 mt-2" x-show="card.labels.length">
                            <template x-for="label in card.labels" :key="label.id">
                                <span class="ws-label-chip"><span class="ws-label-chip__dot" :style="{ background: label.color }"></span><span x-text="label.name"></span></span>
                            </template>
                        </div>

                        <div class="flex items-center gap-3 mt-3">
                            <span class="ws-due" :class="'ws-due--' + due(card).state" :title="'Deadline ' + due(card).label">
                                <i class="far" :class="card.status === 'done' ? 'fa-circle-check' : 'fa-clock'"></i>
                                <span x-text="card.status === 'done' ? 'Selesai' : due(card).relative"></span>
                            </span>
                            <span class="ws-meta-count" x-show="card.checklist.total" :title="'Checklist ' + card.checklist.done + '/' + card.checklist.total">
                                <i class="far fa-square-check"></i><span x-text="card.checklist.done + '/' + card.checklist.total"></span>
                            </span>
                            <span class="ws-meta-count" x-show="card.comments_count" title="Komentar">
                                <i class="far fa-comment"></i><span x-text="card.comments_count"></span>
                            </span>
                            <span class="ws-meta-count" x-show="card.attachments_count" title="Lampiran">
                                <i class="fas fa-paperclip"></i><span x-text="card.attachments_count"></span>
                            </span>
                            {{-- Assignee: mentor/admin klik untuk assign (tugas dari backlog bisa belum di-assign). --}}
                            <div class="relative ml-auto" data-no-drag x-data="{ pick: false }" x-on:click.stop x-on:keydown.enter.stop
                                 x-on:click.outside="pick = false" x-on:keydown.escape="if (pick) { pick = false; $event.stopPropagation(); }">
                                <template x-if="canAssign(card)">
                                    <button type="button" class="ws-assign-btn" x-on:click="pick = !pick" :aria-expanded="pick.toString()"
                                            :aria-label="card.assignees.length ? 'Ubah pembagian ' + card.code + ': ' + card.assignees.map((a) => a.name).join(', ') : 'Bagi tugas ' + card.code">
                                        <span class="ws-avatar-stack" x-show="card.assignees.length">
                                            <template x-for="a in card.assignees" :key="a.id"><x-workspace.avatar-js user="a" size="xs" /></template>
                                        </span>
                                        <span class="ws-assign-btn__empty" x-show="!card.assignees.length"><i class="fas fa-user-plus" aria-hidden="true"></i> Bagi</span>
                                    </button>
                                </template>
                                <template x-if="!canAssign(card)">
                                    <span class="ws-avatar-stack">
                                        <template x-for="a in card.assignees" :key="a.id"><x-workspace.avatar-js user="a" size="xs" /></template>
                                        <span class="ws-bl-unassigned" x-show="!card.assignees.length" title="Belum dibagi"><i class="fas fa-user" aria-hidden="true"></i><span class="sr-only">Belum dibagi</span></span>
                                    </span>
                                </template>
                                <div class="ws-menu" x-show="pick" x-cloak x-transition.opacity>
                                    <p class="ws-menu__label">Bagi ke <span class="font-normal normal-case">(boleh lebih dari satu)</span></p>
                                    <template x-for="i in $store.wsTasks.interns" :key="i.id">
                                        <button type="button" class="ws-menu__item" x-on:click="pick = false; toggleAssignee(card, i.id)"
                                                :aria-pressed="card.assignees.some((a) => a.id === i.id).toString()">
                                            <x-workspace.avatar-js user="i" size="xs" />
                                            <span class="truncate" x-text="i.name"></span>
                                            <i class="fas fa-check text-xs text-indigo-600 ml-auto" x-show="card.assignees.some((a) => a.id === i.id)"></i>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </article>
                </template>

                <div class="ws-column__empty" x-show="$store.wsTasks.count('{{ $status }}') === 0">
                    {{ $status === 'review' ? 'Belum ada yang dikirim' : 'Belum ada tugas' }}
                </div>
            </div>
        </section>
    @endforeach
</div>
