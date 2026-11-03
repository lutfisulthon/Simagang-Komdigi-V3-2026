<?php

/*
 * Warna & identitas tiap halaman, dipakai layouts/app.blade.php untuk <main data-tone>, penanda grup di top bar,
 * dan ikon hero. Selaras dengan warna menu sidebar (lihat *-nav.blade.php).
 *
 * Setiap entri: pola route (routeIs) => [tone, grup, kode ikon Font Awesome (CSS content) atau null].
 * Ikon null = halaman memakai ikon sendiri / dashboard. Pola pertama yang cocok dipakai.
 */
return [
    // Umum
    'workspace.*' => ['violet', 'Utama', null],
    'notifications.*' => ['blue', 'Utama', null],

    // Mentor
    'mentor.dashboard' => ['blue', 'Utama', null],
    'mentor.intern.*' => ['teal', 'Bimbingan', '\f501'],
    'mentor.attendance.*' => ['green', 'Bimbingan', '\f274'],
    'mentor.logbook.*' => ['amber', 'Bimbingan', '\f518'],
    'mentor.certificates.*' => ['amber', 'Evaluasi', '\f0a3'],
    'mentor.report.*' => ['rose', 'Evaluasi', '\f15c'],
    'mentor.microskill.*' => ['orange', 'Evaluasi', null],
    'mentor.profile.*' => ['violet', 'Akun', '\f007'],

    // Anak magang
    'intern.dashboard' => ['blue', 'Utama', null],
    'intern.attendance.*' => ['green', 'Aktivitas', '\f274'],
    'intern.logbook.*' => ['amber', 'Aktivitas', '\f518'],
    'intern.sharing-session.*' => ['teal', 'Aktivitas', '\f086'],
    'intern.report.*' => ['rose', 'Evaluasi', '\f15c'],
    'intern.microskill.*' => ['orange', 'Evaluasi', '\f559'],
    'intern.profile.*' => ['violet', 'Akun', '\f007'],

    // Admin & super admin
    'admin.dashboard' => ['blue', 'Utama', null],
    'admin.accounts.*' => ['rose', 'Pengguna', '\f505'],
    'admin.mentor.*' => ['teal', 'Pengguna', '\f51c'],
    'admin.mitra.*' => ['amber', 'Pengguna', '\f2b5'],
    'admin.intern.*' => ['green', 'Pengguna', '\f501'],
    'admin.team.*' => ['violet', 'Pengguna', '\e533'],
    'admin.lowongan.*' => ['blue', 'Operasional', '\f0b1'],
    'admin.verifikasi.*' => ['teal', 'Operasional', '\f46c'],
    'admin.pengajuan.*' => ['orange', 'Operasional', '\f56c'],
    'admin.attendance.*' => ['green', 'Operasional', '\f274'],
    'admin.logbook.*' => ['amber', 'Operasional', '\f518'],
    'admin.microskill.*' => ['orange', 'Operasional', '\f559'],
    'admin.sharing-session.*' => ['teal', 'Operasional', '\f086'],
    'admin.aktivitas.*,admin.activity-posts.*' => ['rose', 'Operasional', '\f1ea'],
    'admin.monitoring.industri.*' => ['violet', 'Monitoring & Evaluasi', '\f1ad'],
    'admin.monitoring.*' => ['blue', 'Monitoring & Evaluasi', '\f201'],
    'admin.certificates.*' => ['amber', 'Monitoring & Evaluasi', '\f0a3'],
    'admin.report.*' => ['rose', 'Monitoring & Evaluasi', '\f15c'],
    'admin.rag.*' => ['violet', 'Sistem', '\f5dc'],
    'admin.*' => ['blue', 'Admin', null],

    // Institusi
    'institusi.dashboard' => ['blue', 'Utama', null],
    'institusi.lowongan.*' => ['violet', 'Utama', null],
    'institusi.pengajuan.*' => ['orange', 'Utama', null],
    'institusi.intern.*' => ['teal', 'Monitoring', null],
    'institusi.attendance.*' => ['green', 'Monitoring', null],
    'institusi.logbook.*' => ['amber', 'Monitoring', null],
    'institusi.microskill.*' => ['orange', 'Monitoring', null],
    'institusi.certificate.*' => ['rose', 'Monitoring', null],
    'institusi.profile.*' => ['violet', 'Akun', null],
    'institusi.*' => ['blue', 'Institusi', null],

    // Industri
    'industri.dashboard' => ['blue', 'Utama', null],
    'industri.lowongan.*' => ['violet', 'Utama', null],
    'industri.pengajuan.*' => ['orange', 'Utama', null],
    'industri.intern.*' => ['teal', 'Monitoring', null],
    'industri.attendance.*' => ['green', 'Monitoring', null],
    'industri.logbook.*' => ['amber', 'Monitoring', null],
    'industri.microskill.*' => ['orange', 'Monitoring', null],
    'industri.report.*' => ['rose', 'Monitoring', null],
    'industri.profile.*' => ['violet', 'Akun', null],
    'industri.*' => ['blue', 'Industri', null],
];
