@extends('layouts.app')

@section('title', 'Monitoring Industri - Sistem Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin-monitoring-industri-index.css') }}?v={{ @filemtime(public_path('css/pages/admin-monitoring-industri-index.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">

            {{-- ── HERO STRIP ── --}}
            <div class="hero-strip mb-6">
                <div class="relative z-10 px-6 py-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="hero-title text-4xl font-bold mb-1">Monitoring Industri</h1>
                        <p class="text-blue-100">Ringkasan data peserta magang per industri mitra</p>
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
                <form method="GET" action="{{ route('admin.monitoring.industri.index') }}"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-2">Cari Nama Industri</label>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            class="input-main"
                            placeholder="Ketik nama industri..."
                        />
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 btn-filter">
                            <i class="fas fa-filter"></i>Filter
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.monitoring.industri.index') }}" class="btn-reset">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- ── TABEL INDUSTRI ── --}}
            <div class="panel overflow-hidden">
                <div class="panel-header-blue flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-building mr-3"></i>Daftar Industri Mitra
                    </h2>
                    <span class="count-badge">{{ $totalIndustri }} industri</span>
                </div>
                <div class="p-6">
                    <div class="table-responsive" style="overflow-x:auto;overflow-y:auto;max-height:600px;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="text-align:center" >Nama Industri</th>
                                    <th style="text-align:center">Alamat</th>
                                    <th style="text-align:center;">Total</th>
                                    <th style="text-align:center;">Aktif</th>
                                    <th style="text-align:center;">Alumni</th>
                                    <th style="text-align:center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($industris as $industri)
                                    <tr>
                                        <td>
                                            <div class="icon-cell">
                                                <div class="logo-box">
                                                    @if($industri->logo_industri)
                                                        <img
                                                            src="{{ asset('storage/' . $industri->logo_industri) }}"
                                                            alt="{{ $industri->nama_industri }}"
                                                            onerror="this.parentElement.innerHTML='<div class=\'logo-box-fallback\'><i class=\'fas fa-building\'></i></div>'">
                                                    @else
                                                        <div class="logo-box-fallback">
                                                            <i class="fas fa-building"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <span class="font-semibold text-gray-900 text-sm">
                                                    {{ $industri->nama_industri }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-gray-500"
                                            style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            {{ $industri->alamat_industri ?? '—' }}
                                        </td>
                                        <td style="text-align:center;">
                                            <span class="pill pill-blue">{{ $industri->total_intern }}</span>
                                        </td>
                                        <td style="text-align:center;">
                                            <span class="pill pill-green">{{ $industri->active_intern }}</span>
                                        </td>
                                        <td style="text-align:center;">
                                            <span class="pill pill-orange">{{ $industri->alumni_intern }}</span>
                                        </td>
                                        <td>
                                            <div class="action-group">
                                                <a href="{{ route('admin.monitoring.industri.show', $industri) }}"
                                                    class="action-btn-sm action-view"
                                                    title="Lihat detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <i class="fas fa-building"></i>
                                                <p>Tidak ada data industri ditemukan.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection