<x-lp-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Verifikasi Rencana Training
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">{{ session('success') }}</div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Daftar Menunggu Verifikasi
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    Berikut adalah pengajuan yang sudah disetujui Supervisor dan menunggu validasi LP.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Pengajuan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disetujui Oleh</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Masuk</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($groupedPlans as $group)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                
                                {{-- KOLOM 1: KARYAWAN --}}
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $group->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $group->user->position->title ?? 'Posisi Tidak Diketahui' }}</div>
                                </td>

                                {{-- KOLOM 2: JUMLAH PENGAJUAN --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                            {{ $group->total_items }} Item Pelatihan
                                        </span>
                                    </div>
                                </td>

                                {{-- KOLOM 3: SUPERVISOR PENYETUJU --}}
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div class="flex items-center gap-1">
                                        <ion-icon name="checkmark-circle-outline" class="text-green-500"></ion-icon>
                                        {{ $group->approver_name }}
                                    </div>
                                </td>

                                {{-- KOLOM 4: TANGGAL --}}
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($group->latest_update)->format('d M Y') }}
                                </td>

                                {{-- KOLOM 5: AKSI --}}
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('lp.persetujuan.review-user', $group->user_id) }}" 
                                       class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 shadow transition">
                                        Review Semua
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <ion-icon name="checkmark-done-circle-outline" class="text-4xl text-gray-300 mb-2"></ion-icon>
                                        <p>Tidak ada data yang perlu diverifikasi saat ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-lp-layout>