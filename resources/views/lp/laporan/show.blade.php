<x-lp-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Detail Laporan Pengajuan
        </h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
            
            <div class="bg-gray-100 dark:bg-gray-700 px-6 py-4 flex justify-between items-center">
                <div>
                    {{-- PERBAIKAN: Gunakan Null Safe Operator --}}
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $plan->user->name ?? 'User Terhapus' }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $plan->user?->position?->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider 
                        {{ $plan->status == 'approved' ? 'bg-green-200 text-green-800' : 
                          ($plan->status == 'rejected' ? 'bg-red-200 text-red-800' : 'bg-yellow-200 text-yellow-800') }}">
                        {{ $plan->status }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <div>
                        <span class="block text-gray-500 dark:text-gray-400">Tanggal Pengajuan:</span>
                        <span class="font-semibold text-gray-800 dark:text-white">{{ $plan->created_at->format('d F Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-500 dark:text-gray-400">Supervisor Approval:</span>
                        <span class="font-semibold text-gray-800 dark:text-white">
                            {{ $plan->supervisor_approved_at ? \Carbon\Carbon::parse($plan->supervisor_approved_at)->format('d M Y') : '-' }}
                        </span>
                    </div>
                </div>

                <hr class="mb-6 dark:border-gray-700">

                @foreach($plan->items as $item)
                    <div class="mb-6 pb-6 border-b last:border-0 dark:border-gray-700">
                        {{-- PERBAIKAN: Cek title dari item atau relasi training --}}
                        <h4 class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mb-3">
                            {{ $item->title ?? ($item->training->title ?? 'Pelatihan Tanpa Judul') }}
                        </h4>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 block">Provider</span>
                                <span class="font-medium dark:text-gray-200">
                                    {{ $item->provider ?? ($item->training->provider ?? 'Internal') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Metode</span>
                                <span class="font-medium dark:text-gray-200">
                                    {{ $item->method ?? ($item->training->method ?? '-') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Biaya</span>
                                <span class="font-bold text-gray-800 dark:text-white">
                                    Rp {{ number_format($item->cost ?? ($item->training->cost ?? 0)) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Jenis</span>
                                <span class="font-medium dark:text-gray-200">{{ $item->training_id ? 'Katalog' : 'Custom' }}</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <span class="text-gray-500 block text-xs uppercase font-bold mb-1">Tujuan / Justifikasi:</span>
                            <p class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded text-gray-700 dark:text-gray-300 text-sm italic">
                                "{{ $item->justification ?? ($item->objective ?? 'Tidak ada keterangan detail.') }}"
                            </p>
                        </div>
                    </div>
                @endforeach

                <div class="mt-6 flex justify-end">
                    <a href="{{ route('lp.laporan.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-2 rounded-lg font-medium transition">
                        &larr; Kembali ke Laporan
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-lp-layout>