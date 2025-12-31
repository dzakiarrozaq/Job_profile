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
                            <div class="relative inline-block">
                                <img class="h-24 w-24 rounded-full object-cover border-4 border-white dark:border-gray-700 shadow-md mx-auto" 
                                    src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                                    alt="Foto Profil">
                                <span class="absolute bottom-1 right-1 h-5 w-5 rounded-full border-2 border-white dark:border-gray-800 bg-green-500"></span>
                            </div>
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
                            
                            {{-- STATUS BADGE: Cek apakah Employee Profile statusnya 'verified' --}}
                            @if($user->employeeProfile && $user->employeeProfile->status === 'verified')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Terverifikasi
                                </span>
                            @elseif($user->employeeProfile && $user->employeeProfile->status === 'pending_verification')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                    Menunggu Verifikasi
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
                                    @if($user->employeeProfile && $user->employeeProfile->status === 'verified')

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
                                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                                Data kompetensi tidak ditemukan.
                                            </td>
                                        </tr>
                                        @endforelse

                                    @else
                                        <tr>
                                            <td colspan="4" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                                @if($user->employeeProfile && $user->employeeProfile->status === 'pending_verification')
                                                    <ion-icon name="hourglass-outline" class="text-4xl mb-2 text-yellow-500"></ion-icon>
                                                    <p class="font-bold text-gray-700">Sedang Diverifikasi</p>
                                                    <p class="text-xs mt-1">Hasil analisis akan muncul setelah disetujui Supervisor.</p>
                                                @else
                                                    <ion-icon name="document-text-outline" class="text-4xl mb-2 opacity-50"></ion-icon>
                                                    <p>Belum ada data penilaian yang diverifikasi.</p>
                                                    <a href="{{ route('penilaian') }}" class="text-indigo-600 hover:underline text-sm mt-2 inline-block">
                                                        Lakukan Penilaian Sekarang &rarr;
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif

                                </tbody>
                            </table>
                        </div>

                        @if($user->employeeProfile && $user->employeeProfile->status === 'verified' && $gapRecords->where('gap_value', '<', 0)->count() > 0)
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
                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">Menampilkan pelatihan yang telah diselesaikan dan diverifikasi.</p>
                </div>
            </div>

            <div x-show="currentTab === 'keahlian'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Keahlian (Job Function)</h2>
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
                         <a href="{{ route('profile.interests.edit') }}" class="text-xs text-indigo-600 hover:underline">Tambah/Ubah</a>
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

            <div x-show="currentTab === 'pengaturan'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Informasi Pribadi</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Perbarui informasi profil akun dan alamat email Anda.</p>

                        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            @method('patch')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2" x-data="{ photoName: null, photoPreview: null }">
                                    <input type="file" id="photo" class="hidden" name="profile_photo"
                                                x-ref="photo"
                                                x-on:change="
                                                        photoName = $refs.photo.files[0].name;
                                                        const reader = new FileReader();
                                                        reader.onload = (e) => {
                                                            photoPreview = e.target.result;
                                                        };
                                                        reader.readAsDataURL($refs.photo.files[0]);
                                                " />

                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="photo">
                                        Foto Profil
                                    </label>

                                    <div class="flex items-center gap-4">
                                        <div class="mt-2" x-show="! photoPreview">
                                            <img src="{{ $user->profile_photo_path ? asset('storage/'.$user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" 
                                                alt="{{ $user->name }}" 
                                                class="rounded-full h-20 w-20 object-cover border-2 border-gray-200">
                                        </div>

                                        <div class="mt-2" x-show="photoPreview" style="display: none;">
                                            <span class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center border-2 border-indigo-500"
                                                x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                                            </span>
                                        </div>

                                        <button type="button" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                                                x-on:click.prevent="$refs.photo.click()">
                                            {{ __('Pilih Foto Baru') }}
                                        </button>
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
                                </div>
                                <div>
                                    <x-input-label for="name" :value="__('Nama Lengkap')" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                </div>

                                <div>
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                                </div>

                                <div>
                                    <x-input-label for="place_of_birth" :value="__('Tempat Lahir')" />
                                    <x-text-input id="place_of_birth" name="place_of_birth" type="text" class="mt-1 block w-full" :value="old('place_of_birth', $user->place_of_birth)" placeholder="Kota Kelahiran" />
                                </div>

                                <div>
                                    <x-input-label for="date_of_birth" :value="__('Tanggal Lahir')" />
                                    <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', $user->date_of_birth)" />
                                </div>

                                <div class="md:col-span-2">
                                    <x-input-label for="domicile" :value="__('Alamat Domisili')" />
                                    <x-text-input id="domicile" name="domicile" type="text" class="mt-1 block w-full" :value="old('domicile', $user->domicile)" placeholder="Alamat tempat tinggal saat ini" />
                                </div>
                            </div>

                            <div class="flex items-center gap-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <x-primary-button>{{ __('Simpan Perubahan') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-1 space-y-6">
                    
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 border-l-4 border-yellow-400">
                        <div class="flex items-center mb-4">
                            <div class="p-2 bg-yellow-100 text-yellow-600 rounded-full mr-3">
                                <ion-icon name="key-outline" class="text-xl"></ion-icon>
                            </div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Lupa Password?</h2>
                        </div>
                        
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            Jika Anda ingin mengubah password atau merasa password Anda tidak aman, kami akan mengirimkan link reset password ke email Anda (<strong>{{ $user->email }}</strong>).
                        </p>

                        <form method="POST" action="{{ route('profile.trigger-reset') }}">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <ion-icon name="mail-outline" class="mr-2 text-lg"></ion-icon>
                                Kirim Link Reset Password
                            </button>
                        </form>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 border-l-4 border-red-500">
                        <h2 class="text-lg font-bold dark:text-white mb-2">Area Berbahaya</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Setelah akun dihapus, semua data akan hilang permanen.
                        </p>
                        @include('profile.partials.delete-user-form')
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>