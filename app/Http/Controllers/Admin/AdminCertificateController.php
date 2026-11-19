<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\CertificatePdfService;
use App\Models\CertificateScore;
use App\Models\Intern;
use App\Models\Attendance;
use App\Models\MicroSkillSubmission;
use App\Models\MicroSkill;
use Illuminate\Http\Request;

class AdminCertificateController extends Controller
{
    /**
     * List sertifikat
     */
    public function index()
    {
        $certificates = Certificate::with(['intern', 'score'])->latest()->get();
        // return view('admin.certificate.create', compact('certificates'));
    }

    /**
     * Form tambah sertifikat
     */
    public function create(Request $request)
    {
        $interns = Intern::orderBy('name')->get();
        $selectedIntern = null;
        $certificate = null;
        $mode = 'create';

        if ($request->has('intern_id')) {
            $selectedIntern = Intern::with(['certificate.score'])
                ->find($request->intern_id);

            if ($selectedIntern && $selectedIntern->certificate) {
                $certificate = $selectedIntern->certificate;
                $mode = 'edit';
            }
            // hitung persentase kehadiran seperti pada Mentor controller
            if ($selectedIntern) {
                $totalAttendance = Attendance::where('intern_id', $selectedIntern->id)->count();
                $totalPresent = Attendance::where('intern_id', $selectedIntern->id)
                    ->where('status', 'hadir')
                    ->count();

                $attendancePercentage = $totalAttendance > 0
                    ? round(($totalPresent / $totalAttendance) * 100)
                    : 0;

                $selectedIntern->attendance_percentage = $attendancePercentage;

                // hitung microskill yang disetujui
                $completedMicroSkills = MicroSkillSubmission::where('intern_id', $selectedIntern->id)
                    ->where('status', 'approved')
                    ->count();

                $selectedIntern->micro_skill_completed = $completedMicroSkills;
                $selectedIntern->micro_skill_total = MicroSkill::count();
            }
        }

        $microSkillDefault = null;
        if ($selectedIntern) {
            $microSkillDefault = $selectedIntern->micro_skill_completed ?? 0;
        }

        return view('admin.certificate.create', compact(
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
            // micro_skill computed automatically from approved micro skill submissions
        ]);

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
            'micro_skill' => MicroSkillSubmission::where('intern_id', $request->intern_id)
                ->where('status', 'approved')
                ->count(),
        ]);

        return redirect()
            ->route('admin.report.index')
            ->with('success', 'Sertifikat berhasil dibuat');
    }

    public function update(Request $request, Certificate $certificate)
    {

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
            // micro_skill computed automatically from approved micro skill submissions
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
            ->route('admin.report.index')
            ->with('success', 'Nilai sertifikat berhasil diperbarui');
    }


    /**
     * Detail sertifikat
     */
    public function show(Certificate $certificate)
    {
        // $certificate->load(['intern', 'score']);
        // return view('admin.certificate.show', compact('certificate'));
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

    public function print(Certificate $certificate, CertificatePdfService $pdf)
    {
        return $pdf->response($certificate);
    }

}
