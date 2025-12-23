<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rekomendasi Pelatihan (AI Powered)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                
                {{-- Header Section --}}
                <div class="mb-8 border-b border-gray-100 pb-6">
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Analisis Kebutuhan Anda
                    </h3>
                    <p class="text-gray-600 mt-2">
                        Sistem mendeteksi gap kompetensi pada: <span class="font-semibold text-indigo-600 bg-indigo-50 px-2 py-1 rounded">{{ $gapText }}</span>
                    </p>
                </div>

                <div class="flex justify-between items-end mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Pelatihan yang Disarankan:</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                    @forelse($recommendations as $training)
                        <div class="group border border-gray-200 rounded-xl p-5 hover:shadow-lg hover:border-indigo-300 transition duration-300 bg-white flex flex-col h-full">
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-lg text-gray-900 group-hover:text-indigo-600 transition">{{ $training->title }}</h4>
                                </div>
                                <p class="text-sm text-gray-500 line-clamp-2 mb-4">{{ $training->description }}</p>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-100 text-xs font-semibold rounded-full uppercase tracking-wide">
                                    {{ ucfirst($training->type) }}
                                </span>
                                <button class="text-indigo-600 text-sm font-semibold hover:text-indigo-800 flex items-center gap-1 group/btn">
                                    Ajukan
                                    <svg class="w-4 h-4 transform group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 flex flex-col items-center justify-center py-12 text-center bg-gray-50 rounded-xl border border-dashed border-gray-300">
                            <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h4 class="text-gray-900 font-medium">Anda Kompeten!</h4>
                            <p class="text-gray-500 text-sm mt-1">Tidak ada gap kompetensi yang terdeteksi, atau belum ada pelatihan yang relevan.</p>
                        </div>
                    @endforelse
                </div>

                <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-gray-900 to-gray-800 text-white shadow-xl">
                    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-indigo-500 opacity-20 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 rounded-full bg-blue-500 opacity-20 blur-3xl"></div>

                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between p-8 gap-6">
                        <div class="flex items-start gap-5">
                            <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
                                <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-white">Ingin eksplorasi materi lain?</h4>
                                <p class="text-gray-300 mt-1 text-sm max-w-lg">
                                    Selain rekomendasi AI di atas, kami memiliki ratusan pelatihan lain yang bisa menunjang karir Anda.
                                </p>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <a href="{{ route('katalog') }}" class="group inline-flex items-center gap-2 px-6 py-3 bg-white text-gray-900 font-bold rounded-lg hover:bg-indigo-50 transition-all duration-300 shadow-lg hover:shadow-indigo-500/30">
                                Lihat Semua Katalog
                                <svg class="w-5 h-5 text-indigo-600 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>