<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                Rencana Pelatihan Saya
            </h2>
            
            @if(isset($hasDrafts) && $hasDrafts)
                <form action="{{ route('rencana.submitAll') }}" method="POST" onsubmit="return confirm('Ajukan semua rencana di keranjang ke Supervisor?');">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold shadow-lg transition transform hover:-translate-y-0.5">
                        <ion-icon name="paper-plane-outline" class="text-xl"></ion-icon>
                        Ajukan Semua ke Supervisor
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex justify-between items-center">
                <div>{{ session('success') }}</div>
                <ion-icon name="checkmark-circle" class="text-xl"></ion-icon>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelatihan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($plans as $plan)
                            @php $item = $plan->items->first(); @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $item->title ?? 'Judul Tidak Tersedia' }}</div>
                                    <div class="text-xs text-gray-500">{{ $plan->created_at->format('d M Y') }}</div>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $item->provider ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    @if($plan->status == 'draft')
                                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-gray-200 text-gray-700 border border-gray-300">
                                            <ion-icon name="cart-outline" class="align-middle mr-1"></ion-icon> Draft
                                        </span>
                                    @elseif($plan->status == 'pending_supervisor')
                                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                            Menunggu SPV
                                        </span>
                                    @elseif($plan->status == 'pending_lp')
                                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-orange-100 text-orange-800 border border-orange-200">
                                            Verifikasi LP
                                        </span>
                                    @elseif($plan->status == 'approved')
                                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800 border border-green-200">
                                            Disetujui
                                        </span>
                                    @elseif($plan->status == 'rejected')
                                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800 border border-red-200">
                                            Ditolak
                                        </span>
                                    @elseif($plan->status == 'completed')
                                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-gray-700 text-white border border-gray-600">
                                            Selesai
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-600">
                                            {{ ucfirst($plan->status) }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    @if(in_array($plan->status, ['draft', 'pending_supervisor', 'rejected']))
                                        <form action="{{ route('rencana.destroy', $plan->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus rencana ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md transition" title="Hapus">
                                                <ion-icon name="trash-outline" class="text-lg align-middle"></ion-icon>
                                            </button>
                                        </form>
                                    @elseif($plan->status == 'approved')
                                        @php $item = $plan->items->first(); @endphp
                                        
                                        <div class="flex items-center justify-end gap-2">
                                            
                                            @if($item->certificate_status == 'uploaded' && $item->certificate_path)
                                                <a href="{{ asset('storage/' . $item->certificate_path) }}" target="_blank" 
                                                   class="inline-flex items-center gap-1 px-3 py-1 rounded-md bg-green-100 text-green-700 hover:bg-green-200 transition"
                                                   title="Lihat Sertifikat">
                                                    <ion-icon name="document-text-outline" class="text-lg"></ion-icon>
                                                    <span class="text-xs font-bold hidden sm:inline">Lihat</span>
                                                </a>

                                                <a href="{{ route('rencana.sertifikat', $item->id) }}" 
                                                   class="text-gray-400 hover:text-indigo-600" title="Ganti File">
                                                    <ion-icon name="create-outline" class="text-lg"></ion-icon>
                                                </a>

                                            @else
                                                <a href="{{ route('rencana.sertifikat', $item->id) }}" 
                                                   class="inline-flex items-center gap-1 px-3 py-1 rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition animate-pulse">
                                                    <ion-icon name="cloud-upload-outline" class="text-lg"></ion-icon>
                                                    <span class="text-xs font-bold">Upload</span>
                                                </a>
                                            @endif
                                        </div>

                                    @elseif($plan->status == 'completed')
                                        @php $item = $plan->items->first(); @endphp
                                        @if($item->certificate_path)
                                            <a href="{{ asset('storage/' . $item->certificate_path) }}" target="_blank" 
                                               class="inline-flex items-center gap-1 px-3 py-1 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                                                <ion-icon name="ribbon-outline" class="text-lg"></ion-icon>
                                                <span class="text-xs font-bold">Sertifikat</span>
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400 italic">No File</span>
                                        @endif

                                    @else
                                        <span class="text-xs text-gray-400 italic">Terkunci</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                    Belum ada rencana pelatihan. <a href="{{ route('katalog') }}" class="text-indigo-600 hover:underline">Cari Pelatihan</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t dark:border-gray-700">
                {{ $plans->links() }}
            </div>
        </div>
    </div>
</x-app-layout>