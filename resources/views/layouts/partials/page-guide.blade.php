{{--
    Panduan penggunaan per halaman untuk SEMUA peran (admin, super admin, mentor, peserta, institusi, industri).
    Hanya pemuat kecil yang dimuat di sini; panel, isi tutorial, dan Driver.js baru diunduh saat tombol "Panduan penggunaan"
    di sidebar diklik atau saat halaman dibuka dengan ?tutorial=1 (lihat public/js/page-guide.js).
    Isi tutorial tiap halaman: public/js/page-guide-tours.js.
--}}
@php
    $pgUser = auth()->user();
    [$pgRole, $pgLabel] = match (true) {
        $pgUser->isSuperAdmin() => ['admin', 'Super Admin'],
        $pgUser->isAdmin() => ['admin', 'Admin'],
        $pgUser->isMentor() => ['mentor', 'Mentor'],
        $pgUser->isInstitusi() => ['institusi', 'Institusi'],
        $pgUser->isIndustri() => ['industri', 'Industri'],
        default => ['intern', 'Peserta Magang'],
    };
@endphp
<script>
    window.PageGuideConfig = @js(['role' => $pgRole, 'roleLabel' => $pgLabel, 'userId' => (string) $pgUser->id]);
</script>
<script defer src="{{ asset('js/page-guide.js') }}?v={{ @filemtime(public_path('js/page-guide.js')) . @filemtime(public_path('js/page-guide-ui.js')) . @filemtime(public_path('js/page-guide-tours.js')) . @filemtime(public_path('css/page-guide.css')) }}"></script>
