<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Intern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Services\HolidayService;
use App\Services\TimeService;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Support\SafeUpload;

class AttendanceController extends Controller
{
    public function index()
    {
        $intern = Auth::user()->intern;
        $nowWita = TimeService::nowWita();
        $todayWita = $nowWita->toDateString();

        $totalHadir      = Attendance::where('intern_id', $intern->id)->where('status', 'hadir')->count();
        $totalIzin       = Attendance::where('intern_id', $intern->id)->where('status', 'izin')->count();
        $totalSakit      = Attendance::where('intern_id', $intern->id)->where('status', 'sakit')->count();
        $totalTidakHadir = Attendance::where('intern_id', $intern->id)->where('status', 'alfa')->count();
        $attendances     = Attendance::where('intern_id', $intern->id)
            ->orderBy('date', 'desc')
            ->paginate(15);


        $todayVirtualAbsent = false;
        $isWorkday = !HolidayService::isHoliday($nowWita);
        $hasTodayRecord = Attendance::where('intern_id', $intern->id)
            ->whereDate('date', $todayWita)
            ->exists();

        if ($isWorkday && !$hasTodayRecord) {
            $todayVirtualAbsent = true;
        }

        $cekaktif = Intern::where('id', $intern->id)
            ->where('is_active', true)
            ->exists();

        return view('intern.attendance.index', compact(
            'attendances', 'totalHadir', 'totalIzin', 'totalSakit', 'totalTidakHadir',
            'todayVirtualAbsent', 'todayWita', 'cekaktif'
        ));
    }

    public function create()
    {
        $intern = Auth::user()->intern;

        $nowWita = TimeService::nowWita();
        $todayWita = $nowWita->toDateString();

        if (HolidayService::isWeekend($nowWita)) {
            $dayName = $nowWita->isSaturday() ? 'Sabtu' : 'Minggu';
            return redirect()->route('intern.attendance.index')
                ->with('info', "Hari ini {$dayName} — hari libur, tidak ada absensi.");
        }

        if (HolidayService::isNationalHoliday($nowWita)) {
            $holidayName = HolidayService::getHolidayName($nowWita) ?? 'Hari Libur Nasional';
            return redirect()->route('intern.attendance.index')
                ->with('info', "Hari ini libur nasional ({$holidayName}), tidak ada absensi.");
        }

        $checkInStart = env('ATTENDANCE_CHECKIN_START', '08:00');
        $checkInEnd = env('ATTENDANCE_CHECKIN_END', '12:00');
        $currentTime = $nowWita->format('H:i');

        if ($currentTime < $checkInStart || $currentTime > $checkInEnd) {
            return redirect()->route('intern.attendance.index')
                ->with('info', 'Form absensi masuk hanya tersedia pukul ' . $checkInStart . ' - ' . $checkInEnd . ' WITA.');
        }

        $todayAttendance = Attendance::where('intern_id', $intern->id)
            ->whereDate('date', $todayWita)
            ->first();

        if ($todayAttendance) {
            return redirect()->route('intern.attendance.index')
                ->with('info', 'Anda sudah melakukan absensi hari ini.');
        }

        return view('intern.attendance.create');
    }

    public function store(Request $request)
    {
        $intern = Auth::user()->intern;

        $nowWita = TimeService::nowWita();
        $todayWita = $nowWita->toDateString();

        if (HolidayService::isWeekend($nowWita)) {
            $dayName = $nowWita->isSaturday() ? 'Sabtu' : 'Minggu';
            return back()->withErrors(['error' => "Hari ini {$dayName} — hari libur, tidak bisa melakukan absensi."]);
        }

        if (HolidayService::isNationalHoliday($nowWita)) {
            $holidayName = HolidayService::getHolidayName($nowWita) ?? 'Hari Libur Nasional';
            return back()->withErrors(['error' => "Hari ini libur nasional ({$holidayName}), tidak bisa melakukan absensi."]);
        }

        $todayAttendance = Attendance::where('intern_id', $intern->id)
            ->whereDate('date', $todayWita)
            ->first();

        if ($todayAttendance) {
            return back()->withErrors(['error' => 'Anda sudah melakukan absensi hari ini.']);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:hadir,izin,sakit'],

            'photo' => [
                Rule::requiredIf(fn () =>
                    $request->status === 'hadir' && !$request->filled('photo_data')
                ),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],

            'photo_data' => [
                Rule::requiredIf(fn () =>
                    $request->status === 'hadir' && !$request->hasFile('photo')
                ),
                'nullable',
                'string',
            ],

            'note' => [
                'required_if:status,izin',
                'required_if:status,sakit',
                'nullable',
                'string',
            ],

            'document' => [
                Rule::requiredIf(fn () => $request->status === 'izin'),
                'nullable',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
                'max:5120',
            ],
        ]);

        $data = [
            'intern_id' => $intern->id,
            'date' => $todayWita,
            'status' => $validated['status'],
        ];

