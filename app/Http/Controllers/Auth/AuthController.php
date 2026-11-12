<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Intern;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Support\SafeUpload;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'gender' => ['required', 'in:Laki-laki,Perempuan'],
            'education_level' => ['required', 'in:SMA/SMK,S1/D4'],
            'major' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:255'],
            'custom_institution' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'in:Magang,KKN Profesi,PKL,Praktek Industri,Magang Industri,Guru Magang Industri,Job on Training'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'photo' => ['required', 'image', 'max:2048'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'intern',
        ]);
        $user->assignRole('intern');

        // Handle photo upload - use direct move() method to avoid storage adapter issues
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            
            // Check if file upload was successful
            if ($photo->isValid() && $photo->getError() === UPLOAD_ERR_OK) {
                try {
                    // Generate unique filename
                    $extension = SafeUpload::extension($photo, SafeUpload::IMAGE, 'jpg');
                    $filename = 'photo_' . time() . '_' . uniqid() . '.' . $extension;
                    
                    // Use direct move method - most reliable on Windows
                    $destinationPath = storage_path('app/public/photos');
                    
                    // Create directory if not exists
                    if (!file_exists($destinationPath)) {
                        if (!mkdir($destinationPath, 0755, true)) {
                            return back()->withErrors(['photo' => 'Gagal membuat folder storage.'])->withInput();
                        }
                    }
                    
                    // Move uploaded file
                    $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;
                    if ($photo->move($destinationPath, $filename) && file_exists($fullPath)) {
                        $photoPath = 'photos/' . $filename;
                    } else {
                        return back()->withErrors(['photo' => 'Gagal memindahkan file foto. Pastikan folder storage dapat ditulis.'])->withInput();
                    }
                } catch (\Exception $e) {
                    return back()->withErrors(['photo' => 'Terjadi kesalahan saat mengupload foto: ' . $e->getMessage()])->withInput();
                }
            } else {
                $errorMsg = 'File foto tidak valid atau corrupt.';
                if ($photo->getError() !== UPLOAD_ERR_OK) {
                    $errorMsg .= ' Error code: ' . $photo->getError();
                }
                return back()->withErrors(['photo' => $errorMsg])->withInput();
            }
        } else {
            return back()->withErrors(['photo' => 'Foto wajib diupload.'])->withInput();
        }

        // Use custom institution if provided, otherwise use selected institution
        $institution = $validated['institution'];
        if ($institution === '__custom__' && !empty($validated['custom_institution'])) {
            $institution = $validated['custom_institution'];
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
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'photo_path' => $photoPath,
            'is_active' => true,
        ]);

        Auth::login($user);

        return redirect()->route('intern.dashboard');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Batasi tebakan password: maksimal 5 kali gagal per kombinasi email dan IP dalam 5 menit.
        $throttleKey = Str::transliterate(Str::lower($credentials['email']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user instanceof User && $user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            } elseif ($user instanceof User && $user->isMentor()) {
                return redirect()->route('mentor.dashboard');
            } elseif ($user instanceof User && $user->isIntern()) {
                return redirect()->route('intern.dashboard');
            } elseif ($user instanceof User && $user->isInstitusi()) {
                return redirect()->route('institusi.dashboard');
            } elseif ($user instanceof User && $user->isIndustri()) {
                return redirect()->route('industri.dashboard');
            }

            // Fallback safe redirect
            return redirect()->route('login');
        }

        RateLimiter::hit($throttleKey, 300);

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing')->withHeaders([
            'Clear-Site-Data' => '"cache"',
        ]);
    }
}
