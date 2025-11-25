<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\GapRecord;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        $user->load([
            'roles', 
            'position.department', 
            'position.jobGrade',
            'jobHistories', 
            'educationHistories', 
            'skills', 
            'interests'
        ]);

        $gapRecords = GapRecord::where('user_id', $user->id)
                        ->orderBy('gap_value', 'asc') 
                        ->get();

        return view('profile.edit', [
            'user' => $user,
            'gapRecords' => $gapRecords, 
            'verifiedCompetencies' => $user->employeeProfiles,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function updateSupervisorProfile(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('supervisor.profile')->with('status', 'profile-updated');
    }

    public function editSkills(Request $request): View
    {
        return view('profile.keahlian', [
            'user' => $request->user(),
            'skills' => $request->user()->skills // Ambil data skill yang sudah ada
        ]);
    }

    /**
     * Menyimpan perubahan keahlian.
     */
    public function updateSkills(Request $request): RedirectResponse
    {
        $request->validate([
            'skills' => 'nullable|array',
            'skills.*.skill_name' => 'required|string|max:255',
            'skills.*.years_experience' => 'required|integer|min:0',
            'skills.*.certification' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        // Gunakan Transaksi agar aman
        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $request) {
            // 1. Hapus semua skill lama (cara paling bersih untuk update list dinamis)
            $user->skills()->delete();

            // 2. Masukkan data baru jika ada
            if ($request->has('skills')) {
                $user->skills()->createMany($request->skills);
            }
        });

        return redirect()->route('profile.edit')->with('success', 'Data keahlian berhasil diperbarui.');
    }

    /**
     * Menampilkan form edit minat karir.
     */
    public function editInterests(Request $request): View
    {
        return view('profile.minat', [
            'user' => $request->user(),
            'interests' => $request->user()->interests // Ambil data minat
        ]);
    }

    /**
     * Menyimpan perubahan minat karir.
     */
    public function updateInterests(Request $request): RedirectResponse
    {
        $request->validate([
            'interests' => 'nullable|array',
            'interests.*.position_name' => 'required|string|max:255',
            'interests.*.interest_level' => 'required|in:Tinggi,Sedang,Rendah',
        ]);

        $user = $request->user();

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $request) {
            // 1. Hapus semua minat lama
            $user->interests()->delete();

            // 2. Masukkan data baru jika ada
            if ($request->has('interests')) {
                $user->interests()->createMany($request->interests);
            }
        });

        return redirect()->route('profile.edit')->with('success', 'Data minat karir berhasil diperbarui.');
    }
}
