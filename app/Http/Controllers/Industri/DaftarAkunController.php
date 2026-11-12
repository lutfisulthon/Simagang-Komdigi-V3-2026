<?php

namespace App\Http\Controllers\Industri;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Institusi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class DaftarAkunController extends Controller
{
    /**
     * 🔍 Tampilkan semua institusi
     */
    public function index()
    {
        // $institusi = Institusi::with('user')->latest()->get();
        // return view('institusi.index', compact('institusi'));
    }

    /**
     * 📝 Form tambah institusiDaftarInstitusiController
     */
    public function create()
    {
        return view('industri.register_akun');
    }

    /**
     * 💾 Simpan data institusi + user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_admin' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'string', Password::defaults()],
        ]);

        // 1. simpan user
        $user = User::create([
            'name' => $validated['nama_admin'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'industri',
        ]);

        return redirect()->route('login')
            ->with('success', 'Data industri berhasil ditambahkan');
    }

    /**
     * 👁️ Detail institusi
     */
    public function show($id)
    {
        // $institusi = Institusi::with('user')->findOrFail($id);
        // return view('institusi.show', compact('institusi'));
    }

    /**
     * ✏️ Form edit
     */
    public function edit($id)
    {
        // $institusi = Institusi::with('user')->findOrFail($id);
        // return view('institusi.edit', compact('institusi'));
    }

    /**
     * 🔄 Update data
     */
    public function update(Request $request, $id)
    {
        // $institusi = Institusi::with('user')->findOrFail($id);

        // $validated = $request->validate([
        //     'nama_admin' => ['required', 'string', 'max:255'],
        //     'email' => ['required', 'email', 'unique:users,email,' . $institusi->user->id],
        //     'password' => ['nullable', 'confirmed'],

        //     'nama_institusi' => ['required', 'string', 'max:255'],
        //     'jenis_institusi' => ['required', 'in:sekolah,kampus'],
        //     'nomor_identitas' => ['nullable', 'string'],
        //     'no_hp' => ['required', 'string'],
        // ]);

        // // update user
        // $institusi->user->update([
        //     'name' => $validated['nama_admin'],
        //     'email' => $validated['email'],
        //     'password' => $validated['password']
        //         ? Hash::make($validated['password'])
        //         : $institusi->user->password,
        // ]);

        // // update institusi
        // $institusi->update([
        //     'nama_institusi' => $validated['nama_institusi'],
        //     'jenis_institusi' => $validated['jenis_institusi'],
        //     'nomor_identitas' => $validated['nomor_identitas'],
        //     'no_hp' => $validated['no_hp'],
        // ]);

        // return redirect()->route('institusi.index')
        //     ->with('success', 'Data berhasil diupdate');
    }

    /**
     * 🗑️ Hapus data
     */
    // public function destroy($id)
    // {
    //     $institusi = Institusi::findOrFail($id);

    //     // hapus user otomatis karena cascade
    //     $institusi->delete();

    //     return redirect()->route('institusi.index')
    //         ->with('success', 'Data berhasil dihapus');
    // }
}