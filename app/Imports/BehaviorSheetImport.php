<?php

namespace App\Imports;

use App\Models\CompetenciesMaster;
use App\Models\MasterCompetency;
use App\Models\JobGrade;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BehaviorSheetImport implements ToCollection, WithHeadingRow
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                // Mencari kolom yang mengandung kata 'band'
                if (str_contains(strtolower($key), 'band') && !empty($value)) {
                    
                    // 1. Ambil Angka saja dari Header (Contoh: 'band_1' -> '1')
                    $bandNumber = filter_var($key, FILTER_SANITIZE_NUMBER_INT);
                    
                    if (!$bandNumber) continue;

                    // 2. Cari Job Grade
                    $jobGrade = JobGrade::where('id', $bandNumber)
                                ->orWhere('name', $bandNumber)
                                ->orWhere('name', 'Band ' . $bandNumber)
                                ->first();

                    if (!$jobGrade) {
                        Log::warning("Import Matrix: Job Grade untuk Band $bandNumber tidak ditemukan.");
                        continue;
                    }

                    // 3. Pecah daftar kompetensi
                    $competencyList = preg_split('/\r\n|\r|\n/', $value);

                    foreach ($competencyList as $compName) {
                        $cleanName = trim($compName);
                        if (empty($cleanName)) continue;

                        // Cari Master Kompetensi
                        $master = CompetenciesMaster::where('competency_name', $cleanName)->first();

                        if ($master) {
                            // PERBAIKAN DI SINI: Gunakan updateOrInsert untuk Query Builder
                            DB::table('competency_matrices')->updateOrInsert(
                                [
                                    'job_grade_id'         => $jobGrade->id,
                                    'competency_master_id' => $master->id,
                                    'type'                 => $this->type,
                                ],
                                [
                                    'updated_at' => now(),
                                    'created_at' => now(),
                                ]
                            );
                        } else {
                            Log::info("Import Matrix: Kompetensi '$cleanName' tidak ditemukan di Master.");
                        }
                    }
                }
            }
        }
    }
}