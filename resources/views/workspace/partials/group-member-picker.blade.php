{{--
    Daftar centang anak bimbingan (scope wsGroupDialogs).
    Variabel: $onlyFree (bool) hanya yang belum punya kelompok; $labelledby id label grup.
    Yang sudah berkelompok tampil nonaktif dengan nama kelompoknya (1 anak magang = 1 kelompok).
--}}
<p class="text-sm text-slate-500 py-3" x-show="loading"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat anak bimbingan…</p>
<p class="ws-field-error" x-show="loadError" x-text="loadError"></p>
<div x-show="roster && !loading">
    <input type="search" class="ws-input !py-2 !text-sm mb-2" x-model="q" placeholder="Cari anak magang…" aria-label="Cari anak magang"
           x-show="{{ $onlyFree ? 'free' : '(roster ? roster.interns : [])' }}.length > 6 || q">
    <ul class="ws-tpl-pick" :class="errors.intern_ids && 'ws-tpl-pick--error'" role="group" aria-labelledby="{{ $labelledby }}">
        <template x-for="i in interns{{ $onlyFree ? '.filter((x) => !x.group)' : '' }}" :key="i.id">
            <li>
                <label class="ws-tpl-pick__item" :class="i.group && 'ws-tpl-pick__item--disabled'">
                    <input type="checkbox" class="w-4 h-4 accent-indigo-600" :checked="selected.includes(i.id)" :disabled="!!i.group"
                           x-on:change="toggle(i.id)">
                    <x-workspace.avatar-js user="i" size="xs" />
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-medium text-gray-800 truncate" x-text="i.name"></span>
                        <span class="block text-xs truncate" :class="i.group ? 'text-slate-500' : 'text-emerald-700 font-semibold'"
                              x-text="i.group ? 'Di ' + i.group.name : 'Belum berkelompok'"></span>
                    </span>
                </label>
            </li>
        </template>
        <li class="px-3 py-4 text-sm text-slate-500 text-center"
            x-show="!interns{{ $onlyFree ? '.filter((x) => !x.group)' : '' }}.length">
            <span x-text="q ? 'Tidak ada anak magang yang cocok.' : '{{ $onlyFree ? 'Semua anak bimbingan Anda sudah punya kelompok.' : 'Belum ada anak bimbingan aktif.' }}'"></span>
        </li>
    </ul>
    <p class="ws-field-error" x-show="errors.intern_ids" x-text="errors.intern_ids"></p>
</div>
<x-workspace.groups-js />
