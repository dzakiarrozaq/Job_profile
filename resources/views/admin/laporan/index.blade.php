{{-- File: resources/views/admin/laporan/index.blade.php --}}
<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                Laporan Sistem & Statistik
            </h2>
            <button onclick="window.print()" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-sm font-medium flex items-center shadow-sm transition">
                <ion-icon name="print-outline" class="mr-2 text-lg"></ion-icon>
                Cetak Laporan
            </button>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-8">

        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('admin.laporan.index') }}">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Filter Data Gap Kompetensi</h3>
                <div class="flex flex-col md:flex-row gap-4">
                    
                    <select name="department_id" class="flex-1 rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                        <option value="all">Semua Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>

                    <select name="position_id" class="flex-1 rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                        <option value="all">Semua Jabatan</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" {{ ($filters['position_id'] ?? '') == $pos->id ? 'selected' : '' }}>{{ $pos->title }}</option>
                        @endforeach
                    </select>
                    
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm">
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="font-bold text-gray-800 dark:text-gray-100 mb-6">Kompetensi Paling Kritis (Perusahaan)</h3>
                
                <div class="space-y-6">
                    @foreach($criticalCompetencies as $comp)
                        @php 
                            // Hitung persentase visual (misal gap -2 dianggap 80% penuh bar merahnya)
                            $width = min(abs($comp->avg_gap) * 40, 100); 
                            $color = $comp->avg_gap <= -1.5 ? 'bg-red-600' : 'bg-yellow-500';
                            $textColor = $comp->avg_gap <= -1.5 ? 'text-red-600' : 'text-yellow-600';
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $comp->competency_name }}</span>
                                <span class="font-bold {{ $textColor }}">Gap Rata-rata: {{ number_format($comp->avg_gap, 1) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                                <div class="{{ $color }} h-3 rounded-full" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @endforeach
                    @if($criticalCompetencies->isEmpty())
                        <p class="text-center text-gray-400 text-sm py-4">Belum ada data gap kompetensi.</p>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="font-bold text-gray-800 dark:text-gray-100 mb-6">Pelatihan Paling Banyak Diambil</h3>
                
                <div class="space-y-6">
                    @foreach($popularTrainings as $training)
                         @php 
                            // Hitung persentase visual (misal 32 orang = 100%)
                            $max = $popularTrainings->max('total_participants');
                            $width = ($max > 0) ? ($training->total_participants / $max) * 100 : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ $training->title }}</span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $training->total_participants }} Karyawan</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                                <div class="bg-indigo-500 h-3 rounded-full" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @endforeach
                    @if($popularTrainings->isEmpty())
                        <p class="text-center text-gray-400 text-sm py-4">Belum ada data pelatihan.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4">
                <h3 class="font-bold text-gray-800 dark:text-gray-100">Data Mentah Gap Kompetensi</h3>
                
                <a href="{{ route('admin.laporan.admin.export', request()->query()) }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 flex items-center shadow-sm">
                    <ion-icon name="download-outline" class="mr-2 text-lg"></ion-icon>
                    Export ke Excel
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Departemen</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Kompetensi (Gap Terburuk)</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ideal</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aktual</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Gap</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @forelse($employees as $employee)
                            {{-- Kita hanya ambil satu gap terburuk untuk tampilan ringkas di tabel ini --}}
                            @php $gap = $employee->gapRecords->first(); @endphp
                            
                            @if($gap)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ $employee->name }}
                                    <span class="block text-xs font-normal text-gray-400">{{ $employee->position->title ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $employee->department->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                    {{ $gap->competency_name }}
                                    <span class="text-xs text-gray-400 ml-1">({{ $employee->gapRecords->count() }} kompetensi dinilai)</span>
                                </td>
                                <td class="px-6 py-4 text-center font-medium">{{ $gap->ideal_level }}</td>
                                <td class="px-6 py-4 text-center font-medium">{{ $gap->current_level }}</td>
                                <td class="px-6 py-4 text-center font-bold {{ $gap->gap_value < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $gap->gap_value }}
                                </td>
                            </tr>
                            @endif
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">Tidak ada data yang sesuai filter.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                {{ $employees->links() }}
            </div>
        </div>

    </div>
</x-admin-layout>