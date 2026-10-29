{{--
    Widget Workspace untuk dashboard yang sudah ada (PRD "Perubahan di halaman yang sudah ada").
      role="intern" → "Tugas Saya" (5 deadline terdekat) + "Pesan belum dibaca"
      role="mentor" → "Anak bimbingan baru" (belum punya kelompok, bila ada) + "Perlu Review" + "Tugas Terlambat" per intern
                      + "Deadline 7 Hari ke Depan" (per tugas, semua kelompok)
    Data diambil sendiri lewat WS.api.dashboard.summary() (GET /workspace/summary), jadi controller
    dashboard tidak perlu diubah. Tidak dirender sama sekali selama modul Workspace belum aktif.
--}}
@props(['role'])

@if (Route::has('workspace.index'))
    @once
        @push('scripts')
            <script src="{{ asset('js/workspace/dashboard.js') }}?v={{ @filemtime(public_path('js/workspace/dashboard.js')) }}"></script>
        @endpush
    @endonce

    <section {{ $attributes->class(['ws-font grid grid-cols-1 gap-4 sm:gap-5', $role === 'intern' ? 'lg:grid-cols-3' : 'md:grid-cols-2 lg:grid-cols-5']) }}
             x-data="wsDashboard" aria-label="Ringkasan Workspace">

        @if ($role === 'intern')
            {{-- ================= Tugas Saya ================= --}}
            <div class="ws-widget lg:col-span-2">
                <div class="ws-widget__head">
                    <span class="ws-widget__icon" style="--w-c:#2d3ecb;--w-bg:#eef2ff;"><i class="fas fa-clipboard-list"></i></span>
                    <h2 class="ws-widget__title">Tugas Saya</h2>
                    <span class="ws-widget__count" x-cloak x-show="data && data.my_tasks && data.my_tasks.counts.active" x-text="data && data.my_tasks ? data.my_tasks.counts.active + ' aktif' : ''"></span>
                    <a class="ws-widget__more" :href="data ? data.workspace_url : '{{ route('workspace.index') }}'" href="{{ route('workspace.index') }}">
                        Buka board <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                <template x-if="data && data.my_tasks && (data.my_tasks.counts.revision || data.my_tasks.counts.late)">
                    <div class="ws-widget__chips">
                        <span class="ws-widget__chip ws-widget__chip--orange" x-show="data.my_tasks.counts.revision">
                            <i class="fas fa-rotate-left"></i> <span x-text="data.my_tasks.counts.revision + ' perlu revisi'"></span>
                        </span>
                        <span class="ws-widget__chip ws-widget__chip--red" x-show="data.my_tasks.counts.late">
                            <i class="fas fa-triangle-exclamation"></i> <span x-text="data.my_tasks.counts.late + ' terlambat'"></span>
                        </span>
                    </div>
                </template>

                <div class="ws-widget__body">
                    @include('workspace.partials.widget-states', ['rows' => 5])

                    <template x-if="!loading && !error && data && data.pending_mentor">
                        <p class="ws-widget__empty"><i class="fas fa-user-clock"></i> Mentor akan memasukkan Anda ke kelompok. Tugas kelompok muncul di sini.</p>
                    </template>
                    <template x-if="!loading && !error && data && !data.pending_mentor && !data.my_tasks.data.length">
                        <p class="ws-widget__empty"><i class="fas fa-circle-check"></i> Tidak ada tugas aktif. Tugas baru dari mentor akan muncul di sini.</p>
                    </template>

                    <ul x-show="!loading && !error && data && data.my_tasks && data.my_tasks.data.length">
                        <template x-for="t in (data && data.my_tasks ? data.my_tasks.data : [])" :key="t.id">
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
                                        <span class="ws-unfinished" x-show="WS.tasks.unfinished(t)">
                                            <span class="ws-badge ws-badge--late !text-[11px] !px-2 !py-0"><i class="fas fa-hourglass-end text-[9px]"></i>Belum diselesaikan</span>
                                            <span class="ws-unfinished__lock" x-show="WS.tasks.submitLocked(t)" :title="WS.tasks.LOCK_NOTE"><i class="fas fa-lock"></i>Upload dikunci</span>
                                        </span>
                                    </span>
                                    <span class="ws-widget__due" :class="'ws-widget__due--' + due(t).state" :title="dateTime(t.due_at)" x-text="due(t).text"></span>
                                </a>
                            </li>
                        </template>
                    </ul>
                    <p class="ws-widget__footnote" x-show="!loading && !error && data && data.my_tasks && data.my_tasks.counts.active > data.my_tasks.data.length"
                       x-text="data && data.my_tasks ? '+' + (data.my_tasks.counts.active - data.my_tasks.data.length) + ' tugas aktif lainnya di board' : ''"></p>
                </div>
            </div>

            {{-- ================= Pesan belum dibaca ================= --}}
            <div class="ws-widget">
                <div class="ws-widget__head">
                    <span class="ws-widget__icon" style="--w-c:#0891b2;--w-bg:#ecfeff;"><i class="fas fa-comments"></i></span>
                    <h2 class="ws-widget__title">Pesan belum dibaca</h2>
                    <span class="ws-widget__count ws-widget__count--alert" x-cloak x-show="unreadTotal > 0" x-text="unreadTotal"></span>
                </div>
                <div class="ws-widget__body">
                    @include('workspace.partials.widget-states', ['rows' => 3])

                    <template x-if="!loading && !error && data && !data.unread_channels.length">
                        <p class="ws-widget__empty"><i class="fas fa-check-double"></i> Semua pesan sudah dibaca.</p>
                    </template>

                    <ul x-show="!loading && !error && data && data.unread_channels && data.unread_channels.length">
                        <template x-for="u in (data && data.unread_channels ? data.unread_channels : [])" :key="u.channel.id">
                            <li>
                                <a :href="u.url" class="ws-widget__row !items-start">
                                    <span class="ws-widget__chan-icon">
                                        <i class="fas" :class="u.channel.type === 'announcement' ? 'fa-bullhorn' : u.channel.type === 'direct' ? 'fa-user' : 'fa-hashtag'"></i>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-gray-800 truncate" x-text="channelTitle(u.channel)"></span>
                                            <span class="ws-chan__badge ml-auto shrink-0" x-text="u.unread_count" :aria-label="u.unread_count + ' pesan belum dibaca'"></span>
                                        </span>
                                        <span class="block text-xs text-slate-600 line-clamp-2 mt-0.5">
                                            <span class="font-semibold" x-text="u.last_message.user ? u.last_message.user.name.split(' ')[0] + ':' : 'Sistem:'"></span>
                                            <span x-text="u.last_message.body"></span>
                                        </span>
                                        <span class="block text-[11px] ws-text-faint mt-0.5" x-text="relative(u.last_message.created_at) + (u.workspace ? ' · ' + u.workspace.name : '')"></span>
                                    </span>
                                </a>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        @else
            {{-- ================= Anak bimbingan baru (belum punya kelompok) ================= --}}
            <div class="ws-widget md:col-span-2 lg:col-span-5 ws-widget--callout" x-cloak x-show="data && data.ungrouped_interns && data.ungrouped_interns.length">
                <div class="ws-widget__head">
                    <span class="ws-widget__icon" style="--w-c:#b45309;--w-bg:#fffbeb;"><i class="fas fa-user-plus"></i></span>
                    <h2 class="ws-widget__title">Anak bimbingan baru</h2>
                    <span class="ws-widget__count" x-text="data && data.ungrouped_interns ? data.ungrouped_interns.length + ' belum punya kelompok' : ''"></span>
                    <button type="button" class="ws-widget__more" x-show="data && data.ungrouped_interns && data.ungrouped_interns.length > 1"
                            x-on:click="$dispatch('ws-group-create', { intern_ids: data.ungrouped_interns.map((i) => i.id) })">
                        Kelompokkan semua <i class="fas fa-arrow-right text-[10px]"></i>
                    </button>
                </div>
                <div class="ws-widget__body">
                    <p class="text-sm text-slate-600 mb-3">Masukkan ke kelompok agar mereka bisa menerima tugas dan ikut diskusi.</p>
                    <ul class="flex flex-wrap gap-2">
                        <template x-for="i in (data && data.ungrouped_interns ? data.ungrouped_interns : [])" :key="i.id">
                            <li class="ws-chip-person">
                                <x-workspace.avatar-js user="i" size="xs" />
                                <span class="text-sm font-medium text-gray-800" x-text="i.name"></span>
                                <button type="button" class="ws-chip-person__action" x-on:click="$dispatch('ws-group-assign', { intern: { id: i.id, name: i.name } })">
                                    Masukkan<span class="sr-only" x-text="' ' + i.name + ' ke kelompok'"></span>
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            {{-- ================= Perlu Review ================= --}}
            <div class="ws-widget lg:col-span-3">
                <div class="ws-widget__head">
                    <span class="ws-widget__icon" style="--w-c:#7c3aed;--w-bg:#f5f3ff;"><i class="fas fa-magnifying-glass"></i></span>
                    <h2 class="ws-widget__title">Perlu Review</h2>
                    <span class="ws-widget__count" x-cloak x-show="data && data.to_review && data.to_review.total" x-text="data && data.to_review ? data.to_review.total + ' tugas' : ''"></span>
                    <a class="ws-widget__more" href="{{ route('workspace.index') }}">Semua kelompok <i class="fas fa-arrow-right text-[10px]"></i></a>
                </div>
                <div class="ws-widget__body">
                    @include('workspace.partials.widget-states', ['rows' => 4])

                    <template x-if="!loading && !error && data && !data.to_review.data.length">
                        <p class="ws-widget__empty"><i class="fas fa-circle-check"></i> Tidak ada tugas yang menunggu review.</p>
                    </template>

                    <ul x-show="!loading && !error && data && data.to_review && data.to_review.data.length">
                        <template x-for="t in (data && data.to_review ? data.to_review.data : [])" :key="t.id">
                            <li class="ws-widget__row">
                                <span class="ws-avatar-stack shrink-0">
                                    <template x-for="a in t.assignees" :key="a.id">
                                        <x-workspace.avatar-js user="a" size="sm" />
                                    </template>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <a :href="boardUrl(t)" class="block py-0.5 text-sm font-semibold text-gray-800 truncate hover:text-indigo-700">
                                        <span class="ws-mono ws-text-faint font-medium mr-1" x-text="t.code"></span><span x-text="t.title"></span>
                                    </a>
                                    <span class="block text-xs ws-text-faint truncate">
                                        <span x-text="t.assignees.map((a) => a.name).join(', ')"></span> ·
                                        <span x-text="t.workspace.name"></span> ·
                                        <time :datetime="t.submitted_at" :title="dateTime(t.submitted_at)" x-text="'dikirim ' + relative(t.submitted_at)"></time>
                                    </span>
                                </span>
                                <a :href="boardUrl(t)" class="ws-btn ws-btn--soft ws-btn--sm shrink-0" :aria-label="'Review ' + t.code">Review</a>
                            </li>
                        </template>
                    </ul>
                    <p class="ws-widget__footnote" x-show="!loading && !error && data && data.to_review && data.to_review.total > data.to_review.data.length"
                       x-text="data && data.to_review ? '+' + (data.to_review.total - data.to_review.data.length) + ' tugas lain menunggu review' : ''"></p>
                </div>
            </div>

            {{-- ================= Tugas Terlambat per intern ================= --}}
            <div class="ws-widget lg:col-span-2">
                <div class="ws-widget__head">
                    <span class="ws-widget__icon" style="--w-c:#dc2626;--w-bg:#fef2f2;"><i class="fas fa-triangle-exclamation"></i></span>
                    <h2 class="ws-widget__title">Tugas Terlambat</h2>
                    <span class="ws-widget__count ws-widget__count--alert" x-cloak
                          x-show="data && data.late_by_intern && data.late_by_intern.length"
                          x-text="data && data.late_by_intern ? data.late_by_intern.reduce((n, l) => n + l.late_count, 0) : ''"></span>
                </div>
                <div class="ws-widget__body">
                    @include('workspace.partials.widget-states', ['rows' => 3])

                    <template x-if="!loading && !error && data && !data.late_by_intern.length">
                        <p class="ws-widget__empty"><i class="fas fa-face-smile"></i> Tidak ada tugas terlambat.</p>
                    </template>

                    <ul x-show="!loading && !error && data && data.late_by_intern && data.late_by_intern.length">
                        <template x-for="l in (data && data.late_by_intern ? data.late_by_intern : [])" :key="l.intern.id">
                            <li class="ws-widget__late">
                                <div class="flex items-center gap-3">
                                    <x-workspace.avatar-js user="l.intern" size="sm" />
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-semibold text-gray-800 truncate" x-text="l.intern.name"></span>
                                        <a :href="l.workspace.url + '?tab=list'" class="block py-1 text-xs text-indigo-600 hover:underline truncate" x-text="l.workspace.name"></a>
                                    </span>
                                    <span class="ws-badge ws-badge--late shrink-0" x-text="l.late_count + ' terlambat'"></span>
                                </div>
                                <ul class="mt-2 space-y-1 pl-11">
                                    <template x-for="t in l.tasks.slice(0, 2)" :key="t.id">
                                        <li class="text-xs truncate">
                                            <a :href="t.url" class="text-slate-600 hover:text-indigo-700">
                                                <span class="ws-mono font-medium" x-text="t.code"></span>
                                                <span x-text="t.title"></span>
                                                <span class="text-red-700" x-text="'· ' + relative(t.due_at)"></span>
                                            </a>
                                        </li>
                                    </template>
                                    <li class="text-xs ws-text-faint" x-show="l.tasks.length > 2" x-text="'+' + (l.tasks.length - 2) + ' lainnya'"></li>
                                </ul>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            {{-- ================= Deadline tugas 7 hari ke depan (per tugas, semua kelompok yang dibimbing) ================= --}}
            <div class="ws-widget md:col-span-2 lg:col-span-5">
                <div class="ws-widget__head">
                    <span class="ws-widget__icon" style="--w-c:#b45309;--w-bg:#fffbeb;"><i class="fas fa-calendar-days"></i></span>
                    <h2 class="ws-widget__title">Deadline 7 Hari ke Depan</h2>
                    <span class="ws-widget__count" x-cloak x-show="data && data.upcoming_deadlines && data.upcoming_deadlines.total"
                          x-text="data && data.upcoming_deadlines ? data.upcoming_deadlines.total + ' tugas' : ''"></span>
                    <a class="ws-widget__more" href="{{ route('workspace.index') }}">Semua kelompok <i class="fas fa-arrow-right text-[10px]"></i></a>
                </div>
                <div class="ws-widget__body">
                    @include('workspace.partials.widget-states', ['rows' => 3])

                    <template x-if="!loading && !error && data && data.upcoming_deadlines && !data.upcoming_deadlines.data.length">
                        <p class="ws-widget__empty"><i class="fas fa-calendar-check"></i> Tidak ada tugas yang jatuh tempo dalam 7 hari ke depan.</p>
                    </template>

                    <ul x-show="!loading && !error && data && data.upcoming_deadlines && data.upcoming_deadlines.data.length">
                        <template x-for="t in (data && data.upcoming_deadlines ? data.upcoming_deadlines.data : [])" :key="t.id">
                            <li>
                                <a :href="boardUrl(t)" class="ws-widget__row">
                                    <span class="ws-avatar-stack shrink-0">
                                        <template x-for="a in t.assignees" :key="a.id"><x-workspace.avatar-js user="a" size="sm" /></template>
                                        <span class="ws-bl-unassigned" x-show="!t.assignees.length" title="Belum dibagi"><i class="fas fa-user" aria-hidden="true"></i><span class="sr-only">Belum dibagi</span></span>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-semibold text-gray-800 truncate">
                                            <span class="ws-mono ws-text-faint font-medium mr-1" x-text="t.code"></span><span x-text="t.title"></span>
                                        </span>
                                        <span class="block text-xs ws-text-faint truncate">
                                            <span x-text="t.assignees.length ? t.assignees.map((a) => a.name).join(', ') : 'Belum dibagi'"></span> ·
                                            <span x-text="t.workspace.name"></span>
                                        </span>
                                    </span>
                                    <span class="ws-widget__due" :class="'ws-widget__due--' + due(t).state" :title="dateTime(t.due_at)" x-text="due(t).text"></span>
                                </a>
                            </li>
                        </template>
                    </ul>
                    <p class="ws-widget__footnote" x-show="!loading && !error && data && data.upcoming_deadlines && data.upcoming_deadlines.total > data.upcoming_deadlines.data.length"
                       x-text="data && data.upcoming_deadlines ? '+' + (data.upcoming_deadlines.total - data.upcoming_deadlines.data.length) + ' tugas lain dalam 7 hari ke depan' : ''"></p>
                </div>
            </div>

            {{-- ================= Progres kelompok (grafik mini) ================= --}}
            <div class="ws-widget lg:col-span-3">
                <div class="ws-widget__head">
                    <span class="ws-widget__icon" style="--w-c:#4f46e5;--w-bg:#eef2ff;"><i class="fas fa-chart-simple"></i></span>
                    <h2 class="ws-widget__title">Progres Kelompok</h2>
                    <a class="ws-widget__more" href="{{ route('workspace.index') }}">Semua kelompok <i class="fas fa-arrow-right text-[10px]"></i></a>
                </div>
                <div class="ws-widget__body">
                    @include('workspace.partials.widget-states', ['rows' => 3])

                    <template x-if="!loading && !error && data && data.group_progress && !data.group_progress.length">
                        <p class="ws-widget__empty"><i class="fas fa-users"></i> Belum ada kelompok aktif.</p>
                    </template>

                    <ul x-show="!loading && !error && data && data.group_progress && data.group_progress.length">
                        <template x-for="g in (data && data.group_progress ? data.group_progress : [])" :key="g.workspace.id">
                            <li>
                                <a :href="g.workspace.url + '?tab=laporan'" class="ws-widget__row">
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ background: g.workspace.color }" aria-hidden="true"></span>
                                            <span class="text-sm font-semibold text-gray-800 truncate" x-text="g.workspace.name"></span>
                                            <span class="ws-badge ws-badge--late !text-[11px] !px-2 !py-0 shrink-0" x-show="g.overdue" x-text="g.overdue + ' terlambat'"></span>
                                        </span>
                                        <span class="ws-progress mt-2 block" role="progressbar" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="pct(g)" :aria-label="'Progres ' + g.workspace.name">
                                            <span class="ws-progress__bar block" :style="{ width: pct(g) + '%' }"></span>
                                        </span>
                                    </span>
                                    <span class="ws-widget__due text-right" x-text="g.done + '/' + g.total + ' selesai'"></span>
                                </a>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            {{-- ================= Beban tugas aktif per anak bimbingan (grafik mini) ================= --}}
            <div class="ws-widget lg:col-span-2">
                <div class="ws-widget__head">
                    <span class="ws-widget__icon" style="--w-c:#0891b2;--w-bg:#ecfeff;"><i class="fas fa-layer-group"></i></span>
                    <h2 class="ws-widget__title">Beban Tugas Aktif</h2>
                </div>
                <div class="ws-widget__body">
                    @include('workspace.partials.widget-states', ['rows' => 3])

                    <template x-if="!loading && !error && data && data.workload && !data.workload.length">
                        <p class="ws-widget__empty"><i class="fas fa-circle-check"></i> Tidak ada tugas aktif.</p>
                    </template>

                    <ul class="ws-wl !gap-0" x-show="!loading && !error && data && data.workload && data.workload.length">
                        <template x-for="r in workloadRows" :key="r.intern.id">
                            <li>
                                <a :href="r.workspace.url + '?tab=laporan'" class="ws-wl__row !px-2 !py-2 rounded-xl hover:bg-slate-50">
                                    <span class="ws-wl__name"><x-workspace.avatar-js user="r.intern" size="xs" /><span class="truncate" x-text="r.intern.name.split(' ')[0]"></span></span>
                                    <span class="ws-wl__bar" role="img" :aria-label="r.intern.name + ': ' + r.segs.filter((s) => s.count).map((s) => s.count + ' ' + s.label).join(', ')">
                                        <template x-for="s in r.segs" :key="s.key"><span class="ws-wl__seg" x-show="s.count" :style="{ width: s.pct + '%', background: s.color }" :title="s.label + ': ' + s.count"></span></template>
                                    </span>
                                    <span class="ws-wl__total" x-text="r.total"></span>
                                </a>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        @endif
    </section>

    @if ($role === 'mentor')
        @include('workspace.partials.group-modals')
    @endif
@endif
