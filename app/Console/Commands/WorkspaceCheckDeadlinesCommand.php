<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\Sprint;
use App\Models\Task;
use App\Notifications\TaskDueSoonNotification;
use App\Notifications\TaskOverdueNotification;
use App\Services\SprintService;
use App\Services\TaskNotifier;
use App\Services\TaskService;
use Illuminate\Console\Command;

class WorkspaceCheckDeadlinesCommand extends Command
{
    protected $signature = 'workspace:check-deadlines';

    protected $description = 'Tandai tugas Terlambat, kirim pengingat H-1 deadline (sekali per tugas), dan ingatkan ketua sprint membagi tugas (H+1)';

    public function handle(TaskService $tasks, SprintService $sprints, TaskNotifier $notifier): int
    {
        $overdue = $this->markOverdue($tasks);
        $reminded = $this->sendReminders();
        $leaders = $this->remindLeaders($sprints, $notifier);

        $this->info("Ditandai terlambat: {$overdue}");
        $this->info("Pengingat H-1 terkirim: {$reminded}");
        $this->info("Pengingat ketua sprint terkirim: {$leaders}");

        return self::SUCCESS;
    }

    /** Lewat deadline dan intern masih perlu mengerjakan (belum Review/Selesai): tandai sekali, beri tahu intern dan mentor pembuat. */
    private function markOverdue(TaskService $tasks): int
    {
        $count = 0;

        Task::query()
            ->whereNull('overdue_at')
            ->where('due_at', '<', now())
            ->whereIn('status', [TaskStatus::Todo->value, TaskStatus::InProgress->value, TaskStatus::Revision->value])
            ->whereHas('workspace', fn ($q) => $q->where('is_archived', false))
            ->with(['assignees.user', 'creator'])
            ->chunkById(100, function ($batch) use ($tasks, &$count) {
                foreach ($batch as $task) {
                    $task->forceFill(['overdue_at' => now()])->save();
                    $tasks->record($task, null, 'overdue');

                    $recipients = $task->assignees->pluck('user')->push($task->creator)->filter()->unique('id');
                    $this->notifyAll($recipients, new TaskOverdueNotification($task));

                    $count++;
                }
            });

        return $count;
    }

    /** Deadline dalam 24 jam ke depan dan intern masih perlu mengerjakan (belum Review/Selesai). */
    private function sendReminders(): int
    {
        $count = 0;

        Task::query()
            ->whereNull('reminded_at')
            ->whereBetween('due_at', [now(), now()->addDay()])
            ->whereIn('status', [TaskStatus::Todo->value, TaskStatus::InProgress->value, TaskStatus::Revision->value])
            ->whereHas('workspace', fn ($q) => $q->where('is_archived', false))
            ->with('assignees.user')
            ->chunkById(100, function ($batch) use (&$count) {
                foreach ($batch as $task) {
                    $task->forceFill(['reminded_at' => now()])->save();

                    $this->notifyAll($task->assignees->pluck('user')->filter()->unique('id'), new TaskDueSoonNotification($task));

                    $count++;
                }
            });

        return $count;
    }

    /** Sprint berjalan yang masih punya tugas belum dibagi: ingatkan ketua, paling cepat 24 jam sejak pengingat terakhir. */
    private function remindLeaders(SprintService $sprints, TaskNotifier $notifier): int
    {
        $count = 0;

        Sprint::query()
            ->where('status', Sprint::ACTIVE)
            ->where(fn ($q) => $q->whereNull('divide_reminded_at')->orWhere('divide_reminded_at', '<=', now()->subDay()))
            ->whereHas('workspace', fn ($q) => $q->where('is_archived', false))
            ->with('leader.user')
            ->chunkById(100, function ($batch) use ($sprints, $notifier, &$count) {
                foreach ($batch as $sprint) {
                    $undivided = $sprints->undivided($sprint);

                    if ($undivided->isEmpty()) {
                        continue;
                    }

                    $notifier->sprintToDivide($sprint, $undivided->count(), $undivided->pluck('title')->take(5)->all());
                    $sprint->forceFill(['divide_reminded_at' => now()])->save();
                    $count++;
                }
            });

        return $count;
    }

    private function notifyAll($users, $notification): void
    {
        foreach ($users as $user) {
            try {
                $user->notify($notification);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
