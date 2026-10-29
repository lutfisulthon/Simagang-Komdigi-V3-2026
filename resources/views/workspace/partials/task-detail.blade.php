{{--
    Isi detail tugas — dipakai slide-over (board/list) dan halaman penuh /workspace/tasks/{id}.
    Harus berada di dalam x-data="wsTaskDetail(...)". Variabel Blade: $mode ('panel' | 'page').
--}}

{{-- Memuat --}}
<template x-if="loading">
    <div class="space-y-4 animate-pulse" aria-busy="true">
        <div class="h-4 w-24 bg-slate-100 rounded"></div>
        <div class="h-6 w-3/4 bg-slate-100 rounded"></div>
        <div class="h-24 bg-slate-100 rounded-xl"></div>
        <div class="h-40 bg-slate-100 rounded-xl"></div>
    </div>
</template>

{{-- Gagal --}}
<template x-if="error && !loading">
    <div class="text-center py-10">
        <i class="fas fa-circle-exclamation text-2xl text-red-400 mb-2"></i>
        <p class="text-sm text-slate-600" x-text="error"></p>
        <button type="button" class="ws-btn ws-btn--soft ws-btn--sm mt-4" x-on:click="load($store.wsTasks.detailId)">Coba lagi</button>
    </div>
</template>

