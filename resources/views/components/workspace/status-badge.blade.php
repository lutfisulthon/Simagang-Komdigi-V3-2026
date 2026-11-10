{{-- Label harus sama dengan WS.STATUS di public/js/workspace/core.js --}}
@props(['status'])

@php
    $labels = [
        'todo' => 'To Do',
        'in_progress' => 'In Progress',
        'review' => 'Review',
        'revision' => 'Revisi',
        'done' => 'Selesai',
    ];
@endphp

<span {{ $attributes->class(['ws-badge', 'ws-status--' . $status]) }}>
    <span class="ws-badge__dot"></span>
    {{ $labels[$status] ?? $status }}
</span>
<x-workspace.page-css />
