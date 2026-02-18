<x-supervisor-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
            <ion-icon name="create-outline" class="text-2xl text-indigo-600"></ion-icon>
            {{ __('Review IDP Bawahan') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- TOMBOL KEMBALI --}}
            <a href="{{ route('supervisor.persetujuan') }}" class="inline-flex items-center text-gray-500 hover:text-indigo-600 transition font-medium group mb-2">
                <ion-icon name="arrow-back-outline" class="mr-1 group-hover:-translate-x-1 transition-transform"></ion-icon> Kembali ke Daftar Persetujuan
            </a>

            {{-- 1. INFO KARYAWAN CARD --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden relative">
                {{-- Watermark Icon --}}
                <div class="absolute top-0 right-0 p-4 opacity-5">
                    <ion-icon name="id-card-outline" class="text-9xl text-indigo-900"></ion-icon>
                </div>

                {{-- Status Bar --}}
                <div class="bg-gradient-to-r from-gray-900 to-gray-800 px-8 py-5 flex justify-between items-center relative z-10">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/10 p-2 rounded-lg">
                            <ion-icon name="calendar-outline" class="text-white text-xl"></ion-icon>
                        </div>
                        <h3 class="text-lg font-bold text-white tracking-wide">PERIODE IDP: {{ $idp->year }}</h3>
                    </div>
                    <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm
                        {{ $idp->status == 'approved' ? 'bg-green-500 text-white' : ($idp->status == 'rejected' ? 'bg-red-500 text-white' : 'bg-yellow-500 text-white') }}">
                        Status: {{ ucfirst($idp->status) }}
                    </span>
                </div>

                {{-- Detail Karyawan --}}
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-400 shadow-sm border border-blue-100 dark:border-blue-800">
                            <ion-icon name="person" class="text-3xl"></ion-icon>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Nama Karyawan</label>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $idp->user->name }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-5 md:border-l md:border-gray-100 dark:border-gray-700 md:pl-8">
                        <div class="w-16 h-16 bg-indigo-50 dark:bg-indigo-900/20 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-sm border border-indigo-100 dark:border-indigo-800">
                            <ion-icon name="briefcase" class="text-3xl"></ion-icon>
                        </div>
                        <div class="w-full">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Posisi Saat Ini</label>
                                    <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $idp->user->position->title ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FORM WRAPPER UNTUK ACTION --}}
            <form action="{{ route('supervisor.idp.update', $idp->id) }}" method="POST">
                @csrf
                
                {{-- 2. DEVELOPMENT PLAN SECTION --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
                    <div class="px-8 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 flex items-center gap-4">
                        <div class="bg-indigo-600 text-white w-10 h-10 flex items-center justify-center rounded-xl shadow-indigo-200 shadow-md font-bold text-lg">1</div>
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white text-lg">Development Plan & Progress</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Rencana pengembangan kompetensi bawahan Anda.</p>
                        </div>
                    </div>

                    {{-- Tabel Header (Desktop) --}}
                    <div class="hidden md:grid grid-cols-12 bg-gray-900 text-gray-300 font-bold text-xs uppercase tracking-wider border-b border-gray-800">
                        <div class="col-span-4 p-4 border-r border-gray-800">Sasaran & Kategori</div>
                        <div class="col-span-8 p-4 text-center">Aktivitas & Realisasi</div>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($idp->details as $index => $detail)
                            <div class="grid grid-cols-1 md:grid-cols-12 bg-white dark:bg-gray-800 hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                
                                {{-- KOLOM KIRI (GOALS) --}}
                                <div class="md:col-span-4 p-6 md:border-r border-gray-100 dark:border-gray-700 space-y-4">
                                    <div class="md:hidden font-bold text-gray-800 dark:text-white mb-2 border-b pb-2">Sasaran Ke-{{ $index + 1 }}</div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Sasaran Pengembangan</label>
                                        <p class="text-sm text-gray-900 dark:text-white font-medium leading-relaxed">{{ $detail->development_goal }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Kategori</label>
                                        <span class="inline-block px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-semibold rounded border border-gray-200 dark:border-gray-600">
                                            {{ $detail->dev_category ?? '-' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- KOLOM KANAN (ACTIVITIES) --}}
                                <div class="md:col-span-8 bg-white dark:bg-gray-800 p-0">
                                    {{-- Sub-Header Mobile --}}
                                    <div class="grid grid-cols-12 bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 text-[10px] font-bold uppercase py-2 px-4 border-b border-gray-100 dark:border-gray-700">
                                        <div class="col-span-7">Aktivitas</div>
                                        <div class="col-span-5 border-l border-gray-200 dark:border-gray-700 pl-4">Update Progres</div>
                                    </div>

                                    <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
                                        @if(is_array($detail->activities) && count($detail->activities) > 0)
                                            @foreach($detail->activities as $i => $act)
                                                <div class="grid grid-cols-12 items-start p-4 hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                                                    
                                                    {{-- Aktivitas --}}
                                                    <div class="col-span-7 pr-4 flex gap-3">
                                                        <span class="text-gray-400 font-bold text-xs mt-0.5 bg-white dark:bg-gray-700 w-5 h-5 flex items-center justify-center rounded-full border dark:border-gray-600 shadow-sm flex-shrink-0">
                                                            {{ $i + 1 }}
                                                        </span>
                                                        <div>
                                                            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $act['desc'] ?? '-' }}</p>
                                                            @if(!empty($act['date']))
                                                                <div class="mt-1.5 inline-flex items-center gap-1 text-[10px] text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                                                                    <ion-icon name="calendar-outline"></ion-icon>
                                                                    Target: {{ $act['date'] }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Progres --}}
                                                    <div class="col-span-5 border-l border-gray-100 dark:border-gray-700 pl-4">
                                                        @if(!empty($act['progress']))
                                                            <div class="text-xs text-green-700 dark:text-green-300 bg-green-50 dark:bg-green-900/20 p-2.5 rounded border border-green-100 dark:border-green-800">
                                                                <strong class="block mb-1 text-green-800 dark:text-green-400 flex items-center gap-1">
                                                                    <ion-icon name="trending-up-outline"></ion-icon> Update:
                                                                </strong>
                                                                {{ $act['progress'] }}
                                                            </div>
                                                        @else
                                                            <span class="text-xs text-gray-400 italic flex items-center gap-1">
                                                                <ion-icon name="remove-circle-outline"></ion-icon> Belum ada update
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="p-4 text-sm text-gray-400 italic text-center">Tidak ada aktivitas detail.</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center">
                                <ion-icon name="document-outline" class="text-6xl text-gray-200 mb-4"></ion-icon>
                                <p class="text-gray-500 italic">Belum ada rencana pengembangan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- 3. CAREER ASPIRATION SECTION --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
                    <div class="px-8 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 flex items-center gap-4">
                        <div class="bg-indigo-600 text-white w-10 h-10 flex items-center justify-center rounded-xl shadow-indigo-200 shadow-md font-bold text-lg">2</div>
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white text-lg">Career Aspiration</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Rencana karir masa depan bawahan Anda.</p>
                        </div>
                    </div>

                    <div class="p-8 space-y-10">
                        {{-- Layout Grid untuk A dan B --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                            
                            {{-- BAGIAN A --}}
                            <div>
                                <h5 class="font-bold text-gray-800 dark:text-white mb-4 border-l-4 border-indigo-500 pl-3 flex items-center gap-2">
                                    A. Job Family yang Sama
                                </h5>
                                <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden shadow-sm">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 font-bold text-xs uppercase">
                                            <tr>
                                                <th class="p-3 w-10 text-center border-r dark:border-gray-600">#</th>
                                                <th class="p-3 border-r dark:border-gray-600 text-left">Minat Karir</th>
                                                <th class="p-3 text-left">Target Jabatan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-600">
                                            @if(isset($idp->career_aspirations['a']) && count($idp->career_aspirations['a']) > 0)
                                                @foreach($idp->career_aspirations['a'] as $index => $item)
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-600/30">
                                                        <td class="p-3 text-center text-gray-400 border-r dark:border-gray-600">{{ $index + 1 }}</td>
                                                        <td class="p-3 border-r dark:border-gray-600 text-gray-800 dark:text-white">{{ $item['career_interest'] ?? '-' }}</td>
                                                        <td class="p-3 text-gray-800 dark:text-white">{{ $item['future_job_interest'] ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr><td colspan="3" class="p-4 text-center text-gray-400 italic text-xs">Tidak ada data</td></tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- BAGIAN B --}}
                            <div>
                                <h5 class="font-bold text-gray-800 dark:text-white mb-4 border-l-4 border-pink-500 pl-3 flex items-center gap-2">
                                    B. Job Family yang Berbeda
                                </h5>
                                <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden shadow-sm">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 font-bold text-xs uppercase">
                                            <tr>
                                                <th class="p-3 w-10 text-center border-r dark:border-gray-600">#</th>
                                                <th class="p-3 border-r dark:border-gray-600 text-left">Minat Karir</th>
                                                <th class="p-3 text-left">Target Jabatan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-600">
                                            @if(isset($idp->career_aspirations['b']) && count($idp->career_aspirations['b']) > 0)
                                                @foreach($idp->career_aspirations['b'] as $index => $item)
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-600/30">
                                                        <td class="p-3 text-center text-gray-400 border-r dark:border-gray-600">{{ $index + 1 }}</td>
                                                        <td class="p-3 border-r dark:border-gray-600 text-gray-800 dark:text-white">{{ $item['career_interest'] ?? '-' }}</td>
                                                        <td class="p-3 text-gray-800 dark:text-white">{{ $item['future_job_interest'] ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr><td colspan="3" class="p-4 text-center text-gray-400 italic text-xs">Tidak ada data</td></tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- 4. ACTION BAR (STICKY) --}}
                <div class="sticky bottom-4 z-50">
                    <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-md border border-gray-200 dark:border-gray-700 p-4 rounded-xl shadow-2xl flex flex-col md:flex-row justify-between items-center gap-4 max-w-7xl mx-auto">
                        
                        {{-- Input Catatan (Wajib jika reject) --}}
                        <div class="w-full md:w-2/3">
                            <label class="sr-only">Catatan Supervisor</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <ion-icon name="chatbox-ellipses-outline" class="text-gray-400"></ion-icon>
                                </div>
                                <input type="text" name="rejection_note" 
                                    class="block w-full pl-10 pr-3 py-2.5 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm shadow-sm" 
                                    placeholder="Tulis catatan atau alasan penolakan di sini...">
                            </div>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex gap-3 w-full md:w-auto">
                            <button type="submit" name="action" value="reject" 
                                onclick="return confirm('Yakin ingin menolak/meminta revisi? Pastikan Anda sudah mengisi catatan.')"
                                class="flex-1 md:flex-none justify-center px-5 py-2.5 bg-white dark:bg-gray-700 border border-red-300 dark:border-red-500 text-red-600 dark:text-red-400 font-bold rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors shadow-sm flex items-center gap-2">
                                <ion-icon name="close-circle-outline" class="text-xl"></ion-icon> Revisi
                            </button>
                            
                            <button type="submit" name="action" value="approve" 
                                onclick="return confirm('Yakin ingin menyetujui IDP ini?')"
                                class="flex-1 md:flex-none justify-center px-6 py-2.5 bg-gradient-to-r from-green-600 to-green-500 text-white font-bold rounded-lg hover:from-green-700 hover:to-green-600 transition-all shadow-lg hover:shadow-green-500/30 flex items-center gap-2">
                                <ion-icon name="checkmark-done-circle" class="text-xl"></ion-icon> Setujui
                            </button>
                        </div>
                    </div>
                </div>

            </form>

        </div>
    </div>
</x-supervisor-layout>