<template x-if="task && !loading">
    <div>
        {{-- Judul & aksi --}}
        <div class="flex items-start gap-3 mb-4">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="ws-mono text-xs font-medium text-slate-500" x-text="task.code"></span>
                    <span class="ws-badge" :class="'ws-status--' + task.status"><span class="ws-badge__dot"></span><span x-text="WS.STATUS[task.status]"></span></span>
                    <span class="ws-badge" :class="'ws-priority--' + task.priority"><i class="fas text-[10px]" :class="WS.tasks.priorityIcon(task.priority)"></i><span x-text="WS.PRIORITY[task.priority]"></span></span>
                    <template x-if="task.is_late && task.status !== 'done'">
                        <span class="ws-badge ws-badge--late"><i class="fas fa-triangle-exclamation text-[10px]"></i>Terlambat</span>
                    </template>
                </div>
                <h2 class="{{ $mode === 'page' ? 'text-2xl' : 'text-xl' }} font-bold text-gray-800 leading-snug" x-text="task.title" id="ws-task-title"></h2>
            </div>

            <div class="relative shrink-0" x-data="{ menu: false }" x-on:click.outside="menu = false" x-on:keydown.escape="if (menu) { menu = false; $event.preventDefault(); $el.querySelector('button').focus(); }">
                <button type="button" class="ws-icon-btn" x-on:click="menu = !menu" :aria-expanded="menu.toString()" aria-label="Aksi lain">
                    <i class="fas fa-ellipsis-vertical"></i>
                </button>
                <div class="ws-menu" x-show="menu" x-cloak x-transition.opacity>
                    @if ($mode === 'panel')
                        <a class="ws-menu__item" :href="task.url"><i class="fas fa-up-right-from-square w-4"></i>Buka halaman penuh</a>
                    @endif
                    <button type="button" class="ws-menu__item" x-on:click="menu = false; copyLink()"><i class="fas fa-link w-4"></i>Salin link</button>
                    <template x-if="task.can.update">
                        <button type="button" class="ws-menu__item" x-on:click="menu = false; edit()"><i class="fas fa-pen w-4"></i>Edit tugas</button>
                    </template>
                    @if ($canTemplate ?? false)
                        {{-- Form template ada di partials/template-modals (disertakan halaman bila $can['create_template']). --}}
                        <button type="button" class="ws-menu__item" x-on:click="menu = false; $dispatch('ws-open-template-form', { fromTask: task })"><i class="far fa-copy w-4"></i>Simpan sebagai template</button>
                    @endif
                    <template x-if="task.can.delete">
                        <button type="button" class="ws-menu__item ws-menu__item--danger" x-on:click="menu = false; remove()"><i class="fas fa-trash w-4"></i>Hapus tugas</button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Aksi status --}}
        <div class="ws-tile !bg-white flex flex-wrap items-center gap-2 mb-5" x-show="primaryActions.length || statusOptions.length || waitingNote || task.can.extend_deadline">
            <template x-for="action in primaryActions" :key="action.target">
                <button type="button" class="ws-btn ws-btn--sm" :class="action.style === 'primary' ? 'ws-btn--primary' : 'ws-btn--soft'"
                        :disabled="moving" x-on:click="moveTo(action.target)">
                    <i class="fas" :class="action.icon"></i><span x-text="action.label"></span>
                </button>
            </template>
            <template x-if="task.can.extend_deadline">
                <button type="button" class="ws-btn ws-btn--sm ws-btn--ghost" x-on:click="$dispatch('ws-open-deadline', { task })">
                    <i class="fas fa-calendar-plus text-amber-600"></i><span>Perpanjang deadline</span>
                </button>
            </template>
            <template x-if="isStaff && statusOptions.length">
                <label class="flex items-center gap-2 text-sm text-slate-500" :class="primaryActions.length ? 'ml-auto' : ''">
                    <span>Ubah status</span>
                    <select class="ws-input !py-1.5 !text-sm !w-auto" x-on:change="moveTo($event.target.value); $event.target.value = ''">
                        <option value="">Pilih…</option>
                        <template x-for="s in statusOptions" :key="s"><option :value="s" x-text="WS.STATUS[s]"></option></template>
                    </select>
                </label>
            </template>
            <p class="text-sm text-slate-500 flex items-center gap-2" x-show="waitingNote">
                <i class="fas fa-circle-info text-indigo-400"></i><span x-text="waitingNote"></span>
            </p>
        </div>

        {{-- Hasil review --}}
        <template x-if="task.feedback || task.score !== null">
            <div class="rounded-2xl border px-4 py-3 mb-5"
                 :class="task.status === 'revision' ? 'bg-orange-50 border-orange-200' : 'bg-emerald-50 border-emerald-200'">
                <div class="flex items-center justify-between gap-3 mb-1">
                    <p class="text-sm font-bold" :class="task.status === 'revision' ? 'text-orange-800' : 'text-emerald-800'"
                       x-text="task.status === 'revision' ? 'Catatan revisi dari mentor' : 'Feedback mentor'"></p>
                    <template x-if="task.score !== null">
                        <span class="ws-mono text-lg font-medium text-emerald-700"><span x-text="task.score"></span><span class="text-xs text-emerald-500">/100</span></span>
                    </template>
                </div>
                <div class="ws-prose !text-[13px]" x-show="task.feedback_html" x-html="task.feedback_html"></div>
            </div>
        </template>

        {{-- Info --}}
        <dl class="ws-detail-meta ws-detail-section">
            <dt>Assignee</dt>
            <dd class="flex flex-wrap gap-2">
                <template x-for="a in task.assignees" :key="a.id">
                    <span class="inline-flex items-center gap-2 text-sm font-medium text-gray-800">
                        <x-workspace.avatar-js user="a" size="xs" /><span x-text="a.name"></span>
                    </span>
                </template>
            </dd>
            <dt>Dibuat oleh</dt>
            <dd class="text-sm text-gray-800" x-text="task.creator ? task.creator.name : '—'"></dd>
            <template x-if="task.start_at">
                <dt>Mulai</dt>
            </template>
            <template x-if="task.start_at">
                <dd class="text-sm text-gray-800" x-text="dateTime(task.start_at)"></dd>
            </template>
            <dt>Deadline</dt>
            <dd class="text-sm">
                <span class="ws-due !text-sm" :class="'ws-due--' + due().state">
                    <i class="far fa-clock"></i><span x-text="due().label"></span>
                </span>
                <span class="text-xs ws-text-faint ml-1" x-show="task.status !== 'done'" x-text="'(' + due().relative + ')'"></span>
                <span class="text-xs text-emerald-600 ml-1" x-show="task.completed_at" x-text="'· selesai ' + dateTime(task.completed_at)"></span>
            </dd>
            <template x-if="task.labels.length">
                <dt>Label</dt>
            </template>
            <template x-if="task.labels.length">
                <dd class="flex flex-wrap gap-1">
                    <template x-for="label in task.labels" :key="label.id">
                        <span class="ws-label-chip"><span class="ws-label-chip__dot" :style="{ background: label.color }"></span><span x-text="label.name"></span></span>
                    </template>
                </dd>
            </template>
            <template x-if="task.workspace">
                <dt>Workspace</dt>
            </template>
            <template x-if="task.workspace">
                <dd class="text-sm"><a :href="task.workspace.url" class="inline-block py-0.5 text-indigo-600 hover:underline" x-text="task.workspace.name"></a></dd>
            </template>
        </dl>

        {{-- Deskripsi --}}
        <section class="ws-detail-section">
            <h3 class="ws-section-label mb-2">Deskripsi</h3>
            <div class="ws-prose" x-show="task.description_html" x-html="task.description_html"></div>
            <p class="text-sm ws-text-faint italic" x-show="!task.description_html">Tidak ada deskripsi.</p>
        </section>

        {{-- Checklist --}}
        <section class="ws-detail-section" x-show="task.checklist_items.length">
            <div class="flex items-center justify-between mb-2">
                <h3 class="ws-section-label">Checklist</h3>
                <span class="ws-mono text-xs text-slate-500" x-text="task.checklist.done + '/' + task.checklist.total + ' · ' + checklistPercent + '%'"></span>
            </div>
            <div class="ws-progress mb-3"><div class="ws-progress__bar transition-all" :style="{ width: checklistPercent + '%' }"></div></div>
            <ul class="space-y-1">
                <template x-for="item in task.checklist_items" :key="item.id">
                    <li>
                        <label class="flex items-start gap-3 px-2 py-1.5 rounded-lg" :class="canCheck ? 'cursor-pointer hover:bg-slate-50' : 'cursor-default'">
                            <input type="checkbox" class="mt-0.5 w-4 h-4 accent-indigo-600" :checked="item.is_done" :disabled="!canCheck" x-on:change="toggleItem(item)">
                            <span class="text-sm" :class="item.is_done ? 'line-through ws-text-faint' : 'text-gray-700'" x-text="item.content"></span>
                        </label>
                    </li>
                </template>
            </ul>
        </section>

        {{-- Link hasil --}}
        <section class="ws-detail-section" x-show="task.result_links.length">
            <h3 class="ws-section-label mb-2">Link hasil</h3>
            <ul class="space-y-2">
                <template x-for="(link, i) in task.result_links" :key="i">
                    <li>
                        <a :href="link.url" target="_blank" rel="noopener noreferrer" class="ws-tile !py-2.5 flex items-center gap-3 text-sm">
                            <i class="fas ws-text-faint" :class="/github/.test(link.url) ? 'fa-brands fa-github' : (/figma/.test(link.url) ? 'fa-brands fa-figma' : 'fa-link')"></i>
                            <span class="min-w-0">
                                <span class="block font-semibold text-gray-800 truncate" x-text="link.label || link.url"></span>
                                <span class="block text-xs ws-text-faint truncate" x-show="link.label" x-text="link.url"></span>
                            </span>
                            <i class="fas fa-arrow-up-right-from-square text-xs text-slate-300 ml-auto"></i>
                        </a>
                    </li>
                </template>
            </ul>
        </section>

        {{-- Lampiran --}}
        <section class="ws-detail-section" x-show="task.attachments.length">
            <h3 class="ws-section-label mb-2">Lampiran</h3>
            <ul class="grid sm:grid-cols-2 gap-2">
                <template x-for="file in task.attachments" :key="file.id">
                    <li>
                        <a :href="file.download_url" class="ws-tile !py-2.5 flex items-center gap-3" :download="file.original_name">
                            <template x-if="file.thumbnail_url">
                                <img :src="file.thumbnail_url" alt="" class="w-9 h-9 rounded-lg object-cover shrink-0">
                            </template>
                            <template x-if="!file.thumbnail_url">
                                <span class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0">
                                    <i class="fas" :class="fileIcon(file.mime).icon" :style="{ color: fileIcon(file.mime).color }"></i>
                                </span>
                            </template>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-gray-800 truncate" x-text="file.original_name"></span>
                                <span class="block text-xs ws-text-faint" x-text="fileSize(file.size)"></span>
                            </span>
                        </a>
                    </li>
                </template>
            </ul>
        </section>

        {{-- Komentar --}}
        <section class="ws-detail-section">
            <h3 class="ws-section-label mb-3">Komentar · <span x-text="task.comments.length"></span></h3>
            <ul class="space-y-4 mb-4" x-ref="comments">
                <template x-for="c in task.comments" :key="c.id">
                    <li class="flex gap-3">
                        <x-workspace.avatar-js user="c.user" size="sm" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm">
                                <span class="font-semibold text-gray-800" x-text="c.user.name"></span>
                                <span class="text-xs ws-text-faint ml-1" :title="dateTime(c.created_at)" x-text="relative(c.created_at)"></span>
                            </p>
                            <div class="ws-prose !text-[13px] mt-0.5" x-show="!c.is_deleted" x-html="c.body_html"></div>
                            <p class="text-sm italic ws-text-faint" x-show="c.is_deleted">Komentar dihapus</p>
                        </div>
                    </li>
                </template>
            </ul>
            <p class="text-sm ws-text-faint mb-4" x-show="!task.comments.length">Belum ada komentar. Diskusi khusus tugas ini ditulis di sini.</p>

            <template x-if="task.can.comment !== false">
                <form x-on:submit.prevent="sendComment()" class="flex gap-3 items-start">
                    <div class="flex-1">
                        <label for="ws-comment-{{ $mode }}" class="sr-only">Tulis komentar</label>
                        <textarea id="ws-comment-{{ $mode }}" rows="2" class="ws-input !text-sm" x-model="comment" :class="commentError && 'ws-input--error'"
                                  placeholder="Tulis komentar… (Ctrl+Enter untuk kirim)"
                                  x-on:keydown.ctrl.enter.prevent="sendComment()" x-on:keydown.meta.enter.prevent="sendComment()"></textarea>
                        <p class="ws-field-error" x-show="commentError" x-text="commentError"></p>
                    </div>
                    <button type="submit" class="ws-btn ws-btn--primary ws-btn--sm mt-1" :disabled="sending" aria-label="Kirim komentar">
                        <i class="fas" :class="sending ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                    </button>
                </form>
            </template>
        </section>

        {{-- Riwayat --}}
        <section class="ws-detail-section" x-data="{ all: false }" x-show="task.activities.length">
            <h3 class="ws-section-label mb-3">Riwayat</h3>
            <ol class="space-y-2.5">
                <template x-for="item in (all ? task.activities : task.activities.slice(0, 6))" :key="item.id">
                    <li class="flex items-start gap-2.5 text-[13px] text-slate-600">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-[10px]"
                              :style="{ background: describe(item).bg, color: describe(item).color }"><i class="fas" :class="describe(item).icon"></i></span>
                        <p class="pt-0.5">
                            <span class="font-semibold text-gray-700" x-text="describe(item).actor"></span>
                            {{-- Konteksnya tugas ini sendiri, jadi kata sambung sebelum kode tugas dibuang. --}}
                            <span x-text="describe(item).before.replace(/ (di|untuk|dari)$/, '')"></span>
                            <span x-text="describe(item).after"></span>
                            <span class="ws-text-faint" :title="dateTime(item.created_at)" x-text="'· ' + relative(item.created_at)"></span>
                        </p>
                    </li>
                </template>
            </ol>
            <button type="button" class="text-xs font-semibold text-indigo-600 mt-3" x-show="task.activities.length > 6 && !all" x-on:click="all = true">
                Tampilkan semua riwayat (<span x-text="task.activities.length"></span>)
            </button>
        </section>
    </div>
</template>
