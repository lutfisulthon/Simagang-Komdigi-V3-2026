@extends('layouts.app')

@section('title', 'Tambah Mentor - Sistem Manajemen Magang')

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

        .panel {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(30, 58, 138, 0.06), 0 4px 20px rgba(30, 58, 138, 0.06);
            overflow: hidden;
        }

        .input-main {
            width: 100%;
            margin-top: 0.25rem;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.75rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            transition: all 0.2s ease;
        }

        .input-main:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        @media (max-width: 640px) {
            .hero-title {
                font-size: 1.55rem;
            }

            .form-section,
            .form-actions {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .action-mobile {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dash-bg py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="hero-strip mb-6">
                <div class="relative z-10 px-6 py-7">
                    <h1 class="hero-title text-3xl font-bold">Tambah Mentor</h1>
                    <p class="text-sm text-blue-100 mt-1">Tambahkan data mentor baru</p>
                </div>
            </div>

            <div class="panel">
                <form method="POST" action="{{ route('admin.mentor.store') }}" class="space-y-6">
                    @csrf

                    <div class="form-section p-8 border-b">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-blue-900">Informasi Pribadi</h2>
                                <p class="text-sm text-gray-600">Data diri dan kontak mentor</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-blue-900 mb-2">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    class="input-main">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-blue-900 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    class="input-main">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="position" class="block text-sm font-medium text-blue-900 mb-2">
                                    Jabatan
                                </label>
                                <input type="text" name="position" id="position" value="{{ old('position') }}"
                                    class="input-main">
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-blue-900 mb-2">
                                    Telepon
                                </label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                    class="input-main">
                            </div>
                        </div>
                    </div>

                    <!-- Penempatan -->
                    <div class="form-section p-8 border-b">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-users text-blue-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-blue-900">Penempatan</h2>
                                <p class="text-sm text-gray-600">Tim yang ditugaskan</p>
                            </div>
                        </div>

                        <div>

                            {{-- Tim Dropdown --}}
                            <div>
                                <label class="text-sm font-medium text-blue-900 mb-2">
                                    Pilih Tim <span class="text-red-500">*</span>
                                </label>

                                <select name="team_id" id="mentorSelect" required class="input-main">

                                    <option value="">Pilih Tim</option>

                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}" data-team="{{ $team->name }}"
                                            {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                            {{ $team->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('team_id')
                                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>


                    <div class="form-section p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-shield-alt text-blue-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-blue-900">Keamanan & Status</h2>
                                <p class="text-sm text-gray-600">Pengaturan password dan status aktif</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="password" class="block text-sm font-medium text-blue-900 mb-2">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" name="password" id="password" value="password123"
                                    class="input-main">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-blue-900 mb-3">
                                    Status Aktif
                                </label>
                                <div
                                    class="flex items-center mt-1 w-full bg-blue-50 rounded-xl px-4 py-3.5 border border-gray-300">
                                    <label for="is_active" class="flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_active" id="is_active" value="1"
                                            class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                            checked>
                                        <span class="ml-3 text-sm font-semibold text-gray-700">Tandai sebagai aktif</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="form-actions px-8 py-6 bg-gray-50 flex flex-col-reverse md:flex-row md:items-center md:justify-between gap-4">
                        <a href="{{ route('admin.mentor.index') }}"
                            class="action-mobile inline-flex justify-center md:justify-start items-center gap-2 text-blue-600 hover:text-blue-800 font-semibold transition">
                            <i class="fas fa-arrow-left"></i>
                            Kembali
                        </a>

                        <button type="submit"
                            class="action-mobile inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold px-8 py-3 rounded-xl shadow-lg transition transform hover:scale-105">
                            <i class="fas fa-save"></i>
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
