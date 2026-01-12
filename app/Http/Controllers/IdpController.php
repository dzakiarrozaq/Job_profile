<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller; // Pastikan ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // WAJIB: Untuk Auth::id()
use App\Models\Idp; // WAJIB: Panggil Model Idp

class IdpController extends Controller
{
    public function index()
    {
        $currentYear = date('Y');
        
        $idp = Idp::with('details')
                  ->where('user_id', Auth::id())
                  ->where('year', $currentYear)
                  ->first();

        return view('idp.my-idp', compact('idp'));
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'career_preference'   => 'nullable|string|max:255',
            'career_interest'     => 'nullable|string|max:255',
            'future_job_interest' => 'nullable|string|max:255',
            'goals'               => 'nullable|array',
            'goals.*.goal'        => 'required|string',
            'goals.*.category'    => 'required|string',
            'goals.*.activity'    => 'required|string',
            'goals.*.date'        => 'required|string',
        ]);

        $idp = Idp::updateOrCreate(
            [
                'user_id' => Auth::id(), 
                'year'    => date('Y') 
            ],
            [
                'career_preference'   => $request->career_preference,
                'career_interest'     => $request->career_interest,
                'future_job_interest' => $request->future_job_interest,
                'status'              => $request->action == 'submit' ? 'submitted' : 'draft'
            ]
        );

        $idp->details()->delete(); 

        if($request->has('goals') && is_array($request->goals)) {
            foreach($request->goals as $goal) {
                $idp->details()->create([
                    'development_goal' => $goal['goal'],
                    'dev_category'     => $goal['category'],
                    'activity'         => $goal['activity'],
                    'expected_date'    => $goal['date'],
                    'progress'         => $goal['progress'] ?? null, 
                ]);
            }
        }

        $msg = $request->action == 'submit' ? 'IDP berhasil dikirim ke Atasan!' : 'Draft IDP berhasil disimpan.';
        return back()->with('success', $msg);
    }
}