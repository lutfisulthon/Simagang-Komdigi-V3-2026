@extends('layouts.app')

@push('styles')
    <style>

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .mono {
            font-family: 'DM Mono', monospace;
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
            border: 1px solid #dbeafe;
        }

        .panel-header {
            background: linear-gradient(110deg, #1e3a8a 0%, #3b4fd8 100%);
            color: #fff;
        }

        .leader-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .leader-main {
            min-width: 0;
        }

        .leader-name {
            word-break: break-word;
        }

        .leader-score {
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.55rem;
                line-height: 1.3;
            }

            .panel-content {
                padding: 1rem;
            }

            .leader-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .leader-main {
                width: 100%;
            }

            .leader-name {
                font-size: 0.95rem;
                line-height: 1.35;
            }

            .leader-score {
                align-self: flex-end;
            }

            .back-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="hero-strip mb-6">
                <div class="relative z-10 px-6 py-7">
                    <h1 class="hero-title text-3xl sm:text-4xl font-bold mb-2">Leaderboard Mikro Skill</h1>
                    <p class="text-blue-100">Peringkat capaian mikro skill anak magang</p>
                </div>
            </div>

            <!-- Leaderboard Card -->
            <div class="panel">
                <div class="panel-header px-6 py-4">
                    <h2 class="text-lg font-bold text-white flex items-center">
                        <i class="fas fa-trophy mr-3"></i>
                        Leaderboard Mikro Skill
                    </h2>
                </div>

                <div class="panel-content p-6">
                    <!-- SCROLL CONTAINER -->
                    <div
                        class="max-h-[500px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-blue-400 scrollbar-track-blue-100">
                        @forelse($rows as $index => $row)
                            <div
                                class="leader-item p-3 bg-[#f0f4ff] rounded-xl hover:shadow-md transition-all duration-300 border border-[#e0e7ff] mb-3">
                                <div class="leader-main flex items-center">
                                    <!-- Rank Badge -->
                                    <div class="relative">
                                        <span
                                            class="w-8 h-8 rounded-full 
                                    @if ($rows->firstItem() + $index == 1) bg-yellow-500
                                    @elseif($rows->firstItem() + $index == 2) bg-gray-400
                                    @elseif($rows->firstItem() + $index == 3) bg-orange-500
                                    @else bg-indigo-500 @endif
                                    text-white flex items-center justify-center font-bold text-sm shadow-lg mr-3">
                                            {{ $rows->firstItem() + $index }}
                                        </span>
                                        @if ($rows->firstItem() + $index < 4)
                                            <i class="fas fa-crown absolute -top-2 -right-1 text-yellow-500 text-xs"></i>
                                        @endif
                                    </div>

                                    <!-- Photo -->
                                    @if ($row->photo_path)
                                        <img src="{{ url('storage/' . $row->photo_path) }}"
                                            class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-md mr-3"
                                            alt="{{ $row->name }}" loading="lazy" decoding="async">
                                    @else
                                        <div
                                            class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center mr-3 shadow-md">
                                            <i class="fas fa-user text-gray-500 text-xs"></i>
                                        </div>
                                    @endif

                                    <!-- Info -->
                                    <div class="min-w-0">
                                        <div class="leader-name font-semibold text-gray-900 text-sm">
                                            {{ $row->name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $row->institution }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Score Badge -->
                                <span
                                    class="leader-score px-3 py-1 bg-[#e0e7ff] text-[#3730a3] rounded-full text-xs font-semibold mono">
                                    {{ $row->total }} course
                                </span>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-8 text-gray-500">
                                <i class="fas fa-chart-line text-4xl mb-3 text-gray-300"></i>
                                <p class="text-sm">Belum ada data.</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.dashboard') }}"
                            class="back-btn inline-flex items-center px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 font-semibold rounded-lg transition duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>

                    <!-- Pagination -->
                    @if ($rows->hasPages())
                        <div class="mt-4">
                            {{ $rows->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
