<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            <ion-icon name="document-text-outline" class="text-2xl"></ion-icon>
            {{ __('Individual Development Plan (IDP)') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm flex items-center gap-3">
                    <ion-icon name="checkmark-circle" class="text-green-500 text-xl"></ion-icon>
                    <p class="text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            {{-- 1. HEADER INFORMASI KARYAWAN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-800 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white tracking-wide">PERIODE IDP: {{ date('Y') }}</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                        {{ ($idp->status ?? 'draft') == 'draft' ? 'bg-gray-600 text-gray-200' : '' }}
                        {{ ($idp->status ?? '') == 'submitted' ? 'bg-blue-500 text-white' : '' }}
                        {{ ($idp->status ?? '') == 'approved' ? 'bg-green-500 text-white' : '' }}">
                        {{ ucfirst($idp->status ?? 'Draft') }}
                    </span>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-50 rounded-full text-blue-600">
                            <ion-icon name="person" class="text-2xl"></ion-icon>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Nama Karyawan</label>
                            <div class="text-lg font-bold text-gray-900">{{ Auth::user()->name }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 md:border-l md:border-gray-200 md:pl-6">
                        <div class="p-3 bg-indigo-50 rounded-full text-indigo-600">
                            <ion-icon name="briefcase" class="text-2xl"></ion-icon>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Posisi Saat Ini</label>
                            <div class="text-lg font-bold text-gray-900">{{ Auth::user()->position->title ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('idp.store') }}" method="POST" id="idpForm">
                @csrf
                
                {{-- 2. DEVELOPMENT PLAN (TABEL UTAMA) --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                     x-data="{ 
                        rows: {{ $idp && $idp->details->count() > 0 
                            ? $idp->details->map(fn($d) => [
                                'goal' => $d->development_goal, 
                                'category' => $d->dev_category, 
                                'activities' => [[ 'desc' => $d->activity, 'date' => $d->expected_date ]] 
                              ]) 
                            : "[{ goal: '', category: '', activities: [{ desc: '', date: '' }] }]" 
                        }}
                     }">
                    
                    {{-- Judul Section --}}
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h4 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                            <span class="bg-gray-800 text-white w-6 h-6 flex items-center justify-center rounded-full text-xs">1</span>
                            Development Plan
                        </h4>
                        <button type="button" 
                                @click="rows.push({ goal: '', category: '', activities: [{ desc: '', date: '' }] })"
                                class="text-sm font-bold text-blue-600 hover:text-blue-800 hover:bg-blue-50 px-3 py-1.5 rounded transition flex items-center gap-1 border border-transparent hover:border-blue-100">
                            <ion-icon name="add-circle-outline" class="text-lg"></ion-icon> Tambah Sasaran
                        </button>
                    </div>

                    {{-- Tabel Header --}}
                    <div class="hidden md:grid grid-cols-12 bg-gray-800 text-white font-bold text-xs uppercase tracking-wider">
                        <div class="col-span-4 p-3 border-r border-gray-700">Development Goals & Category</div>
                        <div class="col-span-6 p-3 border-r border-gray-700">Development Activities</div>
                        <div class="col-span-2 p-3 text-center">Expected Date</div>
                    </div>

                    {{-- Body --}}
                    <div class="divide-y divide-gray-200">
                        <template x-for="(row, index) in rows" :key="index">
                            <div class="grid grid-cols-1 md:grid-cols-12 bg-white group hover:bg-gray-50/50 transition relative">
                                
                                {{-- KOLOM KIRI (GOALS) --}}
                                <div class="md:col-span-4 p-5 md:border-r border-gray-200 space-y-4">
                                    <div class="md:hidden font-bold text-gray-800 mb-2 border-b pb-1">Sasaran Ke-<span x-text="index+1"></span></div>
                                    
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Sasaran Pengembangan</label>
                                        <textarea :name="`goals[${index}][goal]`" x-model="row.goal" rows="3" 
                                                  class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition" 
                                                  placeholder="Kompetensi apa yang ingin ditingkatkan?"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Kategori / Judul Project</label>
                                        <input type="text" :name="`goals[${index}][category]`" x-model="row.category" 
                                               class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition" 
                                               placeholder="Judul Project...">
                                    </div>
                                    
                                    {{-- Hapus Baris --}}
                                    <button type="button" @click="rows.splice(index, 1)" 
                                            class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1 mt-2 md:mt-0 hover:underline">
                                        <ion-icon name="trash-outline"></ion-icon> Hapus Sasaran Ini
                                    </button>
                                </div>

                                {{-- KOLOM TENGAH & KANAN (ACTIVITIES) --}}
                                <div class="md:col-span-8 bg-gray-50/30">
                                    <div class="divide-y divide-gray-100">
                                        <template x-for="(act, actIndex) in row.activities" :key="actIndex">
                                            <div class="grid grid-cols-1 md:grid-cols-8 items-start p-2">
                                                
                                                {{-- Deskripsi Aktivitas --}}
                                                <div class="md:col-span-6 p-2">
                                                    <div class="flex gap-2">
                                                        <span class="text-gray-400 font-bold text-xs mt-2" x-text="actIndex + 1 + '.'"></span>
                                                        <div class="w-full">
                                                            <label class="md:hidden text-[10px] font-bold text-gray-500 uppercase">Aktivitas</label>
                                                            <textarea :name="`goals[${index}][activities][${actIndex}][desc]`" x-model="act.desc" rows="2" 
                                                                    class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                                                    placeholder="Detail aktivitas..."></textarea>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Tanggal & Hapus --}}
                                                <div class="md:col-span-2 p-2 flex flex-col justify-center">
                                                    <label class="md:hidden text-[10px] font-bold text-gray-500 uppercase mb-1">Target</label>
                                                    <input type="text" :name="`goals[${index}][activities][${actIndex}][date]`" x-model="act.date" 
                                                           class="w-full text-sm border-gray-300 rounded-md text-center shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                                           placeholder="Bulan Thn">
                                                    
                                                    <button type="button" @click="row.activities.splice(actIndex, 1)" x-show="row.activities.length > 1"
                                                            class="text-red-400 hover:text-red-600 text-[10px] mt-2 text-center w-full hover:underline flex justify-center items-center gap-1">
                                                        <ion-icon name="close"></ion-icon> Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    
                                    {{-- Tombol Tambah Activity --}}
                                    <div class="p-3 border-t border-gray-200 text-center">
                                        <button type="button" @click="row.activities.push({ desc: '', date: '' })" 
                                                class="text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 px-3 py-1 rounded transition flex items-center justify-center gap-1 mx-auto">
                                            <ion-icon name="add"></ion-icon> Tambah Aktivitas Lain
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </template>
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
                                            @for ($i = 1; $i <= 5; $i++)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="p-2 text-center font-bold text-gray-500 border-r border-gray-200">{{ $i }}</td>
                                                    <td class="p-2 border-r border-gray-200">
                                                        <input type="text" name="career_interest_a[]" class="w-full text-sm border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="Peminatan...">
                                                    </td>
                                                    <td class="p-2">
                                                        <input type="text" name="future_job_interest_a[]" class="w-full text-sm border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="Jabatan...">
                                                    </td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Keterangan Samping --}}
                            <div class="lg:w-1/3">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-5 shadow-sm">
                                    <h6 class="font-bold text-blue-800 flex items-center gap-2 mb-3 text-sm">
                                        <ion-icon name="information-circle" class="text-lg"></ion-icon> Panduan Pengisian
                                    </h6>
                                    <div class="text-xs text-blue-900 space-y-4 leading-relaxed">
                                        <div>
                                            <strong class="block mb-1 text-blue-700">Career Interest:</strong>
                                            Peminatan karir yang diurutkan (5 prioritas pertama) dapat dipilih berdasarkan nama job function pada struktur organisasi.
                                        </div>
                                        <hr class="border-blue-200">
                                        <div>
                                            <strong class="block mb-1 text-blue-700">Future Job Interest:</strong>
                                            Nama spesifik jabatan yang diminati atau ditargetkan di masa depan.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- BAGIAN B --}}
                        <div>
                            <h5 class="font-bold text-gray-800 mb-4 border-l-4 border-indigo-500 pl-3">B. Job Family yang Berbeda</h5>
                            <div class="lg:w-2/3">
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
                                            @for ($i = 1; $i <= 5; $i++)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="p-2 text-center font-bold text-gray-500 border-r border-gray-200">{{ $i }}</td>
                                                    <td class="p-2 border-r border-gray-200">
                                                        <input type="text" name="career_interest_b[]" class="w-full text-sm border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="Peminatan...">
                                                    </td>
                                                    <td class="p-2">
                                                        <input type="text" name="future_job_interest_b[]" class="w-full text-sm border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="Jabatan...">
                                                    </td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Footer Keterangan --}}
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-xs text-yellow-800 mt-4">
                    <p class="font-bold mb-1 flex items-center gap-1"><ion-icon name="bulb-outline"></ion-icon> Keterangan:</p>
                    <ul class="list-disc pl-5 space-y-1 opacity-90">
                        <li><strong>Development Activities:</strong> Detail aktifitas dalam Project Assignment.</li>
                        <li><strong>Expected Date:</strong> Tanggal selesai pelaksanaan aktivitas (est. total 6 Bulan).</li>
                        <li><strong>Development Progress:</strong> Progress pelaksanaan development (diisi saat review berkala).</li>
                    </ul>
                </div>

                {{-- TOMBOL ACTION --}}
                <div class="sticky bottom-0 bg-white/90 backdrop-blur-sm border-t border-gray-200 p-4 mt-8 -mx-6 sm:-mx-8 px-6 sm:px-8 flex justify-end gap-3 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-20">
                    <button type="submit" name="action" value="draft" 
                            class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg shadow-sm hover:bg-gray-50 hover:text-gray-900 transition flex items-center gap-2">
                        <ion-icon name="save-outline" class="text-lg"></ion-icon> Simpan Draft
                    </button>
                    <button type="submit" name="action" value="submit" 
                            onclick="return confirm('Apakah Anda yakin ingin mengirim IDP ini? Data tidak dapat diubah setelah dikirim.')"
                            class="px-5 py-2.5 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:bg-indigo-700 hover:shadow-lg transition flex items-center gap-2">
                        <ion-icon name="paper-plane" class="text-lg"></ion-icon> Submit IDP
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>