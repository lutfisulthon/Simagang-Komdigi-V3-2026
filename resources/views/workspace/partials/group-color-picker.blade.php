{{-- Pilihan warna kelompok (scope wsGroupDialogs). Variabel: $radioName --}}
<div class="flex flex-wrap gap-2">
    <template x-for="c in colors" :key="c">
        <label class="ws-swatch" :style="{ background: c }" :title="c">
            <input type="radio" name="{{ $radioName }}" class="sr-only" :value="c" x-model="color" :aria-label="'Warna ' + c">
            <i class="fas fa-check text-white text-xs" x-show="color === c"></i>
        </label>
    </template>
</div>
<x-workspace.groups-js />
