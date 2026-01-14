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

        {{-- CONTENT 2: TRAINING CATALOG --}}
        <div x-show="activeTab === 'catalog'" class="space-y-4" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
             
            @forelse($trainings as $plan)
                @php
                    $item = $plan->items->first(); 
                    if (!$item) { continue; }
                @endphp

                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow mb-4 border-l-4 border-blue-500 hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                        <div class="flex items-start gap-4 w-full md:w-auto">
                            <img class="h-12 w-12 rounded-full object-cover" 
                                 src="{{ $plan->user->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($plan->user->name ?? 'Unknown') }}" 
                                 alt="Foto">
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white text-lg">
                                    {{ $plan->user->name ?? 'User Tidak Dikenal' }}
                                </h4>
                                <p class="text-sm text-blue-600 font-semibold mt-1">
                                    {{ $item->title ?? 'Judul Kosong' }}
                                </p>
                                <div class="text-xs text-gray-500 mt-2 flex items-center gap-3">
                                    <span class="flex items-center bg-gray-100 px-2 py-1 rounded">
                                        <ion-icon name="business-outline" class="mr-1"></ion-icon>
                                        {{ $item->provider ?? '-' }}
                                    </span>
                                    <span class="flex items-center bg-gray-100 px-2 py-1 rounded">
                                        <ion-icon name="laptop-outline" class="mr-1"></ion-icon>
                                        {{ $item->method ?? 'Offline' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                            <form action="{{ route('supervisor.reject', $plan->id) }}" method="POST" onsubmit="return confirm('Tolak rencana ini?');">
                                @csrf
                                <button type="submit" class="px-4 py-2 border border-red-500 text-red-500 hover:bg-red-50 rounded-lg text-sm font-bold transition">
                                    Tolak
                                </button>
                            </form>
                            <a href="{{ route('supervisor.rencana.show', $plan->id) }}" 
                               class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow-sm transition">
                                Review & Setujui
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 border-dashed">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 mb-4">
                        <ion-icon name="library-outline" class="text-4xl text-blue-400"></ion-icon>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Semua Bersih!</h3>
                    <p class="text-gray-500">Tidak ada usulan pelatihan baru.</p>
                </div>
            @endforelse
        </div>

        {{-- CONTENT 3: JOB PROFILE --}}
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

        {{-- CONTENT 5: CERTIFICATE (BARU) --}}
        <div x-show="activeTab === 'certificate'" class="space-y-4" style="display: none;" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            
            @forelse($pendingCertificates ?? [] as $certItem)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-purple-500 hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-4 w-full md:w-auto">
                            <div class="p-3 bg-purple-50 rounded-full text-purple-600">
                                <ion-icon name="ribbon-outline" class="text-2xl"></ion-icon>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ $certItem->plan->user->name }}
                                </h3>
                                <p class="text-sm text-purple-600 font-semibold mt-0.5">
                                    {{ $certItem->title }}
                                </p>
                                <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                                    <span class="flex items-center">
                                        <ion-icon name="business-outline" class="mr-1"></ion-icon>
                                        {{ $certItem->provider }}
                                    </span>
                                    <span class="flex items-center">
                                        <ion-icon name="cloud-upload-outline" class="mr-1"></ion-icon>
                                        Diunggah: {{ $certItem->updated_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                            {{-- Link Lihat File --}}
                            <a href="{{ asset('storage/' . $certItem->certificate_path) }}" target="_blank" 
                               class="text-gray-500 hover:text-purple-600 font-medium text-sm flex items-center">
                                <ion-icon name="document-text-outline" class="mr-1 text-lg"></ion-icon>
                                Lihat File
                            </a>

                            {{-- Tombol ACC --}}
                            <form action="{{ route('supervisor.sertifikat.approve', $certItem->id) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" onclick="return confirm('Validasi sertifikat ini? Status akan menjadi Completed.')"
                                   class="px-5 py-2.5 bg-purple-600 text-white text-sm font-bold rounded-lg hover:bg-purple-700 flex items-center shadow-sm transition-transform active:scale-95">
                                    <ion-icon name="checkmark-done-outline" class="mr-2"></ion-icon> 
                                    Validasi & Selesai
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 border-dashed">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-purple-50 mb-4">
                        <ion-icon name="ribbon-outline" class="text-4xl text-purple-400"></ion-icon>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Semua Bersih!</h3>
                    <p class="text-gray-500 max-w-sm mx-auto mt-1">Tidak ada sertifikat baru yang perlu diverifikasi.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-supervisor-layout>