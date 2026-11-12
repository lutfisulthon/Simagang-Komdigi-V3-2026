<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Support\SafeUpload;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $intern = $user->intern;

        if (!$intern) {
            return redirect()->route('intern.dashboard')->withErrors(['error' => 'Data profil tidak ditemukan.']);
        }

        return view('intern.profile.show', compact('intern', 'user'));
    }

    public function edit()
    {
        $user = Auth::user();
        $intern = $user->intern;

        if (!$intern) {
            return redirect()->route('intern.dashboard')->withErrors(['error' => 'Data profil tidak ditemukan.']);
        }

        return view('intern.profile.edit', compact('intern', 'user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $intern = $user->intern;

        if (!$intern) {
            return redirect()->route('intern.dashboard')->withErrors(['error' => 'Data profil tidak ditemukan.']);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];

        // Only require password fields if password is being changed
        if ($request->filled('password')) {
            $rules['current_password'] = ['required', 'string'];
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $validated = $request->validate($rules);

        // Update user data
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        // Update intern data
        $intern->name = $validated['name'];
        $intern->phone = $validated['phone'];
        $intern->save();

        // Update password if provided
        if ($request->filled('password')) {
            // Verify current password
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama tidak sesuai.'])->withInput();
            }

            $user->password = Hash::make($validated['password']);
            $user->save();
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            
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
                    
                    // Delete old photo if exists
                    if ($intern->photo_path) {
                        $oldPhotoPath = storage_path('app/public/' . $intern->photo_path);
                        if (file_exists($oldPhotoPath)) {
                            @unlink($oldPhotoPath);
                        }
                    }
                    
                    // Move uploaded file
                    $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;
                    if ($photo->move($destinationPath, $filename) && file_exists($fullPath)) {
                        $intern->photo_path = 'photos/' . $filename;
                    } else {
                        return back()->withErrors(['photo' => 'Gagal memindahkan file foto.'])->withInput();
                    }
                } catch (\Exception $e) {
                    return back()->withErrors(['photo' => 'Terjadi kesalahan saat mengupload foto: ' . $e->getMessage()])->withInput();
                }
            } else {
                return back()->withErrors(['photo' => 'File foto tidak valid.'])->withInput();
            }
        }

        $intern->save();

        $message = 'Profil berhasil diperbarui.';
        if ($request->filled('password')) {
            $message .= ' Password telah diubah.';
        }
        if ($request->hasFile('photo')) {
            $message .= ' Foto profil telah diperbarui.';
        }

        return redirect()->route('intern.profile.show')->with('success', $message);
    }
}

