<x-supervisor-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('supervisor.persetujuan') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline mr-2">
                Pusat Persetujuan
            </a>
            <span class="text-gray-400 dark:text-gray-500 mr-2">/</span>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                Verifikasi: {{ $employee->name }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6">
        <form action="{{ route('supervisor.penilaian.store', $employee->id) }}" method="POST">
            @csrf
            
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Formulir Verifikasi Kompetensi</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tinjau penilaian mandiri karyawan. Klik nama kompetensi untuk melihat panduan perilaku.</p>
                </div>

                <div class="overflow-x-visible">
                    <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/3">Kompetensi</th>
                                <th class="px-6 py-3 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/12">Target</th>
                                <th class="px-6 py-3 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">Penilaian Karyawan</th>
                                <th class="px-6 py-3 text-center font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider bg-indigo-50 dark:bg-indigo-900/20 w-1/6">Verifikasi</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">Catatan</th>
                            </tr>
                        </thead>
                        
                        {{-- LOOPING MENGGUNAKAN TBODY AGAR BISA GROUPING ROW --}}
                        @foreach($competencies as $comp)
                        <tbody class="bg-white dark:bg-gray-800 border-b dark:border-gray-700" x-data="{ showDetails: false }">
                            
                            {{-- BARIS UTAMA --}}
                            <tr>
                                <td class="px-6 py-4 align-top">
                                    <div class="flex flex-col">
                                        <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $comp->competency_name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $comp->competency_code }} • {{ ucfirst($comp->type) }}</div>
                                        
                                        {{-- TOMBOL BUKA TUTUP KAMUS --}}
                                        <button type="button" @click="showDetails = !showDetails" 
                                                class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center gap-1 w-max focus:outline-none">
                                            <ion-icon :name="showDetails ? 'chevron-up-outline' : 'book-outline'"></ion-icon>
                                            <span x-text="showDetails ? 'Tutup Panduan' : 'Lihat Panduan Level'"></span>
                                        </button>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center align-top pt-6">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 font-bold border border-gray-300">
                                        {{ $comp->ideal_level }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center align-top">
                                    <div class="flex flex-col items-center gap-2">
                                        {{-- Badge Nilai Karyawan --}}
                                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-bold 
                                            {{ $comp->submitted_level > $comp->ideal_level ? 'bg-yellow-100 text-yellow-800 border border-yellow-300' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $comp->submitted_level }}
                                            
                                            @if($comp->submitted_level > $comp->ideal_level)
                                                <ion-icon name="arrow-up-outline" class="ml-1 text-xs"></ion-icon>
                                            @endif
                                        </span>

                                        {{-- KOTAK EVIDENCE (MUNCUL JIKA NILAI > TARGET & ADA BUKTI) --}}
                                        @if($comp->submitted_level > $comp->ideal_level && !empty($comp->evidence))
                                            <div class="mt-2 w-full text-left bg-yellow-50 border border-yellow-200 p-3 rounded-lg shadow-sm">
                                                <p class="text-[10px] font-bold text-yellow-700 uppercase mb-1 flex items-center">
                                                    <ion-icon name="chatbox-ellipses-outline" class="mr-1"></ion-icon> Alasan Karyawan:
                                                </p>
                                                <p class="text-xs text-gray-700 italic leading-snug">
                                                    "{{ $comp->evidence }}"
                                                </p>
                                            </div>
                                        @elseif($comp->submitted_level > $comp->ideal_level && empty($comp->evidence))
                                            <p class="text-xs text-red-500 italic mt-1">(Tidak ada alasan dilampirkan)</p>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center bg-indigo-50/50 dark:bg-indigo-900/10 align-top pt-5">
                                    <select name="verified_level[{{ $comp->competency_code }}]" 
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white text-center font-bold h-10">
                                        @foreach([1,2,3,4,5] as $val)
                                            <option value="{{ $val }}" {{ $val == ($comp->current_level ?: $comp->submitted_level) ? 'selected' : '' }}>
                                                {{ $val }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-6 py-4 align-top">
                                    <textarea name="notes[{{ $comp->competency_code }}]" rows="2" 
                                              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                              placeholder="Catatan untuk karyawan...">{{ $comp->reviewer_notes }}</textarea>
                                </td>
                            </tr>

                            {{-- BARIS ACCORDION (KAMUS PERILAKU) --}}
                            <tr x-show="showDetails" x-transition class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200">
                                <td colspan="5" class="px-6 py-4">
                                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Panduan Level Kompetensi: {{ $comp->competency_name }}</div>
                                    
                                    <div class="grid grid-cols-1 gap-2">
                                        @forelse($comp->key_behaviors as $behavior)
                                            <div class="flex items-start gap-3 p-2 rounded border border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700">
                                                <div class="flex-shrink-0">
                                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border 
                                                        {{ $behavior->level == $comp->ideal_level ? 'bg-green-100 text-green-800 border-green-300' : 'bg-gray-100 text-gray-500 border-gray-300' }}">
                                                        Level {{ $behavior->level }}
                                                        @if($behavior->level == $comp->ideal_level) (Target) @endif
                                                    </span>
                                                </div>
                                                <div class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
                                                    {!! nl2br(preg_replace('/(\d+\.\s)/', '<br>$1', e($behavior->behavior))) !!}
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-xs text-gray-400 italic">Panduan perilaku belum tersedia.</p>
                                        @endforelse
                                    </div>
                                    
                                    <div class="mt-2 text-center">
                                        <button type="button" @click="showDetails = false" class="text-xs text-indigo-500 hover:text-indigo-700 underline">Tutup</button>
                                    </div>
                                </td>
                            </tr>

                        </tbody>
                        @endforeach
                    </table>
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex justify-between items-center border-t border-gray-200 dark:border-gray-600">
    
                    {{-- KIRI: Tombol Batal --}}
                    <a href="{{ route('supervisor.persetujuan') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </a>

                    {{-- KANAN: Group Tombol Aksi --}}
                    <div class="flex gap-3">
                        {{-- TOMBOL TOLAK (REJECT) --}}
                        <button type="submit" name="action" value="reject" 
                                onclick="return confirm('Yakin ingin menolak? Status akan kembali ke Draft dan karyawan harus mengisi ulang.')"
                                class="px-4 py-2 text-sm font-bold text-red-700 bg-red-100 border border-red-200 rounded-lg hover:bg-red-200 focus:ring-2 focus:ring-red-500 transition flex items-center">
                            <ion-icon name="close-circle-outline" class="mr-1 text-lg"></ion-icon> Tolak / Revisi
                        </button>

                        {{-- TOMBOL SIMPAN (APPROVE) --}}
                        <button type="submit" name="action" value="approve" 
                                onclick="return confirm('Apakah Anda yakin ingin menyetujui penilaian ini?')" 
                                class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 shadow-md transition flex items-center">
                            <ion-icon name="checkmark-done-circle-outline" class="mr-1 text-lg"></ion-icon> Simpan & Verifikasi
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</x-supervisor-layout>