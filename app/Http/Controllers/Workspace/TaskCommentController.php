<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\AttachmentService;
use App\Services\MessageService;
use App\Services\TaskNotifier;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskCommentController extends Controller
{
    public function __construct(
        private TaskService $tasks,
        private AttachmentService $attachments,
        private MessageService $messages,
        private TaskNotifier $notifier,
    ) {
    }

    public function store(Request $request, Task $task): JsonResponse
    {
        $this->authorize('comment', $task);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'files' => ['nullable', 'array', 'max:10'],
            'files.*' => AttachmentController::FILE_RULES,
            'attachment_ids' => ['nullable', 'array', 'max:10'],
            'attachment_ids.*' => ['integer'],
        ]);

        $user = $request->user();

        $comment = DB::transaction(function () use ($request, $task, $data, $user) {
            $comment = $task->comments()->create([
                'user_id' => $user->id,
                'body' => $data['body'],
            ]);

            if ($request->hasFile('files')) {
                $this->attachments->store($comment, $request->file('files'), $user);
            }

            $this->attachments->claim($comment, $data['attachment_ids'] ?? [], $user, $task->workspace_id);

            $this->tasks->record($task, $user, 'commented', ['comment_id' => $comment->id]);

            return $comment;
        });

        // Mention @nama mendapat notifikasi khusus; penerima lain mendapat notifikasi komentar baru.
        $mentionedIds = $this->messages->resolveTaskMentionIds($task, $user, $data['body']);
        $comment->setRelation('user', $user);
        $this->notifier->mentionedInComment($task, $comment, $mentionedIds);
        $this->notifier->commented($task, $comment, $mentionedIds);

        $comment->load('attachments');

        return response()->json([
            'data' => app(\App\Services\WorkspacePresenter::class)->comment($comment),
            // Kunci lama dipertahankan untuk klien yang belum pindah ke `data`.
            'id' => $comment->id,
            'body' => $comment->body,
            'user' => ['id' => $user->id, 'name' => $user->name],
            'created_at' => $comment->created_at,
        ], 201);
    }
}
