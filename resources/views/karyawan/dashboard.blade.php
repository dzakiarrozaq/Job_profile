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
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Karyawan') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(!$user->position)
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg shadow-sm flex items-start gap-3">
                    <ion-icon name="warning" class="text-yellow-500 text-xl mt-1"></ion-icon>
                    <div>
                        <h3 class="text-sm font-bold text-yellow-800">Profil Belum Lengkap</h3>
                        <p class="text-sm text-yellow-700 mt-1">
                            Akun Anda belum memiliki <strong>Posisi/Jabatan</strong>. Hubungi Admin untuk mengatur posisi agar fitur penilaian aktif.
                        </p>
                    </div>
                </div>
            @elseif(!$jobProfile)
                <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg shadow-sm flex items-start gap-3">
                    <ion-icon name="information-circle" class="text-blue-500 text-xl mt-1"></ion-icon>
                    <div>
                        <h3 class="text-sm font-bold text-blue-800">Menunggu Job Profile</h3>
                        <p class="text-sm text-blue-700 mt-1">
                            Posisi <strong>{{ $user->position->title }}</strong> belum memiliki standar kompetensi. Harap tunggu update dari Supervisor.
                        </p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <div class="lg:col-span-1 space-y-6 sticky top-8">
                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="h-24 bg-gradient-to-r from-indigo-600 to-blue-500"></div>
                        
                        <div class="px-6 pb-6 relative">
                            <div class="-mt-12 mb-4 text-center">
                                <img class="h-24 w-24 rounded-full object-cover border-4 border-white dark:border-gray-800 shadow-md inline-block bg-white" 
                                     src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                                     alt="Foto">
                            </div>
                            
                            <div class="text-center mb-6">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 mt-2 inline-block">
                                    {{ $user->batch_number ?? 'NIK: -' }}
                                </span>
                            </div>
                            
                            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300 border-t border-gray-100 dark:border-gray-700 pt-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Jabatan</span>
                                    <span class="font-medium text-right">{{ $user->position->title ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Unit</span>
                                    <span class="font-medium text-right">{{ $user->position->unit->name ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Supervisor</span>
                                    <span class="font-medium text-right">{{ $user->manager->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Statistik Kompetensi</h4>
                        
                        <div class="mb-6">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700 dark:text-gray-300">Kecocokan</span>
                                <span class="font-bold text-indigo-600">{{ $percentMet }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $percentMet }}%"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800 rounded-xl text-center">
                                <span class="block text-2xl font-bold text-green-600 dark:text-green-400">{{ $metComp }}</span>
                                <span class="text-[10px] uppercase font-bold text-green-800 dark:text-green-300">Terpenuhi</span>
                            </div>
                            <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 rounded-xl text-center">
                                <span class="block text-2xl font-bold text-red-600 dark:text-red-400">{{ $totalComp - $metComp }}</span>
                                <span class="text-[10px] uppercase font-bold text-red-800 dark:text-red-300">Gap</span>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-sm">
                            <span class="text-gray-500">Rencana Aktif</span>
                            <span class="font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-md">
                                {{ isset($recentTrainings) ? $recentTrainings->where('status', 'approved')->count() : 0 }} Program
                            </span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <ion-icon name="analytics" class="text-indigo-500"></ion-icon>
                                    Analisis Kesenjangan
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">Perbandingan level kompetensi Ideal vs Aktual.</p>
                            </div>

                            @php
                                $statusColor = match($globalStatus ?? 'not_started') {
                                    'not_started'          => 'red',
                                    'draft'                => 'gray',
                                    'pending_verification' => 'yellow', // <--- Perbaikan di sini (sebelumnya 'pending')
                                    'verified'             => 'green',
                                    default                => 'blue',
                                };

                                $statusLabel = match($globalStatus ?? 'not_started') {
                                    'not_started'          => 'BELUM MENGISI',
                                    'draft'                => 'DRAFT',
                                    'pending_verification' => 'MENUNGGU VERIFIKASI', // <--- Perbaikan di sini
                                    'verified'             => 'TERVERIFIKASI',
                                    default                => strtoupper(str_replace('_', ' ', $globalStatus ?? 'STATUS')), // Biar kalau ada status lain tetap terbaca rapi
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold border bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700 border-{{ $statusColor }}-200">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 mb-6">
                            <table class="min-w-full text-sm text-left">
                                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-300 font-semibold">
                                    <tr>
                                        <th class="px-4 py-3">Kompetensi</th>
                                        <th class="px-4 py-3 text-center w-20">Ideal</th>
                                        <th class="px-4 py-3 text-center w-20">Aktual</th>
                                        <th class="px-4 py-3 text-center w-20">Gap</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse($gapRecords as $gap)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $gap->competency_name }}</p>
                                            <p class="text-xs text-gray-400">{{ $gap->competency_code }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-500 font-medium">{{ $gap->ideal_level }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 font-bold text-xs text-gray-700 dark:text-gray-300">
                                                {{ $gap->current_level }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if(($globalStatus ?? '') === 'verified')
                                                {{-- JIKA SUDAH VERIFIED: Tampilkan Angka Gap --}}
                                                @if($gap->gap_value < 0)
                                                    <span class="text-red-600 dark:text-red-400 font-bold">{{ $gap->gap_value }}</span>
                                                @elseif($gap->gap_value > 0)
                                                    <span class="text-blue-600 dark:text-blue-400 font-bold">+{{ $gap->gap_value }}</span>
                                                @else
                                                    <span class="text-green-600 dark:text-green-400 font-bold">OK</span>
                                                @endif
                                            @elseif(($globalStatus ?? '') === 'pending_verification')
                                                {{-- JIKA SEDANG DIPROSES: Tampilkan Pending --}}
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                    <ion-icon name="time-outline" class="mr-1"></ion-icon> Pending
                                                </span>
                                            @else
                                                {{-- JIKA BELUM MENGISI --}}
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                                            <ion-icon name="document-text-outline" class="text-3xl mb-2 opacity-50"></ion-icon>
                                            <p>Data penilaian belum tersedia.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="bg-{{ $statusColor }}-50 border border-{{ $statusColor }}-200 rounded-lg p-4 mb-4 flex gap-3 items-start">
                            <ion-icon name="notifications-outline" class="text-{{ $statusColor }}-600 text-xl mt-0.5"></ion-icon>
                            <div>
                                <p class="text-sm text-{{ $statusColor }}-800 font-bold mb-1">Status: {{ ucfirst(str_replace('_', ' ', $globalStatus ?? 'Not Started')) }}</p>
                                <p class="text-xs text-{{ $statusColor }}-600">
                                    @if(($globalStatus ?? '') == 'not_started')
                                        Anda belum mengisi penilaian mandiri tahun ini. Segera lengkapi untuk melihat analisis gap.
                                    @elseif($globalStatus == 'verified')
                                        Penilaian Anda telah disetujui. Anda dapat menggunakan hasil ini untuk merencanakan pelatihan.
                                    @else
                                        Penilaian sedang dalam proses. Harap tunggu verifikasi.
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            
                            {{-- Tombol 1: Penilaian (Selalu Ada) --}}
                            @if($jobProfile)
                                <a href="{{ route('penilaian') }}" class="flex-1 inline-flex justify-center items-center px-4 py-2.5 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition">
                                    <ion-icon name="create-outline" class="mr-2"></ion-icon>
                                    {{ ($globalStatus ?? '') == 'not_started' ? 'Mulai Penilaian' : 'Update Penilaian' }}
                                </a>
                            @else
                                <button disabled class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-400 rounded-lg text-sm font-medium cursor-not-allowed text-center">
                                    Penilaian Belum Tersedia
                                </button>
                            @endif

                            {{-- Tombol 2: Rekomendasi AI (Hanya jika Verified) --}}
                            @if(($globalStatus ?? '') == 'verified')
                                <a href="{{ route('rekomendasi') }}" class="flex-1 inline-flex justify-center items-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-lg text-sm font-bold shadow-md transition transform hover:scale-[1.02]">
                                    <ion-icon name="sparkles" class="mr-2 animate-pulse"></ion-icon>
                                    Minta Rekomendasi AI
                                </a>
                            @else
                                {{-- Tombol Disabled (Opsional, agar user tau fitur ini ada) --}}
                                <button disabled class="flex-1 inline-flex justify-center items-center px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-400 border border-gray-200 dark:border-gray-600 rounded-lg text-sm font-medium cursor-not-allowed" title="Selesaikan penilaian dulu">
                                    <ion-icon name="sparkles-outline" class="mr-2"></ion-icon>
                                    AI Terkunci
                                </button>
                            @endif

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>