{{--
    Panduan penggunaan Workspace (panel geser). Murni frontend: tanpa route, isinya statis per peran.
    Dibuka dari tombol "Panduan" di sidebar, atau dari ikon "?" (<x-workspace.help term="sprint" />).
    Buka dari mana saja: window.dispatchEvent(new CustomEvent('ws-guide-open', { detail: { tab?, topic? } })).
    tab: 'mulai' | 'alur' | 'kamus' | 'faq'; topic: id istilah di $terms (otomatis membuka tab Kamus dan menyorot istilahnya).
    Props: $role = 'mentor' | 'intern' | 'admin'.
--}}
@props(['role' => 'intern'])

@php
    $isMentor = $role === 'mentor';
    $isIntern = $role === 'intern';

    $steps = match ($role) {
        'mentor' => [
            ['Buat kelompok', 'Buka menu Workspace, klik "Buat kelompok", lalu isi nama, warna, dan pilih anak bimbingan Anda. Satu anak magang hanya bisa berada di satu kelompok aktif.'],
            ['Susun tugas di Backlog', 'Buka kelompok, tab Backlog, lalu klik "Buat tugas". Isi judul, prioritas, estimasi story point, dan epic bila perlu.'],
            ['Buat dan mulai sprint', 'Klik "Buat sprint", pilih tugas yang akan dikerjakan, tentukan tanggal dan satu Ketua dari anggota, lalu mulai sprint.'],
            ['Pantau di Board', 'Tab Board menampilkan kartu per status. Kartu bergeser seiring pekerjaan anak magang; tanda merah berarti lewat deadline.'],
            ['Review dan beri nilai', 'Buka tugas berstatus Review. Pilih "Setujui & beri nilai", atau "Minta revisi" dengan catatan perbaikan.'],
            ['Selesaikan sprint', 'Di akhir periode klik "Selesaikan sprint", lalu pilih ke mana tugas yang belum selesai dipindahkan (Backlog atau sprint baru).'],
        ],
        'intern' => [
            ['Temukan tugas Anda', 'Buka menu Workspace, lalu klik kelompok Anda. Tugas untuk Anda ditandai avatar Anda di kartu, dan muncul juga di lonceng notifikasi.'],
            ['Mulai kerjakan', 'Geser kartu dari To Do ke In Progress, atau ubah statusnya dari detail tugas. Dengan begitu mentor tahu Anda sudah mulai.'],
            ['Kirim untuk di-review', 'Setelah selesai, pindahkan kartu ke Review. Lampirkan tautan atau berkas hasil, dan tulis catatan singkat bila perlu.'],
            ['Jika diminta revisi', 'Kartu kembali ke kolom Revisi disertai catatan mentor. Perbaiki, lalu kirim ke Review lagi.'],
            ['Tugas selesai', 'Setelah disetujui, kartu masuk ke Selesai dan nilai dari mentor terlihat di detail tugas.'],
            ['Jika Anda menjadi Ketua Sprint', 'Anda yang membagi tugas sprint ke teman satu kelompok, lewat tombol Bagi atau avatar di kartu.'],
        ],
        default => [
            ['Buka Workspace', 'Menu Workspace menampilkan "Semua Kelompok" dari semua mentor beserta progres tiap kelompok.'],
            ['Saring kelompok', 'Gunakan kolom cari serta pilihan unit dan mentor untuk menemukan kelompok tertentu.'],
            ['Perhatikan tanda', 'Tombol "Perlu perhatian" menyorot kelompok yang punya tugas lewat deadline atau menunggu review.'],
            ['Buka detail kelompok', 'Klik "Buka" untuk melihat Board, Timeline, Laporan, dan aktivitas kelompok tersebut.'],
        ],
    };

    $terms = [
        ['id' => 'kelompok', 'term' => 'Kelompok', 'def' => 'Ruang kerja satu tim, dibuat oleh mentor, berisi anggota, tugas, dan chat.', 'analogy' => 'Seperti ruang kelas dengan papan tugasnya sendiri.'],
        ['id' => 'kanban', 'term' => 'Kanban / Board', 'def' => 'Papan tugas berkolom (To Do, In Progress, Review, Revisi, Selesai). Tugas berpindah kolom sesuai statusnya.', 'analogy' => 'Seperti papan tempel kertas: kertas dipindah dari "akan dikerjakan" ke "selesai".'],
        ['id' => 'backlog', 'term' => 'Backlog', 'def' => 'Daftar tugas yang sudah dicatat tetapi belum dijadwalkan ke sprint.', 'analogy' => 'Seperti daftar belanja: dicatat dulu, dipilih nanti.'],
        ['id' => 'sprint', 'term' => 'Sprint', 'def' => 'Periode kerja pendek dengan target jelas (misalnya 2 minggu). Hanya tugas di sprint berjalan yang tampil di Board.', 'analogy' => 'Seperti satu babak pertandingan dengan target skor.'],
        ['id' => 'epic', 'term' => 'Epic', 'def' => 'Kelompok tugas yang bertema sama, untuk melihat pekerjaan besar yang dipecah menjadi tugas kecil.', 'analogy' => 'Seperti satu bab dalam buku; tugas adalah halamannya.'],
        ['id' => 'story-point', 'term' => 'Story Point', 'def' => 'Angka estimasi seberapa berat sebuah tugas. Bukan jumlah jam.', 'analogy' => 'Seperti ukuran S, M, L pada baju.'],
        ['id' => 'timeline', 'term' => 'Timeline', 'def' => 'Tampilan batang waktu tiap tugas dari mulai sampai deadline, dengan garis "hari ini". Tugas tanpa deadline tidak muncul di sini.', 'analogy' => 'Seperti jadwal proyek berbentuk batang.'],
        ['id' => 'review', 'term' => 'Review', 'def' => 'Tahap saat mentor memeriksa hasil kerja yang dikirim anak magang.', 'analogy' => 'Seperti dosen memeriksa draf sebelum disetujui.'],
        ['id' => 'revisi', 'term' => 'Revisi', 'def' => 'Hasil dikembalikan oleh mentor dengan catatan perbaikan, lalu dikerjakan ulang dan dikirim ke Review lagi.', 'analogy' => 'Seperti draf yang dicoret merah lalu diperbaiki.'],
        ['id' => 'burndown', 'term' => 'Burndown', 'def' => 'Grafik sisa pekerjaan dibanding rencana ideal di tab Laporan. Garis di bawah rencana berarti lebih cepat dari jadwal.', 'analogy' => 'Seperti indikator bensin: turun sesuai jadwal atau tertinggal.'],
        ['id' => 'deadline', 'term' => 'Deadline', 'def' => 'Batas waktu tugas. Tugas yang melewatinya ditandai terlambat, dan mentor dapat memperpanjangnya.', 'analogy' => 'Seperti tanggal kumpul tugas kuliah.'],
        ['id' => 'ketua-sprint', 'term' => 'Ketua Sprint', 'def' => 'Anak magang yang ditunjuk mentor untuk satu sprint dan bertugas membagi tugas ke anggota kelompok.', 'analogy' => 'Seperti ketua kelompok dalam tugas kuliah.'],
        ['id' => 'notifikasi', 'term' => 'Notifikasi', 'def' => 'Pemberitahuan tugas baru, review, revisi, komentar, dan deadline. Muncul sebagai pop-up, di ikon lonceng, dan di judul tab browser.', 'analogy' => 'Seperti pesan masuk di ponsel.'],
    ];

    $faqs = array_values(array_filter([
        ['roles' => ['intern'], 'q' => 'Kenapa saya tidak bisa memindahkan kartu ke Selesai?', 'a' => 'Status Selesai dan Revisi hanya bisa diberikan mentor. Anak magang memindahkan kartu sampai Review, lalu mentor memutuskan.'],
        ['roles' => ['intern', 'mentor'], 'q' => 'Kartu saya tidak muncul di Board.', 'a' => 'Board hanya menampilkan tugas di sprint yang sedang berjalan. Tugas lain ada di tab Backlog sampai sprint-nya dimulai.'],
        ['roles' => ['admin'], 'q' => 'Kenapa tidak ada tombol "Buat kelompok"?', 'a' => 'Kelompok dibuat oleh mentor untuk anak bimbingannya. Admin memantau semua kelompok.'],
        ['roles' => ['mentor'], 'q' => 'Bisakah anggota dikeluarkan dari kelompok?', 'a' => 'Saat ini anggota tidak dapat dikeluarkan. Anggota baru dapat ditambahkan lewat menu Kelola di halaman kelompok.'],
        ['roles' => ['mentor'], 'q' => 'Apa yang terjadi pada tugas yang belum selesai saat sprint ditutup?', 'a' => 'Anda memilih tujuannya: kembali ke Backlog atau dipindah ke sprint berikutnya.'],
        ['roles' => ['mentor', 'intern', 'admin'], 'q' => 'Apa beda Backlog dan Board?', 'a' => 'Backlog adalah tempat merencanakan tugas ke sprint. Board adalah tempat mengerjakan tugas sprint yang sedang berjalan.'],
        ['roles' => ['mentor', 'intern', 'admin'], 'q' => 'Tugas tanpa deadline tampil di mana?', 'a' => 'Di Backlog. Tugas yang sudah masuk sprint mengikuti tanggal akhir sprint, dan baru tampil di Timeline bila punya deadline.'],
        ['roles' => ['mentor', 'intern', 'admin'], 'q' => 'Di mana saya melihat pemberitahuan?', 'a' => 'Di ikon lonceng pojok kanan atas, juga sebagai pop-up saat ada yang baru dan angka di judul tab browser. Pengaturannya ada di ikon gerigi dalam lonceng.'],
        ['roles' => ['mentor', 'intern', 'admin'], 'q' => 'Bagaimana mengganti ke mode gelap?', 'a' => 'Klik ikon gerigi di pojok kanan atas, lalu pilih mode gelap.'],
    ], fn ($f) => in_array($role, $f['roles'], true)));

    $faqs = array_map(fn ($f) => ['q' => $f['q'], 'a' => $f['a']], $faqs);
