{{--
    Halaman semua notifikasi. Variabel (lihat docs/workspace/kontrak-frontend.md §5):
      $notifications  { data: Notification[], meta: { current_page, has_more } } | null
                      Halaman pertama sesuai filter. null → diambil lewat JSON saat halaman dibuka.
      $filter         'all' | 'unread'                         (dari ?filter=)
      $category       null | 'task' | 'mention' | 'announcement' | 'intern'   (dari ?category=)
    Jumlah belum dibaca & status dibaca disinkronkan dengan lonceng (store wsNotif).
--}}
@extends('workspace.layout')

@section('title', 'Notifikasi - Sistem Magang')

@section('workspace-content')
    @php
        $categories = array_filter([
            'task' => ['Tugas', 'fa-clipboard-list'],
            'mention' => ['Mention', 'fa-at'],
            'announcement' => ['Pengumuman', 'fa-bullhorn'],
            // Notifikasi intern baru / tanpa mentor hanya dikirim ke mentor & admin.
            'intern' => auth()->user()->isIntern() ? null : ['Intern', 'fa-user-plus'],
        ]);
    @endphp

    <div class="max-w-4xl mx-auto"
         x-data="wsNotificationsPage(@js(['initial' => $notifications, 'filter' => $filter, 'category' => $category]))">
        <x-workspace.hero class="ws-anim-1 mb-6" eyebrow="Pusat notifikasi" icon="bell" color="#3b82f6" title="Notifikasi"
                          subtitle="Pemberitahuan tugas, mention, dan pengumuman dari semua workspace Anda.">
            <x-slot:meta>
                <span class="inline-flex items-center gap-1.5">
                    <i class="fas fa-bell"></i>
                    <span x-text="$store.wsNotif.loaded ? ($store.wsNotif.unread ? $store.wsNotif.unread + ' belum dibaca' : 'Semua sudah dibaca') : 'Memuat…'">Memuat…</span>
                </span>
            </x-slot:meta>

            @if (Route::has('notifications.settings'))
                <a href="{{ route('notifications.settings') }}" class="ws-hero-btn"><i class="fas fa-gear"></i> Pengaturan</a>
            @endif
            <button type="button" class="ws-hero-btn" x-on:click="markAllRead()" :disabled="!$store.wsNotif.unread"
                    :class="!$store.wsNotif.unread && 'opacity-60 cursor-not-allowed'">
                <i class="fas fa-check-double"></i> Tandai semua dibaca
            </button>
        </x-workspace.hero>

        <section class="ws-panel ws-anim-2">
            {{-- Filter --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5">
                <div class="ws-tabs self-start" role="tablist" aria-label="Status notifikasi">
                    @foreach (['all' => 'Semua', 'unread' => 'Belum dibaca'] as $key => $label)
                        <button type="button" role="tab" class="ws-tab" x-on:click="setFilter('{{ $key }}')"
                                aria-selected="{{ $filter === $key ? 'true' : 'false' }}" :aria-selected="(filter === '{{ $key }}').toString()">
                            {{ $label }}
                            @if ($key === 'unread')
                                <span class="ws-tab__count ws-tab__count--alert" x-cloak x-show="$store.wsNotif.unread > 0" x-text="$store.wsNotif.badgeText()"></span>
                            @endif
                        </button>
                    @endforeach
                </div>

                <div class="ws-notif-cats" role="group" aria-label="Kategori">
                    @foreach ($categories as $key => [$label, $icon])
                        <button type="button" class="ws-chip" x-on:click="setCategory('{{ $key }}')"
                                aria-pressed="{{ $category === $key ? 'true' : 'false' }}" :aria-pressed="(category === '{{ $key }}').toString()">
                            <i class="fas {{ $icon }} text-[11px]"></i> {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Ada notifikasi baru dari polling --}}
            <button type="button" class="ws-notif-new" x-cloak x-show="hasNew" x-transition.opacity x-on:click="load()">
                <i class="fas fa-arrow-rotate-right"></i> Ada notifikasi baru · tampilkan
            </button>

            {{-- Memuat --}}
            <template x-if="loading">
                <div class="space-y-4 py-2" aria-busy="true">
                    <template x-for="i in 6" :key="i">
                        <div class="flex gap-4 animate-pulse">
                            <span class="w-10 h-10 rounded-xl bg-slate-100 shrink-0"></span>
                            <span class="flex-1 space-y-2 pt-1">
                                <span class="block h-3 rounded bg-slate-100 w-2/3"></span>
                                <span class="block h-3 rounded bg-slate-100 w-1/2"></span>
                            </span>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Gagal --}}
            <template x-if="!loading && error">
                <x-workspace.empty-state icon="fa-plug-circle-exclamation" title="Notifikasi gagal dimuat">
                    <button type="button" class="ws-btn ws-btn--soft ws-btn--sm" x-on:click="load()"><i class="fas fa-rotate-right"></i> Coba lagi</button>
                </x-workspace.empty-state>
            </template>

            {{-- Kosong --}}
            <template x-if="!loading && !error && items.length === 0">
                <div class="flex flex-col items-center justify-center text-center px-6 py-12">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background:#f0f4ff;color:#3b4fd8;">
                        <i class="fas text-2xl" :class="filter === 'unread' ? 'fa-check-double' : 'fa-bell-slash'"></i>
                    </div>
                    <h2 class="text-base font-bold text-gray-800" x-text="emptyText[0]"></h2>
                    <p class="mt-1 text-sm text-slate-500 max-w-sm" x-text="emptyText[1]"></p>
                    <button type="button" class="mt-5 ws-btn ws-btn--soft ws-btn--sm" x-show="filter !== 'all' || category" x-on:click="filter = 'all'; category = null; syncUrl(); load()">
                        Lihat semua notifikasi
                    </button>
                </div>
            </template>

            {{-- Daftar per hari --}}
            <template x-if="!loading && !error">
                <div>
                    <template x-for="group in groups" :key="group.label">
                        <div class="mb-5 last:mb-0">
                            <p class="ws-section-label mb-2" x-text="group.label"></p>
                            <ul class="ws-notif-list">
                                <template x-for="item in group.items" :key="item.id">
                                    <li class="ws-notif" :class="!item.read_at && 'ws-notif--unread'">
                                        <a :href="item.url || '#'" class="ws-notif__link" x-on:click="open($event, item)">
                                            <span class="ws-notif__icon" :style="{ background: meta(item.type).bg, color: meta(item.type).color }">
                                                <i class="fas" :class="meta(item.type).icon"></i>
                                            </span>
                                            <span class="flex-1 min-w-0">
                                                <span class="block text-sm text-gray-800" :class="item.read_at ? 'font-medium' : 'font-bold'" x-text="item.title"></span>
                                                <span class="block text-sm text-slate-500 mt-0.5 line-clamp-2" x-show="item.body" x-text="item.body"></span>
                                                <span class="ws-notif__meta">
                                                    <span class="ws-notif__cat" x-text="categoryLabel(item.type)"></span>
                                                    <span x-show="item.actor" x-text="item.actor ? item.actor.name : ''"></span>
                                                    <span x-show="item.actor" aria-hidden="true">·</span>
                                                    <time :datetime="item.created_at" :title="dateTime(item.created_at)" x-text="relative(item.created_at)"></time>
                                                </span>
                                            </span>
                                        </a>
                                        <button type="button" class="ws-icon-btn ws-icon-btn--sm ws-notif__mark" x-show="!item.read_at"
                                                x-on:click="markRead(item)" title="Tandai dibaca" aria-label="Tandai dibaca">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <span class="ws-notif__dot" x-show="!item.read_at" aria-hidden="true"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <div class="pt-4 text-center" x-show="hasMore">
                        <button type="button" class="ws-btn ws-btn--soft ws-btn--sm" x-on:click="loadMore()" :disabled="loadingMore">
                            <i class="fas" :class="loadingMore ? 'fa-spinner fa-spin' : 'fa-angles-down'"></i>
                            <span x-text="loadingMore ? 'Memuat…' : 'Muat notifikasi lebih lama'"></span>
                        </button>
                    </div>
                </div>
            </template>
        </section>
    </div>
@endsection
