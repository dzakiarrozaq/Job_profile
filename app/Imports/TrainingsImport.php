<?php

namespace App\Imports;

use App\Models\Training;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TrainingsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['course_title'])) {
            return null;
        }

        return new Training([
            'competency_name' => $row['nama_kompetensi'] ?? $row['kompetensi'] ?? null,
            'title'           => $row['course_title'] ?? $row['judul_course'] ?? 'No Title',
            'level'           => $row['course_level'] ?? $row['level'] ?? 'Basic',
            'duration'        => $row['duration_hours'] ?? $row['duration'] ?? $row['durasi'] ?? 0,
            'objective'       => $row['course_objective'] ?? $row['objective'] ?? $row['tujuan'] ?? null,
            'content'         => $row['course_content'] ?? $row['content'] ?? $row['materi'] ?? null,
            
            'delivery_method' => $row['delivery_method'] ?? $row['metode'] ?? null,
            
            'status'          => 'approved',
            'provider'        => 'Internal',
        ]);
    }
}