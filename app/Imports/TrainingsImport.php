<?php

namespace App\Imports;

use App\Models\Training;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TrainingsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Cek baris kosong
        if (empty($row['course_title'])) {
            return null;
        }

        return new Training([
            // --- MAPPING KOLOM (Coba beberapa kemungkinan nama header) ---
            
            // Nama Kompetensi
            'competency_name' => $row['nama_kompetensi'] ?? $row['kompetensi'] ?? null,

            // Course Title
            'title'           => $row['course_title'] ?? $row['judul_course'] ?? 'No Title',

            // Level (Course Level)
            'level'           => $row['course_level'] ?? $row['level'] ?? 'Basic',

            // Duration (Duration/ hours -> biasanya jadi duration_hours)
            'duration'        => $row['duration_hours'] ?? $row['duration'] ?? $row['durasi'] ?? 0,

            // Course Objective
            'objective'       => $row['course_objective'] ?? $row['objective'] ?? $row['tujuan'] ?? null,

            // Course Content
            'content'         => $row['course_content'] ?? $row['content'] ?? $row['materi'] ?? null,

            // --- NILAI DEFAULT ---
            'status'          => 'approved',
            'provider'        => 'Internal',
        ]);
    }
}