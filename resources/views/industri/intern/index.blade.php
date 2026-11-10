@extends('layouts.app')

@section('title', 'Kelola Peserta Magang - Sistem Magang')

@push('styles')
    <style>

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .dash-bg {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8eeff 0%, #f0f4ff 40%, #e4ecff 100%);
        }

        /* ── Hero Strip (sama persis dengan logbook) ── */
        .hero-strip {
            background: linear-gradient(100deg, #1e3a8a 0%, #3b4fd8 50%, #4f46e5 100%);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(20, 40, 120, 0.16);
            color: #fff;
        }

        .hero-strip::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
        }

        .hero-strip::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: 30%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
        }

        /* ── Panel (sama persis dengan logbook) ── */
        .panel {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(30, 58, 138, 0.06), 0 4px 20px rgba(30, 58, 138, 0.06);
            overflow: hidden;
        }

        /* ── Input (sama persis dengan logbook) ── */
        .input-main {
            width: 100%;
            padding: 0.6rem 0.9rem;
            border: 1px solid #d1d5db;
            border-radius: 0.6rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            transition: all .15s ease;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            color: #1f2937;
            background: #fff;
        }

        .input-main:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        select.input-main {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 34px;
        }

        /* ── Panel header bar (sama seperti logbook) ── */
        .panel-header-blue {
            background: linear-gradient(to right, #2563eb, #4f46e5);
            padding: 1rem 1.5rem;
        }

        .panel-header-gray {
            background: linear-gradient(to right, #4b5563, #6b7280);
            padding: 1rem 1.5rem;
        }

        /* ── Tombol filter (serupa dengan logbook) ── */
        .btn-filter {
            background: linear-gradient(to right, #2563eb, #4338ca);
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            padding: 0.5rem 1.25rem;
            border-radius: 0.6rem;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .15s ease;
            white-space: nowrap;
        }

        .btn-filter:hover {
            box-shadow: 0 4px 14px rgba(59, 79, 216, 0.3);
            transform: translateY(-1px);
        }

        .btn-reset {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #2563eb;
            font-weight: 700;
            font-size: 13px;
            padding: 0.5rem 0.75rem;
            border-radius: 0.6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all .15s ease;
        }

        .btn-reset:hover {
            background: #dbeafe;
            color: #1d4ed8;
            text-decoration: none;
        }

        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.55rem 1.25rem;
            border-radius: 0.6rem;
            background: rgba(255, 255, 255, 0.15);
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            transition: all .2s ease;
            white-space: nowrap;
        }

        .btn-add:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.4);
            color: #fff;
            text-decoration: none;
            transform: translateY(-1px);
        }

        /* ── Tabel ── */
        .data-table {
            min-width: 900px;
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead tr {
            background: #eff6ff;
        }

        .data-table th {
            padding: 0.75rem 1rem;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: #1e3a8a;
            text-align: left;
            white-space: nowrap;
        }

        .data-table th:first-child { border-radius: 10px 0 0 10px; }
        .data-table th:last-child  { border-radius: 0 10px 10px 0; text-align: center; }

        .data-table td {
            padding: 0.85rem 1rem;
            font-size: 13px;
            color: #334155;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .data-table tbody tr { transition: background 0.15s; }
        .data-table tbody tr:hover { background: #eff6ff; }
        .data-table tbody tr:last-child td { border-bottom: none; }

        /* Alumni table */
        .data-table.alumni thead tr { background: #f8fafc; }
        .data-table.alumni th { color: #6b7280; }
        .data-table.alumni tbody tr:hover { background: #f9fafb; }

        /* ── Avatar cell ── */
        .avatar-cell { display: flex; align-items: center; gap: 10px; }

        .avatar-sm {
            width: 38px; height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #bfdbfe;
            flex-shrink: 0;
        }

        .avatar-sm-placeholder {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: #dbeafe;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
            font-weight: 700;
            color: #2563eb;
        }

        .avatar-sm-placeholder.gray {
            background: #f1f5f9;
            color: #94a3b8;
        }

        /* ── Pills ── */
        .pill {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .pill-blue  { background: #dbeafe; color: #1e40af; }
        .pill-green { background: #dcfce7; color: #15803d; }
        .pill-red   { background: #fee2e2; color: #b91c1c; }
        .pill-gray  { background: #f1f5f9; color: #64748b; }

        /* ── Action buttons ── */
        .action-group { display: flex; align-items: center; justify-content: center; gap: 6px; }

        .action-btn-sm {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px;
            border: none;
            cursor: pointer;
            transition: all .15s ease;
            text-decoration: none;
        }

        .action-btn-sm:hover { transform: translateY(-1px); text-decoration: none; }
        .action-view   { background: #dcfce7; color: #15803d; }
        .action-view:hover  { background: #bbf7d0; }
        .action-edit   { background: #dbeafe; color: #1e40af; }
        .action-edit:hover  { background: #bfdbfe; }
        .action-delete { background: #fee2e2; color: #b91c1c; }
        .action-delete:hover { background: #fecaca; }

        /* ── Empty state ── */
        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: #94a3b8;
        }

        .empty-state i { font-size: 2.5rem; display: block; margin-bottom: 10px; color: #e2e8f0; }
        .empty-state p { font-size: 13px; margin: 0; }

        /* ── Count badge ── */
        .count-badge {
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 999px;
            background: #dbeafe;
            color: #1e40af;
        }

        .count-badge.gray {
            background: #f1f5f9;
            color: #64748b;
        }

        @media (max-width: 768px) {
            .hero-title { font-size: 1.5rem; }
            .action-mobile { width: 100%; justify-content: center; }
            .table-responsive { overflow: auto; }
        }
        }
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            backdrop-filter: blur(3px);
        }

        .modal-box {
            background: #fff;
            border-radius: 20px;
            padding: 28px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(30, 58, 138, 0.18);
            position: relative;
        }
    </style>
@endpush

@section('content')
    <div class="dash-bg py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">

            {{-- ── HERO STRIP ── --}}
            <div class="hero-strip mb-6">
                <div class="relative z-10 px-6 py-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="hero-title text-4xl font-bold mb-1">Kelola Peserta Magang</h1>
                        <p class="text-blue-100">Manajemen data peserta magang aktif dan alumni</p>
                    </div>
                    <div class="flex flex-col sm:items-end gap-3 flex-shrink-0">
                        <a href="{{ route('industri.intern.create') }}" class="btn-add">
                            <i class="fas fa-plus text-sm"></i>
                            Tambah Peserta
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
                <form method="GET" action="{{ route('industri.intern.index') }}"
                      class="grid grid-cols-1 md:grid-cols-2 gap-4 p-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">Cari Nama</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="input-main" placeholder="Ketik nama peserta..." />
                    </div>
                    {{-- <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">Tim</label>
                        <select name="team_id" class="input-main">
                            <option value="">Semua Tim</option>
                            @foreach($teams as $teamOption)
                                <option value="{{ $teamOption->id }}"
                                    {{ request('team_id') == $teamOption->id ? 'selected' : '' }}>
                                    {{ $teamOption->name }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}
                    {{-- <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">Mentor</label>
                        <select name="mentor_id" class="input-main">
                            <option value="">Semua Mentor</option>
                            @foreach($mentors as $mentor)
                                <option value="{{ $mentor->id }}"
                                    {{ request('mentor_id') == $mentor->id ? 'selected' : '' }}>
                                    {{ $mentor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}
                    <div class="flex items-end gap-2">
                        <button type="submit" class="action-mobile flex-1 btn-filter">
                            <i class="fas fa-filter"></i>Filter
                        </button>
                        @if(request()->anyFilled(['search']))
                            <a href="{{ route('industri.intern.index') }}" class="btn-reset">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- ── TABEL PESERTA AKTIF ── --}}
            <div class="panel overflow-hidden">
                <div class="panel-header-blue flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-users mr-3"></i>Peserta Magang Aktif
                    </h2>
                    <span class="count-badge">{{ $activeInterns->total() }} peserta</span>
                </div>
                <div class="p-6">
                    <div class="table-responsive" style="overflow-x:auto;overflow-y:auto;max-height:520px;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Institusi</th>
                                    {{-- <th>Tim</th>
                                    <th>Mentor</th> --}}
                                    <th>Status</th>
                                    <th style="text-align:center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($activeInterns as $intern)
                                    <tr>
                                        <td>
                                            <div class="avatar-cell">
                                                @if($intern->photo_path)
                                                    <img src="{{ url('storage/'.$intern->photo_path) }}"
                                                        alt="{{ $intern->name }}" class="avatar-sm" loading="lazy" decoding="async">
                                                @else
                                                    <div class="avatar-sm-placeholder">
                                                        {{ strtoupper(substr($intern->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <span class="font-semibold text-gray-900 text-sm">{{ $intern->name }}</span>
                                            </div>
                                        </td>
                                        <td class="text-gray-500" style="font-size:12px;">{{ $intern->user->email }}</td>
                                        <td class="text-gray-600" style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            {{ $intern->institution }}
                                        </td>
                                        {{-- <td>
                                            @if($intern->team)
                                                <span class="pill pill-blue">{{ $intern->team->name }}</span>
                                            @else
                                                <span class="pill pill-gray">—</span>
                                            @endif
                                        </td> --}}
                                        {{-- <td class="text-gray-600">{{ $intern->mentor?->name ?? '—' }}</td> --}}
                                        <td>
                                            @if($intern->is_active)
                                                <span class="pill pill-green">
                                                    <i class="fas fa-circle" style="font-size:6px;margin-right:4px;"></i>Aktif
                                                </span>
                                            @else
                                                <span class="pill pill-red">
                                                    <i class="fas fa-circle" style="font-size:6px;margin-right:4px;"></i>Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-group">
                                                <a href="{{ route('industri.intern.show', $intern) }}"
                                                    class="action-btn-sm action-view" title="Lihat detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('industri.intern.edit', $intern) }}"
                                                   class="action-btn-sm action-edit" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-delete-modal-intern', { detail: { url: '{{ route('industri.intern.destroy', $intern) }}', name: '{{ addslashes($intern->name) }}' } }))" class="action-btn-sm action-delete" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox"></i>
                                                <p>Belum ada peserta magang aktif.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($activeInterns->hasPages())
                        <div class="mt-6">{{ $activeInterns->links() }}</div>
                    @endif
                </div>
            </div>

            {{-- ── TABEL ALUMNI ── --}}
            <div class="panel overflow-hidden">
                <div class="panel-header-gray flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-user-graduate mr-3"></i>Data Alumni
                    </h2>
                    <span class="count-badge gray">{{ $alumniInterns->total() }} alumni</span>
                </div>
                <div class="p-6">
                    <div class="table-responsive" style="overflow-x:auto;overflow-y:auto;max-height:420px;">
                        <table class="data-table alumni">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Institusi</th>
                                    {{-- <th>Tim</th>
                                    <th>Mentor</th> --}}
                                    <th style="text-align:center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($alumniInterns as $intern)
                                    <tr>
                                        <td>
                                            <div class="avatar-cell">
                                                @if($intern->photo_path)
                                                    <img src="{{ url('storage/'.$intern->photo_path) }}"
                                                        alt="{{ $intern->name }}"
                                                        class="avatar-sm" style="border-color:#e2e8f0;" loading="lazy" decoding="async">
                                                @else
                                                    <div class="avatar-sm-placeholder gray">
                                                        {{ strtoupper(substr($intern->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <span class="font-semibold text-gray-700 text-sm">{{ $intern->name }}</span>
                                            </div>
                                        </td>
                                        <td class="text-gray-400" style="font-size:12px;">{{ $intern->user->email }}</td>
                                        <td class="text-gray-500" style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            {{ $intern->institution }}
                                        </td>
                                        {{-- <td>
                                            @if($intern->team)
                                                <span class="pill pill-gray">{{ $intern->team->name }}</span>
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td class="text-gray-500">{{ $intern->mentor?->name ?? '—' }}</td> --}}
                                        <td>
                                            <div class="action-group">
                                                <a href="{{ route('industri.intern.show', $intern) }}"
                                                    class="action-btn-sm action-view" title="Lihat detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('industri.intern.edit', $intern) }}"
                                                    class="action-btn-sm action-edit" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-delete-modal-intern', { detail: { url: '{{ route('industri.intern.destroy', $intern) }}', name: '{{ addslashes($intern->name) }}' } }))" class="action-btn-sm action-delete" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox"></i>
                                                <p>Belum ada data alumni.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($alumniInterns->hasPages())
                        <div class="mt-6">{{ $alumniInterns->links() }}</div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-data="{ showDeleteModal: false, deleteUrl: '', internName: '' }" @open-delete-modal-intern.window="showDeleteModal = true; deleteUrl = $event.detail.url; internName = $event.detail.name">
        <!-- Modal Backdrop -->
        <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm" x-transition.opacity>
            <!-- Modal Content -->
            <div @click.away="showDeleteModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all" x-show="showDeleteModal" x-transition.scale.origin.bottom>
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Konfirmasi Hapus</h3>
                <p class="text-center text-gray-600 mb-6">Apakah Anda yakin ingin menghapus <strong x-text="internName"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
                <div class="flex justify-center gap-3">
                    <button type="button" @click="showDeleteModal = false" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                            Batal
                    </button>                    
                        <form :action="deleteUrl" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors flex items-center gap-2">
                                <i class="fas fa-trash"></i> Ya, Hapus
                            </button>
                        </form>                                                            
                </div>
            </div>
        </div>
    </div>
@endsection