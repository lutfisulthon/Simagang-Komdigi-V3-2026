<?php

namespace App\Providers;

use App\Models\Intern;
use App\Models\Mentor;
use App\Models\User;
use App\Models\Workspace;
use App\Observers\InternObserver;
use App\Observers\MentorObserver;
use App\Services\WorkspaceProvisioner;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Intern::observe(InternObserver::class);
        Mentor::observe(MentorObserver::class);

        // Intern dihapus: arsipkan Space-nya agar tidak menjadi workspace yatim yang masih bisa ditulisi.
        // Harus sebelum baris terhapus, karena setelah itu workspaces.intern_id menjadi NULL oleh foreign key.
        Intern::deleting(function (Intern $intern) {
            Workspace::where('type', 'intern')->where('intern_id', $intern->id)->update(['is_archived' => true]);
        });

        // Mentor dihapus: interns.mentor_id di-NULL oleh foreign key tanpa event model. Catat intern bimbingannya
        // sebelum dihapus, lalu sinkronkan ulang supaya mereka dianggap tanpa mentor dan admin diberi tahu.
        $orphanedInterns = [];

        Mentor::deleting(function (Mentor $mentor) use (&$orphanedInterns) {
            $orphanedInterns[$mentor->id] = Intern::where('mentor_id', $mentor->id)->where('is_active', true)->pluck('id')->all();
        });

        Mentor::deleted(function (Mentor $mentor) use (&$orphanedInterns) {
            $internIds = $orphanedInterns[$mentor->id] ?? [];
            unset($orphanedInterns[$mentor->id]);

            foreach (Intern::whereIn('id', $internIds)->get() as $intern) {
                try {
                    app(WorkspaceProvisioner::class)->sync($intern);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });

        // Menghapus User langsung menghapus intern lewat cascade database (tanpa event model), jadi dicegat di sini juga.
        User::deleting(function (User $user) {
            Workspace::where('type', 'intern')
                ->whereIn('intern_id', Intern::where('user_id', $user->id)->select('id'))
                ->update(['is_archived' => true]);
        });
    }
}
