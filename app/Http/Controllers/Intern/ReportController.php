<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\FinalReport;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index()
    {
        $intern = Auth::user()->intern;
        $report = $intern->finalReport;
        $testimonial = $report?->testimonial;

        return view('intern.report.index', compact('report', 'testimonial'));
    }

    private function isAllowedDomain($url)
    {
        $allowed = [
            'github.com',
            'github.io',
            'drive.google.com',
            'youtube.com',
            'youtu.be',
            'canva.com',
            'tableau.com',
        ];
        foreach ($allowed as $domain) {
            if (stripos($url, $domain) !== false) {
                return true;
            }
        }
        return false;
    }

    public function store(Request $request)
    {
        $intern = Auth::user()->intern;

        // Check if already has a report
        if ($intern->finalReport) {
            return back()->withErrors(['error' => 'Anda sudah mengupload laporan akhir.']);
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'project_files' => ['nullable', 'array', 'max:3'],
            'project_files.*' => ['file', 'mimes:zip,rar,7z,tar,gz', 'max:102400'],
            'project_links' => ['nullable', 'array', 'max:3'],
            'project_links.*' => ['nullable', 'url', 'max:1024'],
            'activities' => ['nullable', 'array'],
            'activities.*.description' => ['nullable', 'string', 'max:2000'],
        ]);

        // izin link dari domain tertentu
        $links = array_filter((array) $request->input('project_links', []));
        foreach ($links as $link) {
            $trimmedLink = trim($link);
            if (!$this->isAllowedDomain($trimmedLink)) {
                return back()->withErrors(['project_links' => 'Link project hanya boleh dari Link tertentu.'])->withInput();
            }
        }

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        
        if ($file->isValid() && $file->getError() === UPLOAD_ERR_OK) {
            try {
                $extension = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'pdf');
                $filename = 'report_' . time() . '_' . uniqid() . '.' . $extension;
                $destinationPath = storage_path('app/public/final-reports');
                
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                
                $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;
                if ($file->move($destinationPath, $filename) && file_exists($fullPath)) {
                    $filePath = 'final-reports/' . $filename;
                } else {
                    return back()->withErrors(['file' => 'Gagal menyimpan file.'])->withInput();
                }
            } catch (\Exception $e) {
                return back()->withErrors(['file' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
            }
        } else {
            return back()->withErrors(['file' => 'File tidak valid.'])->withInput();
        }

        $projectFiles = null;
        $projectFilePath = null;
        $projectFileName = null;
        if ($request->hasFile('project_files')) {
            $uploaded = $request->file('project_files');
            $projectFiles = [];
            $pdest = storage_path('app/public/projects');
            if (!file_exists($pdest)) mkdir($pdest, 0755, true);
            $count = 0;
            foreach ($uploaded as $pfile) {
                if (!$pfile->isValid()) continue;
                if ($count++ >= 3) break;
                $pext = $pfile->getClientOriginalExtension() ?: 'zip';
                $pname = 'project_' . time() . '_' . uniqid() . '.' . $pext;
                if ($pfile->move($pdest, $pname) && file_exists($pdest . DIRECTORY_SEPARATOR . $pname)) {
                    $path = 'projects/' . $pname;
                    $projectFiles[] = ['path' => $path, 'name' => $pfile->getClientOriginalName()];
                    // set first as legacy fields
                    if (is_null($projectFilePath)) {
                        $projectFilePath = $path;
                        $projectFileName = $pfile->getClientOriginalName();
                    }
                }
            }
        }

        FinalReport::create([
            'intern_id' => $intern->id,
            'file_path' => $filePath,
            'project_file' => $projectFilePath,
            'project_file_name' => $projectFileName,
            'project_files' => $projectFiles,
            'project_link' => null,
            'project_links' => array_values(array_filter((array) $request->input('project_links', []))),
            'activities' => $request->input('activities'),
            'include_task_recap' => $request->boolean('include_task_recap', true),
            'file_name' => $fileName,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return redirect()->route('intern.report.index')
            ->with('success', 'Laporan akhir berhasil diupload.');
    }

    public function update(Request $request, FinalReport $report)
    {
        $this->authorize('update', $report);

        // Allow update if status is pending, rejected, or needs revision
        if ($report->status === 'approved' && !$report->needs_revision) {
            return back()->withErrors(['error' => 'Laporan yang sudah disetujui dan tidak perlu revisi tidak dapat diubah.']);
        }

        $validated = $request->validate([
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'project_files' => ['nullable', 'array', 'max:3'],
            'project_files.*' => ['file', 'mimes:zip,rar,7z,tar,gz', 'max:102400'],
            'project_links' => ['nullable', 'array', 'max:3'],
            'project_links.*' => ['nullable', 'url', 'max:1024'],
            'activities' => ['nullable', 'array'],
            'activities.*.description' => ['nullable', 'string', 'max:2000'],
        ]);

        // Hanya izinkan link dari domain tertentu
        $links = array_filter((array) $request->input('project_links', []));
        foreach ($links as $link) {
            $trimmedLink = trim($link);
            if (!$this->isAllowedDomain($trimmedLink)) {
                return back()->withErrors(['project_links' => 'Link project hanya boleh dari Link tertentu.'])->withInput();
            }
        }

        // Handle main report file replacement
        if ($request->hasFile('file')) {
            // delete old report file
            if ($report->file_path) {
                $oldPath = storage_path('app/public/' . $report->file_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            
            if ($file->isValid() && $file->getError() === UPLOAD_ERR_OK) {
                try {
                    $extension = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'pdf');
                    $filename = 'report_' . time() . '_' . uniqid() . '.' . $extension;
                    $destinationPath = storage_path('app/public/final-reports');
                    
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
                    
                    $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;
                    if ($file->move($destinationPath, $filename) && file_exists($fullPath)) {
                        $filePath = 'final-reports/' . $filename;
                    } else {
                        return back()->withErrors(['file' => 'Gagal menyimpan file.'])->withInput();
                    }
                } catch (\Exception $e) {
                    return back()->withErrors(['file' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
                }
            } else {
                return back()->withErrors(['file' => 'File tidak valid.'])->withInput();
            }
        } else {
            $filePath = $report->file_path;
            $fileName = $report->file_name;
        }

        // Handle project files replacement (multiple)
        $projectFiles = is_array($report->project_files) ? $report->project_files : [];
        $projectFilePath = $report->project_file;
        $projectFileName = $report->project_file_name;

        // If legacy single project_file exists and no project_files array, include it so we can append new uploads
        if ($report->project_file && empty($projectFiles)) {
            $projectFiles[] = ['path' => $report->project_file, 'name' => $report->project_file_name];
        }

        if ($request->hasFile('project_files')) {
            // delete old project_files entries (we'll keep legacy single file reference if present as part of array)
            if (!empty($report->project_files) && is_array($report->project_files)) {
                foreach ($report->project_files as $pf) {
                    if (!empty($pf['path'])) {
                        $oldP = storage_path('app/public/' . $pf['path']);
                        if (file_exists($oldP)) @unlink($oldP);
                    }
                }
                // reset array; we'll rebuild preserving legacy single-file earlier added
                $projectFiles = array_values(array_filter($projectFiles));
            }

            $uploaded = $request->file('project_files');
            $pdest = storage_path('app/public/projects');
            if (!file_exists($pdest)) mkdir($pdest, 0755, true);
            $count = count($projectFiles);
            foreach ($uploaded as $pfile) {
                if (!$pfile->isValid()) continue;
                if ($count >= 3) break;
                $pext = $pfile->getClientOriginalExtension() ?: 'zip';
                $pname = 'project_' . time() . '_' . uniqid() . '.' . $pext;
                if ($pfile->move($pdest, $pname) && file_exists($pdest . DIRECTORY_SEPARATOR . $pname)) {
                    $path = 'projects/' . $pname;
                    $projectFiles[] = ['path' => $path, 'name' => $pfile->getClientOriginalName()];
                    $count++;
                    if (is_null($projectFilePath)) {
                        $projectFilePath = $path;
                        $projectFileName = $pfile->getClientOriginalName();
                    }
                }
            }
            // ensure no more than 3 stored
            if (count($projectFiles) > 3) {
                $projectFiles = array_slice($projectFiles, 0, 3);
            }
        }

        $report->update([
            'file_path' => $filePath,
            'project_file' => $projectFilePath,
            'project_file_name' => $projectFileName,
            'project_files' => $projectFiles,
            'project_link' => null,
            'project_links' => array_values(array_filter((array) $request->input('project_links', []))),
            'activities' => $request->input('activities'),
            'include_task_recap' => $request->boolean('include_task_recap', true),
            'file_name' => $fileName,
            'status' => 'pending',
            'needs_revision' => false, // Reset revision flag when resubmitted
            'submitted_at' => now(),
        ]);

        return redirect()->route('intern.report.index')
            ->with('success', 'Laporan akhir berhasil diperbarui.');
    }

    public function storeTestimonial(Request $request, FinalReport $report)
    {
        $this->authorize('update', $report);

        // Check if report is submitted
        if (!$report->submitted_at) {
            return back()->withErrors(['error' => 'Laporan harus disubmit terlebih dahulu.']);
        }

        $validated = $request->validate([
            'testimony' => ['required', 'string', 'min:20', 'max:1000'],
        ]);

        $testimonial = Testimonial::where('final_report_id', $report->id)->first();

        if ($testimonial) {
            $testimonial->update(['testimony' => $validated['testimony']]);
            $message = 'Testimoni berhasil diperbarui.';
        } else {
            Testimonial::create([
                'final_report_id' => $report->id,
                'intern_id' => $report->intern_id,
                'testimony' => $validated['testimony'],
            ]);
            $message = 'Testimoni berhasil disimpan.';
        }

        return back()->with('success_testimony', $message);
    }
}