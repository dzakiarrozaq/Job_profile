<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Position;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents; // <--- WAJIB: Untuk Event AfterImport
use Maatwebsite\Excel\Events\AfterImport;  // <--- WAJIB
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class UsersImport implements ToModel, WithHeadingRow, WithCalculatedFormulas, WithChunkReading, WithEvents
{
    private $orgCache = [];
    private $posCache = [];
    private $roleOrganikId = null;
    private $roleSupervisorId = null;

    // --- ARRAY SEMENTARA UNTUK MENYIMPAN HUBUNGAN NIK ---
    // Format: ['NIK_BAWAHAN' => 'NIK_ATASAN']
    private static $hierarchyMap = [];

    const TYPE_DEPT = 'department';
    const TYPE_SECT = 'section';
    const TYPE_UNIT = 'unit';

    public function model(array $row)
    {
        // 1. Validasi
        if (empty($row['nik'])) return null;
        $email = trim($row['email'] ?? '');
        if (empty($email) || $email == '#N/A' || $email == '#REF!') return null;

        // 2. Logika Organisasi & Posisi (Sama seperti sebelumnya)
        $parentId = null;
        $finalOrgId = null; 
        if (!empty($row['txt_dept'])) {
            $parentId = $this->getOrCreateOrg($row['txt_dept'], self::TYPE_DEPT, null);
            $finalOrgId = $parentId;
        }
        if (!empty($row['txt_sect'])) {
            $parentId = $this->getOrCreateOrg($row['txt_sect'], self::TYPE_SECT, $parentId);
            $finalOrgId = $parentId;
        }
        if (!empty($row['txt_biro'])) {
            $parentId = $this->getOrCreateOrg($row['txt_biro'], self::TYPE_UNIT, $parentId);
            $finalOrgId = $parentId;
        }

        // Posisi
        $positionId = null;
        if (!empty($row['position'])) {
            $posTitle = trim($row['position']);
            $posKey = strtoupper($posTitle) . '_' . ($finalOrgId ?? '0');
            
            if (isset($this->posCache[$posKey])) {
                $positionId = $this->posCache[$posKey];
            } else {
                $position = Position::where('title', $posTitle)->where('organization_id', $finalOrgId)->first();
                if (!$position) {
                    $position = Position::create(['title' => $posTitle, 'organization_id' => $finalOrgId]);
                } elseif ($finalOrgId && $position->organization_id !== $finalOrgId) {
                    $position->update(['organization_id' => $finalOrgId]);
                }
                $positionId = $position->id;
                $this->posCache[$posKey] = $positionId;
            }
        }

        // 3. User
        $birthDate = $this->transformDate($row['birth_date']);
        $hiringDate = $this->transformDate($row['hiring_date']);
        $passwordRaw = 'P@ssw0rd-' . $row['nik'];

        $user = User::updateOrCreate(
            ['email' => $email], 
            [
                'nik'               => $row['nik'],
                'name'              => $row['nama_karyawan'], 
                'company_name'      => $row['company_code'] ?? null,
                'password'          => Hash::make($passwordRaw),
                'position_id'       => $positionId,
                'birth_date'        => $birthDate,
                'hiring_date'       => $hiringDate,
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 4. Role (Sama seperti sebelumnya)
        if (!$this->roleOrganikId) $this->roleOrganikId = Role::firstOrCreate(['name' => 'Karyawan Organik'])->id;
        if (!$this->roleSupervisorId) $this->roleSupervisorId = Role::firstOrCreate(['name' => 'Supervisor'])->id;

        $rolesToAssign = [$this->roleOrganikId];
        $sfRaw = strtoupper(trim($row['sf'] ?? '')); 
        $jabatanRaw = strtoupper(trim($row['position'] ?? ''));
        $isBoss = false;

        if (Str::contains($sfRaw, 'STRUKTURAL')) {
            $isBoss = true;
        } else {
            $bossKeywords = ['MANAGER', 'MANAJER', 'MGR', 'SUPERVISOR', 'SPV', 'GM', 'SM ', 'HEAD', 'KEPALA', 'KA.'];
            foreach ($bossKeywords as $keyword) {
                if (str_contains($jabatanRaw, $keyword)) {
                    $isBoss = true; 
                    break;
                }
            }
        }
        if ($isBoss) $rolesToAssign[] = $this->roleSupervisorId;
        $user->roles()->syncWithoutDetaching($rolesToAssign);

        // =============================================================
        // 5. REKAM HIERARKI (TAMPUNG DULU)
        // =============================================================
        // Kita simpan mapping: NIK 606 melapor ke Atasan NIK 944
        if (!empty($row['id_user_atasan']) && $row['id_user_atasan'] != '0') {
            // Self::$hierarchyMap['NIK_ANAK'] = 'NIK_BAPAK';
            self::$hierarchyMap[$row['nik']] = $row['id_user_atasan'];
        }

        return $user;
    }

    // =============================================================
    // EVENT: JALAN SETELAH SEMUA IMPORT SELESAI
    // =============================================================
    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                
                // 1. Ambil semua mapping yang sudah kita kumpulkan
                $map = self::$hierarchyMap;

                if (empty($map)) return;

                // 2. Loop mapping tersebut untuk update database
                // Ini akan menghubungkan Jabatan Bawahan -> ke Jabatan Atasan
                
                foreach ($map as $childNik => $bossNik) {
                    // Cari User Bawahan & User Atasan berdasarkan NIK
                    $childUser = User::with('position')->where('nik', $childNik)->first();
                    $bossUser  = User::with('position')->where('nik', $bossNik)->first();

                    // Syarat: Keduanya harus ada & Keduanya harus punya posisi
                    if ($childUser && $bossUser && $childUser->position && $bossUser->position) {
                        
                        // UPDATE: Set Posisi Atasan sebagai Parent dari Posisi Bawahan
                        // "Jabatan Saya" -> parent_id = "ID Jabatan Bos Saya"
                        
                        $childUser->position->update([
                            'parent_id' => $bossUser->position_id
                        ]);
                    }
                }
            },
        ];
    }

    // --- Helpers Org, Date, & Chunk ---
    private function getOrCreateOrg($name, $type, $parentId) {
        $name = trim($name);
        if ($name == '#N/A' || $name == '0' || $name == '') return $parentId;
        $key = strtoupper($name) . '_' . ($parentId ?? 'ROOT');
        if (isset($this->orgCache[$key])) return $this->orgCache[$key];
        $org = Organization::where('name', $name)->where('type', $type)->first();
        if (!$org) $org = Organization::create(['name' => $name, 'parent_id' => $parentId, 'type' => $type]);
        elseif ($parentId && $org->parent_id !== $parentId) $org->update(['parent_id' => $parentId]);
        $this->orgCache[$key] = $org->id;
        return $org->id;
    }

    private function transformDate($value) {
        if (empty($value) || $value == '#N/A') return null;
        try {
            if (is_numeric($value)) return Date::excelToDateTimeObject($value)->format('Y-m-d');
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) { return null; }
    }

    public function chunkSize(): int { return 1000; }
}