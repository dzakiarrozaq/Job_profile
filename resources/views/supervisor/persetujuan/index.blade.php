{{-- File: resources/views/supervisor/persetujuan/index.blade.php --}}
<x-supervisor-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Pusat Persetujuan
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Daftar antrean persetujuan yang membutuhkan tindakan Anda.
        </p>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6" 
         x-data="{ activeTab: 'assessment' }">

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex px-6 space-x-8" aria-label="Tabs">
                
                <button @click="activeTab = 'assessment'" 
                        :class="activeTab === 'assessment' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                    1. Penilaian Kompetensi
                    @if($assessments->count() > 0)
                        <span class="ml-2 bg-red-100 text-red-800 py-0.5 px-2 rounded-full text-xs font-bold">{{ $assessments->count() }}</span>
                    @endif
                </button>

                <button @click="activeTab = 'catalog'" 
                        :class="activeTab === 'catalog' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                    2. Usulan Katalog Pelatihan
                    @if($trainings->count() > 0)
                        <span class="ml-2 bg-blue-100 text-blue-800 py-0.5 px-2 rounded-full text-xs font-bold">{{ $trainings->count() }}</span>
                    @endif
                </button>

                <button @click="activeTab = 'jobprofile'" 
                        :class="activeTab === 'jobprofile' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                    3. Usulan Job Profile
                    @if($jobProfiles->count() > 0)
                        <span class="ml-2 bg-yellow-100 text-yellow-800 py-0.5 px-2 rounded-full text-xs font-bold">{{ $jobProfiles->count() }}</span>
                    @endif
                </button>

            </nav>
        </div>

        <div x-show="activeTab === 'assessment'" class="space-y-4">
            @forelse($assessments as $item)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-l-4 border-yellow-400">
                    <div class="flex items-center gap-4">
                        <img class="h-12 w-12 rounded-full object-cover" src="https://i.pravatar.cc/150?u={{ $item->user->email }}" alt="Foto">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $item->user->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $item->user->position->title ?? 'Posisi tidak diketahui' }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-gray-500">Diajukan: {{ $item->submitted_at ? \Carbon\Carbon::parse($item->submitted_at)->format('d M Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('supervisor.penilaian.show', $item->user_id) }}" class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 flex items-center shadow-sm">
                            <ion-icon name="create-outline" class="mr-2"></ion-icon> Verifikasi Penilaian
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200">
                    <ion-icon name="checkmark-done-circle-outline" class="text-5xl text-gray-300 mb-3"></ion-icon>
                    <p class="text-gray-500">Semua penilaian kompetensi sudah selesai.</p>
                </div>
            @endforelse
        </div>

        <div x-show="activeTab === 'catalog'" class="space-y-4" style="display: none;">
            @forelse($trainings as $training)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-600 border border-gray-200">
                                    {{ $training->type }}
                                </span>
                                <span class="text-xs text-gray-400">Diusulkan oleh: {{ $training->creator->name ?? 'Unknown' }}</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $training->title }}</h3>
                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $training->description }}</p>
                            <p class="text-xs text-gray-500 mt-2">Provider: {{ $training->provider ?? 'Internal' }} • Durasi: {{ $training->duration_hours ?? '-' }} Jam</p>
                        </div>
                        <div class="flex flex-col gap-2">
                            {{-- (Nanti buat route untuk approve training) --}}
                            <button class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 shadow-sm">
                                Review & Setujui
                            </button>
                            <button class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                                Tolak
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200">
                    <ion-icon name="library-outline" class="text-5xl text-gray-300 mb-3"></ion-icon>
                    <p class="text-gray-500">Tidak ada usulan katalog pelatihan baru.</p>
                </div>
            @endforelse
        </div>

        <div x-show="activeTab === 'jobprofile'" class="space-y-4" style="display: none;">
            @forelse($jobProfiles as $profile)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-yellow-50 rounded-lg text-yellow-600">
                                <ion-icon name="briefcase" class="text-2xl"></ion-icon>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ $profile->position->title ?? 'Posisi Dihapus' }}
                                    <span class="ml-2 text-sm font-normal text-gray-500">(v{{ $profile->version }})</span>
                                </h3>
                                <p class="text-sm text-gray-600">Unit: {{ $profile->position->unit->name ?? '-' }}</p>
                                <p class="text-xs text-gray-400 mt-1">Diedit oleh: {{ $profile->creator->name ?? 'Sistem' }} • {{ $profile->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                             <a href="{{ route('supervisor.job-profile.edit', $profile->id) }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 shadow-sm flex items-center">
                                <ion-icon name="eye-outline" class="mr-2"></ion-icon> Periksa Revisi
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200">
                    <ion-icon name="briefcase-outline" class="text-5xl text-gray-300 mb-3"></ion-icon>
                    <p class="text-gray-500">Tidak ada usulan perubahan Job Profile.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-supervisor-layout>