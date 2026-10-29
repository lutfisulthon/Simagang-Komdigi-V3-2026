{{--
    Tab Otomasi (mentor/admin): aturan pekerjaan berulang. Komponen wsAutomations (public/js/workspace/automations.js).
    Dua tampilan: daftar aturan, dan penyusun aturan berbentuk rantai When → If → Then (gaya Jira).
    Variabel: $workspace
--}}
<div x-data="wsAutomations(@js(['workspaceId' => $workspace['id']]))" class="space-y-4">

    {{-- ================= Daftar aturan ================= --}}
    <div x-show="view === 'list'" class="space-y-4">
        <div class="ws-panel">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-gray-800">Otomasi</h2>
                    <p class="text-sm text-slate-500 mt-1 max-w-2xl">Atur pekerjaan yang berulang supaya berjalan sendiri sesuai jadwal: membuat tugas rutin atau mengirim pengingat ke anggota kelompok.</p>
                </div>
                <button type="button" class="ws-btn ws-btn--primary shrink-0" x-on:click="startNew()"><i class="fas fa-plus" aria-hidden="true"></i> Buat aturan</button>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider ws-text-faint">Mulai dari contoh</span>
                <template x-for="p in presetList" :key="p.key">
                    <button type="button" class="ws-chip ws-chip--plain !py-1 !text-xs" :title="p.hint" x-on:click="startNew(p.key)">
                        <i class="fas" :class="p.icon" aria-hidden="true"></i><span x-text="p.label"></span>
                    </button>
                </template>
            </div>
        </div>

        <div class="space-y-3" x-show="loading" aria-hidden="true">
            <div class="h-28 rounded-2xl bg-white animate-pulse"></div>
            <div class="h-28 rounded-2xl bg-white animate-pulse"></div>
        </div>

        <div class="ws-callout" role="alert" x-show="!loading && error" x-cloak>
            <span class="ws-callout__icon" aria-hidden="true"><i class="fas fa-triangle-exclamation"></i></span>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-sm text-gray-800">Aturan gagal dimuat</p>
                <p class="text-sm text-slate-600" x-text="error"></p>
            </div>
            <button type="button" class="ws-btn ws-btn--soft ws-btn--sm shrink-0" x-on:click="load()">Coba lagi</button>
        </div>

        <div class="ws-panel" x-show="!loading && !error && !rules.length" x-cloak>
            <x-workspace.empty-state icon="fa-bolt" title="Belum ada aturan otomasi"
                                     description="Buat aturan pertama, misalnya tugas laporan yang muncul tiap Jumat, atau pengingat logbook tiap sore." />
        </div>

        <ul class="space-y-3" x-show="!loading && !error && rules.length" x-cloak>
            <template x-for="rule in rules" :key="rule.id">
                <li class="ws-panel ws-au-rule" :class="!rule.enabled && 'ws-au-rule--off'">
                    <div class="ws-au-rule__main">
                        <button type="button" role="switch" class="ws-switch shrink-0 mt-0.5" :aria-checked="rule.enabled.toString()"
                                :aria-label="'Aturan ' + rule.name + ' aktif'" x-on:click="toggle(rule)"><span></span></button>
                        <div class="min-w-0 flex-1">
                            <button type="button" class="ws-au-rule__name" x-on:click="edit(rule)" x-text="rule.name"></button>
                            <p class="ws-au-rule__desc" x-text="describe(rule)"></p>
                            <p class="ws-au-rule__meta">
                                <span x-show="rule.enabled && ruleNext(rule)"><i class="far fa-clock" aria-hidden="true"></i> Berikutnya <strong x-text="ruleNext(rule)"></strong></span>
                                <span x-show="!rule.enabled"><i class="fas fa-pause" aria-hidden="true"></i> Nonaktif</span>
                                <span x-show="rule.last_run_at && rule.runs && rule.runs.length"><i class="fas fa-rotate" aria-hidden="true"></i> Terakhir jalan <span x-text="ago(rule.last_run_at)"></span>: <span x-text="rule.runs[0] ? rule.runs[0].summary : ''"></span></span>
                            </p>
                        </div>
                    </div>
                    <div class="ws-au-rule__actions">
                        <button type="button" class="ws-btn ws-btn--ghost ws-btn--sm" x-on:click="run(rule)" :disabled="!!running[rule.id]">
                            <i class="fas" :class="running[rule.id] ? 'fa-spinner fa-spin' : 'fa-play'" aria-hidden="true"></i> Jalankan<span class="hidden sm:inline">&nbsp;sekarang</span>
                        </button>
                        <button type="button" class="ws-btn ws-btn--soft ws-btn--sm" x-on:click="edit(rule)"><i class="fas fa-pen" aria-hidden="true"></i> Ubah</button>
                        <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-on:click="duplicate(rule)" :aria-label="'Duplikat ' + rule.name" title="Duplikat"><i class="far fa-copy" aria-hidden="true"></i></button>
                        <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-show="confirming !== rule.id" x-on:click="askRemove(rule)" :aria-label="'Hapus ' + rule.name" title="Hapus"><i class="far fa-trash-can" aria-hidden="true"></i></button>
                        <button type="button" class="ws-btn ws-btn--sm ws-btn--danger" x-show="confirming === rule.id" x-cloak x-on:click="remove(rule)">Yakin hapus?</button>
                    </div>
                </li>
            </template>
        </ul>
    </div>

    {{-- ================= Penyusun aturan ================= --}}
    <template x-if="view === 'edit' && draft">
        <div class="space-y-4">
            <div class="ws-panel !p-4 flex flex-wrap items-start gap-x-4 gap-y-3">
                <button type="button" class="ws-btn ws-btn--ghost ws-btn--sm mt-1" x-on:click="back()"><i class="fas fa-arrow-left" aria-hidden="true"></i> Semua aturan</button>
                <div class="flex-1 min-w-[220px]">
                    <label class="sr-only" for="ws-au-name">Nama aturan</label>
                    <input id="ws-au-name" type="text" class="ws-au-name" x-model="draft.name" maxlength="60" placeholder="Nama aturan, mis. Laporan mingguan"
                           :class="err('name') && 'ws-input--error'" :aria-invalid="(!!err('name')).toString()">
                    <p class="ws-field-error" x-show="err('name')" x-text="err('name')"></p>
                </div>
                <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 mt-2">
                    <button type="button" role="switch" class="ws-switch" :aria-checked="draft.enabled.toString()" aria-label="Aturan aktif" x-on:click="draft.enabled = !draft.enabled"><span></span></button>
                    <span x-text="draft.enabled ? 'Aktif' : 'Nonaktif'"></span>
                </label>
                <button type="button" class="ws-btn ws-btn--primary mt-0.5" x-on:click="save()" :disabled="saving">
                    <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-check'" aria-hidden="true"></i> Simpan
                </button>
            </div>

            <div class="grid gap-5 lg:grid-cols-[minmax(0,560px)_minmax(0,1fr)] items-start">
                {{-- Rantai When → If → Then --}}
                <div class="ws-au-chain" role="group" aria-label="Alur aturan">

                    {{-- WHEN --}}
                    <div class="ws-au-card" :class="[selected === 'trigger' && 'ws-au-card--on', hasError('trigger') && 'ws-au-card--err']">
                        <button type="button" class="ws-au-card__head" x-on:click="select('trigger')" :aria-expanded="(selected === 'trigger').toString()" aria-controls="ws-au-trigger">
                            <span class="ws-au-icon ws-au-icon--when"><i class="fas fa-clock" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 text-left">
                                <span class="ws-au-card__title">When: Jadwal berulang</span>
                                <span class="ws-au-card__sub" x-text="triggerText(draft.trigger)"></span>
                            </span>
                            <i class="fas fa-chevron-down ws-au-chev" :class="selected === 'trigger' && 'is-open'" aria-hidden="true"></i>
                        </button>
                        <div id="ws-au-trigger" class="ws-au-card__body" x-show="selected === 'trigger'" x-cloak>
                            <fieldset class="ws-au-field">
                                <legend class="ws-label">Seberapa sering</legend>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="f in [['daily', 'Harian'], ['weekly', 'Mingguan'], ['monthly', 'Bulanan']]" :key="f[0]">
                                        <button type="button" class="ws-chip ws-chip--plain" :aria-pressed="(draft.trigger.frequency === f[0]).toString()" x-on:click="draft.trigger.frequency = f[0]" x-text="f[1]"></button>
                                    </template>
                                </div>
                            </fieldset>
                            <fieldset class="ws-au-field" x-show="draft.trigger.frequency === 'weekly'">
                                <legend class="ws-label">Pada hari</legend>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="(d, i) in days" :key="d">
                                        <button type="button" class="ws-chip ws-chip--plain" :aria-pressed="draft.trigger.days.includes(i + 1).toString()" x-on:click="toggleDay(i + 1)" x-text="d.slice(0, 3)"></button>
                                    </template>
                                </div>
                                <p class="ws-field-error" x-show="err('trigger.days')" x-text="err('trigger.days')"></p>
                            </fieldset>
                            <div class="ws-au-field" x-show="draft.trigger.frequency === 'monthly'">
                                <label class="ws-label" for="ws-au-monthday">Pada tanggal</label>
                                <select id="ws-au-monthday" class="ws-input !w-auto" x-model="draft.trigger.month_day">
                                    <template x-for="n in 31" :key="n"><option :value="String(n)" x-text="n"></option></template>
                                    <option value="last">Akhir bulan</option>
                                </select>
                                <p class="ws-field-error" x-show="err('trigger.month_day')" x-text="err('trigger.month_day')"></p>
                            </div>
                            <div class="ws-au-field">
                                <label class="ws-label" for="ws-au-time">Pukul</label>
                                <input id="ws-au-time" type="time" class="ws-input !w-auto" x-model="draft.trigger.time" :class="err('trigger.time') && 'ws-input--error'">
                                <p class="ws-field-error" x-show="err('trigger.time')" x-text="err('trigger.time')"></p>
                            </div>
                        </div>
                    </div>

                    {{-- IF --}}
                    <template x-for="c in draft.conditions" :key="c.type">
                        <div>
                            <div class="ws-au-link" aria-hidden="true"></div>
                            <div class="ws-au-card" :class="selected === 'c:' + c.type && 'ws-au-card--on'">
                                <div class="ws-au-card__row">
                                    <button type="button" class="ws-au-card__head" x-on:click="select('c:' + c.type)" :aria-expanded="(selected === 'c:' + c.type).toString()">
                                        <span class="ws-au-icon ws-au-icon--if"><i class="fas fa-shuffle" aria-hidden="true"></i></span>
                                        <span class="min-w-0 flex-1 text-left">
                                            <span class="ws-au-card__title" x-text="'If: ' + conditionDefs[c.type].label"></span>
                                            <span class="ws-au-card__sub" x-text="selected === 'c:' + c.type ? '' : 'Kondisi tambahan'"></span>
                                        </span>
                                    </button>
                                    <button type="button" class="ws-icon-btn ws-icon-btn--sm shrink-0 mr-2" x-on:click="removeCondition(c.type)" :aria-label="'Hapus kondisi ' + conditionDefs[c.type].label" title="Hapus kondisi"><i class="fas fa-xmark" aria-hidden="true"></i></button>
                                </div>
                                <div class="ws-au-card__body" x-show="selected === 'c:' + c.type" x-cloak>
                                    <p class="text-sm text-slate-600" x-text="conditionDefs[c.type].hint"></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- THEN --}}
                    <template x-for="(a, i) in draft.actions" :key="'a' + i">
                        <div>
                            <div class="ws-au-link" aria-hidden="true"></div>
                            <div class="ws-au-card" :class="[selected === 'a:' + i && 'ws-au-card--on', hasError('actions.' + i) && 'ws-au-card--err']">
                                <div class="ws-au-card__row">
                                    <button type="button" class="ws-au-card__head" x-on:click="select('a:' + i)" :aria-expanded="(selected === 'a:' + i).toString()" :aria-controls="'ws-au-action-' + i">
                                        <span class="ws-au-icon" :class="a.type === 'create_task' ? 'ws-au-icon--then' : 'ws-au-icon--notify'"><i class="fas" :class="a.type === 'create_task' ? 'fa-plus' : 'fa-bell'" aria-hidden="true"></i></span>
                                        <span class="min-w-0 flex-1 text-left">
                                            <span class="ws-au-card__title" x-text="'Then: ' + (a.type === 'create_task' ? 'Buat tugas' : 'Kirim notifikasi')"></span>
                                            <span class="ws-au-card__sub" x-text="actionText(a)"></span>
                                        </span>
                                    </button>
                                    <span class="ws-au-card__tools">
                                        <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-show="i > 0" x-on:click="moveAction(i, -1)" aria-label="Pindah aksi ke atas" title="Naik"><i class="fas fa-arrow-up text-[11px]" aria-hidden="true"></i></button>
                                        <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-show="i < draft.actions.length - 1" x-on:click="moveAction(i, 1)" aria-label="Pindah aksi ke bawah" title="Turun"><i class="fas fa-arrow-down text-[11px]" aria-hidden="true"></i></button>
                                        <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-on:click="removeAction(i)" aria-label="Hapus aksi" title="Hapus aksi"><i class="fas fa-xmark" aria-hidden="true"></i></button>
                                    </span>
                                </div>

                                {{-- Buat tugas --}}
                                <template x-if="a.type === 'create_task'">
                                <div class="ws-au-card__body" :id="'ws-au-action-' + i" x-show="selected === 'a:' + i" x-cloak>
                                    <div class="ws-au-field">
                                        <label class="ws-label" :for="'ws-au-title-' + i">Judul tugas <span class="text-red-600">*</span></label>
                                        <input :id="'ws-au-title-' + i" type="text" class="ws-input" x-model="a.title" maxlength="150" placeholder="Contoh: Laporan kemajuan mingguan" :class="err('actions.' + i + '.title') && 'ws-input--error'">
                                        <p class="ws-field-error" x-show="err('actions.' + i + '.title')" x-text="err('actions.' + i + '.title')"></p>
                                    </div>
                                    <fieldset class="ws-au-field">
                                        <legend class="ws-label">Prioritas</legend>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="p in [['low', 'Rendah'], ['medium', 'Sedang'], ['high', 'Tinggi'], ['urgent', 'Urgent']]" :key="p[0]">
                                                <button type="button" class="ws-chip ws-chip--plain" :aria-pressed="(a.priority === p[0]).toString()" x-on:click="a.priority = p[0]" x-text="p[1]"></button>
                                            </template>
                                        </div>
                                    </fieldset>
                                    <fieldset class="ws-au-field">
                                        <legend class="ws-label">Dikerjakan oleh</legend>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="m in [['all', 'Semua anggota'], ['leader', 'Ketua sprint'], ['selected', 'Pilih anggota']]" :key="m[0]">
                                                <button type="button" class="ws-chip ws-chip--plain" :aria-pressed="(a.assignees === m[0]).toString()" x-on:click="a.assignees = m[0]" x-text="m[1]"></button>
                                            </template>
                                        </div>
                                        <div class="flex flex-wrap gap-2 mt-2" x-show="a.assignees === 'selected'">
                                            <template x-for="m in interns" :key="m.id">
                                                <button type="button" class="ws-chip" :aria-pressed="a.assignee_ids.includes(m.id).toString()" x-on:click="toggleAssignee(a, m.id)">
                                                    <x-workspace.avatar-js user="m" size="xs" /><span x-text="m.name"></span>
                                                </button>
                                            </template>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-2" x-show="a.assignees === 'leader'" x-text="activeSprint && activeSprint.leader ? 'Ketua sprint saat ini: ' + activeSprint.leader.name + '.' : 'Saat ini belum ada sprint berjalan dengan ketua; tugas dibuat tanpa penanggung jawab.'"></p>
                                        <p class="ws-field-error" x-show="err('actions.' + i + '.assignee_ids')" x-text="err('actions.' + i + '.assignee_ids')"></p>
                                        <label class="inline-flex items-center gap-2 text-sm text-slate-600 mt-3" x-show="a.assignees !== 'leader'">
                                            <input type="checkbox" class="h-4 w-4 accent-indigo-600" x-model="a.per_member"> Buat satu tugas untuk tiap anggota
                                        </label>
                                    </fieldset>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="ws-au-field !mb-0">
                                            <label class="ws-label" :for="'ws-au-due-' + i">Deadline</label>
                                            <div class="flex items-center gap-2">
                                                <input :id="'ws-au-due-' + i" type="number" min="0" max="60" class="ws-input !w-20" x-model.number="a.due_in_days" :class="err('actions.' + i + '.due_in_days') && 'ws-input--error'">
                                                <span class="text-sm text-slate-600">hari setelah dibuat (pukul 17.00)</span>
                                            </div>
                                            <p class="ws-field-error" x-show="err('actions.' + i + '.due_in_days')" x-text="err('actions.' + i + '.due_in_days')"></p>
                                        </div>
                                        <fieldset class="ws-au-field !mb-0">
                                            <legend class="ws-label">Masuk ke</legend>
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="d in [['active_sprint', 'Sprint berjalan'], ['backlog', 'Backlog']]" :key="d[0]">
                                                    <button type="button" class="ws-chip ws-chip--plain" :aria-pressed="(a.destination === d[0]).toString()" x-on:click="a.destination = d[0]" x-text="d[1]"></button>
                                                </template>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                                </template>

                                {{-- Kirim notifikasi --}}
                                <template x-if="a.type === 'notify'">
                                <div class="ws-au-card__body" :id="'ws-au-action-' + i" x-show="selected === 'a:' + i" x-cloak>
                                    <div class="ws-au-field">
                                        <label class="ws-label" :for="'ws-au-to-' + i">Kirim ke</label>
                                        <select :id="'ws-au-to-' + i" class="ws-input !w-auto" x-model="a.to">
                                            <option value="assignees">Penerima tugas yang dibuat</option>
                                            <option value="all">Semua anggota</option>
                                            <option value="leader">Ketua sprint</option>
                                            <option value="mentor">Mentor</option>
                                        </select>
                                    </div>
                                    <div class="ws-au-field !mb-0">
                                        <label class="ws-label" :for="'ws-au-msg-' + i">Isi pesan <span class="text-red-600">*</span></label>
                                        <textarea :id="'ws-au-msg-' + i" rows="3" maxlength="200" class="ws-input" x-model="a.message" placeholder="Contoh: Jangan lupa mengisi logbook hari ini." :class="err('actions.' + i + '.message') && 'ws-input--error'"></textarea>
                                        <div class="flex justify-between gap-2">
                                            <p class="ws-field-error" x-show="err('actions.' + i + '.message')" x-text="err('actions.' + i + '.message')"></p>
                                            <span class="text-xs ws-text-faint ml-auto" x-text="a.message.length + '/200'"></span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">Dikirim sebagai notifikasi di aplikasi dan email.</p>
                                    </div>
                                </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <p class="ws-field-error mt-2" x-show="err('actions')" x-text="err('actions')"></p>

                    {{-- Add component --}}
                    <div class="ws-au-link" aria-hidden="true"></div>
                    <div class="relative inline-block" x-on:click.outside="addMenu = false" x-on:keydown.escape="addMenu = false">
                        <button type="button" class="ws-au-add" x-on:click="addMenu = !addMenu" :aria-expanded="addMenu.toString()" aria-haspopup="true">
                            <i class="fas fa-plus" aria-hidden="true"></i> Add component
                        </button>
                        <div class="ws-menu !left-0 !right-auto !min-w-[290px]" x-show="addMenu" x-cloak x-transition.opacity>
                            <p class="ws-menu__label">Kondisi (If)</p>
                            <template x-for="def in conditionList" :key="def.type">
                                <button type="button" class="ws-menu__item" :disabled="hasCondition(def.type)" :class="hasCondition(def.type) && 'opacity-50 cursor-not-allowed'" x-on:click="addCondition(def.type)">
                                    <i class="fas fa-shuffle text-xs text-amber-600" aria-hidden="true"></i><span x-text="def.label"></span>
                                    <i class="fas fa-check text-xs text-emerald-600 ml-auto" x-show="hasCondition(def.type)" aria-label="Sudah ditambahkan"></i>
                                </button>
                            </template>
                            <p class="ws-menu__label border-t border-slate-100 mt-1 pt-2">Aksi (Then)</p>
                            <button type="button" class="ws-menu__item" x-on:click="addAction('create_task')"><i class="fas fa-plus text-xs text-blue-600" aria-hidden="true"></i> Buat tugas</button>
                            <button type="button" class="ws-menu__item" x-on:click="addAction('notify')"><i class="fas fa-bell text-xs text-violet-600" aria-hidden="true"></i> Kirim notifikasi</button>
                        </div>
                    </div>
                </div>

                {{-- Ringkasan --}}
                <aside class="space-y-4 lg:sticky lg:top-4" aria-label="Ringkasan aturan">
                    <section class="ws-panel">
                        <h2 class="text-sm font-bold text-gray-800 mb-2"><i class="fas fa-circle-info text-indigo-500 mr-1.5" aria-hidden="true"></i>Ringkasan</h2>
                        <p class="text-sm leading-relaxed text-slate-700" x-text="summary"></p>
                    </section>
                    <section class="ws-panel">
                        <h2 class="text-sm font-bold text-gray-800 mb-2"><i class="far fa-calendar text-indigo-500 mr-1.5" aria-hidden="true"></i>Jadwal berikutnya</h2>
                        <ul class="space-y-1.5 text-sm text-slate-700">
                            <template x-for="(w, i) in upcoming" :key="i"><li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-indigo-400" aria-hidden="true"></span><span x-text="w"></span></li></template>
                        </ul>
                        <p class="text-sm text-slate-500" x-show="!upcoming.length">Belum ada jadwal. Lengkapi hari dan jam pada kartu When.</p>
                        <p class="text-xs text-slate-500 mt-2" x-show="!draft.enabled">Aturan nonaktif tidak akan berjalan sampai diaktifkan.</p>
                    </section>
                </aside>
            </div>
        </div>
    </template>
</div>
