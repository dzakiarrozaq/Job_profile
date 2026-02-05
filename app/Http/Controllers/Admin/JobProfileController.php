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
use App\Models\Role;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\BandResponsibility;
use App\Models\MasterResponsibility; 
Use App\Imports\MasterResponsibilityImport; // Pastikan ini di-use di atas
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MasterResponsibilityAllImport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log; 
use App\Imports\TechnicalStandardImport;


class JobProfileController extends Controller
{
    /**
     * Menampilkan daftar Job Profile.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        
        // Tentukan role aktif (dari session atau default)
        $activeRoleName = $request->session()->get('active_role_name') 
                        ?? ($user->hasRole('Admin') ? 'Admin' : 'Supervisor');

        // 1. LOGIKA UNTUK ADMIN
        if ($activeRoleName === 'Admin') {
            $query = JobProfile::with(['position.organization', 'creator'])
                ->latest();

            // Filter Pencarian berdasarkan Nama Jabatan
            if ($request->filled('search')) {
                $query->whereHas('position', function($q) use ($request) {
                    $q->where('title', 'like', "%{$request->search}%");
                });
            }

            // Filter Status
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            return view('admin.job-profile.index', [
                'jobProfiles' => $query->paginate(10),
                'filters' => $request->all()
            ]);
        }

        // 2. LOGIKA UNTUK SUPERVISOR (GM/SM/Manager)
        $supervisorPosition = $user->position;

        if (!$supervisorPosition) {
            return view('supervisor.job-profile.index', [
                'jobProfiles' => collect([]),
                'totalCount' => 0
            ]);
        }

        // Ambil semua ID posisi di bawah hirarki (Recursive)
        $allSubordinatePositionIds = $supervisorPosition->getAllSubordinateIds();

        // Query Job Profile yang posisi-nya ada dalam hirarki bawahan
        $query = JobProfile::whereIn('position_id', $allSubordinatePositionIds)
            ->with(['position.organization', 'creator'])
            ->latest();

        if ($request->filled('search')) {
            $query->whereHas('position', function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%");
            });
        }

        $jobProfiles = $query->paginate(10);

        return view('supervisor.job-profile.index', [
            'jobProfiles' => $jobProfiles,
            'filters' => $request->all()
        ]);
    }

    /**
     * Menampilkan form untuk membuat Job Profile baru.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // 1. Tentukan Role Aktif (Gunakan method hasRole yang baru kita buat di Model User)
        $activeRoleName = $request->session()->get('active_role_name');
        if (!$activeRoleName) {
            $activeRoleName = $user->hasRole('Admin') ? 'Admin' : 'Supervisor';
        }

        // 2. Query Dasar Posisi yang Belum Punya Job Profile
        $query = Position::whereDoesntHave('jobProfile')
                        ->with(['organization.parent.parent'])
                        ->orderBy('title', 'asc');

        // 3. Jika Supervisor, Batasi Hanya Posisi Bawahan (Recursive)
        if ($activeRoleName === 'Supervisor') {
            if (!$user->position) {
                return redirect()->back()->with('error', 'Akses Ditolak: Posisi Anda tidak terdaftar.');
            }

            $subordinateIds = $user->position->getAllSubordinateIds();
            if (empty($subordinateIds)) {
                return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak memiliki bawahan.');
            }

            $query->whereIn('id', $subordinateIds);
        }

        $positions = $query->get();

        // =================================================================
        // 4. LOGIKA AUTO-POPULATE TEKNIS (PENTING)
        // =================================================================
        $pakemTechnicals = [];
        $selectedPositionId = $request->query('position_id');

        if ($selectedPositionId) {
            // Ambil standar kompetensi teknis dari tabel jembatan (asumsi nama tabel: position_technical_standards)
            $standards = \DB::table('position_technical_standards')
                ->join('competencies_master', 'position_technical_standards.competency_master_id', '=', 'competencies_master.id')
                ->where('position_id', $selectedPositionId)
                ->select('competencies_master.*', 'position_technical_standards.ideal_level')
                ->get();

            $pakemTechnicals = $standards->map(function($std) {
                // Tarik behavior level 1-5 untuk kamus di tabel
                $behaviors = \DB::table('competency_key_behaviors')
                    ->where('competency_master_id', $std->id)
                    ->whereIn('level', [1,2,3,4,5])
                    ->orderBy('level', 'asc')
                    ->get();

                return [
                    'key' => 'pakem_' . $std->id,
                    'id' => null, // null karena belum masuk ke job_competencies
                    'competency_master_id' => $std->id,
                    'competency_name' => $std->competency_name,
                    'type' => 'Teknis',
                    'ideal_level' => $std->ideal_level,
                    'behaviors' => $behaviors
                ];
            });
        }

        // 5. Tentukan View Berdasarkan Role
        $viewPath = ($activeRoleName === 'Admin') ? 'admin.job-profile.create' : 'supervisor.job-profile.create';

        return view($viewPath, [
            'positions' => $positions,
            'pakemTechnicals' => $pakemTechnicals, // Kirim data pakem ke Blade
            'selectedPositionId' => $selectedPositionId
        ]);
    }

    /**
     * Menyimpan Job Profile baru & Otomatis mengisi Tanggung Jawab Pakem.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi Input Dasar
        $validated = $request->validate([
            'position_id' => 'required|integer|exists:positions,id', 
        ]);

        $user = Auth::user();
        
        // Tentukan Role Aktif untuk penentuan prefix route nantinya
        $activeRoleName = $request->session()->get('active_role_name');
        if (!$activeRoleName) {
            $activeRoleName = $user->hasRole('Admin') ? 'Admin' : 'Supervisor';
        }

        // Load Position beserta data pendukungnya
        $targetPosition = Position::with(['organization', 'jobGrade'])->find($validated['position_id']);

        // Validasi Duplikasi
        if (JobProfile::where('position_id', $validated['position_id'])->exists()) {
            return redirect()->back()->with('error', 'Job Profile untuk posisi ini sudah ada.')->withInput();
        }

        DB::beginTransaction();
        try {
            // 1. Buat Header Job Profile
            $jobProfile = JobProfile::create([
                'position_id' => $validated['position_id'],
                'created_by'  => $user->id,
                'version'     => 1,
                'status'      => 'draft', 
            ]);

            $position = $targetPosition;

            // PENTING: Cek Job Grade
            if (!$position->job_grade_id) {
                throw new \Exception("Posisi ini belum memiliki Job Grade (Band). Silakan atur Master Posisi terlebih dahulu.");
            }

            // Tentukan Tipe (structural / functional)
            $familyRaw = strtoupper($position->job_family ?? '');
            $type = str_contains($familyRaw, 'STRUKTURAL') ? 'structural' : 'functional';

            // =================================================================
            // 2. AUTO-POPULATE TANGGUNG JAWAB (Master)
            // =================================================================
            $masters = \App\Models\MasterResponsibility::where('job_grade_id', $position->job_grade_id)
                ->where(function($q) use ($type) {
                    $q->where('type', $type)->orWhere('type', 'general');
                })->get();

            foreach ($masters as $master) {
                $jobProfile->responsibilities()->create([
                    'description'     => $master->responsibility, 
                    'expected_result' => $master->expected_outcome ?? '-', 
                ]);
            }

            // =================================================================
            // 3. AUTO-POPULATE KOMPETENSI PERILAKU (Matrix Band)
            // =================================================================
            $matrixCompetencies = DB::table('competency_matrices')
                ->where('job_grade_id', $position->job_grade_id)
                ->where('type', $type)
                ->get();

            if ($matrixCompetencies->isNotEmpty()) {
                foreach ($matrixCompetencies as $matrix) {
                    $masterComp = \App\Models\CompetenciesMaster::find($matrix->competency_master_id);
                    if ($masterComp) {
                        $jobProfile->competencies()->create([
                            'competency_master_id' => $masterComp->id,
                            'competency_name'      => $masterComp->competency_name,
                            'type'                 => 'Perilaku', 
                            'ideal_level'          => 1, // Default, nantinya diupdate di halaman edit
                            'weight'               => 1,
                        ]);
                    }
                }
            }

            // =================================================================
            // 4. AUTO-POPULATE KOMPETENSI TEKNIS (Sesuai Recap Excel)
            // =================================================================
            // Kita tarik data dari tabel jembatan yang menyimpan 'pakem' teknis per posisi
            $technicalStandards = DB::table('position_technical_standards')
                ->where('position_id', $position->id)
                ->get();

            foreach ($technicalStandards as $std) {
                $masterTech = \App\Models\CompetenciesMaster::find($std->competency_master_id);
                if ($masterTech) {
                    $jobProfile->competencies()->create([
                        'competency_master_id' => $masterTech->id,
                        'competency_name'      => $masterTech->competency_name,
                        'type'                 => 'Teknis',
                        'ideal_level'          => $std->ideal_level, // Gunakan level standar dari Recap
                        'weight'               => 1,
                    ]);
                }
            }

            DB::commit();

            // 5. Dynamic Redirect berdasarkan Role
            $routePrefix = ($activeRoleName === 'Admin') ? 'admin' : 'supervisor';

            return redirect()
                ->route($routePrefix . '.job-profile.edit', $jobProfile->id)
                ->with('success', 'Job Profile berhasil dibuat dengan standar Tanggung Jawab & Kompetensi posisi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit Job Profile yang ada.
     */
    public function edit(Request $request, JobProfile $jobProfile)
    {
        $user = Auth::user();
        
        // 1. Tentukan Role Aktif (Gunakan fallback hasRole jika session kosong)
        $activeRoleName = $request->session()->get('active_role_name');
        if (!$activeRoleName) {
            $activeRoleName = $user->hasRole('Admin') ? 'Admin' : 'Supervisor';
        }

        // 2. SECURITY CHECK: Jika Supervisor, pastikan hanya bisa edit profil bawahannya
        if ($activeRoleName === 'Supervisor') {
            if (!$user->position) {
                abort(403, 'Akses Ditolak: Posisi Anda tidak terdaftar.');
            }

            $subordinateIds = $user->position->getAllSubordinateIds();
            
            // Cek apakah position_id dari Job Profile ini ada dalam hirarki bawahan
            if (!in_array($jobProfile->position_id, $subordinateIds)) {
                abort(403, 'Anda tidak memiliki otoritas untuk mengubah Job Profile di luar hirarki tim Anda.');
            }
        }

        // 3. Eager Load Relations (Sama seperti kode Anda, dioptimasi)
        $jobProfile->load([
            'responsibilities', 
            'competencies.competency.keyBehaviors', 
            'specifications', 
            'workRelations', 
            'position.organization.parent.parent', 
            'position.jobGrade'
        ]);

        $allJobCompetencies = $jobProfile->competencies;

        // 4. Mapping Data Kompetensi TEKNIS
        $technicalsData = $allJobCompetencies
            ->filter(function($c) {
                $masterType = strtolower(trim(optional($c->competency)->type ?? ''));
                return !str_contains($masterType, 'perilaku');
            })
            ->map(fn($comp) => [
                'key' => 'tech_' . $comp->id,
                'id' => $comp->id,
                'competency_master_id' => $comp->competency_master_id,
                'competency_name' => $comp->competency_name,
                'type' => 'Teknis',
                'ideal_level' => $comp->ideal_level,
                'behaviors' => optional($comp->competency)->keyBehaviors->whereIn('level', [1,2,3,4,5])->values() ?? []
            ])->values()->toArray();

        // 5. Mapping Data Kompetensi PERILAKU
        $behavioralsData = $allJobCompetencies
            ->filter(function($c) {
                $masterType = strtolower(trim(optional($c->competency)->type ?? ''));
                return str_contains($masterType, 'perilaku');
            })
            ->map(fn($comp) => [
                'key' => 'beh_' . $comp->id,
                'id' => $comp->id,
                'competency_master_id' => $comp->competency_master_id,
                'competency_name' => $comp->competency_name,
                'description' => optional($comp->competency)->description ?? '-',
                'ideal_level' => $comp->ideal_level,
                'behaviors' => optional($comp->competency)->keyBehaviors->where('level', 0)->values() ?? []
            ])->values()->toArray();

        // 6. Mapping Data Pendukung (Responsibilities, Specs, WorkRelations)
        $responsibilitiesData = $jobProfile->responsibilities->map(fn($r) => [
            'key' => 'db_'.$r->id, 'id' => $r->id, 'description' => $r->description, 'expected_result' => $r->expected_result
        ])->toArray();
        
        $specificationsData = $jobProfile->specifications->map(fn($s) => [
            'key' => 'db_'.$s->id, 'id' => $s->id, 'type' => $s->type, 'requirement' => $s->requirement, 'level_or_notes' => $s->level_or_notes
        ])->toArray();
        
        $workRelationsData = $jobProfile->workRelations->map(fn($w) => [
            'key' => 'db_'.$w->id, 'id' => $w->id, 'type' => $w->type, 'unit_instansi' => $w->unit_instansi, 'purpose' => $w->purpose
        ])->toArray();

        // 7. Ambil daftar posisi sesuai otoritas untuk dropdown ganti posisi (jika diperlukan)
        $positionsQuery = Position::orderBy('title', 'asc');
        if ($activeRoleName === 'Supervisor') {
            $positionsQuery->whereIn('id', $user->position->getAllSubordinateIds());
        }
        $positions = $positionsQuery->get();

        // 8. Tentukan View
        $viewPath = ($activeRoleName === 'Admin') ? 'admin.job-profile.edit' : 'supervisor.job-profile.edit';

        return view($viewPath, compact(
            'jobProfile', 'positions', 'responsibilitiesData', 
            'technicalsData', 'behavioralsData', 'specificationsData', 'workRelationsData'
        ));
    }

