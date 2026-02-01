<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Kompetensi
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Tombol Kembali --}}
            <a href="{{ route('admin.competencies.index') }}" class="inline-flex items-center mb-4 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                <ion-icon name="arrow-back-outline" class="mr-1"></ion-icon> Kembali ke Daftar
            </a>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('admin.competencies.update', $competency->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        
                        {{-- KOLOM KIRI: INFO UTAMA --}}
                        <div class="md:col-span-1 space-y-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 border-b pb-2">Informasi Umum</h3>
                            
                            {{-- Nama Kompetensi --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Kompetensi</label>
                                <input type="text" name="competency_name" value="{{ old('competency_name', $competency->competency_name) }}" required
                                       class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Definisi --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Definisi</label>
                                <textarea name="description" rows="10" required
                                          class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $competency->description) }}</textarea>
                            </div>

                            {{-- Tipe (Read Only) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Kategori / Tipe</label>
                                <input type="text" value="{{ $competency->type }}" readonly
                                       class="w-full rounded-md bg-gray-100 border-transparent text-gray-500 text-sm cursor-not-allowed">
                            </div>
                        </div>

                        {{-- KOLOM KANAN: KEY BEHAVIORS --}}
                        <div class="md:col-span-2 space-y-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 border-b pb-2">Perilaku Kunci (Key Behaviors)</h3>
                            
                            @php
                                // Definisi Nama Level
                                $levelNames = [
                                    1 => 'Knowledgeable',
                                    2 => 'Comprehension',
                                    3 => 'Practitioner',
                                    4 => 'Advance',
                                    5 => 'Expert'
                                ];
                            @endphp

                            {{-- Loop Level 1 sampai 5 --}}
                            @foreach(range(1, 5) as $level)
                                @php
                                    // Cari data behavior di level ini (jika ada)
                                    $behavior = $competency->keyBehaviors->where('level', $level)->first();
                                    $value = $behavior ? $behavior->behavior : '';
                                    
                                    // Ambil nama level dari array
                                    $name = $levelNames[$level] ?? 'Unknown';
                                @endphp

                                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-100 dark:border-gray-600">
                                    <label class="block text-sm font-bold text-indigo-600 dark:text-indigo-400 mb-2">
                                        Level {{ $level }} - {{ $name }}
                                    </label>
                                    <textarea name="behaviors[{{ $level }}]" rows="4" 
                                              placeholder="Deskripsi perilaku untuk level {{ $name }}..."
                                              class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old("behaviors.$level", $value) }}</textarea>
                                </div>
                            @endforeach

                        </div>
                    </div>

                    {{-- TOMBOL AKSI --}}
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <a href="{{ route('admin.competencies.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <ion-icon name="save-outline" class="mr-2 text-base"></ion-icon>
                            Simpan Perubahan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-admin-layout>