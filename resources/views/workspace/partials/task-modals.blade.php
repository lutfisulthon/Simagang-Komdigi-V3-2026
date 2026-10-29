{{--
    Modal tugas: form buat/edit, review (mentor), kirim hasil (intern), hapus.
    Variabel: $workspace, $assignableInterns, $labels, $selfInternId (int|null — intern membuat to-do pribadi),
              $templates (opsional, Template[] tim — untuk "Pakai template")
--}}

{{-- ================= Form buat / edit ================= --}}
<div x-data="wsTaskForm(@js(['workspaceId' => $workspace['id'], 'interns' => $assignableInterns, 'labels' => $labels, 'selfInternId' => $selfInternId ?? null, 'templates' => $templates ?? [], 'teamId' => $workspace['team']['id'] ?? null, 'canManageLabels' => $can['manage_labels'] ?? false]))">
    <x-workspace.modal name="ws-task-form" max-width="lg" labelledby="ws-task-form-heading" x-model="open">
        <form x-on:submit.prevent="submit()" novalidate class="-mt-1">
            <h2 id="ws-task-form-heading" class="text-lg font-bold text-gray-800 mb-5" x-text="editing ? 'Edit ' + editing.code : 'Buat tugas baru'"></h2>

            <div class="space-y-5">
                {{-- Pakai template (hanya saat membuat, bila tim punya template) --}}
                <div class="ws-tpl-use" x-show="!editing && !selfOnly && templates.length">
                    <label class="ws-label !mb-1" for="ws-f-template"><i class="fas fa-copy mr-1 text-indigo-500"></i>Pakai template</label>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <select id="ws-f-template" class="ws-input !py-2 !text-sm" :value="form.template_id" x-on:change="applyTemplate($event.target.value)">
                            <option value="">Tanpa template</option>
                            <template x-for="t in templates" :key="t.id">
                                <option :value="t.id" x-text="t.title + (t.is_onboarding ? ' · onboarding' : '')"></option>
                            </template>
                        </select>
                        @if (Route::has('workspace.templates.index'))
                            <a href="{{ route('workspace.templates.index') }}" class="text-sm font-semibold text-indigo-600 hover:underline shrink-0 py-1">Kelola template</a>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1.5" x-show="form.template_id">Judul, deskripsi, prioritas, label, checklist, dan deadline diisi dari template. Semua masih bisa diubah.</p>
                </div>

                {{-- Judul --}}
                <div>
                    <div class="flex items-center justify-between">
                        <label class="ws-label" for="ws-f-title">Judul <span class="text-red-600">*</span></label>
                        <span class="text-xs" :class="form.title.length > 150 ? 'text-red-600' : 'ws-text-faint'" x-text="form.title.length + '/150'"></span>
                    </div>
                    <input id="ws-f-title" type="text" class="ws-input" x-ref="title" data-autofocus x-model="form.title" maxlength="160"
                           :class="errors.title && 'ws-input--error'" placeholder="Mis. Rancang wireframe halaman profil">
                    <p class="ws-field-error" x-show="errors.title" x-text="errors.title"></p>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="ws-label" for="ws-f-desc">Deskripsi</label>
                    <div class="flex gap-1 mb-1.5" role="toolbar" aria-label="Format teks">
                        <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-on:click="format('bold')" title="Tebal" aria-label="Tebal"><i class="fas fa-bold"></i></button>
                        <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-on:click="format('list')" title="Daftar" aria-label="Daftar"><i class="fas fa-list-ul"></i></button>
                        <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-on:click="format('link')" title="Link" aria-label="Link"><i class="fas fa-link"></i></button>
                        <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-on:click="format('code')" title="Kode" aria-label="Kode"><i class="fas fa-code"></i></button>
                        <span class="ml-auto self-center text-[11px] ws-text-faint">Mendukung markdown sederhana</span>
                    </div>
                    <textarea id="ws-f-desc" rows="4" class="ws-input !text-sm" x-ref="description" x-model="form.description"
                              placeholder="Tujuan, langkah, dan kriteria selesai…"></textarea>
                </div>

                {{-- Assignee --}}
                <div x-show="!selfOnly">
                    <p class="ws-label" id="ws-f-assignee">Assignee <span class="font-normal text-slate-500">(opsional · bisa di-assign nanti di board)</span></p>
                    <div class="flex flex-wrap gap-2" role="group" aria-labelledby="ws-f-assignee">
                        <template x-for="i in interns" :key="i.id">
                            <button type="button" class="ws-chip" :aria-pressed="form.assignee_ids.includes(i.id).toString()" x-on:click="toggle('assignee_ids', i.id)">
                                <x-workspace.avatar-js user="i" size="xs" />
                                <span x-text="i.name"></span>
                                <i class="fas fa-check text-[10px]" x-show="form.assignee_ids.includes(i.id)"></i>
                            </button>
                        </template>
                    </div>
                    <p class="ws-field-error" x-show="errors.assignee_ids" x-text="errors.assignee_ids"></p>
                    <label class="ws-tpl-toggle mt-3" x-show="!editing && form.assignee_ids.length > 1">
                        <input type="checkbox" class="mt-1 w-4 h-4 accent-indigo-600" x-model="form.separate_copies">
                        <span>
                            <span class="block text-sm font-semibold text-gray-800" x-text="'Buat salinan terpisah untuk tiap intern (' + form.assignee_ids.length + ' tugas)'"></span>
                            <span class="block text-xs text-slate-600" x-text="form.separate_copies ? 'Setiap intern mengerjakan dan dinilai sendiri-sendiri.' : 'Tidak dicentang: satu tugas dikerjakan bersama oleh semua assignee.'"></span>
                        </span>
                    </label>
                </div>

                {{-- Prioritas & waktu --}}
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="ws-label" for="ws-f-priority">Prioritas</label>
                        <select id="ws-f-priority" class="ws-input" x-model="form.priority">
                            <option value="low">Rendah</option>
                            <option value="medium">Sedang</option>
                            <option value="high">Tinggi</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="ws-label" for="ws-f-start">Mulai</label>
                        <input id="ws-f-start" type="datetime-local" class="ws-input" x-model="form.start_at">
                    </div>
                    <div>
                        <label class="ws-label" for="ws-f-due">Deadline <span class="text-red-600">*</span></label>
                        <input id="ws-f-due" type="datetime-local" class="ws-input" x-model="form.due_at" :class="errors.due_at && 'ws-input--error'">
                        <p class="ws-field-error" x-show="errors.due_at" x-text="errors.due_at"></p>
                    </div>
                </div>

                {{-- Label --}}
                <div x-show="labels.length || canManageLabels">
                    <div class="flex items-center justify-between">
                        <p class="ws-label" id="ws-f-labels">Label</p>
                        <button type="button" class="text-xs font-semibold text-indigo-600 hover:underline py-1" x-show="canManageLabels" x-on:click="manageLabels()">
                            <i class="fas fa-tags mr-1"></i>Kelola label
                        </button>
                    </div>
                    <p class="text-sm text-slate-500" x-show="!labels.length">Belum ada label tim.</p>
                    <div class="flex flex-wrap gap-2" role="group" aria-labelledby="ws-f-labels">
                        <template x-for="l in labels" :key="l.id">
                            <button type="button" class="ws-chip ws-chip--plain !py-1 !text-xs" :aria-pressed="form.label_ids.includes(l.id).toString()" x-on:click="toggle('label_ids', l.id)">
                                <span class="ws-label-chip__dot" :style="{ background: l.color }"></span><span x-text="l.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Checklist --}}
                <div>
                    <p class="ws-label">Checklist</p>
                    <ul class="space-y-2">
                        <template x-for="(item, index) in form.checklist" :key="index">
                            <li class="flex items-center gap-2">
                                <i class="far fa-square text-slate-300"></i>
                                <input type="text" class="ws-input !py-2 !text-sm" x-model="item.content" data-checklist-input :aria-label="'Item checklist ' + (index + 1)"
                                       x-on:keydown.enter.prevent="addChecklist()">
                                <button type="button" class="ws-icon-btn ws-icon-btn--sm shrink-0" x-on:click="form.checklist.splice(index, 1)" aria-label="Hapus item"><i class="fas fa-xmark"></i></button>
                            </li>
                        </template>
                    </ul>
                    <button type="button" class="text-sm font-semibold text-indigo-600 mt-2" x-on:click="addChecklist()"><i class="fas fa-plus mr-1 text-xs"></i>Tambah item</button>
                </div>

                {{-- Link --}}
                <div>
                    <p class="ws-label">Link referensi / hasil</p>
                    <ul class="space-y-2">
                        <template x-for="(link, index) in form.result_links" :key="index">
                            <li>
                                <div class="flex items-center gap-2">
                                    <input type="url" class="ws-input !py-2 !text-sm flex-[2]" x-model="link.url" placeholder="https://github.com/…"
                                           :class="errors['result_links.' + index + '.url'] && 'ws-input--error'" :aria-label="'URL link ' + (index + 1)">
                                    <input type="text" class="ws-input !py-2 !text-sm flex-1" x-model="link.label" placeholder="Label (opsional)" :aria-label="'Label link ' + (index + 1)">
                                    <button type="button" class="ws-icon-btn ws-icon-btn--sm shrink-0" x-on:click="form.result_links.splice(index, 1)" aria-label="Hapus link"><i class="fas fa-xmark"></i></button>
                                </div>
                                <p class="ws-field-error" x-show="errors['result_links.' + index + '.url']" x-text="errors['result_links.' + index + '.url']"></p>
                            </li>
                        </template>
                    </ul>
                    <button type="button" class="text-sm font-semibold text-indigo-600 mt-2" x-on:click="form.result_links.push({ url: '', label: '' })"><i class="fas fa-plus mr-1 text-xs"></i>Tambah link</button>
                </div>

                {{-- Lampiran --}}
                <div>
                    <p class="ws-label">Lampiran</p>
                    @include('workspace.partials.uploader', ['model' => 'files'])
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="open = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--primary" x-on:click="submit()" :disabled="saving">
                <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                <span x-text="editing ? 'Simpan perubahan' : 'Buat tugas'"></span>
            </button>
        </x-slot:footer>
    </x-workspace.modal>
