@php
    $user = Auth::user();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Karyawan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-1 space-y-6">
                    
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-bold mb-4">Profil Saya</h3>
                            <div class="flex items-center gap-4 mb-4">
                                <img class="h-16 w-16 rounded-full object-cover" 
                                     src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                                     alt="Foto Profil">
                                <div>
                                    <h4 class="font-bold text-lg">{{ $user->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $user->batch_number ?? 'NIK Belum Diatur' }}</p>
                                </div>
                            </div>
                            <div class="space-y-2 text-sm">
                                <p><span class="font-semibold">Jabatan:</span> {{ $user->position->title ?? '-' }}</p>
                                <p><span class="font-semibold">Unit:</span> {{ $user->position->unit->name ?? '-' }}</p>
                                <p><span class="font-semibold">Supervisor:</span> {{ $user->manager->name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-bold mb-4">Ringkasan</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span>Kompetensi Terpenuhi</span>
                                    {{-- Logika hitung kompetensi (Contoh) --}}
                                    @php 
                                        $totalComp = $user->position->jobProfile->competencies->count() ?? 0;
                                        $metComp = $user->gapRecords->where('gap_value', '>=', 0)->count();
                                    @endphp
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
                            <h3 class="text-lg font-bold mb-4">Analisis Kesenjangan Nilai Kompetensi (Gap Analysis)</h3>
                            
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
                                        @forelse($user->gapRecords as $gap)
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
                                $statusColor = match($globalStatus) {
                                    'not_started' => 'red',    
                                    'draft'       => 'gray',   
                                    'pending'     => 'yellow', 
                                    'verified'    => 'green',  
                                    default       => 'blue',
                                };
                            @endphp

                            <div class="bg-{{ $statusColor }}-50 border border-{{ $statusColor }}-200 rounded-lg p-4 mb-4 transition-colors duration-300">
                                
                                <h4 class="font-bold text-{{ $statusColor }}-800 mb-1">
                                    Status Penilaian: 
                                    @if($globalStatus == 'not_started')
                                        BELUM MENGISI
                                    @elseif($globalStatus == 'draft')
                                        DRAFT / BELUM DIAJUKAN
                                    @elseif($globalStatus == 'pending')
                                        MENUNGGU VERIFIKASI
                                    @elseif($globalStatus == 'verified')
                                        SUDAH TERVERIFIKASI
                                    @endif
                                </h4>

                                <p class="text-sm text-{{ $statusColor }}-600">
                                    @if($globalStatus == 'not_started')
                                        Anda belum melakukan penilaian mandiri tahun ini. Mohon segera lengkapi agar kami dapat merekomendasikan pelatihan.
                                    @elseif($globalStatus == 'verified')
                                        Penilaian Anda sudah diverifikasi. Anda dapat memperbaruinya kapan saja jika ada perubahan skill.
                                    @else
                                        Penilaian Anda sedang dalam proses atau masih draft. Silakan selesaikan pengajuan Anda.
                                    @endif
                                </p>
                            </div>

                            <div class="flex gap-3">
                                <a href="{{ route('penilaian') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    <ion-icon name="refresh-outline" class="mr-1 align-middle"></ion-icon>
                                    Perbarui Level Kompetensi
                                </a>
                                
                                {{-- Jika verified, munculkan tombol rekomendasi --}}
                                @if($globalStatus == 'verified')
                                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 shadow-sm">
                                        <ion-icon name="sparkles-outline" class="mr-1 align-middle"></ion-icon>
                                        Dapatkan Rekomendasi Pelatihan
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
    
                    {{-- <div class="bg-white rounded-lg shadow p-6" x-show="showRecommendations" x-transition>
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Rekomendasi Pelatihan Untuk Anda</h2>
                        <div class="space-y-3">

                            @forelse ($recommendations as $rec)
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 text-sm">{{ $rec->title }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Kompetensi: {{ $rec->skill_tags }} | Tipe: {{ $rec->type }}</p>
                                </div>
                                <button class="ml-4 inline-flex items-center justify-center px-3 py-1.5 bg-blue-50 text-blue-800 rounded-lg text-sm font-medium hover:bg-blue-100">
                                    ➕ Tambah
                                </button>
                            </div>
                            @empty
                            <p class="text-center text-gray-500">Tidak ada rekomendasi yang sesuai dengan gap Anda saat ini.</p>
                            @endforelse
                            
                        </div>
                        
                        <div class="mt-4 border-t pt-4">
                            <a href="{{ route('rencana') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 rounded-lg text-sm font-semibold text-white hover:bg-blue-700">
                                <ion-icon name="cart-outline" class="mr-2 text-lg"></ion-icon>
                                Buka Keranjang Rencana (3) </a>
                        </div>
                    </div> --}}
                    
                    
                    @if(false)
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-4">Riwayat & Status Pelatihan</h2> 
                            <div class="bg-white rounded-lg shadow p-6">
                                <h2 class="text-lg font-bold text-gray-900 mb-4">Riwayat & Status Pelatihan</h2>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-50 border-b border-gray-200">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Judul Pelatihan</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tindakan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">

                                            @forelse ($recentTrainings as $plan)
                                            <tr>
                                                <td class="px-4 py-3 text-gray-900">
                                                    {{ $plan->items->first() ? $plan->items->first()->training->title : 'Pelatihan Tidak Ditemukan' }}
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if ($plan->status == 'pending_supervisor')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            MENUNGGU PERSETUJUAN SUPERVISOR
                                                        </span>
                                                    @elseif ($plan->status == 'pending_lp')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            MENUNGGU PERSETUJUAN LEARNING PARTNER
                                                        </span>
                                                    @elseif ($plan->status == 'approved')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            DISETUJUI FINAL
                                                        </span>
                                                    @elseif ($plan->status == 'completed')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-600 text-white">
                                                            COMPLETE & FINAL
                                                        </span>
                                                    @elseif ($plan->status == 'rejected')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            DITOLAK
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if ($plan->status == 'approved')
                                                        <a href="{{-- route('unggah-sertifikat', $plan->id) --}}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Unggah Sertifikat</a>
                                                    @elseif ($plan->status == 'completed')
                                                        <a href="{{-- route('lihat-sertifikat', $plan->id) --}}" class="text-gray-600 hover:text-gray-800 text-sm font-medium">Lihat Sertifikat</a>
                                                    @elseif ($plan->status == 'rejected')
                                                        <a href="{{-- route('catatan-ditolak', $plan->id) --}}" class="text-gray-600 hover:text-gray-800 text-sm font-medium">Lihat Catatan</a>
                                                    @elseif ($plan->status == 'pending_supervisor')
                                                        <a href="#" class="text-red-600 hover:text-red-800 text-sm font-medium">Batalkan</a>
                                                    @else
                                                        <span class="text-gray-400 text-sm">Menunggu...</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="3" class="px-4 py-4 text-center text-gray-500">
                                                    Anda belum memiliki riwayat pelatihan.
                                                </td>
                                            </tr>
                                            @endforelse
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
               
                </div>
                </div> 
            </div>
    </div>
</x-app-layout>