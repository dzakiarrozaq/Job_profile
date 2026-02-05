<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Master Kamus Kompetensi') }}
            </h2>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Total Kompetensi: <span class="font-bold text-gray-800 dark:text-gray-200">{{ $competencies->total() }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Alert Messages --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition class="flex items-center p-4 mb-4 text-sm text-green-800 border border-green-300 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 dark:border-green-800" role="alert">
                    <ion-icon name="checkmark-circle" class="flex-shrink-0 inline w-5 h-5 mr-3"></ion-icon>
                    <div><span class="font-medium">Berhasil!</span> {{ session('success') }}</div>
                    <button @click="show = false" type="button" class="ml-auto -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex h-8 w-8 dark:bg-gray-800 dark:text-green-400 dark:hover:bg-gray-700">
                        <ion-icon name="close"></ion-icon>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition class="flex items-center p-4 mb-4 text-sm text-red-800 border border-red-300 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800" role="alert">
                    <ion-icon name="alert-circle" class="flex-shrink-0 inline w-5 h-5 mr-3"></ion-icon>
                    <div><span class="font-medium">Gagal!</span> {{ session('error') }}</div>
                    <button @click="show = false" type="button" class="ml-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700">
                        <ion-icon name="close"></ion-icon>
                    </button>
                </div>
            @endif

            {{-- SECTION 1: TOOLBAR & ACTIONS --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- SEARCH & FILTER CARD --}}
                <div class="lg:col-span-1 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <ion-icon name="search-outline"></ion-icon> Filter Data
                    </h3>
                    <form method="GET" action="{{ url()->current() }}" class="space-y-4">
                        {{-- Search Input --}}
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <ion-icon name="search" class="text-gray-400"></ion-icon>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                class="block w-full p-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" 
                                placeholder="Cari nama kompetensi...">
                        </div>

                        {{-- Tipe Dropdown --}}
                        <div>
                            <select name="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                <option value="">Semua Tipe</option>
                                <option value="Teknis" {{ request('type') == 'Teknis' ? 'selected' : '' }}>Teknis</option>
                                <option value="Perilaku" {{ request('type') == 'Perilaku' ? 'selected' : '' }}>Perilaku</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-indigo-600 dark:hover:bg-indigo-700 focus:outline-none dark:focus:ring-indigo-800 transition">
                                Terapkan
                            </button>
                            @if(request('search') || request('type'))
                                <a href="{{ url()->current() }}" class="w-auto text-gray-700 bg-gray-200 hover:bg-gray-300 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-4 py-2.5 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500 transition flex items-center justify-center">
                                    <ion-icon name="refresh"></ion-icon>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- IMPORT ACTIONS CARD --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <ion-icon name="cloud-upload-outline"></ion-icon> Pusat Import Data
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        {{-- Group Perilaku --}}
                        <div class="space-y-3">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Kompetensi Perilaku</p>
                            
                            <button x-data @click="$dispatch('open-modal', 'import-definition-modal')" 
                                class="w-full flex items-center justify-between px-4 py-3 bg-purple-50 text-purple-700 rounded-lg border border-purple-200 hover:bg-purple-100 transition group">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-purple-200 rounded-full text-purple-700 group-hover:bg-white transition">
                                        <ion-icon name="book"></ion-icon>
                                    </div>
                                    <div class="text-left">
                                        <div class="font-bold text-sm">Master Definisi</div>
                                        <div class="text-xs text-purple-500">Import Nama & Definisi</div>
                                    </div>
                                </div>
                                <ion-icon name="chevron-forward" class="text-purple-400"></ion-icon>
                            </button>

                            <button x-data @click="$dispatch('open-modal', 'import-behavior-modal')" 
                                class="w-full flex items-center justify-between px-4 py-3 bg-indigo-50 text-indigo-700 rounded-lg border border-indigo-200 hover:bg-indigo-100 transition group">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-indigo-200 rounded-full text-indigo-700 group-hover:bg-white transition">
                                        <ion-icon name="git-network"></ion-icon>
                                    </div>
                                    <div class="text-left">
                                        <div class="font-bold text-sm">Relasi Perilaku</div>
                                        <div class="text-xs text-indigo-500">Relasi Struktural & Fungsional</div>
                                    </div>
                                </div>
                                <ion-icon name="chevron-forward" class="text-indigo-400"></ion-icon>
                            </button>
                        </div>

                        {{-- Group Teknis --}}
                        <div class="space-y-3">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Kompetensi Teknis</p>
                            
                            <button x-data @click="$dispatch('open-modal', 'import-competency-modal')" 
                                class="w-full flex items-center justify-between px-4 py-3 bg-green-50 text-green-700 rounded-lg border border-green-200 hover:bg-green-100 transition group">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-green-200 rounded-full text-green-700 group-hover:bg-white transition">
                                        <ion-icon name="list"></ion-icon>
                                    </div>
                                    <div class="text-left">
                                        <div class="font-bold text-sm">Master Teknis</div>
                                        <div class="text-xs text-green-500">Import Daftar Kompetensi</div>
                                    </div>
                                </div>
                                <ion-icon name="chevron-forward" class="text-green-400"></ion-icon>
                            </button>

                            <button x-data @click="$dispatch('open-modal', 'import-technical-standard-modal')" 
                                class="w-full flex items-center justify-between px-4 py-3 bg-orange-50 text-orange-700 rounded-lg border border-orange-200 hover:bg-orange-100 transition group">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-orange-200 rounded-full text-orange-700 group-hover:bg-white transition">
                                        <ion-icon name="construct"></ion-icon>
                                    </div>
                                    <div class="text-left">
                                        <div class="font-bold text-sm">Relasi Teknis</div>
                                        <div class="text-xs text-orange-500">Map Kompetensi ke Posisi</div>
                                    </div>
                                </div>
                                <ion-icon name="chevron-forward" class="text-orange-400"></ion-icon>
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            {{-- SECTION 2: DATA TABLE --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-1">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b">
                                <tr>
                                    <th scope="col" class="px-6 py-4 w-12 text-center">No</th>
                                    <th scope="col" class="px-6 py-4">Detail Kompetensi</th>
                                    <th scope="col" class="px-6 py-4 w-32 text-center">Tipe</th>
                                    <th scope="col" class="px-6 py-4 w-32 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($competencies as $index => $comp)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                                    <td class="px-6 py-4 text-center font-medium text-gray-900 dark:text-white">
                                        {{ $competencies->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                                {{ $comp->competency_name }}
                                                @if($comp->competency_code)
                                                    <span class="text-xs font-normal text-gray-400 bg-gray-100 px-2 py-0.5 rounded border border-gray-200">
                                                        {{ $comp->competency_code }}
                                                    </span>
                                                @endif
                                            </span>
                                            <span class="mt-1 text-gray-500 line-clamp-2 italic">
                                                "{{ $comp->definition ?? $comp->description ?? 'Tidak ada definisi' }}"
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($comp->type == 'Perilaku')
                                            <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-purple-900 dark:text-purple-300 border border-purple-300">
                                                Perilaku
                                            </span>
                                        @else
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300 border border-blue-300">
                                                Teknis
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('admin.competencies.edit', $comp->id) }}" class="text-yellow-500 hover:text-yellow-600 dark:text-yellow-400 dark:hover:text-yellow-300 transition" title="Edit">
                                                <ion-icon name="create" class="text-xl"></ion-icon>
                                            </a>
                                            <form action="{{ route('admin.competencies.destroy', $comp->id) }}" method="POST" onsubmit="return confirm('Yakin hapus? Data yang berelasi mungkin akan terpengaruh.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 transition" title="Hapus">
                                                    <ion-icon name="trash" class="text-xl"></ion-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="p-3 bg-gray-100 rounded-full mb-3">
                                                <ion-icon name="search-outline" class="text-4xl text-gray-400"></ion-icon>
                                            </div>
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Data Tidak Ditemukan</h3>
                                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Coba kata kunci lain atau import data baru.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                {{-- Pagination --}}
                @if($competencies->hasPages())
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-200 dark:border-gray-600">
                    {{ $competencies->withQueryString()->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ================= MODALS (Keep Existing Logic, Better UI) ================= --}}

    {{-- MODAL 1: IMPORT BIASA --}}
    <x-modal name="import-competency-modal" focusable>
        <form method="post" action="{{ route('admin.competencies.import') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Import Master Teknis</h2>
                <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-500"><ion-icon name="close" class="text-xl"></ion-icon></button>
            </div>
            
            <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                <span class="font-medium">Format Kolom:</span> Name, Code, Type, Definition.
            </div>

            <div class="mb-6">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_input">Upload File (Excel/CSV)</label>
                <input name="file" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="file_input" type="file" required>
            </div>

            <div class="flex justify-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-primary-button class="bg-green-600 hover:bg-green-700">Mulai Import</x-primary-button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL 2: IMPORT MATRIX --}}
    <x-modal name="import-behavior-modal" focusable>
        <form method="post" action="{{ route('admin.competencies.import.behavior') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Import Matrix Level</h2>
                <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-500"><ion-icon name="close" class="text-xl"></ion-icon></button>
            </div>

            <p class="text-sm text-gray-500 mb-4">File: <strong>Master Kompetensi Perilaku & Band.xlsx</strong> (Sheet: STRUKTURAL & FUNGSIONAL)</p>

            <div class="mb-6">
                <input name="file" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50" type="file" required>
            </div>

            <div class="flex justify-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">Import Matrix</x-primary-button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL 3: IMPORT DEFINISI --}}
    <x-modal name="import-definition-modal" focusable>
        <form method="post" action="{{ route('admin.competencies.import.definition') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Import Master Definisi</h2>
                <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-500"><ion-icon name="close" class="text-xl"></ion-icon></button>
            </div>
            
            <p class="text-sm text-gray-500 mb-4">File: <strong>Kompetensi Perilaku.xlsx</strong> (Definisi & Perilaku Kunci)</p>

            <div class="mb-6">
                <input name="file" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50" type="file" required>
            </div>

            <div class="flex justify-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-primary-button class="bg-purple-600 hover:bg-purple-700">Import Definisi</x-primary-button>
            </div>
        </form>
    </x-modal>
    
    {{-- MODAL 4: IMPORT PAKEM TEKNIS --}}
    <x-modal name="import-technical-standard-modal" focusable>
        <form method="post" action="{{ route('admin.competencies.import.technical-standard') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Import Pakem Teknis</h2>
                <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-500"><ion-icon name="close" class="text-xl"></ion-icon></button>
            </div>
            
            <div class="p-4 mb-4 text-sm text-orange-800 rounded-lg bg-orange-50" role="alert">
                <span class="font-medium">File:</span> Recap Kompetensi Teknis.xlsx. <br>
                Pastikan nama Jabatan sesuai dengan database.
            </div>

            <div class="mb-6">
                <input name="file" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50" type="file" required>
            </div>

            <div class="flex justify-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-primary-button class="bg-orange-600 hover:bg-orange-700">Proses Pakem</x-primary-button>
            </div>
        </form>
    </x-modal>

</x-admin-layout>