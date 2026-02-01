<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Master Kamus Kompetensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Pesan Sukses/Error --}}
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Kompetensi</h3>
                    
                    <button x-data @click="$dispatch('open-modal', 'import-competency-modal')" 
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center gap-2">
                        <ion-icon name="document-text-outline"></ion-icon>
                        Import Excel
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                {{-- Kolom No (Opsional, agar rapi) --}}
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-10">No</th>
                                
                                {{-- Kolom Nama & Definisi digabung --}}
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Kompetensi & Definisi</th>
                                
                                {{-- Kolom Aksi (Edit & Hapus) --}}
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($competencies as $index => $comp)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $competencies->firstItem() + $index }}
                                </td>
                                
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <div class="font-bold text-base mb-1">{{ $comp->competency_name }}</div>
                                    <p class="text-sm text-gray-500 line-clamp-2">
                                        {{ $comp->description ?? '-' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <div class="flex justify-center gap-2">
                                        {{-- Tombol Edit (Kuning) --}}
                                        <a href="{{ route('admin.competencies.edit', $comp->id) }}" 
                                           class="inline-flex items-center px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md text-xs transition">
                                            <ion-icon name="create-outline" class="text-base"></ion-icon>
                                        </a>

                                        {{-- Tombol Hapus (Merah) --}}
                                        <form action="{{ route('admin.competencies.destroy', $comp->id) }}" method="POST" 
                                              onsubmit="return confirm('Yakin ingin menghapus kompetensi ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-md text-xs transition">
                                                <ion-icon name="trash-outline" class="text-base"></ion-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">Belum ada data kompetensi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $competencies->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Import (Tetap Sama) --}}
    <x-modal name="import-competency-modal" focusable>
        <form method="post" action="{{ route('admin.competencies.import') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Import Data Kompetensi</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Silakan upload file Excel (.xlsx).</p>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih File</label>
                <input type="file" name="file" required class="block w-full text-sm border border-gray-300 rounded-lg cursor-pointer bg-gray-50">
                <p class="mt-1 text-xs text-gray-500">Max size: 5MB</p>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-primary-button class="bg-green-600 hover:bg-green-700">Import Sekarang</x-primary-button>
            </div>
        </form>
    </x-modal>
</x-admin-layout>