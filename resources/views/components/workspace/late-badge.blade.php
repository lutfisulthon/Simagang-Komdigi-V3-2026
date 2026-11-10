{{-- "Terlambat" adalah label dari scheduler harian, bukan status/kolom board. --}}
<span {{ $attributes->class(['ws-badge', 'ws-badge--late']) }}>
    <i class="fas fa-triangle-exclamation text-[10px]"></i>
    Terlambat
</span>
<x-workspace.page-css />
