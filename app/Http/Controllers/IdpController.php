<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Idp;
use App\Models\IdpDetail;
use App\Models\User; 

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
        $request->validate([
            'successor_position' => 'nullable|string|max:255',
            
            'career_interest_a'     => 'nullable|array',
            'future_job_interest_a' => 'nullable|array',
            'career_interest_b'     => 'nullable|array',
            'future_job_interest_b' => 'nullable|array',

            'goals'                 => 'nullable|array',
            'goals.*.goal'          => 'required|string',
            'goals.*.category'      => 'nullable|string',
            
            'goals.*.activities'    => 'nullable|array',
            'goals.*.activities.*.desc' => 'required|string',
            'goals.*.activities.*.date' => 'nullable|string',
            'goals.*.activities.*.progress' => 'nullable|string',
        ]);

        $careerAspirations = [
            'a' => [], 
            'b' => []  
        ];

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

        $user = Auth::user();
        $managerId = null;

        if ($user->position && $user->position->atasan_id) {
            $manager = User::where('position_id', $user->position->atasan_id)->first();
            $managerId = $manager ? $manager->id : null;
        }

        $idp = Idp::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'year'    => date('Y')
            ],
            [
                'successor_position' => $request->successor_position,
                'career_aspirations' => $careerAspirations, 
                'status'             => $request->action == 'submit' ? 'submitted' : 'draft',
                
                'manager_id'         => $request->action == 'submit' ? $managerId : null,
            ]
        );

        $idp->details()->delete();

        if ($request->has('goals') && is_array($request->goals)) {
            foreach ($request->goals as $goalData) {
                if (empty($goalData['goal'])) continue;

                $idp->details()->create([
                    'development_goal' => $goalData['goal'],
                    'dev_category'     => $goalData['category'] ?? null,
                    
                    'activities'       => $goalData['activities'] ?? [], 
                    
                    'progress'         => null 
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