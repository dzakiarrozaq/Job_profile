<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Detail Rencana Pelatihan
        </h2>
    </x-slot>

    <div class="py-12 max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8 border border-gray-100 dark:border-gray-700">
            
            <div class="mb-6 flex justify-between items-center">
                <span class="text-sm text-gray-500">Diajukan pada: {{ $plan->created_at->format('d M Y') }}</span>
                
                @php
                    $statusClasses = [
                        'pending_supervisor' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                        'approved' => 'bg-green-100 text-green-800 border-green-200',
                        'rejected' => 'bg-red-100 text-red-800 border-red-200',
                        'completed' => 'bg-blue-100 text-blue-800 border-blue-200',
                    ];
                    $statusLabels = [
                        'pending_supervisor' => 'Menunggu Approval Supervisor',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'completed' => 'Selesai',
                    ];
                    $status = $plan->status;
                @endphp
                <span class="px-3 py-1 rounded-full text-sm font-bold border {{ $statusClasses[$status] ?? 'bg-gray-100' }}">
                    {{ $statusLabels[$status] ?? ucfirst($status) }}
                </span>
            </div>

            @foreach($plan->items as $item)
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ $item->title ?? 'Judul Tidak Tersedia' }}
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-300 mt-4">
                        <div>
                            <p class="font-semibold text-gray-500 text-xs uppercase">Provider</p>
                            <p>{{ $item->provider ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-500 text-xs uppercase">Metode</p>
                            <p>{{ $item->method ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-500 text-xs uppercase">Estimasi Biaya</p>
                            <p>Rp {{ number_format($item->cost ?? 0) }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-500 text-xs uppercase">Jadwal Pelaksanaan</p>
                            <p>
                                {{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('d M Y') : '-' }} 
                                s/d 
                                {{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('d M Y') : '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <p class="font-semibold text-gray-500 text-xs uppercase mb-1">Tujuan / Objective</p>
                        <p class="text-gray-800 dark:text-gray-200 italic">
                            "{{ $item->objective ?? 'Belum diisi' }}"
                        </p>
                    </div>
                </div>
            @endforeach

            <div class="flex justify-start mt-6">
                <a href="{{ route('rencana.index') }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    &larr; Kembali ke Daftar
                </a>
            </div>

        </div>
    </div>
</x-app-layout>