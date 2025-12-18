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
        $jobProfile = $position?->jobProfile;


        // Cek jika tidak ada posisi atau profil
        if (!$position || !$jobProfile) {
            return redirect()->route('dashboard')
                ->with('error', 'Profil Kompetensi belum diatur untuk posisi Anda.');
        }

        $competencies = $jobProfile->competencies;

        // Ambil gap record yang sudah ada
        $existingGaps = GapRecord::where('user_id', $user->id)
            ->where('job_profile_id', $jobProfile->id)
            ->get();

        // Mapping data untuk dikirim ke View
        $dataPenilaian = $competencies->map(function ($comp) use ($existingGaps) {
            $gap = $existingGaps->firstWhere('competency_name', $comp->competency_name);

            return (object) [
                'id'              => $comp->id,
                'competency_name' => $comp->competency_name,
                'competency_code' => $comp->competency_code ?? '-', 
                
                // PERBAIKAN 1: Gunakan 'ideal_level' (sesuai database baru)
                'ideal_level'     => $comp->ideal_level, 
                
                'current_level'   => $gap ? $gap->current_level : 0,
            ];
        });

        $employeeProfile = EmployeeProfile::where('user_id', $user->id)->latest()->first();
        $statusSaatIni = $employeeProfile ? $employeeProfile->status : 'not_started';

        return view('karyawan.penilaian', [
            'user' => $user,
            'jobProfile' => $jobProfile,
            'assessments' => $dataPenilaian, // Variabel ini sudah BENAR
            'globalStatus' => $statusSaatIni, // Variabel ini sudah BENAR
            'status' => $statusSaatIni
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $jobProfile = $user->position->jobProfile;

        $request->validate([
            'competencies' => 'required|array',
            'competencies.*' => 'required|integer|min:1|max:5',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->competencies as $competencyId => $currentLevel) {
                // Cari data kompetensi asli dari database master/job competency
                $masterComp = JobCompetency::find($competencyId);
                
                if($masterComp) {
                    // PERBAIKAN 2: Gunakan 'ideal_level' (sesuai database baru)
                    $idealLevel = $masterComp->ideal_level;
                    
                    $gapValue = $currentLevel - $idealLevel;

                    GapRecord::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'job_profile_id' => $jobProfile->id,
                            'competency_name' => $masterComp->competency_name
                        ],
                        [
                            'competency_code' => $masterComp->competency_code,
                            'ideal_level' => $idealLevel,
                            'current_level' => $currentLevel,
                            'gap_value' => $gapValue
                        ]
                    );
                }
            }

            // Update status profil karyawan
            EmployeeProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'status' => 'pending_verification',
                    'submitted_at' => now()
                ]
            );

            DB::commit();

            return redirect()->route('dashboard')->with('success', 'Penilaian berhasil dikirim! Menunggu verifikasi supervisor.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}