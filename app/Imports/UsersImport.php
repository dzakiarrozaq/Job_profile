<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Position;
use App\Models\Organization;
use App\Models\Role;
use App\Models\JobGrade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class UsersImport implements WithMultipleSheets, WithEvents
{
    public function sheets(): array
    {
        Log::info('--- MULAI IMPORT DATA LENGKAP (KARYAWAN + LEVEL + HIERARKI + S/F) ---');
        
        return [
            // TAHAP 1: Import User, Jabatan, & Status S/F (Sheet "5000")
            0 => new EmployeeImport(),
            
            // TAHAP 2: Update Level/Band Jabatan (Sheet "add")
            // Pastikan sheet ini ada di urutan ke-2 di Excel Anda
            1 => new JobLevelImport(),
            
            // TAHAP 3: Sambungkan Hierarki Atasan (Sheet "Atasan")
            // Pastikan sheet ini ada di urutan ke-3 di Excel Anda
            2 => new HierarchyImport(),
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function(BeforeImport $event) {
                $sheetNames = $event->getReader()->getSheetNames();
                Log::info('Sheet yang tersedia:', $sheetNames);
            },
        ];
    }
}

// ============================================================================
// CLASS 1: EMPLOYEE IMPORT (Sheet Index 0 - "5000")
// Tugas: Bikin User, Organisasi, Posisi, dan Penetapan Role Supervisor
// ============================================================================
class EmployeeImport implements ToModel, WithHeadingRow, WithChunkReading, WithCalculatedFormulas
{
    private $orgCache = [];
    private $roleOrganikId;
    private $roleSupervisorId;
    private $processedRows = 0;

    public function __construct() {
        // Pastikan Role sudah ada di database
        $this->roleOrganikId = Role::firstOrCreate(['name' => 'Karyawan Organik'])->id;
        $this->roleSupervisorId = Role::firstOrCreate(['name' => 'Supervisor'])->id;
    }

    public function model(array $row)
    {
        $this->processedRows++;
        if ($this->processedRows % 500 == 0) Log::info("EmployeeImport: Memproses baris ke-{$this->processedRows}");

        try {
            // Validasi NIK Wajib Ada
            if (empty($row['nik'])) return null;
            
            // Validasi Email
            $email = trim($row['email'] ?? '');
            if (empty($email) || $email == '#N/A' || $email == '#REF!') {
                $email = $row['nik'] . '@sig.id'; 
            }

            // 1. Setup Organisasi
            $parentId = null;
            $finalOrgId = null; 
            // Urutan hierarki: Directorate -> Department -> Unit -> Section
            if (!empty($row['txt_dir']))  $parentId = $this->getOrCreateOrg($row['txt_dir'], 'directorate', null);
            if (!empty($row['txt_dept'])) $parentId = $this->getOrCreateOrg($row['txt_dept'], 'department', $parentId);
            if (!empty($row['txt_biro'])) $parentId = $this->getOrCreateOrg($row['txt_biro'], 'unit', $parentId);
            if (!empty($row['txt_sect'])) $parentId = $this->getOrCreateOrg($row['txt_sect'], 'section', $parentId);
            $finalOrgId = $parentId;

            // 2. Setup Posisi & Job Family (S/F)
            $positionId = null;
            $posTitle = ''; 

            if (!empty($row['position'])) {
                $posTitle = trim($row['position']);
                
                // Cek variasi header untuk Job Family (S/F)
                $rawSF = $row['s_f'] ?? $row['sf'] ?? $row['s_f_'] ?? null;
                $jobFamily = !empty($rawSF) ? trim($rawSF) : 'Fungsional'; 

                $position = Position::firstOrCreate(
                    ['title' => $posTitle, 'organization_id' => $finalOrgId],
                    [
                        'parent_id' => null, 
                        'job_grade_id' => null, 
                        'job_family' => $jobFamily 
                    ]
                );
                
                // Update Job Family jika berbeda (Penting untuk data lama)
                if ($position->job_family !== $jobFamily) {
                    $position->job_family = $jobFamily;
                    $position->save(); 
                }

                $positionId = $position->id;
            }

            // 3. Setup User / Update User
            $user = User::updateOrCreate(
                ['email' => $email], 
                [
                    'nik'               => $row['nik'],
                    'name'              => $row['nama_karyawan'], 
                    'company_name'      => $row['company_code'] ?? null,
                    // Default password format: P@ssw0rd-NIK
                    'password'          => Hash::make('P@ssw0rd-' . $row['nik']),
                    'position_id'       => $positionId,
                    'birth_date'        => $this->transformDate($row['birth_date']),
                    'hiring_date'       => $this->transformDate($row['hiring_date']),
                    'status'            => 'active',
                    'email_verified_at' => now(),
                ]
            );

            // ====================================================================
            // 4. Setup Role User (DUAL CHECK LOGIC)
            // ====================================================================
            $rolesToAssign = [$this->roleOrganikId]; // Default semua dapat Karyawan Organik
            
            // Ambil data untuk pengecekan
            $sf = strtoupper($row['s_f'] ?? $row['sf'] ?? '');
            $jobTitleUpper = strtoupper($posTitle);

            $isSupervisor = false;

            // Cek 1: Berdasarkan kolom S/F (Struktural)
            if (str_contains($sf, 'STRUKTURAL') || $sf === 'S') {
                $isSupervisor = true;
            }

            // Cek 2: Berdasarkan Kata Kunci di Nama Jabatan (Backup jika S/F kosong/salah)
            $keywords = ['MANAGER', 'MGR', 'SUPERINTENDENT', 'SUPERVISOR', 'SPV', 'HEAD', 'CHIEF', 'DIREKTUR', 'GM', 'VP', 'SECTION HEAD', 'DEPT HEAD'];
            
            foreach ($keywords as $key) {
                // Pastikan kata kuncinya match, misalnya "Manager" ada di "Production Manager"
                if (str_contains($jobTitleUpper, $key)) {
                    $isSupervisor = true;
                    break;
                }
            }

            // Jika terdeteksi sebagai Supervisor, tambahkan role Supervisor
            if ($isSupervisor) {
                $rolesToAssign[] = $this->roleSupervisorId;
            }
            
            // Sync Role (Tanpa menghapus role lain yang mungkin sudah di-set manual)
            $user->roles()->syncWithoutDetaching($rolesToAssign);
            // ====================================================================

            return $user;

        } catch (\Exception $e) {
            Log::error("Gagal Import NIK {$row['nik']}: " . $e->getMessage());
            return null;
        }
    }
    
