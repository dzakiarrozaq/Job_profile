<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Password;
use App\Models\GapRecord;
use Illuminate\Support\Facades\Storage;
use App\Models\JobHistory;
use App\Models\EducationHistory;
use App\Models\Position; 

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
            'position.organization', 
            'position.jobGrade',
            'jobHistories', 
            'educationHistories', 
            'skills', 
            'interests'
        ]);

        $allPositions = Position::with('organization')->get();

        $gapRecords = GapRecord::where('user_id', $user->id)
                        ->orderBy('gap_value', 'asc') 
                        ->get();

        return view('profile.edit', [
            'user' => $user,
            'gapRecords' => $gapRecords, 
            'verifiedCompetencies' => $user->employeeProfiles,
            'positions' => $allPositions,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path; 
        }

        $user->fill($data);
        
        if ($user->isDirty('position_id')) {
            
            $oldPositionId = $user->getOriginal('position_id');

            if ($oldPositionId) {
                $oldPosition = Position::with('department')->find($oldPositionId);
                
                if ($oldPosition) {
                    JobHistory::create([
                        'user_id' => $user->id,
                        'title' => $oldPosition->title, // Judul Posisi Lama
                        'unit' => $oldPosition->department->name ?? 'General', // Departemen Lama
                        
                        'start_date' => $user->hiring_date ?? $user->created_at, 
                        
                        'end_date' => now(), 
                        'description' => 'Rotasi/Promosi Otomatis dari Sistem (Update Profil)',
                    ]);
                }
            }
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profil diperbarui. Jika posisi berubah, riwayat lama telah diarsipkan otomatis.');
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
            'skills' => $request->user()->skills
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

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $request) {
            $user->skills()->delete();

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
            'interests' => $request->user()->interests 
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
            $user->interests()->delete();

            if ($request->has('interests')) {
                $user->interests()->createMany($request->interests);
            }
        });

        return redirect()->route('profile.edit')->with('success', 'Data minat karir berhasil diperbarui.');
    }

    /**
    * Mengirim link reset password ke email pengguna yang sedang login.
    */
    public function triggerResetPassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $status = Password::broker()->sendResetLink(['email' => $user->email]);

        return $status == Password::RESET_LINK_SENT
            ? back()->with('success', 'Link reset password telah dikirim ke email Anda.')
            : back()->with('error', 'Gagal mengirim link reset password.');
    }

    public function storeJobHistory(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        JobHistory::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'unit' => $validated['unit'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        return back()->with('success', 'Riwayat jabatan berhasil ditambahkan.');
    }

    public function storeEducation(Request $request)
    {
        $validated = $request->validate([
            'degree' => 'required|string|max:50',
            'institution' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'year_start' => 'required|integer|digits:4',
            'year_end' => 'nullable|integer|digits:4|gte:year_start',
        ]);

        EducationHistory::create([
            'user_id' => auth()->id(),
            'degree' => $validated['degree'],
            'institution' => $validated['institution'],
            'field_of_study' => $validated['field_of_study'],
            'year_start' => $validated['year_start'],
            'year_end' => $validated['year_end'],
        ]);

        return back()->with('success', 'Riwayat pendidikan berhasil ditambahkan.');
    }

    /**
     * Menghapus riwayat jabatan.
     */
    public function destroyJobHistory($id): RedirectResponse
    {
        // Cari data berdasarkan ID, TAPI pastikan user_id nya sama dengan yang login (Security)
        $job = JobHistory::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail(); // Akan return 404 jika id tidak ketemu atau punya orang lain

        $job->delete();

        return back()->with('success', 'Riwayat jabatan berhasil dihapus.');
    }

    /**
     * Menghapus riwayat pendidikan.
     */
    public function destroyEducation($id): RedirectResponse
    {
        // Cari data berdasarkan ID, TAPI pastikan user_id nya sama dengan yang login (Security)
        $education = EducationHistory::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $education->delete();

        return back()->with('success', 'Riwayat pendidikan berhasil dihapus.');
    }
}
