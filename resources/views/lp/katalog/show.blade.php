<x-lp-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Detail Pelatihan
        </h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
            
            <div class="bg-indigo-600 px-6 py-6 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="inline-block px-3 py-1 bg-indigo-500 rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                            {{ $training->type ?? 'Training' }}
                        </span>
                        <h3 class="text-3xl font-bold">{{ $training->title }}</h3>
                        <p class="text-indigo-200 mt-1 text-lg">by {{ $training->provider }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm opacity-75">Biaya Investasi</p>
                        <p class="text-2xl font-bold">Rp {{ number_format($training->cost) }}</p>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <div class="mb-8">
                    <h4 class="text-lg font-bold text-gray-800 dark:text-white mb-2 border-b pb-2">Deskripsi Pelatihan</h4>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        {{ $training->description ?: 'Tidak ada deskripsi detail.' }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 dark:bg-gray-700/50 p-6 rounded-xl">
                    <div>
                        <span class="text-gray-500 text-sm block">Durasi</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $training->duration ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 text-sm block">Lokasi / Link</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $training->location ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 text-sm block">Level Difficulty</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $training->difficulty ?? 'General' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 text-sm block">Terakhir Diupdate</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ $training->updated_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-8 pt-6 border-t dark:border-gray-700">
                    <a href="{{ route('lp.katalog.index') }}" class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition">
                        &larr; Kembali ke Katalog
                    </a>

                    <div class="flex gap-3">
                        <form action="{{ route('lp.katalog.destroy', $training->id) }}" method="POST" onsubmit="return confirm('Hapus permanen?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-5 py-2.5 border border-red-500 text-red-600 rounded-lg hover:bg-red-50 font-medium transition">
                                Hapus
                            </button>
                        </form>
                        
                        <a href="{{ route('lp.katalog.edit', $training->id) }}" class="px-5 py-2.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 font-bold shadow-lg transition">
                            Edit Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-lp-layout>