    // Helper: Membuat Organisasi jika belum ada
    private function getOrCreateOrg($name, $type, $parentId) {
        $name = trim($name);
        if (empty($name) || $name == '#N/A' || $name == '0') return $parentId;
        
        $key = strtoupper($name) . '_' . ($parentId ?? 'ROOT');
        if (isset($this->orgCache[$key])) return $this->orgCache[$key];

        $org = Organization::firstOrCreate(
            ['name' => $name, 'type' => $type],
            ['parent_id' => $parentId]
        );
        // Update parent jika berubah (misal struktur baru)
        if ($parentId && $org->parent_id !== $parentId) $org->update(['parent_id' => $parentId]);
        
        return $this->orgCache[$key] = $org->id;
    }

    // Helper: Konversi Tanggal Excel
    private function transformDate($value) {
        try {
            if (empty($value) || $value == '#N/A') return null;
            if (is_numeric($value)) return Date::excelToDateTimeObject($value)->format('Y-m-d');
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) { return null; }
    }

    public function chunkSize(): int { return 1000; }
}

// ============================================================================
// CLASS 2: JOB LEVEL IMPORT (Sheet Index 1 - "add")
// Tugas: Update Job Grade (Band 1-5) ke Posisi
// ============================================================================
class JobLevelImport implements ToModel, WithHeadingRow, WithChunkReading, WithCalculatedFormulas
{
    public function headingRow(): int
    {
        return 6; // Sesuaikan dengan baris header di sheet "add" Excel Anda
    }

    public function model(array $row)
    {
        if (empty($row['position']) || empty($row['position_level'])) {
            return null;
        }

        try {
            $excelPosition = trim($row['position']);
            $rawLevel      = trim($row['position_level']); 

            // 1. Ambil Angka Saja (Misal "BOD-4" jadi "4")
            $gradeName = null;
            if (preg_match('/(\d+)/', $rawLevel, $matches)) {
                $gradeName = $matches[1];
            }

            // 2. Validasi Ketat (Hanya 1-5)
            if (!$gradeName || $gradeName < 1 || $gradeName > 5) {
                return null;
            }

            // 3. Cari Job Grade
            // Prioritas: Cari berdasarkan ID dulu (Paling Akurat)
            $jobGrade = JobGrade::find($gradeName);

            // Fallback: Jika tidak ketemu by ID, cari by Name
            if (!$jobGrade) {
                $jobGrade = JobGrade::where('name', 'LIKE', "%Band {$gradeName}%")->first();
            }
            
            if (!$jobGrade) {
                return null;
            }

            // 4. Update Posisi dengan Grade tersebut
            // Gunakan LIKE untuk antisipasi spasi/typo dikit pada nama posisi
            $positions = Position::where('title', 'LIKE', $excelPosition)->get();

            foreach ($positions as $pos) {
                if ($pos->job_grade_id !== $jobGrade->id) {
                    $pos->update(['job_grade_id' => $jobGrade->id]);
                }
            }

        } catch (\Exception $e) {
            Log::error("JobLevelImport Error: " . $e->getMessage());
        }

        return null;
    }

    public function chunkSize(): int { return 1000; }
}

// ============================================================================
// CLASS 3: HIERARCHY IMPORT (Sheet Index 2 - "Atasan")
// Tugas: Sambungkan Jabatan Bawahan ke Atasan
// ============================================================================
class HierarchyImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        // Pastikan kolom excel bernama "jabatan" dan "atasan1_jabatan"
        if (empty($row['jabatan']) || empty($row['atasan1_jabatan'])) return null;

        try {
            $childTitle  = trim($row['jabatan']);
            $parentTitle = trim($row['atasan1_jabatan']);

            // Cari Posisi Bawahan & Atasan
            $childPositions = Position::where('title', $childTitle)->get();
            $parentPosition = Position::where('title', $parentTitle)->first();

            // Jika Atasan belum ada di DB, buat baru (misal level Direksi/GM yang tidak ada di sheet user)
            if (!$parentPosition) {
                $parentPosition = Position::create([
                    'title' => $parentTitle,
                    'organization_id' => null // Organisasi bisa diset manual nanti
                ]);
            }

            // Sambungkan
            if ($parentPosition && $childPositions->count() > 0) {
                foreach ($childPositions as $child) {
                    // Cegah self-reference (Atasan diri sendiri)
                    if ($child->id !== $parentPosition->id) {
                        $child->update(['atasan_id' => $parentPosition->id]);
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error("Hierarchy Error: " . $e->getMessage());
        }

        return null;
    }

    public function chunkSize(): int { return 1000; }
}