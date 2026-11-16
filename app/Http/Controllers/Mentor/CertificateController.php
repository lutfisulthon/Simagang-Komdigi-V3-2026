<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateScore;
use App\Models\Intern;
use App\Models\Attendance;
use App\Models\MicroSkillSubmission;
use App\Models\MicroSkill;
use App\Models\Mentor;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    /** Profil mentor yang sedang login; akun tanpa profil mentor tidak boleh mengelola sertifikat. */
    private function mentor(): Mentor
    {
        $mentor = Auth::user()?->mentor;
        abort_unless($mentor, 403);

        return $mentor;
    }

    /** Mentor hanya boleh mengelola sertifikat intern bimbingannya sendiri. */
    private function authorizeIntern(int $internId): void
    {
        abort_unless($this->mentor()->interns()->whereKey($internId)->exists(), 403, 'Intern ini bukan bimbingan Anda.');
    }

    /**
     * List sertifikat
     */
    public function index()
    {
        $certificates = Certificate::with(['intern', 'score'])
            ->whereIn('intern_id', $this->mentor()->interns()->select('interns.id'))
            ->latest()
            ->get();
        // return view('mentor.certificate.create', compact('certificates'));
    }

    /**
     * Form tambah sertifikat
     */
    public function create(Request $request)
    {
        $interns = $this->mentor()->interns()->orderBy('name')->get();
        $selectedIntern = null;
        $certificate = null;
        $mode = 'create';

        if ($request->has('intern_id')) {
            $this->authorizeIntern((int) $request->intern_id);

            $selectedIntern = Intern::with(['certificate.score'])
                ->find($request->intern_id);

            if ($selectedIntern && $selectedIntern->certificate) {
                $certificate = $selectedIntern->certificate;
                $mode = 'edit';
            }

            // hitung persentase kehadiran seperti pada Attendance controller
            if ($selectedIntern) {
                $totalAttendance = Attendance::where('intern_id', $selectedIntern->id)->count();
                $totalPresent = Attendance::where('intern_id', $selectedIntern->id)
                    ->where('status', 'hadir')
                    ->count();

                $attendancePercentage = $totalAttendance > 0
                    ? round(($totalPresent / $totalAttendance) * 100)
                    : 0;

                $selectedIntern->attendance_percentage = $attendancePercentage;

                // hitung jumlah microskill yang sudah disetujui (completed)
                $completedMicroSkills = MicroSkillSubmission::where('intern_id', $selectedIntern->id)
                    ->where('status', 'approved')
                    ->count();

                // total microskill available
                $totalMicroSkills = MicroSkill::count();

                // assign computed values to pass to view
                $selectedIntern->micro_skill_completed = $completedMicroSkills;
                $selectedIntern->micro_skill_total = $totalMicroSkills;
            }
        }

        // compute default micro_skill value to show in form (count of completed)
        $microSkillDefault = null;
        if ($selectedIntern) {
            $microSkillDefault = $selectedIntern->micro_skill_completed ?? 0;
        }

        return view('mentor.certificate.create', compact(
            'interns',
            'selectedIntern',
            'certificate',
            'mode',
            'microSkillDefault'
        ));
    }


    /**
     * Simpan sertifikat + nilai
     */
    public function store(Request $request)
    {
        $request->validate([
            'intern_id' => 'required|exists:interns,id',
            'issue_date' => 'required|date',

            'discipline_attendance' => 'required|integer|min:0|max:100',
            'responsibility' => 'required|integer|min:0|max:100',
            'teamwork_communication' => 'required|integer|min:0|max:100',
            'technical_skill' => 'required|integer|min:0|max:100',
            'work_ethic' => 'required|integer|min:0|max:100',
            'initiative_creativity' => 'required|integer|min:0|max:100',
        ]);

        $this->authorizeIntern((int) $request->intern_id);

        /** Simpan sertifikat */
        $certificate = Certificate::create([
            'intern_id' => $request->intern_id,
            'certificate_number' => $this->generateCertificateNumber(),
            'issue_date' => $request->issue_date,
        ]);

        /** Simpan nilai */
        CertificateScore::create([
            'certificate_id' => $certificate->id,
            'discipline_attendance' => $request->discipline_attendance,
            'responsibility' => $request->responsibility,
            'teamwork_communication' => $request->teamwork_communication,
            'technical_skill' => $request->technical_skill,
            'work_ethic' => $request->work_ethic,
            'initiative_creativity' => $request->initiative_creativity,
            // microskill value is computed from intern submissions (count of approved)
            'micro_skill' => MicroSkillSubmission::where('intern_id', $request->intern_id)
                ->where('status', 'approved')
                ->count(),
        ]);

        return redirect()
            ->route('mentor.report.index')
            ->with('success', 'Sertifikat berhasil dibuat');
    }

    public function update(Request $request, Certificate $certificate)
    {
        $this->authorizeIntern((int) $certificate->intern_id);

        // Jika hari ini sama ATAU sudah lewat dari tanggal terbit
        if (now()->startOfDay()->greaterThanOrEqualTo(
            $certificate->issue_date->startOfDay()
        )) {
            return redirect()
                ->back()
                ->with('error', 'Sertifikat tidak dapat diubah karena sudah diterbitkan.');
        }

        $request->validate([
            'issue_date' => 'required|date',
            'discipline_attendance' => 'required|integer|min:0|max:100',
            'responsibility' => 'required|integer|min:0|max:100',
            'teamwork_communication' => 'required|integer|min:0|max:100',
            'technical_skill' => 'required|integer|min:0|max:100',
            'work_ethic' => 'required|integer|min:0|max:100',
            'initiative_creativity' => 'required|integer|min:0|max:100',
            // micro_skill not accepted from user input anymore
        ]);

        $certificate->update([
            'issue_date' => $request->issue_date,
        ]);

        $certificate->score->update([
            'discipline_attendance' => $request->discipline_attendance,
            'responsibility' => $request->responsibility,
            'teamwork_communication' => $request->teamwork_communication,
            'technical_skill' => $request->technical_skill,
            'work_ethic' => $request->work_ethic,
            'initiative_creativity' => $request->initiative_creativity,
            'micro_skill' => MicroSkillSubmission::where('intern_id', $certificate->intern_id)
                ->where('status', 'approved')
                ->count(),
        ]);

        return redirect()
            ->route('mentor.report.index')
            ->with('success', 'Nilai sertifikat berhasil diperbarui');
    }


    /**
     * Detail sertifikat
     */
    public function show(Certificate $certificate)
    {
        // $certificate->load(['intern', 'score']);
        // return view('mentor.certificate.show', compact('certificate'));
    }

    /**
     * Generate nomor sertifikat otomatis
     */
    private function generateCertificateNumber()
    {
        return 'CERT-' . date('Y') . '-' . str_pad(
            Certificate::count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    public function print(Certificate $certificate)
    {
        $this->authorizeIntern((int) $certificate->intern_id);

        $certificate->load(['intern', 'score']);

        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        // /** @var \setasign\Fpdi\Tcpdf\Fpdi $pdf */
        $pdf->SetAutoPageBreak(false);

        $templatePath = storage_path('app/public/certificates/SertifikatMagang.pdf');
        $pageCount = $pdf->setSourceFile($templatePath);

        /** HALAMAN 1 **/
        $pdf->AddPage('L');
        $tplId1 = $pdf->importPage(1);
        $pdf->useTemplate($tplId1, 0, 0, 297); // A4 landscape

        $pdf->AddFont('poppinsb', '', 'poppinsb.php');
        $pdf->AddFont('poppins', '', 'poppins.php');
        $pdf->AddFont('poppinsextralight', '', 'poppinsextralight.php');

        $pdf->SetFont('poppinsb', '', 28);

        // Nama Intern
        $pdf->SetXY(0, 87);
        $pdf->Cell(297, 10, $certificate->intern->name, 0, 0, 'C');

        // durasi magang
        $pdf->SetTextColor(64, 64, 64);
        $pdf->SetFont('poppins', '', 12);
        $start = $certificate->intern->start_date;
        $end   = $certificate->intern->end_date;

        $duration1 =
            $start->translatedFormat('d') . ' ' . bulanSingkat($start) .
            ' s/d ' .
            $end->translatedFormat('d') . ' ' . bulanSingkat($end) . ' ' .
            $end->translatedFormat('Y');

        $pdf->SetXY(215, 106);
        $pdf->Write(0, $duration1);


        // Tanggal
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetXY(143, 142.6);
        $pdf->Write(0, $certificate->issue_date->translatedFormat('d F Y'));

        $pdf->Image(
            storage_path('app/public/images/ttd.jpg'),
            140,
            152,
            20,
            20
        );


        /** HALAMAN 2 **/
        if ($pageCount >= 2) {
            $pdf->AddPage('L');
            $tplId2 = $pdf->importPage(2);
            $pdf->useTemplate($tplId2, 0, 0, 297);

            $pdf->SetFont('poppins', '', 12);

            // IDENTITAS
            $pdf->SetXY(70, 63.5);
            $pdf->Write(0, $certificate->intern->name);

            $pdf->SetXY(70, 69.9);
            $pdf->Write(0, $certificate->intern->institution);

            $pdf->SetXY(70, 76.3);
            $pdf->Write(0, $certificate->intern->major);

            $start = $certificate->intern->start_date;
            $end   = $certificate->intern->end_date;

            $duration =
                $start->translatedFormat('d') . ' ' . bulanSingkat($start) .
                ' - ' .
                $end->translatedFormat('d') . ' ' . bulanSingkat($end) . ' ' .
                $end->translatedFormat('Y');

            $pdf->SetXY(70, 82.7);
            $pdf->Write(0, $duration);



            // ===== NILAI =====
            $startY = 103;
            $gap = 7.5;

            $pdf->SetXY(168, $startY);
            $pdf->Write(0, scoreToGrade($certificate->score->discipline_attendance));

            $pdf->SetXY(168, $startY + $gap);
            $pdf->Write(0, scoreToGrade($certificate->score->responsibility));

            $pdf->SetXY(168, $startY + ($gap * 2));
            $pdf->Write(0, scoreToGrade($certificate->score->teamwork_communication));

            $pdf->SetXY(168, $startY + ($gap * 3));
            $pdf->Write(0, scoreToGrade($certificate->score->technical_skill));

            $pdf->SetXY(168, $startY + ($gap * 4));
            $pdf->Write(0, scoreToGrade($certificate->score->work_ethic));

            $pdf->SetXY(168, $startY + ($gap * 5));
            $pdf->Write(0, scoreToGrade($certificate->score->initiative_creativity));


            $pdf->SetXY(136, $startY + ($gap * 5.9));
            $pdf->Write(0, 'Telah menyelesaikan ' . $certificate->score->micro_skill . ' Micro Skill');
        }

        return response($pdf->Output('S'))
            ->header('Content-Type', 'application/pdf');
    }

}