@endphp

<div x-data="{
        tab: 'mulai',
        q: '',
        topic: null,
        faq: null,
        steps: @js($steps),
        terms: @js($terms),
        faqs: @js($faqs),
        get shownTerms() {
            const q = this.q.trim().toLowerCase();
            return q ? this.terms.filter((t) => (t.term + ' ' + t.def + ' ' + t.analogy).toLowerCase().includes(q)) : this.terms;
        },
        show(d) {
            if (d.tab) this.tab = d.tab;
            if (d.topic) { this.tab = 'kamus'; this.q = ''; this.topic = d.topic; }
            this.$dispatch('ws-open-modal', 'ws-guide');
            if (d.topic) setTimeout(() => {
                const el = document.getElementById('ws-guide-term-' + d.topic);
                if (el) el.scrollIntoView({ block: 'center', behavior: 'smooth' });
            }, 360);
        },
     }"
     x-on:ws-guide-open.window="show($event.detail || {})">
    <x-workspace.slide-over name="ws-guide">
        <x-slot:header>
            <h2 id="ws-slideover-title-ws-guide" class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <span class="ws-guide__ico"><i class="fas fa-circle-question"></i></span> Panduan penggunaan
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ $isMentor ? 'Untuk mentor' : ($isIntern ? 'Untuk anak magang' : 'Untuk admin') }} · Workspace
            </p>
        </x-slot:header>

        <div class="ws-guide">
            <div class="ws-guide__tabs" role="tablist" aria-label="Bagian panduan">
                <button type="button" role="tab" :aria-selected="(tab === 'mulai').toString()" :class="tab === 'mulai' && 'is-active'" x-on:click="tab = 'mulai'">Mulai cepat</button>
                <button type="button" role="tab" :aria-selected="(tab === 'alur').toString()" :class="tab === 'alur' && 'is-active'" x-on:click="tab = 'alur'">Alur kerja</button>
                <button type="button" role="tab" :aria-selected="(tab === 'kamus').toString()" :class="tab === 'kamus' && 'is-active'" x-on:click="tab = 'kamus'">Kamus istilah</button>
                <button type="button" role="tab" :aria-selected="(tab === 'faq').toString()" :class="tab === 'faq' && 'is-active'" x-on:click="tab = 'faq'">FAQ</button>
            </div>

            {{-- Mulai cepat --}}
            <div x-show="tab === 'mulai'" role="tabpanel">
                <p class="ws-guide__lead">Langkah dari awal sampai selesai. Ikuti urutannya.</p>
                <ol class="ws-guide__steps">
                    <template x-for="(s, i) in steps" :key="i">
                        <li>
                            <span class="ws-guide__num" x-text="i + 1"></span>
                            <div>
                                <p class="ws-guide__step-title" x-text="s[0]"></p>
                                <p class="ws-guide__step-text" x-text="s[1]"></p>
                            </div>
                        </li>
                    </template>
                </ol>
                <div class="ws-guide__replay">
                    <p class="ws-guide__lead">Ulangi tutorial</p>
                    <div class="ws-guide__replay-btns">
                        <button type="button" x-on:click="$dispatch('ws-tour-start', { id: 'dasar' })"><i class="fas fa-play"></i> Tutorial dasar</button>
                        <button type="button" x-on:click="$dispatch('ws-tour-start', { id: 'halaman' })"><i class="fas fa-location-dot"></i> Tutorial halaman ini</button>
                    </div>
                </div>
                <p class="ws-guide__note">
                    <i class="fas fa-lightbulb"></i>
                    <span>Bingung dengan sebuah istilah? Buka tab <button type="button" x-on:click="tab = 'kamus'">Kamus istilah</button> atau klik ikon <span class="ws-help ws-help--inline">?</span> di samping judul.</span>
                </p>
            </div>

            {{-- Alur kerja --}}
            <div x-show="tab === 'alur'" x-cloak role="tabpanel">
                <p class="ws-guide__lead">Perjalanan sebuah tugas dari dibuat sampai dinilai.</p>
                <div class="ws-guide__flow">
                    <div class="ws-guide__flow-row"><span class="ws-guide__pill" style="--c:#64748b;--b:#f1f5f9">To Do</span><p><b>Tugas baru</b> menunggu dikerjakan.</p></div>
                    <i class="fas fa-arrow-down ws-guide__arrow" aria-hidden="true"></i>
                    <div class="ws-guide__flow-row"><span class="ws-guide__pill" style="--c:#2563eb;--b:#eff6ff">In Progress</span><p><b>Anak magang</b> sedang mengerjakan.</p></div>
                    <i class="fas fa-arrow-down ws-guide__arrow" aria-hidden="true"></i>
                    <div class="ws-guide__flow-row"><span class="ws-guide__pill" style="--c:#7c3aed;--b:#f5f3ff">Review</span><p>Hasil dikirim. <b>Mentor</b> memeriksa.</p></div>
                    <div class="ws-guide__fork">
                        <div class="ws-guide__flow-row"><span class="ws-guide__pill" style="--c:#059669;--b:#ecfdf5">Selesai</span><p><b>Disetujui</b> dan diberi nilai 0 sampai 100.</p></div>
                        <div class="ws-guide__flow-row"><span class="ws-guide__pill" style="--c:#ea580c;--b:#fff7ed">Revisi</span><p><b>Dikembalikan</b> dengan catatan, lalu kembali ke In Progress.</p></div>
                    </div>
                </div>
                <p class="ws-guide__note">
                    <i class="fas fa-user-lock"></i>
                    <span>Anak magang hanya dapat memindahkan kartu ke To Do, In Progress, dan Review. Selesai dan Revisi hanya dapat diberikan mentor.</span>
                </p>
                <p class="ws-guide__lead" style="margin-top:18px">Persiapan sebelum mengerjakan</p>
                <ol class="ws-guide__mini">
                    <li><b>Mentor</b> membuat kelompok dan memilih anggota.</li>
                    <li><b>Mentor</b> menyusun tugas di Backlog dan membuat sprint.</li>
                    <li><b>Mentor</b> memulai sprint dan menunjuk Ketua.</li>
                    <li><b>Ketua</b> membagi tugas ke anggota kelompok.</li>
                </ol>
            </div>

            {{-- Kamus --}}
            <div x-show="tab === 'kamus'" x-cloak role="tabpanel">
                <label class="ws-guide__search">
                    <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" x-model="q" placeholder="Cari istilah, mis. sprint" aria-label="Cari istilah">
                </label>
                <div class="ws-guide__terms">
                    <template x-for="t in shownTerms" :key="t.id">
                        <article class="ws-guide__term" :id="'ws-guide-term-' + t.id" :class="topic === t.id && 'is-focus'">
                            <h3 x-text="t.term"></h3>
                            <p x-text="t.def"></p>
                            <p class="ws-guide__analogy"><i class="fas fa-quote-left" aria-hidden="true"></i> <span x-text="t.analogy"></span></p>
                        </article>
                    </template>
                    <p class="ws-guide__empty" x-show="!shownTerms.length">Tidak ada istilah yang cocok.</p>
                </div>
            </div>

            {{-- FAQ --}}
            <div x-show="tab === 'faq'" x-cloak role="tabpanel">
                <div class="ws-guide__faqs">
                    <template x-for="(f, i) in faqs" :key="i">
                        <div class="ws-guide__faq" :class="faq === i && 'is-open'">
                            <button type="button" x-on:click="faq = faq === i ? null : i" :aria-expanded="(faq === i).toString()">
                                <span x-text="f.q"></span>
                                <i class="fas fa-chevron-down" aria-hidden="true"></i>
                            </button>
                            <p x-show="faq === i" x-transition.opacity.duration.150ms x-text="f.a"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </x-workspace.slide-over>
</div>
