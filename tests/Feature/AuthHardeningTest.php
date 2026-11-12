<?php

namespace Tests\Feature;

use App\Models\Intern;
use App\Models\User;
use App\Support\SafeUpload;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\WorkspaceFixtures;
use Tests\TestCase;

/** Hardening autentikasi, registrasi publik, header, dan penanganan ekstensi unggahan. */
class AuthHardeningTest extends TestCase
{
    use RefreshDatabase, WorkspaceFixtures;

    /** PNG 1x1 yang valid. */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private function realFile(string $name, string $content): UploadedFile
    {
        $dir = storage_path('framework/testing');
        File::ensureDirectoryExists($dir);
        $path = $dir.'/'.uniqid('up_').'.bin';
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, null, null, true);
    }

    private function failLogin(string $email, int $times): void
    {
        for ($i = 0; $i < $times; $i++) {
            $this->post('/login', ['email' => $email, 'password' => 'salah-total']);
        }
    }

    // ------------------------------------------------------------------ login

    public function test_login_dikunci_setelah_lima_kali_gagal_walau_password_benar(): void
    {
        $user = $this->makeUser('intern', 'Peserta Uji');
        $this->failLogin($user->email, 5);

        $response = $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak percobaan', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_login_berhasil_mereset_hitungan_gagal(): void
    {
        $user = $this->makeUser('intern', 'Peserta Uji');
        $this->failLogin($user->email, 4);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($user);

        $this->post('/logout');
        $this->failLogin($user->email, 4);
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_pembatasan_login_per_email_tidak_mengunci_akun_lain(): void
    {
        $korban = $this->makeUser('intern', 'Korban');
        $lain = $this->makeUser('intern', 'Orang Lain');
        $this->failLogin($korban->email, 5);

        $this->post('/login', ['email' => $lain->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($lain);
    }

    public function test_pesan_gagal_login_sama_untuk_email_terdaftar_dan_tidak(): void
    {
        $user = $this->makeUser('intern', 'Peserta Uji');

        $this->post('/login', ['email' => $user->email, 'password' => 'salah-total']);
        $known = session('errors')->first('email');

        $this->post('/login', ['email' => 'tidak-ada@test.local', 'password' => 'salah-total']);
        $unknown = session('errors')->first('email');

        $this->assertSame($known, $unknown);
    }

    // ----------------------------------------------------------- reset password

    public function test_lupa_password_tidak_membocorkan_apakah_email_terdaftar(): void
    {
        Notification::fake();
        $user = $this->makeUser('intern', 'Peserta Uji');

        $this->post('/forgot-password', ['email' => $user->email]);
        $known = session('status');
        $knownErrors = session('errors');

        $this->post('/forgot-password', ['email' => 'tidak-ada@test.local']);
        $unknown = session('status');

        $this->assertNotEmpty($known);
        $this->assertSame($known, $unknown, 'respons harus identik untuk email terdaftar maupun tidak');
        $this->assertNull($knownErrors);
        Notification::assertSentTo($user, ResetPasswordNotification::class);
        Notification::assertCount(1);
    }

    public function test_lupa_password_dibatasi_lima_kali_per_menit(): void
    {
        Notification::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/forgot-password', ['email' => "u{$i}@test.local"])->assertSessionDoesntHaveErrors();
        }

        $this->post('/forgot-password', ['email' => 'u6@test.local'])->assertStatus(429);
    }

    public function test_reset_password_dengan_email_tidak_dikenal_memberi_pesan_umum(): void
    {
        $this->post('/reset-password', [
            'token' => 'token-palsu',
            'email' => 'tidak-ada@test.local',
            'password' => 'Baru12345',
            'password_confirmation' => 'Baru12345',
        ])->assertSessionHasErrors('email');

        $this->assertStringNotContainsString('tidak terdaftar', session('errors')->first('email'));
    }

    // ------------------------------------------------------- registrasi publik

    public function test_registrasi_institusi_menolak_password_lemah(): void
    {
        $payload = fn (string $email, string $password) => [
            'nama_admin' => 'Admin Kampus',
            'email' => $email,
            'password' => $password,
            'nama_institusi' => 'Kampus Baru',
            'jenis_institusi' => 'kampus',
            'no_hp' => '0812345678',
        ];

        $this->post('/institusi', $payload('a@test.local', 'abc'))->assertSessionHasErrors('password');
        $this->post('/institusi', $payload('b@test.local', 'abcdefgh'))->assertSessionHasErrors('password');
        $this->post('/institusi', $payload('c@test.local', 'abcdefg1'))->assertSessionHasNoErrors();

        $this->assertNull(User::where('email', 'a@test.local')->first());
        $this->assertNotNull(User::where('email', 'c@test.local')->first());
    }

    public function test_registrasi_industri_menolak_password_lemah(): void
    {
        $this->post('/register-industri', [
            'nama_admin' => 'Admin Industri',
            'email' => 'industri-lemah@test.local',
            'password' => '123',
        ])->assertSessionHasErrors('password');

        $this->assertNull(User::where('email', 'industri-lemah@test.local')->first());
    }

    public function test_registrasi_dibatasi_lima_percobaan_per_sepuluh_menit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/institusi', ['email' => "x{$i}@test.local"]);
        }

        $this->post('/institusi', ['email' => 'x6@test.local'])->assertStatus(429);
    }

    public function test_chatbot_publik_dibatasi_laju(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->postJson('/chatbot', ['message' => ''])->assertStatus(422);
        }

        $this->postJson('/chatbot', ['message' => ''])->assertStatus(429);
    }

    // ------------------------------------------------------------------ header

    public function test_hsts_hanya_dikirim_lewat_https(): void
    {
        $this->get('http://localhost/login')->assertHeaderMissing('Strict-Transport-Security');

        $this->get('https://localhost/login')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        $this->get('http://localhost/login')->assertHeader('Permissions-Policy');
    }

    // ------------------------------------------------------------ ekstensi unggahan

    public function test_ekstensi_diambil_dari_isi_berkas_bukan_nama_dari_klien(): void
    {
        $png = $this->realFile('evil.php', base64_decode(self::PNG));
        $this->assertSame('png', SafeUpload::extension($png, SafeUpload::IMAGE, 'jpg'));

        $html = $this->realFile('evil.php', '<html><script>alert(1)</script></html>');
        $this->assertSame('jpg', SafeUpload::extension($html, SafeUpload::IMAGE, 'jpg'), 'ekstensi php tidak boleh pernah dipakai');

        $phtml = $this->realFile('evil.phtml', '<html></html>');
        $this->assertSame('pdf', SafeUpload::extension($phtml, SafeUpload::DOCUMENT, 'pdf'));
    }

    private function registerWithPhoto(string $email, string $photoName)
    {
        return $this->post('/register', [
            'name' => 'Peserta Uji Foto',
            'email' => $email,
            'gender' => 'Laki-laki',
            'education_level' => 'S1/D4',
            'institution' => 'Kampus Uji',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'photo' => $this->realFile($photoName, base64_decode(self::PNG)),
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
        ]);
    }

    private function photoFiles(): array
    {
        return glob(storage_path('app/public/photos/photo_*')) ?: [];
    }

    public function test_foto_registrasi_bernama_php_ditolak_dan_tidak_tersimpan(): void
    {
        $before = $this->photoFiles();

        $this->registerWithPhoto('polyglot@test.local', 'evil.php')->assertSessionHasErrors('photo');

        $this->assertNull(User::where('email', 'polyglot@test.local')->first());
        $this->assertSame($before, $this->photoFiles(), 'tidak boleh ada berkas yang tersimpan');
    }

    public function test_foto_registrasi_disimpan_dengan_ekstensi_sesuai_isi_berkas(): void
    {
        $before = $this->photoFiles();

        try {
            // Isi PNG, nama .jpeg: ekstensi tersimpan mengikuti isi (png), atau ditolak validasi; tidak pernah dipercaya dari nama.
            $this->registerWithPhoto('salah-ekstensi@test.local', 'foto.jpeg');

            $intern = Intern::whereHas('user', fn ($q) => $q->where('email', 'salah-ekstensi@test.local'))->first();

            if ($intern) {
                $this->assertMatchesRegularExpression('/\.png$/', $intern->photo_path);
            } else {
                $this->assertSame($before, $this->photoFiles());
            }
        } finally {
            foreach (array_diff($this->photoFiles(), $before) as $file) {
                @unlink($file);
            }
        }
    }
}