</div>

{{-- ================= Review (mentor/admin) ================= --}}
<div x-data="wsReviewModal">
    <x-workspace.modal name="ws-review" labelledby="ws-review-heading" x-model="open">
        <template x-if="task">
            <div class="-mt-1">
                <p class="ws-mono text-xs text-slate-500" x-text="task.code"></p>
                <h2 id="ws-review-heading" class="text-lg font-bold text-gray-800 mb-5" x-text="'Review: ' + task.title"></h2>

                <div class="grid grid-cols-2 gap-2 mb-4" role="radiogroup" aria-label="Keputusan review">
                    <label class="ws-tile cursor-pointer flex items-center gap-2" :class="decision === 'approve' && '!border-emerald-300 !bg-emerald-50'">
                        <input type="radio" value="approve" x-model="decision" class="accent-emerald-600" x-ref="first">
                        <span class="text-sm font-semibold">Selesai</span>
                    </label>
                    <label class="ws-tile cursor-pointer flex items-center gap-2" :class="decision === 'revision' && '!border-orange-300 !bg-orange-50'">
                        <input type="radio" value="revision" x-model="decision" class="accent-orange-600">
                        <span class="text-sm font-semibold">Minta revisi</span>
                    </label>
                </div>

                <div class="mb-4" x-show="decision === 'approve'">
                    <label class="ws-label" for="ws-r-score">Nilai (0–100, opsional)</label>
                    <input id="ws-r-score" type="number" min="0" max="100" class="ws-input" x-model="score" :class="errors.score && 'ws-input--error'" placeholder="Mis. 85">
                    <p class="ws-field-error" x-show="errors.score" x-text="errors.score"></p>
                </div>

                <div>
                    <label class="ws-label" for="ws-r-feedback">
                        <span x-text="decision === 'revision' ? 'Catatan revisi' : 'Feedback'"></span>
                        <span class="text-red-600" x-show="decision === 'revision'">*</span>
                    </label>
                    <textarea id="ws-r-feedback" rows="4" class="ws-input !text-sm" x-model="feedback" :class="errors.feedback && 'ws-input--error'"
                              :placeholder="decision === 'revision' ? 'Apa yang perlu diperbaiki?' : 'Apresiasi atau masukan untuk intern'"></textarea>
                    <p class="ws-field-error" x-show="errors.feedback" x-text="errors.feedback"></p>
                </div>
            </div>
        </template>

        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="open = false">Batal</button>
            <button type="button" class="ws-btn" :class="decision === 'revision' ? 'ws-btn--danger' : 'ws-btn--primary'" x-on:click="submit()" :disabled="saving">
                <i class="fas" :class="saving ? 'fa-spinner fa-spin' : (decision === 'revision' ? 'fa-rotate-left' : 'fa-circle-check')"></i>
                <span x-text="decision === 'revision' ? 'Kembalikan untuk revisi' : 'Tandai selesai'"></span>
            </button>
        </x-slot:footer>
    </x-workspace.modal>
