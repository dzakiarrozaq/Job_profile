<x-supervisor-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
            Review Pengajuan Pelatihan
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Tombol Kembali --}}
        <a href="{{ route('supervisor.dashboard') }}" class="inline-flex items-center mb-6 text-gray-500 hover:text-indigo-600 transition">
            <ion-icon name="arrow-back-outline" class="mr-1"></ion-icon> Kembali ke Dashboard
        </a>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            
            {{-- HEADER: INFO USER --}}
            <div class="p-6 bg-indigo-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex flex-col sm:flex-row items-center gap-6">
                <img class="h-20 w-20 rounded-full object-cover ring-4 ring-white dark:ring-gray-600" 
                     src="{{ $user->profile_photo_path ? asset('storage/'.$user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" 
                     alt="{{ $user->name }}">
                
                <div class="text-center sm:text-left">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">{{ $user->position->title ?? 'Staff' }}</p>
                    <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-bold border border-yellow-200">
                        <ion-icon name="time-outline" class="mr-1"></ion-icon>
                        Menunggu Review Anda
                    </div>
                </div>

                {{-- Opsi Aksi Massal (Opsional, dipindah ke kanan atas biar rapi) --}}
                <div class="ml-auto mt-4 sm:mt-0 flex gap-2">
                    <form action="{{ route('supervisor.approve.user', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menyetujui SEMUA item sekaligus?');">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 shadow flex items-center gap-1">
                            <ion-icon name="checkmark-done-circle-outline"></ion-icon>
                            Approve All
                        </button>
                    </form>
                </div>
            </div>

            {{-- BODY: DAFTAR ITEM PELATIHAN --}}
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-bold text-gray-800 dark:text-gray-200 text-lg flex items-center gap-2">
                        <ion-icon name="list-outline" class="text-indigo-600"></ion-icon>
                        Daftar Item Pelatihan
                    </h4>
                </div>

                <div class="border rounded-lg overflow-hidden dark:border-gray-600">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-3/12">Pelatihan</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-2/12">Detail</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-4/12">Deskripsi</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase w-3/12">Keputusan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach($plans as $plan)
                                @foreach($plan->items as $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition group">
                                        
                                        {{-- KOLOM 1: JUDUL --}}
                                        <td class="px-6 py-4 align-top">
                                            <div class="text-sm font-bold text-indigo-700 dark:text-indigo-400 mb-1">
                                                {{ $item->title ?? ($item->training->title ?? '-') }}
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1">
                                                <ion-icon name="calendar-outline" class="align-middle"></ion-icon> 
                                                Diajukan: {{ $plan->created_at->format('d M Y') }}
                                            </div>
                                        </td>

                                        {{-- KOLOM 2: PROVIDER --}}
                                        <td class="px-6 py-4 align-top">
                                            <div class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                                                {{ $item->provider ?? '-' }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ $item->method ?? '-' }}
                                            </div>
                                        </td>

                                        {{-- KOLOM 3: DESKRIPSI (VERSI RAPI & SMOOTH) --}}
                                        <td class="px-6 py-4 align-top text-sm text-gray-600 dark:text-gray-300">
                                            @php
                                                // Ambil data dan hapus enter/spasi kosong di awal-akhir
                                                $rawDesc = $item->description ?? $item->objective ?? $item->training->description ?? 'Tidak ada deskripsi.';
                                                $desc = trim($rawDesc);
                                                
                                                // Cek apakah teks cukup panjang untuk membutuhkan fitur 'Expand'
                                                $isLong = strlen($desc) > 120;
                                            @endphp

                                            <div x-data="{ expanded: false }" class="max-w-xs md:max-w-md lg:max-w-lg">
                                                {{-- Teks Utama --}}
                                                <div 
                                                    class="whitespace-pre-line leading-relaxed transition-all duration-300 overflow-hidden"
                                                    :class="expanded ? '' : 'line-clamp-3'"
                                                    style="text-align: left;"
                                                >{{ $desc }}</div>

                                                @if($isLong)
                                                    {{-- Tombol Toggle yang Lebih Elegan --}}
                                                    <button 
                                                        @click="expanded = !expanded" 
                                                        class="mt-2 text-indigo-600 hover:text-indigo-800 text-xs font-extrabold flex items-center gap-1 focus:outline-none group transition"
                                                    >
                                                        <span x-text="expanded ? 'Sembunyikan' : 'Lihat Selengkapnya'"></span>
                                                        <div class="transform transition-transform duration-300" :class="expanded ? 'rotate-180' : ''">
                                                            <ion-icon name="chevron-down-outline" class="text-sm"></ion-icon>
                                                        </div>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- KOLOM 4: AKSI PER ITEM --}}
                                        <td class="px-6 py-4 align-top text-center">
                                            <div class="flex justify-center gap-2">
                                                
                                                {{-- Tombol Setuju Per Item --}}
                                                <form action="{{ route('supervisor.approve.item', $item->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="p-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition tooltip-container"
                                                            title="Setujui Item Ini">
                                                        <ion-icon name="checkmark-outline" class="text-xl font-bold"></ion-icon>
                                                    </button>
                                                </form>

                                                {{-- Tombol Tolak Per Item --}}
                                                <button type="button" 
                                                        onclick="rejectItem(this, '{{ route('supervisor.reject.item', $item->id) }}')"
                                                        class="p-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition"
                                                        title="Tolak Item Ini">
                                                    <ion-icon name="close-outline" class="text-xl font-bold"></ion-icon>
                                                </button>

                                            </div>
                                        </td>

                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- FOOTER: SUMMARY --}}
            <div class="p-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 text-right text-sm text-gray-500">
                Total {{ $plans->flatMap->items->count() }} item perlu direview.
            </div>
        </div>
    </div>

    {{-- Script untuk Modal Reject Per Item --}}
    <script>
        function rejectItem(btn, url) {
            const reason = prompt("Masukkan alasan penolakan untuk ITEM ini:");
            if (reason === null) return; // Cancel
            
            if (reason.trim() === "") {
                alert("Alasan penolakan wajib diisi!");
                return;
            }

            // Buat form dinamis untuk submit rejection
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const inputCsrf = document.createElement('input');
            inputCsrf.type = 'hidden';
            inputCsrf.name = '_token';
            inputCsrf.value = csrfToken;
            
            const inputReason = document.createElement('input');
            inputReason.type = 'hidden';
            inputReason.name = 'reason';
            inputReason.value = reason;

            form.appendChild(inputCsrf);
            form.appendChild(inputReason);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</x-supervisor-layout>