        if ($validated['status'] === 'hadir') {
            $checkInStart = env('ATTENDANCE_CHECKIN_START', '08:00');
            $checkInEnd = env('ATTENDANCE_CHECKIN_END', '12:00');
            $currentTime = $nowWita->format('H:i');

            if ($currentTime < $checkInStart || $currentTime > $checkInEnd) {
                return back()->withErrors(['error' => 'Absensi masuk hanya diperbolehkan antara ' . $checkInStart . ' - ' . $checkInEnd . ' WITA.'])->withInput();
            }

            $photoPath = null;
            
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');

                $allowedMimeTypes = [
                    'image/jpeg',
                    'image/png',
                ];

                if (!in_array($photo->getMimeType(), $allowedMimeTypes)) {
                    return back()->withErrors([
                        'photo' => 'Tipe file tidak valid.'
                    ])->withInput();
                }

                if (!$photo->isValid() || $photo->getError() !== UPLOAD_ERR_OK) {
                    return back()->withErrors([
                        'photo' => 'File foto tidak valid.'
                    ])->withInput();
                }

                try {
                    $filename = Str::uuid() . '.jpg';
                    $path = 'private/attendance-photos/' . $filename;

                    $destinationPath = storage_path('app/private/attendance-photos');

                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    $manager = new ImageManager(new Driver());

                    $image = $manager->read($photo)
                        ->toJpeg(80);

                    Storage::disk('local')->put($path, (string) $image);

                    $photoPath = $path;
                } catch (\Exception $e) {
                    return back()->withErrors([
                        'photo' => 'Gagal upload foto.'
                    ])->withInput();
                }

            } elseif ($request->filled('photo_data')) {
                $imageData = $request->input('photo_data');

                if (!preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $imageData)) {
                    return back()->withErrors([
                        'photo' => 'Format gambar tidak valid.'
                    ])->withInput();
                }

                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $imageData = base64_decode($imageData);

                if ($imageData === false) {
                    return back()->withErrors([
                        'photo' => 'Data gambar rusak.'
                    ])->withInput();
                }

