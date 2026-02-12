<x-lp-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
            Review Verifikasi Akhir
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Tombol Kembali --}}
        <a href="{{ route('lp.persetujuan.index') }}" class="inline-flex items-center mb-6 text-gray-500 hover:text-indigo-600 transition">
            <ion-icon name="arrow-back-outline" class="mr-1"></ion-icon> Kembali ke Daftar
        </a>

        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            
            {{-- HEADER: INFO USER --}}
            <div class="p-6 bg-indigo-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex items-center gap-6">
                <img class="h-16 w-16 rounded-full object-cover ring-4 ring-white dark:ring-gray-600" 
                     src="{{ $user->profile_photo_path ? asset('storage/'.$user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" 
                     alt="{{ $user->name }}">
                
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">{{ $user->position->title ?? 'Posisi Tidak Diketahui' }}</p>
                </div>
            </div>

            {{-- BODY: LOOPING RENCANA PELATIHAN --}}
            <div class="p-6 space-y-8">
                
                @foreach($plans as $plan)
                    <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-600 shadow-sm">
                        
                        {{-- Info Approval Supervisor --}}
                        <div class="mb-4 flex items-center gap-2 text-sm text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 p-3 rounded-lg border border-green-100 dark:border-green-800">
                            <ion-icon name="checkmark-circle"></ion-icon>
                            <span>
                                Disetujui Supervisor <strong>{{ $plan->approver->name ?? 'N/A' }}</strong> 
                                pada {{ $plan->supervisor_approved_at ? $plan->supervisor_approved_at->format('d M Y') : '-' }}
                            </span>
                        </div>

                        {{-- Tabel Item Pelatihan --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600 bg-white dark:bg-gray-800 rounded-md overflow-hidden">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/4">Judul Pelatihan</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">Provider</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">Metode</th>
                                        {{-- KOLOM DESKRIPSI (Lebar disesuaikan) --}}
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/3">Deskripsi / Tujuan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                    @foreach($plan->items as $item)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                            
                                            {{-- Judul --}}
                                            <td class="px-4 py-3 align-top">
                                                <div class="font-bold text-indigo-700 dark:text-indigo-400">
                                                    {{ $item->title ?? ($item->training->title ?? '-') }}
                                                </div>
                                            </td>

                                            {{-- Provider --}}
                                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 align-top">
                                                {{ $item->provider ?? 'Internal' }}
                                            </td>

                                            {{-- Metode --}}
                                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 align-top">
                                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">
                                                    {{ $item->method ?? '-' }}
                                                </span>
                                            </td>

                                            {{-- Deskripsi (DENGAN TRIM UNTUK MENGHILANGKAN GAP ATAS) --}}
                                            <td class="px-4 pt-1 pb-3 text-sm text-gray-600 dark:text-gray-300 align-top">
                                                @php
                                                    // Ambil teks dan langsung bersihkan spasi/enter di awal & akhir kalimat
                                                    $rawDesc = $item->description ?? $item->objective ?? $item->training->description ?? 'Tidak ada keterangan.';
                                                    $desc = trim($rawDesc);
                                                    
                                                    $limit = 150; 
                                                    $isLong = strlen($desc) > $limit;
                                                @endphp

                                                <div x-data="{ expanded: false }" class="min-w-[250px] mt-0">
                                                    {{-- whitespace-pre-line akan mempertahankan enter di TENGAH teks, tapi trim() sudah menghapus enter di AWAL --}}
                                                    <div class="whitespace-pre-line leading-tight mt-0 pt-0 text-justify">
                                                        @if($isLong)
                                                            <span x-show="!expanded">
                                                                {{ \Illuminate\Support\Str::limit($desc, $limit) }}
                                                            </span>
                                                            <span x-show="expanded" style="display: none;">
                                                                {{ $desc }}
                                                            </span>
                                                        @else
                                                            {{ $desc }}
                                                        @endif
                                                    </div>

                                                    @if($isLong)
                                                        <button @click="expanded = !expanded" 
                                                                class="text-indigo-600 hover:text-indigo-800 text-xs font-bold mt-1 flex items-center gap-1 focus:outline-none transition">
                                                            <span x-show="!expanded" class="flex items-center">
                                                                Lihat Selengkapnya <ion-icon name="chevron-down-outline" class="ml-1"></ion-icon>
                                                            </span>
                                                            <span x-show="expanded" class="flex items-center">
                                                                Sembunyikan <ion-icon name="chevron-up-outline" class="ml-1"></ion-icon>
                                                            </span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Footer Aksi Per Plan --}}
                        <div class="mt-4 flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 -mx-4 -mb-4 px-4 pb-4 rounded-b-lg">
                            
                            {{-- Form Tolak --}}
                            <form action="{{ route('lp.persetujuan.reject', $plan->id) }}" method="POST">
                                @csrf
                                <button type="button" onclick="rejectPlan(this)" class="px-4 py-2 bg-white border border-red-300 text-red-600 rounded-lg hover:bg-red-50 text-sm font-bold transition shadow-sm">
                                    Tolak
                                </button>
                            </form>

                            {{-- Form Setuju --}}
                            <form action="{{ route('lp.persetujuan.approve', $plan->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-bold shadow transition flex items-center gap-2">
                                    <ion-icon name="checkmark-done-circle"></ion-icon> Verifikasi & Setujui
                                </button>
                            </form>
                        </div>

                    </div>
                @endforeach

            </div>
        </div>
    </div>

    {{-- Script untuk Reject dengan Alasan --}}
    <script>
        function rejectPlan(btn) {
            const reason = prompt("Masukkan alasan penolakan:");
            if (reason === null) return; // Jika klik Cancel
            if (reason.trim() === "") {
                alert("Alasan wajib diisi untuk penolakan!");
                return;
            }
            
            const form = btn.closest('form');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'reason';
            input.value = reason;
            form.appendChild(input);
            form.submit();
        }
    </script>
</x-lp-layout>