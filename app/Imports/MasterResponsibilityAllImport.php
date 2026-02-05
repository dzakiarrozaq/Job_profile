<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
// PENTING: Jangan lupa baris ini agar script tahu class anaknya
use App\Imports\MasterResponsibilityImport; 

class MasterResponsibilityAllImport implements WithMultipleSheets
{
    private $sheetMap;

    // Terima data mapping dari Controller
    public function __construct($sheetMap)
    {
        $this->sheetMap = $sheetMap;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Loop mapping yang sudah dibuat di Controller
        foreach ($this->sheetMap as $sheetName => $data) {
            // $sheetName = Nama Sheet Asli di Excel
            // $data['band'] = Angka Band
            // $data['type'] = structural/functional/general
            
            $sheets[$sheetName] = new MasterResponsibilityImport($data['band'], $data['type']);
        }

        return $sheets;
    }
}