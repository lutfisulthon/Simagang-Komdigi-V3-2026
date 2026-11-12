<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response instanceof Response) {
            $response = response($response);
        }

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');

        // Websocket Reverb berjalan di host/port sendiri, jadi perlu diizinkan eksplisit.
        $realtime = \App\Support\Realtime::connectSource();

        $csp = "default-src 'self'; "
            . "connect-src 'self'" . ($realtime ? " {$realtime}" : '') . "; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; "
            . "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:; "
            . "img-src 'self' data: blob: https://i.ytimg.com https://img.youtube.com https://i3.ytimg.com https://i4.ytimg.com https://ui-avatars.com; "
            . "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com; "
            . "object-src 'none'; "
            . "frame-ancestors 'none';";

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(self), payment=()');

        // HSTS hanya bermakna lewat HTTPS; jangan dikirim di HTTP lokal.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Prevent stale authenticated pages/files from being reused after user switch.
        if ($request->user()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            $response->headers->set('Vary', 'Cookie, Authorization');
        }

        return $response;
    }
}