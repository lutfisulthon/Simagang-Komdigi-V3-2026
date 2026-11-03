{{-- Top bar desktop (lg ke atas): judul halaman + tanggal di kiri, aksi & profil di kanan. Di HP, lonceng dan gerigi ada di header mobile. --}}
@php
    $tbTitle = trim(preg_split('/\s*-\s*(Sistem|SIMAGANG)/i', $__env->yieldContent('title'))[0] ?? '') ?: 'Beranda';
    $tbUser = auth()->user();
    $tbMentor = $tbUser?->isMentor();
    $tbPhoto = $tbUser ? ($tbMentor ? $tbUser->mentor?->photo_path : ($tbUser->isIntern() ? $tbUser->intern?->photo_path : null)) : null;
    $tbProfile = match (true) {
        ! $tbUser => null,
        $tbMentor => route('mentor.profile.show'),
        $tbUser->isInstitusi() => route('institusi.profile.show'),
        $tbUser->isIntern() => route('intern.profile.show'),
        default => null,
    };
    $tbRole = match (true) {
        ! $tbUser => '',
        $tbUser->isSuperAdmin() => 'Super Admin',
        $tbUser->isAdmin() => 'Admin',
        $tbMentor => 'Mentor',
        $tbUser->isInstitusi() => 'Institusi',
        $tbUser->isIndustri() => 'Industri',
        default => 'Peserta Magang',
    };
@endphp
<header class="tb hidden lg:flex shrink-0">
    <div class="min-w-0">
        <h2 class="tb__title" @if (in_array($tbTitle, ['Beranda', 'Sistem Manajemen Peserta Magang'], true)) data-title-fallback @endif>{{ $tbTitle }}</h2>
        <p class="tb__date">
            @if ($pageMeta)
                <span class="tb__group" data-tone="{{ $pageMeta[0] }}">{{ $pageMeta[1] }}</span><span aria-hidden="true">·</span>
            @endif
            <i class="far fa-calendar"></i>{{ now()->locale('id')->translatedFormat('l, j F Y') }}
        </p>
    </div>

    <div class="tb__right">
        @if ($wsEnabled)
            @if ($wsMock)
                <span class="tb-chip hidden xl:inline-flex" title="Data berasal dari public/js/workspace/mock.js, bukan dari server">
                    <i class="fas fa-flask"></i> Mode preview · data contoh
                </span>
            @endif
            {{-- Status realtime: hanya tampil bila realtime dikonfigurasi (Reverb). Tanpa Reverb, aplikasi memakai polling. --}}
            <span class="ws-rt" x-data="{ s: WS.realtime ? WS.realtime.status : 'off' }" x-on:ws-realtime-status.window="s = $event.detail"
                  x-show="s !== 'off'" x-cloak role="status"
                  :title="s === 'connected' ? 'Pembaruan realtime aktif' : s === 'connecting' ? 'Menghubungkan realtime…' : 'Realtime terputus — memakai polling'">
                <span class="ws-rt__dot" :class="'ws-rt__dot--' + s" aria-hidden="true"></span>
                <span x-text="s === 'connected' ? 'Realtime' : s === 'connecting' ? 'Menghubungkan…' : 'Polling'"></span>
            </span>
            <x-workspace.notification-bell />
        @endif
        <x-theme-menu />

        @if ($tbUser)
            <span class="tb__sep" aria-hidden="true"></span>
            <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false">
                <button type="button" class="tb-user" x-on:click="open = !open" :aria-expanded="open.toString()" aria-haspopup="true">
                    @if ($tbPhoto)
                        <img src="{{ asset('storage/' . $tbPhoto) }}" alt="" class="sb-avatar">
                    @else
                        <span class="sb-avatar"><i class="fas {{ $tbUser->isIndustri() ? 'fa-building' : 'fa-user' }}"></i></span>
                    @endif
                    <span class="hidden xl:block">
                        <span class="tb-user__name">{{ $tbUser->name }}</span>
                        <span class="tb-user__role">{{ $tbRole }}</span>
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div x-cloak x-show="open" x-transition.opacity.duration.120ms class="tb-menu">
                    <div class="tb-menu__head">
                        <b>{{ $tbUser->name }}</b>
                        <span>{{ $tbUser->email }}</span>
                    </div>
                    @if ($tbProfile)
                        <a href="{{ $tbProfile }}" class="tb-menu__item"><i class="fas fa-user"></i> Profil saya</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="tb-menu__item tb-menu__item--danger"><i class="fas fa-right-from-bracket"></i> Logout</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</header>
