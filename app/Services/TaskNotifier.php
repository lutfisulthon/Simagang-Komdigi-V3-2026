<?php

namespace App\Services;

use App\Models\Intern;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\Sprint;
use App\Notifications\DeadlineExtendedNotification;
use App\Notifications\SprintLeaderNotification;
use App\Notifications\SprintToDivideNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCommentedNotification;
use App\Notifications\TaskCommentMentionNotification;
use App\Notifications\TaskReviewedNotification;
use App\Notifications\TaskReviewRequestedNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * Notifikasi in-app untuk event tugas. Kegagalan kirim tidak boleh menggagalkan aksi utama.
 * Dipanggil setelah transaksi selesai supaya tidak ada notifikasi untuk perubahan yang di-rollback.
 */
class TaskNotifier
{
    /** @param  array<int, int>  $internIds */
    public function assigned(Task $task, array $internIds, User $actor): void
    {
        $this->send($this->internUsers($internIds)->reject(fn (User $u) => $u->id === $actor->id), new TaskAssignedNotification($task, $actor));
    }

    /** Intern ditunjuk menjadi ketua sprint. */
    public function sprintLeader(Sprint $sprint, User $actor): void
    {
        $leader = $sprint->leader?->user;

        if ($leader && $leader->id !== $actor->id) {
            $this->send(collect([$leader]), new SprintLeaderNotification($sprint, $actor, $sprint->tasks()->count()));
        }
    }

    /**
     * Ketua diingatkan ada tugas sprint yang belum dibagi.
     *
     * @param  array<int, string>  $titles
     */
    public function sprintToDivide(Sprint $sprint, int $count, array $titles): void
    {
        $leader = $sprint->leader?->user;

        if ($leader && $count > 0) {
            $this->send(collect([$leader]), new SprintToDivideNotification($sprint, $count, $titles));
        }
    }

    /** Deadline tugas diperpanjang: semua assignee diberi tahu. */
    public function deadlineExtended(Task $task, User $actor, \Illuminate\Support\Carbon $from, ?string $reason): void
    {
        $this->send(
            $this->assigneeUsers($task)->reject(fn (User $u) => $u->id === $actor->id),
            new DeadlineExtendedNotification($task, $actor, $from, $reason)
        );
    }

    /** Tugas masuk Review: beri tahu mentor/admin pembuat tugas. */
    public function reviewRequested(Task $task, User $actor): void
    {
        $creator = $task->creator;

        if ($creator && $creator->id !== $actor->id) {
            $this->send(collect([$creator]), new TaskReviewRequestedNotification($task, $actor));
        }
    }

    /** Tugas Selesai atau Revisi: beri tahu intern yang mengerjakan. */
    public function reviewed(Task $task, User $actor): void
    {
        $this->send(
            $this->assigneeUsers($task)->reject(fn (User $u) => $u->id === $actor->id),
            new TaskReviewedNotification($task, $actor)
        );
    }

    /**
     * Komentar baru: pembuat tugas dan semua assignee, kecuali penulis dan yang sudah
     * mendapat notifikasi mention.
     *
     * @param  array<int, int>  $mentionedIds
     */
    public function commented(Task $task, TaskComment $comment, array $mentionedIds = []): void
    {
        $recipients = $this->assigneeUsers($task)
            ->push($task->creator)
            ->filter()
            ->unique('id')
            ->reject(fn (User $u) => $u->id === $comment->user_id || in_array($u->id, $mentionedIds, true));

        $this->send($recipients, new TaskCommentedNotification($task, $comment));
    }

    /** @param  array<int, int>  $userIds */
    public function mentionedInComment(Task $task, TaskComment $comment, array $userIds): void
    {
        $this->send(User::whereIn('id', $userIds)->get(), new TaskCommentMentionNotification($task, $comment));
    }

    /** @return Collection<int, User> */
    private function assigneeUsers(Task $task): Collection
    {
        return $task->assignees()->with('user')->get()->pluck('user')->filter()->unique('id');
    }

    /**
     * @param  array<int, int>  $internIds
     * @return Collection<int, User>
     */
    private function internUsers(array $internIds): Collection
    {
        return Intern::with('user')->whereIn('id', $internIds)->get()->pluck('user')->filter()->unique('id');
    }

    /** @param  Collection<int, User>  $users */
    private function send(Collection $users, Notification $notification): void
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
