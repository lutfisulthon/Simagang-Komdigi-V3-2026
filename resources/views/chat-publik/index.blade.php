{{--
    Halaman Chat Publik: ruang obrolan bersama untuk semua pengguna yang sudah login (admin, mentor, peserta, institusi, industri).
    Tata letak dua panel (struktur mirip aplikasi chat): kiri pencarian + daftar orang di ruang, kanan percakapan dengan bilah pengumuman tersemat.
    Data saat ini contoh di browser (lihat docs/public-chat-backend.md). Perilaku: public/js/public-chat.js, gaya: public/css/public-chat.css.
--}}
@extends('layouts.app')

@section('title', 'Chat Publik - Sistem Manajemen Peserta Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/public-chat.css') }}?v={{ @filemtime(public_path('css/public-chat.css')) }}">
@endpush

@section('content')
    @php $pcIsMentor = auth()->user()?->isMentor(); @endphp
    <div id="pc-root" class="pc-root pcp" data-dm-native data-pc-mode="page">
        <div class="pcp__grid">

            {{-- ===== Kiri: pencarian + daftar orang di ruang ===== --}}
            <aside id="pc-listpane" class="pcl" aria-label="Pencarian dan daftar orang di ruang">
                <div class="pcl__top">
                    <button type="button" id="pc-list-close" class="pcp__iconbtn pcl__back" aria-label="Kembali ke percakapan">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                    </button>
                    <label class="pcl__search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.500-3.500"/></svg>
                        <input id="pc-search" type="search" placeholder="Cari orang atau pesan" aria-label="Cari orang atau pesan" autocomplete="off">
                    </label>
                </div>
                <div id="pc-chips" class="pcl__chips" role="group" aria-label="Saring menurut peran"></div>

                <div id="pc-rules" class="pc-rules" role="note">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0"><path d="M12 2 4 5v6c0 5 3.400 9.300 8 11 4.600-1.700 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                    <span>Obrolan ini terlihat oleh seluruh pengguna SIMAGANG yang sudah login. Jangan bagikan kata sandi atau data pribadi.</span>
                    <button type="button" id="pc-rules-x" aria-label="Sembunyikan aturan">×</button>
                </div>

                <div id="pc-people" class="pcl__body"></div>
            </aside>

            {{-- ===== Kanan: percakapan ===== --}}
            <section class="pcp__chat" aria-label="Percakapan Chat Publik">
                <header class="pcp__head">
                    <button type="button" id="pc-open-list" class="pcp__iconbtn pcp__people" aria-label="Buka daftar orang dan pencarian">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.870"/><path d="M16 3.130a4 4 0 0 1 0 7.750"/></svg>
                    </button>
                    <span class="pcp__ava" aria-hidden="true">
                        <svg viewBox="0 0 36 36" fill="none"><rect x="14" y="2.5" width="19" height="13.5" rx="6.5" fill="#fff" opacity=".5"/><path d="M9 9.5h13a6 6 0 0 1 6 6v6.5a6 6 0 0 1-6 6h-6.6l-5.5 4.4a.8.8 0 0 1-1.3-.62V28A6 6 0 0 1 3 22v-6.500a6 6 0 0 1 6-6Z" fill="#fff"/><circle cx="10.500" cy="19" r="1.900" fill="#2563eb"/><circle cx="16" cy="19" r="1.900" fill="#2563eb"/><circle cx="21.500" cy="19" r="1.900" fill="#2563eb"/></svg>
                    </span>
                    <div class="pcp__head-txt">
                        <h1>Ruang Bersama</h1>
                        <p><span class="pc-dot" aria-hidden="true"></span><span id="pc-online">0 online</span><span id="pc-members" class="pcp__members"></span></p>
                    </div>
                    <button type="button" id="pc-hsearch" class="pcp__iconbtn" aria-label="Cari orang atau pesan" title="Cari">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.500-3.500"/></svg>
                    </button>
                </header>

                {{-- Bilah pengumuman tersemat: satu baris di bawah header; klik untuk melompat ke pesannya, panah untuk membuka daftar lengkap. --}}
                <div id="pc-pinwrap" class="pcp__pinwrap" hidden>
                    <div id="pc-pinbar" class="pcp__pinbar"></div>
                    <div id="pc-pindrop" class="pcp__pindrop" hidden></div>
                </div>

                <div class="pc-body">
                    <div id="pc-list" class="pc-list" role="log" aria-live="polite" aria-relevant="additions" tabindex="0" aria-label="Pesan chat publik"></div>
                    <button type="button" id="pc-jump" class="pc-jump" aria-label="Ke pesan terbaru" hidden>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        <span id="pc-jump-n" class="pc-jump__n" hidden>0</span>
                    </button>
                </div>

                <footer class="pc-foot">
                    {{-- data-no-live: cegah skrip "filter langsung" (live-filter.js) menyembunyikan tombol Kirim --}}
                    <form id="pc-compose" class="pc-compose" autocomplete="off" data-no-live>
                        @if ($pcIsMentor)
                            {{-- Mode pengumuman: pesan yang dikirim langsung disematkan di bilah pengumuman selama 7 atau 30 hari. --}}
                            <div id="pc-annbar" class="pc-annbar" hidden>
                                <span class="pc-annbar__lbl">Kirim sebagai pengumuman</span>
                                <span class="pc-seg" role="group" aria-label="Lama disematkan">
                                    <button type="button" data-ann-days="7" aria-pressed="true">7 hari</button>
                                    <button type="button" data-ann-days="30" aria-pressed="false">30 hari</button>
                                </span>
                            </div>
                        @endif
                        <p id="pc-err" class="pc-err" role="alert" hidden></p>
                        <div id="pc-emoji" class="pc-emoji" role="dialog" aria-label="Pilih emoji" hidden></div>
                        <div class="pc-row">
                            <button type="button" id="pc-emoji-btn" class="pc-tool" aria-haspopup="dialog" aria-expanded="false" aria-label="Sisipkan emoji" title="Emoji">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9.500"/><path d="M8.500 14.500a4.500 4.500 0 0 0 7 0"/><path d="M9 9.500h.01M15 9.500h.01"/></svg>
                            </button>
                            @if ($pcIsMentor)
                                <button type="button" id="pc-ann" class="pc-tool pc-ann" aria-pressed="false" aria-label="Kirim sebagai pengumuman" title="Kirim sebagai pengumuman (disematkan di atas)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 11 15-6v14L3 13v-2Z"/><path d="M7 13.500V18a2 2 0 0 0 3.800.9L12 16"/><path d="M21 9.500v5"/></svg>
                                </button>
                            @endif
                            <textarea id="pc-text" class="pc-text" rows="1" placeholder="Ketik pesan" aria-label="Tulis pesan (Enter untuk baris baru, kirim dengan tombol Kirim)"></textarea>
                            <button type="submit" id="pc-send" class="pc-send is-idle" aria-label="Kirim pesan" title="Kirim pesan">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7Z"/></svg>
                                <span class="pc-send__lbl">Kirim</span>
                            </button>
                        </div>
                        <div class="pc-meta">
                            <span>Masuk sebagai <strong id="pc-me"></strong></span>
                            <span id="pc-count" class="pc-count">0/10000</span>
                        </div>
                    </form>
                </footer>
            </section>
        </div>
    </div>
@endsection
