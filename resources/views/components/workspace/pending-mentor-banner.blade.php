{{-- Ditampilkan ke intern aktif yang belum punya mentor: workspace belum dibuat (PRD Fitur 1, kasus khusus). --}}
<div {{ $attributes->class(['flex items-start gap-4 bg-amber-50 border border-amber-300 text-amber-800 px-5 py-4 rounded-2xl shadow-sm']) }} role="status">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 shrink-0">
        <i class="fas fa-user-clock text-amber-500"></i>
    </span>
    <div class="flex-1">
        <p class="font-semibold text-amber-900 text-sm mb-1">Menunggu penetapan mentor</p>
        <p class="text-amber-700 text-sm">
            Workspace tim dan space pribadi Anda akan dibuat otomatis setelah admin menetapkan mentor.
            Admin sudah mendapat pemberitahuan.
        </p>
    </div>
</div>
<x-workspace.page-css />
