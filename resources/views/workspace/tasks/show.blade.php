{{--
    Detail tugas — halaman penuh (dibuka dari notifikasi, link #T-12, atau "Buka halaman penuh").
    Variabel (lihat docs/workspace/kontrak-frontend.md §5):
      $task               TaskDetail
      $workspace          Workspace
      $members            UserMini[]
      $assignableInterns  InternMini[]  (form edit)
      $labels             {id,name,color}[]
--}}
@extends('workspace.layout')

@section('title', $task['code'] . ' · ' . $task['title'] . ' - Sistem Magang')

@section('workspace-content')
    @php
        $selfInternId = null;
    @endphp

    <nav class="mb-4 text-sm text-slate-600 flex items-center gap-2 min-w-0" aria-label="Breadcrumb">
        <a href="{{ route('workspace.index') }}" class="hover:text-indigo-600 shrink-0 py-1 -my-1"><i class="fas fa-layer-group mr-1"></i>Workspace</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
        <a href="{{ $workspace['url'] }}" class="hover:text-indigo-600 truncate py-1 -my-1">{{ $workspace['name'] }}</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
        <span class="font-medium text-slate-700 shrink-0 ws-mono" aria-current="page">{{ $task['code'] }}</span>
    </nav>

    <div class="max-w-3xl">
        <a href="{{ $workspace['url'] }}?task={{ $task['code'] }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800 mb-3 py-1">
            <i class="fas fa-arrow-left text-xs"></i> Kembali ke board
        </a>

        <article class="ws-panel ws-anim-1" x-data="wsTaskDetail(@js(['mode' => 'page', 'task' => $task]))" aria-labelledby="ws-task-title">
            @include('workspace.partials.task-detail', ['mode' => 'page'])
        </article>
    </div>

    @include('workspace.partials.task-modals')

@endsection
