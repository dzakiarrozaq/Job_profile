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
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-indigo-500">
                <h3 class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider mb-4">Cakupan Job Profile</h3>
                <div class="flex items-end justify-between mb-2">
                    <div>
                        <span class="text-4xl font-bold text-gray-900 dark:text-white">{{ $coveredPositions }}</span>
                        <span class="text-gray-500 dark:text-gray-400 text-sm">/ {{ $totalPositions }} Posisi</span>
                    </div>
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold text-xl">{{ number_format($coverageRatio, 0) }}%</span>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-4">
                    <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-1000" style="width: {{ $coverageRatio }}%"></div>
                </div>

                <div class="flex gap-4 text-xs mt-4">
                    <div class="flex items-center">
                        <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                        <span class="text-gray-600 dark:text-gray-300">Verified: <strong>{{ $coveredPositions }}</strong></span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 rounded-full bg-yellow-500 mr-2"></span>
                        <span class="text-gray-600 dark:text-gray-300">Draft/Pending: <strong>{{ $draftProfiles }}</strong></span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>
                        <span class="text-gray-600 dark:text-gray-300">Kosong: <strong>{{ $totalPositions - $coveredPositions - $draftProfiles }}</strong></span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <h3 class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider mb-4">Distribusi Pengguna</h3>
                <div class="space-y-3">
                    @foreach($usersByRole as $stat)
                        <div class="flex justify-between items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded transition">
                            <div class="flex items-center">
                                <div class="p-1.5 rounded-md bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 mr-3">
                                    <ion-icon name="people-outline"></ion-icon>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $stat->name }}</span>
                            </div>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $stat->total }}</span>
                        </div>
                    @endforeach
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-2 mt-2 flex justify-between items-center">
                        <span class="text-sm text-gray-500">Total Akun Aktif</span>
                        <span class="text-sm font-bold text-green-600">{{ $activeUsers }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 flex justify-between items-center">
                <h3 class="font-bold text-gray-800 dark:text-gray-100">Kepatuhan Penilaian Kompetensi per Departemen</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">Diurutkan berdasarkan tingkat penyelesaian</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300 uppercase">Departemen</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-600 dark:text-gray-300 uppercase">Total Karyawan</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300 uppercase w-1/3">Tingkat Penyelesaian</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($departments as $dept)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $dept->name }}
                            </td>
                            <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-400">
                                {{ $dept->users_count }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-indigo-600 bg-indigo-200 mr-3">
                                        {{ $dept->completion_rate }}%
                                    </span>
                                    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-600">
                                        <div class="bg-{{ $dept->completion_rate >= 80 ? 'green' : ($dept->completion_rate >= 50 ? 'yellow' : 'red') }}-500 h-2 rounded-full" 
                                             style="width: {{ $dept->completion_rate }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($dept->completion_rate >= 80)
                                    <span class="text-green-600 font-bold text-xs">SANGAT BAIK</span>
                                @elseif($dept->completion_rate >= 50)
                                    <span class="text-yellow-600 font-bold text-xs">CUKUP</span>
                                @else
                                    <span class="text-red-600 font-bold text-xs">PERLU PERHATIAN</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-admin-layout>