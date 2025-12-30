<x-supervisor-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Laporan Kompetensi Tim
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <p class="text-gray-600 dark:text-gray-400">Analisis performa dan kesenjangan kompetensi seluruh anggota tim.</p>
            </div>
            <a href="{{ route('supervisor.laporan.export') }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium flex items-center shadow-sm transition">
                <ion-icon name="document-text-outline" class="mr-2 text-lg"></ion-icon>
                Export ke Excel
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                <h3 class="text-sm font-semibold text-gray-500 uppercase">Performa Kompetensi Terbaik</h3>
                @php $best = $teamGaps->sortByDesc('avg_gap')->first(); @endphp
                @if($best)
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $best->user->name }}</p>
                    <p class="text-sm text-green-600">Avg Gap: {{ number_format($best->avg_gap, 1) }}</p>
                @else
                    <p class="text-lg text-gray-400 mt-2">-</p>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-red-500">
                <h3 class="text-sm font-semibold text-gray-500 uppercase">Perlu Perhatian Khusus</h3>
                @php $worst = $teamGaps->sortBy('avg_gap')->first(); @endphp
                @if($worst && $worst->avg_gap < 0)
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $worst->user->name }}</p>
                    <p class="text-sm text-red-600">Avg Gap: {{ number_format($worst->avg_gap, 1) }}</p>
                @else
                    <p class="text-lg text-gray-400 mt-2">Semua Aman</p>
                @endif
            </div>
             <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                <h3 class="text-sm font-semibold text-gray-500 uppercase">Rata-rata Gap Tim</h3>
                @php $avg = $teamGaps->avg('avg_gap'); @endphp
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($avg, 2) }}</p>
                <p class="text-xs text-gray-500">Dari {{ $teamGaps->count() }} Anggota</p>
            </div>
        </div>

        <div class="space-y-8">
            <h3 class="font-bold text-xl text-gray-800 dark:text-gray-100">Detail Kompetensi Anggota Tim</h3>
            @forelse($employees as $employee)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
                    
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <img class="h-16 w-16 rounded-full object-cover" 
                                src="{{ $employee->profile_photo_path ? asset('storage/' . $employee->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($employee->name) }}" 
                                alt="Foto">                            <div>
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ $employee->name }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-300">{{ $employee->position->title ?? 'Posisi tidak diketahui' }}</p>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <span class="text-xs text-gray-500 uppercase font-semibold">Rata-rata Gap</span>
                            @php $avg = $employee->gapRecords->avg('gap_value'); @endphp
                            <p class="text-lg font-bold {{ $avg < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($avg, 1) }}
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left">
                            <thead class="bg-white border-b border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                                <tr>
                                    <th class="px-6 py-2 font-semibold text-gray-600 dark:text-gray-400">Kompetensi</th>
                                    <th class="px-6 py-2 font-semibold text-gray-600 dark:text-gray-400 text-center">Target</th>
                                    <th class="px-6 py-2 font-semibold text-gray-600 dark:text-gray-400 text-center">Aktual</th>
                                    <th class="px-6 py-2 font-semibold text-gray-600 dark:text-gray-400 text-center">Gap</th>
                                    <th class="px-6 py-2 font-semibold text-gray-600 dark:text-gray-400 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($employee->gapRecords as $gap)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-3">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $gap->competency_name }}</span>
                                        <span class="text-xs text-gray-400 block">{{ $gap->competency_code }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-center">{{ $gap->ideal_level }}</td>
                                    <td class="px-6 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold text-xs">
                                            {{ $gap->current_level }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-center font-bold {{ $gap->gap_value < 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $gap->gap_value > 0 ? '+'.$gap->gap_value : $gap->gap_value }}
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        @if($gap->gap_value < 0)
                                            <span class="px-2 py-1 rounded bg-red-50 text-red-700 text-xs font-semibold border border-red-100">
                                                PERLU PERBAIKAN
                                            </span>
                                        @elseif($gap->gap_value == 0)
                                            <span class="px-2 py-1 rounded bg-green-50 text-green-700 text-xs font-semibold border border-green-100">
                                                SESUAI
                                            </span>
                                        @else
                                            <span class="px-2 py-1 rounded bg-blue-50 text-blue-700 text-xs font-semibold border border-blue-100">
                                                MELEBIHI
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
                    <ion-icon name="folder-open-outline" class="text-4xl text-gray-400 mb-2"></ion-icon>
                    <p class="text-gray-500 dark:text-gray-400">Belum ada data kompetensi anggota tim yang dinilai.</p>
                </div>
            @endforelse
            
            <div class="mt-4">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
</x-supervisor-layout>