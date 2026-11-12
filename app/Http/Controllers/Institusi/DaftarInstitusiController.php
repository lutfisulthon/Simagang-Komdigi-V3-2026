<?php

namespace App\Http\Controllers\Institusi;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Institusi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DaftarInstitusiController extends Controller
{
    /**
     * 📝 Form tambah institusiDaftarInstitusiController
     */
    public function create()
    {
        return view('daftarInstitusi');
    }

    /**
     * 💾 Simpan data institusi + user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_admin' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required'],

            'nama_institusi' => ['required', 'string', 'max:255'],
            'jenis_institusi' => ['required', 'in:sekolah,kampus'],
            'nomor_identitas' => ['nullable', 'string'],
            'no_hp' => ['required', 'string'],
            'fakultas' => ['nullable', 'string'],
            'departemen' => ['nullable', 'string'],
        ]);

        // 1. simpan user
        $user = User::create([
            'name' => $validated['nama_admin'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'institusi',
        ]);

        // 2. simpan institusi
        Institusi::create([
            'user_id' => $user->id,
            'nama_institusi' => $validated['nama_institusi'],
            'jenis_institusi' => $validated['jenis_institusi'],
            'nomor_identitas' => $validated['nomor_identitas'] ?? null,
            'no_hp' => $validated['no_hp'],
            'fakultas' => $validated['fakultas'] ?? null,
            'departemen' => $validated['departemen'] ?? null,
        ]);

        return redirect()->route('login')
            ->with('success', 'Data institusi berhasil ditambahkan');
    }
}
