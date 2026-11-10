@extends('layouts.app')

@section('title', 'Mikro Skill Intern - Sistem Manajemen Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin-microskill-index.css') }}?v={{ @filemtime(public_path('css/pages/admin-microskill-index.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">

            {{-- ── HERO STRIP ── --}}
            <div class="hero-strip mb-6">
                <div class="relative z-10 px-6 py-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="hero-title text-4xl font-bold mb-1">Mikro Skill Peserta Magang</h1>
                        <p class="text-blue-100">Monitoring mikro skill peserta magang</p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('admin.microskill.create') }}" class="btn-add">
                            <i class="fas fa-plus text-sm"></i>
                            Tambah Mikroskill
                        </a>
                    </div>
                </div>
            </div>

            {{-- ── FILTER PANEL ── --}}
            <div class="panel mb-6">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-filter mr-3"></i>Filter Data
                    </h2>
                </div>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-600 mb-2">Cari judul</label>
                        <input type="text" name="q" value="{{ request('q') }}" class="input-main"
                            placeholder="Ketik untuk mencari..." />
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="action-mobile flex-1 btn-filter">
                            <i class="fas fa-filter"></i>Filter
                        </button>
                        @if(request()->anyFilled(['q']))
                            <a href="{{ route('admin.microskill.index') }}" class="btn-reset">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- ── TABLE PANEL ── --}}
            <div class="panel overflow-hidden">
                <div class="bg-blue-600 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-star mr-3"></i>Data Mikro Skill
                    </h2>
                    <span class="count-badge">{{ $microskills->total() }} mikro skill</span>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto" style="max-height:560px;overflow-y:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Link</th>
                                    <th class="text-center" style="border-radius:0;">Total Pengerjaan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($microskills as $m)
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="skill-icon">
                                                    <i class="fas fa-book-open text-white" style="font-size:12px;"></i>
                                                </div>
                                                <span class="font-semibold text-gray-800">{{ $m->judul_micro }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ $m->link_micro }}" target="_blank" class="link-chip">
                                                <i class="fas fa-external-link-alt" style="font-size:10px;flex-shrink:0;"></i>
                                                {{ $m->link_micro }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="count-pill">{{ $m->total ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <div class="action-group">
                                                <a href="{{ route('admin.microskill.show', ['id' => $m->id]) }}"
                                                    class="action-btn action-view">
                                                    <i class="fas fa-eye" style="font-size:11px;"></i>
                                                </a>
                                                {{-- Tombol hapus — membuka modal Alpine.js --}}
                                                <button type="button"
                                                    class="action-btn action-delete"
                                                    onclick="window.dispatchEvent(new CustomEvent('open-delete-modal-microskill', { detail: { url: '{{ route('admin.microskill.destroy', ['id' => $m->id]) }}' } }))">
                                                    <i class="fas fa-trash" style="font-size:11px;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox"></i>
                                                <p>Tidak ada data mikro skill.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">{{ $microskills->links() }}</div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── DELETE CONFIRMATION MODAL (Alpine.js — sama persis dengan index mentor) ── --}}
    <div x-data="{ showDeleteModal: false, deleteUrl: '' }"
         @open-delete-modal-microskill.window="showDeleteModal = true; deleteUrl = $event.detail.url">

        <div x-show="showDeleteModal"
             style="display: none;"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm"
             x-transition.opacity>

            <div @click.away="showDeleteModal = false"
                 class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all"
                 x-show="showDeleteModal"
                 x-transition.scale.origin.bottom>

                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>

                <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Konfirmasi Hapus</h3>
                <p class="text-center text-gray-600 mb-6">
                    Apakah Anda yakin ingin menghapus mikro skill ini? Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="flex justify-center gap-3">
                    <button type="button"
                            @click="showDeleteModal = false"
                            class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        Batal
                    </button>
                    <form :action="deleteUrl" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors flex items-center gap-2">
                            <i class="fas fa-trash"></i> Ya, Hapus
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection