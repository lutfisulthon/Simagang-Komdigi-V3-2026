<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Services\WorkspaceDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private WorkspaceDashboardService $dashboard)
    {
    }

    /** Ringkasan sesuai role: intern, mentor, atau admin. */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isSuperAdmin() || $user->isAdminFull()) {
            return response()->json(['role' => 'admin', 'data' => $this->dashboard->adminRecap()]);
        }

        if ($user->isMentor() && $user->mentor) {
            return response()->json(['role' => 'mentor', 'data' => $this->dashboard->forMentor($user, $user->mentor)]);
        }

        if ($user->isIntern() && $user->intern) {
            return response()->json(['role' => 'intern', 'data' => $this->dashboard->forIntern($user, $user->intern)]);
        }

        abort(403);
    }
}
