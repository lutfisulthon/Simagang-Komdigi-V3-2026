<?php

namespace App\Providers;

use App\Models\Attachment;
use App\Models\Attendance;
use App\Models\Channel;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Message;
use App\Models\MicroSkillSubmission;
use App\Models\Task;
use App\Models\Workspace;
use App\Policies\AttachmentPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\ChannelPolicy;
use App\Policies\MessagePolicy;
use App\Policies\TaskPolicy;
use App\Policies\WorkspacePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Attendance::class => AttendancePolicy::class,
        Logbook::class => 'App\\Policies\\LogbookPolicy',
        MicroSkillSubmission::class => 'App\\Policies\\MicroSkillSubmissionPolicy',
        FinalReport::class => 'App\\Policies\\FinalReportPolicy',
        Workspace::class => WorkspacePolicy::class,
        Task::class => TaskPolicy::class,
        Channel::class => ChannelPolicy::class,
        Message::class => MessagePolicy::class,
        Attachment::class => AttachmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Policy modul Workspace sudah memberi akses admin sendiri dan menegakkan status arsip,
        // jadi super admin tidak boleh melewatkannya lewat Gate::before.
        $workspaceModels = [Workspace::class, Task::class, Channel::class, Message::class, Attachment::class];

        Gate::before(function ($user, $ability, $arguments = []) use ($workspaceModels) {
            $subject = $arguments[0] ?? null;
            $subjectClass = is_object($subject) ? $subject::class : $subject;

            if (is_string($subjectClass) && in_array($subjectClass, $workspaceModels, true)) {
                return null;
            }

            if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }

            return null;
        });
    }
}
