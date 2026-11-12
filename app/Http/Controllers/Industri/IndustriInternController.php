<?php

namespace App\Http\Controllers\Industri;

use App\Http\Controllers\Controller;
use App\Models\Intern;
use App\Models\User;
use App\Models\PengajuanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Support\SafeUpload;

class IndustriInternController extends Controller
{
    /**
     * Pastikan intern yang diakses memang milik industri yang sedang login.
     */
    private function requireIndustriProfile(): \Illuminate\Http\RedirectResponse|null
    {
        if (! auth()->user()->industri) {
            return redirect()->route('industri.profile.create')
                ->with('warning', 'Anda harus melengkapi profil perusahaan terlebih dahulu sebelum dapat mengakses fitur ini.');
        }
        return null;
    }

    private function authorizeIntern(Intern $intern): void
    {
        $industriId = auth()->user()->industri->id;

        $owns = $intern->pengajuan_detail_id &&
            $intern->pengajuanDetail?->pengajuan?->lowongan?->industri_id == $industriId;

        abort_if(!$owns, 403, 'Anda tidak memiliki akses ke data ini.');
    }

    public function index(Request $request)
    {
        if ($redirect = $this->requireIndustriProfile()) {
            return $redirect;
        }

        $industriId = auth()->user()->industri->id;

        $baseQuery = Intern::with(['user'])
            ->whereNotNull('pengajuan_detail_id')
            ->whereHas('pengajuanDetail.pengajuan.lowongan', function ($query) use ($industriId) {
                $query->where('industri_id', $industriId);
            });

        if ($request->filled('search')) {
            $baseQuery->where('name', 'like', '%' . $request->search . '%');
        }

        $activeInterns = (clone $baseQuery)
            ->where('is_active', true)
            ->latest()
            ->paginate(15, ['*'], 'active_page')
            ->withQueryString();

        $alumniInterns = (clone $baseQuery)
            ->where('is_active', false)
            ->latest()
            ->paginate(15, ['*'], 'alumni_page')
            ->withQueryString();

        return view('industri.intern.index', compact(
            'activeInterns',
            'alumniInterns'
        ));
    }

    public function create()
    {
        if ($redirect = $this->requireIndustriProfile()) {
            return $redirect;
        }

        $industri = auth()->user()->industri;

        $calonMagang = PengajuanDetail::with(['pengajuan.institusi'])
            ->whereHas('pengajuan', function ($query) use ($industri) {
                $query->where('status', 'approved')
                    ->whereHas('lowongan', function ($lowongan) use ($industri) {
                        $lowongan->where('industri_id', $industri->id);
                    });
            })
            ->doesntHave('intern')
            ->get();

        return view('industri.intern.create', compact('calonMagang'));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->requireIndustriProfile()) {
            return $redirect;
        }

