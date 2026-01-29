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
        $isSupervisorRoute = $request->routeIs('supervisor.*');
        $query = JobProfile::with('position.organization', 'creator', 'position.atasan')
                            ->orderBy('updated_at', 'desc');

        if ($isSupervisorRoute) {
            $request->session()->put('active_role_name', 'Supervisor');
            $user = Auth::user();
            
            // Cek Bawahan (Recursive)
            if ($user->position_id) {
                $directSubordinates = \App\Models\Position::where('atasan_id', $user->position_id)->pluck('id')->toArray();
                $indirectSubordinates = \App\Models\Position::whereIn('atasan_id', $directSubordinates)->pluck('id')->toArray();
                $allSubordinatePositionIds = array_merge($directSubordinates, $indirectSubordinates);

                $query->where(function($q) use ($user, $allSubordinatePositionIds) {
                    // 1. Tampilkan Draft/Pending HANYA jika buatan sendiri
                    $q->where('created_by', $user->id);
                    
                    // 2. Tampilkan milik bawahan HANYA jika sudah VERIFIED
                    $q->orWhere(function($subQ) use ($allSubordinatePositionIds) {
                        $subQ->whereIn('position_id', $allSubordinatePositionIds)
                            ->where('status', 'verified'); // <--- KUNCI PERBAIKANNYA
                    });

                    // 3. Tampilkan verified public lainnya (jika perlu)
                    $q->orWhere('status', 'verified'); 
                });
            } else {
                // Jika user tidak punya jabatan, hanya lihat buatan sendiri
                $query->where('created_by', $user->id);
            }

            return view('supervisor.job-profile.index', [
                'jobProfiles' => $query->paginate(15)
            ]);
        }
        
        $request->session()->put('active_role_name', 'Admin');
        return view('admin.job-profile.index', [
            'jobProfiles' => $query->paginate(15)
        ]);
    }

    /**
     * Menampilkan form untuk membuat Job Profile baru.
     */
    public function create(Request $request): View 
    {        
        $positions = Position::whereDoesntHave('jobProfile')
                    ->with(['organization.parent.parent']) 
                    ->orderBy('title', 'asc')
                    ->get();

        $activeRoleName = $request->session()->get('active_role_name');

        if (!$activeRoleName) {
            $activeRoleName = $request->routeIs('admin.*') ? 'Admin' : 'Supervisor';
        }

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
            'position.organization', 
            'position.jobGrade',
            'position.atasan',
            'competencies.competency', 
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
    public function update(Request $request, JobProfile $jobProfile)
    {
        $input = $request->all();

        $action = $request->input('action');
        $newStatus = $jobProfile->status; 

        if ($action === 'submit_verification') {
            $newStatus = 'pending_verification'; 
        } elseif ($action === 'approve') {
            $newStatus = 'verified'; 
        } elseif ($action === 'reject') {
            $newStatus = 'draft'; 
        } elseif ($action === 'save_draft') {
            $newStatus = 'draft'; 
        }

        $cleanCompetencies = [];
        if (!empty($input['competencies']) && is_array($input['competencies'])) {
            foreach ($input['competencies'] as $comp) {
                if (!empty($comp['competency_name'])) {
                    if (empty($comp['competency_master_id'])) {
                        $master = CompetenciesMaster::where('competency_name', $comp['competency_name'])->first();
                        $comp['competency_master_id'] = $master ? $master->id : null;
                        $comp['competency_code'] = $master ? $master->competency_code : null;
                    }
                    $comp['ideal_level'] = $comp['ideal_level'] ?? 1;
                    $comp['weight'] = $comp['weight'] ?? 1;
                    $cleanCompetencies[] = $comp;
                }
            }
        }

        $rawSpecifications = array_merge(
            $request->input('educations', []),
            $request->input('experiences', []),
            $request->input('certifications', []),
            $request->input('languages', []),
            $request->input('computers', []),
            $request->input('healths', [])
        );

        $cleanSpecifications = array_filter($rawSpecifications, function($item) {
            return !empty($item['requirement']);
        });

        DB::beginTransaction();
        try {
            $jobProfile->update([
                'tujuan_jabatan'       => $input['tujuan_jabatan'] ?? null,
                'wewenang'             => $input['wewenang'] ?? null,
                'dimensi_keuangan'     => $input['dimensi_keuangan'] ?? null,
                'dimensi_non_keuangan' => $input['dimensi_non_keuangan'] ?? null,
                'version'              => $jobProfile->version + 1,
                'status'               => $newStatus,
            ]);

            $jobProfile->competencies()->delete();
            foreach ($cleanCompetencies as $c) {
                if (!empty($c['competency_master_id'])) {
                    $jobProfile->competencies()->create([
                        'competency_master_id' => $c['competency_master_id'],
                        'competency_name'      => $c['competency_name'],
                        'competency_code'      => $c['competency_code'] ?? null,
                        'ideal_level'          => $c['ideal_level'],
                        'weight'               => $c['weight'],
                    ]);
                }
            }

            $jobProfile->responsibilities()->delete();
            if (!empty($input['responsibilities'])) {
                $validResps = array_filter($input['responsibilities'], fn($r) => !empty($r['description']));
                $jobProfile->responsibilities()->createMany(array_values($validResps));
            }

            $jobProfile->specifications()->delete();
            if (!empty($cleanSpecifications)) {
                $jobProfile->specifications()->createMany(array_values($cleanSpecifications));
            }

            $jobProfile->workRelations()->delete();
            if (!empty($input['workRelations'])) {
                $validRelations = array_filter($input['workRelations'], fn($w) => !empty($w['unit_instansi']));
                $jobProfile->workRelations()->createMany(array_values($validRelations));
            }

            DB::commit();

            $activeRoleName = $request->session()->get('active_role_name');
            $routePrefix = ($activeRoleName === 'Admin') ? 'admin' : 'supervisor';

            $message = 'Job Profile berhasil diperbarui.';
            if ($newStatus === 'verified') $message = 'Job Profile berhasil DISETUJUI.';
            if ($newStatus === 'draft' && $action === 'reject') $message = 'Job Profile berhasil DITOLAK.';

            return redirect()
                ->route($routePrefix . '.job-profile.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            dd('GAGAL MENYIMPAN (SQL ERROR):', $e->getMessage());
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

        $technicalKeywords = ['Operator', 'Mekanik', 'Technician', 'Engineer', 'Foreman', 'Superintendent', 'Maintenance', 'Production', 'Kiln', 'Packer', 'Safety', 'Quality', 'Lab'];
        $isTechnical = false;
        foreach ($technicalKeywords as $keyword) {
            if (stripos($positionTitle, $keyword) !== false) {
                $isTechnical = true;
                break;
            }
        }

        if ($isTechnical) {
            $context = "Heavy Industry (Cement Plant), Safety First (K3/Zero Accident), Machine Reliability (OEE), Technical & Operational Excellence.";
        } else {
            $context = "State-Owned Enterprise (BUMN) Corporate Env, Good Corporate Governance (GCG), Compliance, Data Accuracy, & Internal Service SLA.";
        }

        $prompt = "";

        if ($fieldType === 'tujuan_jabatan') {
            $prompt = "
            Role: Senior HR Specialist at Semen Gresik (SIG).
            Task: Write a concise, professional 'Job Purpose' for the position: '$positionTitle'.
            Context: $context
            
            Constraint:
            - Output MUST be in INDONESIAN (Bahasa Indonesia Formal).
            - Return ONLY a valid JSON Object.
            - JSON Format: { \"text\": \"...the job purpose string...\" }";

        } elseif ($fieldType === 'wewenang') {
            $prompt = "
            Role: Senior HR Specialist at Semen Gresik (SIG).
            Task: List 3-5 key 'Authorities' (Wewenang) for the position: '$positionTitle'.
            Context: $context
            
            Constraint:
            - Output MUST be in INDONESIAN (Bahasa Indonesia Formal).
            - Return ONLY a valid JSON Object.
            - JSON Format: { \"text\": \"- Authority point 1\\n- Authority point 2...\" } (Use bullet points).";

        } elseif ($fieldType === 'tanggung_jawab') {
            $prompt = "
            Role: Senior Expert at Semen Gresik (SIG).
            Task: Create 5 key 'Responsibilities' for the position: '$positionTitle'.
            Context: $context
            
            Constraint:
            - Output MUST be in INDONESIAN (Bahasa Indonesia Formal).
            - Return ONLY a valid JSON Array.
            - JSON Object Keys: 'description' (the activity) and 'expected_result' (measurable KPI/Outcome).
            
            Example Output Format:
            [
                { \"description\": \"Melakukan perawatan rutin...\", \"expected_result\": \"Availability mesin 98%\" }
            ]";

        } else {
            return response()->json(['error' => 'Tipe field tidak valid'], 400);
        }

        try {
            $apiKey = config('services.gemini.key');
            $certificatePath = storage_path('app/cacert.pem'); 

            $http = Http::asJson();
            
            if (file_exists($certificatePath)) {
                $http = $http->withOptions(['verify' => $certificatePath]);
            }

            $response = $http->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.4, 
                    'maxOutputTokens' => 1000,
                ]
            ]);
            
            if (!$response->successful()) {
                return response()->json(['error' => 'AI Service Error', 'details' => $response->body()], 500);
            }

            $jsonText = $response->json('candidates.0.content.parts.0.text');
            
            $jsonText = preg_replace('/^```json\s*|\s*```$/', '', $jsonText);
            
            $resultData = json_decode($jsonText);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Format JSON dari AI tidak valid.', 
                    'raw' => $jsonText
                ], 500);
            }

            return response()->json($resultData);

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