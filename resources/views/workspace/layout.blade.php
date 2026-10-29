{{--
    Layout dasar semua halaman Workspace.
    Halaman: @extends('workspace.layout') lalu isi @section('workspace-content').
    Halaman yang butuh tinggi penuh (chat) dapat menambah @section('workspace-fluid', true).
    Halaman yang butuh lebar ekstra (board) dapat menambah @section('workspace-wide', true).
--}}
@extends('layouts.app')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap">
@endpush

{{-- prepend: harus dimuat sebelum script halaman, dan sebelum Alpine (deferred) start. --}}
@prepend('scripts')
    @foreach (['workspace.js', 'tasks.js', 'backlog.js', 'deadlines.js', 'timeline.js', 'reports.js', 'automations.js', 'task-detail.js', 'task-form.js', 'channels.js', 'templates.js'] as $wsPageScript)
        <script src="{{ asset('js/workspace/' . $wsPageScript) }}?v={{ @filemtime(public_path('js/workspace/' . $wsPageScript)) }}"></script>
    @endforeach
@endprepend

@section('content')
    <div class="ws-page">
        @hasSection('workspace-fluid')
            @yield('workspace-content')
        @else
            {{-- workspace-wide: halaman dengan board 5 kolom memakai lebar lebih besar. --}}
            <div class="@hasSection('workspace-wide') max-w-[1680px] @else max-w-7xl @endif mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
                @yield('workspace-content')
            </div>
        @endif
    </div>
@endsection