        $industri = auth()->user()->industri;

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'gender'              => ['required', 'in:Laki-laki,Perempuan'],
            'education_level'     => ['required', 'in:SMA/SMK,S1/D4'],
            'major'               => ['nullable', 'string', 'max:255'],
            'phone'               => ['nullable', 'string', 'max:255'],
            'institution'         => ['required', 'string', 'max:255'],
            'purpose'             => ['nullable', 'string', 'in:Magang,KKN Profesi,PKL,Praktek Industri,Magang Industri,Guru Magang Industri,Job on Training'],
            'pengajuan_detail_id' => ['required', 'exists:pengajuan_details,id'],
            'start_date'          => ['required', 'date'],
            'end_date'            => ['required', 'date', 'after:start_date'],
            'photo'               => ['required', 'image', 'max:2048'],
            'password'            => ['required', Password::defaults()],
            'hard_skill'          => ['nullable', 'string'],
            'soft_skill'          => ['nullable', 'string'],
        ]);

        // Pastikan pengajuan_detail_id memang milik industri ini
        $detail = PengajuanDetail::whereHas('pengajuan.lowongan', function ($q) use ($industri) {
            $q->where('industri_id', $industri->id);
        })->findOrFail($validated['pengajuan_detail_id']);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'intern',
        ]);
        $user->assignRole('intern');

        // Handle photo upload
        $photo = $request->file('photo');
        if ($photo->isValid() && $photo->getError() === UPLOAD_ERR_OK) {
            try {
                $extension       = SafeUpload::extension($photo, SafeUpload::IMAGE, 'jpg');
                $filename        = 'photo_' . time() . '_' . uniqid() . '.' . $extension;
                $destinationPath = storage_path('app/public/photos');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;
                if ($photo->move($destinationPath, $filename) && file_exists($fullPath)) {
                    $photoPath = 'photos/' . $filename;
                } else {
                    $user->delete();
                    return back()->withErrors(['photo' => 'Gagal menyimpan foto.'])->withInput();
                }
            } catch (\Exception $e) {
                $user->delete();
                return back()->withErrors(['photo' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
            }
        } else {
            $user->delete();
            return back()->withErrors(['photo' => 'File foto tidak valid.'])->withInput();
        }

        $institution = $validated['institution'];
        if ($institution === '__custom__' && !empty($request->custom_institution)) {
            $institution = $request->custom_institution;
        }

        Intern::create([
            'user_id'             => $user->id,
            'name'                => $validated['name'],
            'gender'              => $validated['gender'],
            'education_level'     => $validated['education_level'],
            'major'               => $validated['major'],
            'phone'               => $validated['phone'],
            'institution'         => $institution,
            'purpose'             => $validated['purpose'] ?? null,
            'start_date'          => $validated['start_date'],
            'end_date'            => $validated['end_date'],
            'photo_path'          => $photoPath,
            'is_active'           => $request->boolean('is_active', false),
            'pengajuan_detail_id' => $validated['pengajuan_detail_id'],
            'hard_skill'          => $validated['hard_skill'] ?? null,
            'soft_skill'          => $validated['soft_skill'] ?? null,
        ]);

        return redirect()->route('industri.intern.index')
            ->with('success', 'Data peserta magang berhasil ditambahkan.');
    }

    public function show(Intern $intern)
    {
        $this->authorizeIntern($intern);

        $intern->load([
            'attendances' => fn($q) => $q->orderBy('date', 'desc')->take(30),
            'logbooks'    => fn($q) => $q->orderBy('date', 'desc')->take(10),
            'finalReport',
        ]);

        $stats = [
            'total_hadir'   => $intern->attendances()->where('status', 'hadir')->count(),
            'total_izin'    => $intern->attendances()->where('status', 'izin')->count(),
            'total_sakit'   => $intern->attendances()->where('status', 'sakit')->count(),
            'total_logbooks'=> $intern->logbooks()->count(),
            'has_report'    => $intern->finalReport !== null,
        ];

        return view('industri.intern.show', compact('intern', 'stats'));
    }

    public function edit(Intern $intern)
    {
        $this->authorizeIntern($intern);

        return view('industri.intern.edit', compact('intern'));
    }

    public function update(Request $request, Intern $intern)
    {
        $this->authorizeIntern($intern);

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $intern->user_id],
            'gender'          => ['required', 'in:Laki-laki,Perempuan'],
            'education_level' => ['required', 'in:SMA/SMK,S1/D4'],
            'major'           => ['nullable', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:255'],
            'institution'     => ['required', 'string', 'max:255'],
            'purpose'         => ['nullable', 'string', 'in:Magang,KKN Profesi,PKL,Praktek Industri,Magang Industri,Guru Magang Industri,Job on Training'],
            'start_date'      => ['required', 'date'],
            'end_date'        => ['required', 'date', 'after:start_date'],
            'photo'           => ['nullable', 'image', 'max:2048'],
            'password'        => ['nullable', Password::defaults()],
            'is_active'       => ['boolean'],
            'hard_skill'      => ['nullable', 'string'],
            'soft_skill'      => ['nullable', 'string'],
        ]);

        $intern->user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $intern->user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        $institution = $validated['institution'];
        if ($institution === '__custom__' && !empty($request->custom_institution)) {
            $institution = $request->custom_institution;
        }

        $data = [
            'name'            => $validated['name'],
            'gender'          => $validated['gender'],
            'education_level' => $validated['education_level'],
            'major'           => $validated['major'],
            'phone'           => $validated['phone'],
            'institution'     => $institution,
            'purpose'         => $validated['purpose'] ?? null,
            'start_date'      => $validated['start_date'],
            'end_date'        => $validated['end_date'],
            'is_active'       => $request->boolean('is_active', false),
            'hard_skill'      => $validated['hard_skill'] ?? null,
            'soft_skill'      => $validated['soft_skill'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            if ($photo->isValid() && $photo->getError() === UPLOAD_ERR_OK) {
                try {
                    if ($intern->photo_path) {
                        $oldPath = storage_path('app/public/' . $intern->photo_path);
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }

                    $extension       = SafeUpload::extension($photo, SafeUpload::IMAGE, 'jpg');
                    $filename        = 'photo_' . time() . '_' . uniqid() . '.' . $extension;
                    $destinationPath = storage_path('app/public/photos');

                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;
                    if ($photo->move($destinationPath, $filename) && file_exists($fullPath)) {
                        $data['photo_path'] = 'photos/' . $filename;
                    }
                } catch (\Exception $e) {
                    return back()->withErrors(['photo' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
                }
            }
        }

        $intern->update($data);

        return redirect()->route('industri.intern.index')
            ->with('success', 'Data peserta magang berhasil diperbarui.');
    }

    public function destroy(Intern $intern)
    {
        $this->authorizeIntern($intern);

        if ($intern->photo_path) {
            Storage::disk('public')->delete($intern->photo_path);
        }

        $userId = $intern->user_id;
        $intern->delete();
        User::find($userId)?->delete();

        return redirect()->route('industri.intern.index')
            ->with('success', 'Data peserta magang berhasil dihapus.');
    }
}