</div>

{{-- ================= Kirim hasil (intern → Review) ================= --}}
<div x-data="wsSubmitModal">
    <x-workspace.modal name="ws-submit" labelledby="ws-submit-heading" x-model="open">
        <template x-if="task">
            <div class="-mt-1">
                <p class="ws-mono text-xs text-slate-500" x-text="task.code"></p>
                <h2 id="ws-submit-heading" class="text-lg font-bold text-gray-800 mb-1" x-text="'Kirim hasil: ' + task.title"></h2>
                <p class="text-sm text-slate-500 mb-5">Mentor akan mendapat notifikasi untuk mereview. Lampirkan link atau berkas hasil kerja Anda (opsional).</p>

                <div class="space-y-4">
                    <div>
                        <p class="ws-label">Link hasil</p>
                        <ul class="space-y-2">
                            <template x-for="(link, index) in links" :key="index">
                                <li>
                                    <div class="flex items-center gap-2">
                                        <input type="url" class="ws-input !py-2 !text-sm flex-[2]" x-model="link.url" placeholder="https://github.com/… / figma.com/…"
                                               :class="errors['link' + index] && 'ws-input--error'" :aria-label="'URL hasil ' + (index + 1)">
                                        <input type="text" class="ws-input !py-2 !text-sm flex-1" x-model="link.label" placeholder="Label" :aria-label="'Label hasil ' + (index + 1)">
                                    </div>
                                    <p class="ws-field-error" x-show="errors['link' + index]" x-text="errors['link' + index]"></p>
                                </li>
                            </template>
                        </ul>
                        <button type="button" class="text-sm font-semibold text-indigo-600 mt-2" x-on:click="links.push({ url: '', label: '' })"><i class="fas fa-plus mr-1 text-xs"></i>Tambah link</button>
                    </div>
                    <div>
                        <p class="ws-label">Berkas</p>
                        @include('workspace.partials.uploader', ['model' => 'files'])
                    </div>
                    <div>
                        <label class="ws-label" for="ws-s-note">Catatan untuk mentor</label>
                        <textarea id="ws-s-note" rows="3" class="ws-input !text-sm" x-model="note" placeholder="Opsional — akan ditambahkan sebagai komentar"></textarea>
                    </div>
                </div>
            </div>
        </template>

        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="open = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--primary" x-on:click="submit()" :disabled="saving || uploading">
                <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                Kirim untuk review
            </button>
        </x-slot:footer>
    </x-workspace.modal>
