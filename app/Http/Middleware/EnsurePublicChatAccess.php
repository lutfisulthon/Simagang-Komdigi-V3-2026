<?php

namespace App\Http\Middleware;

use App\Support\PublicChat;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Chat Publik hanya untuk user login yang aktif (lihat PublicChat::isBlocked). */
class EnsurePublicChatAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || PublicChat::isBlocked($user)) {
            abort(403, 'Akun Anda tidak aktif sehingga tidak dapat memakai Chat Publik.');
        }

        return $next($request);
    }
}
