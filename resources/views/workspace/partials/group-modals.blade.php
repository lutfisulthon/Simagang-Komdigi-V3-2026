{{--
    Dialog kelompok (public/js/workspace/groups.js). Include sekali per halaman (mentor/admin).
    Dibuka lewat event window: ws-group-create / ws-group-assign / ws-group-add / ws-group-rename / ws-group-finish.
--}}
<div x-data="wsGroupDialogs">
    {{-- ================= Buat kelompok ================= --}}
    <x-workspace.modal name="ws-group-create" max-width="lg" labelledby="ws-group-create-heading" x-model="createOpen">
        <div class="-mt-1">
            <h2 id="ws-group-create-heading" class="text-lg font-bold text-gray-800 mb-1">Buat kelompok</h2>
            <p class="text-sm text-slate-500 mb-5">Kelompok langsung menjadi workspace: board tugas, list, dan channel diskusi untuk anggotanya.</p>

            <div class="space-y-5">
                <div>
                    <label class="ws-label" for="ws-g-name">Nama kelompok <span class="text-red-600">*</span></label>
                    <input id="ws-g-name" type="text" class="ws-input" x-model="name" maxlength="60" data-autofocus
                           placeholder="Contoh: Tim Redesign Website" :class="errors.name && 'ws-input--error'"
                           :aria-invalid="!!errors.name" aria-describedby="ws-g-name-error">
                    <p id="ws-g-name-error" class="ws-field-error" x-show="errors.name" x-text="errors.name"></p>
                </div>

                <fieldset>
                    <legend class="ws-label">Warna</legend>
                    @include('workspace.partials.group-color-picker', ['radioName' => 'ws-g-color'])
                </fieldset>

                <div>
                    <label class="ws-label" for="ws-g-desc">Deskripsi <span class="font-normal text-slate-500">(opsional)</span></label>
                    <input id="ws-g-desc" type="text" class="ws-input" x-model="description" maxlength="140" placeholder="Fokus pekerjaan kelompok ini">
                </div>

                <div>
                    <p class="ws-label" id="ws-g-members">Anggota <span class="text-red-600">*</span>
                        <span class="font-normal text-slate-500" x-show="selected.length" x-text="'· ' + selected.length + ' dipilih'"></span>
                    </p>
                    @include('workspace.partials.group-member-picker', ['onlyFree' => false, 'labelledby' => 'ws-g-members'])
                </div>
            </div>
        </div>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="createOpen = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--primary" x-on:click="create()" :disabled="saving">
                <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-plus'"></i> Buat kelompok
            </button>
        </x-slot:footer>
    </x-workspace.modal>

    {{-- ================= Masukkan satu anak magang ================= --}}
    <x-workspace.modal name="ws-group-assign" max-width="md" labelledby="ws-group-assign-heading" x-model="assignOpen">
        <template x-if="intern">
            <div class="-mt-1">
                <h2 id="ws-group-assign-heading" class="text-lg font-bold text-gray-800 mb-1" x-text="'Masukkan ' + intern.name"></h2>
                <p class="text-sm text-slate-500 mb-5">Satu anak magang hanya bisa berada di satu kelompok, dan tidak bisa dikeluarkan setelah dimasukkan.</p>
                <p class="text-sm text-slate-500" x-show="loading"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat kelompok…</p>
                <fieldset x-show="!loading">
                    <legend class="ws-label">Kelompok tujuan</legend>
                    <div class="space-y-2">
                        <template x-for="g in (roster ? roster.groups : [])" :key="g.id">
                            <label class="ws-tpl-option" :class="String(target) === String(g.id) && 'ws-tpl-option--on'">
                                <input type="radio" name="ws-g-target" class="mt-1 accent-indigo-600" :value="g.id" x-model="target">
                                <span class="ws-group-dot mt-1" :style="{ background: g.color }" aria-hidden="true"></span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-semibold text-gray-800 truncate" x-text="g.name"></span>
                                    <span class="block text-xs text-slate-600" x-text="(g.members_count - 1) + ' anak magang'"></span>
                                </span>
                            </label>
                        </template>
                        <label class="ws-tpl-option" :class="target === 'new' && 'ws-tpl-option--on'">
                            <input type="radio" name="ws-g-target" value="new" class="mt-1 accent-indigo-600" x-model="target">
                            <span>
                                <span class="block text-sm font-semibold text-gray-800"><i class="fas fa-plus text-xs mr-1"></i>Buat kelompok baru</span>
                                <span class="block text-xs text-slate-600">Beri nama sesuai pekerjaan kelompoknya.</span>
                            </span>
                        </label>
                    </div>
                    <p class="ws-field-error" x-show="errors.target || errors.intern_ids" x-text="errors.target || errors.intern_ids"></p>
                </fieldset>
            </div>
        </template>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="assignOpen = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--primary" x-on:click="assign()" :disabled="saving || loading">
                <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-arrow-right'"></i>
                <span x-text="target === 'new' ? 'Lanjut buat kelompok' : 'Masukkan'"></span>
            </button>
        </x-slot:footer>
    </x-workspace.modal>

    {{-- ================= Tambah anggota ================= --}}
    <x-workspace.modal name="ws-group-add" max-width="md" labelledby="ws-group-add-heading" x-model="addOpen">
        <template x-if="group">
            <div class="-mt-1">
                <h2 id="ws-group-add-heading" class="text-lg font-bold text-gray-800 mb-1">Tambah anggota</h2>
                <p class="text-sm text-slate-500 mb-5">
                    Pilih anak bimbingan yang belum punya kelompok. Anggota tidak bisa dikeluarkan setelah ditambahkan.
                </p>
                <p class="ws-label" id="ws-g-add-members">Anak bimbingan tanpa kelompok
                    <span class="font-normal text-slate-500" x-show="selected.length" x-text="'· ' + selected.length + ' dipilih'"></span>
                </p>
                @include('workspace.partials.group-member-picker', ['onlyFree' => true, 'labelledby' => 'ws-g-add-members'])
            </div>
        </template>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="addOpen = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--primary" x-on:click="add()" :disabled="saving || !free.length">
                <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-user-plus'"></i> Tambahkan
            </button>
        </x-slot:footer>
    </x-workspace.modal>

    {{-- ================= Ganti nama ================= --}}
    <x-workspace.modal name="ws-group-rename" max-width="md" labelledby="ws-group-rename-heading" x-model="renameOpen">
        <div class="-mt-1">
            <h2 id="ws-group-rename-heading" class="text-lg font-bold text-gray-800 mb-5">Ganti nama kelompok</h2>
            <div class="space-y-5">
                <div>
                    <label class="ws-label" for="ws-g-rename">Nama kelompok <span class="text-red-600">*</span></label>
                    <input id="ws-g-rename" type="text" class="ws-input" x-model="name" maxlength="60" data-autofocus
                           x-on:keydown.enter.prevent="rename()" :class="errors.name && 'ws-input--error'"
                           :aria-invalid="!!errors.name" aria-describedby="ws-g-rename-error">
                    <p id="ws-g-rename-error" class="ws-field-error" x-show="errors.name" x-text="errors.name"></p>
                </div>
                <fieldset>
                    <legend class="ws-label">Warna</legend>
                    @include('workspace.partials.group-color-picker', ['radioName' => 'ws-g-rename-color'])
                </fieldset>
            </div>
        </div>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="renameOpen = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--primary" x-on:click="rename()" :disabled="saving">
                <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-check'"></i> Simpan
            </button>
        </x-slot:footer>
    </x-workspace.modal>

    {{-- ================= Tandai selesai ================= --}}
    <x-workspace.modal name="ws-group-finish" max-width="sm" labelledby="ws-group-finish-heading" x-model="finishOpen">
        <template x-if="group">
            <div class="-mt-1">
                <span class="inline-flex w-11 h-11 items-center justify-center rounded-full bg-slate-100 text-slate-600 mb-3"><i class="fas fa-flag-checkered"></i></span>
                <h2 id="ws-group-finish-heading" class="text-lg font-bold text-gray-800 mb-1" x-text="'Tandai ' + group.name + ' selesai?'"></h2>
                <p class="text-sm text-slate-600">Kelompok pindah ke bagian <strong>Selesai magang</strong> dan menjadi hanya baca. Tugas, diskusi, dan nilai tetap tersimpan.</p>
                <p class="text-xs text-slate-500 mt-3">Kelompok juga otomatis selesai saat semua anggotanya selesai magang.</p>
            </div>
        </template>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="finishOpen = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--primary" x-on:click="finish()" :disabled="saving">
                <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-flag-checkered'"></i> Tandai selesai
            </button>
        </x-slot:footer>
    </x-workspace.modal>
</div>
<x-workspace.groups-js />
