<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold text-gray-900">
            Penilaian Kompetensi Saya
        </h1>
        <p class="text-gray-600 mt-1">Isi penilaian mandiri (Self-Assessment). Berikan alasan/bukti jika nilai Anda melebihi standar.</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Alert Messages --}}
            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg flex items-center gap-2 shadow-sm">
                    <ion-icon name="checkmark-circle" class="text-xl"></ion-icon> 
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 lg:p-8">
                
                {{-- Header Status --}}
                <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Formulir Self-Assessment (Teknis)</h2>
                        <p class="text-xs text-gray-500 mt-1">Hanya menampilkan kompetensi fungsional/teknis sesuai jabatan Anda.</p>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] uppercase font-bold text-gray-400 mb-1">Status Dokumen</span>
                        @if($globalStatus === 'verified')
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full font-bold text-xs flex items-center gap-1">
                                <ion-icon name="shield-checkmark"></ion-icon> Terverifikasi
                            </span>
                        @elseif($globalStatus === 'pending_verification')
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full font-bold text-xs flex items-center gap-1">
                                <ion-icon name="time"></ion-icon> Menunggu Review
                            </span>
                        @elseif($globalStatus === 'rejected')
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full font-bold text-xs flex items-center gap-1">
                                <ion-icon name="alert-circle"></ion-icon> Perlu Revisi
                            </span>
                        @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full font-bold text-xs flex items-center gap-1">
                                <ion-icon name="document-text"></ion-icon> Draft
                            </span>
                        @endif
                    </div>
                </div>

                <form action="{{ route('penilaian.store') }}" method="POST">
                    @csrf
                    
                    @php
                        // Di file penilaian.blade.php
                        $technicalAssessments = $assessments->filter(function($a) {
                            // Tambahkan ?? '' agar jika type null tetap jadi string kosong dan tidak error
                            return !str_contains(strtolower(trim($a->type ?? '')), 'perilaku');
                        });
                    @endphp
                    
                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full text-sm border-collapse bg-white">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-bold text-gray-600 uppercase tracking-wider w-1/3">Kompetensi & Definisi</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-600 uppercase tracking-wider w-24">Target</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-600 uppercase tracking-wider w-24">Aktual</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-600 uppercase tracking-wider w-32">Pilihan Saya</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-600 uppercase tracking-wider">Gap & Bukti Perilaku</th>
                                </tr>
                            </thead>
                            
                            @forelse ($technicalAssessments as $index => $item)
                                <tbody class="border-b border-gray-100 hover:bg-gray-50/50 transition" 
                                       x-data="{ 
                                           selectedLevel: {{ $item->current_level ?? 0 }}, 
                                           idealLevel: {{ $item->ideal_level }},
                                           showDetails: false
                                       }">
                                    
                                    {{-- BARIS UTAMA (INPUT) --}}
                                    <tr>
                                        <td class="px-4 py-5 align-top">
                                            <div class="flex flex-col">
                                                <span class="font-black text-gray-900 text-base leading-tight">{{ $item->competency_name }}</span>
                                                <span class="text-[10px] text-indigo-500 font-bold mt-0.5 tracking-widest">{{ $item->competency_code }}</span>
                                                
                                                {{-- Tombol Lihat Kamus --}}
                                                <button type="button" @click="showDetails = !showDetails" 
                                                        class="mt-3 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 transition uppercase tracking-tighter">
                                                    <ion-icon :name="showDetails ? 'chevron-up-circle' : 'book'" class="text-base"></ion-icon>
                                                    <span x-text="showDetails ? 'Tutup Panduan' : 'Lihat Panduan Level (1-5)'"></span>
                                                </button>
                                            </div>
                                        </td>

                                        <td class="px-4 py-5 text-center align-top pt-6">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-50 font-black text-indigo-700 border border-indigo-100 shadow-sm">
                                                {{ $item->ideal_level }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-5 text-center align-top pt-6">
                                            <span class="font-black text-lg {{ ($item->current_level ?? 0) >= $item->ideal_level ? 'text-green-600' : 'text-red-400 opacity-50' }}">
                                                {{ $item->current_level > 0 ? $item->current_level : '-' }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-5 text-center align-top pt-5">
                                            @if ($globalStatus == 'draft' || $globalStatus == 'not_started' || $globalStatus == 'rejected')
                                                <select name="competencies[{{ $item->id }}]" x-model="selectedLevel" 
                                                        class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full text-center font-black text-indigo-700 py-2">
                                                    <option value="">Pilih</option>
                                                    @foreach(range(1, 5) as $lvl)
                                                        <option value="{{ $lvl }}">{{ $lvl }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" value="{{ $item->current_level }}" disabled 
                                                       class="w-full text-center border-gray-100 bg-gray-50 rounded-lg font-black text-gray-400 py-2">
                                            @endif
                                        </td>

                                        <td class="px-4 py-5 align-top">
                                            <div class="flex flex-col gap-3">
                                                {{-- Indikator GAP Real-time --}}
                                                <div x-show="selectedLevel > 0" class="flex items-center gap-2">
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase">Gap:</span>
                                                    <span class="px-2 py-0.5 rounded text-[11px] font-black shadow-sm"
                                                          :class="{
                                                              'bg-green-500 text-white': selectedLevel == idealLevel,
                                                              'bg-blue-500 text-white': selectedLevel > idealLevel,
                                                              'bg-red-500 text-white': selectedLevel < idealLevel
                                                          }"
                                                          x-text="selectedLevel - idealLevel > 0 ? '+' + (selectedLevel - idealLevel) : (selectedLevel - idealLevel)">
                                                    </span>
                                                </div>

                                                {{-- KOTAK BUKTI (Wajib jika Nilai > Target) --}}
                                                <div>
                                                    <div x-show="parseInt(selectedLevel) > parseInt(idealLevel)" 
                                                         x-transition class="mb-2">
                                                        <label class="block text-[10px] font-black text-blue-700 uppercase mb-1">
                                                            ⚠️ Alasan/Bukti (Wajib):
                                                        </label>
                                                    </div>
                                                    <textarea name="evidence[{{ $item->id }}]" 
                                                              rows="2" 
                                                              class="w-full rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs bg-gray-50 placeholder-gray-400"
                                                              placeholder="Ceritakan bukti atau pengalaman Anda di level ini..."
                                                              @if($globalStatus !== 'draft' && $globalStatus !== 'rejected' && $globalStatus !== 'not_started') disabled @endif
                                                    >{{ old('evidence.'.$item->id, $item->evidence ?? '') }}</textarea>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- BARIS PANDUAN LEVEL (ACCORDION) --}}
                                    <tr x-show="showDetails" x-cloak class="bg-indigo-50/30 border-t border-indigo-100">
                                        <td colspan="5" class="px-6 py-6">
                                            <div class="max-w-4xl">
                                                <div class="flex items-center gap-2 mb-4">
                                                    <ion-icon name="library" class="text-indigo-600 text-lg"></ion-icon>
                                                    <h4 class="text-xs font-black uppercase text-indigo-700 tracking-widest">Kamus Perilaku: {{ $item->competency_name }}</h4>
                                                </div>
                                                
                                                <div class="grid grid-cols-1 gap-3">
                                                    @php $behaviors = $item->key_behaviors ?? []; @endphp

                                                    @forelse($behaviors as $behavior)
                                                        <div class="flex items-start gap-4 p-4 rounded-xl border transition-all duration-300 shadow-sm"
                                                             :class="selectedLevel == {{ $behavior->level }} ? 'bg-white border-indigo-400 ring-2 ring-indigo-100' : 'bg-white/50 border-gray-100 opacity-60'">
                                                            
                                                            <div class="flex-shrink-0 text-center">
                                                                <span class="px-3 py-1 text-[11px] font-black uppercase rounded-lg border block"
                                                                      :class="selectedLevel == {{ $behavior->level }} ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-100 text-gray-400 border-gray-200'">
                                                                    Level {{ $behavior->level }}
                                                                </span>
                                                                @if($behavior->level == $item->ideal_level)
                                                                    <div class="mt-2 text-[9px] font-black text-green-600 uppercase bg-green-50 rounded border border-green-100 py-0.5">Target</div>
                                                                @endif
                                                            </div>
                                                            
                                                            <div class="text-sm text-gray-700 leading-relaxed font-medium">
                                                                {!! nl2br(preg_replace('/(\d+\.\s)/', '<br>$1', e($behavior->behavior))) !!}
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p class="text-xs text-gray-400 italic font-medium">Data panduan perilaku belum tersedia.</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            @empty
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="px-6 py-20 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                                    <ion-icon name="construct-outline" class="text-4xl text-gray-300"></ion-icon>
                                                </div>
                                                <p class="font-bold text-gray-500">Belum ada kompetensi teknis yang ditetapkan.</p>
                                                <p class="text-xs text-gray-400 mt-1">Profil kompetensi teknis posisi Anda mungkin masih dalam proses verifikasi.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            @endforelse
                        </table>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="mt-10 flex justify-end gap-3 pt-8 border-t border-gray-100">
                        @if($globalStatus === 'verified' || $globalStatus === 'pending_verification')
                            <button type="button" disabled class="px-8 py-3 bg-gray-100 text-gray-400 rounded-xl cursor-not-allowed font-black uppercase text-xs flex items-center gap-2 border border-gray-200">
                                <ion-icon name="lock-closed" class="text-lg"></ion-icon>
                                {{ $globalStatus === 'verified' ? 'Dokumen Terkunci (Selesai)' : 'Sedang Diverifikasi' }}
                            </button>
                        @else
                            <button type="submit" class="px-8 py-3 text-xs font-black uppercase text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition transform hover:-translate-y-1 flex items-center gap-2">
                                <ion-icon name="send" class="text-lg"></ion-icon> Simpan & Ajukan Verifikasi
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>