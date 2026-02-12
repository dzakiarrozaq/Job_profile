<x-supervisor-layout>
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #999; }
    </style>

    <x-slot name="header">
        <div class="flex items-center">
            {{-- PERUBAHAN ROUTE --}}
            <a href="{{ route('supervisor.job-profile.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Manajemen Job Profile</a>
            <ion-icon name="chevron-forward-outline" class="mx-2 text-gray-400"></ion-icon>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                Edit Job Profile: {{ $jobProfile->position->title ?? 'N/A' }} (v{{ $jobProfile->version }})
            </h2>
        </div>
    </x-slot>

    @php
        // A. AMBIL DATA EXISTING (YANG SUDAH TERSIMPAN DI PROFILE INI)
        $allComps = $jobProfile->competencies;

        // Filter Kompetensi Teknis yang sudah tersimpan
        $savedTechnicals = $allComps
            ->filter(fn($c) => strtolower(trim($c->type)) !== 'perilaku')
            ->map(fn($comp) => [
                'key' => 'saved_' . $comp->id,
                'id' => $comp->id,
                'competency_master_id' => $comp->competency_master_id,
                'competency_name' => $comp->competency_name,
                'ideal_level' => $comp->ideal_level,
                // Ambil behaviors dari relasi competency master
                'behaviors' => optional($comp->competency)->keyBehaviors?->whereIn('level', [1,2,3,4,5])->values() ?? [],
                'is_standard' => false // Penanda ini data manual/existing
            ]);

        // B. AMBIL DATA PAKEM (DARI TABEL STANDARD YANG BARU DI-IMPORT)
        // Kita load standard beserta relasi master & behaviors-nya untuk efisiensi
        $standardComps = $jobProfile->position->technicalStandards()
            ->with(['competencyMaster.keyBehaviors'])
            ->get();

        // Mapping data pakem ke format array JS
        $pakemTechnicals = $standardComps->map(fn($std) => [
            'key' => 'std_' . $std->id,
            'id' => null, // Null karena belum tersimpan di tabel job_profile_competencies
            'competency_master_id' => $std->competency_master_id,
            'competency_name' => $std->competencyMaster->competency_name ?? 'Unknown',
            'ideal_level' => $std->ideal_level,
            'behaviors' => $std->competencyMaster->keyBehaviors?->whereIn('level', [1,2,3,4,5])->values() ?? [],
            'is_standard' => true // Penanda ini data pakem
        ]);

        // C. GABUNGKAN (MERGE)
        // Logika: Tampilkan yang Saved. Jika Pakem belum ada di Saved, tambahkan Pakem.
        
        // Ambil ID Master yang sudah ada di Saved agar tidak duplikat
        $existingMasterIds = $savedTechnicals->pluck('competency_master_id')->toArray();

        // Filter Pakem yang BELUM ada di Saved
        $newStandards = $pakemTechnicals->filter(function($item) use ($existingMasterIds) {
            return !in_array($item['competency_master_id'], $existingMasterIds);
        });

        // Gabungkan Saved + New Standards
        $finalTechnicals = $savedTechnicals->merge($newStandards)->values()->toArray();

        // D. FINALISASI DATA UNTUK VIEW
        // Gunakan old() jika ada validasi error, jika tidak gunakan hasil merge di atas
        $technicals = old('technicals', $finalTechnicals);

        // Kompetensi Perilaku (Tetap sama seperti sebelumnya)
        $behaviorals = old('behaviorals', $allComps
            ->filter(fn($c) => strtolower(trim($c->type)) === 'perilaku')
            ->map(fn($comp) => [
                'key' => 'beh_' . $comp->id,
                'id' => $comp->id,
                'competency_master_id' => $comp->competency_master_id,
                'competency_name' => $comp->competency_name,
                'description' => optional($comp->competency)->description ?? '-', 
                
                // === UBAH BARIS INI ===
                // Paksa jadi integer. Jika null, default ke 1.
                'ideal_level' => (int) ($comp->ideal_level ?? 1), 
                
                'behaviors' => optional($comp->competency)->keyBehaviors->where('level', 0)->values() ?? []
            ])->values()->toArray());
    @endphp

    @php
        $org = $jobProfile->position->organization;
        $parent = $org->parent ?? null;       
        $grandparent = $parent->parent ?? null; 

        $namaUnit = '-';
        $namaSection = '-';
        $namaDepartemen = 'N/A';

        $responsibilities = old('responsibilities', $responsibilitiesData ?? []);
        $technicals = old('technicals', $technicalsData ?? []);
        $behaviorals = old('behaviorals', $behavioralsData ?? []);
        $workRelations = old('workRelations', $workRelationsData ?? []);
        $specifications = old('specifications', $specificationsData ?? []);

        if ($grandparent) {
            $namaUnit = $org->name;
            $namaSection = $parent->name;
            $namaDepartemen = $grandparent->name;
        } elseif ($parent) {
            $namaUnit = '-'; 
            $namaSection = $org->name; 
            $namaDepartemen = $parent->name;
        } else {
            $namaDepartemen = $org->name ?? 'N/A';
        }
    @endphp

    <div class="max-w-7xl mx-auto" \
            x-data="{ 
                currentTab: 'tujuan',
                isLoadingAI: false,
                positionTitle: '',
                tujuan_jabatan: '',
                wewenang: '',
                dimensi_keuangan: '',
                dimensi_non_keuangan: '',
                
                // --- PERUBAHAN 1: MEMISAHKAN ARRAY ---
                responsibilities: [],
                workRelations: [],
                technicals: [],  
                behaviorals: [], 

                // Array Spesifikasi
                educations: [], experiences: [], certifications: [], 
                languages: [], computers: [], healths: [],
                
                searchQuery: '',
                searchResults: [],
                activeSuggestionIndex: -1,
                
                init() {
                    // 1. Load Data Dasar
                    this.positionTitle = {{ Js::from($jobProfile->position->title ?? '') }};
                    this.tujuan_jabatan = {{ Js::from(old('tujuan_jabatan', $jobProfile->tujuan_jabatan ?? '')) }};
                    this.wewenang = {{ Js::from(old('wewenang', $jobProfile->wewenang ?? '')) }};
                    this.dimensi_keuangan = {{ Js::from(old('dimensi_keuangan', $jobProfile->dimensi_keuangan ?? '')) }};
                    this.dimensi_non_keuangan = {{ Js::from(old('dimensi_non_keuangan', $jobProfile->dimensi_non_keuangan ?? '')) }};
                    
                    // 2. Load Relasi
                    this.responsibilities = {{ Js::from($responsibilitiesData) }};
                    this.workRelations = {{ Js::from($workRelationsData) }};

                    // --- PERUBAHAN 2: LOAD DATA TERPISAH ---
                    // Pastikan di bagian PHP atas file sudah mendefinisikan $technicals dan $behaviorals
                    this.technicals = {{ Js::from($technicals) }};
                    this.behaviorals = {{ Js::from($behaviorals) }};
                    
                    // 3. Load Spesifikasi
                    const rawSpecs = {{ Js::from($specificationsData) }};
                    const loadSpec = (type) => {
                        const filtered = rawSpecs.filter(s => s.type === type);
                        return filtered.length ? filtered : [{ key: 'new_'+type+'_'+Date.now(), id: null, type: type, requirement: '', level_or_notes: '' }];
                    };

                    this.educations = loadSpec('pendidikan');
                    this.experiences = loadSpec('pengalaman');
                    this.certifications = loadSpec('sertifikasi');
                    this.languages = loadSpec('bahasa');
                    this.computers = loadSpec('komputer');
                    this.healths = loadSpec('kesehatan');
                },
                
                // Fungsi Tambah Baris
                addRow(type) {
                    const key = 'new_' + Date.now() + Math.random(); 

                    if (type === 'responsibilities') this.responsibilities.push({ key: key, id: null, description: '', expected_result: '' });
                    if (type === 'workRelations') this.workRelations.push({ key: key, id: null, type: 'internal', unit_instansi: '', purpose: '' });

                    // --- PERUBAHAN 3: ADD ROW HANYA UNTUK TEKNIS ---
                    // Tidak ada logika addRow untuk 'behaviorals' karena itu Pakem
                    if (type === 'technicals') {
                        this.technicals.push({ 
                            key: key, 
                            id: null, 
                            competency_master_id: null, 
                            competency_name: '', 
                            type: 'Teknis', // Paksa tipe Teknis
                            ideal_level: 1, 
                            behaviors: [] 
                        });
                    }

                    // Specs
                    if (type === 'educations') this.educations.push({ key: key, id: null, type: 'pendidikan', requirement: '', level_or_notes: '' });
                    if (type === 'experiences') this.experiences.push({ key: key, id: null, type: 'pengalaman', requirement: '', level_or_notes: '' });
                    if (type === 'certifications') this.certifications.push({ key: key, id: null, type: 'sertifikasi', requirement: '', level_or_notes: '' });
                    if (type === 'languages') this.languages.push({ key: key, id: null, type: 'bahasa', requirement: '', level_or_notes: '' });
                    if (type === 'computers') this.computers.push({ key: key, id: null, type: 'komputer', requirement: '', level_or_notes: '' });
                    if (type === 'healths') this.healths.push({ key: key, id: null, type: 'kesehatan', requirement: '', level_or_notes: '' });
                },
                
                // Fungsi Hapus Baris
                removeRow(arrName, key) {
                    const specsArrays = ['educations', 'experiences', 'certifications', 'languages', 'computers', 'healths'];
                    
                    // --- PERUBAHAN 4: REMOVE ROW TEKNIS ---
                    if (arrName === 'technicals') {
                        this.technicals = this.technicals.filter(item => item.key !== key);
                    }
                    else if (specsArrays.includes(arrName)) {
                        if (this[arrName].length > 1) {
                            this[arrName] = this[arrName].filter(item => item.key !== key);
                        } else {
                            this[arrName][0].requirement = '';
                            this[arrName][0].level_or_notes = '';
                        }
                    } else {
                        // Default remove (responsibilities, workRelations)
                        this[arrName] = this[arrName].filter(item => item.key !== key);
                    }
                },

                // AI Suggestion (Tidak Berubah)
                async getAiSuggestion(fieldType) {
                    this.isLoadingAI = true;
                    try {
                        const response = await (await fetch('{{ route('supervisor.job-profile.suggestText') }}', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            body: JSON.stringify({ position_title: this.positionTitle, field_type: fieldType })
                        })).json();
                        
                        if (response.error) throw new Error(response.details || response.error);

                        if (fieldType === 'tanggung_jawab') {
                            this.responsibilities = response.map((r, i) => ({ ...r, key: 'ai_' + i, id: null }));
                        } else if (fieldType === 'tujuan_jabatan') {
                            this.tujuan_jabatan = response.text;
                        } else if (fieldType === 'wewenang') {
                            this.wewenang = response.text;
                        }
                    } catch (e) {
                        alert('Gagal mengambil data AI: ' + e.message);
                    } finally {
                        this.isLoadingAI = false;
                    }
                },
                
                // --- PERUBAHAN 5: SEARCH HANYA KE ARRAY TECHNICALS ---
                async searchCompetencies(query, index) {
                    this.activeSuggestionIndex = index; 
                    if (query.length < 2) { this.searchResults = []; return; }
                    
                    // Reset ID di array technicals
                    this.technicals[index].competency_master_id = null; 
                    
                    try {
                        const url = `{{ route('supervisor.competencies.search') }}?q=${encodeURIComponent(query)}`;
                        const response = await (await fetch(url)).json();
                        this.searchResults = response;
                    } catch (e) { this.searchResults = []; }
                },
                
                // --- PERUBAHAN 6: SELECT HANYA KE ARRAY TECHNICALS ---
                selectCompetency(item, index) {
                    this.technicals[index].competency_master_id = item.id;
                    this.technicals[index].competency_name = item.competency_name;
                    this.technicals[index].behaviors = item.key_behaviors || [];

                    this.searchResults = [];
                    this.activeSuggestionIndex = -1;
                }
            }"
            x-init="init()">
        
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
            </div>
        @endif

        {{-- FORM UPDATE KE ROUTE SUPERVISOR --}}
        <form action="{{ route('supervisor.job-profile.update', $jobProfile->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                    <nav class="-mb-px flex space-x-8 px-6 min-w-max" aria-label="Tabs">
                        @foreach(['identifikasi' => '1. Identifikasi', 'dimensi' => '2. Dimensi', 'tanggung_jawab' => '3. Tanggung Jawab', 'kompetensi' => '4. Kompetensi', 'spesifikasi' => '5. Spesifikasi Lain'] as $key => $label)
                            <button type="button" @click="currentTab = '{{ $key }}'" 
                                    :class="{ 'border-indigo-500 text-indigo-600': currentTab === '{{ $key }}', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': currentTab !== '{{ $key }}' }"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                {{ $label }}
                            </button>
                        @endforeach
                    </nav>
                </div>
                
                {{-- TAB 1: IDENTIFIKASI --}}
                <div x-show="currentTab === 'identifikasi'" class="p-6 lg:p-8 space-y-6">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">1. Identifikasi Jabatan (v{{ $jobProfile->version }})</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Jabatan:</span>
                            <span class="text-gray-900 dark:text-white font-medium">{{ $jobProfile->position->title ?? 'N/A' }}</span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Tingkat (Job Grade):</span>
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    Band {{ $jobProfile->position->jobGrade->name ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Jalur Karir (Job Family):</span>
                            <div class="mt-1">
                                @php
                                    $familyRaw = strtoupper($jobProfile->position->job_family ?? '');
                                    $badgeClass = 'bg-gray-100 text-gray-800 border-gray-200';
                                    if (str_contains($familyRaw, 'STRUKTURAL')) {
                                        $badgeClass = 'bg-purple-100 text-purple-800 border border-purple-200 dark:bg-purple-900 dark:text-purple-200 dark:border-purple-700';
                                    } elseif (str_contains($familyRaw, 'FUNGSIONAL')) {
                                        $badgeClass = 'bg-teal-100 text-teal-800 border border-teal-200 dark:bg-teal-900 dark:text-teal-200 dark:border-teal-700';
                                    }
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                    {{ $jobProfile->position->job_family ?? 'Belum Ditentukan' }}
                                </span>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Jabatan Atasan:</span>
                            <span class="text-gray-900 dark:text-white">{{ $jobProfile->position->atasan->title ?? '-' }}</span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Departemen:</span>
                            <span class="text-gray-900 dark:text-white font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $namaDepartemen ?? '-' }}
                            </span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Unit (Biro):</span>
                            <span class="text-gray-900 dark:text-white">{{ $namaUnit ?? '-' }}</span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md md:col-span-2">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Section (Seksi):</span>
                            <span class="text-gray-900 dark:text-white">{{ $namaSection ?? '-' }}</span>
                        </div>
                    </div>
                    <hr class="dark:border-gray-700">
                    <div>
                        <label class="block text-sm font-medium mb-1 dark:text-gray-300">Tujuan Jabatan</label>
                        <button type="button" @click.prevent="getAiSuggestion('tujuan_jabatan')" :disabled="isLoadingAI" class="text-xs text-blue-600 hover:underline mb-1 disabled:opacity-50">(Generate AI)</button>
                        <textarea name="tujuan_jabatan" x-model="tujuan_jabatan" rows="4" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1 dark:text-gray-300">Wewenang</label>
                        <button type="button" @click.prevent="getAiSuggestion('wewenang')" :disabled="isLoadingAI" class="text-xs text-blue-600 hover:underline mb-1 disabled:opacity-50">(Generate AI)</button>
                        <textarea name="wewenang" x-model="wewenang" rows="4" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>
                </div>

                {{-- TAB 2: DIMENSI --}}
                <div x-show="currentTab === 'dimensi'" class="p-6 lg:p-8 space-y-6">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">2. Dimensi & Hubungan Kerja</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Dimensi Keuangan</label>
                            <textarea name="dimensi_keuangan" x-model="dimensi_keuangan" rows="3" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Dimensi Non-Keuangan</label>
                            <textarea name="dimensi_non_keuangan" x-model="dimensi_non_keuangan" rows="3" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center mt-6">
                        <h4 class="font-bold dark:text-gray-200">Hubungan Kerja</h4>
                        <button type="button" @click.prevent="addRow('workRelations')" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">+ Tambah</button>
                    </div>
                    <div class="space-y-4">
                        <template x-for="(row, index) in workRelations" :key="row.key">
                            <div class="grid grid-cols-12 gap-4 p-4 border rounded-lg dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                <input type="hidden" :name="'workRelations['+index+'][id]'" x-model="row.id">
                                <div class="col-span-3">
                                    <label class="text-xs text-gray-500">Tipe</label>
                                    <select x-model="row.type" :name="'workRelations['+index+'][type]'" class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="internal">Internal</option>
                                        <option value="external">Eksternal</option>
                                    </select>
                                </div>
                                <div class="col-span-4">
                                    <label class="text-xs text-gray-500">Unit/Instansi</label>
                                    <input type="text" x-model="row.unit_instansi" :name="'workRelations['+index+'][unit_instansi]'" class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                                <div class="col-span-4">
                                    <label class="text-xs text-gray-500">Tujuan</label>
                                    <input type="text" x-model="row.purpose" :name="'workRelations['+index+'][purpose]'" class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <button type="button" @click.prevent="removeRow('workRelations', row.key)" class="text-red-500 hover:text-red-700"><ion-icon name="trash-outline" class="text-xl"></ion-icon></button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- TAB 3: TANGGUNG JAWAB --}}
                <div x-show="currentTab === 'tanggung_jawab'" class="p-6 lg:p-8 space-y-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold dark:text-gray-100">3. Tanggung Jawab</h3>
                        <div class="flex gap-2">
                            <button type="button" @click.prevent="getAiSuggestion('tanggung_jawab')" :disabled="isLoadingAI" class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 disabled:opacity-50">AI Generate</button>
                            <button type="button" @click.prevent="addRow('responsibilities')" class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">+ Tambah</button>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <template x-for="(row, index) in responsibilities" :key="row.key">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 p-4 border rounded-lg bg-gray-50 dark:bg-gray-800/50 dark:border-gray-700">
                                <input type="hidden" :name="'responsibilities['+index+'][id]'" x-model="row.id">
                                <div class="md:col-span-6">
                                    <label class="text-xs text-gray-500">Aktivitas Utama</label>
                                    <textarea x-model="row.description" :name="'responsibilities['+index+'][description]'" rows="2" class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                </div>
                                <div class="md:col-span-5">
                                    <label class="text-xs text-gray-500">Output / Hasil</label>
                                    <textarea x-model="row.expected_result" :name="'responsibilities['+index+'][expected_result]'" rows="2" class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                </div>
                                <div class="md:col-span-1 flex items-center justify-center">
                                    <button type="button" @click.prevent="removeRow('responsibilities', row.key)" class="text-red-500"><ion-icon name="trash-outline" class="text-xl"></ion-icon></button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- TAB 4: KOMPETENSI --}}
                <div x-show="currentTab === 'kompetensi'" class="p-6 space-y-12">
                    
                    {{-- =================================================================
                        4.1 KOMPETENSI TEKNIS (Hard Skills - Editable Table)
                    ================================================================= --}}
                    <div class="space-y-4">
                        <div class="flex justify-between items-center border-b pb-2">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">4.1 Kompetensi Teknis</h3>
                                <p class="text-xs text-gray-500">Tambahkan kompetensi spesifik operasional.</p>
                            </div>
                            <button type="button" @click.prevent="addRow('technicals')" 
                                    class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 flex items-center shadow-sm transition">
                                <ion-icon name="add-circle-outline" class="mr-1"></ion-icon> Tambah Teknis
                            </button>
                        </div>

                        <div class="overflow-x-visible">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-200 uppercase text-xs leading-normal">
                                    <tr>
                                        <th class="px-4 py-3 text-left w-1/2">Nama Kompetensi</th>
                                        <th class="px-4 py-3 text-center w-1/6">Target Level</th>
                                        <th class="px-4 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                
                                <template x-for="(row, index) in technicals" :key="row.key">
                                    <tbody class="border-b dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 transition" 
                                        x-data="{ showGuide: false }">
                                        
                                        <tr class="align-top">
                                            <td class="p-2 relative">
                                                <input type="hidden" :name="'technicals['+index+'][id]'" x-model="row.id">
                                                <input type="hidden" :name="'technicals['+index+'][competency_master_id]'" x-model="row.competency_master_id">
                                                
                                                <div class="relative">
                                                    <input type="text" x-model="row.competency_name"
                                                        @keyup.debounce.300ms="searchCompetencies(row.competency_name, index)"
                                                        class="w-full rounded-md border-gray-300 text-sm font-bold dark:bg-gray-700 dark:text-white pl-8" {{-- Tambah pl-8 --}}
                                                        :class="{'border-red-400 bg-red-50': row.competency_master_id === null && row.competency_name.length > 0, 'bg-orange-50 border-orange-200': row.is_standard}"
                                                        placeholder="Cari kompetensi...">
                                                    
                                                    {{-- Ikon Penanda --}}
                                                    <div class="absolute left-2 top-2.5 text-gray-400">
                                                        {{-- Jika ini Pakem, tampilkan ikon Kunci/Lock --}}
                                                        <template x-if="row.is_standard">
                                                            <ion-icon name="lock-closed" class="text-orange-500" title="Standar Pakem Posisi"></ion-icon>
                                                        </template>
                                                        {{-- Jika Manual, tampilkan ikon Search --}}
                                                        <template x-if="!row.is_standard">
                                                            <ion-icon name="search"></ion-icon>
                                                        </template>
                                                    </div>

                                                    <div class="absolute right-2 top-2.5">
                                                        <span x-show="row.competency_master_id" class="text-green-500"><ion-icon name="checkmark-circle"></ion-icon></span>
                                                    </div>
                                                </div>

                                                <div class="flex justify-between items-center mt-1">
                                                    <button type="button" x-show="row.behaviors && row.behaviors.length > 0" @click="showGuide = !showGuide"
                                                            class="text-[10px] font-bold text-indigo-600 hover:underline flex items-center gap-1 uppercase">
                                                        <ion-icon :name="showGuide ? 'chevron-up-outline' : 'book-outline'"></ion-icon>
                                                        <span x-text="showGuide ? 'Tutup Panduan' : 'Lihat Panduan Level'"></span>
                                                    </button>
                                                </div>

                                                {{-- Suggestions --}}
                                                <div x-show="activeSuggestionIndex === index && searchResults.length > 0" 
                                                    @click.away="searchResults = []"
                                                    class="absolute z-50 left-0 right-0 bg-white dark:bg-gray-700 shadow-xl border rounded-md mt-1 max-h-60 overflow-y-auto">
                                                    <template x-for="res in searchResults" :key="res.id">
                                                        <div @click="selectCompetency(res, index); showGuide = true;" 
                                                            class="p-2.5 hover:bg-blue-50 dark:hover:bg-gray-600 cursor-pointer border-b last:border-0 border-gray-100 flex justify-between items-center group">
                                                            <div>
                                                                <div class="font-bold text-xs text-gray-800 dark:text-white group-hover:text-blue-700 uppercase" x-text="res.competency_name"></div>
                                                                <div class="text-[9px] text-gray-400" x-text="res.competency_code"></div>
                                                            </div>
                                                            <ion-icon name="arrow-forward-circle-outline" class="text-gray-300 group-hover:text-blue-500"></ion-icon>
                                                        </div>
                                                    </template>
                                                </div>
                                            </td>

                                            <td class="p-2">
                                                <select :name="'technicals['+index+'][ideal_level]'" x-model="row.ideal_level" 
                                                        class="w-full rounded-md border-gray-300 text-sm text-center font-bold dark:bg-gray-700 dark:text-white focus:ring-blue-500">
                                                    <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option>
                                                </select>
                                            </td>

                                            <td class="p-2 text-center">
                                                <button type="button" @click.prevent="removeRow('technicals', row.key)" class="text-red-500 hover:text-red-700 p-2 bg-red-50 rounded-full">
                                                    <ion-icon name="trash-outline" class="text-lg"></ion-icon>
                                                </button>
                                            </td>
                                        </tr>

                                        {{-- Baris Panduan Level (Accordion) --}}
                                        <tr x-show="showGuide" x-transition.opacity class="bg-indigo-50/50 dark:bg-gray-900/50 border-t border-indigo-100">
                                            <td colspan="3" class="p-4">
                                                <div class="grid grid-cols-1 gap-2">
                                                    <template x-for="behavior in row.behaviors" :key="behavior.id">
                                                        <div class="flex items-start gap-3 p-3 rounded-md border transition-all"
                                                            :class="parseInt(row.ideal_level) === parseInt(behavior.level) 
                                                                ? 'bg-white border-indigo-400 shadow-sm ring-1 ring-indigo-200 dark:bg-gray-800' 
                                                                : 'bg-white/60 border-transparent opacity-75'">
                                                            <div class="flex-shrink-0 w-12">
                                                                <span class="px-2 py-1 rounded text-[10px] font-bold uppercase block text-center border"
                                                                    :class="parseInt(row.ideal_level) === parseInt(behavior.level) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400'">
                                                                    LVL <span x-text="behavior.level"></span>
                                                                </span>
                                                            </div>
                                                            <div class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed" 
                                                                x-html="behavior.behavior ? behavior.behavior.replace(/(\d+\.\s)/g, '<br>$1') : '-'">
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </template>
                            </table>
                        </div>
                    </div>

                    {{-- =================================================================
                        4.2 KOMPETENSI PERILAKU (Soft Skills - PAKEM / FIXED)
                    ================================================================= --}}
                    <div class="space-y-4 pt-6">
                        <div class="border-b pb-2 flex justify-between items-end">
                            <h3 class="text-lg font-bold text-indigo-700 dark:text-indigo-400">4.2 Kompetensi Perilaku (Matrix Band)</h3>
                            <span class="text-[10px] bg-indigo-100 text-indigo-700 px-2 py-1 rounded font-bold uppercase tracking-wider">Fixed List</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="(row, index) in behaviorals" :key="row.key">
                                <div class="p-4 border rounded-xl bg-white dark:bg-gray-800 shadow-sm border-indigo-100 dark:border-indigo-900">
                                    <input type="hidden" :name="'behaviorals['+index+'][id]'" x-model="row.id">
                                    <input type="hidden" :name="'behaviorals['+index+'][competency_master_id]'" x-model="row.competency_master_id">
                                    <input type="hidden" :name="'behaviorals['+index+'][type]'" value="Perilaku">
                                    
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="pr-2">
                                            <div class="font-bold text-indigo-900 dark:text-indigo-200 text-sm uppercase" x-text="row.competency_name"></div>
                                            <p class="text-[10px] text-gray-400 mt-1 leading-tight" x-text="row.description"></p>
                                        </div>
                                        <div class="flex-shrink-0 text-center">
                                            <label class="text-[9px] text-gray-400 block uppercase font-bold mb-1">Target</label>
                                            
                                            {{-- PERBAIKAN: Tambahkan .number pada x-model dan :value pada option --}}
                                            <select :name="'behaviorals['+index+'][ideal_level]'" 
                                                    x-model.number="row.ideal_level" 
                                                    class="w-16 rounded-lg border-indigo-200 text-xs text-center font-black text-indigo-700 focus:ring-indigo-500 py-1">
                                                <option :value="1">1</option>
                                                <option :value="2">2</option>
                                                <option :value="3">3</option>
                                                <option :value="4">4</option>
                                                <option :value="5">5</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    {{-- Indikator Perilaku Utama (Level 0) --}}
                                    <div class="p-2.5 bg-indigo-50/50 dark:bg-gray-900 rounded-lg border border-indigo-50/50">
                                        <div class="text-[9px] font-bold text-indigo-400 uppercase mb-1">Indikator Perilaku Utama:</div>
                                        <div class="space-y-1.5 custom-scrollbar max-h-40 overflow-y-auto pr-1">
                                            <template x-for="beh in row.behaviors" :key="beh.id">
                                                <div class="text-[11px] text-gray-600 dark:text-gray-400 leading-relaxed flex gap-2">
                                                    <span class="text-indigo-400">•</span>
                                                    <span x-text="beh.behavior"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- TAB 5: SPESIFIKASI --}}
                <div x-show="currentTab === 'spesifikasi'" class="p-6 lg:p-8 space-y-8">
                    <div class="bg-blue-50 text-blue-800 p-3 rounded text-sm mb-4">Lengkapi spesifikasi jabatan di bawah ini.</div>

                    @include('components.admin-spec-section', ['title' => 'A. Pendidikan', 'var' => 'educations', 'type' => 'pendidikan', 'ph1' => 'Jurusan (S1 Teknik)', 'ph2' => 'Catatan (Min IPK 3.00)'])

                    @include('components.admin-spec-section', ['title' => 'B. Pengalaman Kerja', 'var' => 'experiences', 'type' => 'pengalaman', 'ph1' => 'Posisi / Bidang', 'ph2' => 'Lama Kerja (Min 2 Tahun)'])

                    @include('components.admin-spec-section', ['title' => 'C. Sertifikasi', 'var' => 'certifications', 'type' => 'sertifikasi', 'ph1' => 'Nama Sertifikat', 'ph2' => 'Penerbit / Masa Berlaku'])

                    @include('components.admin-spec-section', ['title' => 'D. Bahasa', 'var' => 'languages', 'type' => 'bahasa', 'ph1' => 'Bahasa', 'ph2' => 'Level (Fasih/Pasif)'])

                    @include('components.admin-spec-section', ['title' => 'E. Komputer / Teknis', 'var' => 'computers', 'type' => 'komputer', 'ph1' => 'Software / Skill', 'ph2' => 'Level Kemahiran'])

                    @include('components.admin-spec-section', ['title' => 'F. Kesehatan / Fisik', 'var' => 'healths', 'type' => 'kesehatan', 'ph1' => 'Persyaratan', 'ph2' => 'Keterangan'])
                </div>

                <div class="flex justify-end gap-4 border-t border-gray-200 dark:border-gray-700 p-6 bg-gray-50 dark:bg-gray-900 rounded-b-xl">
                    <a href="{{ route('supervisor.job-profile.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Batal</a>
                    
                    <button type="submit" name="action" value="save_draft" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm flex items-center">
                        <ion-icon name="save-outline" class="mr-2"></ion-icon> Simpan Draf
                    </button>

                    <button type="submit" name="action" value="approve" onclick="return confirm('Simpan dan Setujui?')" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm flex items-center">
                        <ion-icon name="checkmark-circle-outline" class="mr-2"></ion-icon> Simpan & Setujui
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-supervisor-layout>