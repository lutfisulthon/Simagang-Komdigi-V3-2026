<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Epic;
use App\Models\Workspace;
use App\Services\WorkspacePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Epic per workspace (pengelompokan tugas lintas sprint). Hanya mentor/admin, sama seperti sprint. */
class EpicController extends Controller
{
    public function __construct(private WorkspacePresenter $presenter)
    {
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }

    private function assertUniqueName(Workspace $workspace, string $name, ?int $ignoreId = null): void
    {
        $exists = $workspace->epics()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists();

        if ($exists) {
            throw ValidationException::withMessages(['name' => 'Nama epic sudah dipakai di kelompok ini.']);
        }
    }

    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('manageSprints', $workspace);

        $data = $request->validate($this->rules());
        $name = trim($data['name']);
        $this->assertUniqueName($workspace, $name);

        $epic = $workspace->epics()->create(['name' => $name, 'color' => $data['color'] ?? '#6366f1']);

        return response()->json(['data' => $this->presenter->epic($epic)], 201);
    }

    public function update(Request $request, Epic $epic): JsonResponse
    {
        $workspace = $epic->workspace;
        $this->authorize('manageSprints', $workspace);

        $data = $request->validate([
            'name' => ['sometimes'] + $this->rules()['name'],
            'color' => $this->rules()['color'],
        ]);

        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);
            $this->assertUniqueName($workspace, $data['name'], $epic->id);
        }

        $epic->fill(array_filter($data, fn ($v) => $v !== null))->save();

        return response()->json(['data' => $this->presenter->epic($epic)]);
    }

    /** Tugas yang memakai epic ini menjadi tanpa epic. */
    public function destroy(Epic $epic): JsonResponse
    {
        $this->authorize('manageSprints', $epic->workspace);

        $epic->delete();

        return response()->json(null, 204);
    }
}
