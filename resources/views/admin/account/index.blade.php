@extends('layouts.app')

@section('title', 'Kelola Akun Admin - Sistem Manajemen Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin-account-index.css') }}?v={{ @filemtime(public_path('css/pages/admin-account-index.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">

            {{-- ── HERO STRIP ── --}}
            <div class="hero-strip mb-6">
                <div class="relative z-10 px-6 py-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="hero-title text-4xl font-bold mb-1">Kelola Akun Admin</h1>
                        <p class="text-blue-100">Atur super admin dan admin lain sesuai akses yang dibutuhkan.</p>
                    </div>
                    <div class="flex-shrink-0 flex flex-col items-start sm:items-end gap-3">
                        <div>
                            <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Total Admin</p>

                        <!-- Delete Confirmation Modal -->
                        <div x-data="{ showDeleteModal: false, deleteUrl: '' }" @open-delete-modal-account.window="showDeleteModal = true; deleteUrl = $event.detail.url">
                            <!-- Modal Backdrop -->
                            <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm" x-transition.opacity>
                                <!-- Modal Content -->
                                <div @click.away="showDeleteModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all" x-show="showDeleteModal" x-transition.scale.origin.bottom>
                                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Konfirmasi Hapus</h3>
                                    <p class="text-center text-gray-600 mb-6">Apakah Anda yakin ingin menghapus akun admin ini? Tindakan ini tidak dapat dibatalkan.</p>
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
                        </div>                            <p class="font-extrabold text-white text-center text-4xl leading-none">{{ $accounts->total() }}</p>
                        </div>
                        <a href="{{ route('admin.accounts.create') }}"
                            class="inline-flex items-center gap-2 bg-white text-blue-700 font-bold text-sm px-5 py-2.5 rounded-xl shadow hover:shadow-md hover:bg-blue-50 transition-all duration-200">
                            <i class="fas fa-plus text-xs"></i>
                            Tambah Admin
                        </a>
                    </div>
                </div>
            </div>

            {{-- ── SEARCH FILTER PANEL ── --}}
            <div class="panel mb-6">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-search mr-3"></i>Cari Akun Admin
                    </h2>
                </div>
                <form method="GET" action="{{ route('admin.accounts.index') }}"
                    class="p-6 flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 sm:max-w-md relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama atau email..."
                            class="input-main pl-9">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="action-mobile bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        @if (request()->filled('search'))
                            <a href="{{ route('admin.accounts.index') }}"
                                class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- ── ACCOUNTS TABLE PANEL ── --}}
            <div class="panel overflow-hidden">
                <div class="bg-blue-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-user-shield mr-3"></i>Daftar Akun Admin
                    </h2>
                </div>
                <div class="p-6">
                    <div class="table-responsive max-h-[560px]">
                        <table class="min-w-full table-min-w divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-blue-50">
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tl-lg">
                                        Nama</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Email</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Akses</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tr-lg">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($accounts as $account)
                                    @php
                                        $roleName = $account->getRoleNames()->first() ?? ($account->role ?? '-');
                                        $roleBadgeMap = [
                                            'super_admin' => [
                                                'label'   => 'Super Admin',
                                                'classes' => 'background:#fef2f2;color:#b91c1c;',
                                                'dot'     => '#ef4444',
                                            ],
                                            'admin_full' => [
                                                'label'   => 'Admin Akses Penuh',
                                                'classes' => 'background:#eff6ff;color:#1d4ed8;',
                                                'dot'     => '#3b82f6',
                                            ],
                                            'admin_user_manager' => [
                                                'label'   => 'Admin Pengelola Pengguna',
                                                'classes' => 'background:#f0fdf4;color:#15803d;',
                                                'dot'     => '#22c55e',
                                            ],
                                            'admin_data_manager' => [
                                                'label'   => 'Admin Pengelola Data',
                                                'classes' => 'background:#fefce8;color:#a16207;',
                                                'dot'     => '#f59e0b',
                                            ],
                                        ];
                                        $roleBadge = $roleBadgeMap[$roleName] ?? [
                                            'label'   => str_replace('_', ' ', ucwords($roleName, '_')),
                                            'classes' => 'background:#f1f5f9;color:#475569;',
                                            'dot'     => '#94a3b8',
                                        ];
                                    @endphp
                                    <tr class="hover:bg-blue-50 transition-colors duration-150">

                                        {{-- Nama --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#60a5fa,#818cf8);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                    <i class="fas fa-user text-white" style="font-size:13px;"></i>
                                                </div>
                                                <span class="text-sm font-medium text-gray-900">{{ $account->name }}</span>
                                            </div>
                                        </td>

                                        {{-- Email --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-600">{{ $account->email }}</div>
                                        </td>

                                        {{-- Role badge --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="role-badge" style="{{ $roleBadge['classes'] }}">
                                                <span style="width:6px;height:6px;border-radius:50%;background:{{ $roleBadge['dot'] }};display:inline-block;flex-shrink:0;"></span>
                                                {{ $roleBadge['label'] }}
                                            </span>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('admin.accounts.edit', $account) }}"
                                                    class="icon-btn icon-btn-edit" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                @if (!$account->isSuperAdmin())
                                                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-delete-modal-account', { detail: { url: '{{ route('admin.accounts.destroy', $account) }}' } }))"
                                                        class="icon-btn icon-btn-delete" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @else
                                                    <span class="text-xs text-gray-300 font-medium px-2">Terlindungi</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                                                <p class="text-sm">Belum ada akun admin.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">{{ $accounts->links() }}</div>
                </div>
            </div>

        </div>
    </div>

    
                        <!-- Delete Confirmation Modal -->
                        <div x-data="{ showDeleteModal: false, deleteUrl: '' }" @open-delete-modal-account.window="showDeleteModal = true; deleteUrl = $event.detail.url">
                            <!-- Modal Backdrop -->
                            <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm" x-transition.opacity>
                                <!-- Modal Content -->
                                <div @click.away="showDeleteModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all" x-show="showDeleteModal" x-transition.scale.origin.bottom>
                                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Konfirmasi Hapus</h3>
                                    <p class="text-center text-gray-600 mb-6">Apakah Anda yakin ingin menghapus akun admin ini? Tindakan ini tidak dapat dibatalkan.</p>
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