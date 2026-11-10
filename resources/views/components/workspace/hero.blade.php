{{--
    Slot default = area aksi di kanan (tombol). Slot "meta" = baris info kecil di bawah subjudul.
    $color  warna aksen hex (mis. warna kelompok); tint hero dihitung dari sini. Default ungu (warna menu Workspace).
    $initials / $icon  isi chip di kiri judul (inisial kelompok, atau ikon Font Awesome tanpa awalan "fa-").
--}}
@props(['title', 'subtitle' => null, 'eyebrow' => null, 'color' => null, 'icon' => null, 'initials' => null])

@php
    $heroHex = preg_match('/^#?[0-9a-f]{6}$/i', (string) $color) ? '#' . ltrim($color, '#') : '#8b5cf6';
    [$hr, $hg, $hb] = sscanf($heroHex, '#%02x%02x%02x');
    $heroMix = fn (float $p) => sprintf('#%02x%02x%02x', round($hr + (255 - $hr) * $p), round($hg + (255 - $hg) * $p), round($hb + (255 - $hb) * $p));
    $heroInk = sprintf('#%02x%02x%02x', round($hr * .5), round($hg * .5), round($hb * .5));
@endphp

<div {{ $attributes->class(['ws-hero']) }}
     style="--hc: {{ $heroHex }}; --hs: {{ $heroMix(.91) }}; --hb: {{ $heroMix(.7) }}; --hx: {{ $heroInk }}">
    <div class="px-5 py-6 sm:px-6 sm:py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4 min-w-0">
                @if ($initials || $icon)
                    <span class="ws-hero__chip {{ $initials ? '' : 'ws-hero__chip--icon' }}" aria-hidden="true">
                        @if ($initials) {{ $initials }} @else <i class="fas fa-{{ $icon }}"></i> @endif
                    </span>
                @endif
                <div class="min-w-0">
                    @if ($eyebrow)
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-200/80 mb-1.5">{{ $eyebrow }}</p>
                    @endif
                    <h1 class="text-xl sm:text-3xl font-bold leading-tight text-white mb-1 break-words">{{ $title }}</h1>
                    @if ($subtitle)
                        <p class="text-sm sm:text-base text-blue-200">{{ $subtitle }}</p>
                    @endif
                    @isset($meta)
                        <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs sm:text-sm text-blue-100">{{ $meta }}</div>
                    @endisset
                </div>
            </div>
            @if ($slot->isNotEmpty())
                <div class="ws-hero__actions flex flex-wrap items-center gap-2 shrink-0">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
</div>
<x-workspace.page-css />
