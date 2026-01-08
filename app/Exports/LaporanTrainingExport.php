<?php

namespace App\Exports;

use App\Models\TrainingPlan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; 
use Maatwebsite\Excel\Concerns\WithStyles;     
use Maatwebsite\Excel\Concerns\WithColumnFormatting; 
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanTrainingExport implements 
    FromQuery, 
    WithHeadings, 
    WithMapping, 
    ShouldAutoSize, 
    WithStyles, 
    WithColumnFormatting
{
    use Exportable;

    protected $startDate;
    protected $endDate;
    protected $status;

    public function __construct($startDate, $endDate, $status)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
    }

    public function query()
    {
        $query = TrainingPlan::query()
            ->with(['user.position', 'items'])
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query;
    }

    public function map($plan): array
    {
        $item = $plan->items->first();

        return [
            $plan->created_at->format('Y-m-d'), // Format tanggal Excel friendly
            $plan->user->name,
            $plan->user->position->name ?? '-',
            $item->title ?? '-',
            $item->provider ?? 'Internal',
            $item->method ?? '-',
            $item->cost ?? 0, // Biarkan angka mentah, kita format di columnFormats()
            $this->formatStatus($plan->status),
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Karyawan',
            'Posisi',
            'Judul Pelatihan',
            'Provider',
            'Metode',
            'Biaya (Rp)',
            'Status',
        ];
    }

    /**
     * 1. STYLING (Warna Header, Border, Font)
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], 
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F46E5'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A1:H' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F2:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H2:H' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * FORMAT ANGKA & TANGGAL (Kolom G jadi Rupiah)
     */
    public function columnFormats(): array
    {
        return [
            'G' => '_("Rp"* #,##0_);_("Rp"* (#,##0);_("Rp"* "-"_);_(@_)', 
            'A' => NumberFormat::FORMAT_DATE_DDMMYYYY, 
        ];
    }

    private function formatStatus($status)
    {
        $labels = [
            'pending_supervisor' => 'Menunggu SPV',
            'pending_lp' => 'Verifikasi LP',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'completed' => 'Selesai',
        ];
        return $labels[$status] ?? ucfirst($status);
    }
}