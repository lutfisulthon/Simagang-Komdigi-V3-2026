{{--
    Kartu kelompok di halaman daftar.
    Variabel: $ws (Workspace type=group), $showMentor (bool) — admin melihat nama mentor.
--}}
@php
    $s = $ws['stats'];
    $total = $s['todo'] + $s['in_progress'] + $s['review'] + $s['revision'] + $s['done'];
    $percent = $total ? round($s['done'] / $total * 100) : 0;
    $finished = $ws['status'] === 'finished';
    $interns = collect($ws['members_preview'])->where('role', 'intern');
    // interns_count dari server; cadangan lama mengira satu mentor per kelompok.
    $internCount = $ws['interns_count'] ?? ($ws['members_count'] - 1);
    $mentorList = $ws['mentors'] ?? [$ws['mentor']];
    $mentorLabel = $mentorList[0]['name'] . (count($mentorList) > 1 ? ' +' . (count($mentorList) - 1) : '');
    $extra = max(0, $internCount - $interns->count());
    $date = fn ($iso) => \Illuminate\Support\Carbon::parse($iso)->locale('id');
    $initials = collect(preg_split('/\s+/', trim($ws['name'])))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    // Deadline per tugas (kontrak #20): tugas terdekat yang belum lewat + jumlah tugas lewat deadline.
    $dl = $ws['deadline'] ?? null;
    $next = $dl['next'] ?? null;
    if ($next) {
        $nextDue = \Illuminate\Support\Carbon::parse($next['due_at'])->locale('id');
        $nextDays = (int) now()->startOfDay()->diffInDays($nextDue->copy()->startOfDay(), false);
        $nextState = $nextDays <= 3 ? 'soon' : 'normal';
        $nextText = $nextDays <= 0 ? 'hari ini ' . $nextDue->format('H.i') : ($nextDays === 1 ? 'besok' : $nextDays . ' hari lagi');
    }
@endphp

<a href="{{ $ws['url'] }}" class="ws-card group {{ $finished ? 'ws-card--finished' : '' }}">
    <div class="flex items-start gap-3">
        <span class="ws-group-icon" style="--group-color: {{ $ws['color'] }}" aria-hidden="true">{{ $initials }}</span>
        <div class="min-w-0 flex-1">
            @if ($ws['team'] && ! ($ws['is_personal'] ?? false))
                <p class="text-[10px] font-bold uppercase tracking-[0.12em] ws-text-faint truncate">{{ $ws['team']['name'] }}</p>
            @endif
            <h3 class="font-bold text-gray-800 truncate" title="{{ $ws['name'] }}">{{ $ws['name'] }}</h3>
            <p class="text-xs text-slate-500 truncate">
                @if ($showMentor)
                    Mentor: {{ $mentorLabel }}
                @elseif ($finished)
                    Selesai {{ $date($ws['finished_at'])->translatedFormat('j M Y') }} · {{ $s['done'] }} tugas selesai
                @else
                    Dibuat {{ $date($ws['created_at'])->diffForHumans() }}
                @endif
            </p>
        </div>
        @if ($ws['unread_messages'] > 0)
            <span class="ws-pill ws-pill--alert shrink-0" title="{{ $ws['unread_messages'] }} pesan belum dibaca">
                <i class="fas fa-comment-dots text-[10px]"></i>{{ $ws['unread_messages'] > 99 ? '99+' : $ws['unread_messages'] }}
            </span>
        @endif
    </div>

    @if ($finished)
        {{-- Ringkas: tanggal & jumlah tugas sudah ada di subjudul. --}}
    @elseif ($total)
        <div class="mt-5">
            <div class="flex items-center justify-between text-xs">
                <span class="font-semibold text-slate-500">Progres tugas</span>
                <span class="ws-mono text-slate-600">{{ $s['done'] }}/{{ $total }} selesai</span>
            </div>
            <div class="ws-progress mt-2" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progres tugas {{ $percent }}%">
                <div class="ws-progress__bar" style="width: {{ $percent }}%"></div>
            </div>
        </div>
        @if ($s['review'] || $s['revision'] || (! $dl && $s['late']))
            <div class="mt-3 flex flex-wrap gap-1.5">
                @if ($s['review'])
                    <span class="ws-badge ws-status--review">{{ $s['review'] }} menunggu review</span>
                @endif
                @if ($s['revision'])
                    <span class="ws-badge ws-status--revision">{{ $s['revision'] }} revisi</span>
                @endif
                @if (! $dl && $s['late'])
                    <span class="ws-badge ws-badge--late"><i class="fas fa-hourglass-end text-[10px]"></i>{{ $s['late'] }} belum diselesaikan</span>
                @endif
            </div>
        @endif
        @if ($dl)
            <div class="ws-card__deadline">
                @if ($dl['overdue_count'])
                    <span class="ws-badge ws-badge--late"><i class="fas fa-hourglass-end text-[10px]"></i>{{ $dl['overdue_count'] }} lewat deadline</span>
                @endif
                @if ($next)
                    <span class="ws-due ws-due--{{ $nextState }}" title="{{ $next['title'] }} · {{ $nextDue->translatedFormat('j M Y, H.i') }}">
                        <i class="far fa-clock" aria-hidden="true"></i>
                        <span>Terdekat <span class="ws-mono">{{ $next['code'] }}</span> · {{ $nextText }}</span>
                    </span>
                @elseif (! $dl['overdue_count'])
                    <span class="ws-due"><i class="far fa-circle-check" aria-hidden="true"></i>Tidak ada deadline tersisa</span>
                @endif
            </div>
        @endif
    @else
        <p class="mt-5 text-xs text-slate-500"><i class="fas fa-clipboard-list mr-1"></i>Belum ada tugas. Buka kelompok untuk membuat tugas pertama.</p>
    @endif

    <div class="ws-card__foot">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2 min-w-0">
                <span class="ws-avatar-stack">
                    @foreach ($interns as $member)
                        <x-workspace.avatar :name="$member['name']" :src="$member['avatar_url']" size="xs" />
                    @endforeach
                    @if ($extra)
                        <span class="ws-avatar ws-avatar--xs" style="background:#e2e8f0;color:#475569;">+{{ $extra }}</span>
                    @endif
                </span>
                <span class="text-xs text-slate-500 truncate">{{ $internCount }} anak magang</span>
            </div>
            @if ($finished)
                <span class="ws-badge shrink-0 ml-auto !bg-slate-100 !text-slate-600 !border-slate-200"><i class="fas fa-flag-checkered text-[10px]"></i>Selesai</span>
            @endif
            <span class="text-sm font-semibold text-indigo-600 flex items-center gap-1.5 shrink-0">
                Buka <i class="fas fa-arrow-right text-xs transition-transform group-hover:translate-x-0.5"></i>
            </span>
        </div>
    </div>
</a>
