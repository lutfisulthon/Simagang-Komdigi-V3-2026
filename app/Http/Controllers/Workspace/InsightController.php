<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Intern;
use App\Services\WorkspaceInsightService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Endpoint ringkasan untuk widget dashboard, Monitoring, detail intern, dan rekap tugas. */
class InsightController extends Controller
{
    public function __construct(private WorkspaceInsightService $insights)
    {
    }

    /** Widget dashboard (intern / mentor / admin). */
    public function summary(Request $request): JsonResponse
    {
        return response()->json($this->insights->summary($request->user()));
    }

    /** Monitoring admin: progres per intern dan rekap per unit kerja. */
    public function progress(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isSuperAdmin() || $user->isAdminFull(), 403);

        $data = $request->validate([
            'intern_ids' => ['nullable', 'array', 'max:500'],
            'intern_ids.*' => ['integer'],
        ]);

        return response()->json($this->insights->progress(array_map('intval', $data['intern_ids'] ?? [])));
    }

    /** Tab tugas di detail intern: admin dan mentor pembimbingnya. */
    public function internTasks(Request $request, Intern $intern): JsonResponse
    {
        abort_unless($this->insights->canViewIntern($request->user(), $intern), 403, 'Anda tidak berhak melihat tugas intern ini.');

        return response()->json($this->insights->internTasks($intern, $request->user()));
    }

    /**
     * Rekap tugas selesai. Intern melihat miliknya sendiri; mentor/admin wajib memberi intern_id.
     * HTML = halaman cetak, JSON = data untuk form laporan akhir.
     */
    public function recap(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $data = $request->validate(['intern_id' => ['nullable', 'integer', 'exists:interns,id']]);

        if ($user->isIntern()) {
            $intern = $user->intern;
            abort_unless($intern, 403);
            abort_if(isset($data['intern_id']) && (int) $data['intern_id'] !== $intern->id, 403, 'Anda hanya dapat melihat rekap sendiri.');
        } else {
            abort_unless(isset($data['intern_id']), 422, 'intern_id wajib diisi.');
            $intern = Intern::findOrFail($data['intern_id']);
            abort_unless($this->insights->canViewIntern($user, $intern), 403, 'Anda tidak berhak melihat rekap intern ini.');
        }

        $recap = $this->insights->recap($intern);

        return $request->expectsJson() ? response()->json(['data' => $recap]) : view('workspace.recap', ['recap' => $recap]);
    }
}
