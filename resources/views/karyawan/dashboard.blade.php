@php
    $user = Auth::user();

    $jobProfile = $user->position?->jobProfile;
    $competencies = $jobProfile?->competencies ?? collect([]);
    $totalComp = $competencies->count();

    $gapRecords = $user->gapRecords ?? collect([]);
    $metComp = $gapRecords->where('gap_value', '>=', 0)->count();
    
    $percentMet = $totalComp > 0 ? round(($metComp / $totalComp) * 100) : 0;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Karyawan') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            @if(!$user->position)
                <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm flex items-start gap-4">
                    <ion-icon name="warning" class="text-amber-500 text-2xl mt-0.5"></ion-icon>
                    <div>
                        <h3 class="font-bold text-amber-800">Profil Belum Lengkap</h3>
                        <p class="text-sm text-amber-700 mt-1">
                            Akun Anda belum memiliki <strong>Posisi/Jabatan</strong>. Hubungi Admin untuk mengatur posisi agar fitur penilaian aktif.
                        </p>
                    </div>
                </div>
            @elseif(!$jobProfile)
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-xl shadow-sm flex items-start gap-4">
                    <ion-icon name="information-circle" class="text-blue-500 text-2xl mt-0.5"></ion-icon>
                    <div>
                        <h3 class="font-bold text-blue-800">Menunggu Job Profile</h3>
                        <p class="text-sm text-blue-700 mt-1">
                            Posisi <strong>{{ $user->position->title }}</strong> belum memiliki standar kompetensi. Harap tunggu update dari Supervisor.
                        </p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-8">
                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden relative group">
                        <div class="h-32 bg-gradient-to-br from-blue-600 to-indigo-700 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full translate-x-10 -translate-y-10"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-10 rounded-full -translate-x-5 translate-y-5"></div>
                        </div>
                        
                        <div class="px-6 pb-6 relative">
                            <div class="-mt-12 mb-4 text-center">
                                <div class="relative inline-block">
                                    <img class="h-24 w-24 rounded-full object-cover border-4 border-white dark:border-gray-800 shadow-md bg-white" 
                                         src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                                         alt="Foto">
                                    <div class="absolute bottom-1 right-1 w-5 h-5 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>
                                </div>
                            </div>
                            
                            <div class="text-center mb-6">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                                <div class="flex items-center justify-center gap-2 mt-2">
                                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                        NIK: {{ $user->nik ?? '-' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="space-y-1 text-sm"> {{-- Mengurangi space vertical dari 4 ke 2 --}}
                                <div class="flex items-center justify-between p-1.5 bg-gray-50 dark:bg-gray-700/50 rounded-xl"> {{-- Padding dikurangi jadi p-2.5 --}}
                                    <div class="flex items-center gap-3">
                                        <div class="p-1.5 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600"> {{-- Padding icon dikurangi --}}
                                            <ion-icon name="briefcase-outline" class="text-lg"></ion-icon>
                                        </div>
                                        <span class="text-gray-500 dark:text-gray-400 font-medium">Jabatan</span>
                                    </div>
                                    <span class="font-semibold text-gray-900 dark:text-white text-right truncate max-w-[120px]">
                                        {{ $user->position->title ?? '-' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between p-1.5 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                    <div class="flex items-center gap-3">
                                        <div class="p-1.5 bg-purple-100 dark:bg-purple-900/30 rounded-lg text-purple-600">
                                            <ion-icon name="business-outline" class="text-lg"></ion-icon>
                                        </div>
                                        <span class="text-gray-500 dark:text-gray-400 font-medium">Unit</span>
                                    </div>
                                    <span class="font-semibold text-gray-900 dark:text-white text-right truncate max-w-[120px]">
                                        {{ $user->position->organization->name ?? '-' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between p-1.5 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                    <div class="flex items-center gap-3">
                                        <div class="p-1.5 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg text-indigo-600">
                                            <ion-icon name="person-outline" class="text-lg"></ion-icon>
                                        </div>
                                        <span class="text-gray-500 dark:text-gray-400 font-medium">Atasan</span>
                                    </div>
                                    <span class="font-semibold text-gray-900 dark:text-white text-right truncate max-w-[120px]">
                                        {{ Auth::user()->atasan_name }}
                                    </span>
                                </div>
                            </div>                  
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-6">Ringkasan Kompetensi</h4>
                        
                        <div class="mb-8 text-center">
                            <div class="relative inline-flex items-center justify-center">
                                <svg class="w-32 h-32 transform -rotate-90">
                                    <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="12" fill="transparent" class="text-gray-100 dark:text-gray-700" />
                                    <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="12" fill="transparent" stroke-dasharray="351.86" stroke-dashoffset="{{ 351.86 - (351.86 * $percentMet / 100) }}" class="text-indigo-600 transition-all duration-1000 ease-out" />
                                </svg>
                                <div class="absolute top-0 left-0 w-full h-full flex flex-col items-center justify-center">
                                    <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ $percentMet }}%</span>
                                    <span class="text-xs text-gray-500 uppercase font-semibold">Match</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-green-50 dark:bg-green-900/10 border border-green-100 dark:border-green-800 rounded-2xl text-center">
                                <span class="block text-2xl font-bold text-green-600 dark:text-green-400">{{ $metComp }}</span>
                                <span class="text-xs uppercase font-bold text-green-700 dark:text-green-300">Terpenuhi</span>
                            </div>
                            <div class="p-4 bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-800 rounded-2xl text-center">
                                <span class="block text-2xl font-bold text-red-600 dark:text-red-400">{{ $totalComp - $metComp }}</span>
                                <span class="text-xs uppercase font-bold text-red-700 dark:text-red-300">Gap Area</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50 dark:bg-gray-700/20">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <ion-icon name="analytics" class="text-indigo-600"></ion-icon>
                                    Analisis Kesenjangan
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">Status penilaian kompetensi Anda saat ini.</p>
                            </div>

                            @php
                                $statusColor = match($globalStatus ?? 'not_started') {
                                    'not_started'          => 'red',
                                    'draft'                => 'gray',
                                    'pending_verification' => 'amber',
                                    'verified'             => 'green',
                                    default                => 'blue',
                                };

                                $statusLabel = match($globalStatus ?? 'not_started') {
                                    'not_started'          => 'Belum Mengisi',
                                    'draft'                => 'Draft',
                                    'pending_verification' => 'Menunggu Verifikasi',
                                    'verified'             => 'Terverifikasi',
                                    default                => ucfirst(str_replace('_', ' ', $globalStatus ?? 'Status')),
                                };
                            @endphp
                            <span class="px-4 py-1.5 rounded-full text-xs font-bold border bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700 border-{{ $statusColor }}-200 shadow-sm">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div class="p-6">
                            <div class="mb-8 bg-{{ $statusColor }}-50 border border-{{ $statusColor }}-100 rounded-xl p-4 flex gap-4 items-start">
                                <div class="bg-white p-2 rounded-full shadow-sm text-{{ $statusColor }}-600">
                                    <ion-icon name="notifications" class="text-xl"></ion-icon>
                                </div>
                                <div>
                                    <h5 class="text-sm font-bold text-{{ $statusColor }}-800 mb-1">Status: {{ $statusLabel }}</h5>
                                    <p class="text-xs text-{{ $statusColor }}-700 leading-relaxed">
                                        @if(($globalStatus ?? '') == 'not_started')
                                            Anda belum mengisi penilaian mandiri tahun ini. Segera lengkapi untuk melihat analisis gap dan mendapatkan rekomendasi pelatihan.
                                        @elseif(($globalStatus ?? '') == 'verified')
                                            Penilaian Anda telah disetujui. Silakan gunakan fitur "Rekomendasi AI" untuk merencanakan pengembangan diri.
                                        @else
                                            Penilaian Anda sedang diproses. Mohon menunggu verifikasi dari atasan sebelum dapat mengajukan pelatihan.
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 mb-8">
                                <table class="min-w-full text-sm text-left">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-300 font-semibold uppercase text-xs tracking-wider">
                                        <tr>
                                            <th class="px-6 py-4">Kompetensi</th>
                                            <th class="px-4 py-4 text-center">Ideal</th>
                                            <th class="px-4 py-4 text-center">Aktual</th>
                                            <th class="px-4 py-4 text-center">Gap</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                        @forelse($gapRecords as $gap)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                            <td class="px-6 py-4">
                                                <p class="font-bold text-gray-900 dark:text-white mb-0.5">{{ $gap->competency_name }}</p>
                                                <span class="inline-block px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-500 rounded text-[10px] font-mono border border-gray-200 dark:border-gray-600">
                                                    {{ $gap->competency_code }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <span class="inline-block w-8 h-8 leading-8 rounded-full bg-indigo-50 text-indigo-700 font-bold border border-indigo-100">
                                                    {{ $gap->ideal_level }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <span class="inline-block w-8 h-8 leading-8 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold border border-gray-200 dark:border-gray-600">
                                                    {{ $gap->current_level }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                @if(($globalStatus ?? '') === 'verified')
                                                    @if($gap->gap_value < 0)
                                                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-red-50 text-red-700 border border-red-100 font-bold text-xs">
                                                            {{ $gap->gap_value }}
                                                        </span>
                                                    @elseif($gap->gap_value > 0)
                                                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 border border-blue-100 font-bold text-xs">
                                                            +{{ $gap->gap_value }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-green-50 text-green-700 border border-green-100 font-bold text-xs">
                                                            Fit
                                                        </span>
                                                    @endif
                                                @elseif(($globalStatus ?? '') === 'pending_verification')
                                                    <span class="text-amber-500">
                                                        <ion-icon name="time" class="text-lg"></ion-icon>
                                                    </span>
                                                @else
                                                    <span class="text-gray-300">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                                <div class="flex flex-col items-center">
                                                    <ion-icon name="document-text-outline" class="text-4xl mb-3 opacity-30"></ion-icon>
                                                    <p class="text-sm">Data penilaian belum tersedia.</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @if($jobProfile)
                                    <a href="{{ route('penilaian') }}" class="group relative flex items-center justify-center gap-3 px-6 py-4 bg-white border-2 border-gray-100 hover:border-blue-500 text-gray-700 hover:text-blue-600 rounded-xl transition-all duration-300 shadow-sm hover:shadow-md overflow-hidden">
                                        <div class="absolute inset-0 bg-blue-50 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                        <ion-icon name="create-outline" class="text-xl relative z-10"></ion-icon>
                                        <span class="font-bold relative z-10">
                                            {{ ($globalStatus ?? '') == 'not_started' ? 'Mulai Penilaian' : 'Update Penilaian' }}
                                        </span>
                                    </a>
                                @else
                                    <button disabled class="flex items-center justify-center gap-3 px-6 py-4 bg-gray-50 text-gray-400 rounded-xl border border-gray-100 cursor-not-allowed">
                                        <ion-icon name="ban-outline" class="text-xl"></ion-icon>
                                        <span class="font-bold">Penilaian Terkunci</span>
                                    </button>
                                @endif

                                @if(($globalStatus ?? '') == 'verified')
                                    <a href="{{ route('rekomendasi') }}" class="group relative flex items-center justify-center gap-3 px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all duration-300 overflow-hidden transform hover:-translate-y-0.5">
                                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        <ion-icon name="sparkles" class="text-xl animate-pulse relative z-10"></ion-icon>
                                        <span class="font-bold relative z-10">Minta Rekomendasi AI</span>
                                    </a>
                                @else
                                    <button disabled class="flex items-center justify-center gap-3 px-6 py-4 bg-gray-50 text-gray-400 rounded-xl border border-gray-100 cursor-not-allowed" title="Selesaikan & Verifikasi penilaian dulu">
                                        <ion-icon name="lock-closed-outline" class="text-xl"></ion-icon>
                                        <span class="font-bold">AI Terkunci</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>