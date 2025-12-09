<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EmployeeProfile;
use App\Models\GapRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Notifications\JobProfileStatusNotification; 
use App\Models\AuditLog;

class VerifikasiKompetensiController extends Controller
{
    /**
     * Menampilkan halaman formulir verifikasi untuk satu karyawan.
     */
    public function show($userId)
    {
        $supervisor = Auth::user();
        $employee = User::where('id', $userId)->firstOrFail();
        $employee->load('position.jobProfile.competencies.master', 'employeeProfiles'); 
        $jobProfile = $employee->position->jobProfile;

        if (!$jobProfile) {
            return redirect()->back()->with('error', 'Karyawan ini posisi jabatannya belum memiliki Job Profile.');
        }

        $competencies = $jobProfile->competencies->map(function ($comp) use ($employee) {
            $employeeProfile = $employee->employeeProfiles
                ->where('competency_code', $comp->master->competency_code)
                ->first();

            return (object) [
                'competency_code' => $comp->master->competency_code,
                'competency_name' => $comp->master->competency_name,
                'type'            => $comp->master->type,
                'ideal_level'     => $comp->ideal_level,
                'submitted_level' => $employeeProfile->submitted_level ?? 0,
                'current_level'   => $employeeProfile->current_level ?? 0,
                'status'          => $employeeProfile->status ?? 'draft',
                'reviewer_notes'  => $employeeProfile->reviewer_notes ?? '',
            ];
        });

        return view('supervisor.persetujuan.verifikasi', [
            'employee' => $employee,
            'competencies' => $competencies
        ]);
    }

    /**
     * Menyimpan hasil verifikasi Supervisor.
     */
    public function store(Request $request, $userId)
    {
        $employee = User::findOrFail($userId);
        $jobProfile = $employee->position->jobProfile;

        $request->validate([
            'verified_level' => 'required|array',
            'verified_level.*' => 'required|integer|min:1|max:5',
            'notes' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->input('verified_level') as $code => $level) {
                
                $compData = $jobProfile->competencies->first(fn($c) => $c->master->competency_code == $code);
                $compName = $compData ? $compData->master->competency_name : $code;

                EmployeeProfile::updateOrCreate(
                    ['user_id' => $userId, 'competency_code' => $code],
                    [
                        'competency_name' => $compName,
                        'current_level' => $level,
                        'verified_at' => now(),
                        'verified_by' => Auth::id(),
                        'status' => 'verified',
                        'reviewer_notes' => $request->input("notes.$code")
                    ]
                );

                if ($compData) {
                    $ideal = $compData->ideal_level;
                    $gap = $level - $ideal;
                    $weightedGap = $gap * $compData->weight;

                    GapRecord::updateOrCreate(
                        [
                            'user_id' => $userId, 
                            'job_profile_id' => $jobProfile->id,
                            'competency_code' => $code
                        ],
                        [
                            'competency_name' => $compName,
                            'ideal_level' => $ideal,
                            'current_level' => $level,
                            'gap_value' => $gap,
                            'weighted_gap' => $weightedGap,
                            'calculated_at' => now()
                        ]
                    );
                }
            }
            
            DB::commit();

            AuditLog::record(
                'Verify Competency', 
                "Memverifikasi penilaian kompetensi untuk karyawan: {$employee->name}", 
                $employee
            );

            $employee->notify(new JobProfileStatusNotification(
                'Penilaian Diverifikasi', 
                'Supervisor telah memverifikasi penilaian kompetensi Anda.',
                'success'
            ));

            return redirect()->route('supervisor.persetujuan')->with('success', 'Verifikasi kompetensi berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}