<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Menentukan ekstensi berkas yang disimpan dari isi berkas, bukan dari nama pemberian klien.
 *
 * Ekstensi dari klien tidak boleh dipercaya: berkas polyglot (gambar sah yang disisipi PHP) yang
 * diberi nama `foto.php` lolos validasi `image` karena yang dinilai isinya. Bila disimpan dengan
 * ekstensi aslinya di folder yang bisa dijangkau web server, berkas itu dapat dieksekusi.
 */
final class SafeUpload
{
    public const IMAGE = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    public const DOCUMENT = ['pdf', 'doc', 'docx'];

    public const ATTENDANCE_DOCUMENT = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

    public const ARCHIVE = ['zip', 'rar', '7z', 'tar', 'gz'];

    /**
     * @param  array<int, string>  $allowed  daftar ekstensi yang diizinkan (huruf kecil)
     * @param  string  $default  dipakai bila isi maupun nama klien tidak masuk daftar
     */
    public static function extension(UploadedFile $file, array $allowed, string $default): string
    {
        $guessed = strtolower((string) $file->guessExtension());

        if (in_array($guessed, $allowed, true)) {
            return $guessed;
        }

        $client = strtolower((string) $file->getClientOriginalExtension());

        if (in_array($client, $allowed, true)) {
            return $client;
        }

        return $default;
    }
}
