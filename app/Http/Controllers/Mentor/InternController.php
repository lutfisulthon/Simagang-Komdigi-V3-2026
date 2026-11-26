<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Intern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InternController extends Controller
{
    public function index(Request $request)
    {
        $mentor = Auth::user()->mentor;

        $baseQuery = $mentor ? $mentor->interns() : Intern::query()->whereRaw('1 = 0');

        // Daftar kampus untuk dropdown filter: diambil sebelum filter lain diterapkan,
        // supaya pilihannya tetap lengkap walau sedang menyaring kampus tertentu.
        $institutions = (clone $baseQuery)
            ->whereNotNull('institution')
            ->distinct()
            ->orderBy('institution')
            ->pluck('institution');

        // Search by name
        if ($request->filled('search')) {
            $baseQuery->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by kampus
        if ($request->filled('institution')) {
            $baseQuery->where('institution', $request->institution);
        }

        $selectedSort = $request->input('sort', '');

        // Peserta aktif dan alumni dipisah jadi dua tabel sendiri-sendiri: hasil pencarian
        // dan filter pada satu tabel tidak pernah bercampur dengan tabel lainnya.
        $activeInterns = (clone $baseQuery)->where('is_active', true)
            ->withCount(['attendances', 'logbooks', 'microSkills']);
        $alumniInterns = (clone $baseQuery)->where('is_active', false)
            ->withCount(['attendances', 'logbooks', 'microSkills']);

        switch ($selectedSort) {
            case 'name_desc':
                $activeInterns->orderBy('name', 'desc');
                $alumniInterns->orderBy('name', 'desc');
                break;
            case 'start_desc':
                $activeInterns->orderBy('start_date', 'desc')->orderBy('name', 'asc');
                $alumniInterns->orderBy('start_date', 'desc')->orderBy('name', 'asc');
                break;
            case 'start_asc':
                $activeInterns->orderBy('start_date', 'asc')->orderBy('name', 'asc');
                $alumniInterns->orderBy('start_date', 'asc')->orderBy('name', 'asc');
                break;
            default:
                $activeInterns->orderBy('name', 'asc');
                $alumniInterns->orderBy('name', 'asc');
        }

        $activeInterns = $activeInterns->paginate(15, ['*'], 'active_page')->withQueryString();
        $alumniInterns = $alumniInterns->paginate(15, ['*'], 'alumni_page')->withQueryString();

        // ── Komposisi Aktif vs Alumni (potret status apa adanya, ikut filter yang
        // sedang dipakai: search/kampus) untuk chart donut + wawasan.
        $compositionActive = (clone $baseQuery)->where('is_active', true)->count();
        $compositionAlumni = (clone $baseQuery)->where('is_active', false)->count();
        $compositionTotal  = $compositionActive + $compositionAlumni;

        $avgDurationDays = (clone $baseQuery)
            ->where('is_active', false)
            ->whereNotNull('start_date')
            ->selectRaw('AVG(DATEDIFF(COALESCE(end_date, updated_at), start_date)) as avg_days')
            ->value('avg_days');

        $topAlumniInstitution = (clone $baseQuery)
            ->where('is_active', false)
            ->whereNotNull('institution')
            ->select('institution', DB::raw('count(*) as total'))
            ->groupBy('institution')
            ->orderByDesc('total')
            ->first();

        return view('mentor.intern.index', compact(
            'mentor',
            'activeInterns',
            'alumniInterns',
            'institutions',
            'selectedSort',
            'compositionActive',
            'compositionAlumni',
            'compositionTotal',
            'avgDurationDays',
            'topAlumniInstitution'
        ));
    }

    /**
     * Saran nama (autocomplete) untuk kotak pencarian: dipanggil lewat AJAX sambil
     * mengetik, dipisah Aktif/Alumni supaya jelas sebelum diklik/di-submit.
     */
    public function searchSuggest(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $mentor = Auth::user()->mentor;

        if ($q === '' || !$mentor) {
            return response()->json(['active' => [], 'alumni' => []]);
        }

        $base = $mentor->interns()->where('name', 'like', '%' . $q . '%');
        $map = fn ($rows) => $rows->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'institution' => $i->institution])->values();

        return response()->json([
            'active' => $map((clone $base)->where('is_active', true)->orderBy('name')->limit(6)->get(['id', 'name', 'institution'])),
            'alumni' => $map((clone $base)->where('is_active', false)->orderBy('name')->limit(6)->get(['id', 'name', 'institution'])),
        ]);
    }

    public function show(Intern $intern)
    {
        $mentor = Auth::user()->mentor;
        if (!$mentor || $intern->mentor_id !== $mentor->id) {
            abort(403, 'Unauthorized');
        }

        $intern->load([
            'attendances' => function ($q) {
                $q->orderByDesc('date')->take(30);
            },
            'logbooks' => function ($q) {
                $q->orderByDesc('date')->take(15);
            },
            'finalReport'
        ]);

        return view('mentor.intern.show', compact('mentor', 'intern'));
    }
}


