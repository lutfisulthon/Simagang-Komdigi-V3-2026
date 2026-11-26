<?php

namespace App\Http\Controllers\Admin;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Http\Controllers\Controller;
use App\Models\Intern;
use App\Models\Mentor;
use App\Models\User;
use App\Models\Team;
use App\Models\Institusi;
use App\Models\Industri;
use App\Models\PengajuanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminInternController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage_interns');
    }

    public function index(Request $request)
    {
        // Ambil ID industri BBLSDM Komdigi Makassar (milik admin)
        $komdigi = Industri::where('nama_industri', 'BBLSDM Komdigi Makassar')->first();

        $baseQuery = Intern::with(['user', 'mentor', 'teamRelation'])
            ->where(function ($q) use ($komdigi) {
                // Peserta yang tidak punya pengajuan (didaftarkan manual oleh admin)
                $q->whereNull('pengajuan_detail_id');

                // ATAU peserta yang pengajuannya lewat lowongan BBLSDM Komdigi
                if ($komdigi) {
                    $q->orWhereHas('pengajuanDetail.pengajuan.lowongan', function ($query) use ($komdigi) {
                        $query->where('industri_id', $komdigi->id);
                    });
                }
            });

        // Daftar kampus untuk dropdown filter: diambil sebelum filter lain diterapkan,
        // supaya pilihannya tetap lengkap walau sedang menyaring tim/mentor/kampus tertentu.
        $institutions = (clone $baseQuery)
            ->whereNotNull('institution')
            ->distinct()
            ->orderBy('institution')
            ->pluck('institution');

        if ($request->filled('search')) {
            $baseQuery->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('team_id')) {
            $baseQuery->where('team_id', $request->team_id);
        }

        if ($request->filled('mentor_id')) {
            $baseQuery->where('mentor_id', $request->mentor_id);
        }

        if ($request->filled('institution')) {
            $baseQuery->where('institution', $request->institution);
        }

        $selectedSort = $request->input('sort', '');

        // Active interns (is_active = true)
        $activeInterns = (clone $baseQuery)->where('is_active', true);
        // Alumni / inactive interns (is_active = false)
        $alumniInterns = (clone $baseQuery)->where('is_active', false);

        switch ($selectedSort) {
            case 'name_asc':
                $activeInterns->orderBy('name', 'asc');
                $alumniInterns->orderBy('name', 'asc');
                break;
            case 'created_asc':
                $activeInterns->orderBy('created_at', 'asc');
                $alumniInterns->orderBy('created_at', 'asc');
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
                $activeInterns->orderByDesc('created_at');
                $alumniInterns->orderByDesc('created_at');
        }

        $activeInterns = $activeInterns->paginate(15, ['*'], 'active_page')->withQueryString();
        $alumniInterns = $alumniInterns->paginate(15, ['*'], 'alumni_page')->withQueryString();

        // ── Komposisi Aktif vs Alumni (potret status apa adanya, ikut filter yang
        // sedang dipakai: search/tim/mentor/kampus) untuk chart donut + wawasan.
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

        $mentors = Mentor::orderByDesc('created_at')->get();
        $teams = Team::orderBy('name')->get();

        return view('admin.intern.index', compact(
            'activeInterns',
            'alumniInterns',
            'mentors',
            'teams',
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
        if ($q === '') {
            return response()->json(['active' => [], 'alumni' => []]);
        }

        $komdigi = Industri::where('nama_industri', 'BBLSDM Komdigi Makassar')->first();

        $base = Intern::where(function ($query) use ($komdigi) {
                $query->whereNull('pengajuan_detail_id');
                if ($komdigi) {
                    $query->orWhereHas('pengajuanDetail.pengajuan.lowongan', function ($lq) use ($komdigi) {
                        $lq->where('industri_id', $komdigi->id);
                    });
                }
            })
            ->where('name', 'like', '%' . $q . '%');

        $map = fn ($rows) => $rows->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'institution' => $i->institution])->values();

        return response()->json([
            'active' => $map((clone $base)->where('is_active', true)->orderBy('name')->limit(6)->get(['id', 'name', 'institution'])),
            'alumni' => $map((clone $base)->where('is_active', false)->orderBy('name')->limit(6)->get(['id', 'name', 'institution'])),
        ]);
    }

    public function create()
    {
        $komdigi = Industri::where(
            'nama_industri',
            'BBLSDM Komdigi Makassar'
        )->first();

        $calonMagang = PengajuanDetail::with([
                'pengajuan.institusi',
                'pengajuan.lowongan.team'
            ])
            ->whereHas('pengajuan', function ($q) use ($komdigi) {
                $q->where('status', 'approved')
                ->whereHas('lowongan', function ($lowongan) use ($komdigi) {
                    $lowongan->where('industri_id', optional($komdigi)->id);
                });
            })
            ->doesntHave('intern')
            ->get();

        $mentors = Mentor::with('team')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.intern.create', compact(
            'mentors',
            'calonMagang'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'gender' => ['required', 'in:Laki-laki,Perempuan'],
            'education_level' => ['required', 'in:SMA/SMK,S1/D4'],
            'major' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'in:Magang,KKN Profesi,PKL,Praktek Industri,Magang Industri,Guru Magang Industri,Job on Training'],
            'mentor_id' => ['required', 'exists:mentors,id'],
            'pengajuan_detail_id' => ['nullable', 'exists:pengajuan_details,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'photo' => ['required', 'image', 'max:2048'],
            'password' => ['required', Password::defaults()],
            'hard_skill' => ['nullable', 'string'],
            'soft_skill' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'intern',
        ]);
        $user->assignRole('intern');

        // Handle photo upload
        $photo = $request->file('photo');
        if ($photo->isValid() && $photo->getError() === UPLOAD_ERR_OK) {
            try {
                // $extension = $photo->getClientOriginalExtension() ?: ($photo->guessExtension() ?: 'jpg');
                // $filename = 'photo_' . time() . '_' . uniqid() . '.' . $extension;
                // $destinationPath = storage_path('app/public/photos');

                // if (!file_exists($destinationPath)) {
                //     mkdir($destinationPath, 0755, true);
                // }

                // $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;
                // if ($photo->move($destinationPath, $filename) && file_exists($fullPath)) {
                //     $photoPath = 'photos/' . $filename;
                // } else {
                //     return back()->withErrors(['photo' => 'Gagal menyimpan foto.'])->withInput();
                // }
                $filename = 'photo_' . time() . '_' . uniqid() . '.jpg';
                $destinationPath = storage_path('app/public/photos');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;

                try {
                    // 1. Panggil alat pengolah gambar
                    $manager = new ImageManager(new Driver());
                    
                    // 2. Baca file yang diunggah
                    $image = $manager->read($photo->getPathname());
                    
                    // 3. Perkecil dimensi gambar (maksimal lebar 800px, proporsi tinggi otomatis menyesuaikan)
                    $image->scaleDown(width: 800);
                    
                    // 4. Konversi ke JPG dengan kualitas 75% lalu simpan
                    $image->toJpeg(quality: 75)->save($fullPath);
                    
                    $photoPath = 'photos/' . $filename;
                } catch (\Exception $e) {
                    return back()->withErrors(['photo' => 'Gagal memproses/mengompresi foto: ' . $e->getMessage()])->withInput();
                }
            } catch (\Exception $e) {
                return back()->withErrors(['photo' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
            }
        } else {
            return back()->withErrors(['photo' => 'File foto tidak valid.'])->withInput();
        }

        // Use custom institution if provided
        $institution = $validated['institution'];
        if ($institution === '__custom__' && !empty($validated['custom_institution'])) {
            $institution = $validated['custom_institution'];
        }

        $mentor = Mentor::with('team')->find($validated['mentor_id']);

        if (!$mentor) {
            return back()->withErrors(['mentor_id' => 'Mentor tidak ditemukan.']);
        }

        if (!empty($validated['pengajuan_detail_id'])) {
            $calon = PengajuanDetail::with(['pengajuan.lowongan.team'])->find($validated['pengajuan_detail_id']);
            $targetTeamId = $calon?->pengajuan?->lowongan?->team_id;

            if ($targetTeamId && !$mentor->team) {
                return back()->withErrors(['mentor_id' => 'Mentor ini belum memiliki tim.'])->withInput();
            }

            if ($targetTeamId && (int) $mentor->team_id !== (int) $targetTeamId) {
                return back()->withErrors([
                    'mentor_id' => 'Mentor harus sesuai dengan tim penempatan calon peserta magang.'
                ])->withInput();
            }
        }

        Intern::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'gender' => $validated['gender'],
            'education_level' => $validated['education_level'],
            'major' => $validated['major'],
            'phone' => $validated['phone'],
            'institution' => $institution,
            'purpose' => $validated['purpose'] ?? null,
            'mentor_id' => $mentor->id,
            'team_id' => $mentor->team_id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'photo_path' => $photoPath,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : false,
            'pengajuan_detail_id' => $request->pengajuan_detail_id,
            'hard_skill' => $validated['hard_skill'] ?? null,
            'soft_skill' => $validated['soft_skill'] ?? null,
        ]);

        return redirect()->route('admin.intern.index')
            ->with('success', 'Data anak magang berhasil ditambahkan.');
    }

    public function show(Intern $intern)
    {
        $intern->load(['attendances' => function ($query) {
            $query->orderBy('date', 'desc')->take(30);
        }, 'logbooks' => function ($query) {
            $query->orderBy('date', 'desc')->take(10);
        }, 'finalReport', 'mentor']);

        $stats = [
            'total_hadir' => $intern->attendances()->where('status', 'hadir')->count(),
            'total_izin' => $intern->attendances()->where('status', 'izin')->count(),
            'total_sakit' => $intern->attendances()->where('status', 'sakit')->count(),
            'total_logbooks' => $intern->logbooks()->count(),
            'has_report' => $intern->finalReport !== null,
        ];

        return view('admin.intern.show', compact('intern', 'stats'));
    }

    public function edit(Intern $intern)
    {
        $mentors = Mentor::with('team')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.intern.edit', compact('intern', 'mentors'));
    }

    public function update(Request $request, Intern $intern)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $intern->user_id],
            'gender' => ['required', 'in:Laki-laki,Perempuan'],
            'education_level' => ['required', 'in:SMA/SMK,S1/D4'],
            'major' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'in:Magang,KKN Profesi,PKL,Praktek Industri,Magang Industri,Guru Magang Industri,Job on Training'],
            'mentor_id' => ['required', 'exists:mentors,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'password' => ['nullable', Password::defaults()],
            'is_active' => ['boolean'],
            'hard_skill' => ['nullable', 'string'],
            'soft_skill' => ['nullable', 'string'],
        ]);

        $intern->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (isset($validated['password'])) {
            $intern->user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        // Use custom institution if provided
        $institution = $validated['institution'];
        if ($institution === '__custom__' && !empty($validated['custom_institution'])) {
            $institution = $validated['custom_institution'];
        }

        $mentor = Mentor::with('team')->find($validated['mentor_id']);

        if (!$mentor) {
            return back()->withErrors(['mentor_id' => 'Mentor tidak ditemukan.'])->withInput();
        }

        if ($intern->pengajuan_detail_id) {
            $calon = PengajuanDetail::with(['pengajuan.lowongan.team'])->find($intern->pengajuan_detail_id);
            $targetTeamId = $calon?->pengajuan?->lowongan?->team_id;

            if ($targetTeamId && !$mentor->team) {
                return back()->withErrors(['mentor_id' => 'Mentor ini belum memiliki tim.'])->withInput();
            }

            if ($targetTeamId && (int) $mentor->team_id !== (int) $targetTeamId) {
                return back()->withErrors([
                    'mentor_id' => 'Mentor harus sesuai dengan tim penempatan calon peserta magang.'
                ])->withInput();
            }
        }

        $data = [
            'name' => $validated['name'],
            'gender' => $validated['gender'],
            'education_level' => $validated['education_level'],
            'major' => $validated['major'],
            'phone' => $validated['phone'],
            'institution' => $institution,
            'purpose' => $validated['purpose'] ?? null,
            'mentor_id' => $mentor->id,
            'team_id' => $mentor->team_id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : false,
            'hard_skill' => $validated['hard_skill'] ?? null,
            'soft_skill' => $validated['soft_skill'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            if ($photo->isValid() && $photo->getError() === UPLOAD_ERR_OK) {
                try {
                    // Hapus foto lama jika ada
                    if ($intern->photo_path) {
                        $oldPath = storage_path('app/public/' . $intern->photo_path);
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }


                    $filename = 'photo_' . time() . '_' . uniqid() . '.jpg';
                    $destinationPath = storage_path('app/public/photos');


                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }


                    $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;


                    // Proses Kompresi
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($photo->getPathname());
                    
                    // Resize maksimal lebar 800px & kualitas 75%
                    $image->scaleDown(width: 800);
                    $image->toJpeg(quality: 75)->save($fullPath);
                    
                    $data['photo_path'] = 'photos/' . $filename;
                    
                } catch (\Exception $e) {
                    return back()->withErrors(['photo' => 'Gagal memproses/mengompresi foto: ' . $e->getMessage()])->withInput();
                }
            }
        }


        $intern->update($data);

        return redirect()->route('admin.intern.show', $intern)
            ->with('success', 'Data anak magang berhasil diperbarui.');
    }

    public function destroy(Intern $intern)
    {
        if ($intern->photo_path) {
            Storage::disk('public')->delete($intern->photo_path);
        }

        $userId = $intern->user_id;
        $intern->delete();
        User::find($userId)->delete();

        return redirect()->route('admin.intern.index')
            ->with('success', 'Data anak magang berhasil dihapus.');
    }
}