{{--
    Pengaturan notifikasi per user (PRD Fase 3). Variabel:
      $preferences  { types: [{ type, group, label, description, in_app, email: bool|null }], muted_channels: [...] } | null
                    null → diambil lewat JSON saat halaman dibuka.
    `email: null` = jenis ini tidak dikirim lewat email (PRD: hanya tugas baru, Selesai/Revisi, dan deadline H-1).
--}}
@extends('workspace.layout')

@section('title', 'Pengaturan Notifikasi - Sistem Magang')

@section('workspace-content')
    <div class="max-w-4xl mx-auto" x-data="wsNotifSettings(@js(['initial' => $preferences]))">
        <nav class="mb-4 text-sm text-slate-600 flex items-center gap-2 min-w-0" aria-label="Breadcrumb">
            <a href="{{ route('notifications.index') }}" class="hover:text-indigo-600 shrink-0 py-1 -my-1"><i class="fas fa-bell mr-1"></i>Notifikasi</a>
            <i class="fas fa-chevron-right text-[10px] text-slate-300" aria-hidden="true"></i>
            <span class="font-medium text-slate-700" aria-current="page">Pengaturan</span>
        </nav>

        <x-workspace.hero class="ws-anim-1 mb-6" eyebrow="Pusat notifikasi" icon="sliders" color="#3b82f6" title="Pengaturan Notifikasi"
                          subtitle="Pilih pemberitahuan yang ingin Anda terima di aplikasi dan lewat email." />

        {{-- Memuat / gagal --}}
        <template x-if="loading">
            <div class="ws-panel space-y-4" aria-busy="true">
                <template x-for="i in 5" :key="i">
                    <div class="flex items-center gap-4 animate-pulse">
                        <span class="w-10 h-10 rounded-xl bg-slate-100 shrink-0"></span>
                        <span class="flex-1 space-y-2"><span class="block h-3 rounded bg-slate-100 w-1/3"></span><span class="block h-3 rounded bg-slate-100 w-2/3"></span></span>
                    </div>
                </template>
            </div>
        </template>
        <template x-if="!loading && error">
            <div class="ws-panel">
                <x-workspace.empty-state icon="fa-plug-circle-exclamation" title="Pengaturan gagal dimuat">
                    <button type="button" class="ws-btn ws-btn--soft ws-btn--sm" x-on:click="load()"><i class="fas fa-rotate-right"></i> Coba lagi</button>
                </x-workspace.empty-state>
            </div>
        </template>

        <template x-if="!loading && !error">
            <div class="space-y-6">
                {{-- Jenis notifikasi --}}
                <section class="ws-panel ws-anim-2" aria-labelledby="ws-ns-types">
                    <div class="flex items-end justify-between gap-4 mb-2">
                        <h2 id="ws-ns-types" class="font-bold text-gray-800">Jenis notifikasi</h2>
                        <div class="ws-ns-cols" aria-hidden="true"><span>Aplikasi</span><span>Email</span></div>
                    </div>
                    <template x-for="g in groups" :key="g.name">
                        <div class="mt-4">
                            <p class="ws-section-label mb-1" x-text="g.name"></p>
                            <ul class="divide-y divide-slate-100">
                                <template x-for="t in g.items" :key="t.type">
                                    <li class="ws-ns-row">
                                        <span class="ws-notif__icon !w-9 !h-9 shrink-0" :style="{ background: meta(t.type).bg, color: meta(t.type).color }" aria-hidden="true">
                                            <i class="fas" :class="meta(t.type).icon"></i>
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-sm font-semibold text-gray-800" :id="'ws-ns-' + t.type" x-text="t.label"></span>
                                            <span class="block text-xs text-slate-500" x-text="t.description"></span>
                                        </span>
                                        <span class="ws-ns-cols">
                                            <button type="button" role="switch" class="ws-switch" :aria-checked="t.in_app.toString()"
                                                    :aria-label="t.label + ' di aplikasi'" x-on:click="t.in_app = !t.in_app"><span></span></button>
                                            <template x-if="t.email !== null">
                                                <button type="button" role="switch" class="ws-switch" :aria-checked="t.email.toString()"
                                                        :aria-label="t.label + ' lewat email'" x-on:click="t.email = !t.email"><span></span></button>
                                            </template>
                                            <template x-if="t.email === null">
                                                <span class="ws-ns-na" title="Tidak tersedia lewat email">—<span class="sr-only">Tidak tersedia lewat email</span></span>
                                            </template>
                                        </span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>
                    <p class="mt-5 text-xs text-slate-500 flex items-start gap-2">
                        <i class="fas fa-circle-info mt-0.5" aria-hidden="true"></i>
                        Notifikasi yang dimatikan tetap tercatat di riwayat tugas dan channel; Anda hanya tidak diberi tahu.
                    </p>
                </section>

                {{-- Channel dibisukan --}}
                <section class="ws-panel ws-anim-3" aria-labelledby="ws-ns-muted">
                    <h2 id="ws-ns-muted" class="font-bold text-gray-800">Channel yang dibisukan</h2>
                    <p class="text-sm text-slate-500 mt-1">Pesan baru di channel ini tidak menambah badge. Mention untuk Anda tetap diberitahukan.</p>
                    <p class="mt-4 text-sm text-slate-500" x-show="!muted.length">Tidak ada channel yang dibisukan. Bisukan lewat menu <i class="fas fa-ellipsis-vertical mx-0.5" aria-hidden="true"></i> di header channel.</p>
                    <ul class="mt-3 space-y-2" x-show="muted.length">
                        <template x-for="c in muted" :key="c.id">
                            <li class="ws-tile flex items-center gap-3">
                                <i class="fas fa-bell-slash text-slate-500" aria-hidden="true"></i>
                                <span class="min-w-0 flex-1">
                                    <a :href="c.workspace.url + '?tab=channel&channel=' + c.id" class="block text-sm font-semibold text-gray-800 hover:text-indigo-700 truncate" x-text="'#' + c.name"></a>
                                    <span class="block text-xs text-slate-500 truncate" x-text="c.workspace.name"></span>
                                </span>
                                <button type="button" class="ws-btn ws-btn--soft ws-btn--sm shrink-0" x-on:click="unmute(c)" :disabled="!!unmuting[c.id]">
                                    <i class="fas" :class="unmuting[c.id] ? 'fa-spinner fa-spin' : 'fa-bell'"></i> Aktifkan
                                </button>
                            </li>
                        </template>
                    </ul>
                </section>
            </div>
        </template>

        {{-- Bar simpan (muncul saat ada perubahan) --}}
        <div class="ws-savebar" x-show="dirty" x-cloak x-transition.opacity role="region" aria-label="Simpan perubahan">
            <span class="text-sm font-semibold text-gray-800"><i class="fas fa-pen-to-square mr-1.5 text-indigo-600" aria-hidden="true"></i>Ada perubahan yang belum disimpan</span>
            <span class="ml-auto flex gap-2">
                <button type="button" class="ws-btn ws-btn--ghost ws-btn--sm" x-on:click="reset()">Batalkan</button>
                <button type="button" class="ws-btn ws-btn--primary ws-btn--sm" x-on:click="save()" :disabled="saving">
                    <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-check'"></i> Simpan
                </button>
            </span>
        </div>
    </div>
@endsection
