<x-lp-layout>
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
                <a href="{{ route('lp.katalog.create') }}" 
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
            <form action="{{ route('lp.katalog.index') }}" method="GET" class="relative">
                <ion-icon name="search-outline" class="absolute left-3 top-3 text-gray-400 text-xl"></ion-icon>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul pelatihan, provider, atau deskripsi..." 
                       class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
            </form>
        </div>

        {{-- Grid Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($trainings as $training)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition border border-gray-100 dark:border-gray-700 flex flex-col h-full group overflow-hidden">
                    
                    <div class="p-5 flex-grow">
                        {{-- Header Card: Level --}}
                        <div class="flex justify-end items-start mb-3">
                            <span class="text-[10px] uppercase font-bold px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 tracking-wider">
                                {{ $training->level ?? 'All Level' }}
                            </span>
                        </div>
                        
                        {{-- Judul --}}
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1 line-clamp-2 group-hover:text-indigo-600 transition" title="{{ $training->title }}">
                            {{ $training->title }}
                        </h3>
                        
                        {{-- Provider (dengan truncate agar rapi) --}}
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-4 gap-2">
                            <ion-icon name="business-outline" class="flex-shrink-0 text-indigo-500"></ion-icon>
                            <span class="truncate font-medium" title="{{ $training->provider ?? 'Internal Provider' }}">
                                {{ $training->provider ?? 'Internal Provider' }}
                            </span>
                        </div>

                        {{-- Deskripsi --}}
                        <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3 mb-4 leading-relaxed">
                            {{ $training->description ?? 'Tidak ada deskripsi untuk pelatihan ini.' }}
                        </p>
                    </div>

                    {{-- Footer Card --}}
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 flex justify-between items-center">
                        {{-- Durasi --}}
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Durasi</p>
                            <div class="flex items-center gap-1 text-indigo-600 dark:text-indigo-400">
                                <ion-icon name="time" class="text-sm"></ion-icon>
                                <p class="text-sm font-bold">
                                    {{ $training->duration ? $training->duration . ' Jam' : '-' }}
                                </p>
                            </div>
                        </div>
                        
                        {{-- Tombol Aksi --}}
                        <div class="flex gap-2">
                            <a href="{{ route('lp.katalog.edit', $training->id) }}" 
                               class="p-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition shadow-sm"
                               title="Edit Pelatihan">
                                <ion-icon name="create-outline" class="text-lg"></ion-icon>
                            </a>

                            <form action="{{ route('lp.katalog.destroy', $training->id) }}" method="POST" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelatihan ini? Tindakan ini tidak dapat dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition shadow-sm"
                                        title="Hapus Pelatihan">
                                    <ion-icon name="trash-outline" class="text-lg"></ion-icon>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Empty State --}}
                <div class="col-span-full py-16 text-center bg-white dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-700">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                        <ion-icon name="folder-open-outline" class="text-3xl text-gray-400"></ion-icon>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Belum ada data pelatihan</h3>
                    <p class="text-gray-500 dark:text-gray-400 mt-1 mb-6">Mulai dengan menambahkan pelatihan baru atau import dari Excel.</p>
                    <a href="{{ route('lp.katalog.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition shadow-md gap-2">
                        <ion-icon name="add-circle-outline" class="text-lg"></ion-icon>
                        Tambah Pelatihan
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $trainings->appends(['search' => request('search')])->links() }}
        </div>
    </div>

    {{-- === MODAL IMPORT EXCEL === --}}
    <div id="importModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('importModal').classList.add('hidden')"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                {{-- PERUBAHAN: Route Import mengarah ke 'lp.katalog.import' --}}
                <form action="{{ route('lp.katalog.import') }}" method="POST" enctype="multipart/form-data">
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
                    
                    {{-- Footer Modal --}}
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
</x-lp-layout>