{{--
    Satu item menu sidebar (dipakai nav-list, mentor-nav, intern-nav; desktop dan drawer mobile).
    Variabel: $l (entri menu), $active (bool).
    Entri ber-flag 'badge' => 'chat' menampilkan lencana merah jumlah pesan belum dibaca (menu Chat Publik);
    angkanya diisi public/js/public-chat.js lewat atribut data-pc-badge dan tersembunyi saat tidak ada pesan baru.
--}}
<a href="{{ route($l['route']) }}" data-tone="{{ $l['tone'] }}"
   class="sb-link {{ $active ? 'is-active' : '' }}" @if($active) aria-current="page" @endif>
    <span class="sb-ico"><i class="fas {{ $l['icon'] }}"></i></span>
    <span>{{ $l['label'] }}</span>
    @if (($l['badge'] ?? null) === 'chat')
        <span class="sb-badge" data-pc-badge hidden role="status"></span>
    @endif
</a>
