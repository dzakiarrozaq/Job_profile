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
            // TAMBAHAN: Validasi input evidence (boleh null/kosong)
            'evidence'       => 'nullable|array',
            'evidence.*'     => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->competencies as $competencyId => $currentLevel) {
                // $competencyId di sini adalah ID dari tabel 'job_competencies'
                $masterComp = JobCompetency::find($competencyId);
                
                if($masterComp) {
                    $idealLevel = $masterComp->ideal_level;
                    $gapValue = $currentLevel - $idealLevel;

                    // Ambil text evidence dari request berdasarkan ID
                    $evidenceText = $request->input("evidence.{$competencyId}", null);

                    // Jika level aktual <= ideal, evidence tidak wajib/bisa dikosongkan
                    if ($currentLevel <= $idealLevel) {
                        $evidenceText = null; 
                    }

                    GapRecord::updateOrCreate(
                        [
                            'user_id'         => $user->id,
                            'job_profile_id'  => $jobProfile->id,
                            'competency_name' => $masterComp->competency_name
                        ],
                        [
                            'competency_code' => $masterComp->competency_code ?? ('ID-' . $masterComp->id),
                            'ideal_level'     => $idealLevel,
                            'current_level'   => $currentLevel,
                            'gap_value'       => $gapValue,
                            // TAMBAHAN: Simpan Evidence
                            'evidence'        => $evidenceText 
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
            dd($e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}