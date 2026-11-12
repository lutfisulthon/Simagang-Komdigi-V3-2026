<?php

namespace App\Http\Controllers\Institusi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengajuan;
use App\Models\Lowongan;
use App\Models\PengajuanDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Services\PengajuanWhatsappService;
use Illuminate\Support\Facades\DB;
use App\Support\SafeUpload;



class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengajuan::where(
            'institusi_id',
            Auth::user()->institusi->id
        );

        // filter nomor pengajuan
        if ($request->filled('search')) {
            $query->where('no_surat', 'like', '%' . $request->search . '%');
        }

        // filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // filter tanggal
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // ambil data
        $pengajuans = $query
            ->orderByRaw("status = 'pending' DESC")
            ->orderByDesc('created_at')
            ->get();

        // statistik
        $allPengajuans = Pengajuan::where(
            'institusi_id',
            Auth::user()->institusi->id
        )->get();

        $totalPengajuan = $allPengajuans->count();
        $totalPengajuanFilter = $pengajuans->count();
        $pengajuanPending = $allPengajuans->where('status', 'pending')->count();
        $pengajuanApproved = $allPengajuans->where('status', 'approved')->count();
        $pengajuanRejected = $allPengajuans->where('status', 'rejected')->count();
        $pengajuanRevised = $allPengajuans->where('status', 'revised')->count();

        return view('institusi.pengajuan.index', compact(
            'pengajuans',
            'totalPengajuan',
            'pengajuanPending',
            'pengajuanApproved',
            'pengajuanRejected',
            'pengajuanRevised',
            'totalPengajuanFilter'
        ));
    }
    
    public function create(Lowongan $lowongan)
    {
        return view('institusi.pengajuan.create', compact('lowongan'));
    }

    public function store(Request $request)
    {
        // dd([
        //     'hasFile' => $request->hasFile('surat_magang'),
        //     'file' => $request->file('surat_magang'),
        //     'error' => $request->file('surat_magang')?->getError(),
        //     'size' => $request->file('surat_magang')?->getSize(),
        // ]);
        $request->validate([
            'surat_magang' => 'required|file|mimes:pdf',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'keperluan' => 'required|string',
            'tujuan_surat' => 'required|string',
            'lowongan_id' => 'nullable|exists:lowongans,id',

            'no_surat' => [
                'required',
                'string',
                Rule::unique('pengajuans', 'no_surat')
                    ->where('institusi_id', Auth::user()->institusi->id),
            ],

            'name' => 'required|array|min:1',
            'name.*' => 'required|string|max:255',

            'email' => 'required|array',
            'email.*' => 'required|email',

            'jurusan' => 'required|array',
            'jurusan.*' => 'required|string|max:255',

            'jenis_kelamin' => 'required|array',
            'jenis_kelamin.*' => 'required|in:L,P',

            'no_telp' => 'required|array',
            'no_telp.*' => 'required|string|max:30',

            'soft_skill.*' => 'nullable|string',
            'hard_skill.*' => 'nullable|string',
        ], [
            'no_surat.unique' =>
                'Nomor surat ini sudah pernah digunakan oleh institusi Anda. Silakan gunakan nomor surat yang berbeda.',
        ]);

        try {

            DB::beginTransaction();

            // ==================================================
            // VALIDASI KUOTA LOWONGAN
            // ==================================================
            if ($request->filled('lowongan_id')) {

                $lowongan = Lowongan::find($request->lowongan_id);

                if (!$lowongan) {
                    throw new \Exception('Lowongan tidak ditemukan.');
                }

                if ($lowongan->status !== 'dibuka') {
                    throw new \Exception('Lowongan sudah ditutup.');
                }

                $jumlahPeserta = count($request->name);

                if ($jumlahPeserta > $lowongan->kuota_peserta) {
                    throw new \Exception(
                        "Jumlah peserta yang diajukan ({$jumlahPeserta} orang) melebihi kuota yang tersedia ({$lowongan->kuota_peserta} orang)."
                    );
                }
            }

            // ==================================================
            // UPLOAD FILE
            // ==================================================
            $file = $request->file('surat_magang');

            $extension = SafeUpload::extension($file, ['pdf'], 'pdf');

            $storedFileName =
                'surat_' . time() . '_' . uniqid() . '.' . $extension;

            $destinationPath = storage_path('app/private/surat_magang');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            if (!$file->move($destinationPath, $storedFileName)) {
                throw new \Exception('Gagal menyimpan file surat magang.');
            }

            $path = 'surat_magang/' . $storedFileName;

            // ==================================================
            // SIMPAN PENGAJUAN
            // ==================================================
            $pengajuan = Pengajuan::create([
                'institusi_id' => Auth::user()->institusi->id,
                'surat_path' => $path,
                'status' => 'pending',
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'keperluan' => $request->keperluan,
                'no_surat' => $request->no_surat,
                'tujuan_surat' => $request->tujuan_surat,
                'lowongan_id' => $request->lowongan_id,
            ]);

            // ==================================================
            // SIMPAN DETAIL PESERTA
            // ==================================================
            foreach ($request->name as $i => $nama) {

                PengajuanDetail::create([
                    'pengajuan_id' => $pengajuan->id,
                    'nama' => $nama,
                    'email' => $request->email[$i],
                    'jurusan' => $request->jurusan[$i],
                    'jenis_kelamin' => $request->jenis_kelamin[$i],
                    'no_telp' => $request->no_telp[$i],
                    'soft_skill' => $request->soft_skill[$i] ?? null,
                    'hard_skill' => $request->hard_skill[$i] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('institusi.pengajuan.index')
                ->with('success', 'Pengajuan berhasil dikirim.');

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function edit(int $id)
    {
        $pengajuan = Pengajuan::with('details')
            ->where('institusi_id', Auth::user()->institusi->id)
            ->findOrFail($id);

        if ($pengajuan->status !== 'revised') {
            return redirect()
                ->route('institusi.pengajuan.index')
                ->with('error', 'Pengajuan hanya bisa diedit ketika status revisi. Pengajuan dengan status pending, approved, atau rejected tidak dapat diubah.');
        }

        return view('institusi.pengajuan.edit', compact('pengajuan'));
    }

    public function update(Request $request, int $id)
    {
        $pengajuan = Pengajuan::where('institusi_id', Auth::user()->institusi->id)->findOrFail($id);

        if ($pengajuan->status !== 'revised') {
            return redirect()
                ->route('institusi.pengajuan.index')
                ->with('error', 'Pengajuan hanya bisa diedit ketika status revisi. Pengajuan dengan status pending, approved, atau rejected tidak dapat diubah.');
        }

        $request->validate([
            'surat_magang' => 'nullable|file|mimes:pdf',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'keperluan' => 'required|string',
            'no_surat' => ['required', 'string', Rule::unique('pengajuans', 'no_surat')->where('institusi_id', Auth::user()->institusi->id)->ignore($pengajuan->id)],
            'tujuan_surat' => 'required|string',

            // validasi array peserta
            'name.*' => 'required|string',
            'email.*' => 'required|email',
            'jurusan.*' => 'required|string',
            'jenis_kelamin.*' => 'required|in:L,P',
            'no_telp.*' => 'required|string',
            'soft_skill.*' => 'nullable|string',
            'hard_skill.*' => 'nullable|string',
        ], [
            'no_surat.unique' => 'Nomor surat ini sudah pernah digunakan oleh institusi Anda. Silakan gunakan nomor surat yang berbeda.',
        ]);

        // Handle file upload jika ada
        if ($request->hasFile('surat_magang')) {
            $file = $request->file('surat_magang');
            $fileName = $file->getClientOriginalName();
            $extension = SafeUpload::extension($file, ['pdf'], 'pdf');
            $storedFileName = 'surat_' . time() . '_' . uniqid() . '.' . $extension;
            $destinationPath = storage_path('app/private/surat_magang');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            if (!$file->move($destinationPath, $storedFileName)) {
                return back()->withErrors(['surat_magang' => 'Gagal menyimpan file.'])->withInput();
            }

            $path = 'surat_magang/' . $storedFileName;
            $pengajuan->surat_path = $path;
        }

        // Update data pengajuan
        $pengajuan->update([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'keperluan' => $request->keperluan,
            'no_surat' => $request->no_surat,
            'tujuan_surat' => $request->tujuan_surat,
            'surat_path' => $pengajuan->surat_path,
        ]);

        // Hapus detail lama
        $pengajuan->details()->delete();

        // Simpan detail baru
        foreach ($request->name as $i => $nama) {
            PengajuanDetail::create([
                'pengajuan_id' => $pengajuan->id,
                'nama' => $nama,
                'email' => $request->email[$i],
                'jurusan' => $request->jurusan[$i],
                'jenis_kelamin' => $request->jenis_kelamin[$i],
                'no_telp' => $request->no_telp[$i],
                'soft_skill' => $request->soft_skill[$i] ?? null,
                'hard_skill' => $request->hard_skill[$i] ?? null,
            ]);
        }

        return redirect()
            ->route('institusi.pengajuan.index')
            ->with('success', 'Pengajuan berhasil diperbarui');
    }

    public function show(int $id, PengajuanWhatsappService $whatsappService)
    {
        $pengajuan = Pengajuan::with(['details', 'institusi', 'lowongan', 'lowongan.industri'])
            ->where('institusi_id', Auth::user()->institusi->id)
            ->findOrFail($id);

        $whatsapp = $whatsappService->payloadFor($pengajuan);

        return view('institusi.pengajuan.show', compact('pengajuan', 'whatsapp'));
    }

    public function destroy(int $id)
    {
        $pengajuan = Pengajuan::where('institusi_id', Auth::user()->institusi->id)->findOrFail($id);

        // 🔥 Cek status dulu
        if ($pengajuan->status !== 'rejected' && $pengajuan->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'Pengajuan hanya bisa dihapus jika statusnya rejected atau pending.');
        }

        // Hapus relasi detail dulu
        $pengajuan->details()->delete();

        // Hapus pengajuan
        $pengajuan->delete();

        return redirect()
            ->route('institusi.pengajuan.index')
            ->with('success', 'Pengajuan berhasil dihapus.');
    }

    public function generateSuratBalasan(Pengajuan $pengajuan)
    {
        // Hanya institusi pemilik pengajuan yang boleh mencetak surat balasannya.
        abort_unless(
            Auth::user()->institusi?->id !== null && $pengajuan->institusi_id === Auth::user()->institusi->id,
            403,
            'Anda tidak memiliki akses ke pengajuan ini.'
        );

        $pengajuan->load('details');

        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Load template
        $templatePath = storage_path('app/public/balasan_surat/template_balasanSurat.pdf');
        $pdf->setSourceFile($templatePath);

        $pdf->AddPage();
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl, 0, 0, 210);

        $pdf->SetFont('Helvetica', '', 12);

        // Nomor surat
         $pdf->SetXY(43.8, 57.8);
        $pdf->Write(0, $pengajuan->nomor_surat_balasan ?? '-');

        //Tanggal
        $tanggalSurat= $pengajuan->updated_at;
        $pdf->SetXY(146, 57.8);
        $pdf->Write(0, "Makassar, " . $tanggalSurat->translatedFormat('d'). ' ' . bulanSingkat($tanggalSurat) . ' ' . $tanggalSurat->translatedFormat('Y')) ;

        // Tujuan surat
        $pdf->SetXY(18, 87.5); 
        $pdf->Write(0, $pengajuan->tujuan_surat);

        // Institusi
         $pdf->SetXY(18, 94.7); 
        if ($pengajuan->institusi->jenis_institusi === 'sekolah') {
            $pdf->Write(0, $pengajuan->institusi->nama_institusi );
        } else {
            $pdf->Write(0, "Fakultas ". $pengajuan->institusi->fakultas . " " . $pengajuan->institusi->nama_institusi);
        }

        // isi
       $tanggal = $pengajuan->created_at;

        $teks = "Sehubungan dengan Surat Permohonan Izin Magang No. {$pengajuan->no_surat} "
            . "tanggal "
            . $tanggal->translatedFormat('d') . ' ' . bulanSingkat($tanggal) . ' ' . $tanggal->translatedFormat('Y')
            . " yang diajukan oleh mahasiswa/siswa dari instansi Bapak/Ibu kepada kami, "
            . "dengan ini kami menyampaikan bahwa permohonan tersebut dapat kami setujui.";

        $pdf->SetFont('Helvetica', '', 12);
        $pdf->setCellHeightRatio(1.8);

        // paragraf
        $pdf->SetXY(18, 114);
        $pdf->MultiCell(173.3, 0, $teks, 0, 'J');

        // ttd
        $pdf->Image(
            storage_path('app/public/images/ttd_baru5.png'),
            117.5,
            226.2,
            80,
            50
        );
            
        // halaman 2
        $pdf->AddPage();
        $tpl2 = $pdf->importPage(2);
        $pdf->useTemplate($tpl2, 0, 0, 210);

        // TABEL
        $pdf->SetFont('Helvetica', '', 12);

        // posisi awal tabel
        $startY = 77;
        $pdf->SetXY(24, $startY);

        // lebar kolom 
        $wNo = 12;
        $wNama = 86;
        $wJurusan = 65;

        // ================= HEADER =================
        $pdf->SetFont('Helvetica', 12);
        $pdf->SetX(24); 
        $pdf->Cell($wNo, 8, 'No', 1, 0, 'C');
        $pdf->Cell($wNama, 8, 'Nama', 1, 0, 'C');
        $pdf->Cell($wJurusan, 8, 'Jurusan', 1, 1, 'C');

        // ================= ISI =================
        $pdf->SetFont('Helvetica', '', 12);

        $no = 1;
        foreach ($pengajuan->details as $detail) {

            $pdf->SetX(24); 

            $pdf->Cell($wNo, 7, $no++, 1, 0, 'C');
            $pdf->Cell($wNama, 7, ' ' . $detail->nama, 1);
            $pdf->Cell($wJurusan, 7, ' ' . $detail->jurusan ?? '-', 1);
            
            $pdf->Ln();
        }
        
        return response($pdf->Output('surat_balasan.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }
}