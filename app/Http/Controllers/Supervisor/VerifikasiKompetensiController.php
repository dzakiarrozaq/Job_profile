<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\JobProfile;
use App\Models\EmployeeProfile;
use App\Models\GapRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VerifikasiKompetensiController extends Controller
{
    /**
     * Menampilkan halaman verifikasi untuk Supervisor.
     * (Dipanggil oleh GET /supervisor/verifikasi-kompetensi/{user})
     */
    public function show(User $user): View
    {
        // 1. Dapatkan Job Profile user
        $user->load('position.jobProfile.requirements', 'employeeProfiles');
        $jobProfile = $user->position?->jobProfile;

        if (!$jobProfile) {
            abort(404, 'User ini tidak memiliki Job Profile.');
        }

        // 2. Ambil data self-assessment (employee_profiles)
        $employeeProfiles = $user->employeeProfiles
                            ->keyBy('competency_code'); // Ubah jadi array asosiatif

        // 3. Gabungkan data
        $assessments = $jobProfile->requirements->map(function ($req) use ($employeeProfiles) {
            $profile = $employeeProfiles->get($req->competency_code);
            return (object) [
                'competency_name' => $req->competency_name,
                'competency_code' => $req->competency_code,
                'ideal_level' => $req->ideal_level,
                'weight' => $req->weight, // Kita butuh weight untuk menghitung gap
                'submitted_level' => $profile?->submitted_level,
                'status' => $profile?->status,
                'reviewer_notes' => $profile?->reviewer_notes,
            ];
        });

        // Tampilkan view verifikasi supervisor
        return view('supervisor.verifikasi-penilaian', [
            'karyawan' => $user, // Data karyawan yang dinilai
            'assessments' => $assessments // Data gabungan
        ]);
    }

    /**
     * Menyimpan hasil verifikasi Supervisor.
     * Di sinilah perhitungan GAP terjadi!
     * (Dipanggil oleh POST /supervisor/verifikasi-kompetensi/{user})
     */
    public function store(Request $request, User $user)
    {
        $supervisor = Auth::user(); 
        $user->load('position.jobProfile.requirements');
        $jobProfile = $user->position?->jobProfile;

        if (!$jobProfile) {
            return redirect()->back()->with('error', 'User ini tidak memiliki Job Profile.');
        }
        
        $requirements = $jobProfile->requirements->keyBy('competency_code');

        DB::beginTransaction();
        try {
            foreach ($request->input('level', []) as $code => $verified_level) {
                
                $req = $requirements->get($code);
                if (!$req) continue; 

                $ideal_level = $req->ideal_level;
                $weight = $req->weight;
                $catatan = $request->input("notes.{$code}", null);

                EmployeeProfile::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'competency_code' => $code,
                    ],
                    [
                        'competency_name' => $req->competency_name, 
                        'current_level' => $verified_level, 
                        'status' => 'verified',
                        'verified_by' => $supervisor->id,
                        'verified_at' => now(),
                        'reviewer_notes' => $catatan
                    ]
                );

                $gap_value = $verified_level - $ideal_level;
                $weighted_gap = $gap_value * $weight;

                GapRecord::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'job_profile_id' => $jobProfile->id,
                        'competency_code' => $code,
                    ],
                    [
                        'competency_name' => $req->competency_name,
                        'ideal_level' => $ideal_level,
                        'current_level' => $verified_level,
                        'gap_value' => $gap_value,
                        'weighted_gap' => $weighted_gap,
                        'calculated_at' => now()
                    ]
                );
            }
            
            DB::commit();

            return redirect()->route('supervisor.dashboard')->with('success', 'Penilaian untuk ' . $user->name . ' berhasil diverifikasi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}