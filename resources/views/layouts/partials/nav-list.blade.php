{{--
    Render daftar menu sidebar berkelompok (dipakai admin, institusi, industri; mentor & intern punya partial sendiri dengan markup sama).
    Variabel: $navGroups = ['Judul grup' => [ ['route','match'[],'icon','label','tone', 'can'?, 'super'?, 'needs'?] ]].
    Warna (tone) didefinisikan di public/css/app-shell.css ([data-tone]).
--}}
@php
    $navUser = auth()->user();
@endphp
<div class="sb-nav">
    @foreach ($navGroups as $caption => $links)
        @php
            $links = array_filter($links, function ($l) use ($navUser, $wsEnabled) {
                if (($l['needs'] ?? null) === 'workspace' && ! $wsEnabled) return false;
                if (! empty($l['super']) && ! $navUser->isSuperAdmin()) return false;
                if (! empty($l['can']) && ! $navUser->can($l['can'])) return false;
                return \Illuminate\Support\Facades\Route::has($l['route']);
            });
        @endphp
        @continue(empty($links))
        <p class="sb-cap">{{ $caption }}</p>
        @foreach ($links as $l)
            @php $active = request()->routeIs(...$l['match']); @endphp
            <a href="{{ route($l['route']) }}" data-tone="{{ $l['tone'] }}"
               class="sb-link {{ $active ? 'is-active' : '' }}" @if($active) aria-current="page" @endif>
                <span class="sb-ico"><i class="fas {{ $l['icon'] }}"></i></span>
                <span>{{ $l['label'] }}</span>
            </a>
        @endforeach
    @endforeach
</div>
