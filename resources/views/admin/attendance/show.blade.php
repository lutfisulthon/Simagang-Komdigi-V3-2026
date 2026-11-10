@extends('layouts.app')

@section('title', 'Detail Absensi - Sistem Magang')

@push('styles')
    <style>

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .dash-bg {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8eeff 0%, #f0f4ff 40%, #e4ecff 100%);
        }

        .hero-strip {
            background: linear-gradient(100deg, #1e3a8a 0%, #3b4fd8 50%, #4f46e5 100%);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(20, 40, 120, 0.16);
            color: #fff;
        }

        .panel {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(30, 58, 138, 0.06), 0 4px 20px rgba(30, 58, 138, 0.06);
            overflow: hidden;
        }

        .input-main {
            width: 100%;
            padding: 0.6rem 0.9rem;
            border: 1px solid #d1d5db;
            border-radius: 0.6rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            transition: all .15s ease;
        }

        @media (max-width:768px) {
            .hero-title {
                font-size: 1.6rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dash-bg py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">

            <div class="hero-strip mb-6">
                <div class="relative z-10 p-6">
                    <h1 class="hero-title text-3xl font-bold">Detail Absensi</h1>
                    <p class="text-blue-100 text-sm">Informasi kehadiran anak magang</p>
                </div>
            </div>

            <div class="panel p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Detail Absensi</h2>
                        <p class="text-sm text-gray-500 mt-1">Informasi lengkap check in / check out</p>
                    </div>
                    <a href="{{ route('admin.attendance.index') }}"
                        class="action-mobile inline-flex items-center gap-2 bg-white/90 text-blue-700 font-semibold px-4 py-2 rounded-lg shadow">
                        <i class="fas fa-arrow-left"></i> Kembali</a>
                </div>

                <div class="bg-white"> <!-- content block -->
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs text-gray-500">Anak Magang</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $attendance->intern->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Tanggal</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $attendance->date->format('d F Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Status</p>
                                <span
                                    class="inline-block mt-1 px-3 py-1 rounded-full text-sm font-semibold @if ($attendance->status == 'hadir') bg-green-100 text-green-700 @elseif($attendance->status == 'izin') bg-yellow-100 text-yellow-700 @else bg-red-100 text-red-700 @endif">{{ ucfirst($attendance->status) }}</span>
                            </div>
                        </div>

                        @if ($attendance->status == 'hadir')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-xs text-gray-500">Check In</p>
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Check Out</p>
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '-' }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                @if ($attendance->photo_path)
                                    @php $photoUrl = route('admin.attendance.photo', ['filename' => basename($attendance->photo_path)]); @endphp
                                    @can('view', $attendance)
                                        <div>
                                            <p class="text-xs text-gray-500 mb-2">Foto Check In</p>
                                            <img src="{{ $photoUrl }}" class="rounded-lg border cursor-pointer"
                                                onclick="window.open('{{ $photoUrl }}','_blank')" loading="lazy" decoding="async">
                                        </div>
                                    @else
                                        <div>
                                            <p class="text-xs text-gray-500 mb-2">Foto Check In</p>
                                            <p class="text-gray-400">Tidak ada akses</p>
                                        </div>
                                    @endcan
                                @endif

                                @if ($attendance->photo_checkout)
                                    @php $photoCheckoutUrl = route('admin.attendance.photo', ['filename' => basename($attendance->photo_checkout)]); @endphp
                                    @can('view', $attendance)
                                        <div>
                                            <p class="text-xs text-gray-500 mb-2">Foto Check Out</p>
                                            <img src="{{ $photoCheckoutUrl }}" class="rounded-lg border cursor-pointer"
                                                onclick="window.open('{{ $photoCheckoutUrl }}','_blank')" loading="lazy" decoding="async">
                                        </div>
                                    @else
                                        <div>
                                            <p class="text-xs text-gray-500 mb-2">Foto Check Out</p>
                                            <p class="text-gray-400">Tidak ada akses</p>
                                        </div>
                                    @endcan
                                @endif
                            </div>
                        @else
                            <div>
                                <p class="text-xs text-gray-500">Keterangan</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $attendance->note ?? '-' }}</p>
                            </div>

                            @if ($attendance->document_path)
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Dokumen Pendukung</p>
                                    <a href="{{ route('admin.attendance.document', ['filename' => basename($attendance->document_path)]) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-semibold">
                                        <i class="fas fa-file-download"></i>
                                        Download Dokumen
                                    </a>
                                </div>
                            @endif

                            <form method="POST"
                                action="{{ route('admin.attendance.update-document-status', $attendance) }}"
                                class="border-t pt-6 space-y-4">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Dokumen</label>
                                    <select name="document_status" required class="input-main">
                                        <option value="pending"
                                            {{ $attendance->document_status == 'pending' ? 'selected' : '' }}>Pending
                                        </option>
                                        <option value="approved"
                                            {{ $attendance->document_status == 'approved' ? 'selected' : '' }}>Approved
                                        </option>
                                        <option value="rejected"
                                            {{ $attendance->document_status == 'rejected' ? 'selected' : '' }}>Rejected
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Admin</label>
                                    <textarea name="admin_note" rows="4" class="input-main">{{ old('admin_note', $attendance->admin_note) }}</textarea>
                                </div>
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg">Update
                                    Status</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
