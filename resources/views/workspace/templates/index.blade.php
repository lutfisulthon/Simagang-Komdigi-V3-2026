{{--
    Template tugas: kelola + tugaskan massal (PRD Fitur 2 & onboarding Fitur 1).
    Variabel (lihat docs/workspace/kontrak-frontend.md §5):
      $templates          Template[]
      $teams              {id,name}[]   admin: semua tim; mentor: tim sendiri
      $assignableInterns  [{ id, user_id, name, avatar_url, team: {id,name}, space: {id,name,url}|null }]
      $labels             {id,name,color}[]
      $can                ['create' => bool, 'choose_team' => bool]
--}}
@extends('workspace.layout')

@section('title', 'Template Tugas · Workspace - Sistem Magang')

@section('workspace-content')
    @php
        $onboardingCount = collect($templates)->where('is_onboarding', true)->count();
    @endphp

    <div x-data="wsTemplatesPage(@js(['templates' => $templates, 'teams' => $teams]))">
        <nav class="mb-4 text-sm text-slate-600 flex items-center gap-2 min-w-0" aria-label="Breadcrumb">
            <a href="{{ route('workspace.index') }}" class="hover:text-indigo-600 shrink-0 py-1 -my-1"><i class="fas fa-layer-group mr-1"></i>Workspace</a>
            <i class="fas fa-chevron-right text-[10px] text-slate-300" aria-hidden="true"></i>
            <span class="font-medium text-slate-700" aria-current="page">Template tugas</span>
        </nav>

        <x-workspace.hero class="ws-anim-1 mb-6" eyebrow="Workspace" icon="copy" color="#06b6d4" title="Template Tugas"
                          subtitle="Simpan tugas yang sering diberikan, lalu tugaskan ke banyak intern sekaligus.">
            <x-slot:meta>
                <span class="inline-flex items-center gap-1.5"><i class="fas fa-copy"></i> <span x-text="templates.length + ' template'">{{ count($templates) }} template</span></span>
                <span class="opacity-50 hidden sm:inline">·</span>
                <span class="inline-flex items-center gap-1.5"><i class="fas fa-user-plus"></i> <span x-text="count('onboarding') + ' onboarding'">{{ $onboardingCount }} onboarding</span></span>
            </x-slot:meta>
            @if ($can['create'])
                <button type="button" class="ws-hero-btn" x-on:click="create()">
                    <i class="fas fa-plus"></i> Buat template
                </button>
            @endif
        </x-workspace.hero>

        {{-- Penjelasan onboarding --}}
        <div class="ws-anim-2 mb-6 flex items-start gap-3 bg-cyan-50 border border-cyan-100 text-cyan-900 px-5 py-4 rounded-2xl">
            <i class="fas fa-circle-info mt-0.5 text-cyan-700" aria-hidden="true"></i>
            <p class="text-sm">Template bertanda <strong>Onboarding</strong> otomatis ditugaskan ke setiap intern baru saat ia bergabung ke tim. Template lain dipakai lewat tombol <strong>Tugaskan</strong> atau opsi "Pakai template" saat membuat tugas di board.</p>
        </div>

        {{-- Filter --}}
        <div class="ws-anim-2 flex flex-col md:flex-row md:items-center gap-3 mb-5">
            <div class="ws-tabs self-start" role="tablist" aria-label="Jenis template">
                @foreach (['all' => 'Semua', 'onboarding' => 'Onboarding', 'other' => 'Lainnya'] as $key => $label)
                    <button type="button" role="tab" class="ws-tab" x-on:click="filter = '{{ $key }}'"
                            aria-selected="{{ $key === 'all' ? 'true' : 'false' }}" :aria-selected="(filter === '{{ $key }}').toString()">
                        {{ $label }} <span class="ws-tab__count" x-text="count('{{ $key }}')"></span>
                    </button>
                @endforeach
            </div>
            <div class="flex flex-col sm:flex-row gap-2 md:ml-auto">
                @if ($can['manage_labels'] ?? false)
                    <button type="button" class="ws-btn ws-btn--soft ws-btn--sm self-start sm:self-auto" x-on:click="manageLabels()">
                        <i class="fas fa-tags"></i> Label tim
                    </button>
                @endif
                @if (count($teams) > 1)
                    <select class="ws-input !py-2 !text-sm sm:w-48" x-model="team" aria-label="Filter tim">
                        <option value="">Semua tim</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team['id'] }}">{{ $team['name'] }}</option>
                        @endforeach
                    </select>
                @endif
                <div class="relative sm:w-64">
                    <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-xs ws-text-faint" aria-hidden="true"></i>
                    <input type="search" class="ws-input !py-2 !pl-9 !text-sm" x-model="q" placeholder="Cari template…" aria-label="Cari template">
                </div>
            </div>
        </div>

        {{-- Kosong --}}
        <template x-if="!visible.length">
            <div class="ws-panel">
                <template x-if="filtering">
                    <x-workspace.empty-state icon="fa-magnifying-glass" title="Tidak ada template yang cocok" description="Ubah kata kunci atau filter.">
                        <button type="button" class="ws-btn ws-btn--soft ws-btn--sm" x-on:click="reset()">Reset filter</button>
                    </x-workspace.empty-state>
                </template>
                <template x-if="!filtering">
                    <x-workspace.empty-state icon="fa-copy" title="Belum ada template" description="Buat template untuk tugas yang sering diberikan, misalnya laporan mingguan atau tugas onboarding.">
                        @if ($can['create'])
                            <button type="button" class="ws-btn ws-btn--primary ws-btn--sm" x-on:click="create()"><i class="fas fa-plus"></i> Buat template</button>
                        @endif
                    </x-workspace.empty-state>
                </template>
            </div>
        </template>

        {{-- Daftar --}}
        <ul class="grid md:grid-cols-2 xl:grid-cols-3 gap-4" x-show="visible.length">
            <template x-for="t in visible" :key="t.id">
                <li class="ws-tpl-card">
                    <div class="flex items-start gap-2 mb-2 min-h-[28px]">
                        <span class="ws-badge ws-tpl-badge--onboarding" x-show="t.is_onboarding"><i class="fas fa-user-plus text-[10px]"></i> Onboarding</span>
                        <span class="ws-badge ws-tpl-badge--team" x-show="teams.length > 1" x-text="t.team.name"></span>
                        <div class="relative ml-auto -mr-1 -mt-1" x-data="{ menu: false }" x-on:click.outside="menu = false"
                             x-on:keydown.escape="if (menu) { menu = false; $event.preventDefault(); $el.querySelector('button').focus(); }"
                             x-show="t.can.update || t.can.delete">
                            <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-on:click="menu = !menu" :aria-expanded="menu.toString()" :aria-label="'Aksi template ' + t.title">
                                <i class="fas fa-ellipsis"></i>
                            </button>
                            <div class="ws-menu" x-show="menu" x-cloak x-transition.opacity>
                                <template x-if="t.can.update">
                                    <button type="button" class="ws-menu__item" x-on:click="menu = false; edit(t)"><i class="fas fa-pen w-4"></i>Edit</button>
                                </template>
                                @if ($can['create'])
                                    <button type="button" class="ws-menu__item" x-on:click="menu = false; duplicate(t)"><i class="far fa-copy w-4"></i>Duplikat</button>
                                @endif
                                <template x-if="t.can.delete">
                                    <button type="button" class="ws-menu__item ws-menu__item--danger" x-on:click="menu = false; remove(t)"><i class="fas fa-trash w-4"></i>Hapus</button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <h2 class="font-bold text-gray-800 leading-snug" x-text="t.title"></h2>
                    <p class="text-sm text-slate-600 mt-1 line-clamp-2 min-h-[2.5rem]" x-text="excerpt(t) || 'Tanpa deskripsi.'"></p>

                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 mt-3 text-xs text-slate-600">
                        <span class="inline-flex items-center gap-1" :title="'Prioritas ' + priorityLabel(t.priority)">
                            <i class="fas text-[10px]" :class="priorityIcon(t.priority)"></i><span x-text="priorityLabel(t.priority)"></span>
                        </span>
                        <span class="inline-flex items-center gap-1"><i class="far fa-clock text-[10px]"></i><span x-text="'Deadline +' + t.due_in_days + ' hari'"></span></span>
                        <span class="inline-flex items-center gap-1" x-show="t.checklist.length"><i class="far fa-square-check text-[10px]"></i><span x-text="t.checklist.length + ' item'"></span></span>
                        <template x-for="l in t.labels" :key="l.id">
                            <span class="ws-label-chip"><span class="ws-label-chip__dot" :style="{ background: l.color }"></span><span x-text="l.name"></span></span>
                        </template>
                    </div>

                    <div class="flex items-center gap-3 mt-auto pt-4">
                        <span class="text-xs text-slate-500 min-w-0 truncate">
                            <span x-text="'Dipakai ' + t.usage_count + '×'"></span> ·
                            <time :datetime="t.updated_at" x-text="'diperbarui ' + relative(t.updated_at)"></time>
                        </span>
                        <button type="button" class="ws-btn ws-btn--primary ws-btn--sm ml-auto shrink-0" x-show="t.can.assign" x-on:click="assign(t)">
                            <i class="fas fa-paper-plane"></i> Tugaskan
                        </button>
                    </div>
                </li>
            </template>
        </ul>
    </div>

    @if ($can['manage_labels'] ?? false)
        @include('workspace.partials.label-manager', [
            'labelTeams' => $teams,
            'labelsByTeam' => collect($teams)->mapWithKeys(fn ($t) => [$t['id'] => $labels])->all(),
        ])
    @endif

    @include('workspace.partials.template-modals', [
        'tplTeams' => $teams,
        'canChooseTeam' => $can['choose_team'],
        'defaultTeamId' => count($teams) === 1 ? $teams[0]['id'] : null,
        'withAssign' => true,
        'assignInterns' => $assignableInterns,
    ])
@endsection

@push('scripts')
    <script>
        if (WS.config.mock && WS.mock) {
            WS.mock.seed('label-context', @js(['teams' => $teams, 'labels' => $labels]));
            WS.mock.seed('template-context', @js(['templates' => $templates, 'interns' => $assignableInterns, 'labels' => $labels, 'teams' => $teams]));
        }
    </script>
@endpush
