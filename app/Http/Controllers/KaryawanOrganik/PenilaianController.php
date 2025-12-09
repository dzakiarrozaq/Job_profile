<?php

namespace App\Http\Controllers\KaryawanOrganik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\JobCompetency;
use App\Models\CompetenciesMaster; 
use App\Models\EmployeeProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\AuditLog;


class PenilaianController extends Controller
{
    /**
     * Menampilkan halaman self-assessment.
     */
    public function index(): View
    {
        $user = Auth::user();
        $user->load('position.jobProfile.competencies.master', 'employeeProfiles');
        $jobProfile = $user->position?->jobProfile;

        if (!$jobProfile) {
            return view('karyawan.penilaian', [
                'assessments' => collect([]),
                'globalStatus' => 'no_profile',
                'hasDrafts' => false
            ]);
        }

        $jobCompetencies = $jobProfile->competencies; 
        
        $employeeProfiles = $user->employeeProfiles
                            ->keyBy('competency_code');

        if ($employeeProfiles->isEmpty()) {
            $globalStatus = 'not_started'; // Status Baru
        } elseif ($employeeProfiles->contains('status', 'pending_verification')) {
            $globalStatus = 'pending';
        } elseif ($employeeProfiles->every(fn($p) => $p->status === 'verified')) {
            $globalStatus = 'verified';
        } else {
            $globalStatus = 'draft';
        }

        $assessments = $jobCompetencies->map(function ($comp) use ($employeeProfiles) {
            $competencyCode = $comp->master->competency_code;
            $competencyName = $comp->master->competency_name;

            $profile = $employeeProfiles->get($competencyCode); 

            return (object) [
                'competency_name' => $competencyName,
                'competency_code' => $competencyCode,
                'ideal_level' => $comp->ideal_level,
                'current_level' => $profile->current_level ?? '-',
                'submitted_level' => $profile->submitted_level ?? null,
                'status' => $profile->status ?? 'draft',
                'reviewer_notes' => $profile->reviewer_notes ?? null
            ];
        });

        return view('karyawan.penilaian', [
            'assessments' => $assessments,
            'globalStatus' => $globalStatus, 
            'hasDrafts' => $globalStatus === 'draft',
        ]);
    }

    /**
     * Menyimpan atau mengajukan self-assessment.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $action = $request->input('action');
        $newStatus = ($action === 'submit') ? 'pending_verification' : 'draft';

        DB::beginTransaction();
        try {
            foreach ($request->input('competency', []) as $code => $level) {
                
                if (is_null($level)) continue; 

                $master = CompetenciesMaster::where('competency_code', $code)->first();

                EmployeeProfile::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'competency_code' => $code
                    ],
                    [
                        'competency_name' => $master?->competency_name ?? $code, 
                        'submitted_level' => $level,
                        'status' => $newStatus,
                        'submitted_at' => ($newStatus === 'pending_verification') ? now() : null,
                    ]
                );
            }
            
            DB::commit();

            if ($newStatus === 'pending_verification') {
                AuditLog::record('Submit Assessment', 'Mengajukan penilaian kompetensi untuk diverifikasi.', Auth::user());
                return redirect()->route('penilaian')->with('success', 'Penilaian berhasil diajukan ke Supervisor!');
            } else {
                AuditLog::record('Save Draft Assessment', 'Menyimpan draf penilaian kompetensi.', Auth::user());
                return redirect()->route('penilaian')->with('success', 'Draf berhasil disimpan.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}