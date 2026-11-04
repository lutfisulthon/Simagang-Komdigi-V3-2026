<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Intern;
use App\Models\Mentor;
use App\Models\Industri;
use App\Exports\MonitoringExport;
use App\Services\WorkspaceDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AdminMonitoringController extends Controller
{
    /**
     * Scope query agar hanya mengambil intern milik admin:
     * 1. Tidak punya pengajuan (didaftarkan manual oleh admin)
     * 2. ATAU pengajuannya lewat lowongan BBLSDM Komdigi Makassar
     */
    private function adminInternScope($query, $komdigi)
    {
        return $query->where(function ($q) use ($komdigi) {
            $q->whereNull('pengajuan_detail_id');

            if ($komdigi) {
                $q->orWhereHas('pengajuanDetail.pengajuan.lowongan', function ($lq) use ($komdigi) {
                    $lq->where('industri_id', $komdigi->id);
                });
            }
        });
    }

    public function index(Request $request)
    {
        // ── Ambil industri BBLSDM Komdigi (milik admin) ──────────────────
        $komdigi = Industri::where('nama_industri', 'BBLSDM Komdigi Makassar')->first();

        // ── Parse bulan ──────────────────────────────────────────────────
        $rawMonth = $request->input('month', Carbon::now()->format('Y-m-01'));
        $rawStatus = $request->input('status', 'all');
        $selectedStatus = str_replace('-', '_', $rawStatus);
        $selectedMentor = $request->input('mentor_id', null);

        try {
            $month = Carbon::parse($rawMonth)->startOfMonth();
        } catch (\Exception $e) {
            $month = Carbon::now()->startOfMonth();
        }

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth   = $month->copy()->endOfMonth();
        $year         = $month->year;
        $mon          = $month->month;
        $selectedMonth   = $month->format('Y-m-d');
        $selectedMonthYm = $month->format('Y-m');
        $selectedYear    = $month->year;

        Log::debug('Monitoring selectedMonth', [
            'selectedMonth' => $selectedMonth,
            'year'          => $year,
            'month'         => $mon,
        ]);

        // ── Intern masuk bulan ini ────────────────────────────────────────
        $internsMasuk = $this->adminInternScope(
                Intern::query(), $komdigi
            )
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $mon)
            ->with(['mentor', 'user'])
            ->orderBy('start_date', 'asc')
            ->get();

        // ── Intern keluar (rencana, berdasarkan end_date) ─────────────────
        $internsKeluar = $this->adminInternScope(
                Intern::query(), $komdigi
            )
            ->whereYear('end_date', $year)
            ->whereMonth('end_date', $mon)
            ->with(['mentor', 'user'])
            ->orderBy('end_date', 'asc')
            ->get();

        // ── Intern aktif bulan ini ────────────────────────────────────────
        $internsAktif = $this->adminInternScope(
                Intern::query(), $komdigi
            )
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $endOfMonth)
            ->with(['mentor', 'user'])
            ->orderBy('end_date', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // ── Akan pelepasan (aktif, end_date di bulan ini) ─────────────────
        $internsAkanPelepasan = $this->adminInternScope(
                Intern::query(), $komdigi
            )
            ->where('is_active', true)
            ->whereYear('end_date', $year)
            ->whereMonth('end_date', $mon)
            ->with(['mentor', 'user'])
            ->orderBy('end_date', 'asc')
            ->get();

        // ── Pelepasan (sudah tidak aktif, updated_at di bulan ini) ────────
        $internsPelepasan = $this->adminInternScope(
                Intern::query(), $komdigi
            )
            ->where('is_active', false)
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $mon)
            ->with(['mentor', 'user'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // ── Query dasar untuk tabel aktif & alumni ────────────────────────
        $activeQuery = $this->adminInternScope(
                Intern::with(['mentor', 'user'])->where('is_active', true), $komdigi
            )
            ->whereDate('start_date', '<=', $endOfMonth->toDateString());

        $alumniQuery = $this->adminInternScope(
                Intern::with(['mentor', 'user'])->where('is_active', false), $komdigi
            );

        // ── Terapkan filter tambahan ──────────────────────────────────────
        $isFilterApplied = (
            ($request->filled('status') && $selectedStatus !== 'all') ||
            ($request->filled('mentor_id') && $selectedMentor !== '') ||
            ($request->filled('institution') && $request->institution !== 'all')
        );

        $hasFilter = false;

        if ($isFilterApplied) {
            $hasFilter = true;

            if ($selectedMentor !== null && $selectedMentor !== '') {
                $activeQuery->where('mentor_id', $selectedMentor);
                $alumniQuery->where('mentor_id', $selectedMentor);
            }

            if ($request->filled('institution') && $request->institution !== 'all') {
                $activeQuery->where('institution', $request->institution);
                $alumniQuery->where('institution', $request->institution);
            }

            if ($selectedStatus === 'masuk') {
                $activeQuery->whereYear('start_date', $year)->whereMonth('start_date', $mon);
            } elseif ($selectedStatus === 'aktif') {
                // sudah terbatas is_active = true
            } elseif ($selectedStatus === 'akan_pelepasan') {
                $activeQuery->whereNotNull('end_date')
                    ->whereBetween('end_date', [
                        $month->copy()->startOfMonth(),
                        $month->copy()->endOfMonth(),
                    ]);
            } elseif ($selectedStatus === 'pelepasan') {
                $alumniQuery->whereYear('updated_at', $year)->whereMonth('updated_at', $mon);
                $activeQuery->whereRaw('1 = 0');
            }
        }

        $activeInterns = $activeQuery->orderBy('end_date', 'asc')->orderBy('name', 'asc')
            ->paginate(15, ['*'], 'active_page');

        // Progres tugas workspace per intern (selesai/total, terlambat) untuk kolom Tugas
        $taskProgress = app(WorkspaceDashboardService::class)->progressByIntern($activeInterns->pluck('id')->all());

        $alumniInterns = $alumniQuery->orderBy('updated_at', 'desc')->orderBy('name', 'asc')
            ->paginate(15, ['*'], 'alumni_page');

        // ── Rencana pelepasan bulan ini ───────────────────────────────────
        $releasedThisMonth = $this->adminInternScope(
                Intern::query(), $komdigi
            )
            ->whereYear('end_date', $year)
            ->whereMonth('end_date', $mon)
            ->with(['mentor', 'user'])
            ->orderBy('end_date', 'desc')
            ->get();

        // ── Group by institution ──────────────────────────────────────────
        $groupByInstitution = $internsAktif
            ->groupBy('institution')
            ->map(function ($interns, $institution) {
                return [
                    'institution' => $institution,
                    'count'       => $interns->count(),
                    'interns'     => $interns->map(fn($i) => [
                        'name'   => $i->name,
                        'mentor' => $i->mentor ? $i->mentor->name : 'Belum ada mentor',
                    ])->values(),
                ];
            })->values();

        // ── Group by mentor ───────────────────────────────────────────────
        $groupByMentor = $internsAktif
            ->filter(fn($i) => $i->mentor !== null)
            ->groupBy('mentor_id')
            ->map(function ($interns, $mentorId) {
                $mentor = $interns->first()->mentor;
                return [
                    'mentor_id'   => $mentorId,
                    'mentor_name' => $mentor->name,
                    'count'       => $interns->count(),
                    'interns'     => $interns->map(fn($i) => [
                        'name'        => $i->name,
                        'institution' => $i->institution,
                    ])->values(),
                ];
            })->values();

        // ── Statistik bulan terpilih ──────────────────────────────────────
        $masukCount = $this->adminInternScope(Intern::query(), $komdigi)
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $mon)
            ->count();

        $keluarCount = $this->adminInternScope(Intern::query(), $komdigi)
            ->where('is_active', true)
            ->whereNotNull('end_date')
            ->whereYear('end_date', $year)
            ->whereMonth('end_date', $mon)
            ->count();

        $aktifCount = $this->adminInternScope(Intern::query(), $komdigi)
            ->where('is_active', true)
            ->count();

        // ── Chart 12 bulan terakhir ───────────────────────────────────────
        $monthlyStats = [];
        for ($i = 11; $i >= 0; $i--) {
            $checkMonth    = $month->copy()->subMonths($i);
            $checkYear     = $checkMonth->year;
            $checkMon      = $checkMonth->month;
            $endOfCheckMonth = $checkMonth->copy()->endOfMonth();

            $masuk = $this->adminInternScope(Intern::query(), $komdigi)
                ->whereYear('start_date', $checkYear)
                ->whereMonth('start_date', $checkMon)
                ->count();

            $keluar = $this->adminInternScope(Intern::query(), $komdigi)
                ->where('is_active', false)
                ->whereYear('updated_at', $checkYear)
                ->whereMonth('updated_at', $checkMon)
                ->count();

            $aktif = $this->adminInternScope(Intern::query(), $komdigi)
                ->where('is_active', true)
                ->where('start_date', '<=', $endOfCheckMonth)
                ->count();

            $monthlyStats[] = [
                'month'  => $checkMonth->format('M Y'),
                'masuk'  => $masuk,
                'keluar' => $keluar,
                'aktif'  => $aktif,
            ];
        }

        // ── Data pendukung ────────────────────────────────────────────────
        $mentors = Mentor::where('is_active', true)->orderBy('name')->get();

        $topInstitutions = $this->adminInternScope(Intern::query(), $komdigi)
            ->select('institution', DB::raw('count(*) as total'))
            ->groupBy('institution')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        $topInstitutionsThisMonth = $this->adminInternScope(Intern::query(), $komdigi)
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $mon)
            ->select('institution', DB::raw('count(*) as total'))
            ->groupBy('institution')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        $institutions = $this->adminInternScope(Intern::query(), $komdigi)
            ->select('institution')
            ->whereNotNull('institution')
            ->distinct()
            ->orderBy('institution')
            ->pluck('institution');

        return view('admin.monitoring.index', compact(
            'selectedMonth',
            'selectedYear',
            'selectedMonthYm',
            'selectedStatus',
            'selectedMentor',
            'hasFilter',
            'activeInterns',
            'taskProgress',
            'alumniInterns',
            'releasedThisMonth',
            'internsMasuk',
            'internsKeluar',
            'internsAktif',
            'internsPelepasan',
            'internsAkanPelepasan',
            'groupByInstitution',
            'groupByMentor',
            'monthlyStats',
            'mentors',
            'topInstitutions',
            'institutions',
            'topInstitutionsThisMonth',
            'masukCount',
            'keluarCount',
            'aktifCount'
        ));
    }

    public function markAsReleased(Intern $intern)
    {
        try {
            $intern->update(['is_active' => false]);
            return redirect()->back()
                ->with('success', 'Mahasiswa ' . $intern->name . ' berhasil ditandai sebagai pelepasan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengubah status mahasiswa: ' . $e->getMessage());
        }
    }

    public function markAsActive(Intern $intern)
    {
        try {
            $intern->update(['is_active' => true]);
            return redirect()->back()
                ->with('success', 'Mahasiswa ' . $intern->name . ' berhasil ditandai sebagai aktif.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengubah status mahasiswa: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $filename = 'Laporan_Monitoring_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new MonitoringExport([
                'month'       => $request->month,
                'status'      => $request->status,
                'mentor_id'   => $request->mentor_id,
                'institution' => $request->institution,
            ]),
            $filename
        );
    }
}