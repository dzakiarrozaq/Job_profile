<x-lp-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Laporan Realisasi Pelatihan
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('lp.laporan.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="pending_supervisor" {{ $status == 'pending_supervisor' ? 'selected' : '' }}>Pending Supervisor</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition w-full">
                        Filter
                    </button>
                    <button type="submit" 
                            formaction="{{ route('lp.laporan.export') }}" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center justify-center">
                        <ion-icon name="download-outline" class="text-xl"></ion-icon>
                    </button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Pengajuan</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $summary['total_pengajuan'] }}</h3>
            </div>
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                <p class="text-sm text-gray-500 dark:text-gray-400">Disetujui</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $summary['total_disetujui'] }}</h3>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-purple-500">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Estimasi Biaya</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Rp {{ number_format($summary['total_biaya'] / 1000000, 1) }} Juta
                </h3>
                <p class="text-xs text-gray-400">Rp {{ number_format($summary['total_biaya']) }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-yellow-500">
                <p class="text-sm text-gray-500 dark:text-gray-400">Menunggu Proses</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $summary['total_pending'] }}</h3>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Pelatihan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider & Biaya</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pengajuan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($trainings as $plan)
                            @php
                                $item = $plan->items->first();
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="ml-0">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{-- PERBAIKAN: Gunakan ?? untuk mencegah error null --}}
                                                {{ $plan->user->name ?? 'User Terhapus' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{-- PERBAIKAN: Cek user dan position --}}
                                                {{ $plan->user?->position?->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white font-bold">
                                        {{-- PERBAIKAN: Cek item dan title --}}
                                        {{ $item->title ?? ($item->training->title ?? '-') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{-- PERBAIKAN: Cek method --}}
                                        Metode: {{ $item->method ?? ($item->training->method ?? '-') }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $item->provider ?? ($item->training->provider ?? 'Internal') }}
                                    </div>
                                    <div class="text-xs text-green-600 font-bold">
                                        Rp {{ number_format($item->cost ?? ($item->training->cost ?? 0)) }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $plan->created_at->format('d M Y') }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusStyles = [
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'pending_supervisor' => 'bg-yellow-100 text-yellow-800',
                                            'pending_lp' => 'bg-orange-100 text-orange-800',
                                            'completed' => 'bg-blue-100 text-blue-800',
                                        ];
                                        $statusLabels = [
                                            'approved' => 'Disetujui',
                                            'rejected' => 'Ditolak',
                                            'pending_supervisor' => 'Menunggu SPV',
                                            'pending_lp' => 'Verifikasi LP',
                                            'completed' => 'Selesai',
                                        ];
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusStyles[$plan->status] ?? 'bg-gray-100' }}">
                                        {{ $statusLabels[$plan->status] ?? ucfirst($plan->status) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('lp.laporan.show', $plan->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md transition">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    Tidak ada data laporan pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t dark:border-gray-700">
                {{ $trainings->links() }}
            </div>
        </div>
    </div>
</x-lp-layout>