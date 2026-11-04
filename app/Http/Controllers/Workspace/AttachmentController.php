<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Task;
use App\Models\Workspace;
use App\Services\AttachmentService;
use App\Services\TaskService;
use App\Services\WorkspacePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /** PDF, DOC/DOCX, XLS/XLSX, PNG/JPG, ZIP, maksimal 10 MB per file. */
    public const FILE_RULES = ['file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip'];

    public function __construct(
        private TaskService $tasks,
        private AttachmentService $attachments,
        private WorkspacePresenter $presenter,
    ) {
    }

    /**
     * Unggah satu berkas lebih dulu (lampiran yatim). Ditautkan ke tugas, komentar, atau pesan
     * lewat `attachment_ids` saat formulirnya dikirim.
     */
    public function upload(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', ...self::FILE_RULES],
            'workspace_id' => ['required', 'integer', 'exists:workspaces,id'],
        ]);

        $workspace = Workspace::findOrFail($data['workspace_id']);
        $this->authorize('view', $workspace);
        abort_if($workspace->is_archived, 403, 'Workspace sudah diarsipkan. Berkas tidak bisa diunggah.');

        $attachment = $this->attachments->upload($request->file('file'), $request->user(), $workspace->id);

        return response()->json(['data' => $this->presenter->attachment($attachment)], 201);
    }

    /** Unggah lampiran ke tugas. */
    public function store(Request $request, Task $task): JsonResponse
    {
        $this->authorize('comment', $task);

        $request->validate([
            'files' => ['required', 'array', 'max:10'],
            'files.*' => self::FILE_RULES,
        ]);

        $saved = $this->attachments->store($task, $request->file('files'), $request->user());

        $this->tasks->record($task, $request->user(), 'attachment_added', [
            'files' => array_map(fn (Attachment $a) => $a->original_name, $saved),
        ]);

        return response()->json([
            'data' => array_map(fn (Attachment $a) => [
                'id' => $a->id,
                'name' => $a->original_name,
                'mime' => $a->mime,
                'size' => $a->size,
                'url' => route('workspace.attachments.download', $a),
            ], $saved),
        ], 201);
    }

    /** Unduh lampiran dari storage private; hanya yang berhak melihat induknya. */
    public function download(Attachment $attachment): StreamedResponse
    {
        $this->authorize('view', $attachment);

        $disk = Storage::disk($attachment->disk);

        abort_unless($disk->exists($attachment->path), 404);

        return $disk->download($attachment->path, $attachment->original_name);
    }
}
