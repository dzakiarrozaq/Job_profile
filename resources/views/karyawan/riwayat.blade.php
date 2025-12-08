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

            <div class="space-y-4">
                @forelse($trainingHistory as $plan)
                    @php
                        $trainingTitle = $plan->items->first() ? ($plan->items->first()->training->title ?? 'Pelatihan Kustom') : 'Rencana Pelatihan';
                        $competency = $plan->items->first() ? ($plan->items->first()->training->skill_tags ?? '-') : '-';
                        $type = $plan->items->first() ? ($plan->items->first()->training->type ?? 'Internal') : 'Internal';
                    @endphp

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col md:flex-row justify-between items-center gap-4">
                        
                        <div class="flex-1 w-full">
                            <div class="flex items-center gap-3 mb-2">
                                {{-- Badge Status --}}
                                @if($plan->status == 'completed')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-700 text-white">SELESAI & TERVERIFIKASI</span>
                                @elseif($plan->status == 'approved')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">DISETUJUI FINAL</span>
                                @elseif($plan->status == 'pending_lp')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">MENUNGGU PERSETUJUAN LEARNING PARTNER</span>
                                @elseif($plan->status == 'pending_supervisor')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">MENUNGGU PERSETUJUAN ATASAN</span>
                                @elseif($plan->status == 'rejected')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">DITOLAK</span>
                                @endif

                                <span class="text-xs text-gray-400">Diajukan: {{ $plan->created_at->format('d F Y') }}</span>
                            </div>

                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ $trainingTitle }}</h3>
                            
                            <div class="text-sm text-gray-500 dark:text-gray-400 flex flex-wrap gap-x-4">
                                <span>Kompetensi: <span class="text-indigo-600 font-medium">{{ $competency }}</span></span>
                                <span class="hidden md:inline">•</span>
                                <span>Tipe: {{ ucfirst($type) }}</span>
                            </div>
                        </div>

                        <div class="flex-shrink-0 w-full md:w-auto">
                            @if($plan->status == 'completed')
                                <a href="#" class="inline-flex justify-center items-center w-full px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">
                                    Lihat Sertifikat
                                </a>
                            @elseif($plan->status == 'approved')
                                <a href="#" class="inline-flex justify-center items-center w-full px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">
                                    Unggah Sertifikat
                                </a>
                            @elseif($plan->status == 'rejected')
                                <a href="#" class="inline-flex justify-center items-center w-full px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">
                                    Lihat Catatan Atasan
                                </a>
                            @else
                                <span class="text-sm text-gray-400 italic px-4">Menunggu...</span>
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