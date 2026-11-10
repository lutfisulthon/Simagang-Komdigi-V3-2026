@extends('layouts.app')

@section('title', 'Moderasi Chat Publik')

@section('content')
@php
    $roleLabels = ['admin' => 'Admin', 'mentor' => 'Mentor', 'intern' => 'Peserta', 'institusi' => 'Institusi', 'industri' => 'Industri'];
    $tabs = [
        'all' => ['Semua', 'fa-comment-dots'],
        'visible' => ['Tampil', 'fa-eye'],
        'hidden' => ['Disembunyikan', 'fa-eye-slash'],
        'pinned' => ['Pengumuman aktif', 'fa-thumbtack'],
    ];
@endphp

<div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 mb-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.2em] text-blue-600 mb-2">Moderasi</p>
                <h1 class="text-3xl font-extrabold text-slate-900">Chat Publik</h1>
                <p class="text-slate-500 mt-2">
                    Sembunyikan pesan yang melanggar aturan, cabut pengumuman, atau bungkam pengguna.
                    Pesan lebih dari {{ config('public_chat.retention_days') }} hari dihapus otomatis.
                </p>
            </div>
            <a href="{{ route('public-chat.index') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-500/20">
                <i class="fas fa-comment-dots"></i>
                Buka Chat Publik
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 text-green-700 px-5 py-4 font-semibold">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 text-red-700 px-5 py-4 font-semibold">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        @if($muted->isNotEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5 mb-6">
                <h2 class="font-extrabold text-slate-900 mb-3"><i class="fas fa-user-slash text-red-500 mr-2"></i>Pengguna dibungkam ({{ $muted->count() }})</h2>
                <div class="divide-y divide-slate-100">
                    @foreach($muted as $m)
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 py-3">
                            <div class="min-w-0">
                                <p class="font-bold text-slate-800">{{ $m->user?->name ?? 'Pengguna dihapus' }}</p>
                                <p class="text-sm text-slate-500">
                                    {{ $m->muted_until ? 'Sampai ' . $m->muted_until->translatedFormat('d M Y H:i') : 'Tanpa batas waktu' }}
                                    @if($m->mute_reason) · {{ $m->mute_reason }} @endif
                                    @if($m->mutedBy) · oleh {{ $m->mutedBy->name }} @endif
                                </p>
                            </div>
                            @if($m->user)
                                <form method="POST" action="{{ route('admin.public-chat.unmute', $m->user) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                        <i class="fas fa-rotate-left text-xs"></i> Cabut bungkam
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div class="inline-flex flex-wrap items-center gap-2 bg-white border border-slate-200 rounded-2xl p-2 shadow-sm w-fit">
                @foreach($tabs as $key => [$label, $icon])
                    <a href="{{ route('admin.public-chat.index', array_filter(['status' => $key, 'q' => $q])) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition
                       {{ $status === $key ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-500 hover:bg-slate-50' }}">
                        <i class="fas {{ $icon }} text-xs"></i>
                        {{ $label }}
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $status === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $counts[$key] }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.public-chat.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari pesan atau nama"
                       class="w-64 px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-blue-600 text-white hover:bg-blue-700 transition">
                    <i class="fas fa-magnifying-glass text-xs"></i> Cari
                </button>
            </form>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px]">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-extrabold text-slate-500 uppercase w-48">Pengirim</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold text-slate-500 uppercase">Pesan</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold text-slate-500 uppercase w-40">Waktu</th>
                            <th class="px-5 py-4 text-right text-xs font-extrabold text-slate-500 uppercase w-72">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($messages as $message)
                            @php
                                $role = \App\Support\PublicChat::roleOf($message->user);
                                $isMuted = in_array($message->user_id, $mutedIds);
                            @endphp
                            <tr style="vertical-align: top" class="{{ $message->hidden_at ? 'bg-slate-50' : 'hover:bg-slate-50' }} transition">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-900">{{ $message->user?->name }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $roleLabels[$role] }}
                                        @if($isMuted) · <span class="text-red-600 font-bold">dibungkam</span> @endif
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm text-slate-700 whitespace-pre-line break-words {{ $message->hidden_at ? 'line-through text-slate-400' : '' }}">{{ \Illuminate\Support\Str::limit($message->text, 400) }}</p>
                                    @if($message->isPinned())
                                        <p class="mt-1 text-xs font-bold text-amber-600"><i class="fas fa-thumbtack mr-1"></i>Pengumuman sampai {{ $message->pinned_until->translatedFormat('d M Y H:i') }}</p>
                                    @endif
                                    @if($message->hidden_at)
                                        <p class="mt-1 text-xs font-bold text-slate-500"><i class="fas fa-eye-slash mr-1"></i>Disembunyikan {{ $message->hidden_at->translatedFormat('d M Y H:i') }}{{ $message->hiddenBy ? ' oleh ' . $message->hiddenBy->name : '' }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-500">{{ $message->created_at->translatedFormat('d M Y H:i') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        @if($message->hidden_at)
                                            <form method="POST" action="{{ route('admin.public-chat.restore', $message) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                                    <i class="fas fa-eye"></i> Tampilkan
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.public-chat.hide', $message) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-red-50 text-red-600 hover:bg-red-100 transition">
                                                    <i class="fas fa-eye-slash"></i> Sembunyikan
                                                </button>
                                            </form>
                                        @endif

                                        @if($message->isPinned())
                                            <form method="POST" action="{{ route('admin.public-chat.unpin', $message) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 hover:bg-slate-200 transition">
                                                    <i class="fas fa-thumbtack"></i> Cabut sematan
                                                </button>
                                            </form>
                                        @endif

                                        @if($message->user && $role !== 'admin')
                                            @if($isMuted)
                                                <form method="POST" action="{{ route('admin.public-chat.unmute', $message->user) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                                        <i class="fas fa-rotate-left"></i> Cabut bungkam
                                                    </button>
                                                </form>
                                            @else
                                                <details class="relative">
                                                    <summary style="list-style: none" class="cursor-pointer inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-800 text-white hover:bg-slate-900 transition">
                                                        <i class="fas fa-ban"></i> Bungkam
                                                    </summary>
                                                    <form method="POST" action="{{ route('admin.public-chat.mute', $message->user) }}"
                                                          class="absolute right-0 z-10 mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-lg p-4 space-y-3 text-left">
                                                        @csrf
                                                        <label class="block text-xs font-bold text-slate-600">Lama
                                                            <select name="days" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                                                                <option value="1">1 hari</option>
                                                                <option value="7" selected>7 hari</option>
                                                                <option value="30">30 hari</option>
                                                                <option value="0">Tanpa batas</option>
                                                            </select>
                                                        </label>
                                                        <label class="block text-xs font-bold text-slate-600">Alasan (opsional)
                                                            <input type="text" name="reason" maxlength="255" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                                                        </label>
                                                        <button type="submit" class="w-full px-3 py-2 rounded-xl text-sm font-bold bg-red-600 text-white hover:bg-red-700 transition">Bungkam {{ $message->user->name }}</button>
                                                    </form>
                                                </details>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-slate-500">Belum ada pesan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $messages->links() }}</div>
    </div>
</div>
@endsection
