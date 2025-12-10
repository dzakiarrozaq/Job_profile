<?php

namespace App\Exports;

use App\Models\AuditLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AuditLogExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = AuditLog::with('user')->orderBy('timestamp', 'desc');

        if ($this->request->user_id && $this->request->user_id != 'all') {
            $query->where('user_id', $this->request->user_id);
        }
        if ($this->request->action && $this->request->action != 'all') {
            $query->where('action', $this->request->action);
        }

        return $query;
    }

    public function headings(): array
    {
        return ['Waktu', 'Pengguna', 'Email', 'Role', 'Aksi', 'Deskripsi', 'IP Address'];
    }

    public function map($log): array
    {
        return [
            $log->timestamp->format('Y-m-d H:i:s'),
            $log->user->name ?? 'Unknown',
            $log->user->email ?? '-',
            $log->user->role->name ?? '-', 
            $log->action,
            $log->description,
            $log->ip_address
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1F2937'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}