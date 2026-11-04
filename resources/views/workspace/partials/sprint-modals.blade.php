{{-- Dialog sprint (wsSprintDialogs, public/js/workspace/backlog.js). Include sekali di halaman kelompok (mentor/admin). --}}
<div x-data="wsSprintDialogs(@js(['workspaceId' => $workspace['id']]))">
    {{-- ================= Buat / ubah sprint ================= --}}
    <x-workspace.modal name="ws-sprint-form" max-width="lg" labelledby="ws-sprint-form-heading" x-model="formOpen">
        <div class="-mt-1">
            <h2 id="ws-sprint-form-heading" class="text-lg font-bold text-gray-800 mb-1" x-text="sprint ? 'Ubah ' + sprint.name : 'Buat sprint'"></h2>
            <p class="text-sm text-slate-500 mb-5" x-show="!sprint">Sprint berisi tugas dengan satu deadline dan satu ketua tim. Ketua yang membagi tugas ke anggota di Board.</p>
            <div class="space-y-5" :class="sprint && 'mt-4'">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="ws-label" for="ws-sp-name">Nama sprint</label>
                        <input id="ws-sp-name" type="text" class="ws-input" x-model="form.name" maxlength="60" data-autofocus
                               :placeholder="sprint ? '' : 'Kosongkan: Sprint N'" :class="errors.name && 'ws-input--error'">
                        <p class="ws-field-error" x-show="errors.name" x-text="errors.name"></p>
                    </div>
                    <div>
                        <label class="ws-label" for="ws-sp-end">Deadline sprint <span class="text-red-600">*</span></label>
                        <input id="ws-sp-end" type="date" class="ws-input" x-model="form.end_date" :class="errors.end_date && 'ws-input--error'" aria-describedby="ws-sp-end-hint">
                        <p class="ws-field-error" x-show="errors.end_date" x-text="errors.end_date"></p>
                        <p id="ws-sp-end-hint" class="text-xs text-slate-500 mt-1" x-show="!errors.end_date && !sprint">Deadline tugas baru mengikuti tanggal ini.</p>
                    </div>
                </div>
                <div>
                    <label class="ws-label" for="ws-sp-goal">Deskripsi / tujuan <span class="font-normal text-slate-500">(opsional)</span></label>
                    <input id="ws-sp-goal" type="text" class="ws-input" x-model="form.goal" maxlength="140" placeholder="Contoh: Selesaikan prototipe halaman profil">
                </div>
                <div class="grid grid-cols-2 gap-3" x-show="sprint && sprint.status === 'active'">
                    <div>
                        <label class="ws-label" for="ws-sp-start">Mulai</label>
                        <input id="ws-sp-start" type="date" class="ws-input" x-model="form.start_date" :class="errors.start_date && 'ws-input--error'">
                        <p class="ws-field-error" x-show="errors.start_date" x-text="errors.start_date"></p>
                    </div>
                </div>
                <fieldset>
                    <legend class="ws-label">Ketua tim <span class="text-red-600" x-show="sprint && sprint.status === 'active'">*</span>
                        <span class="font-normal text-slate-500">· membagi tugas sprint ke anggota</span></legend>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="m in members" :key="m.id">
                            <label class="ws-leader-opt" :class="String(form.leader_id) === String(m.id) && 'ws-leader-opt--on'">
                                <input type="radio" class="sr-only" :name="'ws-sp-leader'" :value="String(m.id)" x-model="form.leader_id">
                                <x-workspace.avatar-js user="m" size="xs" />
                                <span class="text-sm font-medium" x-text="m.name"></span>
                                <i class="fas fa-crown text-[10px] text-amber-500" x-show="String(form.leader_id) === String(m.id)" aria-hidden="true"></i>
                            </label>
                        </template>
                    </div>
                    <p class="ws-field-error" x-show="errors.leader_id" x-text="errors.leader_id"></p>
                </fieldset>

                {{-- Tugas cepat (hanya saat membuat) --}}
                <div x-show="!sprint">
                    <p class="ws-label" id="ws-sp-tasks">Tugas <span class="font-normal text-slate-500" x-text="form.tasks.length ? '(' + form.tasks.length + ')' : '(opsional)'"></span></p>
                    <ul class="ws-quick-tasks" aria-labelledby="ws-sp-tasks" x-show="form.tasks.length">
                        <template x-for="(t, i) in form.tasks" :key="t.key">
                            <li class="ws-quick-task">
                                <span class="ws-mono text-xs ws-text-faint w-5 shrink-0" x-text="i + 1"></span>
                                <input type="text" class="ws-input !py-1.5 !text-sm flex-1 min-w-0" x-model="t.title" maxlength="150" :aria-label="'Judul tugas ' + (i + 1)"
                                       :class="errors['tasks.' + i + '.title'] && 'ws-input--error'">
                                <select class="ws-input !py-1.5 !text-sm !w-auto" x-model="t.priority" :aria-label="'Prioritas tugas ' + (i + 1)">
                                    <option value="low">Rendah</option><option value="medium">Sedang</option><option value="high">Tinggi</option><option value="urgent">Urgent</option>
                                </select>
                                <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-on:click="removeTask(i)" :aria-label="'Hapus tugas ' + (i + 1)"><i class="fas fa-xmark"></i></button>
                            </li>
                        </template>
                    </ul>
                    <div class="ws-quick-task ws-quick-task--new">
                        <i class="fas fa-plus text-xs ws-text-faint w-5 text-center shrink-0" aria-hidden="true"></i>
                        <input type="text" class="ws-input !py-1.5 !text-sm flex-1 min-w-0" x-model="newTitle" maxlength="150"
                               placeholder="Ketik judul tugas lalu Enter…" aria-label="Judul tugas baru" x-on:keydown.enter.prevent="addTask()">
                        <select class="ws-input !py-1.5 !text-sm !w-auto" x-model="newPriority" aria-label="Prioritas tugas baru">
                            <option value="low">Rendah</option><option value="medium">Sedang</option><option value="high">Tinggi</option><option value="urgent">Urgent</option>
                        </select>
                        <button type="button" class="ws-btn ws-btn--soft ws-btn--sm" x-on:click="addTask()" :disabled="!newTitle.trim()">Tambah</button>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Detail lain (deskripsi, label, checklist) bisa dilengkapi nanti dari detail tugas. Tugas dari Backlog bisa diseret ke sprint ini.</p>
                </div>
            </div>
        </div>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="formOpen = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--primary" x-on:click="saveForm()" :disabled="saving">
                <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                <span x-text="sprint ? 'Simpan' : (form.tasks.length ? 'Buat sprint + ' + form.tasks.length + ' tugas' : 'Buat sprint')"></span>
            </button>
        </x-slot:footer>
    </x-workspace.modal>

    {{-- ================= Mulai sprint ================= --}}
    <x-workspace.modal name="ws-sprint-start" max-width="md" labelledby="ws-sprint-start-heading" x-model="startOpen">
        <template x-if="sprint">
            <div class="-mt-1">
                <h2 id="ws-sprint-start-heading" class="text-lg font-bold text-gray-800 mb-1">Mulai sprint</h2>
                <p class="text-sm text-slate-500 mb-5">
                    <span x-text="$store.wsTasks.section(sprint.id).length + ' tugas'"></span> akan tampil di Board.
                    Ketua tim mendapat notifikasi (email) untuk membagi tugas ke anggota.
                </p>
                <div class="space-y-4">
                    <div>
                        <label class="ws-label" for="ws-ss-name">Nama sprint <span class="text-red-600">*</span></label>
                        <input id="ws-ss-name" type="text" class="ws-input" x-model="form.name" maxlength="60" data-autofocus :class="errors.name && 'ws-input--error'">
                        <p class="ws-field-error" x-show="errors.name" x-text="errors.name"></p>
                    </div>
                    <div>
                        <label class="ws-label" for="ws-ss-weeks">Durasi</label>
                        <select id="ws-ss-weeks" class="ws-input" x-model="form.weeks">
                            <option value="1">1 minggu</option>
                            <option value="2">2 minggu</option>
                            <option value="3">3 minggu</option>
                            <option value="4">4 minggu</option>
                            <option value="custom">Atur sendiri</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="ws-label" for="ws-ss-start">Mulai <span class="text-red-600">*</span></label>
                            <input id="ws-ss-start" type="date" class="ws-input" x-model="form.start_date" :class="errors.start_date && 'ws-input--error'">
                            <p class="ws-field-error" x-show="errors.start_date" x-text="errors.start_date"></p>
                        </div>
                        <div>
                            <label class="ws-label" for="ws-ss-end">Selesai <span class="text-red-600">*</span></label>
                            <input id="ws-ss-end" type="date" class="ws-input" x-model="form.end_date" :readonly="form.weeks !== 'custom'"
                                   :class="[errors.end_date && 'ws-input--error', form.weeks !== 'custom' && 'bg-slate-50']">
                            <p class="ws-field-error" x-show="errors.end_date" x-text="errors.end_date"></p>
                        </div>
                    </div>
                <fieldset>
                    <legend class="ws-label">Ketua tim <span class="text-red-600" x-show="true">*</span>
                        <span class="font-normal text-slate-500">· membagi tugas sprint ke anggota</span></legend>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="m in members" :key="m.id">
                            <label class="ws-leader-opt" :class="String(form.leader_id) === String(m.id) && 'ws-leader-opt--on'">
                                <input type="radio" class="sr-only" :name="'ws-ss-leader'" :value="String(m.id)" x-model="form.leader_id">
                                <x-workspace.avatar-js user="m" size="xs" />
                                <span class="text-sm font-medium" x-text="m.name"></span>
                                <i class="fas fa-crown text-[10px] text-amber-500" x-show="String(form.leader_id) === String(m.id)" aria-hidden="true"></i>
                            </label>
                        </template>
                    </div>
                    <p class="ws-field-error" x-show="errors.leader_id" x-text="errors.leader_id"></p>
                </fieldset>
                    <div>
                        <label class="ws-label" for="ws-ss-goal">Tujuan sprint <span class="font-normal text-slate-500">(opsional)</span></label>
                        <textarea id="ws-ss-goal" rows="2" class="ws-input" x-model="form.goal" maxlength="140" placeholder="Apa yang ingin dicapai kelompok di sprint ini?"></textarea>
                    </div>
                    <p class="ws-field-error" x-show="errors.sprint" x-text="errors.sprint"></p>
                </div>
            </div>
        </template>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="startOpen = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--primary" x-on:click="start()" :disabled="saving">
                <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-play'"></i> Mulai sprint
            </button>
        </x-slot:footer>
    </x-workspace.modal>

    {{-- ================= Selesaikan sprint ================= --}}
    <x-workspace.modal name="ws-sprint-complete" max-width="md" labelledby="ws-sprint-complete-heading" x-model="completeOpen">
        <template x-if="sprint">
            <div class="-mt-1">
                <h2 id="ws-sprint-complete-heading" class="text-lg font-bold text-gray-800 mb-4" x-text="'Selesaikan ' + sprint.name"></h2>
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="ws-tile !bg-emerald-50 !border-emerald-200">
                        <p class="text-2xl font-bold text-emerald-800" x-text="completeStats.done"></p>
                        <p class="text-xs font-semibold text-emerald-800">tugas selesai</p>
                    </div>
                    <div class="ws-tile !bg-amber-50 !border-amber-200">
                        <p class="text-2xl font-bold text-amber-800" x-text="completeStats.total - completeStats.done"></p>
                        <p class="text-xs font-semibold text-amber-800">belum selesai</p>
                    </div>
                </div>
                <div x-show="completeStats.total - completeStats.done > 0">
                    <label class="ws-label" for="ws-sc-move">Pindahkan tugas yang belum selesai ke</label>
                    <select id="ws-sc-move" class="ws-input" x-model="moveTo">
                        <option value="backlog">Backlog</option>
                        <template x-for="sp in plannedSprints" :key="sp.id">
                            <option :value="String(sp.id)" x-text="sp.name"></option>
                        </template>
                        <option value="new">Sprint baru</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-2">Tugas tetap dengan status, assignee, dan deadline masing-masing.</p>
                </div>
                <p class="text-sm text-slate-600" x-show="completeStats.total - completeStats.done === 0">Semua tugas sprint ini sudah selesai. 🎉</p>
                <p class="ws-field-error" x-show="errors.sprint || errors.move_to" x-text="errors.sprint || errors.move_to"></p>
            </div>
        </template>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="completeOpen = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--primary" x-on:click="complete()" :disabled="saving">
                <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-flag-checkered'"></i> Selesaikan sprint
            </button>
        </x-slot:footer>
    </x-workspace.modal>

    {{-- ================= Hapus sprint ================= --}}
    <x-workspace.modal name="ws-sprint-delete" max-width="sm" labelledby="ws-sprint-delete-heading" x-model="deleteOpen">
        <template x-if="sprint">
            <div class="-mt-1">
                <h2 id="ws-sprint-delete-heading" class="text-lg font-bold text-gray-800 mb-1" x-text="'Hapus ' + sprint.name + '?'"></h2>
                <p class="text-sm text-slate-600" x-text="$store.wsTasks.section(sprint.id).length + ' tugas di sprint ini kembali ke Backlog. Tugasnya tidak ikut terhapus.'"></p>
            </div>
        </template>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="deleteOpen = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--danger" x-on:click="remove()" :disabled="saving">
                <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-trash'"></i> Hapus sprint
            </button>
        </x-slot:footer>
    </x-workspace.modal>
