<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\CertificatePdfService;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    /**
     * Intern hanya boleh mengunduh sertifikat miliknya sendiri, dan baru setelah tanggal terbit
     * (sama dengan syarat tombol "Unduh Sertifikat Magang" di dashboard).
     */
    public function print(Certificate $certificate, CertificatePdfService $pdf)
    {
        $intern = Auth::user()->intern;

        abort_unless($intern && (int) $certificate->intern_id === (int) $intern->id, 403);
        abort_if($certificate->issue_date->startOfDay()->isFuture(), 403, 'Sertifikat belum diterbitkan.');

        return $pdf->response($certificate);
    }
}
