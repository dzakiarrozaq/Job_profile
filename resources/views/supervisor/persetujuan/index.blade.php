<x-supervisor-layout>
    <x-slot name="header">
        <style>
            /* Utility untuk menyembunyikan scrollbar */
            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        </style>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Pusat Persetujuan
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Daftar antrean persetujuan yang membutuhkan tindakan Anda.
        </p>
    </x-slot>
    
    @php
        $posOrg = $profile->position->organization ?? null;
        $parent = $posOrg->parent ?? null;
        $grandparent = $parent->parent ?? null;

        $unitName = '-';
        $sectionName = '-';
        $deptName = 'N/A';

        if ($grandparent) {
            // 3 Level (Unit -> Section -> Dept)
            $unitName = $posOrg->name;
            $sectionName = $parent->name;
            $deptName = $grandparent->name;
        } elseif ($parent) {
            // 2 Level (Section -> Dept)
            $sectionName = $posOrg->name;
            $deptName = $parent->name;
        } else {
            // 1 Level
            $deptName = $posOrg->name ?? 'N/A';
        }
    @endphp

    {{-- Data State & Tab Management --}}
    <div class="max-w-7xl mx-auto space-y-6" x-data="{ activeTab: 'assessment' }">

        {{-- NAVIGATION TABS --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border-b border-gray-200 dark:border-gray-700 overflow-x-auto no-scrollbar">
            <nav class="-mb-px flex px-6 space-x-8 min-w-max" aria-label="Tabs">
                
                {{-- Tab 1: Assessment --}}
                <button @click="activeTab = 'assessment'" 
                        :class="activeTab === 'assessment' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                    1. Penilaian Kompetensi
                    @if($assessments->count() > 0)
                        <span class="ml-2 bg-indigo-100 text-indigo-800 py-0.5 px-2 rounded-full text-xs font-bold animate-pulse">{{ $assessments->count() }}</span>
                    @endif
                </button>

                {{-- Tab 2: Pelatihan --}}
                <button @click="activeTab = 'catalog'" 
                        :class="activeTab === 'catalog' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                    2. Usulan Pelatihan
                    @if($trainings->count() > 0)
                        <span class="ml-2 bg-blue-100 text-blue-800 py-0.5 px-2 rounded-full text-xs font-bold animate-pulse">{{ $trainings->count() }}</span>
                    @endif
                </button>

                {{-- Tab 3: Job Profile --}}
                <button @click="activeTab = 'jobprofile'" 
                        :class="activeTab === 'jobprofile' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                    3. Usulan Job Profile
                    @if($jobProfiles->count() > 0)
                        <span class="ml-2 bg-yellow-100 text-yellow-800 py-0.5 px-2 rounded-full text-xs font-bold animate-pulse">{{ $jobProfiles->count() }}</span>
                    @endif
                </button>

                {{-- Tab 4: IDP --}}
                <button @click="activeTab = 'idp'" 
                        :class="activeTab === 'idp' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                    4. IDP (Rencana)
                    @if(isset($pendingIdps) && $pendingIdps->count() > 0)
                        <span class="ml-2 bg-green-100 text-green-800 py-0.5 px-2 rounded-full text-xs font-bold animate-pulse">{{ $pendingIdps->count() }}</span>
                    @endif
                </button>

                {{-- Tab 5: Sertifikat (BARU) --}}
                <button @click="activeTab = 'certificate'" 
                        :class="activeTab === 'certificate' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                    5. Verifikasi Sertifikat
                    @if(isset($pendingCertificates) && $pendingCertificates->count() > 0)
                        <span class="ml-2 bg-purple-100 text-purple-800 py-0.5 px-2 rounded-full text-xs font-bold animate-pulse">{{ $pendingCertificates->count() }}</span>
                    @endif
                </button>

            </nav>
        </div>

        {{-- CONTENT 1: ASSESSMENT --}}
        <div x-show="activeTab === 'assessment'" class="space-y-4" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
             
            @forelse($assessments as $item)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-indigo-500 hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-4 w-full md:w-auto">
                            {{-- FOTO PROFIL DARI DATABASE --}}
                            <img class="h-12 w-12 rounded-full object-cover ring-2 ring-indigo-50" 
                                 src="{{ $item->user->profile_photo_path ? asset('storage/' . $item->user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($item->user->name) . '&color=7F9CF5&background=EBF4FF' }}" 
                                 alt="Foto {{ $item->user->name }}">
                            
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $item->user->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $item->user->position->title ?? 'Posisi tidak diketahui' }}</p>
                                <div class="flex items-center gap-2 mt-1 text-xs text-gray-400">
                                    <ion-icon name="time-outline"></ion-icon> 
                                    Diajukan: {{ $item->submitted_at ? \Carbon\Carbon::parse($item->submitted_at)->format('d M Y') : '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                            <a href="{{ route('supervisor.penilaian.show', $item->user_id) }}" 
                               class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 flex items-center shadow-sm transition-transform active:scale-95">
                                <ion-icon name="create-outline" class="mr-2"></ion-icon> Verifikasi Penilaian
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 border-dashed">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 mb-4">
                        <ion-icon name="checkmark-done-circle-outline" class="text-4xl text-indigo-400"></ion-icon>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Semua Bersih!</h3>
                    <p class="text-gray-500">Semua penilaian kompetensi sudah selesai.</p>
                </div>
            @endforelse
        </div>

        {{-- CONTENT 2: TRAINING CATALOG (MERGED PER USER) --}}
        <div x-show="activeTab === 'catalog'" class="space-y-4" style="display: none;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100">
            
            @php
                $groupedTrainings = $trainings->groupBy('user_id');
            @endphp

            @forelse($groupedTrainings as $userId => $userPlans)
                @php
                    $user = $userPlans->first()->user;
                    
                    // Gabungkan item dari semua plan user ini menjadi satu list
                    $allItems = $userPlans->flatMap->items; 
                @endphp

                {{-- CARD PER USER --}}
                <div x-data="{ expanded: false }" class="bg-white dark:bg-gray-800 rounded-lg shadow mb-4 border-l-4 border-indigo-500 hover:shadow-md transition-all">
                    
                    {{-- HEADER: Info User & Ringkasan --}}
                    <div class="p-6 flex flex-col md:flex-row justify-between items-center gap-4 cursor-pointer" @click="expanded = !expanded">
                        <div class="flex items-center gap-4 w-full">
                            {{-- Foto --}}
                            <img class="h-14 w-14 rounded-full object-cover ring-2 ring-indigo-50" 
                                src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                                alt="{{ $user->name }}">

                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 dark:text-white text-lg flex items-center gap-2">
                                    {{ $user->name }}
                                    {{-- Badge Jumlah Item --}}
                                    <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full border border-indigo-200">
                                        {{ $allItems->count() }} Pelatihan Diajukan
                                    </span>
                                </h4>
                                <p class="text-sm text-gray-500 mb-1">{{ $user->position->title ?? 'Posisi Staff' }}</p>
                                
                                {{-- Ringkasan Status --}}
                                <div class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                    <ion-icon name="time-outline"></ion-icon> 
                                    Menunggu persetujuan Anda
                                </div>
                            </div>

                            {{-- Icon Toggle --}}
                            <div class="text-gray-400">
                                <ion-icon :name="expanded ? 'chevron-up-circle' : 'chevron-down-circle'" class="text-2xl transition-transform text-indigo-500"></ion-icon>
                            </div>
                        </div>
                    </div>

                    {{-- BODY: Daftar Gabungan Pelatihan --}}
                    <div x-show="expanded" x-collapse class="bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700">
                        
                        {{-- List Item --}}
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($allItems as $item)
                                <div class="p-4 hover:bg-white dark:hover:bg-gray-800 transition">
                                    
                                    <h5 class="font-bold text-gray-800 dark:text-gray-200 text-sm mb-1">
                                        {{ $item->title ?? ($item->training->title ?? 'Custom Training') }}
                                    </h5>
                                    
                                    <div class="flex flex-wrap gap-4 text-xs text-gray-500 mt-1">
                                        <span class="flex items-center">
                                            <ion-icon name="business-outline" class="mr-1 text-indigo-500"></ion-icon> 
                                            {{ $item->provider ?? '-' }}
                                        </span>
                                        <span class="flex items-center">
                                            <ion-icon name="laptop-outline" class="mr-1 text-indigo-500"></ion-icon> 
                                            {{ $item->method ?? '-' }}
                                        </span>
                                    </div>

                                </div>
                            @endforeach
                        </div>

                        {{-- FOOTER AKSI --}}
                        <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 flex justify-end items-center gap-3 border-t border-indigo-100">
                            <span class="text-xs text-gray-500 italic mr-auto hidden sm:block">
                                Menggabungkan {{ $userPlans->count() }} form pengajuan.
                            </span>

                            <a href="{{ route('supervisor.review.user', $user->id) }}" 
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-md transition transform hover:-translate-y-0.5">
                                <ion-icon name="create-outline" class="mr-2 text-lg"></ion-icon>
                                Review & Verifikasi
                            </a>
                        </div>

                    </div>
                </div>

            @empty
                {{-- Empty State --}}
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 border-dashed">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 mb-4">
                        <ion-icon name="library-outline" class="text-4xl text-indigo-400"></ion-icon>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Semua Bersih!</h3>
                    <p class="text-gray-500">Tidak ada usulan pelatihan baru dari tim Anda.</p>
                </div>
            @endforelse
        </div>

        {{-- CONTENT 3: JOB PROFILE --}}
        <div x-show="activeTab === 'jobprofile'" class="space-y-4" style="display: none;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100">
            
            @forelse($jobProfiles as $profile)
                {{-- LOGIKA HIRARKI (PHP) --}}
                @php
                    $posOrg = $profile->position->organization ?? null;
                    $parent = $posOrg->parent ?? null;
                    $grandparent = $parent->parent ?? null;

                    $unitName = '-';
                    $sectionName = '-';
                    $deptName = 'N/A';

                    if ($grandparent) {
                        // 3 Level (Unit -> Section -> Dept)
                        $unitName = $posOrg->name;
                        $sectionName = $parent->name;
                        $deptName = $grandparent->name;
                    } elseif ($parent) {
                        // 2 Level (Section -> Dept)
                        $sectionName = $posOrg->name;
                        $deptName = $parent->name;
                    } else {
                        // 1 Level (Dept Only)
                        $deptName = $posOrg->name ?? 'N/A';
                    }
                @endphp

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-yellow-500 hover:shadow-md transition-all duration-200 mb-4">
                    
                    {{-- HEADER: JUDUL & VERSI --}}
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg text-yellow-600 dark:text-yellow-500">
                                <ion-icon name="briefcase" class="text-xl"></ion-icon>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">
                                    {{ $profile->position->title ?? 'Posisi Dihapus' }}
                                </h3>
                                <div class="text-xs text-gray-400 flex items-center gap-1 mt-1">
                                    <ion-icon name="person-circle-outline"></ion-icon>
                                    <span>{{ $profile->creator->name ?? 'Sistem' }}</span>
                                    <span>•</span>
                                    <span>{{ $profile->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- BADGE VERSI --}}
                        <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold rounded-md border border-gray-200 dark:border-gray-600">
                            v{{ $profile->version }}
                        </span>
                    </div>

                    {{-- HIRARKI ORGANISASI (TAMPILAN BARU YANG RAPI) --}}
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-600 p-3">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-y-3 sm:gap-x-4">
                            
                            {{-- UNIT --}}
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase text-gray-400 font-bold tracking-wider mb-0.5">Unit</span>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate" title="{{ $unitName }}">
                                    {{ $unitName }}
                                </span>
                            </div>

                            {{-- SECTION --}}
                            <div class="flex flex-col sm:border-l sm:border-gray-200 dark:sm:border-gray-600 sm:pl-4">
                                <span class="text-[10px] uppercase text-gray-400 font-bold tracking-wider mb-0.5">Section</span>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate" title="{{ $sectionName }}">
                                    {{ $sectionName }}
                                </span>
                            </div>

                            {{-- DEPARTEMEN --}}
                            <div class="flex flex-col sm:border-l sm:border-gray-200 dark:sm:border-gray-600 sm:pl-4">
                                <span class="text-[10px] uppercase text-gray-400 font-bold tracking-wider mb-0.5">Departemen</span>
                                <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400 truncate" title="{{ $deptName }}">
                                    {{ $deptName }}
                                </span>
                            </div>

                        </div>
                    </div>

                    {{-- FOOTER TOMBOL --}}
                    <div class="mt-4 flex justify-end border-t border-gray-100 dark:border-gray-700 pt-3">
                        <a href="{{ route('supervisor.job-profile.edit', $profile->id) }}" 
                        class="group inline-flex items-center text-sm font-semibold text-yellow-600 hover:text-yellow-700 transition-colors">
                            Periksa & Revisi
                            <ion-icon name="arrow-forward" class="ml-1 transition-transform group-hover:translate-x-1"></ion-icon>
                        </a>
                    </div>
                </div>
            @empty
                {{-- EMPTY STATE --}}
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 border-dashed">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-50 mb-4">
                        <ion-icon name="briefcase-outline" class="text-4xl text-yellow-400"></ion-icon>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Semua Bersih!</h3>
                    <p class="text-gray-500">Tidak ada usulan perubahan Job Profile.</p>
                </div>
            @endforelse
        </div>

        {{-- CONTENT 4: IDP --}}
        <div x-show="activeTab === 'idp'" class="space-y-4" style="display: none;" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            
            @forelse($pendingIdps ?? [] as $idp)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-4 w-full md:w-auto">
                            <div class="p-3 bg-green-50 rounded-full text-green-600">
                                <ion-icon name="document-text-outline" class="text-2xl"></ion-icon>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ $idp->user->name }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ $idp->user->position->title ?? 'Posisi Umum' }}
                                </p>
                                <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                                    <span class="flex items-center">
                                        <ion-icon name="calendar-outline" class="mr-1"></ion-icon>
                                        Periode: {{ $idp->year }}
                                    </span>
                                    <span class="flex items-center">
                                        <ion-icon name="time-outline" class="mr-1"></ion-icon>
                                        Diajukan: {{ $idp->updated_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                            <a href="{{ route('supervisor.idp.show', $idp->id) }}" 
                               class="px-5 py-2.5 bg-green-600 text-white text-sm font-bold rounded-lg hover:bg-green-700 flex items-center shadow-sm transition-transform active:scale-95">
                                <ion-icon name="eye-outline" class="mr-2"></ion-icon> 
                                Review IDP
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 border-dashed">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-50 mb-4">
                        <ion-icon name="checkmark-done-circle-outline" class="text-4xl text-green-400"></ion-icon>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Semua Bersih!</h3>
                    <p class="text-gray-500 max-w-sm mx-auto mt-1">Tidak ada pengajuan IDP baru yang perlu persetujuan Anda saat ini.</p>
                </div>
            @endforelse
        </div>

        {{-- CONTENT 5: CERTIFICATE (GROUP BY USER) --}}
        <div x-data="{ fileModalOpen: false, fileUrl: '', fileType: '' }" 
            x-show="activeTab === 'certificate'" 
            class="space-y-6" 
            style="display: none;" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100">
            
            @forelse($pendingCertificates as $userId => $userCertificates)
                @php
                    $firstItem = $userCertificates->first();
                    $user = $firstItem->plan->user;
                @endphp

                {{-- CARD KARYAWAN --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
                    
                    {{-- Header Card --}}
                    <div class="bg-purple-50 dark:bg-gray-700/50 px-6 py-4 flex items-center gap-4 border-b border-gray-100 dark:border-gray-600">
                        <div class="p-2 bg-white dark:bg-gray-600 rounded-full text-purple-600 shadow-sm">
                            <ion-icon name="person" class="text-xl"></ion-icon>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Mengajukan {{ $userCertificates->count() }} sertifikat baru</p>
                        </div>
                    </div>

                    {{-- Body Card --}}
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($userCertificates as $certItem)
                            <div class="p-6 flex flex-col md:flex-row justify-between items-center gap-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                
                                {{-- Info Sertifikat --}}
                                <div class="flex items-start gap-4 w-full md:w-auto">
                                    <div class="mt-1 text-purple-500">
                                        <ion-icon name="ribbon-outline" class="text-2xl"></ion-icon>
                                    </div>
                                    <div>
                                        <h4 class="text-md font-bold text-gray-800 dark:text-gray-200">
                                            {{ $certItem->title ?? ($certItem->training->title ?? 'Judul Tidak Tersedia') }}
                                        </h4>
                                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center">
                                                <ion-icon name="business-outline" class="mr-1"></ion-icon>
                                                {{ $certItem->provider ?? 'Internal' }}
                                            </span>
                                            <span class="flex items-center">
                                                <ion-icon name="time-outline" class="mr-1"></ion-icon>
                                                {{ $certItem->updated_at->format('d M Y') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex items-center gap-2 w-full md:w-auto justify-end pl-10 md:pl-0">
                                    
                                    {{-- 1. TOMBOL LIHAT FILE --}}
                                    <button type="button" 
                                            @click="fileModalOpen = true; fileUrl = '{{ asset('storage/' . $certItem->certificate_path) }}'"
                                            class="px-3 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition flex items-center gap-2 cursor-pointer"
                                            title="Lihat File">
                                        <ion-icon name="eye-outline"></ion-icon>
                                    </button>

                                    {{-- 2. TOMBOL TOLAK (BARU) --}}
                                    <form action="{{ route('supervisor.sertifikat.reject', $certItem->id) }}" method="POST">
                                        @csrf
                                        {{-- Input hidden untuk menyimpan alasan dari prompt JS --}}
                                        <input type="hidden" name="reason" id="reject_reason_{{ $certItem->id }}">
                                        
                                        <button type="button" onclick="rejectCertificate(this, 'reject_reason_{{ $certItem->id }}')"
                                        class="px-3 py-2 bg-red-50 text-red-600 text-sm font-bold rounded-lg hover:bg-red-100 flex items-center shadow-sm border border-red-200 transition active:scale-95"
                                        title="Tolak Sertifikat">
                                            <ion-icon name="close-circle-outline" class="text-lg"></ion-icon>
                                        </button>
                                    </form>

                                    {{-- 3. TOMBOL VALIDASI --}}
                                    <form action="{{ route('supervisor.sertifikat.approve', $certItem->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Validasi sertifikat ini?')"
                                        class="px-4 py-2 bg-purple-600 text-white text-sm font-bold rounded-lg hover:bg-purple-700 flex items-center shadow-sm transition active:scale-95">
                                            <ion-icon name="checkmark-done-outline" class="mr-1.5"></ion-icon> 
                                            Validasi
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                {{-- Empty State --}}
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 border-dashed">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-purple-50 mb-4">
                        <ion-icon name="ribbon-outline" class="text-4xl text-purple-400"></ion-icon>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Semua Bersih!</h3>
                    <p class="text-gray-500 max-w-sm mx-auto mt-1">Tidak ada sertifikat baru yang perlu diverifikasi.</p>
                </div>
            @endforelse

            <div x-show="fileModalOpen" 
                class="fixed inset-0 z-50 overflow-y-auto" 
                aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                
                {{-- Backdrop Gelap --}}
                <div x-show="fileModalOpen" 
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" 
                    @click="fileModalOpen = false"></div>

                {{-- Modal Content --}}
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="fileModalOpen" 
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl h-[80vh] flex flex-col">
                        
                        {{-- Modal Header --}}
                        <div class="bg-white dark:bg-gray-800 px-4 py-3 sm:px-6 flex justify-between items-center border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                                Preview Sertifikat
                            </h3>
                            <button type="button" @click="fileModalOpen = false" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                <ion-icon name="close-outline" class="text-2xl"></ion-icon>
                            </button>
                        </div>

                        {{-- Modal Body (Iframe untuk PDF/Image) --}}
                        <div class="flex-1 bg-gray-100 dark:bg-gray-900 p-2 overflow-hidden relative">
                            <iframe :src="fileUrl" class="w-full h-full rounded border border-gray-300 dark:border-gray-700"></iframe>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <a :href="fileUrl" download target="_blank" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto items-center gap-2">
                                <ion-icon name="download-outline"></ion-icon> Download
                            </a>
                            <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto" @click="fileModalOpen = false">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

<script>
function rejectCertificate(btn, inputId) {
    // 1. Minta alasan penolakan
    const reason = prompt("Masukkan alasan penolakan sertifikat ini:");
    
    // 2. Jika user menekan Cancel, berhenti
    if (reason === null) return;

    // 3. Jika alasan kosong, beri peringatan
    if (reason.trim() === "") {
        alert("Alasan penolakan wajib diisi!");
        return;
    }

    // 4. Masukkan alasan ke input hidden
    document.getElementById(inputId).value = reason;

    // 5. Submit form
    btn.closest('form').submit();
}
</script>
</x-supervisor-layout>