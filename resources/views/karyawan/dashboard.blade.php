<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold text-gray-900">
            My Development Hub
        </h1>
        <p class="text-gray-600 mt-1">Dasbor untuk memantau dan merencanakan pengembangan kompetensi Anda.</p>
    </x-slot>

    <div class="py-8" x-data="{ showRecommendations: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
                <div class="lg:col-span-1 space-y-6">
                    
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Profil Saya</h2>
                        <div class="flex items-center space-x-4 mb-4">
                            <img class="h-16 w-16 rounded-full object-cover" src="https://i.pravatar.cc/150?u={{ Auth::user()->name }}" alt="Foto Profil">
                            <div>
                                <p class="font-bold text-base text-gray-900">{{ $user->name }}</p>
                                <p class="text-sm text-gray-500">Batch Number: {{ $user->batch_number ?? 'EMP-001' }}</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm text-gray-700">
                            <p><span class="font-semibold">Jabatan:</span> {{ $user->position?->title ?? 'Belum Diatur' }}</p>
                            <p><span class="font-semibold">Unit:</span> {{ $user->position?->department?->name ?? 'Belum Diatur' }}</p>
                            <p><span class="font-semibold">Supervisor:</span> {{ $user->manager?->name ?? 'Belum Diatur' }}</p>
                        </div>
                    </div>
    
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Ringkasan</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Kompetensi Terpenuhi</span>
                                <span class="font-bold text-green-600">{{ $metCompetencies }} / {{ $totalCompetencies }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Kompetensi Perlu Ditingkatkan</span>
                                <span class="font-bold text-red-600">{{ $gapCompetencies }} / {{ $totalCompetencies }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Rencana Pelatihan</span>
                                <span class="font-bold text-blue-600">{{ $recentTrainings->count() }} Aktif</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Analisis Kesenjangan Nilai Kompetensi (Gap Analysis)</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Kompetensi</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Ideal</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Aktual</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Gap</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    
                                    @forelse ($gapAnalysisData as $gap)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-900">{{ $gap->competency_name }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $gap->ideal_level }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $gap->current_level }}</td>
                                        <td class="px-4 py-3 text-center font-bold {{ $gap->gap_value < 0 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $gap->gap_value }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                                            Data gap belum tersedia. Silakan lakukan penilaian diri.
                                        </td>
                                    </tr>
                                    @endforelse

                                </tbody>
                            </table>
                        </div>

                        @if ($assessmentStatus)
                            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="font-semibold text-yellow-800 text-sm">Status Penilaian: Menunggu Verifikasi Supervisor</p>
                                <p class="text-xs text-yellow-700 mt-1">Penilaian Anda sedang ditinjau. Anda tidak dapat mengubahnya saat ini.</p>
                            </div>
                        @else
                            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="font-semibold text-blue-800 text-sm">Status Penilaian: Draft / Terverifikasi</p>
                                <p class="text-xs text-blue-700 mt-1">Penilaian Anda sudah diverifikasi atau masih dalam bentuk draf. Anda dapat memperbaruinya kapan saja.</p>
                            </div>
                        @endif

                        <div class="mt-4 flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('penilaian') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                🔄 Perbarui level Kompetensi
                            </a>
                            
                            {{-- <button @click="showRecommendations = true" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 rounded-lg text-sm font-semibold text-white hover:bg-blue-700">
                                🤖 Dapatkan Rekomendasi Pelatihan
                            </button> --}}
                        </div>
                    </div>
    
                    <div class="bg-white rounded-lg shadow p-6" x-show="showRecommendations" x-transition>
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
                    </div>
                    
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
                </div> </div>
    </div>
</x-app-layout>