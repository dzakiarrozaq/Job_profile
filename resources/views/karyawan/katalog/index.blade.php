<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                {{ __('Katalog Pelatihan') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                <form method="GET" action="{{ route('katalog') }}" class="flex flex-col md:flex-row gap-4">
                    
                    <div class="relative flex-grow">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                            class="pl-10 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                            placeholder="Cari judul pelatihan, topik, atau provider...">
                    </div>

                    <div class="w-full md:w-48">
                        <select name="type" onchange="this.form.submit()" 
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm cursor-pointer">
                            <option value="all">Semua Tipe</option>
                            <option value="internal" {{ request('type') == 'internal' ? 'selected' : '' }}>Internal</option>
                            <option value="external" {{ request('type') == 'external' ? 'selected' : '' }}>External</option>
                        </select>
                    </div>

                    @if(request('search') || (request('type') && request('type') !== 'all'))
                        <a href="{{ route('katalog') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($trainings as $training)
                    <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 flex flex-col h-full overflow-hidden">
                        
                        <div class="h-32 bg-gradient-to-r {{ $training->type == 'internal' ? 'from-blue-500 to-indigo-600' : 'from-purple-500 to-pink-600' }} relative">
                            <span class="absolute top-4 right-4 px-3 py-1 rounded-full text-xs font-bold text-white bg-white/20 backdrop-blur-md border border-white/30 uppercase tracking-wide">
                                {{ $training->type }}
                            </span>
                            
                            <div class="absolute -bottom-6 -left-6 text-white/10 transform rotate-12">
                                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors">
                                    {{ $training->title }}
                                </h3>
                                
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    {{ $training->provider ?? 'Internal Company' }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-3 mb-4">
                                    {{ $training->description }}
                                </p>
                            </div>

                            <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center gap-3">
                                {{-- Tombol Detail --}}
                                {{-- <a href="{{ route('katalog.show', $training->id) }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    Lihat Detail
                                </a> --}}
                                
                                <button type="button" class="text-sm font-medium text-gray-500 cursor-not-allowed" disabled>Detail</button>

                                <form action="#" method="POST"> 
                                    @csrf
                                    <button type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        Ajukan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                @empty
                    <div class="col-span-1 md:col-span-2 lg:col-span-3">
                        <div class="flex flex-col items-center justify-center py-16 px-4 text-center bg-white dark:bg-gray-800 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700">
                            <div class="p-4 rounded-full bg-gray-50 dark:bg-gray-700 mb-4">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Pelatihan Tidak Ditemukan</h3>
                            <p class="text-gray-500 dark:text-gray-400 mt-1 max-w-sm">
                                Maaf, kami tidak menemukan pelatihan dengan kata kunci <strong>"{{ request('search') }}"</strong> atau filter yang Anda pilih.
                            </p>
                            <a href="{{ route('katalog') }}" class="mt-6 text-indigo-600 font-semibold hover:underline">
                                Bersihkan Pencarian
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $trainings->links() }}
            </div>

        </div>
    </div>
</x-app-layout>