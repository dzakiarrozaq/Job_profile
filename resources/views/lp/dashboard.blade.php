<x-lp-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Dashboard Learning Partner
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6 py-6">
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 px-4 sm:px-0">
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex items-center">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-lg mr-4">
                    <ion-icon name="library" class="text-2xl"></ion-icon>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase">Total Kursus</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalCourses }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex items-center">
                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-lg mr-4">
                    <ion-icon name="business" class="text-2xl"></ion-icon>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase">Pelatihan Internal</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $internalCourses }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex items-center">
                <div class="p-3 bg-purple-50 text-purple-600 rounded-lg mr-4">
                    <ion-icon name="globe" class="text-2xl"></ion-icon>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase">Pelatihan Eksternal</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $externalCourses }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border-l-4 border-yellow-400">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase">Menunggu Verifikasi LP</p>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $pendingApprovals }}</p>
                        <ion-icon name="time" class="text-3xl text-yellow-400 opacity-50"></ion-icon>
                    </div>
                    <a href="{{ route('lp.persetujuan') }}" class="text-xs text-yellow-600 hover:underline mt-2 block font-semibold">
                        Lihat Antrean &rarr;
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 px-4 sm:px-0">
            
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 dark:text-gray-100">Permintaan Persetujuan Terbaru</h3>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Tahap 2 (Final)</span>
                    </div>
                    
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($pendingTasks as $plan)
                            <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center gap-3">
                                        <img class="h-10 w-10 rounded-full object-cover" src="https://ui-avatars.com/api/?name={{ urlencode($plan->user->name) }}&background=random" alt="">
                                        <div>
                                            <h4 class="font-bold text-gray-900 dark:text-white text-sm">{{ $plan->user->name }}</h4>
                                            <p class="text-xs text-gray-500">{{ $plan->user->position->name ?? 'Staff' }}</p>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $plan->created_at->diffForHumans() }}</span>
                                </div>

                                <div class="ml-13 pl-13 border-l-2 border-gray-200 pl-4 ml-5">
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">Mengajukan pelatihan:</p>
                                    <ul class="list-disc list-inside text-sm font-medium text-gray-800 dark:text-gray-200 mb-3">
                                        @foreach($plan->items->take(2) as $item)
                                            <li>{{ $item->training->title ?? $item->title ?? 'Custom Training' }}</li>
                                        @endforeach
                                        @if($plan->items->count() > 2)
                                            <li class="text-gray-400 list-none text-xs ml-4">+ {{ $plan->items->count() - 2 }} lainnya</li>
                                        @endif
                                    </ul>
                                </div>

                                <div class="flex justify-end gap-2 mt-4">
                                    <a href="{{ route('lp.persetujuan.show', $plan->id) }}" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition">
                                        Detail
                                    </a>
                                    <a href="{{ route('lp.persetujuan.show', $plan->id) }}" class="px-3 py-1.5 text-xs font-medium text-white bg-indigo-600 rounded hover:bg-indigo-700 transition shadow-sm">
                                        Verifikasi
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center">
                                <ion-icon name="checkmark-circle-outline" class="text-4xl text-gray-300 mb-2"></ion-icon>
                                <p class="text-gray-500">Tidak ada permintaan persetujuan yang tertunda.</p>
                            </div>
                        @endforelse
                    </div>
                    
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 text-center">
                        <a href="{{ route('lp.persetujuan') }}" class="text-sm text-indigo-600 font-medium hover:underline">
                            Lihat Semua Permintaan
                        </a>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="font-bold text-gray-800 dark:text-gray-100 mb-4">Pelatihan Terpopuler</h3>
                    
                    <div class="space-y-4">
                        @forelse($popularTrainings as $training)
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium text-gray-700 dark:text-gray-300 truncate w-2/3" title="{{ $training->title }}">{{ $training->title }}</span>
                                    <span class="font-bold text-indigo-600">{{ $training->total }} Peserta</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                                    @php $percent = min(($training->total / 10) * 100, 100); @endphp
                                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 text-center py-4">Belum ada data pelatihan.</p>
                        @endforelse
                    </div>

                    <a href="{{ route('lp.laporan.index') }}" class="block w-full mt-6 py-2 text-sm text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg font-medium text-center transition">
                        Analisis Lengkap
                    </a>
                </div>

                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-xl shadow-lg p-6 text-white relative overflow-hidden group">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white opacity-10 group-hover:scale-110 transition duration-500"></div>
                    
                    <h3 class="font-bold text-lg mb-2 relative z-10">Kelola Katalog</h3>
                    <p class="text-indigo-100 text-sm mb-4 relative z-10">Tambahkan pelatihan baru atau update materi pembelajaran.</p>
                    
                    <a href="{{ route('lp.katalog.create') }}" class="block w-full py-2 bg-white text-indigo-700 text-center rounded-lg font-bold text-sm hover:bg-gray-50 shadow-md transition transform hover:-translate-y-0.5 relative z-10">
                        + Tambah Kursus Baru
                    </a>
                </div>

            </div>
        </div>

    </div>
</x-lp-layout>