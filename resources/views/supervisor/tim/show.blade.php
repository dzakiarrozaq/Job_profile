{{-- File: resources/views/supervisor/tim/show.blade.php --}}
<x-supervisor-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('supervisor.tim.index') }}" class="hover:text-indigo-600 transition">Anggota Tim Saya</a>
            <ion-icon name="chevron-forward"></ion-icon>
            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $employee->name }}</span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ currentTab: 'analisis' }">

        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700 text-center">
                
                <div class="relative inline-block">
                    <img class="h-24 w-24 rounded-full object-cover border-4 border-white dark:border-gray-700 shadow-md mx-auto" 
                        src="{{ $employee->profile_photo_path ? asset('storage/' . $employee->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($employee->name) }}" 
                        alt="Foto">
                    <span class="absolute bottom-1 right-1 h-5 w-5 rounded-full border-2 border-white dark:border-gray-800 bg-green-500"></span>
                </div>
                
                <h2 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">{{ $employee->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $employee->position->title ?? 'Posisi Belum Diatur' }}</p>
                
                <div class="mt-3">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200 uppercase">
                        Karyawan Organik
                    </span>
                </div>

                <div class="mt-6 text-left space-y-3 border-t border-gray-100 dark:border-gray-700 pt-6">
                    <div class="flex items-center gap-3 text-sm">
                        <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-500"><ion-icon name="mail-outline"></ion-icon></div>
                        <span class="text-gray-700 dark:text-gray-300 truncate">{{ $employee->email }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-500"><ion-icon name="id-card-outline"></ion-icon></div>
                        <span class="text-gray-700 dark:text-gray-300">{{ $employee->nik ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-500"><ion-icon name="business-outline"></ion-icon></div>
                        <span class="text-gray-700 dark:text-gray-300">{{ $employee->position->unit->name ?? 'Unit tidak diketahui' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-500"><ion-icon name="calendar-outline"></ion-icon></div>
                        <span class="text-gray-700 dark:text-gray-300">Bergabung: {{ $employee->hiring_date ? \Carbon\Carbon::parse($employee->hiring_date)->format('d M Y') : '-' }}</span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <a href="mailto:{{ $employee->email }}" class="flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <ion-icon name="mail" class="mr-2"></ion-icon> Email
                    </a>
                    <button disabled class="flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-400 cursor-not-allowed">
                        <ion-icon name="chatbubble" class="mr-2"></ion-icon> Chat
                    </button>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex" aria-label="Tabs">
                    <button @click="currentTab = 'analisis'" 
                            :class="currentTab === 'analisis' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                        Analisis Kompetensi
                    </button>
                    <button @click="currentTab = 'rencana'" 
                            :class="currentTab === 'rencana' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                        Rencana Aktif
                        @if($activePlans->count() > 0) <span class="ml-1 bg-red-100 text-red-600 text-xs px-1.5 rounded-full">{{ $activePlans->count() }}</span> @endif
                    </button>
                    <button @click="currentTab = 'riwayat'" 
                            :class="currentTab === 'riwayat' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                        Riwayat Selesai
                    </button>
                </nav>
            </div>

            <div x-show="currentTab === 'analisis'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Peta Gap Kompetensi</h3>
                
                @if($gapRecords->count() > 0)
                    @php
                        /** * SARINGAN GANDA: 
                         * Menggunakan str_contains + strtolower + trim 
                         * supaya tidak "bocor" meskipun di DB tulisannya huruf kecil atau ada spasi.
                         */
                        $behavioralGaps = $gapRecords->filter(function($g) {
                            $tipe = strtolower(trim($g->type ?? ''));
                            return str_contains($tipe, 'perilaku');
                        });

                        $technicalGaps = $gapRecords->filter(function($g) {
                            $tipe = strtolower(trim($g->type ?? ''));
                            return !str_contains($tipe, 'perilaku');
                        });
                    @endphp

                    <div class="space-y-10">
                        {{-- 1. TABEL KOMPETENSI TEKNIS (HARD SKILLS) --}}
                        <div class="space-y-3">
                            <h4 class="flex items-center gap-2 text-sm font-bold text-teal-600 dark:text-teal-400 uppercase tracking-widest">
                                <ion-icon name="construct-outline" class="text-lg"></ion-icon>
                                Kompetensi Teknis (Fungsional)
                            </h4>
                            <div class="overflow-x-auto border rounded-xl border-teal-100 dark:border-gray-700 shadow-sm">
                                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-teal-50/50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-bold text-teal-900 dark:text-gray-300">Kompetensi</th>
                                            <th class="px-4 py-3 text-center font-bold text-teal-900 dark:text-gray-300">Target</th>
                                            <th class="px-4 py-3 text-center font-bold text-teal-900 dark:text-gray-300">Aktual</th>
                                            <th class="px-4 py-3 text-center font-bold text-teal-900 dark:text-gray-300">Gap</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                        @forelse($technicalGaps as $gap)
                                            <tr class="hover:bg-teal-50/30 transition">
                                                <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                                    {{ $gap->competency_name }}
                                                    <div class="text-[10px] text-gray-400 font-normal uppercase tracking-tighter">{{ $gap->competency_code }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-center font-bold text-gray-500">{{ $gap->ideal_level }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-50 dark:bg-gray-700 text-teal-700 dark:text-white text-xs font-black">
                                                        {{ $gap->current_level }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    @if($gap->gap_value < 0)
                                                        <span class="px-2 py-1 rounded-md bg-red-100 text-red-700 font-black text-xs">{{ $gap->gap_value }}</span>
                                                    @elseif($gap->gap_value > 0)
                                                        <span class="px-2 py-1 rounded-md bg-blue-100 text-blue-700 font-black text-xs">+{{ $gap->gap_value }}</span>
                                                    @else
                                                        <span class="px-2 py-1 rounded-md bg-green-100 text-green-700 font-black text-xs">0</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-8 text-center text-gray-400 italic">Data kompetensi teknis belum tersedia.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- 2. TABEL KOMPETENSI PERILAKU (SOFT SKILLS) --}}
                        <div class="space-y-3">
                            <h4 class="flex items-center gap-2 text-sm font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">
                                <ion-icon name="people-circle-outline" class="text-lg"></ion-icon>
                                Kompetensi Perilaku (Matrix)
                            </h4>
                            <div class="overflow-x-auto border rounded-xl border-indigo-100 dark:border-gray-700">
                                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-indigo-50/50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-bold text-indigo-900 dark:text-gray-300">Kompetensi</th>
                                            <th class="px-4 py-3 text-center font-bold text-indigo-900 dark:text-gray-300">Target</th>
                                            <th class="px-4 py-3 text-center font-bold text-indigo-900 dark:text-gray-300">Aktual</th>
                                            <th class="px-4 py-3 text-center font-bold text-indigo-900 dark:text-gray-300">Gap</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                        @forelse($behavioralGaps as $gap)
                                            <tr class="hover:bg-indigo-50/30 transition">
                                                <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                                    {{ $gap->competency_name }}
                                                    <div class="text-[10px] text-gray-400 font-normal uppercase tracking-tighter">{{ $gap->competency_code }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-center font-bold text-gray-500">{{ $gap->ideal_level }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-100 dark:bg-gray-700 text-indigo-700 dark:text-white text-xs font-black">
                                                        {{ $gap->current_level }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    @if($gap->gap_value < 0)
                                                        <span class="px-2 py-1 rounded-md bg-red-100 text-red-700 font-black text-xs">{{ $gap->gap_value }}</span>
                                                    @elseif($gap->gap_value > 0)
                                                        <span class="px-2 py-1 rounded-md bg-blue-100 text-blue-700 font-black text-xs">+{{ $gap->gap_value }}</span>
                                                    @else
                                                        <span class="px-2 py-1 rounded-md bg-green-100 text-green-700 font-black text-xs">0</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-8 text-center text-gray-400 italic">Data kompetensi perilaku belum ditarik/diverifikasi.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        
                    </div>
                @else
                    <div class="text-center py-20 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-dashed border-gray-300">
                        <ion-icon name="analytics-outline" class="text-5xl text-gray-200 mb-2"></ion-icon>
                        <p class="text-gray-500 font-medium">Belum ada data penilaian yang diverifikasi untuk anggota tim ini.</p>
                    </div>
                @endif
            </div>

            <div x-show="currentTab === 'rencana'" class="space-y-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Rencana Pelatihan Aktif</h3>
                </div>

                @forelse($activePlans as $plan)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                @foreach($plan->items as $item)
                                    <h4 class="font-bold text-gray-900 dark:text-white text-md">{{ $item->training->title ?? 'Pelatihan Kustom' }}</h4>
                                @endforeach
                                <p class="text-xs text-gray-500 mt-1">Diajukan: {{ $plan->created_at->format('d M Y') }}</p>
                            </div>
                            
                            @if($plan->status == 'pending_supervisor')
                                <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-xs font-bold uppercase">
                                    Menunggu Persetujuan Anda
                                </span>
                            @elseif($plan->status == 'pending_lp')
                                <span class="px-2 py-1 rounded bg-blue-100 text-blue-800 text-xs font-bold uppercase">
                                    Menunggu Persetujuan LP
                                </span>
                            @elseif($plan->status == 'approved')
                                <span class="px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-bold uppercase">
                                    Sedang Berjalan
                                </span>
                            @endif
                        </div>

                        @if($plan->status == 'pending_supervisor')
                            <div class="flex justify-end gap-3 mt-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                                <a href="{{ route('supervisor.persetujuan') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 font-medium">
                                    Tinjau Rencana
                                </a>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-10 text-center border border-dashed border-gray-300">
                        <p class="text-gray-500">Tidak ada rencana pelatihan yang sedang aktif.</p>
                    </div>
                @endforelse
            </div>

            <div x-show="currentTab === 'riwayat'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Riwayat Pelatihan Selesai</h3>
                <ul class="space-y-4">
                    @forelse($completedHistory as $plan)
                        <li class="flex items-start gap-3 pb-4 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <div class="mt-1 p-1.5 bg-green-100 text-green-600 rounded-full">
                                <ion-icon name="checkmark-done"></ion-icon>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">
                                    {{ $plan->items->first()->training->title ?? 'Pelatihan' }}
                                </h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Diselesaikan pada {{ $plan->updated_at->format('d M Y') }}
                                </p>
                                @if($plan->status == 'rejected')
                                    <span class="text-xs text-red-500 font-medium">Ditolak (Lihat detail)</span>
                                @endif
                            </div>
                        </li>
                    @empty
                        <p class="text-sm text-gray-500 italic">Belum ada riwayat pelatihan yang selesai.</p>
                    @endforelse
                </ul>
            </div>

        </div>

    </div>
</x-supervisor-layout>