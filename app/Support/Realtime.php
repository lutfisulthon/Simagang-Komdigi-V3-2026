<?php

namespace App\Support;

/**
 * Konfigurasi realtime (Laravel Reverb) untuk browser. Aktif bila BROADCAST_DRIVER=reverb dan REVERB_APP_KEY terisi.
 * Dipakai Workspace (layouts/partials/workspace-head), Chat Publik (public-chat.blade.php), dan CSP (SecurityHeaders).
 */
class Realtime
{
    public static function enabled(): bool
    {
        return config('broadcasting.default') === 'reverb'
            && ! empty(config('broadcasting.connections.reverb.key'));
    }

    /** { driver, key, host, port, scheme, scripts } atau null bila realtime mati. */
    public static function clientConfig(): ?array
    {
        if (! self::enabled()) {
            return null;
        }

        $options = config('broadcasting.connections.reverb.options', []);

        return [
            'driver' => 'reverb',
            'key' => config('broadcasting.connections.reverb.key'),
            'host' => $options['host'] ?? request()->getHost(),
            'port' => (int) ($options['port'] ?? 443),
            'scheme' => $options['scheme'] ?? 'https',
            'scripts' => [
                asset('vendor/echo/pusher.min.js'),
                asset('vendor/echo/echo.iife.js'),
            ],
        ];
    }

    /** Sumber tambahan untuk CSP connect-src, mis. "wss://ws.contoh.id:443". */
    public static function connectSource(): ?string
    {
        $cfg = self::clientConfig();
        if (! $cfg) {
            return null;
        }

        return ($cfg['scheme'] === 'https' ? 'wss' : 'ws') . '://' . $cfg['host'] . ':' . $cfg['port'];
    }
}
