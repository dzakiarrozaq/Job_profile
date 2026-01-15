<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Katalog Pelatihan') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{ showFilters: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <form method="GET" action="{{ route('katalog') }}">
                    <div class="flex flex-col lg:flex-row gap-4">
                        
                        <div class="relative flex-grow group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <ion-icon name="search-outline" class="text-xl text-gray-400 group-focus-within:text-indigo-500 transition-colors"></ion-icon>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                class="pl-11 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 transition-all" 
                                placeholder="Cari judul pelatihan, topik, atau provider...">
                        </div>

                        <button type="button" @click="showFilters = !showFilters" 
                            class="lg:w-auto w-full inline-flex justify-center items-center px-6 py-3 border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-all"
                            :class="{'bg-indigo-50 border-indigo-200 text-indigo-700': showFilters}">
                            <ion-icon name="options-outline" class="mr-2 text-lg"></ion-icon>
                            Filter
                            <span class="ml-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 py-0.5 px-2 rounded-full text-xs" x-show="!showFilters">
                                {{ collect(request()->except(['search', 'page']))->count() }}
                            </span>
                            <ion-icon :name="showFilters ? 'chevron-up-outline' : 'chevron-down-outline'" class="ml-2 text-sm"></ion-icon>
                        </button>

                        <button type="submit" class="lg:w-auto w-full inline-flex justify-center items-center px-8 py-3 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                            Cari
                        </button>
                    </div>

                    <div x-show="showFilters" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"
                         style="display: none;">
                        
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3 text-sm uppercase tracking-wide">Kategori</h4>
                            <div class="space-y-2 max-h-40 overflow-y-auto pr-2 custom-scrollbar">
                                @foreach(['Leadership', 'Technical', 'Safety', 'Soft Skills', 'Digital'] as $cat)
                                    <label class="flex items-center space-x-3 cursor-pointer group">
                                        <input type="checkbox" name="categories[]" value="{{ $cat }}" 
                                            {{ in_array($cat, request('categories', [])) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="text-sm text-gray-600 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">{{ $cat }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3 text-sm uppercase tracking-wide">Level</h4>
                            <div class="space-y-2">
                                @foreach(['Beginner', 'Intermediate', 'Advanced'] as $level)
                                    <label class="flex items-center space-x-3 cursor-pointer group">
                                        <input type="checkbox" name="levels[]" value="{{ $level }}" 
                                            {{ in_array($level, request('levels', [])) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="text-sm text-gray-600 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">{{ $level }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3 text-sm uppercase tracking-wide">Metode & Tipe</h4>
                            <div class="space-y-2">
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <input type="checkbox" name="methods[]" value="Online" {{ in_array('Online', request('methods', [])) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Online / E-Learning</span>
                                </label>
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <input type="checkbox" name="methods[]" value="Offline" {{ in_array('Offline', request('methods', [])) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Offline / Classroom</span>
                                </label>
                                <hr class="border-gray-200 dark:border-gray-600 my-2">
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <input type="checkbox" name="types[]" value="internal" {{ in_array('internal', request('types', [])) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Internal Provider</span>
                                </label>
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <input type="checkbox" name="types[]" value="external" {{ in_array('external', request('types', [])) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">External Provider</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex flex-col justify-end">
                            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl">
                                <p class="text-xs text-gray-500 mb-3">Filter helps you find relevant content faster.</p>
                                <div class="flex gap-3">
                                    <a href="{{ route('katalog') }}" class="flex-1 px-4 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 text-gray-700 dark:text-gray-200 text-sm font-bold rounded-lg hover:bg-gray-50 text-center transition-colors">
                                        Reset
                                    </a>
                                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 shadow-sm text-center transition-colors">
                                        Terapkan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if(request()->except(['search', 'page']))
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400 mr-1">Active Filters:</span>
                    @foreach(request()->except(['search', 'page']) as $key => $values)
                        @if(is_array($values))
                            @foreach($values as $value)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                    {{ ucfirst($key) }}: {{ $value }}
                                    <a href="#" class="ml-2 text-indigo-600 hover:text-indigo-900 dark:text-indigo-300"><ion-icon name="close-circle"></ion-icon></a>
                                    </span>
                            @endforeach
                        @else
                             <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                {{ ucfirst($key) }}: {{ $values }}
                            </span>
                        @endif
                    @endforeach
                    <a href="{{ route('katalog') }}" class="text-xs text-red-500 hover:text-red-700 font-bold ml-2">Clear All</a>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($trainings as $training)
                    <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 border border-gray-100 dark:border-gray-700 flex flex-col h-full overflow-hidden relative">
                        
                        <div class="h-40 bg-gradient-to-br {{ $training->type == 'internal' ? 'from-blue-600 to-indigo-600' : 'from-purple-600 to-fuchsia-600' }} relative p-6 flex flex-col justify-between">
                            <div class="flex justify-between items-start">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold text-white bg-white/20 backdrop-blur-md border border-white/30 uppercase tracking-wide">
                                    {{ $training->type }}
                                </span>
                                @if(isset($training->difficulty))
                                    <span class="px-2 py-1 rounded-md text-[10px] font-bold text-white bg-black/20 backdrop-blur-sm">
                                        {{ $training->difficulty }}
                                    </span>
                                @endif
                            </div>
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
                                Kami tidak dapat menemukan pelatihan dengan kriteria pencarian Anda. Coba reset filter atau gunakan kata kunci lain.
                            </p>
                            <a href="{{ route('katalog') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                Reset Semua Filter
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $trainings->withQueryString()->links() }}
            </div>

        </div>
    </div>
</x-app-layout>