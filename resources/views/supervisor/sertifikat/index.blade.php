<x-supervisor-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 dark:text-white">Verifikasi Sertifikat Tim</h2>
    </x-slot>

    <div class="py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
            
            @if($items->isEmpty())
                <div class="p-12 text-center">
                    <ion-icon name="checkmark-circle-outline" class="text-6xl text-green-100 mb-4"></ion-icon>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Semua Bersih!</h3>
                    <p class="text-gray-500">Tidak ada sertifikat baru yang perlu diverifikasi saat ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 uppercase font-semibold">
                            <tr>
                                <th class="px-6 py-4">Karyawan</th>
                                <th class="px-6 py-4">Detail Pelatihan</th>
                                <th class="px-6 py-4">Bukti</th>
                                <th class="px-6 py-4 text-right">Keputusan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($items as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900 dark:text-white">{{ $item->plan->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->plan->user->position->title ?? 'Staff' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $item->title }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs border border-blue-100">{{ $item->provider }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ asset('storage/' . $item->certificate_path) }}" target="_blank" 
                                       class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium p-2 hover:bg-indigo-50 rounded-lg transition">
                                        <ion-icon name="document-attach-outline" class="text-xl"></ion-icon>
                                        Lihat File
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Tombol Tolak --}}
                                        <form action="{{ route('supervisor.sertifikat.reject', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Yakin tolak sertifikat ini?')" 
                                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Tolak / Minta Revisi">
                                                <ion-icon name="close-circle-outline" class="text-2xl"></ion-icon>
                                            </button>
                                        </form>

                                        {{-- Tombol Terima --}}
                                        <form action="{{ route('supervisor.sertifikat.approve', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Validasi sertifikat ini? Status akan menjadi Completed.')" 
                                                    class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-bold shadow-md transition transform hover:-translate-y-0.5">
                                                <ion-icon name="checkmark-done-outline" class="text-lg"></ion-icon>
                                                Validasi
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>
</x-supervisor-layout>