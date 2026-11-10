{{--
    Tab Channel: daftar channel + percakapan + komposer + panel samping (thread / cari / disematkan).
    Variabel: $workspace, $channels (Channel[]), $members (UserMini[]), $tab, $can['manage_channels'].
    Store wsChat di-boot di show.blade.php. Pesan diambil lewat WS.api.messages
    (lihat docs/workspace/kontrak-frontend.md §6 "Polling pesan").
--}}
<div class="ws-chat" :class="panel && 'ws-chat--panel'"
     x-data="wsChannels(@js(['workspaceId' => $workspace['id'], 'members' => $members, 'tabOpen' => $tab === 'channel', 'canCreateChannel' => $can['manage_channels'] ?? false]))"
     x-on:ws-chat-send="sendMessage($event.detail)">

    {{-- ================= Daftar channel ================= --}}
    <aside class="ws-chat__side" :class="view === 'chat' && 'ws-chat__side--away'" aria-label="Daftar channel">
        <div class="ws-chat__side-head">
            <p class="font-bold text-gray-800">Channel <x-workspace.help term="channel" label="Apa itu Channel?" /></p>
            <span class="text-xs ws-text-faint ml-auto" x-show="$store.wsChat.unreadTotal > 0" x-text="$store.wsChat.unreadTotal + ' belum dibaca'"></span>
            <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-show="canCreateChannel" x-on:click="openChannelForm()"
                    title="Buat channel" aria-label="Buat channel">
                <i class="fas fa-plus"></i>
            </button>
        </div>
        <nav class="ws-chat__side-body">
            <template x-for="section in $store.wsChat.sections" :key="section.label">
                <div class="mb-4 last:mb-0">
                    <p class="ws-section-label px-3 mb-1.5" x-text="section.label"></p>
                    <ul class="space-y-0.5">
                        <template x-for="c in section.items" :key="c.id">
                            <li>
                                <button type="button" class="ws-chan"
                                        :class="{ 'ws-chan--active': $store.wsChat.activeId === c.id, 'ws-chan--unread': c.unread_count > 0, 'ws-chan--archived': c.is_archived && !$store.wsChat.readOnly }"
                                        :aria-current="($store.wsChat.activeId === c.id).toString()"
                                        x-on:click="open(c.id)">
                                    <template x-if="c.type === 'direct'">
                                        <span class="ws-chan__icon" x-data="{ peer: (c.members || []).find((m) => m.id !== WS.config.user.id) || (c.members || [])[0] }">
                                            <template x-if="peer"><x-workspace.avatar-js user="peer" size="xs" /></template>
                                        </span>
                                    </template>
                                    <template x-if="c.type !== 'direct'">
                                        <i class="fas ws-chan__icon" :class="channelIcon(c)"></i>
                                    </template>
                                    <span class="truncate flex-1 text-left" x-text="$store.wsChat.title(c)"></span>
                                    <i class="fas fa-bell-slash text-[11px] opacity-70" x-show="c.is_muted" aria-hidden="true" title="Dibisukan"></i>
                                    <span class="ws-chan__badge" :class="c.is_muted && 'ws-chan__badge--muted'" x-show="c.unread_count > 0" x-text="c.unread_count > 99 ? '99+' : c.unread_count"
                                          :aria-label="c.unread_count + ' pesan belum dibaca' + (c.is_muted ? ', channel dibisukan' : '')"></span>
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>
            </template>
        </nav>
    </aside>

    {{-- ================= Percakapan ================= --}}
    <div class="ws-chat__main" :class="view === 'list' && 'ws-chat__main--away'">
        <template x-if="channel">
            <div class="contents">
                <header class="ws-chat__head">
                    <button type="button" class="ws-icon-btn md:hidden -ml-2" x-on:click="back()" aria-label="Kembali ke daftar channel">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <span class="ws-chat__head-icon">
                        <template x-if="channel.type === 'direct'">
                            <span x-data="{ get peer() { return (channel.members || []).find((m) => m.id !== WS.config.user.id) || (channel.members || [])[0]; } }">
                                <template x-if="peer"><x-workspace.avatar-js user="peer" size="sm" /></template>
                            </span>
                        </template>
                        <template x-if="channel.type !== 'direct'">
                            <i class="fas" :class="channelIcon(channel)"></i>
                        </template>
                    </span>
                    <div class="min-w-0 flex-1">
                        <h2 class="font-bold text-gray-800 truncate leading-tight" x-text="(channel.type === 'direct' ? '' : '#') + $store.wsChat.title(channel)"></h2>
                        <p class="text-xs text-slate-500 truncate" x-text="subtitle"></p>
                    </div>
                    <span class="ws-badge shrink-0 !bg-slate-100 !text-slate-600 hidden sm:inline-flex" x-show="channel.is_archived"><i class="fas fa-box-archive"></i> Diarsipkan</span>
                    <span class="ws-badge shrink-0 !bg-cyan-50 !text-cyan-700 hidden xl:inline-flex" x-show="channel.type === 'announcement'"><i class="fas fa-globe"></i> Semua peserta</span>

                    <div class="flex items-center gap-0.5 shrink-0">
                        <button type="button" class="ws-icon-btn ws-icon-btn--sm" :class="panel === 'search' && 'ws-icon-btn--on'"
                                :aria-pressed="(panel === 'search').toString()" aria-label="Cari pesan" title="Cari pesan"
                                x-on:click="panel === 'search' ? closePanel() : openSearch()">
                            <i class="fas fa-magnifying-glass"></i>
                        </button>
                        <button type="button" class="ws-icon-btn ws-icon-btn--sm" :class="panel === 'pins' && 'ws-icon-btn--on'"
                                :aria-pressed="(panel === 'pins').toString()" :aria-label="'Pesan disematkan' + (channel.pinned_count ? ' (' + channel.pinned_count + ')' : '')" title="Pesan disematkan"
                                x-on:click="panel === 'pins' ? closePanel() : openPins()">
                            <i class="fas fa-thumbtack"></i>
                            <span class="ws-chat__pin-count" x-show="channel.pinned_count > 0" x-text="channel.pinned_count"></span>
                        </button>
                        <div class="relative" x-data="{ menu: false }" x-on:click.outside="menu = false"
                             x-on:keydown.escape="if (menu) { menu = false; $event.preventDefault(); $el.querySelector('button').focus(); }">
                            <button type="button" class="ws-icon-btn ws-icon-btn--sm" :aria-expanded="menu.toString()" aria-label="Opsi channel" x-on:click="menu = !menu">
                                <i class="fas fa-ellipsis-vertical"></i>
                            </button>
                            <div class="ws-menu" x-show="menu" x-cloak x-transition.opacity>
                                <button type="button" class="ws-menu__item" x-on:click="menu = false; toggleMute()">
                                    <i class="fas w-4" :class="channel.is_muted ? 'fa-bell' : 'fa-bell-slash'"></i>
                                    <span x-text="channel.is_muted ? 'Aktifkan notifikasi' : 'Bisukan channel'"></span>
                                </button>
                                <template x-if="channel.can.manage">
                                    <button type="button" class="ws-menu__item" x-on:click="menu = false; openChannelForm(channel)"><i class="fas fa-pen w-4"></i>Ubah info channel</button>
                                </template>
                                {{-- #umum tim dibuat sistem dan tidak bisa diarsipkan. --}}
                                <template x-if="channel.can.manage && channel.type === 'topic'">
                                    <button type="button" class="ws-menu__item" :class="!channel.is_archived && 'ws-menu__item--danger'" x-on:click="menu = false; archiveOpen = true">
                                        <i class="fas w-4" :class="channel.is_archived ? 'fa-box-open' : 'fa-box-archive'"></i>
                                        <span x-text="channel.is_archived ? 'Pulihkan channel' : 'Arsipkan channel'"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="ws-chat__body">
                <div class="ws-chat__scroll" x-ref="scroller" data-scroller role="log" :aria-label="'Pesan di ' + $store.wsChat.title(channel)"
                     x-on:scroll.passive="onScroll()" x-on:click="onContentClick($event)">

                    {{-- Memuat --}}
                    <template x-if="!state || state.loading">
                        <div class="space-y-5 py-2" aria-hidden="true">
                            <template x-for="i in 5" :key="i">
                                <div class="flex gap-3 animate-pulse">
                                    <span class="w-10 h-10 rounded-full bg-slate-100 shrink-0"></span>
                                    <div class="flex-1 space-y-2 pt-1">
                                        <span class="block h-3 w-32 rounded bg-slate-100"></span>
                                        <span class="block h-3 rounded bg-slate-100" :style="'width:' + (40 + i * 9) + '%'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Gagal memuat --}}
                    <template x-if="state && state.error">
                        <x-workspace.empty-state icon="fa-triangle-exclamation" title="Pesan gagal dimuat">
                            <p class="text-sm text-slate-500 -mt-3 mb-1 w-full" x-text="state.error"></p>
                            <button type="button" class="ws-btn ws-btn--soft ws-btn--sm" x-on:click="retryLoad()"><i class="fas fa-rotate-right"></i> Coba lagi</button>
                        </x-workspace.empty-state>
                    </template>

                    <template x-if="state && !state.loading && !state.error">
                        <div>
                            <div class="py-3 text-center text-xs ws-text-faint" x-show="state.loadingOlder">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Memuat pesan sebelumnya…
                            </div>

                            {{-- Awal channel --}}
                            <div class="ws-chat__intro" x-show="!state.hasMore">
                                <span class="ws-chat__intro-icon"><i class="fas" :class="channel.type === 'direct' ? 'fa-comments' : channelIcon(channel)"></i></span>
                                <p class="font-bold text-gray-800" x-text="channel.type === 'direct' ? 'Awal percakapan dengan ' + $store.wsChat.title(channel) : 'Ini awal channel #' + channel.name"></p>
                                <p class="text-sm text-slate-500" x-text="channel.description || ''"></p>
                            </div>

                            <template x-for="row in rows" :key="row.key">
                                <div>
                                    <template x-if="row.type === 'day'">
                                        <div class="ws-chat__day"><span x-text="row.label"></span></div>
                                    </template>
                                    <template x-if="row.type === 'new'">
                                        <div class="ws-chat__new" data-new-divider><span>Pesan baru</span></div>
                                    </template>
                                    <template x-if="row.type === 'msg'">
                                        @include('workspace.partials.channel-message', ['inThread' => false])
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Pesan baru di bawah saat user sedang membaca pesan lama --}}
                <div class="ws-chat__jump" x-show="state && !state.loading && !atBottom" x-cloak x-transition.opacity>
                    <button type="button" class="ws-chat__jump-btn" x-on:click="jumpToLatest()">
                        <i class="fas fa-arrow-down"></i>
                        <span x-text="newBelow ? newBelow + ' pesan baru' : 'Ke pesan terbaru'"></span>
                    </button>
                </div>
                </div>{{-- /ws-chat__body --}}

                @include('workspace.partials.channel-composer', ['mode' => 'channel', 'show' => 'channel.can.post'])

                <div class="ws-composer-note" x-show="!channel.can.post">
                    <i class="fas fa-lock"></i>
                    <span x-text="postNote"></span>
                </div>
            </div>
        </template>
    </div>

    {{-- ================= Panel samping: thread / cari / disematkan ================= --}}
    <aside class="ws-chat__panel" x-show="panel && channel" x-cloak
           :aria-label="panel === 'thread' ? 'Thread' : panel === 'search' ? 'Cari pesan' : 'Pesan disematkan'">
        <template x-if="panel && channel">
            <div class="contents">
                <header class="ws-chat__panel-head">
                    <span class="ws-chat__head-icon !w-8 !h-8">
                        <i class="fas" :class="panel === 'thread' ? 'fa-comments' : panel === 'search' ? 'fa-magnifying-glass' : 'fa-thumbtack'"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-gray-800 leading-tight" x-text="panel === 'thread' ? 'Thread' : panel === 'search' ? 'Cari pesan' : 'Pesan disematkan'"></h3>
                        <p class="text-xs text-slate-500 truncate" x-text="(channel.type === 'direct' ? '' : '#') + $store.wsChat.title(channel)"></p>
                    </div>
                    <button type="button" class="ws-icon-btn ws-icon-btn--sm" x-on:click="closePanel()" aria-label="Tutup panel">
                        <i class="fas fa-xmark"></i>
                    </button>
                </header>

                {{-- Thread --}}
                <template x-if="panel === 'thread'">
                    <div class="contents">
                        <div class="ws-chat__panel-body" data-thread-scroller x-on:click="onContentClick($event)">
                            <template x-for="row in (threadParentRow ? [threadParentRow] : [])" :key="row.key">
                                @include('workspace.partials.channel-message', ['inThread' => true])
                            </template>
                            <div class="ws-chat__day" x-show="!thread.loading && !thread.error">
                                <span x-text="thread.replies.length ? thread.replies.filter((r) => typeof r.id === 'number').length + ' balasan' : 'Belum ada balasan'"></span>
                            </div>
                            <template x-if="thread.loading">
                                <p class="text-center text-sm text-slate-500 py-6"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat balasan…</p>
                            </template>
                            <template x-if="thread.error">
                                <div class="text-center text-sm text-slate-600 py-6">
                                    <p x-text="thread.error"></p>
                                    <button type="button" class="mt-2 font-semibold text-indigo-600" x-on:click="openThread(thread.id)">Coba lagi</button>
                                </div>
                            </template>
                            <template x-for="row in threadRows" :key="row.key">
                                @include('workspace.partials.channel-message', ['inThread' => true])
                            </template>
                        </div>
                        @include('workspace.partials.channel-composer', ['mode' => 'thread', 'show' => 'channel.can.post && thread.parent && !thread.parent.is_deleted'])
                    </div>
                </template>

                {{-- Cari --}}
                <template x-if="panel === 'search'">
                    <div class="contents">
                        <div class="px-4 pt-3">
                            <div class="relative">
                                <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-xs ws-text-faint" aria-hidden="true"></i>
                                <input type="search" data-search-input class="ws-input !py-2 !pl-9 !text-sm" x-model="search.q" x-on:input="onSearchInput()"
                                       :placeholder="'Cari di ' + (channel.type === 'direct' ? 'percakapan ini' : '#' + channel.name)" aria-label="Kata kunci pesan">
                            </div>
                        </div>
                        <div class="ws-chat__panel-body" aria-live="polite">
                            <p class="text-sm text-slate-500 text-center py-6" x-show="search.q.trim().length < 2">Ketik minimal 2 huruf untuk mencari pesan dan balasan thread.</p>
                            <p class="text-sm text-slate-500 text-center py-6" x-show="search.loading"><i class="fas fa-spinner fa-spin mr-1"></i> Mencari…</p>
                            <p class="text-sm text-red-700 text-center py-6" x-show="search.error && !search.loading" x-text="search.error"></p>
                            <p class="text-sm text-slate-500 text-center py-6" x-show="search.done && !search.loading && !search.results.length && search.q.trim().length >= 2">Tidak ada pesan yang cocok.</p>
                            <p class="ws-section-label px-1 mb-2" x-show="search.results.length && !search.loading"
                               x-text="search.total > search.results.length ? search.results.length + ' dari ' + search.total + ' hasil' : search.total + ' hasil'"></p>
                            <ul class="space-y-1" x-show="!search.loading">
                                <template x-for="m in search.results" :key="m.id">
                                    <li>
                                        <button type="button" class="ws-chat__result" x-on:click="jumpTo(m)">
                                            <span class="flex items-center gap-2 text-xs">
                                                <span class="font-semibold text-gray-800 truncate" x-text="m.user ? m.user.name : 'Simagang'"></span>
                                                <span class="ws-text-faint shrink-0" x-text="relative(m.created_at)"></span>
                                                <span class="ws-chat__result-tag" x-show="m.parent_id">di thread</span>
                                            </span>
                                            <span class="block text-sm text-slate-700 mt-0.5 line-clamp-3" x-html="excerpt(m, search.q.trim())"></span>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </template>

                {{-- Disematkan --}}
                <template x-if="panel === 'pins'">
                    <div class="ws-chat__panel-body">
                        <p class="text-sm text-slate-500 text-center py-6" x-show="pins.loading"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat…</p>
                        <div class="text-center text-sm text-slate-600 py-6" x-show="pins.error && !pins.loading">
                            <p x-text="pins.error"></p>
                            <button type="button" class="mt-2 font-semibold text-indigo-600" x-on:click="openPins()">Coba lagi</button>
                        </div>
                        <div class="text-center py-8 px-4" x-show="!pins.loading && !pins.error && !pins.items.length">
                            <i class="fas fa-thumbtack text-2xl ws-text-faint mb-2"></i>
                            <p class="text-sm font-semibold text-gray-800">Belum ada pesan disematkan</p>
                            <p class="text-xs text-slate-500 mt-1">Mentor dan admin bisa menyematkan pesan penting lewat menu ⋯ pada pesan.</p>
                        </div>
                        <ul class="space-y-2" x-show="!pins.loading">
                            <template x-for="m in pins.items" :key="m.id">
                                <li class="ws-chat__pinned">
                                    <button type="button" class="ws-chat__result !p-0" x-on:click="jumpTo(m)">
                                        <span class="flex items-center gap-2 text-xs">
                                            <span class="font-semibold text-gray-800 truncate" x-text="m.user ? m.user.name : 'Simagang'"></span>
                                            <span class="ws-text-faint shrink-0" x-text="relative(m.created_at)"></span>
                                        </span>
                                        <span class="block text-sm text-slate-700 mt-0.5 line-clamp-3" x-html="excerpt(m, '')"></span>
                                    </button>
                                    <button type="button" class="text-xs font-semibold text-slate-600 hover:text-red-700 mt-2 py-1" x-show="m.can.pin" x-on:click="togglePin(m)">
                                        <i class="fas fa-thumbtack-slash mr-1"></i>Lepas sematan
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>
            </div>
        </template>
    </aside>

    {{-- Hapus pesan --}}
    <x-workspace.modal name="ws-message-delete" title="Hapus pesan?" max-width="sm" x-model="deleteOpen">
        <p class="text-sm text-slate-600">Untuk semua anggota, pesan diganti keterangan <em>"Pesan ini telah dihapus"</em>. Tindakan ini tidak dapat dibatalkan.</p>
        <template x-if="deleting && deleting.body">
            <blockquote class="ws-tile mt-3 text-sm text-slate-600 line-clamp-3 whitespace-pre-line" x-text="deleting.body"></blockquote>
        </template>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="deleteOpen = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--danger" x-on:click="confirmDelete()" :disabled="removing">
                <i class="fas" :class="removing ? 'fa-spinner fa-spin' : 'fa-trash'"></i> Hapus pesan
            </button>
        </x-slot:footer>
    </x-workspace.modal>

    {{-- Buat / ubah channel --}}
    <x-workspace.modal name="ws-channel-form" max-width="md" labelledby="ws-channel-form-heading" x-model="channelForm.open">
        <form x-on:submit.prevent="saveChannel()" novalidate class="-mt-1">
            <h2 id="ws-channel-form-heading" class="text-lg font-bold text-gray-800 mb-1" x-text="channelForm.editing ? 'Ubah info #' + channelForm.editing.name : 'Buat channel'"></h2>
            <p class="text-sm text-slate-500 mb-5" x-show="!channelForm.editing">Channel topik untuk diskusi yang lebih terarah, misalnya satu proyek.</p>
            <div class="space-y-5">
                <div>
                    <label class="ws-label" for="ws-c-name">Nama channel <span class="text-red-600">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 font-semibold" aria-hidden="true">#</span>
                        <input id="ws-c-name" type="text" class="ws-input !pl-8" data-autofocus maxlength="40" autocomplete="off"
                               :value="channelForm.name" x-on:input="onChannelName($event)" :class="channelForm.errors.name && 'ws-input--error'"
                               placeholder="proyek-website" aria-describedby="ws-c-name-hint">
                    </div>
                    <p class="ws-field-error" x-show="channelForm.errors.name" x-text="channelForm.errors.name"></p>
                    <p id="ws-c-name-hint" class="text-xs text-slate-500 mt-1" x-show="!channelForm.errors.name">Huruf kecil, angka, dan tanda hubung. Spasi otomatis menjadi "-".</p>
                </div>
                <div>
                    <div class="flex items-center justify-between">
                        <label class="ws-label" for="ws-c-desc">Deskripsi</label>
                        <span class="text-xs" :class="channelForm.description.length > 120 ? 'text-red-600' : 'ws-text-faint'" x-text="channelForm.description.length + '/120'"></span>
                    </div>
                    <input id="ws-c-desc" type="text" class="ws-input" x-model="channelForm.description" maxlength="140"
                           :class="channelForm.errors.description && 'ws-input--error'" placeholder="Topik yang dibahas di channel ini">
                    <p class="ws-field-error" x-show="channelForm.errors.description" x-text="channelForm.errors.description"></p>
                </div>
                <template x-if="!channelForm.editing">
                    <fieldset>
                        <legend class="ws-label">Siapa yang bisa bergabung</legend>
                        <div class="grid sm:grid-cols-2 gap-2">
                            <label class="ws-tpl-option" :class="!channelForm.is_private && 'ws-tpl-option--on'">
                                <input type="radio" name="ws-c-privacy" class="mt-1 accent-indigo-600" :checked="!channelForm.is_private" x-on:change="channelForm.is_private = false">
                                <span>
                                    <span class="block text-sm font-semibold text-gray-800"><i class="fas fa-hashtag text-xs mr-1"></i>Publik</span>
                                    <span class="block text-xs text-slate-600">Semua anggota workspace.</span>
                                </span>
                            </label>
                            <label class="ws-tpl-option" :class="channelForm.is_private && 'ws-tpl-option--on'">
                                <input type="radio" name="ws-c-privacy" class="mt-1 accent-indigo-600" :checked="channelForm.is_private" x-on:change="channelForm.is_private = true">
                                <span>
                                    <span class="block text-sm font-semibold text-gray-800"><i class="fas fa-lock text-xs mr-1"></i>Privat</span>
                                    <span class="block text-xs text-slate-600">Hanya anggota yang dipilih.</span>
                                </span>
                            </label>
                        </div>
                    </fieldset>
                </template>
                <template x-if="!channelForm.editing && channelForm.is_private">
                    <div>
                        <p class="ws-label" id="ws-c-members">Anggota <span class="font-normal text-slate-500" x-text="'· ' + channelForm.member_ids.length + ' dipilih'"></span></p>
                        <div role="group" aria-labelledby="ws-c-members">
                            <ul class="ws-tpl-pick" :class="channelForm.errors.member_ids && 'ws-tpl-pick--error'">
                                <template x-for="m in members" :key="m.id">
                                    <li>
                                        <label class="ws-tpl-pick__item">
                                            <input type="checkbox" class="w-4 h-4 accent-indigo-600" :checked="channelForm.member_ids.includes(m.id)"
                                                   :disabled="m.id === WS.config.user.id" x-on:change="toggleMember(m.id)">
                                            <x-workspace.avatar-js user="m" size="xs" />
                                            <span class="text-sm font-medium text-gray-800 truncate" x-text="m.name + (m.id === WS.config.user.id ? ' (Anda)' : '')"></span>
                                            <span class="ml-auto text-xs text-slate-500" x-text="m.role === 'mentor' ? 'Mentor' : 'Intern'"></span>
                                        </label>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        <p class="ws-field-error" x-show="channelForm.errors.member_ids" x-text="channelForm.errors.member_ids"></p>
                    </div>
                </template>
            </div>
        </form>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="channelForm.open = false">Batal</button>
            <button type="button" class="ws-btn ws-btn--primary" x-on:click="saveChannel()" :disabled="channelForm.saving">
                <i class="fas" :class="channelForm.saving ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                <span x-text="channelForm.editing ? 'Simpan' : 'Buat channel'"></span>
            </button>
        </x-slot:footer>
    </x-workspace.modal>

    {{-- Arsipkan / pulihkan channel --}}
    <x-workspace.modal name="ws-channel-archive" max-width="sm" labelledby="ws-channel-archive-heading" x-model="archiveOpen">
        <template x-if="channel">
            <div>
                <h2 id="ws-channel-archive-heading" class="text-lg font-bold text-gray-800" x-text="(channel.is_archived ? 'Pulihkan #' : 'Arsipkan #') + channel.name + '?'"></h2>
                <p class="text-sm text-slate-600 mt-2" x-text="channel.is_archived
                    ? 'Anggota bisa mengirim pesan lagi di channel ini.'
                    : 'Channel menjadi hanya baca dan dipindah ke bagian Diarsipkan. Semua pesan tetap tersimpan.'"></p>
            </div>
        </template>
        <x-slot:footer>
            <button type="button" class="ws-btn ws-btn--ghost" x-on:click="archiveOpen = false">Batal</button>
            <button type="button" class="ws-btn" :class="channel && channel.is_archived ? 'ws-btn--primary' : 'ws-btn--danger'" x-on:click="confirmArchive()" :disabled="archiving">
                <i class="fas" :class="archiving ? 'fa-spinner fa-spin' : (channel && channel.is_archived ? 'fa-box-open' : 'fa-box-archive')"></i>
                <span x-text="channel && channel.is_archived ? 'Pulihkan' : 'Arsipkan'"></span>
            </button>
        </x-slot:footer>
    </x-workspace.modal>
</div>
