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

    @php
        // Memisahkan Kompetensi berdasarkan tipe
        // Asumsi: tipe 'Perilaku' untuk behavioral, sisanya dianggap Teknis
        $technicalCompetencies = $competencies->filter(fn($c) => strtolower($c->type) !== 'perilaku');
        $behavioralCompetencies = $competencies->filter(fn($c) => strtolower($c->type) === 'perilaku');
    @endphp

    <div class="max-w-7xl mx-auto py-6 space-y-8"> {{-- Tambah space-y-8 untuk jarak antar tabel --}}
        
        <form action="{{ route('supervisor.penilaian.store', $employee->id) }}" method="POST">
            @csrf
            
            {{-- ==================================================== --}}
            {{-- BAGIAN 1: KOMPETENSI TEKNIS --}}
            {{-- ==================================================== --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20">
                    <h3 class="text-lg font-bold text-blue-800 dark:text-blue-300 flex items-center gap-2">
                        <ion-icon name="construct-outline"></ion-icon> 1. Kompetensi Teknis
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kemampuan spesifik (Hard Skill) yang dibutuhkan untuk jabatan ini.</p>
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
                        
                        @forelse($technicalCompetencies as $comp)
                            {{-- Panggil Component Baris Tabel (Reuse logic yang sama) --}}
                            @include('components.supervisor-verification-row', ['comp' => $comp])
                        @empty
                            <tbody>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 italic">Tidak ada kompetensi teknis untuk dinilai.</td>
                                </tr>
                            </tbody>
                        @endforelse
                    </table>
                </div>
            </div>

            {{-- ==================================================== --}}
            {{-- BAGIAN 2: KOMPETENSI PERILAKU --}}
            {{-- ==================================================== --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-orange-50 dark:bg-orange-900/20">
                    <h3 class="text-lg font-bold text-orange-800 dark:text-orange-300 flex items-center gap-2">
                        <ion-icon name="people-outline"></ion-icon> 2. Kompetensi Perilaku
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sikap kerja dan nilai-nilai (Soft Skill) yang diharapkan.</p>
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
                        
                        @forelse($behavioralCompetencies as $comp)
                            {{-- Panggil Component Baris Tabel (Reuse logic yang sama) --}}
                            @include('components.supervisor-verification-row', ['comp' => $comp])
                        @empty
                            <tbody>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 italic">Tidak ada kompetensi perilaku untuk dinilai.</td>
                                </tr>
                            </tbody>
                        @endforelse
                    </table>
                </div>
            </div>

            {{-- ==================================================== --}}
            {{-- TOMBOL AKSI (FOOTER) --}}
            {{-- ==================================================== --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 flex justify-between items-center">
                
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

        </form>
    </div>
</x-supervisor-layout>

{

@push('scripts')
{{-- Tidak butuh script tambahan, hanya catatan --}}
@endpush