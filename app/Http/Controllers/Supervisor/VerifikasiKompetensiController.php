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
use App\Notifications\StatusDiperbarui;


class VerifikasiKompetensiController extends Controller
{
    /**
     * Menampilkan halaman formulir verifikasi untuk satu karyawan.
     */
    public function show($userId)
    {
        $supervisor = Auth::user();
        $employee = User::where('id', $userId)->firstOrFail();
        
        $employee->load('position.jobProfile.competencies.competency.keyBehaviors', 'employeeProfile');
        
        $jobProfile = $employee->position->jobProfile;

        if (!$jobProfile) {
            return redirect()->back()->with('error', 'Job Profile belum tersedia.');
        }

        $competencies = $jobProfile->competencies->map(function ($comp) use ($employee) {
            
            $userGap = GapRecord::where('user_id', $employee->id)
                        ->where('competency_name', $comp->competency->competency_name)
                        ->first();

            return (object) [
                'competency_code' => $comp->competency->competency_code ?? ('ID-'.$comp->id),
                'competency_name' => $comp->competency->competency_name,
                'type'            => $comp->competency->type,
                'ideal_level'     => $comp->ideal_level,
                
                'submitted_level' => $userGap ? $userGap->current_level : 0, 
                'current_level'   => $userGap ? $userGap->current_level : 0,
                
                'evidence'        => $userGap ? $userGap->evidence : null, 
                'key_behaviors'   => $comp->competency->keyBehaviors ?? [], 

                'status'          => 'pending_verification',
                'reviewer_notes'  => '',
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
        $action = $request->input('action'); 

        $employee = User::findOrFail($userId);
        
        if ($action === 'reject') {
            EmployeeProfile::updateOrCreate(
                ['user_id' => $userId],
                [
                    'status'      => 'draft',
                    'verified_at' => null,
                    'verified_by' => null,
                ]
            );

            $employee->notify(new StatusDiperbarui(
                'Penilaian Perlu Revisi',
                'Supervisor meminta Anda merevisi penilaian kompetensi. Silakan cek kembali.',
                route('supervisor.persetujuan'), 
                'error' 
            ));

            return redirect()->route('supervisor.persetujuan')
                            ->with('success', 'Pengajuan berhasil ditolak dan dikembalikan ke karyawan.');
        }

        $request->validate([
            'verified_level'   => 'required|array',
            'verified_level.*' => 'required|integer|min:1|max:5',
            'notes'            => 'nullable|array',
        ]);

        $jobProfile = $employee->position->jobProfile->load('competencies.competency');

        DB::beginTransaction();
        try {
            foreach ($request->input('verified_level') as $code => $level) {
                $jobComp = $jobProfile->competencies->first(function ($jc) use ($code) {
                    return optional($jc->competency)->competency_code === $code;
                });
                
                if ($jobComp) {
                    $idealLevel = $jobComp->ideal_level;
                    $gapValue   = $level - $idealLevel;
                    $compName = optional($jobComp->competency)->competency_name;

                    GapRecord::updateOrCreate(
                        [
                            'user_id'         => $userId,
                            'job_profile_id'  => $jobProfile->id,
                            'competency_name' => $compName 
                        ],
                        [
                            'competency_code' => $code,
                            'ideal_level'     => $idealLevel,
                            'current_level'   => $level, 
                            'gap_value'       => $gapValue,
                            'calculated_at'   => now(),
                        ]
                    );
                }
            }

            EmployeeProfile::updateOrCreate(
                ['user_id' => $userId],
                [
                    'status'      => 'verified',
                    'verified_at' => now(),
                    'verified_by' => Auth::id(),
                ]
            );

            $employee->notify(new StatusDiperbarui(
                'Penilaian Disetujui',
                'Supervisor telah memverifikasi penilaian Anda.',
                route('dashboard'),
                'success'
            ));

            DB::commit();
            return redirect()->route('supervisor.persetujuan')
                            ->with('success', 'Verifikasi berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}