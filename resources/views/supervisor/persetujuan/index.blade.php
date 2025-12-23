<x-supervisor-layout>
    <x-slot name="header">
        <style>
            /* Utility untuk menyembunyikan scrollbar */
            .no-scrollbar::-webkit-scrollbar {
                display: none;
            }
            .no-scrollbar {
                -ms-overflow-style: none;  /* IE and Edge */
                scrollbar-width: none;  /* Firefox */
            }
        </style>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Pusat Persetujuan
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Daftar antrean persetujuan yang membutuhkan tindakan Anda.
        </p>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6" 
         x-data="{ activeTab: 'assessment' }">

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border-b border-gray-200 dark:border-gray-700 overflow-x-auto no-scrollbar">
            <nav class="-mb-px flex px-6 space-x-8 min-w-max" aria-label="Tabs">
                
                <button @click="activeTab = 'assessment'" 
                        :class="activeTab === 'assessment' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                    1. Penilaian Kompetensi
                    @if($assessments->count() > 0)
                        <span class="ml-2 bg-indigo-100 text-indigo-800 py-0.5 px-2 rounded-full text-xs font-bold animate-pulse">{{ $assessments->count() }}</span>
                    @endif
                </button>

                <button @click="activeTab = 'catalog'" 
                        :class="activeTab === 'catalog' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                    2. Usulan Katalog
                    @if($trainings->count() > 0)
                        <span class="ml-2 bg-blue-100 text-blue-800 py-0.5 px-2 rounded-full text-xs font-bold animate-pulse">{{ $trainings->count() }}</span>
                    @endif
                </button>

                <button @click="activeTab = 'jobprofile'" 
                        :class="activeTab === 'jobprofile' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                    3. Usulan Job Profile
                    @if($jobProfiles->count() > 0)
                        <span class="ml-2 bg-yellow-100 text-yellow-800 py-0.5 px-2 rounded-full text-xs font-bold animate-pulse">{{ $jobProfiles->count() }}</span>
                    @endif
                </button>

                <button @click="activeTab = 'idp'" 
                        :class="activeTab === 'idp' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                    4. IDP (Rencana Pengembangan)
                    @if(isset($pendingIdps) && $pendingIdps->count() > 0)
                        <span class="ml-2 bg-green-100 text-green-800 py-0.5 px-2 rounded-full text-xs font-bold animate-pulse">
                            {{ $pendingIdps->count() }}
                        </span>
                    @endif
                </button>

            </nav>
        </div>

        
        <div x-show="activeTab === 'assessment'" class="space-y-4" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
             
            @forelse($assessments as $item)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-indigo-500 hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-4 w-full md:w-auto">
                            <img class="h-12 w-12 rounded-full object-cover ring-2 ring-indigo-50" src="https://i.pravatar.cc/150?u={{ $item->user->email }}" alt="Foto">
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

        <div x-show="activeTab === 'catalog'" class="space-y-4" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
             
            @forelse($trainings as $training)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-50 text-blue-600 border border-blue-100">
                                    {{ $training->type }}
                                </span>
                                <span class="text-xs text-gray-400 flex items-center">
                                    <ion-icon name="person-circle-outline" class="mr-1"></ion-icon>
                                    {{ $training->creator->name ?? 'Unknown' }}
                                </span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $training->title }}</h3>
                            <p class="text-sm text-gray-600 mt-1 line-clamp-1">{{ $training->description }}</p>
                            <p class="text-xs text-gray-400 mt-2 flex items-center gap-3">
                                <span><ion-icon name="business-outline" class="align-middle"></ion-icon> {{ $training->provider ?? 'Internal' }}</span>
                                <span><ion-icon name="time-outline" class="align-middle"></ion-icon> {{ $training->duration_hours ?? '-' }} Jam</span>
                            </p>
                        </div>
                        <div class="flex flex-col gap-2 w-full md:w-auto min-w-[140px]">
                            <button class="px-4 py-2 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 shadow-sm transition-transform active:scale-95">
                                Review & Setujui
                            </button>
                            <button class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                Tolak
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 border-dashed">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 mb-4">
                        <ion-icon name="library-outline" class="text-4xl text-blue-400"></ion-icon>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Semua Bersih!</h3>
                    <p class="text-gray-500">Tidak ada usulan katalog pelatihan baru.</p>
                </div>
            @endforelse
        </div>

        {{-- 3. CONTENT: JOB PROFILE --}}
        <div x-show="activeTab === 'jobprofile'" class="space-y-4" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
             
            @forelse($jobProfiles as $profile)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-yellow-500 hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-4 w-full md:w-auto">
                            <div class="p-3 bg-yellow-50 rounded-full text-yellow-600">
                                <ion-icon name="briefcase" class="text-2xl"></ion-icon>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    {{ $profile->position->title ?? 'Posisi Dihapus' }}
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-md border">v{{ $profile->version }}</span>
                                </h3>
                                <p class="text-sm text-gray-600">Unit: {{ $profile->position->unit->name ?? '-' }}</p>
                                <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                    <ion-icon name="create-outline"></ion-icon>
                                    Edit: {{ $profile->creator->name ?? 'Sistem' }} • {{ $profile->updated_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                             <a href="{{ route('supervisor.job-profile.edit', $profile->id) }}" 
                                class="px-5 py-2.5 bg-yellow-500 text-white text-sm font-bold rounded-lg hover:bg-yellow-600 shadow-sm flex items-center transition-transform active:scale-95">
                                <ion-icon name="eye-outline" class="mr-2"></ion-icon> Periksa Revisi
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 border-dashed">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-50 mb-4">
                        <ion-icon name="briefcase-outline" class="text-4xl text-yellow-400"></ion-icon>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Semua Bersih!</h3>
                    <p class="text-gray-500">Tidak ada usulan perubahan Job Profile.</p>
                </div>
            @endforelse
        </div>

        {{-- 4. CONTENT: IDP --}}
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

    </div>
</x-supervisor-layout>