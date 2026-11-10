@extends('layouts.app')

@section('title', 'Aktivitas Terbaru')

@section('content')
@php
    $activeType = $type ?? request('type', 'semua');
@endphp

<div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- HEADER --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 mb-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.2em] text-blue-600 mb-2">
                    Manajemen Konten
                </p>

                <h1 class="text-3xl font-extrabold text-slate-900">
                    Aktivitas Terbaru
                </h1>

                <p class="text-slate-500 mt-2">
                    Kelola artikel dan konten YouTube yang tampil di landing page.
                </p>
            </div>

            <a href="{{ route('admin.aktivitas.create') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-500/20">
                <i class="fas fa-plus"></i>
                Tambah Aktivitas
            </a>
        </div>

        {{-- ALERT --}}
        @if(session('success'))
            <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 text-green-700 px-5 py-4 font-semibold">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- FILTER --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div class="inline-flex flex-wrap items-center gap-2 bg-white border border-slate-200 rounded-2xl p-2 shadow-sm w-fit">

                <a href="{{ route('admin.aktivitas.index', ['type' => 'semua']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition
                   {{ $activeType === 'semua' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-500 hover:bg-slate-50' }}">
                    <i class="fas fa-layer-group text-xs"></i>
                    Semua
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $activeType === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
                        {{ $totalAll ?? 0 }}
                    </span>
                </a>

                <a href="{{ route('admin.aktivitas.index', ['type' => 'artikel']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition
                   {{ $activeType === 'artikel' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-500 hover:bg-slate-50' }}">
                    <i class="fas fa-newspaper text-xs"></i>
                    Artikel
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $activeType === 'artikel' ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-600' }}">
                        {{ $totalArtikel ?? 0 }}
                    </span>
                </a>

                <a href="{{ route('admin.aktivitas.index', ['type' => 'youtube']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition
                   {{ $activeType === 'youtube' ? 'bg-red-500 text-white shadow-md shadow-red-500/20' : 'text-slate-500 hover:bg-slate-50' }}">
                    <i class="fab fa-youtube text-xs"></i>
                    YouTube
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $activeType === 'youtube' ? 'bg-white/20 text-white' : 'bg-red-50 text-red-600' }}">
                        {{ $totalYoutube ?? 0 }}
                    </span>
                </a>

            </div>

            <div class="text-sm text-slate-500 font-semibold">
                Menampilkan
                <span class="text-slate-800 font-extrabold">{{ $posts->firstItem() ?? 0 }}</span>
                -
                <span class="text-slate-800 font-extrabold">{{ $posts->lastItem() ?? 0 }}</span>
                dari
                <span class="text-slate-800 font-extrabold">{{ $posts->total() }}</span>
                konten
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">

            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px]">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-extrabold text-slate-500 uppercase">
                                Konten
                            </th>

                            <th class="px-5 py-4 text-left text-xs font-extrabold text-slate-500 uppercase w-40">
                                Tipe
                            </th>

                            <th class="px-5 py-4 text-left text-xs font-extrabold text-slate-500 uppercase w-36">
                                Tanggal
                            </th>

                            <th class="px-5 py-4 text-left text-xs font-extrabold text-slate-500 uppercase w-36">
                                Status
                            </th>

                            <th class="px-5 py-4 text-right text-xs font-extrabold text-slate-500 uppercase w-36">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($posts as $post)
                            <tr class="hover:bg-slate-50 transition">

                                {{-- KONTEN --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-4">
                                        @php
    $thumbnailUrl = $post->thumbnail_url;

    if ($post->type === 'youtube' && $post->youtube_id) {
        $thumbnailUrl = 'https://i.ytimg.com/vi/' . $post->youtube_id . '/hqdefault.jpg';
    }
@endphp

<div class="relative w-24 h-16 rounded-2xl overflow-hidden border border-slate-100 bg-slate-100 shrink-0">
    <img src="{{ $thumbnailUrl }}"
         alt="{{ $post->title }}"
         class="w-full h-full object-cover"
         referrerpolicy="no-referrer"
         onerror="this.onerror=null; this.src='{{ asset('storage/photos-landingpage/default-news.jpg') }}';" loading="lazy" decoding="async">

    @if($post->type === 'youtube')
        <div class="absolute inset-0 bg-black/25 flex items-center justify-center text-white">
            <i class="fas fa-play text-xs"></i>
        </div>
    @endif
</div>

                                        <div class="min-w-0">
                                            <p class="font-extrabold text-slate-900 leading-snug">
                                                {{ \Illuminate\Support\Str::limit($post->title, 90) }}
                                            </p>

                                            <p class="text-sm text-slate-500 mt-1 leading-relaxed">
                                                {{ \Illuminate\Support\Str::limit($post->excerpt ?? 'Tidak ada ringkasan.', 85) }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- TIPE --}}
                                <td class="px-5 py-4">
                                    @if($post->type === 'youtube')
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-50 text-red-600 text-xs font-bold">
                                            <i class="fab fa-youtube"></i>
                                            YouTube
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-blue-600 text-xs font-bold">
                                            <i class="fas fa-newspaper"></i>
                                            Artikel
                                        </span>
                                    @endif
                                </td>

                                {{-- TANGGAL --}}
                                <td class="px-5 py-4 text-sm text-slate-500 font-semibold">
                                    {{ $post->published_at ? $post->published_at->format('d M Y') : '-' }}
                                </td>

                                {{-- STATUS --}}
                                <td class="px-5 py-4">
                                    @if($post->is_published)
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-50 text-green-600 text-xs font-bold">
                                            <i class="fas fa-check-circle"></i>
                                            Published
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 text-slate-500 text-xs font-bold">
                                            <i class="fas fa-clock"></i>
                                            Draft
                                        </span>
                                    @endif
                                </td>

                                {{-- AKSI --}}
<td class="px-5 py-4">
    <div class="flex items-center justify-end gap-2">

        {{-- Detail Admin --}}
        <a href="{{ route('admin.aktivitas.show', $post) }}"
           title="Detail"
           class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 flex items-center justify-center transition">
            <i class="fas fa-eye text-xs"></i>
        </a>

        {{-- Edit --}}
        <a href="{{ route('admin.aktivitas.edit', $post) }}"
           title="Edit"
           class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition">
            <i class="fas fa-edit text-xs"></i>
        </a>

        {{-- Hapus --}}
        <form action="{{ route('admin.aktivitas.destroy', $post) }}"
              method="POST"
              onsubmit="return confirm('Yakin ingin menghapus aktivitas ini?')">
            @csrf
            @method('DELETE')

            <button type="submit"
                    title="Hapus"
                    class="w-9 h-9 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition">
                <i class="fas fa-trash text-xs"></i>
            </button>
        </form>

    </div>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-16 text-center text-slate-400">
                                    <div class="w-20 h-20 mx-auto rounded-3xl bg-slate-100 flex items-center justify-center mb-4">
                                        <i class="fas fa-newspaper text-3xl text-slate-300"></i>
                                    </div>

                                    <p class="font-extrabold text-slate-500">
                                        Belum ada aktivitas.
                                    </p>

                                    <p class="text-sm text-slate-400 mt-1">
                                        Tambahkan artikel atau video YouTube untuk ditampilkan di landing page.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="px-5 py-4 border-t border-slate-100 bg-white">
                @if($posts->hasPages())
                    {{ $posts->links() }}
                @else
                    <div class="text-sm text-slate-400 font-semibold">
                        Semua data sudah ditampilkan.
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection