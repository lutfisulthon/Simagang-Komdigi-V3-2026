{{--
    Aset Workspace di <head>. Harus di-include SEBELUM tag script Alpine supaya
    listener `alpine:init` (store notifikasi, toast) terdaftar sebelum Alpine start.
    Variabel $wsEnabled, $wsMock, $wsRole, $wsUser disiapkan di awal layouts/app.
--}}
@if ($wsEnabled)
    @php
        $wsAsset = fn (string $path) => asset($path) . '?v=' . @filemtime(public_path($path));
        $wsScripts = array_filter([
            'js/workspace/core.js',
            'js/workspace/api.js',
            'js/workspace/realtime.js',
            $wsMock ? 'js/workspace/mock.js' : null,
            $wsMock ? 'js/workspace/mock-tasks.js' : null,
            $wsMock ? 'js/workspace/mock-channels.js' : null,
            $wsMock ? 'js/workspace/mock-templates.js' : null,
            $wsMock ? 'js/workspace/mock-sprints.js' : null,
            $wsMock ? 'js/workspace/mock-automations.js' : null,
            'js/workspace/notifications.js',
            'js/workspace/groups.js',
        ]);
        // Realtime (Fase 3): aktif bila BROADCAST_DRIVER=reverb. Preview lokal: ?realtime=mock.
        $wsReverb = config('broadcasting.connections.reverb');
        $wsRealtime = null;
        if (config('broadcasting.default') === 'reverb' && ! empty($wsReverb['key'])) {
            $wsRealtime = [
                'driver' => 'reverb',
                'key' => $wsReverb['key'],
                'host' => $wsReverb['options']['host'] ?? request()->getHost(),
                'port' => (int) ($wsReverb['options']['port'] ?? 443),
                'scheme' => $wsReverb['options']['scheme'] ?? 'https',
            ];
        } elseif ($wsMock && request('realtime') === 'mock') {
            $wsRealtime = ['driver' => 'mock'];
        }
        $wsConfig = [
            'mock' => $wsMock,
            'realtime' => $wsRealtime,
            'baseUrl' => url('/'),
            'user' => ['id' => $wsUser->id, 'name' => $wsUser->name, 'role' => $wsRole],
        ];
    @endphp
    <link rel="stylesheet" href="{{ $wsAsset('css/workspace.css') }}">
    <script>
        window.SimagangWorkspace = @json($wsConfig);
    </script>
    @foreach ($wsScripts as $script)
        <script src="{{ $wsAsset($script) }}"></script>
    @endforeach
@endif
