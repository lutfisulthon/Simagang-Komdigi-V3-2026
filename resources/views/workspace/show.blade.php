{{--
    Halaman kelompok (workspace) dengan tab Backlog / Board / List / Timeline / Deadline / Laporan / Otomasi (staff) / Channel / Aktivitas.
    Variabel (lihat docs/workspace/kontrak-frontend.md §5):
      $workspace   Workspace type=group (can: rename, add_members, finish)
      $candidates  Roster[]    anak bimbingan tanpa kelompok (untuk "Tambah anggota"; kosong bila tidak berhak)
      $members     UserMini[]
      $activities  Activity[]  (20 terbaru; lanjutan lewat GET /workspace/{id}/activities)
      $initialTasks       TaskCard[]   tugas aktif + Selesai terbaru
      $assignableInterns  InternMini[]
      $labels             {id,name,color}[]
      $channels           Channel[]    channel workspace ini + #pengumuman
      $templates          Template[]   template tim (untuk "Pakai template"); kosong untuk intern
      $sprints     Sprint[]    sprint kelompok (planned, active, completed) — tab Backlog & board
      $tab         'backlog'|'board'|'list'|'timeline'|'deadline'|'laporan'|'otomasi'|'channel'|'activity'
      $can         ['create_task', 'manage_channels', 'create_template', 'manage_labels' => bool]
--}}
@extends('workspace.layout')

@section('title', $workspace['name'] . ' · Workspace - Sistem Magang')
@section('workspace-wide', true)

