@php
    $user = Auth::user();
    $jobProfile = $user->position?->jobProfile;
    
    // 1. DATA TARGET (Dari Job Profile)
    $profileCompetencies = $jobProfile?->competencies ?? collect([]);

    // 2. DATA AKTUAL (Dari Gap Records User)
    $userGapRecords = $user->gapRecords ?? collect([]);

    // --- PERBAIKAN UTAMA DI SINI ---
    // Kita ubah Gap Records menjadi "Key-Value Pair" agar mudah dicari.
    // Kuncinya adalah 'competency_master_id'.
    $gapMap = $userGapRecords->mapWithKeys(function ($item) {
        return [$item->competency_master_id => $item];
    });

    // 3. MAPPING DATA (Menggabungkan Target + Aktual)
    $finalTableData = $profileCompetencies->map(function($profileComp) use ($gapMap) {
        
        // Ambil ID Master Kompetensi (Pastikan tidak null)
        // Kadang diakses via ->competency_master_id, kadang ->id tergantung struktur model
        $masterId = $profileComp->competency_master_id ?? $profileComp->id;

        // A. Cek Tipe (Hanya Ambil yang BUKAN Perilaku)
        // Ambil type dari relasi 'competency' (Master)
        $typeRaw = $profileComp->competency->type ?? $profileComp->type ?? '';
        $isBehavior = str_contains(strtolower(trim($typeRaw)), 'perilaku');

        // B. Ambil Data Nilai dari GapMap menggunakan Master ID
        $match = $gapMap->get($masterId);

        // C. Tentukan Nilai Aktual & Gap
        // Jika ada record di gap map, pakai itu. 
        // Jika tidak, cek apakah user pernah mengisi draft (opsional, tergantung struktur DB).
        // Untuk aman, jika tidak ada match, set 0.
        $currentLevel = $match ? (int)$match->current_level : 0;
        $gapValue = $match ? (int)$match->gap_value : (0 - (int)$profileComp->ideal_level);

        return (object) [
            'id'              => $masterId,
            'is_behavior'     => $isBehavior, // Flag filter
            'competency_name' => $profileComp->competency_name ?? $profileComp->competency->competency_name ?? '-',
            'competency_code' => $profileComp->competency->competency_code ?? '-',
            'ideal_level'     => (int) $profileComp->ideal_level,
            'current_level'   => $currentLevel,
            'gap_value'       => $gapValue,
            'has_record'      => $match ? true : false
        ];
    })
    // 4. FILTER: HANYA TAMPILKAN JIKA TIPE BUKAN PERILAKU
    ->filter(function($item) {
        return $item->is_behavior === false;
    })
    ->values(); 

    // 5. STATISTIK
    $totalComp = $finalTableData->count();
    $metComp = $finalTableData->filter(fn($d) => $d->has_record && $d->gap_value >= 0)->count();
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
            
            {{-- Alert Profil --}}
            @if(!$user->position)
                <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm flex items-start gap-4">
                    <ion-icon name="warning" class="text-amber-500 text-2xl mt-0.5"></ion-icon>
                    <div>
                        <h3 class="font-bold text-amber-800">Profil Belum Lengkap</h3>
                        <p class="text-sm text-amber-700 mt-1">Hubungi Admin untuk mengatur posisi Anda.</p>
                    </div>
                </div>
            @elseif(!$jobProfile)
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-xl shadow-sm flex items-start gap-4">
                    <ion-icon name="information-circle" class="text-blue-500 text-2xl mt-0.5"></ion-icon>
                    <div>
                        <h3 class="font-bold text-blue-800">Menunggu Job Profile</h3>
                        <p class="text-sm text-blue-700 mt-1">Posisi belum memiliki standar kompetensi.</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                {{-- KIRI: SIDEBAR PROFILE --}}
                <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-8">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden relative group">
                        <div class="h-32 bg-gradient-to-br from-blue-600 to-indigo-700 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full translate-x-10 -translate-y-10"></div>
                        </div>
                        <div class="px-6 pb-6 relative">
                            <div class="-mt-12 mb-4 text-center">
                                <img class="h-24 w-24 rounded-full object-cover border-4 border-white dark:border-gray-800 shadow-md bg-white inline-block" 
                                     src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                                     alt="Foto">
                            </div>
                            <div class="text-center mb-6">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                                <div class="flex items-center justify-center gap-2 mt-2">
                                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                                        NIK: {{ $user->nik ?? '-' }}
                                    </span>
                                </div>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <span class="text-gray-500">Jabatan</span>
                                    <span class="font-bold text-gray-900 dark:text-white text-right truncate max-w-[120px]">{{ $user->position->title ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <span class="text-gray-500">Unit</span>
                                    <span class="font-bold text-gray-900 dark:text-white text-right truncate max-w-[120px]">{{ $user->position->organization->name ?? '-' }}</span>
                                </div>
                            </div>                  
                        </div>
                    </div>

                    {{-- RINGKASAN STATISTIK --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-6">Ringkasan Teknis</h4>
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
                            <div class="p-3 bg-green-50 rounded-xl text-center">
                                <span class="block text-xl font-bold text-green-600">{{ $metComp }}</span>
                                <span class="text-[10px] uppercase font-bold text-green-700">Terpenuhi</span>
                            </div>
                            <div class="p-3 bg-red-50 rounded-xl text-center">
                                <span class="block text-xl font-bold text-red-600">{{ $totalComp - $metComp }}</span>
                                <span class="text-[10px] uppercase font-bold text-red-700">Gap Area</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KANAN: TABEL ANALISIS GAP --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        
                        {{-- Header Card --}}
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50 dark:bg-gray-700/20">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <ion-icon name="analytics" class="text-indigo-600"></ion-icon>
                                    Analisis Kesenjangan Teknis
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">Status penilaian kompetensi teknis Anda saat ini.</p>
                            </div>
                            
                            @php
                                $statusLabel = match($globalStatus ?? 'not_started') {
                                    'not_started'          => 'Belum Mengisi',
                                    'draft'                => 'Draft',
                                    'pending_verification' => 'Menunggu Verifikasi',
                                    'verified'             => 'Terverifikasi',
                                    'rejected'             => 'Perlu Revisi',
                                    default                => 'Status',
                                };
                                $statusClass = match($globalStatus ?? 'not_started') {
                                    'verified' => 'bg-green-50 text-green-700 border-green-200',
                                    'pending_verification' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'rejected' => 'bg-red-50 text-red-700 border-red-200',
                                    default => 'bg-gray-50 text-gray-700 border-gray-200'
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div class="p-6">
                            {{-- Notifikasi Status --}}
                            <div class="mb-8 border rounded-xl p-4 flex gap-4 items-start bg-{{ $statusClass }}-50 border-{{ $statusClass }}-100">
                                <div class="bg-white p-2 rounded-full shadow-sm text-{{ $statusClass }}-600">
                                    <ion-icon name="notifications" class="text-xl"></ion-icon>
                                </div>
                                <div>
                                    <h5 class="text-sm font-bold mb-1">Status: {{ $statusLabel }}</h5>
                                    <p class="text-xs leading-relaxed opacity-90">
                                        @if(($globalStatus ?? '') == 'not_started')
                                            Anda belum mengisi penilaian mandiri. Data di bawah ini menunjukkan target level Anda.
                                        @elseif(($globalStatus ?? '') == 'verified')
                                            Penilaian telah disetujui. Lihat hasil gap analisis Anda di bawah ini.
                                        @else
                                            Penilaian sedang diproses. Data "Aktual" dan "Gap" mungkin berubah setelah verifikasi atasan.
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- TABEL DATA --}}
                            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 mb-8">
                                <table class="min-w-full text-sm text-left">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-300 font-semibold uppercase text-xs tracking-wider">
                                        <tr>
                                            <th class="px-6 py-4 w-1/2">KOMPETENSI</th>
                                            <th class="px-4 py-4 text-center">IDEAL</th>
                                            <th class="px-4 py-4 text-center">AKTUAL</th>
                                            <th class="px-4 py-4 text-center">GAP</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                        
                                        @forelse($finalTableData as $item)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                                
                                                {{-- 1. Nama Kompetensi --}}
                                                <td class="px-6 py-4">
                                                    <p class="font-bold text-gray-900 dark:text-white mb-0.5">{{ $item->competency_name }}</p>
                                                    <span class="text-[10px] text-gray-400 font-mono">{{ $item->competency_code }}</span>
                                                </td>

                                                {{-- 2. Level Ideal --}}
                                                <td class="px-4 py-4 text-center">
                                                    <span class="inline-block w-8 h-8 leading-8 rounded-full bg-indigo-50 text-indigo-700 font-bold border border-indigo-100">
                                                        {{ $item->ideal_level }}
                                                    </span>
                                                </td>

                                                {{-- 3. Level Aktual --}}
                                                <td class="px-4 py-4 text-center">
                                                    @if($item->has_record)
                                                        <span class="inline-block w-8 h-8 leading-8 rounded-full bg-gray-100 text-gray-700 font-bold border border-gray-200">
                                                            {{ $item->current_level }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-gray-300 italic">Belum dinilai</span>
                                                    @endif
                                                </td>

                                                {{-- 4. GAP --}}
                                                <td class="px-4 py-4 text-center">
                                                    @if(!$item->has_record)
                                                        <span class="text-gray-300">-</span>
                                                    @else
                                                        @if($item->gap_value < 0)
                                                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-red-50 text-red-700 border border-red-100 font-bold text-xs">
                                                                {{ $item->gap_value }}
                                                            </span>
                                                        @elseif($item->gap_value > 0)
                                                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 border border-blue-100 font-bold text-xs">
                                                                +{{ $item->gap_value }}
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-green-50 text-green-700 border border-green-100 font-bold text-xs">
                                                                Fit
                                                            </span>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                                    <div class="flex flex-col items-center">
                                                        <ion-icon name="document-text-outline" class="text-4xl mb-3 opacity-30"></ion-icon>
                                                        <p class="text-sm">Tidak ada data kompetensi teknis pada profil ini.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse

                                    </tbody>
                                </table>
                            </div>

                            {{-- Tombol Aksi --}}
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