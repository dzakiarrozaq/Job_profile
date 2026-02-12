<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            {{-- Judul Halaman --}}
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Manajemen Katalog Pelatihan') }}
            </h2>
            
            {{-- Group Tombol Aksi --}}
            <div class="flex items-center gap-3 w-full md:w-auto">
                {{-- Tombol Import --}}
                <button onclick="document.getElementById('importModal').classList.remove('hidden')" 
                        class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none transition ease-in-out duration-150 shadow-md gap-2">
                    <ion-icon name="document-text-outline" class="text-lg"></ion-icon>
                    Import Excel
                </button>

                {{-- Tombol Tambah --}}
                <a href="{{ route('admin.trainings.create') }}" 
                   class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none transition ease-in-out duration-150 shadow-md gap-2">
                    <ion-icon name="add-circle-outline" class="text-lg"></ion-icon>
                    Tambah Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Flash Message Success --}}
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex items-center gap-2">
                <ion-icon name="checkmark-circle" class="text-xl flex-shrink-0"></ion-icon>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        {{-- Flash Message Error --}}
        @if(session('error'))
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm flex items-center gap-2">
                <ion-icon name="alert-circle" class="text-xl flex-shrink-0"></ion-icon>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        {{-- Search Bar --}}
        <div class="mb-6 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <form action="{{ route('admin.trainings.index') }}" method="GET" class="relative">
                <ion-icon name="search-outline" class="absolute left-3 top-3 text-gray-400 text-xl"></ion-icon>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul pelatihan, provider, atau deskripsi..." 
                       class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
            </form>
        </div>

        {{-- TABEL VIEW --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">
                                No
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Informasi Pelatihan
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48">
                                Provider
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">
                                Level
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">
                                Durasi
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($trainings as $index => $training)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                {{-- Kolom No --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $trainings->firstItem() + $index }}
                                </td>

                                {{-- Kolom Judul & Deskripsi --}}
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900 dark:text-white mb-1">
                                        {{ $training->title }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2" title="{{ $training->description }}">
                                        {{ Str::limit($training->description, 100) ?? 'Tidak ada deskripsi.' }}
                                    </div>
                                </td>

                                {{-- Kolom Provider --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    <div class="flex items-center gap-2">
                                        <ion-icon name="business-outline" class="text-indigo-500"></ion-icon>
                                        {{ $training->provider ?? 'Internal' }}
                                    </div>
                                </td>

                                {{-- Kolom Level --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                        {{ $training->level ?? 'All' }}
                                    </span>
                                </td>

                                {{-- Kolom Durasi --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600 dark:text-gray-300">
                                    {{ $training->duration ? $training->duration . ' Jam' : '-' }}
                                </td>

                                {{-- Kolom Aksi --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex justify-center items-center gap-2">
                                        <a href="{{ route('admin.trainings.edit', $training->id) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 p-2 rounded-lg transition"
                                           title="Edit">
                                            <ion-icon name="create-outline" class="text-lg"></ion-icon>
                                        </a>

                                        <form action="{{ route('admin.trainings.destroy', $training->id) }}" method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelatihan ini? Tindakan ini tidak dapat dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 bg-red-50 dark:bg-red-900/30 p-2 rounded-lg transition"
                                                    title="Hapus">
                                                <ion-icon name="trash-outline" class="text-lg"></ion-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            {{-- Empty State dalam Tabel --}}
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800">
                                    <div class="flex flex-col items-center justify-center">
                                        <ion-icon name="folder-open-outline" class="text-4xl text-gray-300 mb-2"></ion-icon>
                                        <p class="text-base font-medium">Belum ada data pelatihan</p>
                                        <p class="text-xs mt-1">Silakan tambah baru atau import data.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $trainings->appends(['search' => request('search')])->links() }}
        </div>
    </div>

    {{-- === MODAL IMPORT EXCEL (Tidak Berubah) === --}}
    <div id="importModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('importModal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('admin.trainings.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 sm:mx-0 sm:h-10 sm:w-10">
                                <ion-icon name="cloud-upload-outline" class="text-green-600 dark:text-green-400 text-xl"></ion-icon>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                    Import Data Pelatihan
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                        Pilih file Excel (.xlsx atau .csv) yang berisi data pelatihan sesuai format DevHub.
                                    </p>
                                    <input type="file" name="file" required
                                           class="block w-full text-sm text-gray-500 dark:text-gray-300
                                           file:mr-4 file:py-2 file:px-4
                                           file:rounded-full file:border-0
                                           file:text-xs file:font-bold file:uppercase
                                           file:bg-indigo-50 file:text-indigo-700
                                           hover:file:bg-indigo-100
                                           dark:file:bg-gray-700 dark:file:text-gray-200
                                           cursor-pointer border border-gray-300 dark:border-gray-600 rounded-lg">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition">
                            Upload & Import
                        </button>
                        <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>