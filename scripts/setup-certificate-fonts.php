<?php

/*
 * Membuat definisi font TCPDF untuk cetak sertifikat (Poppins) dari storage/app/fonts/*.ttf.
 * Hasilnya ditulis ke vendor/tecnickcom/tcpdf/fonts, sehingga jalankan sekali di setiap mesin/server
 * setelah `composer install` (atau saat tcpdf di-update):
 *
 *     php scripts/setup-certificate-fonts.php          lewati font yang sudah ada
 *     php scripts/setup-certificate-fonts.php --force  buat ulang semuanya
 *
 * Nama alias di bawah harus sama dengan AddFont() di AdminCertificateController / CertificateController.
 */

require __DIR__ . '/../vendor/autoload.php';

$force = in_array('--force', $argv, true);
$source = realpath(__DIR__ . '/../storage/app/fonts');
$target = dirname((new ReflectionClass('TCPDF_FONTS'))->getFileName(), 2) . '/fonts/';

$fonts = [
    'Poppins-Bold.ttf' => 'poppinsb',
    'Poppins-Regular.ttf' => 'poppins',
    'Poppins-Extralight.ttf' => 'poppinsextralight',
];

if (! is_dir($target) || ! is_writable($target)) {
    fwrite(STDERR, "Folder font TCPDF tidak bisa ditulis: {$target}\n");
    exit(1);
}

$failed = 0;
foreach ($fonts as $ttf => $alias) {
    $file = $source . DIRECTORY_SEPARATOR . $ttf;

    if (! is_file($file)) {
        fwrite(STDERR, "[GAGAL] {$ttf} tidak ditemukan di storage/app/fonts\n");
        $failed++;
        continue;
    }
    if (! $force && is_file($target . $alias . '.php')) {
        echo "[LEWAT] {$alias}.php sudah ada\n";
        continue;
    }

    // Nama file hasil konversi mengikuti nama internal font (mis. poppinsbold); salin ke nama alias.
    $name = TCPDF_FONTS::addTTFfont($file, 'TrueTypeUnicode', '', 32, $target);
    if (! $name || ! is_file($target . $name . '.php')) {
        fwrite(STDERR, "[GAGAL] konversi {$ttf}\n");
        $failed++;
        continue;
    }
    if ($name !== $alias) {
        copy($target . $name . '.php', $target . $alias . '.php');
    }
    echo "[OK]    {$ttf} -> {$alias}.php\n";
}

exit($failed ? 1 : 0);
