<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Katalog Pelatihan') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <form method="GET" action="{{ route('katalog') }}" class="flex flex-col md:flex-row gap-4">
                    
                    <div class="relative flex-grow group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <ion-icon name="search-outline" class="text-xl text-gray-400 group-focus-within:text-indigo-500 transition-colors"></ion-icon>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                            class="pl-11 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 transition-all" 
                            placeholder="Cari judul pelatihan, topik, atau provider...">
                    </div>

                    <div class="w-full md:w-56 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <ion-icon name="filter-outline" class="text-lg text-gray-400"></ion-icon>
                        </div>
                        <select name="type" onchange="this.form.submit()" 
                            class="pl-10 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 cursor-pointer appearance-none">
                            <option value="all">Semua Tipe</option>
                            <option value="internal" {{ request('type') == 'internal' ? 'selected' : '' }}>Internal</option>
                            <option value="external" {{ request('type') == 'external' ? 'selected' : '' }}>External</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <ion-icon name="chevron-down-outline" class="text-gray-400"></ion-icon>
                        </div>
                    </div>

                    @if(request('search') || (request('type') && request('type') !== 'all'))
                        <a href="{{ route('katalog') }}" class="inline-flex justify-center items-center px-6 py-3 border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm text-sm font-bold text-gray-600 bg-gray-50 hover:bg-gray-100 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-all">
                            <ion-icon name="refresh-outline" class="mr-2 text-lg"></ion-icon>
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            {{-- Grid Katalog --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($trainings as $training)
                    <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 border border-gray-100 dark:border-gray-700 flex flex-col h-full overflow-hidden relative">
                        
                        {{-- Card Header / Cover --}}
                        <div class="h-40 bg-gradient-to-br {{ $training->type == 'internal' ? 'from-blue-600 to-indigo-600' : 'from-purple-600 to-fuchsia-600' }} relative p-6 flex flex-col justify-between">
                            
                            <div class="flex justify-between items-start">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold text-white bg-white/20 backdrop-blur-md border border-white/30 uppercase tracking-wide">
                                    {{ $training->type }}
                                </span>
                                
                                {{-- Badge Difficulty (Contoh: Beginner/Advanced) --}}
                                @if(isset($training->difficulty))
                                    <span class="px-2 py-1 rounded-md text-[10px] font-bold text-white bg-black/20 backdrop-blur-sm">
                                        {{ $training->difficulty }}
                                    </span>
                                @endif
                            </div>

                            {{-- Abstract Decoration --}}
                            <div class="absolute bottom-0 right-0 opacity-10 transform translate-x-4 translate-y-4">
                                <ion-icon name="shapes" class="text-9xl text-white"></ion-icon>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $training->method == 'Online' ? 'bg-green-500' : 'bg-orange-500' }}"></span>
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ $training->method ?? 'Online' }}
                                    </span>
                                </div>

                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors">
                                    <a href="{{ route('katalog.show', $training->id) }}">
                                        {{ $training->title }}
                                    </a>
                                </h3>
                                
                                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    <ion-icon name="business-outline" class="text-lg"></ion-icon>
                                    <span class="truncate">{{ $training->provider ?? 'Internal Company' }}</span>
                                </div>

                                <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-3 mb-6 leading-relaxed">
                                    {{ $training->description }}
                                </p>
                            </div>

                            <div class="pt-5 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center gap-4">
                                <div>
                                    <p class="text-xs text-gray-400 uppercase font-semibold">Biaya</p>
                                    <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $training->cost > 0 ? 'Rp ' . number_format($training->cost / 1000) . 'k' : 'Gratis' }}
                                    </p>
                                </div>

                                <div class="flex gap-2">
                                    <a href="{{ route('katalog.show', $training->id) }}" class="p-2.5 rounded-xl text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors" title="Lihat Detail">
                                        <ion-icon name="eye-outline" class="text-xl"></ion-icon>
                                    </a>
                                    
                                    <form action="{{ route('rencana.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="training_id" value="{{ $training->id }}">
                                        
                                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 rounded-xl font-bold text-xs text-white uppercase tracking-wide hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                            <ion-icon name="add-outline" class="text-lg mr-1"></ion-icon>
                                            Ajukan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                @empty
                    <div class="col-span-1 md:col-span-2 lg:col-span-3">
                        <div class="flex flex-col items-center justify-center py-20 px-4 text-center bg-white dark:bg-gray-800 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                            <div class="p-6 rounded-full bg-gray-50 dark:bg-gray-700 mb-6 animate-pulse">
                                <ion-icon name="search" class="text-5xl text-gray-300"></ion-icon>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Pelatihan Tidak Ditemukan</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-sm mx-auto leading-relaxed">
                                Kami tidak dapat menemukan pelatihan dengan kata kunci <strong>"{{ request('search') }}"</strong>. Coba kata kunci lain atau reset filter.
                            </p>
                            <a href="{{ route('katalog') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                Lihat Semua Pelatihan
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $trainings->links() }}
            </div>

        </div>
    </div>
</x-app-layout>