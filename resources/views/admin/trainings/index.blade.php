<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                {{ __('Manajemen Katalog Pelatihan') }}
            </h2>
            
            <a href="{{ route('admin.trainings.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-bold shadow transition flex items-center gap-2">
                <ion-icon name="add-circle-outline" class="text-xl"></ion-icon>
                Tambah Pelatihan
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex items-center gap-2">
                <ion-icon name="checkmark-circle" class="text-xl"></ion-icon>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="mb-6 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <form action="{{ route('admin.trainings.index') }}" method="GET" class="relative">
                <ion-icon name="search-outline" class="absolute left-3 top-3 text-gray-400 text-xl"></ion-icon>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul pelatihan, provider, atau deskripsi..." 
                       class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($trainings as $training)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition border border-gray-100 dark:border-gray-700 flex flex-col h-full group">
                    
                    <div class="p-5 flex-grow">
                        <div class="flex justify-between items-start mb-3">
                            <span class="px-2 py-1 text-xs font-bold rounded-md 
                                {{ $training->type == 'Online' ? 'bg-blue-100 text-blue-700' : 
                                  ($training->type == 'Offline' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-orange-700') }}">
                                {{ $training->type ?? 'General' }}
                            </span>
                            
                            <span class="text-xs font-medium px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                {{ $training->difficulty ?? 'All Level' }}
                            </span>
                        </div>
                        
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1 line-clamp-2 group-hover:text-indigo-600 transition">
                            {{ $training->title }}
                        </h3>
                        
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-4 gap-2">
                            <ion-icon name="business-outline"></ion-icon>
                            <span>{{ $training->provider }}</span>
                        </div>

                        <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3 mb-4">
                            {{ $training->description ?? 'Tidak ada deskripsi.' }}
                        </p>
                    </div>

                    <div class="p-5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 rounded-b-xl flex justify-between items-center">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Biaya</p>
                            <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                Rp {{ number_format($training->cost ?? 0) }}
                            </p>
                        </div>
                        
                        <div class="flex gap-2">
                            <a href="{{ route('admin.trainings.edit', $training->id) }}" 
                               class="p-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition shadow-sm"
                               title="Edit Pelatihan">
                                <ion-icon name="create-outline" class="text-lg"></ion-icon>
                            </a>

                            <form action="{{ route('admin.trainings.destroy', $training->id) }}" method="POST" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelatihan ini? Tindakan ini tidak dapat dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition shadow-sm"
                                        title="Hapus Pelatihan">
                                    <ion-icon name="trash-outline" class="text-lg"></ion-icon>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center bg-white dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-700">
                    <ion-icon name="folder-open-outline" class="text-4xl text-gray-400 mb-2"></ion-icon>
                    <p class="text-gray-500 font-medium">Belum ada data pelatihan.</p>
                    <a href="{{ route('admin.trainings.create') }}" class="text-indigo-600 hover:underline text-sm mt-1">Tambah pelatihan baru sekarang</a>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $trainings->appends(['search' => request('search')])->links() }}
        </div>
    </div>
</x-admin-layout>