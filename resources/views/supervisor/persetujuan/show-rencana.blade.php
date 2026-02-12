<x-supervisor-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
            Review Usulan Pelatihan
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Tombol Kembali --}}
        <a href="{{ route('supervisor.persetujuan') }}" class="inline-flex items-center mb-6 text-gray-500 hover:text-indigo-600 transition">
            <ion-icon name="arrow-back-outline" class="mr-1"></ion-icon> Kembali ke Daftar
        </a>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
            
            {{-- HEADER: PROFIL PENGAJU --}}
            <div class="p-6 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex flex-col md:flex-row items-center md:items-start gap-6">
                {{-- Foto Profil --}}
                <img class="h-20 w-20 rounded-full object-cover ring-4 ring-white dark:ring-gray-600 shadow-sm" 
                     src="{{ $plan->user->profile_photo_path ? asset('storage/'.$plan->user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($plan->user->name).'&color=7F9CF5&background=EBF4FF' }}" 
                     alt="{{ $plan->user->name }}">
                
                <div class="text-center md:text-left">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $plan->user->name }}</h3>
                    <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 mb-1">
                        {{ $plan->user->position->title ?? 'Posisi Belum Diatur' }}
                    </p>
                    <div class="flex items-center justify-center md:justify-start gap-4 text-xs text-gray-500 dark:text-gray-400 mt-2">
                        <span class="flex items-center gap-1">
                            <ion-icon name="calendar-outline"></ion-icon> 
                            Diajukan: {{ $plan->created_at->format('d F Y') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <ion-icon name="layers-outline"></ion-icon> 
                            Total: {{ $plan->items->count() }} Pelatihan
                        </span>
                    </div>
                </div>
            </div>

            {{-- BODY: DAFTAR ITEM PELATIHAN --}}
            <div class="p-6">
                <h4 class="font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                    <ion-icon name="list-circle-outline" class="text-xl text-indigo-600"></ion-icon>
                    Daftar Pelatihan yang Diajukan
                </h4>
                
                <div class="space-y-4">
                    @foreach($plan->items as $item)
                        <div class="border rounded-lg p-5 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition relative group">
                            <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                                
                                <div class="flex-1">
                                    {{-- Judul Pelatihan --}}
                                    <h5 class="font-bold text-indigo-700 dark:text-indigo-400 text-lg mb-1">
                                        {{ $item->title ?? ($item->training->title ?? 'Judul Tidak Tersedia') }}
                                    </h5>
                                    
                                    {{-- Detail Grid --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-y-2 gap-x-6 mt-3 text-sm text-gray-600 dark:text-gray-300">
                                        <div class="flex items-center gap-2">
                                            <ion-icon name="business-outline" class="text-gray-400"></ion-icon>
                                            <span class="font-medium">Provider:</span> {{ $item->provider ?? '-' }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <ion-icon name="laptop-outline" class="text-gray-400"></ion-icon>
                                            <span class="font-medium">Metode:</span> {{ $item->method ?? 'Offline' }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <ion-icon name="cash-outline" class="text-gray-400"></ion-icon>
                                            <span class="font-medium">Biaya:</span> Rp {{ number_format($item->cost, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Total Biaya (Opsional) --}}
                <div class="mt-6 p-4 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg flex justify-between items-center border border-indigo-100 dark:border-indigo-800">
                    <span class="font-bold text-indigo-800 dark:text-indigo-300">Total Estimasi Biaya</span>
                    <span class="font-bold text-xl text-indigo-900 dark:text-white">
                        Rp {{ number_format($plan->items->sum('cost'), 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- FOOTER: TOMBOL AKSI --}}
            <div class="p-6 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-end gap-3">
                
                {{-- Form Reject --}}
                {{-- Pastikan route ini sesuai dengan web.php Anda (misal: supervisor.reject) --}}
                <form action="{{ route('supervisor.reject', $plan->id) }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <button type="button" onclick="confirmReject(this)" class="w-full sm:w-auto px-6 py-3 bg-white dark:bg-gray-800 border border-red-300 dark:border-red-500 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 font-bold shadow-sm transition flex items-center justify-center gap-2">
                        <ion-icon name="close-circle-outline" class="text-xl"></ion-icon> Tolak Pengajuan
                    </button>
                </form>

                {{-- Form Approve --}}
                {{-- Pastikan route ini sesuai dengan web.php Anda (misal: supervisor.approve) --}}
                <form action="{{ route('supervisor.approve', $plan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin menyetujui rencana pelatihan ini?');" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-lg transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <ion-icon name="checkmark-circle-outline" class="text-xl"></ion-icon> Setujui Pengajuan
                    </button>
                </form>

            </div>
        </div>
    </div>

    {{-- Script untuk Reject dengan Alasan --}}
    <script>
        function confirmReject(btn) {
            const reason = prompt("Masukkan alasan penolakan:");
            if (reason === null) return; // User cancel
            if (reason.trim() === "") {
                alert("Alasan penolakan wajib diisi!");
                return;
            }

            // Buat input hidden untuk reason
            const form = btn.closest('form');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'reason';
            input.value = reason;
            form.appendChild(input);
            
            form.submit();
        }
    </script>
</x-supervisor-layout>