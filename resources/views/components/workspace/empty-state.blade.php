@props(['icon' => 'fa-inbox', 'title', 'description' => null])

<div {{ $attributes->class(['flex flex-col items-center justify-center text-center px-6 py-12']) }}>
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background:#f0f4ff;color:#3b4fd8;">
        <i class="fas {{ $icon }} text-2xl"></i>
    </div>
    <h2 class="text-base font-bold text-gray-800">{{ $title }}</h2>
    @if ($description)
        <p class="mt-1 text-sm text-slate-500 max-w-sm">{{ $description }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
<x-workspace.page-css />
