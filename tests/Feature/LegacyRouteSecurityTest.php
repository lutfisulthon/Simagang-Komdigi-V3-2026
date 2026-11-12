<?php

namespace Tests\Feature;

use App\Models\Institusi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\WorkspaceFixtures;
use Tests\TestCase;

/**
 * Sapuan keamanan modul lama (admin, mentor, intern, institusi, industri):
 * route non-publik menolak tamu, dan setiap prefix role menolak role lain.
 */
class LegacyRouteSecurityTest extends TestCase
{
    use RefreshDatabase, WorkspaceFixtures;

    private const ROLE_PREFIXES = [
        'admin' => 'admin_full',
        'mentor' => 'mentor',
        'intern' => 'intern',
        'institusi' => 'institusi',
        'industri' => 'industri',
    ];

    /** Route yang memang publik (landing, artikel, login, daftar, reset password, API institusi, chatbot). */
    private const PUBLIC_URIS = [
        '/', 'aktivitas', 'aktivitas/{activityPost}', 'api/institutions/all', 'api/institutions/search',
        'artikel1', 'artikel2', 'artikel3', 'chatbot', 'convert-font', 'daftar-lowongan',
        'forgot-password', 'login', 'register', 'register-industri', 'reset-password', 'reset-password/{token}',
        'sharing-session/{sharingSession}', 'institusi/create',
    ];

    /** @return array<int, array{string, string, string}> [nama, method, uri] */
    private function legacyRoutes(): array
    {
        $skip = ['workspace', 'public-chat', 'admin/chat-publik', '_', 'sanctum', 'up', 'broadcasting', 'storage', 'api/user', '_ignition'];
        $rows = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            if (collect($skip)->contains(fn ($p) => $uri === $p || str_starts_with($uri, $p.'/'))) {
                continue;
            }

            foreach ($route->methods() as $method) {
                if (in_array($method, ['HEAD', 'OPTIONS'], true)) {
                    continue;
                }
                $rows[] = [(string) $route->getName(), $method, $uri];
            }
        }

        return $rows;
    }

    private function concrete(string $uri): string
    {
        return '/'.ltrim(preg_replace('/\{[^}]+\}/', '1', $uri), '/');
    }

    private function guestStatus(string $method, string $uri): int
    {
        return $this->json($method, $this->concrete($uri))->getStatusCode();
    }

    public function test_route_non_publik_menolak_tamu(): void
    {
        $leaks = [];

        foreach ($this->legacyRoutes() as [$name, $method, $uri]) {
            $isPublic = in_array($uri, self::PUBLIC_URIS, true)
                || ($method === 'POST' && in_array($uri, ['login', 'register', 'register-industri', 'forgot-password', 'reset-password', 'chatbot', 'institusi'], true))
                || in_array($uri, ['logout'], true);

            if ($isPublic) {
                continue;
            }

            $status = $this->guestStatus($method, $uri);

            if (! in_array($status, [401, 302, 419], true)) {
                $leaks[] = "{$method} /{$uri} => {$status}";
            }
        }

        $this->assertSame([], $leaks, "Route dapat diakses tamu tanpa login:\n".implode("\n", $leaks));
    }

    public function test_setiap_prefix_role_menolak_role_lain(): void
    {
        $users = [];
        foreach (self::ROLE_PREFIXES as $prefix => $role) {
            $users[$prefix] = $this->makeUser($role, "Uji {$prefix}");
        }

        $leaks = [];

        foreach ($this->legacyRoutes() as [$name, $method, $uri]) {
            $prefix = explode('/', $uri)[0];

            if (! isset(self::ROLE_PREFIXES[$prefix]) || $uri === 'institusi/create' || ($prefix === 'institusi' && ! str_starts_with($uri, 'institusi/') && $uri !== 'institusi')) {
                continue;
            }

            // `institusi` resource publik (store/show/dst.) ditangani tes terpisah.
            if ($prefix === 'institusi' && ! $this->isInstitusiDashboardRoute($name)) {
                continue;
            }

            foreach ($users as $userPrefix => $user) {
                if ($userPrefix === $prefix) {
                    continue;
                }

                $status = $this->actingAs($user)->json($method, $this->concrete($uri))->getStatusCode();

                if (! in_array($status, [302, 401, 403, 404, 419], true)) {
                    $leaks[] = "{$userPrefix} => {$method} /{$uri} = {$status}";
                }
            }
        }

        $this->assertSame([], $leaks, "Role lain dapat mengakses prefix yang bukan miliknya:\n".implode("\n", $leaks));
    }

    private function isInstitusiDashboardRoute(string $name): bool
    {
        return str_starts_with($name, 'institusi.') && ! in_array($name, [
            'institusi.index', 'institusi.create', 'institusi.store', 'institusi.show',
            'institusi.update', 'institusi.destroy', 'institusi.edit',
        ], true);
    }

    public function test_pendaftaran_institusi_publik_tetap_berfungsi(): void
    {
        $this->get('/institusi/create')->assertOk();

        $this->post('/institusi', [
            'nama_admin' => 'Admin Kampus',
            'email' => 'admin-kampus@test.local',
            'password' => 'rahasia123',
            'nama_institusi' => 'Kampus Baru',
            'jenis_institusi' => 'kampus',
            'no_hp' => '0812345678',
        ])->assertRedirect(route('login'));

        $user = User::where('email', 'admin-kampus@test.local')->firstOrFail();
        $this->assertSame('institusi', $user->role);
        $this->assertTrue(Institusi::where('user_id', $user->id)->where('nama_institusi', 'Kampus Baru')->exists());
    }

    public function test_tamu_tidak_bisa_menghapus_institusi_lewat_resource_route(): void
    {
        $user = $this->makeUser('institusi', 'Kampus Korban');
        $institusi = Institusi::create([
            'user_id' => $user->id,
            'nama_institusi' => 'Kampus Korban',
            'jenis_institusi' => 'kampus',
            'no_hp' => '0812345678',
        ]);

        $this->json('DELETE', "/institusi/{$institusi->id}");

        $this->assertNotNull(Institusi::find($institusi->id), 'tamu tanpa login berhasil menghapus institusi');
        $this->assertNotNull(User::find($user->id));
    }

    public function test_user_role_lain_tidak_bisa_menghapus_institusi_lewat_resource_route(): void
    {
        $owner = $this->makeUser('institusi', 'Kampus Korban');
        $institusi = Institusi::create([
            'user_id' => $owner->id,
            'nama_institusi' => 'Kampus Korban',
            'jenis_institusi' => 'kampus',
            'no_hp' => '0812345678',
        ]);

        $this->actingAs($this->makeUser('intern', 'Penyerang'))->json('DELETE', "/institusi/{$institusi->id}");

        $this->assertNotNull(Institusi::find($institusi->id), 'user biasa berhasil menghapus institusi milik orang lain');
    }
}
