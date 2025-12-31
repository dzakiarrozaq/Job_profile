<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                Rencana Pengembangan Saya
            </h2>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6 space-y-6">

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-sm" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Topik Pelatihan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Metode & Vendor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($plans as $plan)
                            {{-- Ambil item pertama saja (Asumsi 1 Rencana = 1 Pelatihan) --}}
                            @php
                                $item = $plan->items->first(); 
                            @endphp

                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    {{-- Tampilkan data dari Item, bukan dari Plan langsung --}}
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $item->title ?? 'Judul Tidak Tersedia' }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1">
                                        {{ $item->objective ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $item->method ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $item->provider ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('d M Y') : '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        s/d {{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('d M Y') : '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClasses = [
                                            'pending_supervisor' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            'approved'           => 'bg-green-100 text-green-800 border-green-200',
                                            'rejected'           => 'bg-red-100 text-red-800 border-red-200',
                                            'completed'          => 'bg-blue-100 text-blue-800 border-blue-200',
                                        ];

                                        $statusLabels = [
                                            'pending_supervisor' => 'Menunggu Supervisor',
                                            'approved'           => 'Disetujui',
                                            'rejected'           => 'Ditolak',
                                            'completed'          => 'Selesai',
                                        ];

                                        $status = $plan->status ?? 'pending_supervisor';
                                    @endphp
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full border {{ $statusClasses[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $statusLabels[$status] ?? ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center justify-end gap-3">
                                        
                                        <a href="{{ route('rencana.show', $plan->id) }}" 
                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-bold">
                                            Detail
                                        </a>

                                        {{-- Tampilkan tombol hapus JIKA status BUKAN 'approved' dan BUKAN 'completed' --}}
                                        @if(!in_array($plan->status, ['approved', 'completed']))

                                            <form action="{{ route('rencana.destroy', $plan->id) }}" method="POST" 
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus rencana ini?');">
                                                @csrf
                                                @method('DELETE')
                                                
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-bold ml-4">
                                                    Hapus
                                                </button>
                                            </form>

                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                        <ion-icon name="document-text-outline" class="text-4xl mb-3 text-gray-300"></ion-icon>
                                        <p class="text-base font-medium">Belum ada rencana pelatihan.</p>
                                        <p class="text-sm mt-1">Klik tombol "Ajukan Rencana Baru" untuk memulai.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $plans->links() }}
            </div>
        </div>
    </div>
</x-app-layout>