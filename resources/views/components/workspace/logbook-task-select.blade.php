{{--
    Dropdown opsional "Tugas terkait" di form logbook intern (PRD: logbooks.task_id nullable).
    Pilihan dimuat lewat WS.api.tasks.mine() (GET /workspace/tasks/mine); nilai dikirim sebagai field `task_id`.
    Tidak dirender selama modul Workspace belum aktif.
--}}
@props(['selected' => null])

@if (Route::has('workspace.index'))
    @once
        @push('scripts')
            <script src="{{ asset('js/workspace/dashboard.js') }}?v={{ @filemtime(public_path('js/workspace/dashboard.js')) }}"></script>
        @endpush
    @endonce

    <div {{ $attributes->class(['mb-6']) }} x-data="wsLogbookTask(@js(['selected' => $selected ? (int) $selected : null]))">
        <label for="task_id" class="block text-sm font-semibold text-gray-700 mb-2">
            <i class="fas fa-clipboard-list text-blue-500 mr-2"></i>Tugas Terkait (Opsional)
        </label>
        <select name="task_id" id="task_id" x-model="value" :disabled="loading" aria-describedby="task_id_hint"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
            <option value="">{{ '— Tidak terkait tugas —' }}</option>
            @if ($selected)
                {{-- Nilai lama tetap terkirim walau daftar belum/gagal dimuat. --}}
                <option value="{{ (int) $selected }}" x-show="!loaded">Tugas terpilih</option>
            @endif
            <template x-for="g in groups" :key="g.name">
                <optgroup :label="g.name">
                    <template x-for="t in g.tasks" :key="t.id">
                        <option :value="String(t.id)" :selected="String(t.id) === value" x-text="t.code + ' · ' + t.title + (t.status === 'done' ? ' (selesai)' : '')"></option>
                    </template>
                </optgroup>
            </template>
        </select>
        <p id="task_id_hint" class="mt-2 text-sm text-gray-500 flex items-start gap-1.5">
            <i class="fas mt-0.5" :class="error ? 'fa-triangle-exclamation text-amber-500' : 'fa-info-circle'"></i>
            <span x-text="hint">Hubungkan kegiatan hari ini dengan tugas di Workspace.</span>
            <button type="button" class="font-semibold text-blue-600 hover:underline" x-show="error" x-cloak x-on:click="load()">Coba lagi</button>
        </p>
        @error('task_id')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
        @enderror
    </div>
@endif
<x-workspace.page-css />
