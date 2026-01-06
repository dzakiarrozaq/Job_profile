<x-lp-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Detail Verifikasi Training
        </h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
            
            <div class="bg-indigo-600 px-6 py-4">
                <div class="flex justify-between items-center text-white">
                    <div>
                        <h3 class="text-lg font-bold">{{ $plan->user->name }}</h3>
                        <p class="text-indigo-200 text-sm">{{ $plan->user->position->name ?? 'Posisi Tidak Diketahui' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="bg-indigo-500 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                            Status: {{ $plan->status }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <ion-icon name="checkmark-circle" class="text-yellow-400 text-xl"></ion-icon>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Disetujui oleh Supervisor <strong>{{ $plan->user->manager->name ?? 'Atasan' }}</strong>
                                pada {{ $plan->updated_at->format('d F Y, H:i') }}.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Detail Item --}}
                @foreach($plan->items as $item)
                    <div class="mb-6 border-b pb-6 dark:border-gray-700">
                        <h4 class="text-xl font-bold text-gray-800 dark:text-white mb-2">{{ $item->title }}</h4>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm mt-4">
                            <div>
                                <span class="text-gray-500 block">Provider</span>
                                <span class="font-medium dark:text-gray-200">{{ $item->provider ?? 'Internal' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Metode</span>
                                <span class="font-medium dark:text-gray-200">{{ $item->method }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Estimasi Biaya</span>
                                <span class="font-medium dark:text-gray-200">Rp {{ number_format($item->cost ?? 0) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Jadwal</span>
                                <span class="font-medium dark:text-gray-200">
                                    {{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('d M Y') : '-' }} 
                                    s/d 
                                    {{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('d M Y') : '-' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <span class="text-gray-500 block mb-1">Objective / Tujuan:</span>
                            <p class="text-gray-600 dark:text-gray-300 italic bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                "{{ $item->objective ?? 'Tidak ada keterangan.' }}"
                            </p>
                        </div>
                    </div>
                @endforeach

                {{-- Action Buttons --}}
                <div class="flex justify-between items-center mt-8 pt-4">
                    <a href="{{ route('lp.persetujuan') }}" class="text-gray-500 hover:text-gray-700 font-medium">
                        &larr; Kembali
                    </a>

                    <div class="flex gap-3">
                        {{-- Form Reject --}}
                        <form action="{{ route('lp.persetujuan.reject', $plan->id) }}" method="POST" 
                              onsubmit="return confirm('Yakin ingin menolak pengajuan ini?');">
                            @csrf
                            <button type="submit" class="px-5 py-2 border border-red-500 text-red-600 rounded-lg hover:bg-red-50 font-bold transition">
                                Tolak
                            </button>
                        </form>

                        {{-- Form Approve --}}
                        <form action="{{ route('lp.persetujuan.approve', $plan->id) }}" method="POST"
                              onsubmit="return confirm('Verifikasi rencana ini? Status akan berubah menjadi Verified.');">
                            @csrf
                            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-lg font-bold transition">
                                Verifikasi & Setujui
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-lp-layout>