<x-lp-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                Manajemen Katalog Pelatihan
            </h2>
            <a href="{{ route('lp.katalog.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-bold shadow transition flex items-center gap-2">
                <ion-icon name="add-circle-outline" class="text-xl"></ion-icon>
                Tambah Pelatihan
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-6 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <form action="{{ route('lp.katalog.index') }}" method="GET" class="relative">
                <ion-icon name="search-outline" class="absolute left-3 top-3 text-gray-400 text-xl"></ion-icon>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari judul pelatihan atau provider..." 
                       class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($trainings as $training)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition border border-gray-100 dark:border-gray-700 flex flex-col h-full">
                    
                    <div class="p-5 flex-grow">
                        <div class="flex justify-between items-start mb-3">
                            <span class="px-2 py-1 text-xs font-bold rounded-md 
                                {{ $training->method == 'Online' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $training->method }}
                            </span>
                            <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">
                                ID: #{{ $training->id }}
                            </span>
                        </div>
                        
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1 line-clamp-2">
                            {{ $training->title }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Oleh: {{ $training->provider }}
                        </p>

                        <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3 mb-4">
                            {{ $training->description ?? 'Tidak ada deskripsi.' }}
                        </p>
                    </div>

                    <div class="p-5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 rounded-b-xl flex justify-between items-center">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Biaya</p>
                            <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                Rp {{ number_format($training->cost) }}
                            </p>
                        </div>
                        
                        <div class="flex gap-2">
                            <a href="{{ route('lp.katalog.edit', $training->id) }}" 
                               class="p-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition">
                                <ion-icon name="create-outline" class="text-lg"></ion-icon>
                            </a>

                            <form action="{{ route('lp.katalog.destroy', $training->id) }}" method="POST" 
                                  onsubmit="return confirm('Hapus pelatihan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                                    <ion-icon name="trash-outline" class="text-lg"></ion-icon>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                    <p class="text-gray-500">Belum ada data pelatihan.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $trainings->appends(['search' => $search ?? ''])->links() }}
        </div>
    </div>
</x-lp-layout>