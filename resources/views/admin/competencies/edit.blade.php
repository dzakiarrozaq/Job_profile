<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Kompetensi: {{ $competency->competency_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
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
                            
                            {{-- Nama --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Kompetensi</label>
                                <input type="text" name="competency_name" value="{{ old('competency_name', $competency->competency_name) }}" required
                                       class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Definisi --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Definisi</label>
                                <textarea name="description" rows="10" required
                                          class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $competency->description ?? $competency->definition) }}</textarea>
                            </div>

                            {{-- Tipe --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Tipe</label>
                                <span class="inline-flex px-3 py-1 rounded-full text-sm font-bold {{ $competency->type === 'Perilaku' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $competency->type }}
                                </span>
                            </div>
                        </div>

                        {{-- KOLOM KANAN: KEY BEHAVIORS --}}
                        <div class="md:col-span-2 space-y-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 border-b pb-2">Perilaku Kunci (Key Behaviors)</h3>
                            
                            {{-- ================================================= --}}
                            {{-- LOGIKA TAMPILAN BERDASARKAN TIPE KOMPETENSI --}}
                            {{-- ================================================= --}}

                            @if($competency->type === 'Perilaku')
                                {{-- TAMPILAN KHUSUS PERILAKU --}}
                                @php
                                    // Ambil SEMUA data level 0, lalu gabungkan jadi string dengan nomor
                                    $behaviorsList = $competency->keyBehaviors
                                        ->where('level', 0)
                                        ->values()
                                        ->map(function($item, $index) {
                                            // Format: "1. Isi Perilaku"
                                            return ($index + 1) . ". " . $item->behavior;
                                        })
                                        ->implode("\n"); // Gabung dengan Enter (Urut ke bawah)
                                @endphp

                                <div class="bg-orange-50 dark:bg-orange-900/20 p-5 rounded-lg border border-orange-200 dark:border-orange-800">
                                    <div class="flex items-start gap-3 mb-2">
                                        <ion-icon name="information-circle" class="text-orange-600 text-xl mt-0.5"></ion-icon>
                                        <div>
                                            <label class="block text-sm font-bold text-orange-800 dark:text-orange-300">
                                                Ciri Perilaku Umum (General Indicators)
                                            </label>
                                            <p class="text-xs text-orange-600 dark:text-orange-400 mt-0.5">
                                                Tuliskan poin-poin perilaku urut ke bawah. Gunakan nomor (1. 2. 3.) agar sistem bisa mendeteksinya.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- Textarea Besar --}}
                                    <textarea name="behaviors[0]" rows="15" 
                                            placeholder="1. Perilaku pertama&#10;2. Perilaku kedua&#10;3. ..."
                                            class="w-full rounded-md border-orange-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm leading-relaxed">{{ old("behaviors.0", $behaviorsList) }}</textarea>
                                </div>

                            @else
                                {{-- TAMPILAN STANDAR (TEKNIS LEVEL 1-5) --}}
                                @php
                                    $levelNames = [
                                        1 => 'Knowledgeable (Paham)',
                                        2 => 'Comprehension (Mampu)',
                                        3 => 'Practitioner (Terampil)',
                                        4 => 'Advance (Mahir)',
                                        5 => 'Expert (Ahli)'
                                    ];
                                @endphp

                                @foreach(range(1, 5) as $level)
                                    @php
                                        $behavior = $competency->keyBehaviors->where('level', $level)->first();
                                        $value = $behavior ? $behavior->behavior : '';
                                        $name = $levelNames[$level] ?? 'Level ' . $level;
                                    @endphp

                                    <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-100 dark:border-gray-600">
                                        <label class="block text-sm font-bold text-indigo-600 dark:text-indigo-400 mb-2">
                                            Level {{ $level }} - {{ $name }}
                                        </label>
                                        <textarea name="behaviors[{{ $level }}]" rows="3" 
                                                  class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old("behaviors.$level", $value) }}</textarea>
                                    </div>
                                @endforeach
                            @endif

                        </div>
                    </div>

                    {{-- TOMBOL AKSI --}}
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <a href="{{ route('admin.competencies.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg">
                            <ion-icon name="save-outline" class="mr-2 text-base"></ion-icon>
                            Simpan Perubahan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-admin-layout>