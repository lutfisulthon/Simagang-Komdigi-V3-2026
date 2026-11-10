{{-- Ikon "?" kecil di samping judul/istilah; membuka Panduan langsung di penjelasan istilahnya (id di components/workspace/guide). --}}
@props(['term', 'label' => 'Apa artinya?'])

<button type="button" class="ws-help" title="{{ $label }}" aria-label="{{ $label }}"
        onclick="event.stopPropagation(); window.dispatchEvent(new CustomEvent('ws-guide-open', { detail: { topic: '{{ $term }}' } }))">?</button>
