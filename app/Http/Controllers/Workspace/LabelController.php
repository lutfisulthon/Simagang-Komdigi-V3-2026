<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\TaskLabel;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Label per tim: admin semua tim, mentor hanya timnya sendiri. */
class LabelController extends Controller
{
    public function store(Request $request, Team $team): JsonResponse
    {
        $this->ensureCanManage($request, $team->id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:30', Rule::unique('task_labels', 'name')->where('team_id', $team->id)],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $label = TaskLabel::create(['team_id' => $team->id, 'name' => trim($data['name']), 'color' => strtolower($data['color'])]);

        return response()->json(['data' => $this->present($label)], 201);
    }

    public function update(Request $request, TaskLabel $label): JsonResponse
    {
        $this->ensureCanManage($request, $label->team_id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:30', Rule::unique('task_labels', 'name')->where('team_id', $label->team_id)->ignore($label->id)],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $label->update(['name' => trim($data['name']), 'color' => strtolower($data['color'])]);

        return response()->json(['data' => $this->present($label)]);
    }

    /** Hapus label: terlepas dari semua tugas (pivot cascade). */
    public function destroy(Request $request, TaskLabel $label): JsonResponse
    {
        $this->ensureCanManage($request, $label->team_id);

        $label->delete();

        return response()->json(null, 204);
    }

    private function ensureCanManage(Request $request, int $teamId): void
    {
        $user = $request->user();

        $allowed = $user->isSuperAdmin()
            || $user->isAdminFull()
            || ($user->isMentor() && optional($user->mentor)->team_id === $teamId);

        abort_unless($allowed, 403, 'Anda tidak berhak mengelola label tim ini.');
    }

    private function present(TaskLabel $label): array
    {
        return ['id' => $label->id, 'name' => $label->name, 'color' => $label->color];
    }
}