    /**
     * Memperbarui Job Profile di database.
     */
    public function update(Request $request, JobProfile $jobProfile)
    {
        $input = $request->all();

        // 1. Tentukan Status Baru
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

        DB::beginTransaction();
        try {
            // 2. Update Header
            $jobProfile->update([
                'tujuan_jabatan'       => $input['tujuan_jabatan'] ?? null,
                'wewenang'             => $input['wewenang'] ?? null,
                'dimensi_keuangan'     => $input['dimensi_keuangan'] ?? null,
                'dimensi_non_keuangan' => $input['dimensi_non_keuangan'] ?? null,
                'version'              => $jobProfile->version + 1,
                'status'               => $newStatus,
            ]);

            // =================================================================
            // 3. PROSES KOMPETENSI (FIXED)
            // =================================================================
            
            // A. KOMPETENSI TEKNIS
            // -----------------------------------------------------------------
            $inputTechnicals = $request->input('technicals', []);
            $techIdsToKeep = [];

            foreach ($inputTechnicals as $tech) {
                if (empty($tech['competency_master_id'])) continue;

                // Ambil data dari Master untuk memastikan nama kompetensi ikut tersimpan
                $master = \App\Models\CompetenciesMaster::find($tech['competency_master_id']);
                if (!$master) continue;

                // Pastikan ID hanya dikirim jika memang angka (data dari DB)
                // Jika isinya 'tech_...' atau null, kita anggap data baru
                $existingId = (isset($tech['id']) && is_numeric($tech['id'])) ? $tech['id'] : null;

                $comp = $jobProfile->competencies()->updateOrCreate(
                    [
                        'id' => $existingId,
                    ],
                    [
                        'competency_master_id' => $master->id,
                        'competency_name'      => $master->competency_name, // WAJIB ADA AGAR TIDAK ERROR
                        'ideal_level'          => (int)($tech['ideal_level'] ?? 1),
                        'type'                 => 'Teknis', // Paksa tipenya
                        'weight'               => 1
                    ]
                );
                $techIdsToKeep[] = $comp->id;
            }

            // Hapus yang tidak ada di form (kecuali Perilaku)
            $jobProfile->competencies()
                ->where('type', '!=', 'Perilaku') 
                ->whereNotIn('id', $techIdsToKeep)
                ->delete();


            // B. KOMPETENSI PERILAKU (Pakem)
            // -----------------------------------------------------------------
            $inputBehaviorals = $request->input('behaviorals', []);
            
            foreach ($inputBehaviorals as $beh) {
                // Perilaku biasanya sudah ada sejak awal (dibuat saat 'store')
                // Jadi kita cukup update level-nya saja berdasarkan ID pivot-nya
                if (!empty($beh['id']) && is_numeric($beh['id'])) {
                    $jobProfile->competencies()
                        ->where('id', $beh['id'])
                        ->where('type', 'Perilaku')
                        ->update([
                            'ideal_level' => (int)($beh['ideal_level'] ?? 1)
                        ]);
                }
            }


            // 4. Update Tabel Lain (Responsibilities, Specs, WorkRelations)
            // Menggunakan delete -> createMany (Simple approach)
            
            // Responsibilities
            $jobProfile->responsibilities()->delete();
            if (!empty($input['responsibilities'])) {
                $validResps = array_filter($input['responsibilities'], fn($r) => !empty($r['description']));
                $jobProfile->responsibilities()->createMany(array_values($validResps));
            }

            // Specifications (Gabungan 6 Array)
            $rawSpecifications = array_merge(
                $request->input('educations', []),
                $request->input('experiences', []),
                $request->input('certifications', []),
                $request->input('languages', []),
                $request->input('computers', []),
                $request->input('healths', [])
            );
            $cleanSpecifications = array_filter($rawSpecifications, fn($item) => !empty($item['requirement']));
            
            $jobProfile->specifications()->delete();
            if (!empty($cleanSpecifications)) {
                $jobProfile->specifications()->createMany(array_values($cleanSpecifications));
            }

            // Work Relations
            $jobProfile->workRelations()->delete();
            if (!empty($input['workRelations'])) {
                $validRelations = array_filter($input['workRelations'], fn($w) => !empty($w['unit_instansi']));
                $jobProfile->workRelations()->createMany(array_values($validRelations));
            }

            DB::commit();

            // Redirect Logic
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
            // Kembalikan ke form edit dengan pesan error (Jangan dd() di production)
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
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
        
        // Pastikan load 'keyBehaviors' agar deskripsinya terbawa ke frontend
        $competencies = \App\Models\CompetenciesMaster::with(['keyBehaviors' => function($q) {
                            $q->orderBy('level', 'asc');
                        }])
                        ->where('competency_name', 'LIKE', "%{$query}%")
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

    public function getResponsibilitiesByBand(Request $request)
    {
        $band = $request->query('band');
        
        // Cari data berdasarkan band
        $data = BandResponsibility::where('band', $band)->first();

        if ($data) {
            return response()->json([
                'success' => true,
                'responsibility' => $data->responsibility
            ]);
        }

        return response()->json(['success' => false, 'responsibility' => '']);
    }

    public function importMasterResponsibilities(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls', 
        ]);

        try {
            $file = $request->file('file');
            
            // 1. SCAN NAMA SHEET
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheetNames = $spreadsheet->getSheetNames();

            // 2. FILTER SHEET YANG COCOK
            $sheetMap = [];
            foreach ($sheetNames as $sheetName) {
                // Cari sheet yang namanya mengandung "BAND" dan Angka
                if (preg_match('/BAND\s*(\d+)/i', $sheetName, $matches)) {
                    $band = $matches[1]; 
                    
                    // Tentukan Tipe
                    $type = 'general';
                    if (stripos($sheetName, 'STR') !== false || stripos($sheetName, 'STRUKTURAL') !== false) {
                        $type = 'structural';
                    } elseif (stripos($sheetName, 'FSL') !== false || stripos($sheetName, 'FUNGSIONAL') !== false) {
                        $type = 'functional';
                    }

                    $sheetMap[$sheetName] = ['band' => $band, 'type' => $type];
                }
            }

            if (empty($sheetMap)) {
                return back()->with('error', 'Gagal: Tidak ada sheet yang cocok (harus mengandung kata "BAND" dan Angka).');
            }

            // 3. EKSEKUSI IMPORT
            // Kita kirim $sheetMap ke class Import
            \Maatwebsite\Excel\Facades\Excel::import(new MasterResponsibilityAllImport($sheetMap), $file);

            return back()->with('success', "Sukses! Berhasil mengimport " . count($sheetMap) . " sheet.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }

    public function importTechnicalStandard(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            // Eksekusi import
            Excel::import(new TechnicalStandardImport, $request->file('file'));

            return back()->with('success', 'Standar Kompetensi Teknis per Posisi berhasil diimport.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}