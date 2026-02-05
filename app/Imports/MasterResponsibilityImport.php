<?php

namespace App\Imports;

use App\Models\MasterResponsibility;
use App\Models\JobGrade;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MasterResponsibilityImport implements ToModel, WithHeadingRow
{
    private $jobGradeId;
    private $type;

    public function __construct($gradeName, $type)
    {
        // -------------------------------------------------------------
        // LOGIKA BARU: Cari berdasarkan ID dulu (Paling Akurat)
        // -------------------------------------------------------------
        
        // 1. Cek apakah ID "1", "2", dst ada di database?
        $grade = JobGrade::find($gradeName);

        // 2. Jika tidak ketemu by ID, baru cari by Name (Fuzzy Search)
        // Ini untuk jaga-jaga kalau inputnya "Band 1" bukan "1"
        if (!$grade) {
            $cleanName = preg_replace('/[^0-9]/', '', $gradeName); // Ambil angkanya saja
            
            $grade = JobGrade::where('name', $gradeName)                 // Cari persis
                     ->orWhere('name', 'LIKE', "%Band {$cleanName}%")   // Cari "Band 1..."
                     ->orWhere('name', 'LIKE', "%{$cleanName}%")        // Cari yang mengandung angka "1"
                     ->first();
        }

        // 3. Simpan hasilnya
        if ($grade) {
            $this->jobGradeId = $grade->id;
            // Log::info("Import Master: Sukses map grade '{$gradeName}' ke ID {$grade->id}");
        } else {
            Log::error("Import Master Gagal: Job Grade '{$gradeName}' tidak ditemukan di database (ID maupun Nama).");
            $this->jobGradeId = null;
        }

        $this->type = $type;
    }

    public function model(array $row)
    {
        // Skip jika Grade tidak ketemu atau Data kosong
        if (!$this->jobGradeId || empty($row['tanggung_jawab'])) {
            return null;
        }

        return new MasterResponsibility([
            'job_grade_id'     => $this->jobGradeId,
            'type'             => $this->type,
            'responsibility'   => $row['tanggung_jawab'],
            'expected_outcome' => $row['hasil_yang_diharapkan'] ?? '-',
        ]);
    }
    
    public function headingRow(): int
    {
        return 1;
    }
}