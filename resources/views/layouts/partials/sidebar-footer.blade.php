{{-- Kartu profil + logout di dasar sidebar (desktop & drawer mobile). --}}
<div class="sb-foot">
    @if (! empty($wsEnabled))
        <button type="button" class="sb-help" onclick="window.dispatchEvent(new CustomEvent('ws-guide-open', { detail: {} }))">
            <span class="sb-help__ico"><i class="fas fa-circle-question"></i></span>
            <span>Panduan penggunaan</span>
        </button>
    @endif
    @auth
        @php
            $sbUser = auth()->user();
            [$sbRole, $sbUrl, $sbPhoto] = match (true) {
                $sbUser->isSuperAdmin() || $sbUser->isAdmin() => [$sbUser->isSuperAdmin() ? 'Super Admin' : 'Admin', null, null],
                $sbUser->isInstitusi() => ['Institusi', route('institusi.profile.show'), null],
                $sbUser->isIndustri() => ['Industri', route('industri.dashboard'), null],
                $sbUser->isMentor() => ['Mentor', route('mentor.profile.show'), $sbUser->mentor?->photo_path],
                default => ['Peserta Magang', route('intern.profile.show'), $sbUser->intern?->photo_path],
            };
        @endphp
        <{{ $sbUrl ? 'a' : 'div' }} @if($sbUrl) href="{{ $sbUrl }}" @endif class="sb-user">
            @if ($sbPhoto)
                <img src="{{ asset('storage/' . $sbPhoto) }}" alt="Foto profil" class="sb-avatar">
            @else
                <span class="sb-avatar"><i class="fas {{ $sbUser->isIndustri() ? 'fa-building' : 'fa-user' }}"></i></span>
            @endif
            <span class="min-w-0 flex-1">
                <p class="sb-user__name">{{ $sbUser->name }}</p>
                <p class="sb-user__role">{{ $sbRole }}</p>
            </span>
        </{{ $sbUrl ? 'a' : 'div' }}>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sb-logout">
                <i class="fas fa-right-from-bracket w-4 text-center"></i>
                Logout
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="sb-logout">
            <i class="fas fa-right-to-bracket w-4 text-center"></i>
            Login
        </a>
    @endauth
</div>
