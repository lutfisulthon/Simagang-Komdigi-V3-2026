<?php

namespace Tests\Feature\Workspace;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Notifications\ChatUnreadNotification;
use App\Services\NotificationPreferences;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

/**
 * Setiap toggle di halaman Pengaturan Notifikasi (semua jenis, semua role) harus benar-benar mengubah
 * kanal yang dipakai notifikasi aslinya: Aplikasi = database, Email = mail.
 */
class NotificationPreferenceMatrixTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    /** @return array<string, object> jenis → instance notifikasi (tanpa konstruktor) */
    private function notificationsByType(): array
    {
        $map = [];

        foreach (glob(app_path('Notifications/*Notification.php')) as $file) {
            $class = 'App\\Notifications\\' . basename($file, '.php');
            $rc = new ReflectionClass($class);

            if (in_array($rc->getShortName(), ['ResetPasswordNotification', 'ChatUnreadNotification'], true)) {
                continue;
            }

            if ($rc->getShortName() === 'TaskReviewedNotification') {
                foreach ([TaskStatus::Done, TaskStatus::Revision] as $status) {
                    $o = $rc->newInstanceWithoutConstructor();
                    $p = $rc->getProperty('task');
                    $p->setAccessible(true);
                    $p->setValue($o, (new Task)->forceFill(['status' => $status]));
                    $map[$o->preferenceType()] = $o;
                }

                continue;
            }

            $o = $rc->newInstanceWithoutConstructor();
            $map[$o->preferenceType()] = $o;
        }

        return $map;
    }

    private function via(object $notification, User $user, array $prefs, string $type): array
    {
        DB::table('notification_preferences')->where('user_id', $user->id)->delete();

        if ($prefs) {
            app(NotificationPreferences::class)->save($user, [$type => $prefs]);
        }

        return $notification->via($user);
    }

    public function test_setiap_toggle_di_halaman_pengaturan_mengubah_kanal_pengiriman(): void
    {
        $notifications = $this->notificationsByType();
        $service = app(NotificationPreferences::class);
        $tested = 0;

        foreach (['intern' => $this->ahmad->user, 'mentor' => $this->rudy->user, 'admin' => $this->admin] as $role => $user) {
            foreach ($service->typesFor($user) as $row) {
                $type = $row['type'];

                if ($type === 'chat_unread') {
                    continue; // khusus email, diuji terpisah
                }

                $this->assertArrayHasKey($type, $notifications, "Jenis '{$type}' ({$role}) tampil di halaman tetapi tidak punya notifikasi.");
                $n = $notifications[$type];

                $base = $this->via($n, $user, [], $type);
                $this->assertContains('database', $base, "{$role}/{$type}: Aplikasi aktif harus mengirim in-app");
                $this->assertNotContains('database', $this->via($n, $user, ['in_app' => false], $type), "{$role}/{$type}: toggle Aplikasi tidak berpengaruh");

                if ($row['email'] === null) {
                    $this->assertNotContains('mail', $base, "{$role}/{$type}: jenis tanpa toggle email tidak boleh mengirim email");
                } else {
                    $this->assertContains('mail', $base, "{$role}/{$type}: Email aktif harus mengirim email");
                    $this->assertNotContains('mail', $this->via($n, $user, ['email' => false], $type), "{$role}/{$type}: toggle Email tidak berpengaruh");
                    $this->assertContains('database', $this->via($n, $user, ['email' => false], $type), "{$role}/{$type}: mematikan Email tidak boleh mematikan in-app");
                    $this->assertSame([], $this->via($n, $user, ['in_app' => false, 'email' => false], $type));
                }

                $tested++;
            }
        }

        $this->assertGreaterThan(20, $tested);
    }

    public function test_chat_belum_dibaca_hanya_email_dan_mengikuti_toggle_email(): void
    {
        $n = (new ReflectionClass(ChatUnreadNotification::class))->newInstanceWithoutConstructor();

        foreach ([$this->ahmad->user, $this->rudy->user, $this->admin] as $user) {
            $this->assertSame(['mail'], $this->via($n, $user, [], 'chat_unread'));
            $this->assertSame([], $this->via($n, $user, ['email' => false], 'chat_unread'));
        }
    }
}
