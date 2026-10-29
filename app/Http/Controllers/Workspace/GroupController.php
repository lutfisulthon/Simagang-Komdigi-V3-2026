<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Services\GroupService;
use App\Services\WorkspacePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Kelompok buatan mentor (kontrak FE #18). */
class GroupController extends Controller
{
    public function __construct(private GroupService $groups, private WorkspacePresenter $presenter)
    {
    }

    private function body(Workspace $group, Request $request, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $this->presenter->workspace($group->fresh(), $request->user())], $status);
    }

    /** Anak bimbingan mentor beserta kelompoknya, dan kelompok aktif miliknya (untuk "Masukkan"). */
    public function roster(Request $request): JsonResponse
    {
        $this->authorize('createGroup', Workspace::class);

        $user = $request->user();

        $groups = Workspace::where('type', 'group')->where('is_archived', false)->where('mentor_user_id', $user->id)
            ->withCount(['members as members_count' => fn ($m) => $m->where('workspace_members.role', 'member')->whereNull('workspace_members.left_at')])
            ->orderByDesc('id')->get();

        return response()->json([
            'interns' => $this->presenter->roster($this->groups->interns($user)),
            'groups' => $groups->map(fn (Workspace $g) => $this->presenter->groupMini($g) + ['members_count' => (int) $g->members_count])->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('createGroup', Workspace::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'intern_ids' => ['required', 'array', 'min:1'],
            'intern_ids.*' => ['integer', 'distinct'],
        ]);

        $group = $this->groups->create($request->user(), $data, $data['intern_ids']);

        return $this->body($group, $request, 201);
    }

    /** `{ name?, color? }` mengubah tampilan; `{ status: "finished" }` menandai selesai magang. */
    public function update(Request $request, Workspace $group): JsonResponse
    {
        abort_unless($group->type === 'group', 404);
        $this->authorize('manageGroup', $group);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:60'],
            'color' => ['sometimes', 'nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'status' => ['sometimes', Rule::in(['finished'])],
        ]);

        $group->fill(array_intersect_key($data, array_flip(['name', 'color'])))->save();

        if (($data['status'] ?? null) === 'finished') {
            $this->groups->finish($group);
        }

        return $this->body($group, $request);
    }

    public function addMembers(Request $request, Workspace $group): JsonResponse
    {
        abort_unless($group->type === 'group', 404);
        $this->authorize('addGroupMembers', $group);

        $data = $request->validate([
            'intern_ids' => ['required', 'array', 'min:1'],
            'intern_ids.*' => ['integer', 'distinct'],
        ]);

        $this->groups->addMembers($group, $request->user(), $data['intern_ids']);

        return $this->body($group, $request);
    }
}
