<?php

namespace App\Http\Controllers\Industri;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengajuan;
use App\Models\PengajuanDetail;
use Illuminate\Support\Facades\Auth;
use App\Services\PengajuanWhatsappService;
use Illuminate\Support\Facades\DB;
use App\Models\Lowongan;

class IndustriPengajuanController extends Controller
{
    /**
     * Redirect ke halaman buat profil jika industri belum melengkapi profil.
     */
    /** Pengajuan harus ditujukan ke lowongan milik industri yang sedang login. */
    private function ensureOwnsPengajuan(Pengajuan $pengajuan): void
    {
        $industriId = auth()->user()->industri?->id;

        abort_unless(
            $industriId !== null && $pengajuan->lowongan?->industri_id === $industriId,
            403,
            'Anda tidak memiliki akses ke pengajuan ini.'
        );
    }

    private function requireIndustriProfile(): \Illuminate\Http\RedirectResponse|null
    {
        if (! auth()->user()->industri) {
            return redirect()->route('industri.profile.create')
                ->with('warning', 'Anda harus melengkapi profil perusahaan terlebih dahulu sebelum dapat mengakses fitur ini.');
        }
        return null;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if ($redirect = $this->requireIndustriProfile()) {
            return $redirect;
        }

        // KALAU MAU DITAMBAHKAN JUGA UNTUK FULL AKSES:
        // $industri = Industri::where('nama_industri', 'BBLSDM Komdigi Makassar')->firstOrFail();
        $industri = auth()->user()->industri;


        $baseQuery = Pengajuan::with([
            'institusi',
            'lowongan'
        ])
        ->whereHas('lowongan', function ($query) use ($industri) {
            $query->where('industri_id', $industri->id);
        });

        // Filter pencarian
        if (request('search')) {
            $search = request('search');

            $baseQuery->where(function ($query) use ($search) {
                $query->where('no_surat', 'like', "%{$search}%")
                    ->orWhereHas('institusi', function ($q) use ($search) {
                        $q->where('nama_institusi', 'like', "%{$search}%");
                    });
            });
        }

        // Filter status
        if (request('status')) {
            $baseQuery->where('status', request('status'));
        }

        $pengajuanTabel = $baseQuery
            ->latest()
            ->get();

        // Hitung jumlah peserta per pengajuan
        $jumlahPeserta = PengajuanDetail::selectRaw('pengajuan_id, COUNT(*) as total_peserta')
            ->groupBy('pengajuan_id')
            ->pluck('total_peserta', 'pengajuan_id');

        foreach ($pengajuanTabel as $pengajuan) {
            $pengajuan->jumlah_peserta = $jumlahPeserta[$pengajuan->id] ?? 0;
        }

        // Statistik
        $totalPengajuan = $pengajuanTabel->count();
        $totalDiterima = $pengajuanTabel->where('status', 'approved')->count();
        $totalDitolak = $pengajuanTabel->where('status', 'rejected')->count();
        $totalMenunggu = $pengajuanTabel->where('status', 'pending')->count();
        $totalRevisi = $pengajuanTabel->where('status', 'revised')->count();

        return view('industri.pengajuan.index', compact(
            'pengajuanTabel',
            'jumlahPeserta',
            'totalPengajuan',
            'totalDiterima',
            'totalDitolak',
            'totalMenunggu',
            'totalRevisi'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id, PengajuanWhatsappService $whatsappService)
    {
        // Hanya pengajuan ke lowongan milik industri yang login; selain itu 404 (tidak membocorkan keberadaan).
        $pengajuan = Pengajuan::with(['details', 'institusi', 'lowongan', 'lowongan.industri'])
            ->whereHas('lowongan', fn ($q) => $q->where('industri_id', auth()->user()->industri?->id))
            ->findOrFail($id);

        $whatsapp = $whatsappService->payloadFor($pengajuan);

        return view('industri.pengajuan.show', compact('pengajuan', 'whatsapp'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function updateStatus(
        Request $request,
        Pengajuan $pengajuan,
        PengajuanWhatsappService $whatsappService
    ) {
        $this->ensureOwnsPengajuan($pengajuan);

        // Status final tidak boleh diubah lagi
        if (in_array($pengajuan->status, ['approved', 'rejected'], true)) {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status sudah final dan tidak dapat diubah.',
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', 'Status sudah final dan tidak dapat diubah.');
        }

        $statusFromRequest = $request->input('status');

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,pending,revised'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            // 'notify_whatsapp' => ['nullable', 'boolean'],
        ]);

        try {

            DB::transaction(function () use ($pengajuan, $validated) {

                // Jika approve dan berasal dari lowongan
                if (
                    $validated['status'] === 'approved' &&
                    !empty($pengajuan->lowongan_id)
                ) {

                    $lowongan = Lowongan::lockForUpdate()
                        ->find($pengajuan->lowongan_id);

                    if ($lowongan) {

                        $jumlahPeserta = $pengajuan->details()->count();

                        // Cek kuota
                        if ($jumlahPeserta > $lowongan->kuota_peserta) {
                            throw new \Exception(
                                "Kuota tidak mencukupi. Sisa kuota hanya {$lowongan->kuota_peserta} peserta."
                            );
                        }

                        // Kurangi kuota
                        $lowongan->decrement(
                            'kuota_peserta',
                            $jumlahPeserta
                        );

                        // Refresh data lowongan setelah decrement
                        $lowongan->refresh();

                        // Jika kuota habis, tutup lowongan
                        if ($lowongan->kuota_peserta <= 0) {
                            $lowongan->update([
                                'status' => 'ditutup',
                            ]);
                        }

                    }
                }

                $pengajuan->update([
                    'status' => $validated['status'],
                    'admin_note' => $validated['status'] === 'revised'
                        ? ($validated['admin_note'] ?? null)
                        : null,
                    // 'nomor_surat_balasan' => $validated['nomor_surat_balasan'] ?? null,
                ]);
            });

        } catch (\Throwable $e) {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        $message = match ($validated['status']) {
            'approved' => 'Pengajuan berhasil disetujui.',
            'rejected' => 'Pengajuan berhasil ditolak.',
            'revised' => 'Pengajuan ditandai perlu revisi.',
            'pending' => 'Status pengajuan dikembalikan ke pending.',
            default => 'Status berhasil diperbarui.',
        };

        $pengajuan->refresh();

        $whatsapp = $whatsappService->payloadFor(
            $pengajuan->load('institusi')
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $pengajuan->status,
                // 'whatsapp' => $whatsapp,
            ]);
        }

        if (
            $request->boolean('notify_whatsapp') &&
            !empty($whatsapp['url'])
        ) {
            return redirect()->away($whatsapp['url']);
        }

        return redirect()
            ->route('industri.pengajuan.show', $pengajuan->id)
            ->with('success', $message);
    }

    public function generateSuratBalasan(Pengajuan $pengajuan)
    {
        $this->ensureOwnsPengajuan($pengajuan);

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