                try {
                    $filename = Str::uuid() . '.jpg';
                    $path = 'private/attendance-photos/' . $filename;

                    $destinationPath = storage_path('app/private/attendance-photos');

                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    $manager = new ImageManager(new Driver());

                    $image = $manager->read($imageData)
                        ->toJpeg(80);

                    Storage::disk('local')->put($path, (string) $image);

                    $photoPath = $path;
                } catch (\Exception $e) {
                    return back()->withErrors([
                        'photo' => 'Gagal upload foto.'
                    ])->withInput();
                }

            } else {
                return back()->withErrors([
                    'photo' => 'Foto wajib diisi untuk status hadir.'
                ])->withInput();
            }
            
            if ($photoPath) {
                $data['photo_path'] = $photoPath;
                // Simpan waktu check-in berdasarkan WITA
                $data['check_in'] = $nowWita;
            } else {
                return back()->withErrors(['photo' => 'Gagal menyimpan foto.'])->withInput();
            }
        } else {
            $data['note'] = $validated['note'] ?? null;

            if ($request->hasFile('document')) {
                $document = $request->file('document');

                $allowedMimeTypes = [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'image/jpeg',
                    'image/png',
                ];
                if (!in_array($document->getMimeType(), $allowedMimeTypes)) {
                    return back()->withErrors([
                        'document' => 'Tipe file tidak valid.'
                    ])->withInput();
                }

                if (!$document->isValid() || $document->getError() !== UPLOAD_ERR_OK) {
                    return back()->withErrors([
                        'document' => 'File dokumen tidak valid.'
                    ])->withInput();
                }

                try {
                    $extension = SafeUpload::extension($document, SafeUpload::ATTENDANCE_DOCUMENT, 'pdf');

                    $filename = Str::uuid() . '.' . $extension;
                    $path = 'private/attendance-documents/' . $filename;

                    $destinationPath = storage_path('app/private/attendance-documents');

                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    Storage::disk('local')->put(
                        'private/attendance-documents/' . $filename,
                        file_get_contents($document->getRealPath())
                    );

                    $data['document_path'] = $path;
                } catch (\Exception $e) {
                    return back()->withErrors([
                        'document' => 'Gagal upload dokumen.'
                    ])->withInput();
                }
            }

            $data['document_status'] = 'pending';
        }

        Attendance::create($data);

        return redirect()->route('intern.attendance.index')
            ->with('success', 'Absensi berhasil disimpan.');
    }

    public function checkOut(Request $request)
    {
        $nowWita = TimeService::nowWita();

        $validated = $request->validate([
            // 'photo' => ['required_without:photo_data', 'nullable', 'image', 'max:2048'],
            'photo' => [
                'required_without:photo_data',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'mimetypes:image/jpeg,image/png',
                'max:2048'
            ],
            'photo_data' => ['required_without:photo', 'nullable', 'string'],
        ]);

        $intern = Auth::user()->intern;
        
        $todayAttendance = Attendance::where('intern_id', $intern->id)
            ->whereDate('date', $nowWita->toDateString())
            ->where('status', 'hadir')
            ->first();

        if (!$todayAttendance) {
            return back()->withErrors(['error' => 'Anda belum melakukan absensi masuk hari ini.']);
        }

        if ($todayAttendance->check_out) {
            return back()->withErrors(['error' => 'Anda sudah melakukan absensi keluar hari ini.']);
        }

        if ($nowWita->format('H:i') < '16:00') {
            return back()->withErrors(['error' => 'Absensi keluar hanya bisa mulai pukul 16:00 WITA.']);
        }

        $photoCheckoutPath = null;
        
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');

            $allowedMimeTypes = [
                'image/jpeg',
                'image/png',
            ];

            if (!in_array($photo->getMimeType(), $allowedMimeTypes)) {
                return back()->withErrors([
                    'photo' => 'Tipe file tidak valid.'
                ])->withInput();
            }

            if (!$photo->isValid() || $photo->getError() !== UPLOAD_ERR_OK) {
                return back()->withErrors([
                    'photo' => 'File foto tidak valid.'
                ])->withInput();
            }

            try {
                $filename = Str::uuid() . '.jpg';
                $photoCheckoutPath = 'private/attendance-photos/' . $filename;

                $destinationPath = storage_path('app/private/attendance-photos');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $manager = new ImageManager(new Driver());

                $image = $manager->read($photo)
                    ->toJpeg(80);

                Storage::disk('local')->put(
                    $photoCheckoutPath,
                    (string) $image
                );
            } catch (\Exception $e) {
                return back()->withErrors([
                    'photo' => 'Gagal menyimpan foto checkout.'
                ])->withInput();
            }

        } elseif ($request->filled('photo_data')) {
            $imageData = $request->input('photo_data');

            if (!preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $imageData)) {
                return back()->withErrors([
                    'photo' => 'Format gambar tidak valid.'
                ])->withInput();
            }

            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return back()->withErrors([
                    'photo' => 'Data gambar rusak.'
                ])->withInput();
            }

            try {
                $filename = Str::uuid() . '.jpg';
                $photoCheckoutPath = 'private/attendance-photos/' . $filename;

                $destinationPath = storage_path('app/private/attendance-photos');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $manager = new ImageManager(new Driver());

                $image = $manager->read($imageData)
                    ->toJpeg(80);

                Storage::disk('local')->put(
                    $photoCheckoutPath,
                    (string) $image
                );
            } catch (\Exception $e) {
                return back()->withErrors([
                    'photo' => 'Gagal menyimpan foto checkout.'
                ])->withInput();
            }

        } else {
            return back()->withErrors([
                'photo' => 'Foto checkout wajib diisi.'
            ])->withInput();
        }

        $todayAttendance->update([
            'check_out' => $nowWita,
            'photo_checkout' => $photoCheckoutPath,
        ]);

        return redirect()->route('intern.attendance.index')
            ->with('success', 'Absensi keluar berhasil disimpan.');
    }

    /**
     * Serve private attendance photo with permission check
     */
    public function servePhoto(Request $request, $filename)
    {
        $intern = Auth::user()->intern;
        $filePath = storage_path('app/private/attendance-photos/' . $filename);

        if ($filename !== basename($filename)) {
            abort(404, 'File not found');
        }

        $token = $request->query('token');
        if ($token) {
            $cacheKey = "intern-photo-token:{$token}";
            $tokenData = Cache::get($cacheKey);

            if (
                !$tokenData ||
                ($tokenData['user_id'] ?? null) !== Auth::id() ||
                ($tokenData['filename'] ?? null) !== $filename
            ) {
                abort(404, 'File not found');
            }

        } else {

        }

        $attendance = Attendance::where(function ($query) use ($filename) {
                $query->where('photo_path', 'private/attendance-photos/' . $filename)
                    ->orWhere('photo_checkout', 'private/attendance-photos/' . $filename);
            })
            ->first();

        if (!$attendance) {
            abort(403, 'Unauthorized');
        }

        $this->authorize('view', $attendance);

        // Validate the file path to prevent directory traversal
        if (!str_starts_with(realpath($filePath) ?: '', realpath(storage_path('app/private/attendance-photos')) ?: '')) {
            abort(403, 'Unauthorized');
        }

        // Check if file exists
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->file($filePath, [
            'Content-Type' => mime_content_type($filePath),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Serve private attendance document with permission check
     */
    public function serveDocument($filename)
    {
        $intern = Auth::user()->intern;
        $filePath = storage_path('app/private/attendance-documents/' . $filename);

        if ($filename !== basename($filename)) {
            abort(404, 'File not found');
        }

        // Validate the file path to prevent directory traversal
        if (!str_starts_with(realpath($filePath) ?: '', realpath(storage_path('app/private/attendance-documents')) ?: '')) {
            abort(403, 'Unauthorized');
        }

        // Check if file exists
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        // Check if document belongs to authenticated user
        $attendance = Attendance::where('document_path', 'private/attendance-documents/' . $filename)
            ->first();

        if (!$attendance) {
            abort(403, 'Unauthorized');
        }

        $this->authorize('view', $attendance);

        return response()->download($filePath, null, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}