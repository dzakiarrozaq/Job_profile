{{-- File: resources/views/profile/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profil Saya') }}
        </h2>
    </x-slot>

    <div class="py-6" x-data="{ currentTab: 'profil' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                        <button @click="currentTab = 'profil'" 
                                :class="currentTab === 'profil' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                            <ion-icon name="person-circle-outline" class="mr-2 text-lg"></ion-icon>
                            Profil Utama
                        </button>
                        
                        <button @click="currentTab = 'riwayat'" 
                                :class="currentTab === 'riwayat' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                            <ion-icon name="time-outline" class="mr-2 text-lg"></ion-icon>
                            Riwayat
                        </button>

                        <button @click="currentTab = 'keahlian'" 
                                :class="currentTab === 'keahlian' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                            <ion-icon name="sparkles-outline" class="mr-2 text-lg"></ion-icon>
                            Keahlian & Minat
                        </button>

                        <button @click="currentTab = 'pengaturan'" 
                                :class="currentTab === 'pengaturan' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                            <ion-icon name="settings-outline" class="mr-2 text-lg"></ion-icon>
                            Pengaturan Akun
                        </button>
                    </nav>
                </div>
            </div>

            <div x-show="currentTab === 'profil'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center space-x-4 mb-6">
                            <img class="h-20 w-20 rounded-full object-cover border-4 border-indigo-50 dark:border-indigo-900" 
                                 src="https://i.pravatar.cc/150?u={{ $user->email }}" alt="Foto Profil">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->batch_number ?? 'No Batch' }}</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 mt-2">
                                    {{ session('active_role_name') ?? 'Pengguna' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="space-y-4 text-sm border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">Posisi Saat Ini</p>
                                <p class="font-medium text-gray-900 dark:text-white text-base">{{ $user->position->title ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">Unit Kerja</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $user->department->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">Atasan Langsung</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $user->manager->name ?? '-' }}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">Tgl Masuk</p>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ $user->hiring_date ? \Carbon\Carbon::parse($user->hiring_date)->format('d M Y') : '-' }}
                                    </p>
                                </div>
                                {{-- <div>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">Masa Kerja</p>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ $user->hiring_date ? \Carbon\Carbon::parse($user->hiring_date)->diffForHumans(null, true) : '-' }}
                                    </p>
                                </div> --}}
                            </div>

                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">Tempat, Tgl Lahir</p>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $user->place_of_birth ?? '-' }}, 
                                    {{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('d M Y') : '' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">Domisili</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $user->domicile ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Analisis Kesenjangan Kompetensi</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Berdasarkan penilaian terakhir yang terverifikasi.</p>
                            </div>
                            @if($gapRecords->count() > 0)
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Terverifikasi
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    Belum Ada Data
                                </span>
                            @endif
                        </div>
                        
                        <div class="overflow-x-auto border rounded-lg dark:border-gray-700">
                            <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Kompetensi</th>
                                        <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Ideal</th>
                                        <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Aktual</th>
                                        <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Gap</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($gapRecords as $gap)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $gap->competency_name }}</p>
                                            <p class="text-xs text-gray-400">{{ $gap->competency_code }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400 font-medium">{{ $gap->ideal_level }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 font-bold text-gray-800 dark:text-gray-200">
                                                {{ $gap->current_level }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($gap->gap_value < 0)
                                                <span class="text-red-600 dark:text-red-400 font-bold text-lg">{{ $gap->gap_value }}</span>
                                            @elseif($gap->gap_value > 0)
                                                <span class="text-blue-600 dark:text-blue-400 font-bold text-lg">+{{ $gap->gap_value }}</span>
                                            @else
                                                <span class="text-green-600 dark:text-green-400 font-bold text-lg">0</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                            <ion-icon name="document-text-outline" class="text-4xl mb-2 opacity-50"></ion-icon>
                                            <p>Belum ada data penilaian yang diverifikasi.</p>
                                            <a href="{{ route('penilaian') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm mt-2 inline-block">
                                                Lakukan Penilaian Sekarang &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($gapRecords->where('gap_value', '<', 0)->count() > 0)
                            <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg flex items-start gap-3">
                                <ion-icon name="bulb-outline" class="text-yellow-600 dark:text-yellow-500 text-xl mt-0.5"></ion-icon>
                                <div>
                                    <h4 class="text-sm font-bold text-yellow-800 dark:text-yellow-400">Rekomendasi Pengembangan</h4>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-500 mt-1">
                                        Anda memiliki {{ $gapRecords->where('gap_value', '<', 0)->count() }} kompetensi yang perlu ditingkatkan. 
                                        Silakan cek <a href="{{ route('katalog') }}" class="underline font-semibold hover:text-yellow-900">Katalog Pelatihan</a> yang sesuai.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div x-show="currentTab === 'riwayat'" class="space-y-6">
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Riwayat Jabatan</h2>
                        {{-- (Opsional: Tombol Tambah jika perlu) --}}
                    </div>
                    <ul class="space-y-4 border-l-2 border-gray-200 dark:border-gray-700 ml-2 pl-4">
                        @forelse ($user->jobHistories as $job)
                        <li class="relative">
                            <div class="absolute -left-[21px] top-1 h-3 w-3 rounded-full bg-indigo-500 border-2 border-white dark:border-gray-800"></div>
                            <p class="font-semibold text-gray-900 dark:text-white text-base">{{ $job->title }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $job->unit }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ \Carbon\Carbon::parse($job->start_date)->format('M Y') }} - 
                                {{ $job->end_date ? \Carbon\Carbon::parse($job->end_date)->format('M Y') : 'Sekarang' }}
                            </p>
                        </li>
                        @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">Belum ada data riwayat jabatan.</p>
                        @endforelse
                    </ul>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Riwayat Pendidikan</h2>
                    <div class="grid gap-4">
                        @forelse ($user->educationHistories as $edu)
                        <div class="border-b border-gray-100 dark:border-gray-700 pb-3 last:border-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $edu->degree }} - {{ $edu->field_of_study }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $edu->institution }}</p>
                                </div>
                                <span class="text-xs font-medium bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-600 dark:text-gray-300">
                                    {{ $edu->year_start }} - {{ $edu->year_end }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">Belum ada data pendidikan.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Riwayat Pelatihan (Selesai)</h2>
                    {{-- Anda bisa meloop data training_evidences yang verified di sini --}}
                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">Menampilkan pelatihan yang telah diselesaikan dan diverifikasi.</p>
                </div>
            </div>

            <div x-show="currentTab === 'keahlian'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Keahlian (Job Function)</h2>
                        {{-- Update Link Di Sini --}}
                        <a href="{{ route('profile.skills.edit') }}" class="text-xs text-indigo-600 hover:underline">Tambah/Ubah</a>
                    </div>
                    <ul class="space-y-4">
                        @forelse ($user->skills as $skill)
                        <li class="border-b border-gray-100 dark:border-gray-700 pb-3 last:border-0">
                            <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $skill->skill_name }}</p>
                            <div class="flex items-center gap-4 mt-1">
                                <span class="text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded">
                                    {{ $skill->years_experience }} Tahun
                                </span>
                            </div>
                            @if($skill->certification)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <ion-icon name="ribbon-outline" class="align-middle mr-1"></ion-icon>
                                    {{ $skill->certification }}
                                </p>
                            @endif
                        </li>
                        @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">Belum ada data keahlian.</p>
                        @endforelse
                    </ul>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Posisi yang Diminati</h2>
                         <button class="text-xs text-indigo-600 hover:underline">Tambah/Ubah</button>
                    </div>
                    <ul class="space-y-3">
                        @forelse ($user->interests as $interest)
                        <li class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $interest->position_name }}</span>
                            @if($interest->interest_level == 'Tinggi')
                                <span class="text-xs font-bold text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900 px-2 py-1 rounded">Tinggi</span>
                            @else
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-200 dark:bg-gray-600 px-2 py-1 rounded">{{ $interest->interest_level }}</span>
                            @endif
                        </li>
                        @empty
                         <p class="text-sm text-gray-500 dark:text-gray-400 italic">Belum ada data minat karir.</p>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div x-show="currentTab === 'pengaturan'" class="space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>