@section('workspace-content')
    @php
        $s = $workspace['stats'];
        $groupCan = $workspace['can'] ?? [];
        $canManage = in_array(true, $groupCan, true);
        $groupMini = ['id' => $workspace['id'], 'name' => $workspace['name'], 'color' => $workspace['color']];
        $date = fn ($iso) => \Illuminate\Support\Carbon::parse($iso)->locale('id')->translatedFormat('j M Y');
        $initials = collect(preg_split('/\s+/', trim($workspace['name'])))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
        $activeTasks = $s['todo'] + $s['in_progress'] + $s['review'] + $s['revision'];
        $canAutomate = ($can['manage_sprints'] ?? false) && ! $workspace['is_read_only'];
        $isIntern = auth()->user()->isIntern();
        $tabs = array_filter([
            'backlog' => $isIntern ? null : ['Backlog', 'fa-layer-group', null, false],
            'board' => ['Board', 'fa-table-columns', $activeTasks, false],
            'list' => ['List', 'fa-list-ul', null, false],
            'timeline' => ['Timeline', 'fa-chart-gantt', null, false],
            'deadline' => ['Deadline', 'fa-calendar-days', null, true],
            'laporan' => ['Laporan', 'fa-chart-pie', null, false],
            'otomasi' => $canAutomate ? ['Otomasi', 'fa-bolt', null, false] : null,
            'channel' => ['Channel', 'fa-hashtag', $workspace['unread_messages'] ?: null, true],
            'activity' => ['Aktivitas', 'fa-clock-rotate-left', null, false],
        ]);
        $statTiles = [
            ['todo', 'To Do', $s['todo'], '#94a3b8'],
            ['in_progress', 'In Progress', $s['in_progress'], '#3b82f6'],
            ['review', 'Review', $s['review'], '#8b5cf6'],
            ['revision', 'Revisi', $s['revision'], '#f97316'],
            ['done', 'Selesai', $s['done'], '#10b981'],
            ['late', 'Terlambat', $s['late'], '#ef4444'],
        ];
        $mentorMembers = collect($members)->where('role', 'mentor')->values();
        // Workspace tim punya banyak mentor: tampilkan dua nama pertama, sisanya sebagai hitungan.
        $mentorNames = $mentorMembers->pluck('name');
        $mentorLine = $mentorNames->take(2)->implode(', ') . ($mentorNames->count() > 2 ? ' +' . ($mentorNames->count() - 2) . ' lainnya' : '');
        $internMembers = collect($members)->where('role', 'intern')->values();
        $previewMembers = collect($members)->take(4);
    @endphp

    {{-- Boot store tugas sebelum board/list diinisialisasi (urutan DOM). --}}
    <div hidden x-data x-init="$store.wsTasks.boot(@js(['workspace' => $workspace, 'tasks' => $initialTasks, 'sprints' => $sprints ?? [], 'interns' => $assignableInterns]))"></div>
    <div hidden x-data x-init="$store.wsChat.boot(@js(['workspaceId' => $workspace['id'], 'channels' => $channels, 'readOnly' => $workspace['is_read_only']]))"></div>

    <div x-data="wsWorkspaceShow(@js(['tab' => $tab, 'tabs' => array_keys($tabs)]))">
        <nav class="mb-4 text-sm text-slate-600 flex items-center gap-2 min-w-0" aria-label="Breadcrumb">
            <a href="{{ route('workspace.index') }}" class="hover:text-indigo-600 shrink-0 py-1 -my-1"><i class="fas fa-layer-group mr-1"></i>{{ auth()->user()->isIntern() ? 'Workspace' : 'Kelompok' }}</a>
            <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
            <span class="font-medium text-slate-700 truncate" aria-current="page">{{ $workspace['name'] }}</span>
        </nav>

        <x-workspace.hero class="ws-anim-1 mb-6"
                          :eyebrow="'Kelompok' . ($workspace['team'] && ! ($workspace['is_personal'] ?? false) ? ' · ' . $workspace['team']['name'] : '')"
                          :title="$workspace['name']"
                          :subtitle="$workspace['description'] ?: 'Board tugas dan diskusi kelompok'">
            <x-slot:meta>
                <span class="inline-flex items-center gap-1.5"><i class="fas fa-chalkboard-user"></i> Mentor: <span title="{{ $mentorNames->implode(', ') }}">{{ $mentorLine ?: $workspace['mentor']['name'] }}</span></span>
                <span class="opacity-50 hidden sm:inline">·</span>
                {{-- Space pribadi hanya berisi satu intern: jumlah anak magang tidak perlu ditampilkan. --}}
                @unless ($workspace['is_personal'] ?? false)
                    <span class="inline-flex items-center gap-1.5"><i class="fas fa-user-graduate"></i> {{ $internMembers->count() }} anak magang</span>
                    <span class="opacity-50 hidden sm:inline">·</span>
                @endunless
                @if ($workspace['status'] === 'finished')
                    <span class="inline-flex items-center gap-1.5"><i class="fas fa-flag-checkered"></i> Selesai {{ $date($workspace['finished_at']) }}</span>
                @elseif ($workspace['end_date'])
                    <span class="inline-flex items-center gap-1.5"><i class="far fa-calendar"></i> Magang s.d. {{ $date($workspace['end_date']) }}</span>
                @endif
                {{-- Deadline tugas: lewat deadline (merah) atau yang terdekat. Klik → tab Deadline. --}}
                @unless ($workspace['is_read_only'])
                    <button type="button" class="ws-hero-deadline" x-cloak
                            x-show="$store.wsTasks.booted && ($store.wsTasks.overdueCount > 0 || $store.wsTasks.nextDue)"
                            :class="$store.wsTasks.overdueCount > 0 && 'ws-hero-deadline--late'"
                            :title="$store.wsTasks.nextDue ? $store.wsTasks.nextDue.title + ' · ' + WS.time.dateTime($store.wsTasks.nextDue.due_at) : ''"
                            x-on:click="$dispatch('ws-select-tab', 'deadline')">
                        <i class="fas" :class="$store.wsTasks.overdueCount > 0 ? 'fa-triangle-exclamation' : 'fa-clock'" aria-hidden="true"></i>
                        <span x-text="$store.wsTasks.deadlineLine"></span>
                    </button>
                @endunless
            </x-slot:meta>

            <button type="button" class="ws-hero-btn" x-on:click="$dispatch('ws-open-modal', 'ws-members')">
                <span class="ws-avatar-stack">
                    @foreach ($previewMembers as $member)
                        <x-workspace.avatar :name="$member['name']" :src="$member['avatar_url']" size="xs" />
                    @endforeach
                </span>
                {{ count($members) }} anggota
            </button>
            @if ($canManage)
                <div class="relative" x-data="{ menu: false }" x-on:click.outside="menu = false"
                     x-on:keydown.escape="if (menu) { menu = false; $refs.manage.focus(); }">
                    <button type="button" class="ws-hero-btn" x-ref="manage" x-on:click="menu = !menu" :aria-expanded="menu.toString()" aria-haspopup="true">
                        <i class="fas fa-sliders"></i> Kelola <i class="fas fa-chevron-down text-[10px]"></i>
                    </button>
                    <div class="ws-menu right-0 left-auto" x-show="menu" x-cloak x-transition.opacity>
                        @if ($groupCan['rename'] ?? false)
                            <button type="button" class="ws-menu__item" x-on:click="menu = false; $dispatch('ws-group-rename', { group: @js($groupMini) })">
                                <i class="fas fa-pen text-xs ws-text-faint"></i> Ganti nama
                            </button>
                        @endif
                        @if ($groupCan['add_members'] ?? false)
                            <button type="button" class="ws-menu__item" x-on:click="menu = false; $dispatch('ws-group-add', { group: @js($groupMini) })">
                                <i class="fas fa-user-plus text-xs ws-text-faint"></i> Tambah anggota
                            </button>
                        @endif
                        @if ($canAutomate)
                            <button type="button" class="ws-menu__item" x-on:click="menu = false; $dispatch('ws-select-tab', 'otomasi')">
                                <i class="fas fa-bolt text-xs ws-text-faint"></i> Otomasi
                            </button>
                        @endif
                        @if ($groupCan['finish'] ?? false)
                            <button type="button" class="ws-menu__item" x-on:click="menu = false; $dispatch('ws-group-finish', { group: @js($groupMini) })">
                                <i class="fas fa-flag-checkered text-xs ws-text-faint"></i> Tandai selesai magang
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </x-workspace.hero>

        @if ($workspace['is_read_only'])
            <div class="ws-anim-2 mb-6 flex items-start gap-4 bg-slate-50 border border-slate-200 text-slate-700 px-5 py-4 rounded-2xl" role="status">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-200 shrink-0">
                    <i class="fas fa-flag-checkered text-slate-500"></i>
                </span>
                <div>
                    <p class="font-semibold text-sm mb-1">Kelompok selesai magang · hanya baca</p>
                    <p class="text-sm text-slate-500">Tugas, diskusi, dan nilai tetap tersimpan, tetapi tidak bisa diubah lagi.</p>
                </div>
            </div>
        @endif

        {{-- Statistik --}}
        <div class="ws-anim-2 ws-stats grid grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3 mb-5 sm:mb-6">
            @foreach ($statTiles as [$key, $label, $value, $color])
                <div class="ws-stat" style="--stat-color: {{ $color }}">
                    <p class="ws-stat__label">{{ $label }}</p>
                    <p class="ws-stat__value" x-text="$store.wsTasks.booted ? $store.wsTasks.stat('{{ $key }}', {{ $value }}) : {{ $value }}"
                       @if ($key === 'late') :style="$store.wsTasks.stat('late', 0) > 0 && 'color:#dc2626'" @endif>{{ $value }}</p>
                </div>
            @endforeach
        </div>

        {{-- Tab --}}
        <div id="ws-tabbar" class="ws-anim-3 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="ws-tabs" role="tablist" aria-label="Bagian workspace" x-on:keydown="onKey($event)">
                @foreach ($tabs as $key => [$label, $icon, $count, $alert])
                    <button type="button" role="tab" class="ws-tab"
                            id="ws-tab-{{ $key }}" data-tab="{{ $key }}" aria-controls="ws-panel-{{ $key }}"
                            aria-selected="{{ $tab === $key ? 'true' : 'false' }}" tabindex="{{ $tab === $key ? 0 : -1 }}"
                            :aria-selected="(tab === '{{ $key }}').toString()" :tabindex="tab === '{{ $key }}' ? 0 : -1"
                            x-on:click="select('{{ $key }}')">
                        <i class="fas {{ $icon }} text-xs" aria-hidden="true"></i>
                        <span class="ws-tab__label">{{ $label }}</span>
                        @if ($key === 'board')
                            <span class="ws-tab__count" x-text="$store.wsTasks.booted ? $store.wsTasks.activeCount : {{ $count }}">{{ $count }}</span>
                        @elseif ($key === 'deadline')
                            {{-- Jumlah tugas lewat deadline (hidup dari store); disembunyikan bila 0. --}}
                            <span class="ws-tab__count ws-tab__count--alert" style="display: none"
                                  x-show="$store.wsTasks.booted && $store.wsTasks.overdueCount > 0"
                                  x-text="$store.wsTasks.overdueCount"></span>
                        @elseif ($key === 'channel')
                            {{-- Ikut berubah saat channel dibaca / ada pesan baru (store wsChat). --}}
                            <span class="ws-tab__count ws-tab__count--alert" @unless ($count) style="display: none" @endunless
                                  x-show="($store.wsChat.booted ? $store.wsChat.unreadTotal : {{ (int) $count }}) > 0"
                                  x-text="$store.wsChat.booted ? $store.wsChat.unreadTotal : {{ (int) $count }}">{{ $count }}</span>
                        @elseif ($count)
                            <span class="ws-tab__count {{ $alert ? 'ws-tab__count--alert' : '' }}">{{ $count }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            @if ($can['create_task'])
                <button type="button" class="ws-btn ws-btn--primary ws-btn--sm self-start sm:self-auto" x-on:click="$dispatch('ws-open-task-form', {})">
                    <i class="fas fa-plus"></i> Buat tugas
                </button>
            @endif
        </div>

        <section id="ws-panel-backlog" role="tabpanel" aria-labelledby="ws-tab-backlog"
                 x-show="tab === 'backlog'" @if ($tab !== 'backlog') x-cloak @endif>
            @include('workspace.partials.backlog')
        </section>

        <section id="ws-panel-board" role="tabpanel" aria-labelledby="ws-tab-board"
                 x-show="tab === 'board'" @if ($tab !== 'board') x-cloak @endif>
            @if ($workspace['is_read_only'])
                <p class="text-xs text-slate-500 mb-3"><i class="fas fa-lock mr-1"></i>Board hanya baca — kartu tidak bisa dipindahkan.</p>
            @endif
            @include('workspace.partials.board')
        </section>

        <section id="ws-panel-list" role="tabpanel" aria-labelledby="ws-tab-list"
                 x-show="tab === 'list'" @if ($tab !== 'list') x-cloak @endif>
            @include('workspace.partials.task-list')
        </section>

        <section id="ws-panel-timeline" role="tabpanel" aria-labelledby="ws-tab-timeline"
                 x-show="tab === 'timeline'" @if ($tab !== 'timeline') x-cloak @endif>
            @include('workspace.partials.timeline')
        </section>

        @if ($canAutomate)
            <section id="ws-panel-otomasi" role="tabpanel" aria-labelledby="ws-tab-otomasi"
                     x-show="tab === 'otomasi'" @if ($tab !== 'otomasi') x-cloak @endif>
                @include('workspace.partials.automations')
            </section>
        @endif

        <section id="ws-panel-laporan" role="tabpanel" aria-labelledby="ws-tab-laporan"
                 x-show="tab === 'laporan'" @if ($tab !== 'laporan') x-cloak @endif>
            @include('workspace.partials.reports')
        </section>

        <section id="ws-panel-deadline" role="tabpanel" aria-labelledby="ws-tab-deadline"
                 x-show="tab === 'deadline'" @if ($tab !== 'deadline') x-cloak @endif>
            @include('workspace.partials.deadlines')
        </section>

        <section id="ws-panel-channel" role="tabpanel" aria-labelledby="ws-tab-channel"
                 x-show="tab === 'channel'" @if ($tab !== 'channel') x-cloak @endif>
            @include('workspace.partials.channel')
        </section>

        {{-- Aktivitas --}}
        <section id="ws-panel-activity" role="tabpanel" aria-labelledby="ws-tab-activity"
                 x-show="tab === 'activity'" @if ($tab !== 'activity') x-cloak @endif
                 class="ws-panel" x-data="wsActivityFeed({{ $workspace['id'] }}, @js($activities))">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-bold text-gray-800">Aktivitas terbaru</h2>
                <span class="text-xs ws-text-faint">Riwayat perubahan tugas</span>
            </div>

            <template x-if="items.length === 0">
                <x-workspace.empty-state icon="fa-clock-rotate-left" title="Belum ada aktivitas" description="Perubahan tugas di workspace ini akan muncul di sini." />
            </template>

            <template x-for="group in groups" :key="group.label">
                <div class="mb-6 last:mb-2">
                    <p class="ws-section-label mb-4" x-text="group.label"></p>
                    <ol class="ws-timeline space-y-5">
                        <template x-for="item in group.items" :key="item.id">
                            <li class="ws-timeline__item" x-data="{ d: describe(item) }">
                                <span class="ws-timeline__dot" :style="{ background: d.bg, color: d.color }">
                                    <i class="fas" :class="d.icon"></i>
                                </span>
                                <p class="text-sm text-slate-600 leading-relaxed">
                                    <span class="font-semibold text-gray-800" x-text="d.actor"></span>
                                    <span x-text="d.before"></span>
                                    <a :href="item.task.url" class="ws-mono text-[13px] font-medium text-indigo-600 hover:underline" x-text="item.task.code"></a>
                                    <span x-text="d.after"></span>
                                </p>
                                <p class="text-xs ws-text-faint mt-0.5 truncate">
                                    <span x-text="item.task.title"></span>
                                    · <time :datetime="item.created_at" :title="fullTime(item.created_at)" x-text="relative(item.created_at)"></time>
                                </p>
                            </li>
                        </template>
                    </ol>
                </div>
            </template>

            <div class="pt-2 text-center" x-show="hasMore">
                <button type="button" class="ws-btn ws-btn--soft ws-btn--sm" x-on:click="loadMore()" :disabled="loading">
                    <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-angles-down'"></i>
                    <span x-text="loading ? 'Memuat…' : 'Muat aktivitas lebih lama'"></span>
                </button>
            </div>
        </section>

        {{-- Anggota --}}
        <x-workspace.slide-over name="ws-members" :title="'Anggota · ' . count($members)">
            @foreach ([['Mentor', $mentorMembers], ['Intern', $internMembers]] as [$groupLabel, $groupMembers])
                @if ($groupMembers->isNotEmpty())
                    <p class="ws-section-label mb-3 {{ $loop->first ? '' : 'mt-6' }}">{{ $groupLabel }} · {{ $groupMembers->count() }}</p>
                    <ul class="space-y-2">
                        @foreach ($groupMembers as $member)
                            <li class="ws-tile flex items-center gap-3">
                                <x-workspace.avatar :name="$member['name']" :src="$member['avatar_url']" size="sm" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-800 truncate">
                                        {{ $member['name'] }}
                                        @if ($member['id'] === auth()->id())
                                            <span class="text-xs font-medium text-indigo-600">(Anda)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $member['role'] === 'mentor' ? 'Mentor' : 'Peserta magang' }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endforeach

            @if ($groupCan['add_members'] ?? false)
                <button type="button" class="ws-btn ws-btn--soft ws-btn--sm mt-5 w-full justify-center"
                        x-on:click="$dispatch('ws-group-add', { group: @js($groupMini) })" @disabled(! count($candidates))>
                    <i class="fas fa-user-plus"></i> Tambah anggota
                </button>
                @unless (count($candidates))
                    <p class="mt-2 text-xs text-slate-500 text-center">Semua anak bimbingan Anda sudah punya kelompok.</p>
                @endunless
            @endif
            <p class="mt-6 text-xs ws-text-faint flex items-start gap-2">
                <i class="fas fa-circle-info mt-0.5"></i>
                Satu anak magang hanya berada di satu kelompok. Anggota tidak dapat dikeluarkan dari kelompok.
            </p>
        </x-workspace.slide-over>
    </div>

    {{-- Detail tugas (slide-over) — URL ikut berubah: ?task=T-12 --}}
    <div x-data="wsTaskDetail({ mode: 'panel' })">
        <x-workspace.slide-over name="ws-task-detail" title="Detail tugas" x-model="$store.wsTasks.detailOpen">
            @include('workspace.partials.task-detail', ['mode' => 'panel', 'canTemplate' => $can['create_template'] ?? false])
        </x-workspace.slide-over>
    </div>

    @include('workspace.partials.task-modals')

    @if ($canManage)
        @include('workspace.partials.group-modals')
    @endif

    @if ($can['manage_sprints'] ?? false)
        @include('workspace.partials.sprint-modals')
    @endif

    @if (($can['manage_labels'] ?? false) && $workspace['team'])
        @include('workspace.partials.label-manager', ['labelTeams' => [$workspace['team']], 'labelsByTeam' => [$workspace['team']['id'] => $labels]])
    @endif

    @if (($can['create_template'] ?? false) && $workspace['team'])
        @include('workspace.partials.template-modals', [
            'tplTeams' => [$workspace['team']], 'canChooseTeam' => false, 'defaultTeamId' => $workspace['team']['id'], 'withAssign' => false,
        ])
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        if (WS.config.mock && WS.mock) {
            WS.mock.seed('sprint-context', @js(['workspace_id' => $workspace['id'], 'sprints' => $sprints ?? [], 'members' => $assignableInterns]));
            WS.mock.seed('task-context', @js(['interns' => $assignableInterns, 'labels' => $labels, 'workspace' => ['id' => $workspace['id'], 'name' => $workspace['name'], 'type' => $workspace['type'], 'url' => $workspace['url']]]));
            WS.mock.seed('label-context', @js(['teams' => $workspace['team'] ? [$workspace['team']] : [], 'labels' => $labels]));
            WS.mock.seed('template-context', @js(['templates' => $templates ?? [], 'labels' => $labels, 'teams' => $workspace['team'] ? [$workspace['team']] : []]));
            WS.mock.seed('channel-context', @js([
                'workspace' => ['id' => $workspace['id'], 'name' => $workspace['name'], 'url' => $workspace['url']],
                'members' => $members,
                'channels' => $channels,
                'tasks' => collect($initialTasks)->map(fn ($t) => ['id' => $t['id'], 'code' => $t['code'], 'url' => $t['url']])->values(),
            ]));
        }
    </script>
@endpush