</div>

{{-- ================= Epic (kelola) ================= --}}
<div x-data="wsEpics(@js(['workspaceId' => $workspace['id']]))">
    <x-workspace.modal name="ws-epics" max-width="md" labelledby="ws-epics-heading" x-model="open">
        <div class="-mt-1">
            <h2 id="ws-epics-heading" class="text-lg font-bold text-gray-800 mb-1">Epic</h2>
            <p class="text-sm text-slate-500 mb-4">Kelompokkan tugas yang berkaitan lintas sprint. Pilih epic saat membuat atau mengubah tugas.</p>

            <ul class="space-y-2 mb-4" x-show="epics.length">
                <template x-for="e in epics" :key="e.id">
                    <li class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2">
                        <span class="ws-label-chip__dot" :style="{ background: e.color }"></span>
                        <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-800" x-text="e.name"></span>
                        <span class="text-xs text-slate-500 whitespace-nowrap" x-text="usage(e.id) + ' tugas'"></span>
                        <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-on:click="edit(e)" :aria-label="'Ubah ' + e.name"><i class="fas fa-pen"></i></button>
                        <template x-if="confirmId !== e.id">
                            <button type="button" class="ws-icon-btn ws-icon-btn--sm text-red-600" x-on:click="confirmId = e.id" :aria-label="'Hapus ' + e.name"><i class="fas fa-trash"></i></button>
                        </template>
                        <template x-if="confirmId === e.id">
                            <span class="flex items-center gap-1">
                                <button type="button" class="ws-btn ws-btn--danger ws-btn--sm" :disabled="saving" x-on:click="remove(e)">Hapus</button>
                                <button type="button" class="ws-btn ws-btn--ghost ws-btn--sm" x-on:click="confirmId = null">Batal</button>
                            </span>
                        </template>
                    </li>
                </template>
            </ul>
            <p class="text-sm text-slate-500 mb-4" x-show="!epics.length">Belum ada epic.</p>

            <form x-on:submit.prevent="save()" novalidate class="rounded-xl bg-slate-50 p-3">
                <p class="ws-label" x-text="editingId ? 'Ubah epic' : 'Epic baru'"></p>
                <div class="flex flex-wrap items-center gap-2">
                    <input type="text" class="ws-input flex-1 min-w-[10rem]" x-model="form.name" maxlength="60" placeholder="Nama epic, mis. Autentikasi" aria-label="Nama epic" x-ref="name">
                    <div class="flex items-center gap-1.5" role="group" aria-label="Warna epic">
                        <template x-for="c in colors" :key="c">
                            <button type="button" class="h-6 w-6 rounded-full border-2" :style="{ background: c }" :class="form.color === c ? 'border-gray-800' : 'border-transparent'"
                                    :aria-pressed="(form.color === c).toString()" :aria-label="'Warna ' + c" x-on:click="form.color = c"></button>
                        </template>
                    </div>
                </div>
                <p class="ws-field-error" x-show="errors.name" x-text="errors.name"></p>
                <div class="mt-3 flex justify-end gap-2">
                    <button type="button" class="ws-btn ws-btn--ghost ws-btn--sm" x-show="editingId" x-on:click="reset()">Batal ubah</button>
                    <button type="submit" class="ws-btn ws-btn--primary ws-btn--sm" :disabled="saving">
                        <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-plus'"></i> <span x-text="editingId ? 'Simpan' : 'Tambah epic'"></span>
                    </button>
                </div>
            </form>
        </div>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="open = false">Tutup</button>
        </x-slot:footer>
    </x-workspace.modal>
</div>
