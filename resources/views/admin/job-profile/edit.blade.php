<x-admin-layout>
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #999; }
    </style>

    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('admin.job-profile.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Manajemen Job Profile</a>
            <ion-icon name="chevron-forward-outline" class="mx-2 text-gray-400"></ion-icon>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                Edit Job Profile: {{ $jobProfile->position->title ?? 'N/A' }} (v{{ $jobProfile->version }})
            </h2>
        </div>
    </x-slot>

    @php
        $responsibilitiesData = old('responsibilities', $jobProfile->responsibilities->map(fn($r) => ['key' => 'db_'.$r->id, 'id' => $r->id, 'description' => $r->description, 'expected_result' => $r->expected_result])->toArray());
        
        $competenciesData = old('competencies', $jobProfile->competencies->map(function($comp) {
            return [
                'key' => 'db_' . $comp->id,
                'competency_master_id' => $comp->competency_master_id,
                'competency_code' => optional($comp->master)->competency_code ?? 'N/A',
                'competency_name' => optional($comp->master)->competency_name ?? 'N/A',
                'type' => optional($comp->master)->type ?? 'N/A',
                'ideal_level' => $comp->ideal_level,
                'weight' => $comp->weight
            ];
        })->toArray());
        
        $specificationsData = old('specifications', $jobProfile->specifications->map(fn($s) => ['key' => 'db_'.$s->id, 'id' => $s->id, 'type' => $s->type, 'requirement' => $s->requirement, 'level_or_notes' => $s->level_or_notes])->toArray());
        
        $workRelationsData = old('workRelations', $jobProfile->workRelations->map(fn($w) => ['key' => 'db_'.$w->id, 'id' => $w->id, 'type' => $w->type, 'unit_instansi' => $w->unit_instansi, 'purpose' => $w->purpose])->toArray());
    @endphp

    @php
        $org = $jobProfile->position->organization;
        $parent = $org->parent ?? null;       
        $grandparent = $parent->parent ?? null; 

        $namaUnit = '-';
        $namaSection = '-';
        $namaDepartemen = 'N/A';

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

    <div class="max-w-7xl mx-auto" 
         x-data="{ 
            currentTab: 'tujuan',
            isLoadingAI: false,
            positionTitle: '',
            tujuan_jabatan: '',
            wewenang: '',
            dimensi_keuangan: '',
            dimensi_non_keuangan: '',
            
            // Array Data Utama
            responsibilities: [],
            competencies: [],
            workRelations: [],

            // Array Spesifikasi (Dipecah jadi 6)
            educations: [],
            experiences: [],
            certifications: [],
            languages: [],
            computers: [],
            healths: [],
            
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
                
                // 2. Load Relasi (Tanggung Jawab, Kompetensi, Hubungan Kerja)
                this.responsibilities = {{ Js::from(old('responsibilities', $jobProfile->responsibilities->map(fn($r) => ['key' => 'db_'.$r->id, 'id' => $r->id, 'description' => $r->description, 'expected_result' => $r->expected_result]))) }};
                
                this.workRelations = {{ Js::from(old('workRelations', $jobProfile->workRelations->map(fn($w) => ['key' => 'db_'.$w->id, 'id' => $w->id, 'type' => $w->type, 'unit_instansi' => $w->unit_instansi, 'purpose' => $w->purpose]))) }};

                this.competencies = {{ Js::from(old('competencies', $jobProfile->competencies->map(function($comp) {
                    return [
                        'key' => 'db_' . $comp->id,
                        'competency_master_id' => $comp->competency_master_id,
                        'competency_code' => optional($comp->master)->competency_code ?? 'N/A',
                        'competency_name' => optional($comp->master)->competency_name ?? 'N/A',
                        'type' => optional($comp->master)->type ?? 'N/A',
                        'ideal_level' => $comp->ideal_level,
                        'weight' => $comp->weight
                    ];
                }))) }};
                
                // 3. LOGIKA MEMECAH SPESIFIKASI MENJADI 6 KATEGORI
                const rawSpecs = {{ Js::from(old('specifications', $jobProfile->specifications->map(fn($s) => ['key' => 'db_'.$s->id, 'id' => $s->id, 'type' => $s->type, 'requirement' => $s->requirement, 'level_or_notes' => $s->level_or_notes]))) }};

                // Helper function untuk filter dan inisialisasi
                const loadSpec = (type) => {
                    const filtered = rawSpecs.filter(s => s.type === type);
                    // Jika kosong, isi 1 baris default agar form muncul
                    return filtered.length ? filtered : [{ key: 'new_'+type+'_'+Date.now(), id: null, type: type, requirement: '', level_or_notes: '' }];
                };

                this.educations = loadSpec('pendidikan');
                this.experiences = loadSpec('pengalaman');
                this.certifications = loadSpec('sertifikasi');
                this.languages = loadSpec('bahasa');
                this.computers = loadSpec('komputer');
                this.healths = loadSpec('kesehatan');
            },
            
            // Fungsi Tambah Baris Universal
            addRow(type) {
                const key = 'new_' + Date.now() + Math.random(); // Key unik
                
                // Relasi Umum
                if (type === 'responsibilities') this.responsibilities.push({ key: key, id: null, description: '', expected_result: '' });
                if (type === 'workRelations') this.workRelations.push({ key: key, id: null, type: 'internal', unit_instansi: '', purpose: '' });
                if (type === 'competencies') this.competencies.push({ key: key, id: null, competency_master_id: null, competency_code: '', competency_name: '', type: 'teknis', ideal_level: 3, weight: 1.0 });
                
                // Spesifikasi (6 Kategori)
                if (type === 'educations') this.educations.push({ key: key, id: null, type: 'pendidikan', requirement: '', level_or_notes: '' });
                if (type === 'experiences') this.experiences.push({ key: key, id: null, type: 'pengalaman', requirement: '', level_or_notes: '' });
                if (type === 'certifications') this.certifications.push({ key: key, id: null, type: 'sertifikasi', requirement: '', level_or_notes: '' });
                if (type === 'languages') this.languages.push({ key: key, id: null, type: 'bahasa', requirement: '', level_or_notes: '' });
                if (type === 'computers') this.computers.push({ key: key, id: null, type: 'komputer', requirement: '', level_or_notes: '' });
                if (type === 'healths') this.healths.push({ key: key, id: null, type: 'kesehatan', requirement: '', level_or_notes: '' });
            },
            
            // Fungsi Hapus Baris Universal
            removeRow(arrName, key) {
                // Untuk spesifikasi, jika sisa 1 jangan dihapus, tapi dikosongkan (opsional, tapi bagus utk UX)
                const specsArrays = ['educations', 'experiences', 'certifications', 'languages', 'computers', 'healths'];
                
                if (specsArrays.includes(arrName)) {
                    if (this[arrName].length > 1) {
                        this[arrName] = this[arrName].filter(item => item.key !== key);
                    } else {
                        // Reset baris terakhir
                        this[arrName][0].requirement = '';
                        this[arrName][0].level_or_notes = '';
                    }
                } else {
                    // Untuk tabel lain (kompetensi dll) hapus saja
                    this[arrName] = this[arrName].filter(item => item.key !== key);
                }
            },

            // ... (Fungsi AI dan Search Kompetensi SAMA SEPERTI SEBELUMNYA) ...
            async getAiSuggestion(fieldType) {
                this.isLoadingAI = true;
                try {
                    const response = await (await fetch('{{ route('admin.job-profile.suggestText') }}', {
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
            
            async searchCompetencies(query, index) {
                this.activeSuggestionIndex = index; 
                if (query.length < 2) { this.searchResults = []; return; }
                
                this.competencies[index].competency_master_id = null; // Reset ID jika ketik ulang
                
                try {
                    const url = `{{ route('admin.competencies.search') }}?q=${encodeURIComponent(query)}`;
                    const response = await (await fetch(url)).json();
                    this.searchResults = response;
                } catch (e) { this.searchResults = []; }
            },
            
            selectCompetency(competency, index) {
                this.competencies[index].competency_master_id = competency.id;
                this.competencies[index].competency_code = competency.competency_code;
                this.competencies[index].competency_name = competency.competency_name;
                this.competencies[index].type = competency.type;
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

        <form action="{{ route('admin.job-profile.update', $jobProfile->id) }}" method="POST">
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
                
                <div x-show="currentTab === 'identifikasi'" class="p-6 lg:p-8 space-y-6">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">1. Identifikasi Jabatan (v{{ $jobProfile->version }})</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Jabatan:</span>
                            <span class="text-gray-900 dark:text-white">{{ $jobProfile->position->title ?? 'N/A' }}</span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Tingkat (Job Grade):</span>
                            <span class="text-gray-900 dark:text-white">{{ $jobProfile->position->jobGrade->name ?? 'N/A' }}</span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Departemen:</span>
                            <span class="text-gray-900 dark:text-white font-bold text-indigo-600">
                                {{ $namaDepartemen }}
                            </span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Section:</span>
                            <span class="text-gray-900 dark:text-white">
                                {{ $namaSection }}
                            </span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Unit:</span>
                            <span class="text-gray-900 dark:text-white">
                                {{ $namaUnit }}
                            </span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Jabatan Atasan:</span>
                            <span class="text-gray-900 dark:text-white">{{ $jobProfile->position->atasan->title ?? 'N/A' }}</span>
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

                <div x-show="currentTab === 'kompetensi'" class="p-6 space-y-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold dark:text-gray-100">4. Kompetensi</h3>
                        <button type="button" @click.prevent="addRow('competencies')" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">+ Tambah</button>
                    </div>
                    <div class="overflow-x-visible">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left">Tipe</th>
                                    <th class="px-4 py-2 text-left">Kode</th>
                                    <th class="px-4 py-2 text-left w-1/3">Nama Kompetensi</th>
                                    <th class="px-4 py-2 text-center">Level</th>
                                    <th class="px-4 py-2 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="(row, index) in competencies" :key="row.key">
                                    <tr class="bg-white dark:bg-gray-800">
                                        <td class="p-2"><input type="text" x-model="row.type" readonly class="w-full text-xs bg-gray-100 rounded border-0"></td>
                                        <td class="p-2"><input type="text" x-model="row.competency_code" readonly class="w-full text-xs bg-gray-100 rounded border-0"></td>
                                        <td class="p-2 relative">
                                            <input type="hidden" :name="'competencies['+index+'][competency_master_id]'" x-model="row.competency_master_id">
                                            <input type="text" x-model="row.competency_name" 
                                                   @keyup.debounce.300ms="searchCompetencies(row.competency_name, index)"
                                                   @focus="if(row.competency_name.length>=2) searchCompetencies(row.competency_name, index)"
                                                   class="w-full rounded border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                   :class="{'border-red-500': !row.competency_master_id}" placeholder="Cari...">
                                            
                                            <div x-show="activeSuggestionIndex === index && searchResults.length > 0" 
                                                 @click.away="searchResults = []; activeSuggestionIndex = -1;"
                                                 class="absolute z-50 w-full bg-white dark:bg-gray-700 shadow-xl border rounded mt-1 max-h-48 overflow-y-auto">
                                                <template x-for="res in searchResults" :key="res.id">
                                                    <div @click="selectCompetency(res, index)" class="p-2 hover:bg-blue-50 dark:hover:bg-gray-600 cursor-pointer border-b">
                                                        <div class="font-bold text-xs" x-text="res.competency_name"></div>
                                                        <div class="text-[10px] text-gray-500" x-text="res.competency_code"></div>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                        <td class="p-2">
                                            <select :name="'competencies['+index+'][ideal_level]'" x-model="row.ideal_level" class="w-full rounded border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option>
                                            </select>
                                        </td>
                                        <td class="p-2 text-center">
                                            <button type="button" @click.prevent="removeRow('competencies', row.key)" class="text-red-500"><ion-icon name="trash-outline"></ion-icon></button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

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
                    <a href="{{ route('admin.job-profile.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Batal</a>
                    
                    <button type="submit" name="action" value="save_draft" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm flex items-center">
                        <ion-icon name="save-outline" class="mr-2"></ion-icon> Simpan Draf
                    </button>

                    <button type="submit" name="action" value="submit_verification" onclick="return confirm('Simpan dan verifikasi?')" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm flex items-center">
                        <ion-icon name="checkmark-circle-outline" class="mr-2"></ion-icon> Simpan & Verifikasi
                    </button>
                </div>
            </div>
        </form>
    </div>

    
</x-admin-layout>