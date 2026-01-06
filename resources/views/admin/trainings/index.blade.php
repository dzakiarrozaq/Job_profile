<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Katalog Pelatihan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700">
                    <p class="font-bold">Berhasil!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4">
                    
                    <form method="GET" action="{{ route('admin.trainings.index') }}" class="w-full md:w-1/3">
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul pelatihan..." 
                                class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <ion-icon name="search-outline" class="text-gray-400"></ion-icon>
                            </div>
                        </div>
                    </form>

                    <a href="{{ route('admin.trainings.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <ion-icon name="add-outline" class="text-lg mr-2"></ion-icon>
                        Tambah Pelatihan
                    </a>
                </div>

                {{-- Tabel Data --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Judul Pelatihan</th>
                                <th scope="col" class="px-6 py-3 text-center">Tipe</th>
                                <th scope="col" class="px-6 py-3 text-center">Level</th>
                                <th scope="col" class="px-6 py-3 text-center">Provider</th>
                                <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trainings as $training)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $training->title }}
                                    <div class="text-xs text-gray-500 font-normal mt-0.5">{{ Str::limit($training->description, 50) }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($training->type == 'internal')
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded border border-blue-200">Internal</span>
                                    @else
                                        <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded border border-purple-200">External</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        {{ $training->difficulty == 'Beginner' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $training->difficulty == 'Intermediate' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $training->difficulty == 'Advanced' ? 'bg-red-100 text-red-700' : '' }}">
                                        {{ $training->difficulty }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    {{ $training->provider }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-3">
                                        <a href="{{ route('admin.trainings.edit', $training->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline" title="Edit">
                                            <ion-icon name="create-outline" class="text-lg"></ion-icon>
                                        </a>
                                        
                                        {{-- Tombol Hapus (Placeholder) --}}
                                        <a href="#" class="font-medium text-red-600 dark:text-red-500 hover:underline" onclick="return confirm('Yakin ingin menghapus?')">
                                            <ion-icon name="trash-outline" class="text-lg"></ion-icon>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <ion-icon name="file-tray-outline" class="text-4xl mb-2 text-gray-300"></ion-icon>
                                    <p>Belum ada data pelatihan. Silakan tambah baru.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-6">
                    {{ $trainings->links() }}
                </div>
            </div>

        </div>
    </div>
</x-admin-layout>