<x-supervisor-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                <ion-icon name="create-outline" class="text-2xl"></ion-icon>
                {{ __('Review IDP Bawahan') }}
            </h2>
            <a href="{{ route('supervisor.persetujuan') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition flex items-center gap-2">
                <ion-icon name="arrow-back"></ion-icon> Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- 1. HEADER INFORMASI KARYAWAN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-800 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white tracking-wide">PERIODE IDP: {{ $idp->year }}</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider 
                        {{ $idp->status == 'approved' ? 'bg-green-500 text-white' : ($idp->status == 'rejected' ? 'bg-red-500 text-white' : 'bg-yellow-500 text-white') }}">
                        Status: {{ ucfirst($idp->status) }}
                    </span>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-50 rounded-full text-blue-600">
                            <ion-icon name="person" class="text-2xl"></ion-icon>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Nama Karyawan</label>
                            <div class="text-lg font-bold text-gray-900">{{ $idp->user->name }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 md:border-l md:border-gray-200 md:pl-6">
                        <div class="p-3 bg-indigo-50 rounded-full text-indigo-600">
                            <ion-icon name="briefcase" class="text-2xl"></ion-icon>
                        </div>
                        <div class="w-full">
                            <div class="flex justify-between">
                                <div>
                                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Posisi Saat Ini</label>
                                    <div class="text-lg font-bold text-gray-900">{{ $idp->user->position->title ?? '-' }}</div>
                                </div>
                                <div class="text-right">
                                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Target Posisi Suksesi</label>
                                    <div class="text-sm font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded inline-block">
                                        {{ $idp->successor_position ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. DEVELOPMENT PLAN & PROGRESS --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h4 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                        <span class="bg-gray-800 text-white w-6 h-6 flex items-center justify-center rounded-full text-xs">1</span>
                        Development Plan & Progress
                    </h4>
                </div>

                {{-- Tabel Header --}}
                <div class="hidden md:grid grid-cols-12 bg-gray-800 text-white font-bold text-xs uppercase tracking-wider">
                    <div class="col-span-4 p-3 border-r border-gray-700">Development Goals & Category</div>
                    <div class="col-span-8 p-3 text-center">Activities & Realization</div>
                </div>

                <div class="divide-y divide-gray-200">
                    @forelse($idp->details as $index => $detail)
                        <div class="grid grid-cols-1 md:grid-cols-12 bg-white hover:bg-gray-50/50 transition">
                            
                            {{-- KOLOM KIRI (GOALS) --}}
                            <div class="md:col-span-4 p-5 md:border-r border-gray-200 space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Sasaran Pengembangan</label>
                                    <p class="text-sm text-gray-900 font-medium leading-relaxed">{{ $detail->development_goal }}</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Kategori / Judul Project</label>
                                    <span class="inline-block px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded border border-gray-300">
                                        {{ $detail->dev_category ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            {{-- KOLOM KANAN (ACTIVITIES & PROGRESS) --}}
                            <div class="md:col-span-8 bg-white p-0">
                                {{-- Sub-Header --}}
                                <div class="grid grid-cols-12 bg-gray-100 text-gray-500 text-[10px] font-bold uppercase py-2 px-3 border-b border-gray-200">
                                    <div class="col-span-7">Aktivitas</div>
                                    <div class="col-span-5 border-l border-gray-200 pl-3">Realisasi / Catatan Progres</div>
                                </div>

                                <div class="divide-y divide-gray-100">
                                    {{-- LOGIKA LOOP JSON ACTIVITIES --}}
                                    @if(is_array($detail->activities) && count($detail->activities) > 0)
                                        @foreach($detail->activities as $i => $act)
                                            <div class="grid grid-cols-12 items-start p-3 hover:bg-gray-50 transition">
                                                
                                                {{-- Deskripsi Aktivitas --}}
                                                <div class="col-span-7 pr-3 flex gap-3">
                                                    <span class="text-gray-400 font-bold text-xs mt-0.5 bg-white w-5 h-5 flex items-center justify-center rounded-full border shadow-sm flex-shrink-0">
                                                        {{ $i + 1 }}
                                                    </span>
                                                    <div>
                                                        <p class="text-sm text-gray-700 leading-relaxed">{{ $act['desc'] ?? '-' }}</p>
                                                        @if(!empty($act['date']))
                                                            <div class="mt-1 text-[10px] text-gray-400">
                                                                <ion-icon name="calendar-outline" class="align-middle"></ion-icon>
                                                                Target: {{ $act['date'] }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Realisasi / Progres --}}
                                                <div class="col-span-5 border-l border-gray-100 pl-3">
                                                    @if(!empty($act['progress']))
                                                        <div class="text-xs text-green-700 bg-green-50 p-2 rounded border border-green-100">
                                                            <strong class="block mb-1 text-green-800">Update:</strong>
                                                            {{ $act['progress'] }}
                                                        </div>
                                                    @else
                                                        <span class="text-xs text-gray-400 italic">- Belum ada update -</span>
                                                    @endif
                                                </div>

                                            </div>
                                        @endforeach
                                    @else
                                        {{-- Fallback Data Lama (Jika ada) --}}
                                        <div class="p-4 text-sm text-gray-500 italic">
                                            @if($detail->activity)
                                                <p><span class="font-bold">Aktivitas:</span> {{ $detail->activity }}</p>
                                            @else
                                                Tidak ada aktivitas detail.
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="p-12 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                                <ion-icon name="document-outline" class="text-gray-400 text-2xl"></ion-icon>
                            </div>
                            <p class="text-gray-500 italic">Belum ada rencana pengembangan yang dibuat.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- 3. CAREER ASPIRATION --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mt-8">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h4 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                        <span class="bg-gray-800 text-white w-6 h-6 flex items-center justify-center rounded-full text-xs">2</span>
                        Career Aspiration
                    </h4>
                </div>

                <div class="p-8 space-y-12">
                    {{-- BAGIAN A --}}
                    <div class="flex flex-col lg:flex-row gap-8">
                        <div class="flex-1">
                            <h5 class="font-bold text-gray-800 mb-4 border-l-4 border-indigo-500 pl-3">A. Job Family yang Sama</h5>
                            <div class="overflow-hidden rounded-lg border border-gray-300">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-800 text-white text-xs uppercase">
                                        <tr>
                                            <th class="p-3 border-r border-gray-600 w-12 text-center">No</th>
                                            <th class="p-3 border-r border-gray-600 text-left">Career Interest</th>
                                            <th class="p-3 text-left">Future Job Interest</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @if(isset($idp->career_aspirations['a']) && count($idp->career_aspirations['a']) > 0)
                                            @foreach($idp->career_aspirations['a'] as $index => $item)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="p-2 text-center font-bold text-gray-500 border-r">{{ $index + 1 }}</td>
                                                    <td class="p-2 border-r text-gray-800">{{ $item['career_interest'] ?? '-' }}</td>
                                                    <td class="p-2 text-gray-800">{{ $item['future_job_interest'] ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr><td colspan="3" class="p-4 text-center text-gray-400 italic">Tidak ada data</td></tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- BAGIAN B --}}
                    <div class="flex flex-col lg:flex-row gap-8">
                        <div class="flex-1">
                            <h5 class="font-bold text-gray-800 mb-4 border-l-4 border-indigo-500 pl-3">B. Job Family yang Berbeda</h5>
                            <div class="overflow-hidden rounded-lg border border-gray-300">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-800 text-white text-xs uppercase">
                                        <tr>
                                            <th class="p-3 border-r border-gray-600 w-12 text-center">No</th>
                                            <th class="p-3 border-r border-gray-600 text-left">Career Interest</th>
                                            <th class="p-3 text-left">Future Job Interest</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @if(isset($idp->career_aspirations['b']) && count($idp->career_aspirations['b']) > 0)
                                            @foreach($idp->career_aspirations['b'] as $index => $item)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="p-2 text-center font-bold text-gray-500 border-r">{{ $index + 1 }}</td>
                                                    <td class="p-2 border-r text-gray-800">{{ $item['career_interest'] ?? '-' }}</td>
                                                    <td class="p-2 text-gray-800">{{ $item['future_job_interest'] ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr><td colspan="3" class="p-4 text-center text-gray-400 italic">Tidak ada data</td></tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. KEPUTUSAN SUPERVISOR --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-t-4 border-indigo-600 overflow-hidden mt-8">
                <div class="p-8">
                    <h3 class="font-bold text-xl text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <ion-icon name="shield-checkmark" class="text-indigo-600"></ion-icon> Keputusan Supervisor
                    </h3>
                    <p class="text-sm text-gray-500 mb-6">Silakan tinjau kembali data di atas sebelum memberikan keputusan.</p>
                    
                    <form action="{{ route('supervisor.idp.update', $idp->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-6">
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-2">
                                Catatan / Feedback <span class="text-red-500 text-xs">(Wajib jika menolak)</span>
                            </label>
                            <textarea name="rejection_note" 
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition" 
                                rows="3" 
                                placeholder="Berikan masukan untuk karyawan..."></textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="submit" name="action" value="approve" 
                                class="w-full sm:w-auto justify-center inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-lg font-bold text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 shadow-md transition gap-2"
                                onclick="return confirm('Yakin ingin menyetujui IDP ini?')">
                                <ion-icon name="checkmark-circle" class="text-xl"></ion-icon>
                                Setujui IDP
                            </button>

                            <button type="submit" name="action" value="reject" 
                                class="w-full sm:w-auto justify-center inline-flex items-center px-6 py-3 bg-white border-2 border-red-500 rounded-lg font-bold text-red-600 uppercase tracking-widest hover:bg-red-50 focus:outline-none transition gap-2"
                                onclick="return confirm('Yakin ingin menolak/meminta revisi?')">
                                <ion-icon name="close-circle" class="text-xl"></ion-icon>
                                Tolak / Revisi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-supervisor-layout>