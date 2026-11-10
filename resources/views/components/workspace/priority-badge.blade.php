{{-- Label harus sama dengan WS.PRIORITY di public/js/workspace/core.js --}}
@props(['priority' => 'medium'])

@php
    $meta = [
        'low' => ['Rendah', 'fa-arrow-down'],
        'medium' => ['Sedang', 'fa-equals'],
        'high' => ['Tinggi', 'fa-arrow-up'],
        'urgent' => ['Urgent', 'fa-fire'],
    ];
    [$label, $icon] = $meta[$priority] ?? [$priority, 'fa-flag'];
@endphp

<span {{ $attributes->class(['ws-badge', 'ws-priority--' . $priority]) }}>
    <i class="fas {{ $icon }} text-[10px]"></i>
    {{ $label }}
</span>
<x-workspace.page-css />
