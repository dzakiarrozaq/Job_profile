<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Riwayat Pelatihan Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Riwayat Pelatihan Saya</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Semua rencana pengembangan, pelatihan, dan sertifikat yang pernah Anda ajukan.</p>
            </div>

            {{-- Filter Section --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm mb-8 border border-gray-200 dark:border-gray-700">
                <form method="GET" action="{{ route('riwayat') }}" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/3">
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter berdasarkan Status</label>
                        <select name="status" id="status" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="all">Semua Status</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai & Terverifikasi</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui Final</option>
                            <option value="pending_supervisor" {{ request('status') == 'pending_supervisor' ? 'selected' : '' }}>Menunggu Supervisor</option>
                            <option value="pending_lp" {{ request('status') == 'pending_lp' ? 'selected' : '' }}>Menunggu Learning Partner</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <div class="w-full md:w-1/3">
                        <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter berdasarkan Tahun</label>
                        <select name="year" id="year" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="all">Semua Tahun</option>
                            @foreach(range(date('Y'), 2020) as $year)
                                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:w-auto">
                        <button type="submit" class="w-full px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">
                            Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>

            {{-- List Riwayat --}}
            <div class="space-y-4">
                @forelse($trainingHistory as $plan)
                    @php
                        // Ambil Item Pertama
                        $item = $plan->items->first();
                        
                        // Data display
                        $trainingTitle = $item ? ($item->training->title ?? $item->title ?? 'Pelatihan Kustom') : 'Rencana Pelatihan';
                        $competency = $item ? ($item->training->skill_tags ?? '-') : '-';
                        $type = $item ? ($item->training->type ?? 'Internal') : 'Internal';
                    @endphp

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col md:flex-row justify-between items-center gap-4">
                        
                        {{-- Informasi Kiri --}}
                        <div class="flex-1 w-full">
                            <div class="flex items-center gap-3 mb-2">
                                {{-- Badge Status --}}
                                @if($plan->status == 'completed')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-700 text-white">SELESAI</span>
                                @elseif($plan->status == 'approved')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">DISETUJUI FINAL</span>
                                @elseif($plan->status == 'pending_lp')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">VERIFIKASI LP</span>
                                @elseif($plan->status == 'pending_supervisor')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">MENUNGGU ATASAN</span>
                                @elseif($plan->status == 'rejected')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">DITOLAK</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">{{ strtoupper($plan->status) }}</span>
                                @endif

                                <span class="text-xs text-gray-400">Diajukan: {{ $plan->created_at->format('d F Y') }}</span>
                            </div>

                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ $trainingTitle }}</h3>
                            
                            <div class="text-sm text-gray-500 dark:text-gray-400 flex flex-wrap gap-x-4">
                                <span>Provider: {{ $item->provider ?? '-' }}</span>
                                <span class="hidden md:inline">•</span>
                                <span>Metode: {{ $item->method ?? '-' }}</span>
                            </div>
                        </div>

                        {{-- Tombol Aksi Kanan (UPDATED) --}}
                        <div class="flex-shrink-0 w-full md:w-auto flex flex-col gap-2">
                            
                            {{-- LOGIKA 1: Jika Sertifikat SUDAH Diupload (Tampilkan Tombol Lihat) --}}
                            @if($item && $item->certificate_path)
                                <a href="{{ asset('storage/' . $item->certificate_path) }}" target="_blank" class="inline-flex justify-center items-center w-full px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition shadow-sm">
                                    <ion-icon name="document-text-outline" class="mr-2 text-lg"></ion-icon>
                                    Lihat Sertifikat
                                </a>
                                
                                {{-- Jika status masih 'approved' (belum dikunci admin jadi 'completed'), boleh ganti sertifikat --}}
                                @if($plan->status == 'approved')
                                    <a href="{{ route('rencana.sertifikat', $item->id) }}" class="text-xs text-center text-indigo-600 hover:underline">
                                        Ganti File
                                    </a>
                                @endif

                            {{-- LOGIKA 2: Jika Disetujui tapi BELUM Upload (Tampilkan Tombol Upload) --}}
                            @elseif($plan->status == 'approved' && $item)
                                <a href="{{ route('rencana.sertifikat', $item->id) }}" class="inline-flex justify-center items-center w-full px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm animate-pulse">
                                    <ion-icon name="cloud-upload-outline" class="mr-2 text-lg"></ion-icon>
                                    Unggah Sertifikat
                                </a>

                            {{-- LOGIKA 3: Ditolak --}}
                            @elseif($plan->status == 'rejected')
                                <button type="button" onclick="alert('Alasan Penolakan: {{ $plan->rejection_reason ?? 'Tidak ada catatan.' }}')" class="inline-flex justify-center items-center w-full px-4 py-2 bg-red-50 text-red-700 font-medium rounded-lg hover:bg-red-100 transition border border-red-200">
                                    Lihat Alasan
                                </button>

                            {{-- LOGIKA 4: Menunggu --}}
                            @else
                                <span class="inline-flex justify-center items-center w-full px-4 py-2 bg-gray-100 text-gray-400 font-medium rounded-lg cursor-not-allowed">
                                    <ion-icon name="time-outline" class="mr-2"></ion-icon>
                                    Dalam Proses
                                </span>
                            @endif

                        </div>

                    </div>
                @empty
                    <div class="text-center py-12">
                        <ion-icon name="document-text-outline" class="text-6xl text-gray-300 mb-4"></ion-icon>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Belum ada riwayat pelatihan</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">Anda belum mengajukan atau menyelesaikan pelatihan apa pun.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>