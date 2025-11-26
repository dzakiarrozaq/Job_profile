<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CompetencyExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $employees;

    public function __construct()
    {
        $supervisor = Auth::user();
        
        $this->employees = User::where('manager_id', $supervisor->id)
                   ->whereHas('gapRecords') 
                   ->with(['position', 'gapRecords'])
                   ->get();
    }

    public function collection()
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'Jabatan',
            'Kompetensi',
            'Kode',
            'Target Level',
            'Level Aktual',
            'Gap',
            'Status'
        ];
    }

    public function map($user): array
    {
        $rows = [];
        
        foreach ($user->gapRecords as $gap) {
            $rows[] = [
                $user->name,               
                $user->position->title ?? '-', 
                $gap->competency_name,     
                $gap->competency_code,     
                $gap->ideal_level,         
                $gap->current_level,       
                $gap->gap_value,           
                $gap->gap_value < 0 ? 'Perlu Perbaikan' : 'Aman' // Kolom H
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4F46E5'], 
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalRows = $sheet->getHighestRow();
                
                
                $sheet->getStyle('A1:H' . $totalRows)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getStyle('E2:H' . $totalRows)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $currentRow = 2; 

                foreach ($this->employees as $employee) {
                    $count = $employee->gapRecords->count();

                    if ($count > 1) {
                        $endRow = $currentRow + $count - 1;

                        $sheet->mergeCells("A{$currentRow}:A{$endRow}");
                        
                        $sheet->mergeCells("B{$currentRow}:B{$endRow}");
                    }

                    $currentRow += $count;
                }
            },
        ];
    }
}