</div>

{{-- ================= Hapus ================= --}}
<div x-data="wsDeleteModal">
    <x-workspace.modal name="ws-delete" max-width="sm" labelledby="ws-delete-heading" x-model="open">
        <template x-if="task">
            <div class="text-center -mt-1">
                <span class="inline-flex w-12 h-12 items-center justify-center rounded-full bg-red-50 text-red-500 mb-3"><i class="fas fa-trash text-lg"></i></span>
                <h2 id="ws-delete-heading" class="text-lg font-bold text-gray-800" x-text="'Hapus ' + task.code + '?'"></h2>
                <p class="text-sm text-slate-500 mt-1"><span class="font-semibold" x-text="task.title"></span> akan dihapus dari board. Riwayatnya tetap tersimpan untuk audit.</p>
            </div>
        </template>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="open = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--danger" x-on:click="confirm()" :disabled="saving">
                <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-trash'"></i> Hapus tugas
            </button>
        </x-slot:footer>
    </x-workspace.modal>
</div>

{{-- ================= Perpanjang deadline (mentor/admin) ================= --}}
<div x-data="wsDeadline">
    <x-workspace.modal name="ws-deadline" max-width="md" labelledby="ws-deadline-heading" x-model="open">
        <template x-if="task">
            <div class="-mt-1">
                <h2 id="ws-deadline-heading" class="text-lg font-bold text-gray-800 mb-1" x-text="'Perpanjang deadline ' + task.code"></h2>
                <p class="text-sm text-slate-500 mb-4">
                    Deadline sekarang: <strong class="text-slate-700" x-text="current"></strong>.
                    Anak magang mendapat notifikasi (email), dan kunci kirim hasil terbuka lagi.
                </p>
                <fieldset class="mb-4">
                    <legend class="ws-label">Tambah waktu</legend>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="o in options" :key="o.days">
                            <button type="button" class="ws-chip" :aria-pressed="(days === o.days).toString()" x-on:click="choose(o.days)" x-text="o.label"></button>
                        </template>
                    </div>
                </fieldset>
                <div class="mb-4">
                    <label class="ws-label" for="ws-dl-due">Deadline baru <span class="text-red-600">*</span></label>
                    <input id="ws-dl-due" type="datetime-local" class="ws-input" x-model="dueAt" x-on:input="days = null" :class="errors.due_at && 'ws-input--error'">
                    <p class="ws-field-error" x-show="errors.due_at" x-text="errors.due_at"></p>
                </div>
                <div>
                    <label class="ws-label" for="ws-dl-reason">Alasan <span class="font-normal text-slate-500">(opsional, tampil di riwayat)</span></label>
                    <input id="ws-dl-reason" type="text" class="ws-input" x-model="reason" maxlength="200" placeholder="Contoh: menunggu akses API dari tim lain">
                    <p class="ws-field-error" x-show="errors.reason" x-text="errors.reason"></p>
                </div>
            </div>
        </template>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="open = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--primary" x-on:click="submit()" :disabled="saving">
                <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-calendar-plus'"></i> Perpanjang
            </button>
        </x-slot:footer>
    </x-workspace.modal>
</div>
