<?php

namespace App\Exports;

use App\Models\Intern;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonitoringExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    ShouldAutoSize,
    WithStyles
{
    protected array $filters;
    protected int $rowNumber = 0;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

   
    public function collection()
    {
        $query = Intern::with(['mentor', 'user']);

        $startOfMonth = null;
        $endOfMonth = null;

        if (!empty($this->filters['month'])) {
        $month = Carbon::createFromFormat('Y-m-d', $this->filters['month'] . '-01');

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth   = $month->copy()->endOfMonth();
        }

    
        if ($startOfMonth && $endOfMonth) {
            $query->where('start_date', '<=', $endOfMonth)
                ->where(function ($q) use ($startOfMonth) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $startOfMonth);
                });
        }

     
        if (!empty($this->filters['status']) && $this->filters['status'] !== 'all') {
            switch ($this->filters['status']) {

                case 'masuk':
                    $query->whereBetween('start_date', [
                        $startOfMonth,
                        $endOfMonth
                    ]);
                    break;

                case 'aktif':
                    $query->where('is_active', 1)
                        ->where('start_date', '<=', $endOfMonth)
                        ->where(function ($q) use ($startOfMonth) {
                            $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $startOfMonth);
                        });
                    break;

                case 'akan_pelepasan':
                    $query->where('is_active', 1)
                        ->whereBetween('end_date', [
                            $startOfMonth,
                            $endOfMonth
                        ]);
                    break;

                case 'pelepasan':
                    $query->where('is_active', 0);
                    if ($startOfMonth && $endOfMonth) {
                        $query->whereBetween('updated_at', [
                            $startOfMonth,
                            $endOfMonth
                        ]);
                    }
                    break;
            }
        }


        if (!empty($this->filters['mentor_id'])) {
            $query->where('mentor_id', $this->filters['mentor_id']);
        }


        if (!empty($this->filters['institution'])) {
            $query->where('institution', $this->filters['institution']);
        }

        if (!empty($this->filters['team_id'])) {
            $query->where('team_id', $this->filters['team_id']);
        }

        if (!empty($this->filters['search'])) {
            $query->where('name', 'like', '%' . $this->filters['search'] . '%');
        }


        return $query
            ->orderByRaw("
                CASE
                    WHEN is_active = 1
                        AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                    THEN 0
                    WHEN is_active = 1 THEN 1
                    ELSE 2
                END
            ")
            ->orderBy('end_date')
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Anak Magang',
            'Kampus',
            'Jurusan',
            'Mentor',
            'Tim',
            'Tanggal Mulai',
            'Tanggal Rencana Pelepasan',
            'Status',
            'Email',
            'No HP',
            'Jenjang Pendidikan',
            'Tujuan Magang',
            'final Projek'
        ];
    }


    public function map($intern): array
    {
        $this->rowNumber++;

        // Status text (sinkron UI)
        if ($intern->is_active) {
            if ($intern->end_date &&
                $intern->end_date->between(now(), now()->addDays(30))) {
                $status = 'Akan Pelepasan';
            } else {
                $status = 'Aktif';
            }
        } else {
            $status = 'Pelepasan';
        }

        $finalProject = $intern->finalReports->isNotEmpty()
            ? $intern->finalReports
                ->map(function ($report) {
                    return asset('storage/' . $report->file_path);
                })
                ->implode("\n")
            : '-';

        return [
            $this->rowNumber,
            $intern->name,
            $intern->institution ?? '-',
            $intern->major ?? '-',
            $intern->mentor?->name ?? '-',
            $intern->team?->name ?? '-',
            $intern->start_date?->format('d/m/Y') ?? '-',
            $intern->end_date?->format('d/m/Y') ?? '-',
            $status,
            $intern->user?->email ?? '-',
            $intern->phone ?? '-',
            $intern->education_level ?? '-',
            $intern->purpose ?? '-',
            $finalProject,
        ];
    }

    public function title(): string
    {
        return 'Monitoring Magang';
    }


    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ];
    }
}
