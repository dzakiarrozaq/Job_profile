<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            {{-- Judul Halaman --}}
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Katalog Pelatihan') }}
            </h2>

            {{-- Tombol Navigasi Tambahan --}}
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                
                {{-- Tombol Rencana Saya (Secondary / Ghost Style)
                <a href="{{ route('rencana.index') }}" 
                   class="group w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl font-medium text-sm text-gray-600 dark:text-gray-300 shadow-sm hover:text-indigo-600 dark:hover:text-white hover:border-indigo-200 dark:hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200">
                    <ion-icon name="list-outline" class="text-lg mr-2 transition-transform group-hover:-rotate-12 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-white"></ion-icon>
                    Rencana Saya
                </a> --}}

                {{-- Tombol Riwayat Pelatihan (Primary / Gradient Style) --}}
                <a href="{{ route('riwayat') }}" 
                   class="group w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-xl font-bold text-sm text-white shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 transform hover:-translate-y-0.5">
                    <ion-icon name="time-outline" class="text-lg mr-2 transition-transform group-hover:rotate-12"></ion-icon>
                    Riwayat Pelatihan
                </a>

            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ showFilters: false, showGap: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- SEARCH & FILTER --}}
            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <form method="GET" action="{{ route('katalog') }}">
                    <div class="flex flex-col lg:flex-row gap-4">
                        
                        {{-- Search Input --}}
                        <div class="relative flex-grow group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <ion-icon name="search-outline" class="text-xl text-gray-400 group-focus-within:text-indigo-500 transition-colors"></ion-icon>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                class="pl-11 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 transition-all" 
                                placeholder="Cari judul pelatihan, atau Nama Kompetensi...">
                        </div>

                        <button type="button" @click="showFilters = !showFilters" 
                            class="lg:w-auto w-full inline-flex justify-center items-center px-6 py-3 border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-all"
                            :class="{'bg-indigo-50 border-indigo-200 text-indigo-700': showFilters}">
                            <ion-icon name="options-outline" class="mr-2 text-lg"></ion-icon>
                            Filter Level
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
                         class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700 grid grid-cols-1 md:grid-cols-2 gap-6"
                         style="display: none;">
                        
                        {{-- Filter Level --}}
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3 text-sm uppercase tracking-wide">Pilih Level Pelatihan</h4>
                            <div class="flex flex-wrap gap-4">
                                @foreach(['Basic', 'Intermediate', 'Advanced'] as $level)
                                    <label class="flex items-center space-x-3 cursor-pointer group bg-gray-50 dark:bg-gray-700/50 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-indigo-300 transition-all">
                                        <input type="checkbox" name="levels[]" value="{{ $level }}" 
                                            {{ in_array($level, request('levels', [])) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">{{ $level }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex flex-col justify-end">
                            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl">
                                <p class="text-xs text-gray-500 mb-3">Terapkan filter untuk hasil yang lebih spesifik.</p>
                                <div class="flex gap-3">
                                    <a href="{{ route('katalog') }}" class="flex-1 px-4 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 text-gray-700 dark:text-gray-200 text-sm font-bold rounded-lg hover:bg-gray-50 text-center transition-colors">
                                        Reset
                                    </a>
                                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 shadow-sm text-center transition-colors">
                                        Terapkan Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if(request()->except(['search', 'page']))
                <div class="flex flex-wrap gap-2 items-center mb-4">
                    {{-- Cek jika ada filter aktif selain search dan page --}}
                    @if(count(request()->except(['search', 'page'])) > 0)
                        <span class="text-sm text-gray-500 dark:text-gray-400 mr-1">Active Filters:</span>
                        
                        @foreach(request()->except(['search', 'page']) as $key => $values)
                            
                            {{-- KASUS 1: Jika Filternya Array (Contoh: Level Checkbox) --}}
                            @if(is_array($values))
                                @foreach($values as $value)
                                    @php
                                        // Ambil semua query param saat ini
                                        $params = request()->query();
                                        
                                        // Hapus value spesifik ini dari array param tersebut
                                        // array_diff akan membuang 'Basic' jika kita klik silang pada 'Basic'
                                        $params[$key] = array_diff($params[$key], [$value]);
                                        
                                        // Jika array jadi kosong setelah dihapus, hapus key-nya sekalian supaya URL bersih
                                        if(empty($params[$key])) {
                                            unset($params[$key]);
                                        }
                                    @endphp

                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-700">
                                        {{ ucfirst($key) }}: {{ $value }}
                                        
                                        {{-- Link Generate URL Baru --}}
                                        <a href="{{ route('katalog', $params) }}" class="ml-2 text-indigo-600 hover:text-red-600 dark:text-indigo-300 transition-colors">
                                            <ion-icon name="close-circle" class="text-base align-middle"></ion-icon>
                                        </a>
                                    </span>
                                @endforeach

                            {{-- KASUS 2: Jika Filternya String Tunggal (Bukan Array) --}}
                            @else
                                @php
                                    // Ambil semua query param
                                    $params = request()->query();
                                    // Hapus key ini sepenuhnya
                                    unset($params[$key]);
                                @endphp

                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-700">
                                    {{ ucfirst($key) }}: {{ $values }}
                                    
                                    {{-- Link Generate URL Baru --}}
                                    <a href="{{ route('katalog', $params) }}" class="ml-2 text-indigo-600 hover:text-red-600 dark:text-indigo-300 transition-colors">
                                        <ion-icon name="close-circle" class="text-base align-middle"></ion-icon>
                                    </a>
                                </span>
                            @endif
                        @endforeach

                        {{-- Tombol Clear All --}}
                        <a href="{{ route('katalog') }}" class="text-xs text-red-500 hover:text-red-700 font-bold ml-2 underline decoration-dashed">
                            Clear All
                        </a>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($trainings as $training)
                    <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 border border-gray-100 dark:border-gray-700 flex flex-col h-full overflow-hidden relative">
                        
                        <div class="h-40 bg-gradient-to-br {{ $training->type == 'internal' ? 'from-blue-600 to-indigo-600' : 'from-purple-600 to-fuchsia-600' }} relative p-6 flex flex-col justify-between">
                            <div class="flex justify-between items-start">
                                {{-- 1. TAG TYPE DIHAPUS --}}
                                <div></div>
                                
                                @if(isset($training->level))
                                    <span class="px-2 py-1 rounded-md text-[10px] font-bold text-white bg-black/20 backdrop-blur-sm">
                                        {{ $training->level }}
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
                                    {!! nl2br(e(Str::limit($training->description, 150))) !!}
                                </p>
                            </div>

                            <div class="pt-5 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center gap-4">
                                <div>
                                    {{-- 2. DIGANTI JADI DURASI --}}
                                    <p class="text-xs text-gray-400 uppercase font-semibold">Durasi</p>
                                    <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $training->duration ? $training->duration . ' Jam' : '-' }}
                                    </p>
                                </div>

                                <div class="flex gap-2">
                                    <a href="{{ route('katalog.show', $training->id) }}" class="p-2.5 rounded-xl text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors" title="Lihat Detail">
                                        Detail
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
                    {{-- Empty State --}}
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
    {{-- === FLOATING GAP ANALYSIS WIDGET === --}}
    @if(isset($competencyGaps) && $competencyGaps->count() > 0)
        <div x-data="{ open: false }" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
            
            {{-- Panel Konten (Muncul saat diklik) --}}
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                 class="mb-4 w-[90vw] md:w-[600px] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                 style="display: none;">
                
                {{-- Header Panel --}}
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-white font-bold text-lg flex items-center gap-2">
                        <ion-icon name="analytics"></ion-icon>
                        Analisis Gap Kompetensi
                    </h3>
                    <button @click="open = false" class="text-white/80 hover:text-white transition-colors">
                        <ion-icon name="close" class="text-xl"></ion-icon>
                    </button>
                </div>

                {{-- Isi Tabel --}}
                <div class="max-h-[60vh] overflow-y-auto p-0">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 uppercase font-bold text-xs sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-4 py-3">Kompetensi</th>
                                <th class="px-4 py-3 text-center">Target</th>
                                <th class="px-4 py-3 text-center">Aktual</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach($competencyGaps as $gap)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-white line-clamp-2" title="{{ $gap->name }}">
                                            {{ $gap->name }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-gray-700 text-xs font-bold dark:bg-gray-600 dark:text-gray-200">
                                            {{ $gap->target }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white border border-gray-200 text-gray-700 text-xs font-bold dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                            {{ $gap->actual }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($gap->gap < 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-red-100 text-red-700 border border-red-200 dark:bg-red-900/50 dark:text-red-300 dark:border-red-800 whitespace-nowrap">
                                                Gap {{ $gap->gap }}
                                            </span>
                                        @elseif($gap->gap == 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-green-100 text-green-700 border border-green-200 dark:bg-green-900/50 dark:text-green-300 dark:border-green-800">
                                                Fit
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200 dark:bg-blue-900/50 dark:text-blue-300 dark:border-blue-800 whitespace-nowrap">
                                                +{{ $gap->gap }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- Footer Panel --}}
                <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 text-xs text-gray-500 text-center border-t border-gray-100 dark:border-gray-700">
                    * Data diambil dari Assessment terakhir.
                </div>
            </div>

            {{-- Tombol Floating (FAB) --}}
            <button @click="open = !open" 
                    class="group flex items-center gap-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full px-5 py-3 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 focus:outline-none focus:ring-4 focus:ring-indigo-300">
                <span class="font-bold text-sm hidden group-hover:block transition-all duration-300 whitespace-nowrap">
                    Cek Gap Kompetensi
                </span>
                <div class="relative">
                    <ion-icon name="stats-chart" class="text-xl"></ion-icon>
                    @php
                        $gapCount = $competencyGaps->where('gap', '<', 0)->count();
                    @endphp
                    @if($gapCount > 0)
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full animate-pulse border-2 border-indigo-600">
                            {{ $gapCount }}
                        </span>
                    @endif
                </div>
            </button>

        </div>
    @endif
</x-app-layout>