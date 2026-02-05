<?php

namespace App\Imports;

use App\Models\Position;
use App\Models\CompetenciesMaster;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets; // Tambahkan ini agar aman
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TechnicalStandardImport implements WithMultipleSheets
{
    public function sheets(): array {
        return [0 => new SmartTechnicalSheetImport()];
    }
}

class SmartTechnicalSheetImport implements ToCollection
{
    protected $dbPositions;
    protected $dbCompetencies;

    public function __construct() {
        // 1. Cache Posisi
        $this->dbPositions = Position::all();
        
        // 2. Cache Master Kompetensi (Untuk Pencocokan)
        $this->dbCompetencies = CompetenciesMaster::all()->map(function($c) {
            return [
                'id' => $c->id,
                'name' => $c->competency_name, // Nama Asli
                'norm_name' => $this->normalize($c->competency_name) // Nama Bersih
            ];
        });
    }

    public function collection(Collection $rows)
    {
        $totalSaved = 0;
        $matchedJobs = 0;
        $notFoundComps = []; // Penampung kompetensi yang gagal match
        $rowIndex = 0;

        foreach ($rows as $row) {
            $rowIndex++;
            
            // Skip Header & Baris Kosong
            $excelTitle = trim($row[1] ?? ''); // Kolom B (Job Title)
            if ($rowIndex === 1 || empty($excelTitle) || strtolower($excelTitle) === 'job title') continue;

            // 1. Cari Jabatan (Fuzzy Match)
            $positionId = $this->findBestMatchPosition($excelTitle);

            if ($positionId) {
                $matchedJobs++;
                
                // 2. Loop Kolom Kompetensi (Mulai index 6 / Kolom G)
                // Pola: [6]=Komp, [7]=Lvl, [8]=Komp, [9]=Lvl...
                $maxCol = count($row); 
                
                for ($i = 6; $i < $maxCol; $i += 2) {
                    $cName = trim($row[$i] ?? '');
                    $rawLvl = $row[$i + 1] ?? null;

                    if (!empty($cName) && $cName !== '-') {
                        
                        // 3. SMART MATCH KOMPETENSI (Cari yang paling mirip di DB)
                        $compId = $this->findSmartCompetencyID($cName);

                        if ($compId) {
                            // Proses Level
                            if ($rawLvl !== null && $rawLvl !== '') {
                                $cleanLvlStr = preg_replace('/[^0-9.]/', '', (string)$rawLvl);
                                $idealLevel = (int) round(floatval($cleanLvlStr));

                                if ($idealLevel > 0) {
                                    DB::table('position_technical_standards')->updateOrInsert(
                                        ['position_id' => $positionId, 'competency_master_id' => $compId],
                                        ['ideal_level' => $idealLevel, 'updated_at' => now(), 'created_at' => now()]
                                    );
                                    $totalSaved++;
                                }
                            }
                        } else {
                            // Catat yang tidak ketemu
                            $notFoundComps[] = "$cName (di Jabatan: $excelTitle)";
                        }
                    }
                }
            }
        }

        // LOGGING HASIL
        Log::info("=== IMPORT SELESAI ===");
        Log::info("Jabatan Cocok: $matchedJobs");
        Log::info("Standar Tersimpan: $totalSaved");
        
        if (count($notFoundComps) > 0) {
            Log::warning("Total " . count($notFoundComps) . " kompetensi di Excel TIDAK DITEMUKAN di Master (Kemiripan < 85%):");
            // Tampilkan 10 contoh saja agar log tidak penuh
            Log::warning(implode(' | ', array_slice(array_unique($notFoundComps), 0, 10)));
        }
    }

    /**
     * Mencari Kompetensi dengan Toleransi Typo (Fuzzy Match)
     */
    private function findSmartCompetencyID($excelName) {
        $normExcel = $this->normalize($excelName);
        
        // 1. Coba Exact Match dulu (Cepat)
        $exact = $this->dbCompetencies->firstWhere('norm_name', $normExcel);
        if ($exact) return $exact['id'];

        // 2. Coba Similarity Match (Lambat tapi Cerdas)
        // Berguna untuk kasus: "Bussiness" vs "Business"
        $bestId = null;
        $highestPercent = 0;

        foreach ($this->dbCompetencies as $dbComp) {
            similar_text($normExcel, $dbComp['norm_name'], $percent);
            if ($percent > $highestPercent) {
                $highestPercent = $percent;
                $bestId = $dbComp['id'];
            }
        }

        // Toleransi 85% mirip (Sangat aman untuk typo ringan)
        return ($highestPercent >= 85) ? $bestId : null;
    }

    /**
     * Pencarian Posisi (Sudah terbukti berhasil sebelumnya)
     */
    private function findBestMatchPosition($excelTitle) {
        $excelTokens = $this->getTokens($excelTitle);
        if (empty($excelTokens)) return null;

        $bestMatchId = null; $highestScore = 0;
        foreach ($this->dbPositions as $dbPos) {
            $dbTokens = $this->getTokens($dbPos->title);
            $score = count(array_intersect($excelTokens, $dbTokens));
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatchId = $dbPos->id;
            }
        }
        return ($highestScore >= (count($excelTokens) * 0.7)) ? $bestMatchId : null;
    }

    private function getTokens($string) {
        return array_filter(explode(' ', $this->normalize($string)), fn($w) => strlen($w) > 1);
    }

    private function normalize($s) {
        if (!$s) return '';
        $s = strtolower($s);
        // Hapus karakter khusus & standarisasi singkatan
        $s = str_replace(["\xc2\xa0", '&', 'and', 'of', '/', '.', ',', '(', ')', '-'], ' ', $s);
        $s = str_replace(['senior manager', 'sm '], ' sm ', $s);
        $s = str_replace(['manager', 'mgr'], ' mgr ', $s);
        // Hapus spasi ganda
        return trim(preg_replace('/\s+/', ' ', $s));
    }
}