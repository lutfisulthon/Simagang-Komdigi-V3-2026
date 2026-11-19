<?php

namespace App\Services;

use App\Models\Certificate;
use Illuminate\Http\Response;
use setasign\Fpdi\Tcpdf\Fpdi;

/** Membuat PDF sertifikat magang dari template; dipakai bersama oleh admin, mentor, dan intern. */
class CertificatePdfService
{
    public function response(Certificate $certificate): Response
    {
        return response($this->render($certificate))
            ->header('Content-Type', 'application/pdf');
    }

    public function render(Certificate $certificate): string
    {
        $certificate->load(['intern', 'score']);

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        $templatePath = storage_path('app/public/certificates/SertifikatMagang.pdf');
        $pageCount = $pdf->setSourceFile($templatePath);

        /** HALAMAN 1 **/
        $pdf->AddPage('L');
        $tplId1 = $pdf->importPage(1);
        $pdf->useTemplate($tplId1, 0, 0, 297); // A4 landscape

        // Font Poppins disimpan di repo (bukan vendor/) agar tidak hilang saat composer install.
        foreach (['poppinsb', 'poppins', 'poppinsextralight'] as $font) {
            $pdf->AddFont($font, '', resource_path("fonts/tcpdf/{$font}.php"));
        }

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

        // Argumen pertama TCPDF::Output adalah nama file; 'S' harus di argumen kedua agar PDF dikembalikan sebagai string.
        return $pdf->Output('sertifikat.pdf', 'S');
    }
}
