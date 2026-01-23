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
        $employee->load('position.jobProfile.competencies.master', 'employeeProfile');
        $jobProfile = $employee->position->jobProfile;

        if (!$jobProfile) {
            return redirect()->back()->with('error', 'Karyawan ini posisi jabatannya belum memiliki Job Profile.');
        }

        $competencies = $jobProfile->competencies->map(function ($comp) use ($employee) {
    
            $userGap = GapRecord::where('user_id', $employee->id)
                        ->where('competency_code', $comp->master->competency_code)
                        ->first();


            return (object) [
                'competency_code' => $comp->master->competency_code,
                'competency_name' => $comp->master->competency_name,
                'type'            => $comp->master->type,
                'ideal_level'     => $comp->ideal_level,
                
                'submitted_level' => $userGap ? $userGap->current_level : 0, 
                'current_level'   => $userGap ? $userGap->current_level : 0,
                
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
        $request->validate([
            'verified_level' => 'required|array',
            'verified_level.*' => 'required|integer|min:1|max:5',
            'notes' => 'nullable|array',
        ]);

        $employee = User::findOrFail($userId);
        $jobProfile = $employee->position->jobProfile;

        DB::beginTransaction();
        try {
            foreach ($request->input('verified_level') as $code => $level) {
                
                $compMaster = $jobProfile->competencies
                    ->first(fn($c) => $c->master->competency_code == $code);
                
                if ($compMaster) {
                    $idealLevel = $compMaster->ideal_level;
                    $gapValue = $level - $idealLevel;
                    
                    GapRecord::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'job_profile_id' => $jobProfile->id,
                            'competency_code' => $code
                        ],
                        [
                            'competency_name' => $compMaster->master->competency_name, 
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
                    'status' => 'verified',
                    'verified_at' => now(),
                    'verified_by' => Auth::id(),
                ]
            );

            DB::commit();
            
            $employee->notify(new StatusDiperbarui(
                'Penilaian Disetujui',
                'Supervisor telah memverifikasi penilaian kompetensi Anda. Silakan cek analisis gap terbaru.',
                route('dashboard'), // Arahkan ke dashboard karyawan
                'success'
            ));

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Verifikasi Kompetensi',
                'description' => "Memverifikasi kompetensi karyawan: {$employee->name}"
            ]);

            return redirect()->route('supervisor.persetujuan')
                             ->with('success', 'Verifikasi berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            dd([
                'BAGIAN' => 'TERJADI ERROR SAAT SIMPAN KE DB',
                'PESAN ERROR' => $e->getMessage(),
                'BARIS ERROR' => $e->getLine(),
                'FILE ERROR' => $e->getFile()
            ]);
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}