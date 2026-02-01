<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Models\CompetenciesMaster; 
use App\Models\CompetencyKeyBehavior;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\ToCollection;

class CompetenciesImport implements WithMultipleSheets, SkipsUnknownSheets
{
    public function sheets(): array
    {
        $sheets = [];

        // KITA GUNAKAN "ANONYMOUS CLASS"
        // Logika import langsung kita tanam di sini.
        // Tidak butuh file tambahan, tidak butuh composer dump-autoload.
        
        $importLogic = new class implements ToCollection {
            public function collection(Collection $rows)
            {
                $currentCategory = 'Teknis'; 
                $currentMasterId = null;
                $numberedNameCandidate = null;
                $waitingForDescription = false;

                foreach ($rows as $index => $row) {
                    $col0 = trim($row[0] ?? '');
                    $col1 = trim($row[1] ?? '');

                    if ($col0 === '' && $col1 === '') continue;

                    // 1. DETEKSI KATEGORI
                    $textCheck = str_replace([' ', '.', ',', '-', ':'], '', $col0);
                    if (ctype_upper($textCheck) && strlen($col0) > 3 && !preg_match('/^\d/', $col0) && !Str::contains(strtoupper($col0), 'DEFINISI') && !Str::contains(strtoupper($col0), 'LEVEL')) {
                        $currentCategory = $col0;
                        continue; 
                    }

                    // 2. DETEKSI CALON NAMA
                    if (preg_match('/^\d+\./', $col0)) {
                        $numberedNameCandidate = $col0;
                        continue; 
                    }

                    // 3. SIMPAN MASTER
                    if (Str::startsWith(strtolower($col0), 'definisi')) {
                        if ($numberedNameCandidate) {
                            $cleanName = trim(preg_replace('/^\d+\.\s*/', '', $numberedNameCandidate));
                            
                            $master = CompetenciesMaster::updateOrCreate(
                                ['competency_name' => $cleanName],
                                ['type' => $currentCategory, 'description' => '-']
                            );
                            
                            $currentMasterId = $master->id;
                            $waitingForDescription = true;
                            $numberedNameCandidate = null;
                            continue;
                        }
                    }

                    // 4. SIMPAN DESKRIPSI
                    if ($waitingForDescription && $currentMasterId && !empty($col0)) {
                        CompetenciesMaster::where('id', $currentMasterId)->update([
                            'description' => $col0
                        ]);
                        $waitingForDescription = false;
                        continue;
                    }

                    // 5. SIMPAN PERILAKU (FULL TEXT)
                    if (Str::contains(strtolower($col0), 'level') && $currentMasterId) {
                        $level = 0;
                        $txt = strtolower($col0);
                        if (str_contains($txt, 'level 1')) $level = 1;
                        elseif (str_contains($txt, 'level 2')) $level = 2;
                        elseif (str_contains($txt, 'level 3')) $level = 3;
                        elseif (str_contains($txt, 'level 4')) $level = 4;
                        elseif (str_contains($txt, 'level 5')) $level = 5;

                        if ($level > 0 && !empty($col1)) {
                            CompetencyKeyBehavior::updateOrCreate(
                                ['competency_master_id' => $currentMasterId, 'level' => $level],
                                ['behavior' => $col1]
                            );
                        }
                    }
                }
            }
        };

        // LOOPING SHEET
        // Kita batasi 20 sheet dulu agar server tidak crash karena memory
        for ($i = 0; $i <= 150; $i++) {
            $sheets[$i] = $importLogic; 
        }

        return $sheets;
    }

    public function onUnknownSheet($sheetName)
    {
        // Skip sheet kosong
    }
}