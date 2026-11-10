@props(['name' => '?', 'src' => null, 'size' => 'md'])

@php
    $initials = collect(preg_split('/\s+/', trim($name ?: '?')))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp

<span {{ $attributes->class(['ws-avatar', 'ws-avatar--' . $size]) }} title="{{ $name }}">
    @if ($src)
        <img src="{{ $src }}" alt="{{ $name }}" loading="lazy">
    @else
        {{ $initials }}
    @endif
</span>
<x-workspace.page-css />
