@extends('layouts.app')

@section('title', 'Papan Peringkat Mikro Skill - Sistem Magang')

@push('styles')
<style>
    .leader-hero { background: linear-gradient(135deg,#eef2ff,#f8fbff); padding:18px; border-radius:12px; }
    .hero-strip { background: linear-gradient(110deg, #0f2878 0%, #2d3ecb 55%, #4f46e5 100%); border-radius: 20px; position: relative; overflow: hidden; margin-bottom: 28px; }
    .hero-strip::before { content: ''; position: absolute; top: -80px; right: -60px; width: 260px; height: 260px; background: rgba(255,255,255,0.05); border-radius: 50%; pointer-events: none; }
    .hero-strip::after { content: ''; position: absolute; bottom: -100px; left: 25%; width: 320px; height: 320px; background: rgba(255,255,255,0.04); border-radius: 50%; pointer-events: none; }
    .panel { background:#fff;border-radius:20px;padding:24px;box-shadow:0 1px 3px rgba(30,58,138,0.06), 0 4px 20px rgba(30,58,138,0.06); }
    .cta-btn { display:inline-flex;align-items:center;gap:8px;padding:11px 24px;background:linear-gradient(110deg,#1e3a8a,#3b4fd8);color:#fff;border-radius:12px;font-size:14px;font-weight:600;text-decoration:none;transition:all .2s ease }
    .cta-btn:hover { box-shadow:0 6px 16px rgba(59,79,216,0.3); transform:translateY(-1px); color:#fff }
    .leader-card-header { background:#1e40af; padding:14px 18px; }
    .leader-card-header h2 { color:#fff; font-size:1rem; margin:0; }
    .lb-item { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-radius:14px; background:#fff; border:1px solid #eef2ff; transition:all .15s ease; }
    .lb-item:hover { box-shadow:0 8px 20px rgba(15,23,42,0.06); transform:translateY(-3px);} 
    .lb-rank { width:44px; height:44px; display:flex; align-items:center; justify-content:center; border-radius:999px; font-weight:700; color:#fff; flex-shrink:0; }
    .leader-avatar { width:48px; height:48px; border-radius:999px; overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .leader-avatar img { width:100%; height:100%; object-fit:cover; display:block; }
    @media (max-width:640px) {
        .leader-hero { padding:12px; }
        .lb-item { padding:12px; }
        .leader-avatar, .lb-rank { width:40px; height:40px; }
        .grid.md\:grid-cols-12 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
    }
    @media (max-width:420px) {
        .leader-avatar, .lb-rank { width:36px; height:36px; }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-blue-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header (logbook-style hero) -->
        <div class="hero-strip shadow-xl mb-6">
            <div class="relative z-10 px-6 py-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold leading-tight text-white mb-1">Papan Peringkat Mikro Skill</h1>
                        <p class="text-blue-200">Lihat peringkat peserta berdasarkan jumlah pelatihan yang telah diselesaikan.</p>
                    </div>
                    <a href="{{ route('intern.dashboard') }}" class="cta-btn">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="panel mb-6">
            <!-- Filter & Search -->
            <form method="GET" action="{{ route('intern.microskill.leaderboard') }}"
                class="grid grid-cols-1 md:grid-cols-12 gap-4">

                <!-- Search -->
                <div class="md:col-span-5">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                        Cari Nama
                    </label>
                    <input type="text" name="search" id="search"
                        value="{{ request('search') }}"
                        placeholder="Masukkan nama..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Status -->
                <div class="md:col-span-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Filter Status
                    </label>
                    <select name="status" id="status"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                            Aktif
                        </option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                            Tidak Aktif
                        </option>
                    </select>
                </div>

                <!-- Button -->
                <div class="md:col-span-3 flex items-end gap-2 flex-wrap">
                    <button type="submit"
                        class="flex-1 inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-all">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>

                    @if(request('search') || request('status'))
                        <a href="{{ route('intern.microskill.leaderboard') }}"
                            class="inline-flex items-center justify-center w-11 h-[42px] bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition-all flex-shrink-0"
                            title="Reset Filter">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>

            </form>    
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden">

            <!-- Card Header -->
            <div class="bg-blue-600 px-6 py-4">
                <h2 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-trophy mr-3"></i>
                    Papan Peringkat Mikro Skill
                </h2>
            </div>

            <div class="p-6">

                <!-- List -->
                <div class="max-h-none md:max-h-[540px] overflow-y-auto pr-1 space-y-3">

                    @forelse($rows as $index => $row)
                        @php
                            $rank = $rows->firstItem() + $index;
                        @endphp

                        <div class="lb-item">

                            <!-- Left -->
                            <div class="flex items-center min-w-0">

                                <div class="flex items-center min-w-0">
                                    <!-- Rank -->
                                    <div class="mr-4 relative">
                                        <span class="lb-rank"
                                            style="@if($rank == 1) background:linear-gradient(135deg,#f59e0b,#d97706);
                                                @elseif($rank == 2) background:linear-gradient(135deg,#94a3b8,#64748b);
                                                @elseif($rank == 3) background:linear-gradient(135deg,#f97316,#ea580c);
                                                @else background:linear-gradient(135deg,#3b82f6,#6366f1); @endif">
                                            {{ $rank }}
                                        </span>

                                        @if($rank <= 3)
                                            <i class="fas fa-crown absolute -top-2 -right-1 text-yellow-500 text-xs"></i>
                                        @endif
                                    </div>

                                    <!-- Avatar -->
                                    @if($row->photo_path)
                                        <div class="leader-avatar mr-4">
                                            <img src="{{ url('storage/' . $row->photo_path) }}" alt="{{ $row->name }}" class="w-full h-full object-cover" loading="lazy" decoding="async">
                                        </div>
                                    @else
                                        <div class="leader-avatar mr-4" style="background:linear-gradient(135deg,#60a5fa,#818cf8);">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    @endif

                                    <!-- Info -->
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 truncate">
                                            {{ $row->name }}
                                        </p>
                                        <p class="text-sm text-gray-500 truncate">
                                            {{ $row->institution }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Score -->
                            <div class="ml-4 flex-shrink-0">
                                <span class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 rounded-full text-sm font-semibold">
                                    {{ $row->total }} pelatihan
                                </span>
                            </div>
                        </div>

                    @empty
                        <div class="py-12 text-center text-gray-500">
                            <i class="fas fa-chart-line text-4xl text-gray-300 mb-3"></i>
                            <p>Belum ada data papan peringkat.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Footer -->
                <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                    <a href="{{ route('intern.dashboard') }}"
                        class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>

                    @if($rows->hasPages())
                        <div>
                            {{ $rows->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection