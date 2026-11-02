@extends('layouts.app')

@section('title', 'Sharing Session')

@section('content')
<div class="min-h-screen bg-[#F4F7FF] px-6 py-8">
    <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-800 to-indigo-600 rounded-3xl p-8 text-white shadow-lg">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-blue-100 text-sm font-semibold uppercase tracking-wider mb-2">
                        Admin Panel
                    </p>

                    <h1 class="text-3xl font-bold">
                        Kelola Sharing Session
                    </h1>

                    <p class="text-blue-100 mt-2">
                        Atur jadwal, materi, form evaluasi, dan dokumentasi sharing session.
                    </p>
                </div>

                <a href="{{ route('admin.sharing-session.create') }}"
                   class="inline-flex items-center justify-center gap-2 bg-white text-blue-700 px-5 py-3 rounded-2xl font-semibold shadow hover:bg-blue-50 transition">
                    <i class="fas fa-plus"></i>
                    Tambah Jadwal
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-blue-50">
            <p class="text-sm text-gray-400 font-semibold uppercase">
                Total Jadwal
            </p>
            <h2 class="text-3xl font-bold text-gray-800 mt-2">
                {{ $totalJadwal }}
            </h2>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-green-50">
            <p class="text-sm text-gray-400 font-semibold uppercase">
                Hari Ini
            </p>
            <h2 class="text-3xl font-bold text-green-600 mt-2">
                {{ $hariIni }}
            </h2>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-yellow-50">
            <p class="text-sm text-gray-400 font-semibold uppercase">
                Akan Datang
            </p>
            <h2 class="text-3xl font-bold text-yellow-600 mt-2">
                {{ $akanDatang }}
            </h2>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-red-50">
            <p class="text-sm text-gray-400 font-semibold uppercase">
                Selesai
            </p>
            <h2 class="text-3xl font-bold text-red-500 mt-2">
                {{ $selesai }}
            </h2>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    Daftar Sharing Session
                </h2>

                <p class="text-sm text-gray-400 mt-1">
                    Jadwal kegiatan sharing session di kantor.
                </p>
            </div>

            <form method="GET">
                <select name="filter"
                       
                        class="border border-gray-200 rounded-2xl px-4 py-2 text-sm font-medium focus:ring-2 focus:ring-blue-500">
                    <option value="semua" {{ ($filter ?? 'semua') == 'semua' ? 'selected' : '' }}>
                        Semua
                    </option>
                    <option value="hari-ini" {{ ($filter ?? 'semua') == 'hari-ini' ? 'selected' : '' }}>
                        Hari Ini
                    </option>
                    <option value="akan-datang" {{ ($filter ?? 'semua') == 'akan-datang' ? 'selected' : '' }}>
                        Akan Datang
                    </option>
                    <option value="selesai" {{ ($filter ?? 'semua') == 'selesai' ? 'selected' : '' }}>
                        Selesai
                    </option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase">Judul</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase">Jadwal</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase">Narasumber</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase">Moderator</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase">Lokasi</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase">Status Jadwal</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase">Status Materi</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase">Form Evaluasi</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase">Dokumentasi</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($sessions as $session)
                        @php
                            $materiLengkap = $session->material_status === 'lengkap';

                            $formSudahDibuka = $session->session_date
                                && $session->session_date->lte(\Carbon\Carbon::today());

                            $bolehBukaForm = $materiLengkap
                                && $formSudahDibuka
                                && !empty($session->evaluation_form_link);
                        @endphp

                        <tr class="hover:bg-blue-50/40 transition">
                            <td class="px-6 py-5">
                                <div class="font-bold text-gray-800">
                                    {{ $session->title ?? 'Materi Belum Diisi' }}
                                </div>

                                <div class="text-sm text-gray-400 mt-1">
                                    {{ $session->description ? \Illuminate\Support\Str::limit($session->description, 70) : 'Tidak ada deskripsi' }}
                                </div>
                            </td>

                            <td class="px-6 py-5 text-gray-600">
                                <div>
                                    <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                                    {{ $session->session_date->format('d M Y') }}
                                </div>

                                <div class="text-sm text-gray-400 mt-1">
                                    <i class="fas fa-clock mr-2"></i>
                                    {{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('H:i') : '-' }} WITA
                                </div>
                            </td>

                            <td class="px-6 py-5 text-gray-600">
                                <i class="fas fa-user-tie text-purple-500 mr-2"></i>
                                {{ $session->speaker_name }}
                            </td>

                            <td class="px-6 py-5 text-gray-600">
                                <i class="fas fa-user-check text-indigo-500 mr-2"></i>
                                {{ $session->moderator_name }}
                            </td>

                            <td class="px-6 py-5 text-gray-600">
                                <i class="fas fa-map-marker-alt text-orange-400 mr-2"></i>
                                {{ $session->location ?? '-' }}
                            </td>

                            <td class="px-6 py-5">
                                @if($session->session_date->isToday())
                                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-semibold">
                                        Hari Ini
                                    </span>
                                @elseif($session->session_date->lt(today()))
                                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-sm font-semibold">
                                        Selesai
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm font-semibold">
                                        Akan Datang
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-5">
                                @if($session->material_status === 'lengkap')
                                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                        Sudah Diisi
                                    </span>
                                @elseif($session->material_status === 'belum_lengkap')
                                    <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold">
                                        Materi Belum Lengkap
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-xs font-bold">
                                        Belum Diisi
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-5">
                                @if($bolehBukaForm)
                                    <a href="{{ $session->evaluation_form_link }}"
                                       target="_blank"
                                       class="inline-flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-100 px-4 py-2 rounded-xl text-sm font-bold transition">
                                        <i class="fas fa-external-link-alt text-xs"></i>
                                        Buka Form
                                    </a>
                                @elseif(!$materiLengkap)
                                    <span class="inline-flex items-center justify-center gap-2 bg-red-50 text-red-600 border border-red-100 px-4 py-2 rounded-xl text-sm font-bold">
                                        <i class="fas fa-file-circle-xmark text-xs"></i>
                                        Materi Belum Diisi
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-500 border border-gray-100 px-4 py-2 rounded-xl text-sm font-bold">
                                        <i class="fas fa-lock text-xs"></i>
                                        Belum Dibuka
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-5">
                                @if($session->documentation_photo_url)
                                    <button type="button"
                                            onclick="openDocumentationModal('{{ $session->id }}')"
                                            class="inline-flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-100 px-4 py-2 rounded-xl text-sm font-semibold transition">
                                        <i class="fas fa-image"></i>
                                        Lihat Foto
                                    </button>
                                @else
                                    <span class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-500 px-4 py-2 rounded-xl text-sm font-semibold">
                                        <i class="fas fa-image"></i>
                                        Belum Ada
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.sharing-session.edit', $session) }}"
                                       class="w-9 h-9 rounded-xl bg-yellow-100 text-yellow-700 flex items-center justify-center"
                                       title="Edit jadwal">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('admin.sharing-session.destroy', $session) }}" method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                onclick="return confirm('Hapus jadwal ini?')"
                                                class="w-9 h-9 rounded-xl bg-red-100 text-red-700 flex items-center justify-center"
                                                title="Hapus jadwal">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-16 text-center text-gray-500">
                                Belum ada jadwal sharing session.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
            <div class="px-6 py-5 border-t border-gray-100 bg-white">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</div>

@foreach($sessions as $session)
    @if($session->documentation_photo_url)
        <div id="documentation-modal-{{ $session->id }}"
             class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">

            <div class="bg-white rounded-3xl shadow-xl w-full max-w-3xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">
                            Dokumentasi Sharing Session
                        </h3>

                        <p class="text-sm text-gray-400 mt-1">
                            {{ $session->title ?? 'Materi Belum Diisi' }}
                        </p>
                    </div>

                    <button type="button"
                            onclick="closeDocumentationModal('{{ $session->id }}')"
                            class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    <img src="{{ $session->documentation_photo_url }}"
                         alt="Dokumentasi Sharing Session"
                         class="w-full max-h-[70vh] object-contain rounded-2xl border border-gray-200 bg-gray-50">
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3 px-6 py-4 border-t border-gray-100">
                    <a href="{{ $session->documentation_photo_url }}"
                       target="_blank"
                       class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-2xl font-semibold">
                        <i class="fas fa-external-link-alt"></i>
                        Buka Ukuran Penuh
                    </a>

                    <button type="button"
                            onclick="closeDocumentationModal('{{ $session->id }}')"
                            class="inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-600 px-5 py-3 rounded-2xl font-semibold">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
@endforeach

<script>
    function openDocumentationModal(id) {
        const modal = document.getElementById('documentation-modal-' + id);

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeDocumentationModal(id) {
        const modal = document.getElementById('documentation-modal-' + id);

        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('[id^="documentation-modal-"]').forEach(function (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        }
    });

    document.addEventListener('click', function (event) {
        document.querySelectorAll('[id^="documentation-modal-"]').forEach(function (modal) {
            if (event.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    });
</script>
@endsection