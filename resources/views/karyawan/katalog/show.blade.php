<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                <a href="{{ route('katalog') }}" class="text-gray-500 hover:text-indigo-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                {{ __('Detail Pelatihan') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                
                <div class="relative bg-gradient-to-r {{ $training->type == 'internal' ? 'from-blue-600 to-indigo-700' : 'from-purple-600 to-pink-700' }} p-8 md:p-12">
                    <div class="relative z-10">
                        <div class="flex flex-wrap gap-3 mb-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold text-white bg-white/20 backdrop-blur-md border border-white/30 uppercase tracking-wide">
                                {{ $training->type }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold text-white bg-white/20 backdrop-blur-md border border-white/30 uppercase tracking-wide">
                                {{ $training->difficulty }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold text-white bg-white/20 backdrop-blur-md border border-white/30 uppercase tracking-wide">
                                {{ $training->method ?? 'Online' }}
                            </span>
                        </div>
                        
                        <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-4 leading-tight">
                            {{ $training->title }}
                        </h1>
                        
                        <div class="flex items-center text-white/90 text-sm md:text-base gap-6">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                <span>{{ $training->provider }}</span>
                            </div>
                            @if($training->duration)
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>{{ $training->duration }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="absolute top-0 right-0 h-full w-1/3 overflow-hidden opacity-10">
                        <svg class="h-full w-full transform scale-150 translate-x-1/4 -translate-y-1/4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row">
                    <div class="flex-1 p-8 md:p-10">
                        <div class="prose dark:prose-invert max-w-none">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Deskripsi Pelatihan</h3>
                            <div class="text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                                {{ $training->description }}
                            </div>

                            @if($training->link_url)
                                <div class="mt-8">
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Informasi Tambahan</h4>
                                    <a href="{{ $training->link_url }}" target="_blank" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        Kunjungi Link Materi / Pendaftaran
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="w-full md:w-80 bg-gray-50 dark:bg-gray-700/30 border-t md:border-t-0 md:border-l border-gray-100 dark:border-gray-700 p-8">
                        <div class="sticky top-8">
                            <div class="mb-6">
                                <p class="text-sm text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Biaya</p>
                                <p class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">
                                    Rp {{ number_format($training->cost ?? 0) }}
                                </p>
                            </div>

                            <div class="space-y-4">
                                <form action="{{ route('rencana.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="training_id" value="{{ $training->id }}">
                                    
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3.5 bg-indigo-600 border border-transparent rounded-xl font-bold text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Ajukan Rencana
                                    </button>
                                </form>

                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mt-6">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="ml-3 flex-1 md:flex md:justify-between">
                                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                                Pelatihan ini akan ditambahkan ke "Rencana Saya" dan menunggu persetujuan Supervisor.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>