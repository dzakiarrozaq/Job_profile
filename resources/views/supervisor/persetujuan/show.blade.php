<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Detail Pengajuan Pelatihan
        </h2>
    </x-slot>

    <div class="py-12 max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8">
            
            {{-- Header: Info Karyawan --}}
            <div class="flex items-center mb-8 border-b pb-6 dark:border-gray-700">
                <img class="h-16 w-16 rounded-full object-cover mr-4" 
                     src="{{ $plan->user->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($plan->user->name) }}" 
                     alt="Foto">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $plan->user->name }}</h3>
                    <p class="text-sm text-gray-500">Mengajukan pada: {{ $plan->created_at->format('d M Y, H:i') }}</p>
                    <span class="inline-block mt-2 px-3 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800">
                        Status: {{ ucfirst($plan->status) }}
                    </span>
                </div>
            </div>

            {{-- Detail Item Pelatihan --}}
            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Item Pelatihan</h4>
            
            @foreach($plan->items as $item)
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 mb-4 border border-gray-100 dark:border-gray-600">
                    <h5 class="text-xl font-bold text-indigo-600 mb-2">{{ $item->title ?? 'Judul Tidak Tersedia' }}</h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700 dark:text-gray-300">
                        <p><strong>Provider:</strong> {{ $item->provider ?? '-' }}</p>
                        <p><strong>Metode:</strong> {{ $item->method ?? '-' }}</p>
                        <p><strong>Biaya:</strong> Rp {{ number_format($item->cost ?? 0) }}</p>
                        <p><strong>Jadwal:</strong> 
                           {{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('d M Y') : 'Belum ditentukan' }} 
                           s/d 
                           {{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('d M Y') : '-' }}
                        </p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <p class="font-semibold mb-1">Tujuan / Objective:</p>
                        <p class="italic text-gray-600 dark:text-gray-400">"{{ $item->objective ?? 'Tidak ada keterangan tujuan.' }}"</p>
                    </div>
                </div>
            @endforeach

            {{-- Tombol Action --}}
            <div class="flex justify-end gap-4 mt-8 pt-6 border-t dark:border-gray-700">
                <a href="{{ route('supervisor.persetujuan') }}" class="px-5 py-2.5 text-gray-600 hover:text-gray-800 font-medium">
                    Kembali
                </a>

                {{-- Form Tolak --}}
                <form action="{{ route('supervisor.reject', $plan->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menolak pengajuan ini?');">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-red-100 text-red-700 hover:bg-red-200 rounded-lg font-bold transition">
                        Tolak Pengajuan
                    </button>
                </form>

                {{-- Form Setujui --}}
                <form action="{{ route('supervisor.approve', $plan->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menyetujui pengajuan ini?');">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg font-bold shadow-lg transition">
                        Setujui Pengajuan
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>