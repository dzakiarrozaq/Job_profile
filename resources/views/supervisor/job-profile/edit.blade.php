<x-supervisor-layout>
    @if ($errors->any())
        <div class="bg-red-500 text-white p-4 mb-4 rounded-lg">
            <h3 class="font-bold text-lg">⚠️ Gagal Menyimpan!</h3>
            <ul class="list-disc list-inside mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <p class="mt-2 text-sm italic">Cek tab yang relevan untuk memperbaiki.</p>
        </div>
    @endif
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('supervisor.job-profile.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Manajemen Job Profile</a>
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

    <div class="max-w-7xl mx-auto" 
        x-data="{ 
            {{-- LOGIKA: Jika ada error di kompetensi, otomatis buka tab kompetensi --}}
            currentTab: '{{ $errors->has('competencies.*') ? 'kompetensi' : ($errors->has('responsibilities.*') ? 'tanggung_jawab' : ($errors->has('specifications.*') ? 'spesifikasi' : 'identifikasi')) }}',
            isLoadingAI: false,
            positionTitle: {{ Js::from($jobProfile->position->title ?? '') }},
            tujuan_jabatan: {{ Js::from(old('tujuan_jabatan', $jobProfile->tujuan_jabatan ?? '')) }},
            wewenang: {{ Js::from(old('wewenang', $jobProfile->wewenang ?? '')) }},
            dimensi_keuangan: {{ Js::from(old('dimensi_keuangan', $jobProfile->dimensi_keuangan ?? '')) }},
            dimensi_non_keuangan: {{ Js::from(old('dimensi_non_keuangan', $jobProfile->dimensi_non_keuangan ?? '')) }},
            responsibilities: {{ Js::from($responsibilitiesData) }},
            competencies: {{ Js::from($competenciesData) }},
            specifications: {{ Js::from($specificationsData) }},
            workRelations: {{ Js::from($workRelationsData) }},
            
            searchQuery: '',
            searchResults: [],
            activeSuggestionIndex: -1,
            
            addRow(type) {
                const key = 'new_' + Date.now();
                if (type === 'responsibilities') this.responsibilities.push({ key: key, id: null, description: '', expected_result: '' });
                if (type === 'workRelations') this.workRelations.push({ key: key, id: null, type: 'internal', unit_instansi: '', purpose: '' });
                if (type === 'competencies') this.competencies.push({ key: key, id: null, competency_master_id: null, competency_code: '', competency_name: '', type: 'teknis', ideal_level: 3, weight: 1.0 });
                if (type === 'specifications') this.specifications.push({ key: key, id: null, type: 'pendidikan', requirement: '', level_or_notes: '' });
            },
            
            removeRow(type, key) {
                this[type] = this[type].filter(item => item.key !== key);
            },

            async getAiSuggestion(fieldType) {
                this.isLoadingAI = true;
                try {
                    const response = await fetch('{{ route('supervisor.job-profile.suggestText') }}', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        body: JSON.stringify({ position_title: this.positionTitle, field_type: fieldType })
                    });
                    const data = await response.json();
                    
                    if (data.error) throw new Error(data.details || data.error);

                    if (fieldType === 'tanggung_jawab') {
                        this.responsibilities = data.map((r, i) => ({ ...r, key: 'ai_' + i, id: null }));
                    } else if (fieldType === 'tujuan_jabatan') {
                        this.tujuan_jabatan = data.text;
                    } else if (fieldType === 'wewenang') {
                        this.wewenang = data.text;
                    }
                } catch (e) {
                    alert('Gagal mengambil data AI: ' + e.message);
                } finally {
                    this.isLoadingAI = false;
                }
            },
            
            async searchCompetencies(query, index) {
                console.log('Searching:', query, 'for index:', index);
                this.activeSuggestionIndex = index; 
                
                if (query.length < 2) {
                    this.searchResults = [];
                    return;
                }
                
                if (this.competencies[index].competency_master_id) {
                    this.competencies[index].competency_master_id = null;
                    this.competencies[index].competency_code = '';
                    this.competencies[index].type = '';
                }
                
                try {
                    const url = `{{ route('supervisor.competencies.search') }}?q=${encodeURIComponent(query)}`;
                    const response = await fetch(url);
                    this.searchResults = await response.json();
                    console.log('Search results:', this.searchResults);
                } catch (e) {
                    console.error('Search error:', e);
                    this.searchResults = [];
                }
            },
            
            selectCompetency(competency, index) {
                console.log('Selected:', competency);
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
            <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg">
                <p class="font-bold">Error!</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('supervisor.job-profile.update', $jobProfile->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                        <button type="button" @click="currentTab = 'identifikasi'" :class="{ 'border-indigo-500 text-indigo-600': currentTab === 'identifikasi', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': currentTab !== 'identifikasi' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            1. Identifikasi & Tujuan
                        </button>
                        <button type="button" @click="currentTab = 'dimensi'" :class="{ 'border-indigo-500 text-indigo-600': currentTab === 'dimensi', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': currentTab !== 'dimensi' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            2. Dimensi & Hubungan Kerja
                        </button>
                        <button type="button" @click="currentTab = 'tanggung_jawab'" :class="{ 'border-indigo-500 text-indigo-600': currentTab === 'tanggung_jawab', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': currentTab !== 'tanggung_jawab' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            3. Tanggung Jawab
                        </button>
                        <button type="button" @click="currentTab = 'kompetensi'" :class="{ 'border-indigo-500 text-indigo-600': currentTab === 'kompetensi', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': currentTab !== 'kompetensi' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            4. Kompetensi
                        </button>
                        <button type="button" @click="currentTab = 'spesifikasi'" :class="{ 'border-indigo-500 text-indigo-600': currentTab === 'spesifikasi', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': currentTab !== 'spesifikasi' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            5. Spesifikasi Lain
                        </button>
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
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Direktorat:</span>
                            <span class="text-gray-900 dark:text-white">{{ $jobProfile->position->directorate->name ?? 'N/A' }}</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Departemen:</span>
                            <span class="text-gray-900 dark:text-white">{{ $jobProfile->position->department->name ?? 'N/A' }}</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Unit:</span>
                            <span class="text-gray-900 dark:text-white">{{ $jobProfile->position->unit->name ?? 'N/A' }}</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 block">Jabatan Atasan:</span>
                            <span class="text-gray-900 dark:text-white">{{ $jobProfile->position->atasan->title ?? 'N/A' }}</span>
                        </div>
                    </div>
                    
                    <hr class="dark:border-gray-700">
                    
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Tujuan & Wewenang</h3>
                    
                    <div>
                        <label for="tujuan_jabatan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tujuan Jabatan</label>
                        <button type="button" @click.prevent="getAiSuggestion('tujuan_jabatan')" :disabled="isLoadingAI" class="text-xs text-blue-600 hover:underline mb-1 disabled:opacity-50">(Generate with AI)</button>
                        <textarea id="tujuan_jabatan" name="tujuan_jabatan" x-model="tujuan_jabatan" rows="4" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>
                    
                    <div>
                        <label for="wewenang" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wewenang</label>
                        <button type="button" @click.prevent="getAiSuggestion('wewenang')" :disabled="isLoadingAI" class="text-xs text-blue-600 hover:underline mb-1 disabled:opacity-50">(Generate with AI)</button>
                        <textarea id="wewenang" name="wewenang" x-model="wewenang" rows="4" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>
                </div>

                <div x-show="currentTab === 'dimensi'" class="p-6 lg:p-8 space-y-6">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">2. Dimensi Jabatan</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="dimensi_keuangan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dimensi Keuangan</label>
                            <textarea id="dimensi_keuangan" name="dimensi_keuangan" x-model="dimensi_keuangan" rows="3" 
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                      placeholder="Contoh: Biaya sesuai dengan RKAP Tahunan"></textarea>
                        </div>
                        <div>
                            <label for="dimensi_non_keuangan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dimensi Non-Keuangan</label>
                            <textarea id="dimensi_non_keuangan" name="dimensi_non_keuangan" x-model="dimensi_non_keuangan" rows="3" 
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                      placeholder="Contoh: Nilai alat kerja sesuai dengan daftar asset"></textarea>
                        </div>
                    </div>
                    
                    <hr class="dark:border-gray-700">
                    
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Hubungan Kerja</h3>
                        <button type="button" @click.prevent="addRow('workRelations')" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            <ion-icon name="add-outline" class="mr-1"></ion-icon> Tambah Relasi
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <template x-for="(row, index) in workRelations" :key="row.key">
                            <div class="grid grid-cols-12 gap-4 p-4 border rounded-lg dark:border-gray-700">
                                <div class="col-span-3">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Tipe</label>
                                    <input type="hidden" :name="'workRelations['+index+'][id]'" x-model="row.id">
                                    <select x-model="row.type" :name="'workRelations['+index+'][type]'" class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="internal">Internal</option>
                                        <option value="external">Eksternal</option>
                                    </select>
                                </div>
                                <div class="col-span-4">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Unit/Instansi</label>
                                    <input type="text" x-model="row.unit_instansi" :name="'workRelations['+index+'][unit_instansi]'" 
                                           class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                           placeholder="Contoh : Seluruh Unit Kerja / Vendor">
                                </div>
                                <div class="col-span-4">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Tujuan</label>
                                    <textarea x-model="row.purpose" :name="'workRelations['+index+'][purpose]'" rows="2" 
                                              class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                              placeholder="Contoh : Koordinasi requirement fitur"></textarea>
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <button type="button" @click.prevent="removeRow('workRelations', row.key)" class="text-red-500 hover:text-red-700">
                                        <ion-icon name="trash-outline" class="text-2xl"></ion-icon>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div x-show="currentTab === 'tanggung_jawab'" class="p-6 lg:p-8 space-y-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">3. Tanggung Jawab</h3>
                        <div class="flex gap-2">
                             <button type="button" @click.prevent="getAiSuggestion('tanggung_jawab')" :disabled="isLoadingAI" 
                                     class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                <ion-icon name="sparkles-outline" class="mr-1"></ion-icon> Generate AI
                            </button>
                            <button type="button" @click.prevent="addRow('responsibilities')" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                                <ion-icon name="add-outline" class="mr-1"></ion-icon> Tambah Baris
                            </button>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <template x-for="(row, index) in responsibilities" :key="row.key">
                            <div class="grid grid-cols-12 gap-4 p-4 border rounded-lg dark:border-gray-700">
                                <div class="col-span-5">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggung Jawab</label>
                                    <input type="hidden" :name="'responsibilities['+index+'][id]'" x-model="row.id">
                                    <textarea x-model="row.description" :name="'responsibilities['+index+'][description]'" rows="3" 
                                              class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                </div>
                                <div class="col-span-5">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Hasil yang Diharapkan</label>
                                    <textarea x-model="row.expected_result" :name="'responsibilities['+index+'][expected_result]'" rows="3" 
                                              class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                </div>
                                <div class="col-span-2 flex items-center justify-end">
                                    <button type="button" @click.prevent="removeRow('responsibilities', row.key)" class="text-red-500 hover:text-red-700">
                                        <ion-icon name="trash-outline" class="text-2xl"></ion-icon>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                <div x-show="currentTab === 'kompetensi'" class="p-6 lg:p-8 space-y-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">4. Kompetensi</h3>
                        <button type="button" @click.prevent="addRow('competencies')" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            <ion-icon name="add-outline" class="mr-1"></ion-icon> Tambah Kompetensi
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Tipe</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Kode</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Nama Kompetensi (Cari di sini)</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Level Ideal</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Bobot</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="(row, index) in competencies" :key="row.key">
                                    <tr class="bg-white dark:bg-gray-800">
                                        <td class="p-2">
                                            <input type="hidden" :name="'competencies['+index+'][competency_name]'" x-model="row.competency_name">
                                            <input type="hidden" :name="'competencies['+index+'][competency_code]'" x-model="row.competency_code">
                                            <input type="text" x-model="row.type" :name="'competencies['+index+'][type]'" 
                                                   class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:text-white bg-gray-100 dark:bg-gray-800" readonly>
                                        </td>
                                        <td class="p-2">
                                            <input type="text" x-model="row.competency_code" :name="'competencies['+index+'][competency_code]'" 
                                                   class="w-full rounded-md border-gray-300 text-sm  dark:border-gray-600 dark:text-white bg-gray-100 dark:bg-gray-800" readonly>
                                        </td>
                                        <td class="p-2 relative">
                                            {{-- 1. INPUT HIDDEN (WAJIB ADA agar data terkirim ke Controller) --}}
                                            <input type="hidden" :name="'competencies['+index+'][competency_master_id]'" x-model="row.competency_master_id">
                                            <input type="hidden" :name="'competencies['+index+'][competency_code]'" x-model="row.competency_code">

                                            {{-- 2. INPUT TEXT PENCARIAN --}}
                                            <input type="text" 
                                                x-model="row.competency_name"
                                                @keyup.debounce.300ms="searchCompetencies(row.competency_name, index)"
                                                @focus="if(row.competency_name.length >= 2) searchCompetencies(row.competency_name, index)"
                                                :name="'competencies['+index+'][competency_name]'" 
                                                class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                :class="{ 'border-red-500': row.competency_master_id === null && row.competency_name.length > 0 }"
                                                placeholder="Ketik min 2 huruf..."
                                                autocomplete="off">

                                            {{-- 3. INDIKATOR ID TERPILIH (Agar Anda tahu ID sudah masuk) --}}
                                            <span class="block mt-1 text-[10px] text-green-600 font-bold" 
                                                x-show="row.competency_master_id" 
                                                x-text="'✅ ID Terpilih: ' + row.competency_master_id">
                                            </span>

                                            {{-- 4. KOTAK SUGGESTION (DROPDOWN) --}}
                                            <div x-show="activeSuggestionIndex === index && searchResults.length > 0" 
                                                @click.away="searchResults = []; activeSuggestionIndex = -1;" 
                                                class="absolute z-50 left-0 right-0 bg-white dark:bg-gray-700 shadow-lg rounded-md mt-1 max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-600">
                                                
                                                <template x-for="result in searchResults" :key="result.id">
                                                    <div @click="selectCompetency(result, index)" 
                                                        class="p-3 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer border-b border-gray-100 dark:border-gray-600 last:border-b-0">
                                                        <div class="font-bold dark:text-white" x-text="result.competency_name"></div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            <span x-text="'Kode: ' + result.competency_code"></span>
                                                            <span x-text="' | Tipe: ' + result.type"></span>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                            
                                            {{-- 5. PESAN ERROR JIKA LUPA KLIK --}}
                                            <p x-show="row.competency_master_id === null && row.competency_name.length > 0" 
                                            class="text-xs text-red-500 mt-1">
                                                ⚠️ Klik pilihan di list agar ID tersimpan
                                            </p>
                                        </td>
                                        <td class="p-2">
                                            <select x-model="row.ideal_level" :name="'competencies['+index+'][ideal_level]'" 
                                                    class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                            </select>
                                        </td>
                                        <td class="p-2">
                                            <input type="number" step="0.1" x-model="row.weight" :name="'competencies['+index+'][weight]'" 
                                                   class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                                   placeholder="1.0">
                                        </td>
                                        <td class="p-2 text-center">
                                            <button type="button" @click.prevent="removeRow('competencies', row.key)" x-show="competencies.length > 0" 
                                                    class="text-red-500 hover:text-red-700">
                                                <ion-icon name="trash-outline" class="text-xl"></ion-icon>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="currentTab === 'spesifikasi'" class="p-6 lg:p-8 space-y-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">5. Spesifikasi Lain</h3>
                        <button type="button" @click.prevent="addRow('specifications')" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            <ion-icon name="add-outline" class="mr-1"></ion-icon> Tambah Spesifikasi
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Tipe</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Persyaratan</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Level/Catatan</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="(row, index) in specifications" :key="row.key">
                                    <tr class="bg-white dark:bg-gray-800">
                                        <td class="p-2">
                                            <input type="hidden" :name="'specifications['+index+'][id]'" x-model="row.id">
                                            <select x-model="row.type" :name="'specifications['+index+'][type]'" 
                                                    class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option value="pendidikan">Pendidikan</option>
                                                <option value="pengalaman">Pengalaman Kerja</option>
                                                <option value="sertifikasi">Sertifikasi</option>
                                                <option value="bahasa">Bahasa</option>
                                                <option value="komputer">Komputer</option>
                                                <option value="kesehatan">Kesehatan</option>
                                            </select>
                                        </td>
                                        <td class="p-2">
                                            <input type="text" x-model="row.requirement" :name="'specifications['+index+'][requirement]'" 
                                                   class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                                   placeholder="Contoh: S1 / Bahasa Inggris">
                                        </td>
                                        <td class="p-2">
                                            <input type="text" x-model="row.level_or_notes" :name="'specifications['+index+'][level_or_notes]'" 
                                                   class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                                   placeholder="Contoh: Minimal S1 / Level Basic">
                                        </td>
                                        <td class="p-2 text-center">
                                            <button type="button" @click.prevent="removeRow('specifications', row.key)" 
                                                    class="text-red-500 hover:text-red-700">
                                                <ion-icon name="trash-outline" class="text-xl"></ion-icon>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-4 border-t border-gray-200 dark:border-gray-700 p-6">
                    <a href="{{ route('supervisor.job-profile.index') }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Simpan Perubahan Job Profile
                    </button>
                </div>
            </div>
        </form>
    </div>
    {{-- SCRIPT: Otomatis Buka Tab yang Error --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if ($errors->any())
                // Logika sederhana: Cek field mana yang error, lalu buka tab-nya
                const errors = @json($errors->keys());
                let targetTab = 'identifikasi';

                if (errors.some(e => e.includes('competencies'))) targetTab = 'kompetensi';
                else if (errors.some(e => e.includes('responsibilities'))) targetTab = 'tanggung_jawab';
                else if (errors.some(e => e.includes('specifications'))) targetTab = 'spesifikasi';
                else if (errors.some(e => e.includes('workRelations'))) targetTab = 'dimensi'; // asumsi tab 2

                // Manipulasi Alpine.js state dari luar (agak tricky tapi bisa via dispatch)
                // Cara termudah: Cari elemen root x-data dan ubah __x.$data.currentTab
                const root = document.querySelector('[x-data]');
                if(root) {
                    root.__x.$data.currentTab = targetTab;
                }
            @endif
        });
    </script>
</x-supervisor-layout>