<?php

namespace App\Imports;

use App\Models\CompetenciesMaster;
use App\Models\CompetencyKeyBehavior;
use App\Models\MasterCompetency;
use App\Models\MasterKeyBehavior; 
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class BehaviorDefinitionImport implements ToCollection, WithStartRow
{
    // Mulai baca dari baris 3 (Data)
    public function startRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Index 0: Nama, 1: Definisi, 2: Perilaku
            
            // 1. Validasi Nama
            if (!isset($row[0]) || empty(trim($row[0]))) continue;
            $name = trim($row[0]);
            
            // 2. Ambil Definisi (Description)
            $desc = isset($row[1]) ? trim($row[1]) : '-';

            // 3. Simpan Header
            $competency = CompetenciesMaster::updateOrCreate(
                ['competency_name' => $name], 
                [
                    'description'     => $desc, // Pastikan kolom ini benar di DB Anda
                    'type'            => 'Perilaku', 
                    'competency_code' => $this->generateCode($name) 
                ]
            );

            // 4. Parsing Perilaku Kunci
            if (isset($row[2]) && !empty($row[2])) {
                $this->parseAndSaveKeyBehaviors($competency->id, $row[2]);
            }
        }
    }

    private function parseAndSaveKeyBehaviors($masterId, $text)
    {
        // Bersihkan data lama (Level 0)
        CompetencyKeyBehavior::where('competency_master_id', $masterId)
            ->where('level', 0) 
            ->delete();

        // Normalisasi Enter menjadi Spasi agar jadi satu baris panjang
        $oneline = str_replace(["\r\n", "\r", "\n", "_x000D_"], " ", $text);
        
        // Regex Baru: Menangkap angka+titik, meski TANPA SPASI setelahnya
        // Pola: Mencari "Angka+Titik/Kurung" ... ambil isinya ... sampai ketemu "Angka+Titik/Kurung" berikutnya
        preg_match_all('/\d+[\.\)]\s*(.*?)(?=\s*\d+[\.\)]|$)/u', $oneline, $matches);

        $behaviors = $matches[1] ?? [];

        // Fallback: Jika regex gagal (tidak ada nomor), coba pecah pakai Enter manual
        if (empty($behaviors)) {
             $lines = explode("\n", str_replace(["\r\n", "\r", "_x000D_"], "\n", $text));
             foreach($lines as $l) {
                 if(!empty(trim($l))) $behaviors[] = $l;
             }
        }

        foreach ($behaviors as $item) {
            $cleanItem = trim($item);
            // Bersihkan nomor di awal teks (misal "1.A" jadi "A")
            $cleanItem = preg_replace('/^[\d\-\)\.]+\s*/', '', $cleanItem);

            if (!empty($cleanItem)) {
                CompetencyKeyBehavior::create([
                    'competency_master_id' => $masterId,
                    'behavior'             => $cleanItem,
                    'level'                => 0, // Level 0 = Ciri Umum
                ]);
            }
        }
    }

    private function generateCode($name)
    {
        $words = explode(' ', $name);
        $code = '';
        foreach ($words as $w) {
            if (ctype_alnum($w)) $code .= strtoupper(substr($w, 0, 1));
        }
        return (strlen($code) < 2 ? strtoupper(substr($name, 0, 3)) : $code) . '-BEH'; 
    }
}