<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Idp;
use App\Models\IdpDetail;
use App\Models\User; // Tambahkan Model User untuk mencari atasan

class IdpController extends Controller
{
    /**
     * Menampilkan halaman form IDP
     */
    public function index()
    {
        $currentYear = date('Y');
        
        $idp = Idp::with('details')
                  ->where('user_id', Auth::id())
                  ->where('year', $currentYear)
                  ->first();

        return view('idp.my-idp', compact('idp')); // Sesuaikan nama view Anda
    }

    /**
     * Menyimpan Data IDP (Draft / Submit)
     */
    public function store(Request $request)
    {
        // 1. VALIDASI DATA
        $request->validate([
            // Header
            'successor_position' => 'nullable|string|max:255',
            
            // Career Aspiration (Array Input dari View)
            'career_interest_a'     => 'nullable|array',
            'future_job_interest_a' => 'nullable|array',
            'career_interest_b'     => 'nullable|array',
            'future_job_interest_b' => 'nullable|array',

            // Development Goals (Nested Array)
            'goals'                 => 'nullable|array',
            'goals.*.goal'          => 'required|string',
            'goals.*.category'      => 'nullable|string',
            
            // Activities (Array di dalam Goal)
            'goals.*.activities'    => 'nullable|array',
            'goals.*.activities.*.desc' => 'required|string',
            'goals.*.activities.*.date' => 'nullable|string',
            'goals.*.activities.*.progress' => 'nullable|string',
        ]);

        // 2. OLAH DATA CAREER ASPIRATION (Jadi JSON Array Rapi)
        $careerAspirations = [
            'a' => [], // Job Family Sama
            'b' => []  // Job Family Beda
        ];

        // Gabungkan Input Bagian A
        if ($request->has('career_interest_a')) {
            foreach ($request->career_interest_a as $key => $interest) {
                if (!empty($interest) || !empty($request->future_job_interest_a[$key])) {
                    $careerAspirations['a'][] = [
                        'career_interest'     => $interest,
                        'future_job_interest' => $request->future_job_interest_a[$key] ?? null,
                    ];
                }
            }
        }

        // Gabungkan Input Bagian B
        if ($request->has('career_interest_b')) {
            foreach ($request->career_interest_b as $key => $interest) {
                if (!empty($interest) || !empty($request->future_job_interest_b[$key])) {
                    $careerAspirations['b'][] = [
                        'career_interest'     => $interest,
                        'future_job_interest' => $request->future_job_interest_b[$key] ?? null,
                    ];
                }
            }
        }

        // 3. CARI DATA MANAGER (SUPERVISOR)
        // Ini langkah krusial untuk mencegah Error 403 saat approval
        $user = Auth::user();
        $managerId = null;

        // Cek apakah user punya posisi dan punya atasan
        if ($user->position && $user->position->atasan_id) {
            // Cari User yang memegang jabatan atasan tersebut
            $manager = User::where('position_id', $user->position->atasan_id)->first();
            $managerId = $manager ? $manager->id : null;
        }

        // 4. UPDATE / CREATE DATA UTAMA IDP
        $idp = Idp::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'year'    => date('Y')
            ],
            [
                'successor_position' => $request->successor_position,
                'career_aspirations' => $careerAspirations, // Disimpan sebagai JSON
                'status'             => $request->action == 'submit' ? 'submitted' : 'draft',
                
                // --- PERBAIKAN PENTING DI SINI ---
                // Jika disubmit, kita kunci siapa managernya saat itu.
                'manager_id'         => $request->action == 'submit' ? $managerId : null,
            ]
        );

        // 5. SIMPAN DETAIL (GOALS & ACTIVITIES)
        // Hapus detail lama, ganti dengan yang baru (reset)
        $idp->details()->delete();

        if ($request->has('goals') && is_array($request->goals)) {
            foreach ($request->goals as $goalData) {
                // Skip jika baris kosong
                if (empty($goalData['goal'])) continue;

                $idp->details()->create([
                    'development_goal' => $goalData['goal'],
                    'dev_category'     => $goalData['category'] ?? null,
                    
                    // Simpan activities sebagai JSON Array
                    'activities'       => $goalData['activities'] ?? [], 
                    
                    'progress'         => null // Progress diisi nanti saat review
                ]);
            }
        }

        // Pesan Feedback
        if ($request->action == 'submit') {
            if (!$managerId) {
                // Peringatan jika atasan tidak ditemukan di sistem
                return back()->with('success', 'IDP berhasil disubmit, namun data Atasan Langsung tidak ditemukan di sistem. Hubungi Admin.');
            }
            $msg = 'IDP berhasil dikirim ke Atasan untuk persetujuan!';
        } else {
            $msg = 'Draft IDP berhasil disimpan.';
        }

        return back()->with('success', $msg);
    }
}