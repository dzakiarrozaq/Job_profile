@php
    $user = Auth::user();

    $jobProfile = $user->position?->jobProfile;

    $competencies = $jobProfile?->competencies ?? collect([]);
    $totalComp = $competencies->count();

    $gapRecords = $user->gapRecords ?? collect([]);
    $metComp = $gapRecords->where('gap_value', '>=', 0)->count();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Karyawan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(!$user->position)
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <ion-icon name="warning-outline" class="text-yellow-400 text-2xl"></ion-icon>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Profil Belum Lengkap</h3>
                            <p class="text-sm text-yellow-700 mt-1">
                                Akun Anda belum memiliki <strong>Posisi/Jabatan</strong>. Hubungi Admin atau Supervisor untuk mengatur posisi Anda agar fitur penilaian dapat digunakan.
                            </p>
                        </div>
                    </div>
                </div>
            @elseif(!$jobProfile)
                <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <ion-icon name="information-circle-outline" class="text-blue-400 text-2xl"></ion-icon>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Menunggu Job Profile</h3>
                            <p class="text-sm text-blue-700 mt-1">
                                Posisi Anda <strong>({{ $user->position->title }})</strong> belum memiliki standar kompetensi (Job Profile). Fitur penilaian akan aktif setelah Admin/Supervisor melengkapinya.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-1 space-y-6">
                    
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-bold mb-4">Profil Saya</h3>
                            <div class="flex items-center gap-4 mb-4">
                                <img class="h-16 w-16 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700" 
                                     src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                                     alt="Foto Profil">
                                <div>
                                    <h4 class="font-bold text-lg">{{ $user->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $user->batch_number ?? 'NIK Belum Diatur' }}</p>
                                </div>
                            </div>
                            <div class="space-y-2 text-sm">
                                {{-- Gunakan ?-> untuk Nullsafe Access --}}
                                <p><span class="font-semibold">Jabatan:</span> {{ $user->position?->title ?? '-' }}</p>
                                <p><span class="font-semibold">Unit:</span> {{ $user->position?->unit?->name ?? '-' }}</p>
                                <p><span class="font-semibold">Supervisor:</span> {{ $user->manager?->name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-bold mb-4">Ringkasan</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span>Kompetensi Terpenuhi</span>
                                    <span class="font-bold text-green-600">{{ $metComp }} / {{ $totalComp }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Kompetensi Perlu Ditingkatkan</span>
                                    <span class="font-bold text-red-600">{{ $totalComp - $metComp }} / {{ $totalComp }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Rencana Pelatihan</span>
                                    <span class="font-bold text-blue-600">{{ $recentTrainings->where('status', 'approved')->count() }} Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-bold mb-4">Analisis Kesenjangan Kompetensi</h3>
                            
                            <div class="overflow-x-auto mb-6">
                                <table class="min-w-full text-sm text-left">
                                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 uppercase font-semibold">
                                        <tr>
                                            <th class="px-4 py-2">Kompetensi</th>
                                            <th class="px-4 py-2 text-center">Ideal</th>
                                            <th class="px-4 py-2 text-center">Aktual</th>
                                            <th class="px-4 py-2 text-center">Gap</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @forelse($gapRecords as $gap)
                                        <tr>
                                            <td class="px-4 py-3 font-medium">{{ $gap->competency_name }}</td>
                                            <td class="px-4 py-3 text-center">{{ $gap->ideal_level }}</td>
                                            <td class="px-4 py-3 text-center">{{ $gap->current_level }}</td>
                                            <td class="px-4 py-3 text-center font-bold {{ $gap->gap_value < 0 ? 'text-red-500' : 'text-green-500' }}">
                                                {{ $gap->gap_value }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                                Data gap belum tersedia. Silakan lakukan penilaian diri.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @php
                                // Warna Dinamis
                                $statusColor = match($globalStatus ?? 'not_started') {
                                    'not_started' => 'red',
                                    'draft'       => 'gray',
                                    'pending'     => 'yellow',
                                    'verified'    => 'green',
                                    default       => 'blue',
                                };
                            @endphp

                            <div class="bg-{{ $statusColor }}-50 border border-{{ $statusColor }}-200 rounded-lg p-4 mb-4">
                                <h4 class="font-bold text-{{ $statusColor }}-800 mb-1">
                                    Status Penilaian: 
                                    @if(($globalStatus ?? 'not_started') == 'not_started') BELUM MENGISI
                                    @elseif($globalStatus == 'draft') DRAFT
                                    @elseif($globalStatus == 'pending') MENUNGGU VERIFIKASI
                                    @elseif($globalStatus == 'verified') SUDAH TERVERIFIKASI
                                    @endif
                                </h4>
                                <p class="text-sm text-{{ $statusColor }}-600">
                                    @if(($globalStatus ?? 'not_started') == 'not_started')
                                        Anda belum melakukan penilaian.
                                    @else
                                        Status penilaian Anda saat ini.
                                    @endif
                                </p>
                            </div>

                            <div class="flex gap-3">
                                @if($jobProfile)
                                    <a href="{{ route('penilaian') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        <ion-icon name="create-outline" class="mr-1 align-center"></ion-icon>
                                        Isi Penilaian
                                    </a>
                                @else
                                    <button disabled class="px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm font-medium text-gray-400 cursor-not-allowed">
                                        Penilaian Belum Tersedia
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