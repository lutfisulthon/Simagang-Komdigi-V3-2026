@extends('layouts.app')

@section('title', 'Tambah Mikro Skill - Sistem Manajemen Magang')

@push('styles')
    <style>

        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        .mono { font-family: 'DM Mono', monospace; }
        body { background: #f0f4ff; }

        .dash-bg {
            background: linear-gradient(135deg, #e8eeff 0%, #f0f4ff 40%, #e4ecff 100%);
            min-height: 100vh;
        }

        .panel {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(30, 58, 138, 0.06), 0 4px 20px rgba(30, 58, 138, 0.06);
            overflow: hidden;
        }

        .input-main {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid #e0e7ff;
            border-radius: 0.75rem;
            background: #f8faff;
            font-size: 14px;
            color: #1e3a8a;
            transition: all .15s ease;
        }
        .input-main::placeholder { color: #94a3b8; }
        .input-main:focus {
            outline: none;
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(110deg, #1e3a8a, #3b4fd8);
            color: #fff;
            border: none;
            border-radius: 0.75rem;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-primary:hover {
            box-shadow: 0 4px 12px rgba(59, 79, 216, 0.3);
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 0.75rem 1.5rem;
            border: 1.5px solid #e0e7ff;
            background: #f8faff;
            color: #6b7280;
            border-radius: 0.75rem;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-secondary:hover {
            border-color: #c7d2fe;
            background: #eff2ff;
            color: #3730a3;
            text-decoration: none;
        }
    </style>
@endpush

@section('content')
<div class="dash-bg py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">

        <div class="panel">
            <div class="bg-blue-600 px-6 py-4">
                <h2 class="text-white text-lg font-bold flex items-center gap-3">
                    <i class="fas fa-plus"></i>
                    Tambah Mikro Skill Baru
                </h2>
            </div>

            <div class="p-6">
                <form method="POST" action="{{ route('admin.microskill.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="judul_micro" class="block text-sm font-semibold text-gray-700 mb-2">Judul Mikro Skill</label>
                        <input type="text" id="judul_micro" name="judul_micro" value="{{ old('judul_micro') }}" 
                               class="input-main" placeholder="Masukkan judul mikro skill" required>
                        @error('judul_micro')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="link_micro" class="block text-sm font-semibold text-gray-700 mb-2">Link Mikro Skill</label>
                        <input type="url" id="link_micro" name="link_micro" value="{{ old('link_micro') }}" 
                               class="input-main" placeholder="https://example.com" required>
                        @error('link_micro')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save text-xs"></i>
                            <span>Simpan</span>
                        </button>
                        <a href="{{ route('admin.microskill.index') }}" class="btn-secondary">
                            <i class="fas fa-arrow-left text-xs"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection