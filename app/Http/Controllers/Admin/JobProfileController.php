<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; 
use App\Models\JobProfile;
use App\Models\JobRequirement;
use App\Models\JobCompetency; 
use App\Models\CompetenciesMaster;
use App\Models\Position;
use App\Models\AuditLog;

class JobProfileController extends Controller
{
    /**
     * Menampilkan daftar Job Profile.
     */
    public function index(Request $request): View
    {
        $activeRoleName = $request->session()->get('active_role_name');

        $query = JobProfile::with('position.department', 'creator')
                            ->orderBy('created_at', 'desc');
        
        if ($activeRoleName === 'Supervisor') {
            $query->where(function($q) {
                $q->where('status', 'verified') // Yang sudah resmi
                  ->orWhere('created_by', Auth::id()); // Draf buatan sendiri
            });
        }

        $jobProfiles = $query->paginate(15); 
        
        if ($activeRoleName === 'Admin') {
            return view('admin.job-profile.index', [
                'jobProfiles' => $jobProfiles
            ]);
        }

        return view('supervisor.job-profile.index', [
            'jobProfiles' => $jobProfiles
        ]);
    }

    /**
     * Menampilkan form untuk membuat Job Profile baru.
     */
    public function create(Request $request): View 
    {
        $positions = Position::whereDoesntHave('jobProfile')
                        ->orderBy('title', 'asc')
                        ->get();
        
        $activeRoleName = $request->session()->get('active_role_name');

        $view = ($activeRoleName === 'Admin') 
                ? 'admin.job-profile.create' 
                : 'supervisor.job-profile.create';

        return view($view, [
            'positions' => $positions
        ]);
    }

    /**
     * Menyimpan Job Profile baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'position_id' => 'required|integer|exists:positions,id|unique:job_profiles,position_id',
        ], [
            'position_id.required' => 'Silakan pilih posisi terlebih dahulu.',
            'position_id.unique' => 'Job Profile untuk posisi ini sudah ada. Silakan edit yang sudah ada.',
        ]);

        DB::beginTransaction();
        try {
            $jobProfile = JobProfile::create([
                'position_id' => $validated['position_id'],
                'created_by' => Auth::id(),
                'version' => 0,
                'status' => 'draft', 
            ]);

            DB::commit();

            AuditLog::record('Create Job Profile', "Membuat Job Profile baru untuk posisi ID: " . $jobProfile->position_id, $jobProfile);

            $activeRoleName = $request->session()->get('active_role_name');
            
            $routePrefix = ($activeRoleName === 'Admin') ? 'admin' : 'supervisor';

            return redirect()
                ->route($routePrefix . '.job-profile.edit', $jobProfile->id)
                ->with('success', 'Job Profile berhasil dibuat. Silakan lengkapi detailnya sekarang.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat Job Profile: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit Job Profile yang ada.
     */
    public function edit(Request $request, JobProfile $jobProfile): View 
    {
        $jobProfile->load(
            'position.department', 
            'position.jobGrade',
            'position.directorate',
            'position.unit',
            'position.atasan',
            'competencies.master', 
            'responsibilities',
            'specifications',
            'workRelations'
        );
        
        $positions = Position::orderBy('title', 'asc')->get();
        
        $activeRoleName = $request->session()->get('active_role_name');

        $view = ($activeRoleName === 'Admin') 
                ? 'admin.job-profile.edit' 
                : 'supervisor.job-profile.edit';

        return view($view, [
            'jobProfile' => $jobProfile,
            'positions' => $positions
        ]);
    }

    /**
     * Memperbarui Job Profile di database.
     */
    public function update(Request $request, JobProfile $jobProfile): RedirectResponse
    {

        $validated = $request->validate([
                'tujuan_jabatan' => 'nullable|string',
                'wewenang' => 'nullable|string',
                
                'responsibilities' => 'nullable|array',
                'responsibilities.*.description' => 'required_with:responsibilities|string',
                'responsibilities.*.expected_result' => 'nullable|string',
                
                'competencies' => 'nullable|array',
                'competencies.*.competency_master_id' => 'required|integer|exists:competencies_master,id',
                'competencies.*.competency_name' => 'required|string',
                'competencies.*.ideal_level' => 'required|integer|min:1|max:5',
                'competencies.*.weight' => 'required|numeric|min:0.1',

                'dimensi_keuangan' => 'nullable|string',
                'dimensi_non_keuangan' => 'nullable|string',
                'workRelations' => 'nullable|array',
                'workRelations.*.type' => 'required_with:workRelations|in:internal,external',
                'workRelations.*.unit_instansi' => 'required_with:workRelations|string',
                'workRelations.*.purpose' => 'required_with:workRelations|string',
                
                'specifications' => 'nullable|array',
                'specifications.*.type' => 'required_with:specifications|string',
                'specifications.*.requirement' => 'required_with:specifications|string',
                'specifications.*.level_or_notes' => 'nullable|string',

            ], [
                'competencies.*.competency_master_id.required' => 'Pilih kompetensi dari daftar suggestion.',
                'competencies.*.competency_master_id.exists' => 'Kompetensi tidak valid.',
        ]);

        DB::beginTransaction();
        try {

            $jobProfile->update([
                'tujuan_jabatan' => $validated['tujuan_jabatan'],
                'wewenang' => $validated['wewenang'],
                'dimensi_keuangan' => $validated['dimensi_keuangan'] ?? null,   
                'dimensi_non_keuangan' => $validated['dimensi_non_keuangan'] ?? null,
                'version' => $jobProfile->version + 1,
                'created_by' => Auth::id(),
            ]);

            $jobProfile->responsibilities()->delete(); 
            if (!empty($validated['responsibilities'])) {
                foreach ($validated['responsibilities'] as $resp) {
                    $jobProfile->responsibilities()->create($resp); 
                }
            }

            $jobProfile->competencies()->delete();
            if (!empty($validated['competencies'])) { 
                foreach ($validated['competencies'] as $comp) {
                    $jobProfile->competencies()->create([
                        'competency_master_id' => $comp['competency_master_id'],
                        'ideal_level' => $comp['ideal_level'],
                        'weight' => $comp['weight'],
                    ]);
                }
            }

            $jobProfile->specifications()->delete();
            if (!empty($validated['specifications'])) {
                foreach ($validated['specifications'] as $spec) {
                    $jobProfile->specifications()->create($spec);
                }
            }

            $jobProfile->workRelations()->delete(); 
            if (!empty($validated['workRelations'])) {
                foreach ($validated['workRelations'] as $rel) {
                    $jobProfile->workRelations()->create($rel); 
                }
            }

            if ($request->input('action') === 'submit_verification') {
                $jobProfile->update([
                    'status' => 'pending_verification', 
                ]);
                $message = 'Job Profile berhasil disimpan dan diajukan ke Supervisor.';
            } else {
                if ($jobProfile->status !== 'verified') {
                    $jobProfile->update(['status' => 'draft']);
                    AuditLog::record(
                        'Approve Job Profile', 
                        "Menyetujui Job Profile: {$jobProfile->position->title} (v{$jobProfile->version})", 
                        $jobProfile
                    );
                }
                $message = 'Draf Job Profile berhasil disimpan.';
            }

            DB::commit();

            AuditLog::record('Update Job Profile', "Memperbarui Job Profile (v{$jobProfile->version})", $jobProfile);

            $redirectRoute = $request->session()->get('active_role_name') === 'Admin' 
                             ? 'admin.job-profile.index' 
                             : 'supervisor.job-profile.index';

            return redirect()->route($redirectRoute)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui Job Profile: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Memberikan saran requirement kompetensi menggunakan AI.
     */
    public function suggestText(Request $request)
    {
        $validated = $request->validate([
            'position_title' => 'required|string|max:255',
            'field_type' => 'required|string', 
        ]);

        $positionTitle = $validated['position_title'];
        $fieldType = $validated['field_type'];

        $prompt = "";
        if ($fieldType === 'tujuan_jabatan') {
            $prompt = "Anda adalah konsultan HR senior. Tuliskan 'Tujuan Jabatan' (Job Purpose) yang ringkas dan profesional untuk posisi '$positionTitle'.
            Balas HANYA dengan format JSON.
            Objek JSON harus memiliki satu key: 'text' (string)."; 
            
        } elseif ($fieldType === 'wewenang') {
            $prompt = "Anda adalah konsultan HR senior. Tuliskan 3-5 poin 'Wewenang' (Authority) utama untuk posisi '$positionTitle'.
            Balas HANYA dengan format JSON.
            Objek JSON harus memiliki satu key: 'text' (string, gunakan bullet point \n- )."; 
            
        } elseif ($fieldType === 'tanggung_jawab') {
            $prompt = "Anda adalah konsultan HR senior. Buat daftar 5 'Tanggung Jawab' (Responsibilities) utama untuk posisi '$positionTitle'.
            Balas HANYA dengan format JSON array.
            Setiap objek di array HARUS memiliki dua key:
            1. 'description' (string, tanggung jawabnya)
            2. 'expected_result' (string, hasil yang diharapkan)"; 
        } else {
            return response()->json(['error' => 'Tipe field tidak valid'], 400);
        }

        try {
            $apiKey = config('services.gemini.key');
            $certificatePath = storage_path('app/cacert.pem');

            if (!file_exists($certificatePath)) {
                return response()->json(['error' => 'File sertifikat (cacert.pem) tidak ditemukan di storage/app/'], 500);
            }

            $response = Http::withOptions([
                'verify' => $certificatePath
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [ ['parts' => [ ['text' => $prompt] ]] ]
            ]);
            
            if (!$response->successful()) {
                return response()->json(['error' => 'Gagal terhubung ke AI Service.', 'details' => $response->body()], 500);
            }

            $jsonText = $response->json('candidates.0.content.parts.0.text');
            $jsonText = str_replace(['```json', '```'], '', $jsonText);
            
            if ($fieldType === 'tanggung_jawab') {
                $suggestions = json_decode($jsonText);
                if (json_last_error() !== JSON_ERROR_NONE) {
                     return response()->json(['error' => 'AI memberikan format data JSON (array) yang tidak valid.', 'raw' => $jsonText], 500);
                }
                return response()->json($suggestions);
            } else {
                $data = json_decode($jsonText);
                if (json_last_error() !== JSON_ERROR_NONE || !isset($data->text)) {
                     return response()->json(['error' => 'AI memberikan format data JSON (text) yang tidak valid.', 'raw' => $jsonText], 500);
                }
                return response()->json($data); 
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mencari kompetensi dari master.
     */
    public function searchCompetencies(Request $request)
    {
        $query = $request->get('q');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $competencies = CompetenciesMaster::where('competency_name', 'LIKE', "%{$query}%")
                            ->orWhere('competency_code', 'LIKE', "%{$query}%")
                            ->take(10)
                            ->get();
        
        return response()->json($competencies);
    }

    /**
     * Menghapus Job Profile.
     */
    public function destroy(JobProfile $jobProfile): RedirectResponse
    {
        $positionTitle = $jobProfile->position->title ?? 'N/A';

        $jobProfile->delete();

        AuditLog::record(
            'Delete Job Profile', 
            "Menghapus Job Profile untuk posisi: $positionTitle (v{$jobProfile->version})", 
            $jobProfile
        );

        return redirect()->back()->with('success', 'Job Profile berhasil dihapus.');
    }
}