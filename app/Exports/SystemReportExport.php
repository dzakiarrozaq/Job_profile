<?php

namespace App\Exports;

use App\Models\User;
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

class SystemReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $employees;

    public function __construct($request)
    {
        $query = User::whereHas('gapRecords') 
                     ->with(['position', 'department', 'gapRecords']);

        if ($request->department_id && $request->department_id != 'all') {
            $query->where('department_id', $request->department_id);
        }
        if ($request->position_id && $request->position_id != 'all') {
            $query->where('position_id', $request->position_id);
        }
        if ($request->role_id && $request->role_id != 'all') {
            $query->whereHas('roles', fn($q) => $q->where('id', $request->role_id));
        }

        $this->employees = $query->get();
    }

    /**
     * Mengembalikan koleksi User
     */
    public function collection()
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan', 
            'Departemen', 
            'Jabatan', 
            'Kompetensi', 
            'Target', 
            'Aktual', 
            'Gap',
            'Status'
        ];
    }

    /**
     * Mapping: 1 User -> Banyak Baris (Array of Rows)
     */
    public function map($user): array
    {
        $rows = [];
        
        foreach ($user->gapRecords as $gap) {
            $rows[] = [
                $user->name,                 
                $user->department->name ?? '-', 
                $user->position->title ?? '-',  
                $gap->competency_name,        
                $gap->ideal_level,            
                $gap->current_level,          
                $gap->gap_value,              
                $gap->gap_value < 0 ? 'Kurang' : 'Aman' 
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

                        $sheet->mergeCells("C{$currentRow}:C{$endRow}");
                    }

                    $currentRow += $count;
                }
            },
        ];
    }
}