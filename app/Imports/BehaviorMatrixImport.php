<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BehaviorMatrixImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            // Pastikan tulisan STRUKTURAL & FUNGSIONAL sama persis dengan nama sheet di Excel
            'STRUKTURAL' => new BehaviorSheetImport('structural'),
            'FUNGSIONAL' => new BehaviorSheetImport('functional'),
        ];
    }
}