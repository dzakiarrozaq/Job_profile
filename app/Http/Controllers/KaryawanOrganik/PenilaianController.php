<?php

namespace App\Http\Controllers\KaryawanOrganik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Gunakan Facade biar lebih aman
use App\Models\GapRecord;
use App\Models\EmployeeProfile;
use App\Models\JobCompetency;

class PenilaianController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $position = $user->position;
        
        // Load JobProfile beserta relasi kompetensi -> master -> key behaviors
        // Pastikan model JobCompetency Anda memiliki relasi 'competency' ke CompetenciesMaster
        $jobProfile = $position?->jobProfile?->load(['competencies.competency.keyBehaviors']);

        if (!$position || !$jobProfile) {
            return redirect()->route('dashboard')
                ->with('error', 'Profil Kompetensi belum diatur untuk posisi Anda.');
        }

        $competencies = $jobProfile->competencies;

        $existingGaps = GapRecord::where('user_id', $user->id)
            ->where('job_profile_id', $jobProfile->id)
            ->get();

        $dataPenilaian = $competencies->map(function ($comp) use ($existingGaps) {
            $gap = $existingGaps->firstWhere('competency_name', $comp->competency_name);

            return (object) [
                'id'              => $comp->id, 
                'competency_name' => $comp->competency_name,
                'competency_code' => $comp->competency_code ?? '-',
                
                // TAMBAHKAN BARIS INI:
                // Kita ambil tipe dari tabel master (melalui relasi competency)
                'type'            => $comp->type ?? optional($comp->competency)->type ?? 'Teknis',

                'ideal_level'     => $comp->ideal_level,
                'current_level'   => $gap ? $gap->current_level : 0,
                'evidence'        => $gap ? $gap->evidence : null,
                'key_behaviors'   => $comp->competency->keyBehaviors ?? collect([]),
            ];
        });

        $employeeProfile = EmployeeProfile::where('user_id', $user->id)->latest()->first();
        $statusSaatIni = $employeeProfile ? $employeeProfile->status : 'not_started';

        return view('karyawan.penilaian', [
            'user' => $user,
            'jobProfile' => $jobProfile,
            'assessments' => $dataPenilaian, 
            'globalStatus' => $statusSaatIni,
            'status' => $statusSaatIni
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        // Pastikan load jobProfile aman
        $position = $user->position;
        if(!$position || !$position->jobProfile) {
            return back()->with('error', 'Profil tidak ditemukan.');
        }
        
        $jobProfile = $position->jobProfile;

        $request->validate([
            'competencies'   => 'required|array',
            'competencies.*' => 'required|integer|min:1|max:5',
            // Validasi input evidence
            'evidence'       => 'nullable|array',
            'evidence.*'     => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->competencies as $jobCompetencyId => $currentLevel) {
                // $jobCompetencyId adalah ID dari tabel 'job_competencies' (Pivot)
                $jobComp = JobCompetency::find($jobCompetencyId);
                
                if($jobComp) {
                    $idealLevel = $jobComp->ideal_level;
                    $gapValue = $currentLevel - $idealLevel;

                    // Ambil text evidence dari request berdasarkan ID Pivot
                    $evidenceText = $request->input("evidence.{$jobCompetencyId}", null);

                    // Jika level aktual <= ideal, evidence tidak wajib/bisa dikosongkan
                    if ($currentLevel <= $idealLevel) {
                        $evidenceText = null; 
                    }

                    // =========================================================
                    // PERBAIKAN UTAMA: SIMPAN competency_master_id
                    // =========================================================
                    GapRecord::updateOrCreate(
                        [
                            'user_id'              => $user->id,
                            'job_profile_id'       => $jobProfile->id,
                            // Gunakan Master ID sebagai unique key agar tidak duplikat
                            'competency_master_id' => $jobComp->competency_master_id, 
                        ],
                        [
                            // Data pelengkap disimpan di sini
                            'job_competency_id' => $jobComp->id, // ID Pivot (untuk referensi balik)
                            'competency_name'   => $jobComp->competency_name,
                            'competency_code'   => $jobComp->competency->competency_code ?? ('ID-' . $jobComp->id),
                            'ideal_level'       => $idealLevel,
                            'current_level'     => $currentLevel,
                            'gap_value'         => $gapValue,
                            'evidence'          => $evidenceText 
                        ]
                    );
                }
            }

            EmployeeProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'status'       => 'pending_verification',
                    'submitted_at' => now()
                ]
            );

            DB::commit();

            return redirect()->route('dashboard')->with('success', 'Penilaian berhasil dikirim! Menunggu verifikasi supervisor.');

        } catch (\Exception $e) {
            DB::rollback();
            //dd($e->getMessage()); // Uncomment untuk debug jika error 500
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }   
}