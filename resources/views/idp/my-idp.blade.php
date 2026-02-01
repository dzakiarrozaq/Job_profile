<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            <ion-icon name="document-text-outline" class="text-2xl"></ion-icon>
            {{ __('Individual Development Plan (IDP)') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm flex items-center gap-3">
                    <ion-icon name="checkmark-circle" class="text-green-500 text-xl"></ion-icon>
                    <p class="text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            {{-- 1. HEADER --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-800 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white tracking-wide">PERIODE IDP: {{ date('Y') }}</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-gray-600 text-gray-200">
                        {{ ucfirst($idp->status ?? 'Draft') }}
                    </span>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-50 rounded-full text-blue-600"><ion-icon name="person" class="text-2xl"></ion-icon></div>
                        <div><label class="text-xs font-bold text-gray-500 uppercase block">Nama Karyawan</label><div class="text-lg font-bold text-gray-900">{{ Auth::user()->name }}</div></div>
                    </div>
                    <div class="flex items-center gap-4 md:border-l md:border-gray-200 md:pl-6">
                        <div class="p-3 bg-indigo-50 rounded-full text-indigo-600"><ion-icon name="briefcase" class="text-2xl"></ion-icon></div>
                        <div><label class="text-xs font-bold text-gray-500 uppercase block">Posisi Saat Ini</label><div class="text-lg font-bold text-gray-900">{{ Auth::user()->position->title ?? '-' }}</div></div>
                    </div>
                </div>
            </div>

            <form action="{{ route('idp.store') }}" method="POST" id="idpForm">
                @csrf
                
                {{-- 2. DEVELOPMENT PLAN (FIXED X-DATA) --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                     x-data="{ 
                        rows: {{ 
                            $idp && $idp->details->count() > 0 
                            ? Js::from($idp->details->map(fn($d) => [
                                'goal' => $d->development_goal, 
                                'category' => $d->dev_category, 
                                // Pastikan activities selalu array valid
                                'activities' => is_array($d->activities) ? $d->activities : [['desc' => '', 'date' => '', 'progress' => '']]
                              ]))
                            : Js::from([['goal' => '', 'category' => '', 'activities' => [['desc' => '', 'date' => '', 'progress' => '']]]]) 
                        }}
                     }">
                    
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h4 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                            <span class="bg-gray-800 text-white w-6 h-6 flex items-center justify-center rounded-full text-xs">1</span>
                            Development Plan
                        </h4>
                        <button type="button" @click="rows.push({ goal: '', category: '', activities: [{ desc: '', date: '', progress: '' }] })"
                                class="text-sm font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded flex items-center gap-1">
                            <ion-icon name="add-circle-outline" class="text-lg"></ion-icon> Tambah Sasaran
                        </button>
                    </div>

                    <div class="hidden md:grid grid-cols-12 bg-gray-800 text-white font-bold text-xs uppercase tracking-wider">
                        <div class="col-span-4 p-3 border-r border-gray-700">Development Goals & Category</div>
                        <div class="col-span-8 p-3 text-center">Development Activities & Progress</div>
                    </div>

                    <div class="divide-y divide-gray-200">
                        <template x-for="(row, index) in rows" :key="index">
                            <div class="grid grid-cols-1 md:grid-cols-12 bg-white group hover:bg-gray-50/50 transition relative">
                                {{-- KOLOM KIRI --}}
                                <div class="md:col-span-4 p-5 md:border-r border-gray-200 space-y-4">
                                    <div class="md:hidden font-bold text-gray-800 mb-2 border-b pb-1">Sasaran Ke-<span x-text="index+1"></span></div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Sasaran Pengembangan</label>
                                        <textarea :name="`goals[${index}][goal]`" x-model="row.goal" rows="3" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Kategori</label>
                                        <input type="text" :name="`goals[${index}][category]`" x-model="row.category" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                                    </div>
                                    <button type="button" @click="rows.splice(index, 1)" class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1">
                                        <ion-icon name="trash-outline"></ion-icon> Hapus Sasaran
                                    </button>
                                </div>

                                {{-- KOLOM KANAN (ACTIVITIES LOOP) --}}
                                <div class="md:col-span-8 bg-gray-50/30 p-0">
                                    <div class="grid grid-cols-12 bg-gray-100 text-gray-500 text-[10px] font-bold uppercase py-2 px-3 border-b border-gray-200">
                                        <div class="col-span-6">Aktivitas</div>
                                        <div class="col-span-3 text-center">Progres</div>
                                        <div class="col-span-1"></div>
                                    </div>

                                    <div class="divide-y divide-gray-100">
                                        <template x-for="(act, actIndex) in row.activities" :key="actIndex">
                                            <div class="grid grid-cols-1 md:grid-cols-12 items-start p-3 gap-3">
                                                
                                                {{-- Aktivitas (Lebar: 6) --}}
                                                <div class="md:col-span-6 flex gap-2">
                                                    <span class="text-gray-400 font-bold text-xs mt-2" x-text="actIndex + 1 + '.'"></span>
                                                    <textarea 
                                                        :name="`goals[${index}][activities][${actIndex}][desc]`" 
                                                        x-model="act.desc" 
                                                        rows="2" 
                                                        class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                                        placeholder="Aktivitas...">
                                                    </textarea>
                                                </div>

                                                {{-- Progress (Lebar: 5 - Diperlebar agar layout pas) --}}
                                                <div class="md:col-span-5">
                                                    <textarea 
                                                        :name="`goals[${index}][activities][${actIndex}][progress]`" 
                                                        x-model="act.progress" 
                                                        rows="2" 
                                                        class="w-full text-xs border-green-300 bg-green-50 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 placeholder-green-700/50" 
                                                        placeholder="Update progress...">
                                                    </textarea>
                                                </div>

                                                {{-- Tombol Hapus (Lebar: 1) --}}
                                                <div class="md:col-span-1 flex justify-center pt-2">
                                                    <button type="button" 
                                                            @click="row.activities.splice(actIndex, 1)" 
                                                            x-show="row.activities.length > 1" 
                                                            class="text-red-400 hover:text-red-600 text-lg transition"
                                                            title="Hapus Aktivitas">
                                                        <ion-icon name="close-circle"></ion-icon>
                                                    </button>
                                                </div>

                                            </div>
                                        </template>
                                    </div>
                                    
                                    <div class="p-3 border-t border-gray-200 text-center">
                                        <button type="button" @click="row.activities.push({ desc: '', date: '', progress: '' })" class="text-xs font-bold text-indigo-600 hover:bg-indigo-50 px-3 py-1 rounded flex items-center justify-center gap-1 mx-auto">
                                            <ion-icon name="add"></ion-icon> Tambah Aktivitas
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- 3. CAREER ASPIRATION --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mt-8"
                     x-data="{
                        careerA: {{ isset($idp->career_aspirations['a']) ? Js::from($idp->career_aspirations['a']) : Js::from([['career_interest' => '', 'future_job_interest' => '']]) }},
                        careerB: {{ isset($idp->career_aspirations['b']) ? Js::from($idp->career_aspirations['b']) : Js::from([['career_interest' => '', 'future_job_interest' => '']]) }}
                     }">
                    
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h4 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                            <span class="bg-gray-800 text-white w-6 h-6 flex items-center justify-center rounded-full text-xs">2</span>
                            Career Aspiration
                        </h4>
                    </div>

                    <div class="p-8 space-y-12">
                        <div class="flex flex-col lg:flex-row gap-8">
                            <div class="flex-1">
                                <div class="flex justify-between items-center mb-4 border-l-4 border-indigo-500 pl-3">
                                    <h5 class="font-bold text-gray-800">A. Job Family yang Sama</h5>
                                    <button type="button" @click="careerA.push({career_interest: '', future_job_interest: ''})" class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded flex items-center gap-1">
                                        <ion-icon name="add-circle"></ion-icon> Tambah
                                    </button>
                                </div>
                                <div class="overflow-hidden rounded-lg border border-gray-300">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-800 text-white text-xs uppercase">
                                            <tr>
                                                <th class="p-3 w-12 text-center">No</th>
                                                <th class="p-3 text-left">Career Interest</th>
                                                <th class="p-3 text-left">Future Job Interest</th>
                                                <th class="p-3 w-10"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            <template x-for="(row, index) in careerA" :key="index">
                                                <tr>
                                                    <td class="p-2 text-center" x-text="index + 1"></td>
                                                    <td class="p-2"><input type="text" :name="`career_interest_a[${index}]`" x-model="row.career_interest" class="w-full text-sm border-gray-300 rounded"></td>
                                                    <td class="p-2"><input type="text" :name="`future_job_interest_a[${index}]`" x-model="row.future_job_interest" class="w-full text-sm border-gray-300 rounded"></td>
                                                    <td class="p-2 text-center"><button type="button" @click="careerA.splice(index, 1)" class="text-red-500"><ion-icon name="trash-outline"></ion-icon></button></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="lg:w-1/3">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-5 text-xs text-blue-900 space-y-2">
                                    <p><strong>Career Interest:</strong> Peminatan karir prioritas.</p>
                                    <p><strong>Future Job Interest:</strong> Jabatan target spesifik.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-4 border-l-4 border-indigo-500 pl-3 lg:w-2/3">
                                <h5 class="font-bold text-gray-800">B. Job Family yang Berbeda</h5>
                                <button type="button" @click="careerB.push({career_interest: '', future_job_interest: ''})" class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded flex items-center gap-1">
                                    <ion-icon name="add-circle"></ion-icon> Tambah
                                </button>
                            </div>
                            <div class="lg:w-2/3 overflow-hidden rounded-lg border border-gray-300">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-800 text-white text-xs uppercase">
                                        <tr>
                                            <th class="p-3 w-12 text-center">No</th>
                                            <th class="p-3 text-left">Career Interest</th>
                                            <th class="p-3 text-left">Future Job Interest</th>
                                            <th class="p-3 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <template x-for="(row, index) in careerB" :key="index">
                                            <tr>
                                                <td class="p-2 text-center" x-text="index + 1"></td>
                                                <td class="p-2"><input type="text" :name="`career_interest_b[${index}]`" x-model="row.career_interest" class="w-full text-sm border-gray-300 rounded"></td>
                                                <td class="p-2"><input type="text" :name="`future_job_interest_b[${index}]`" x-model="row.future_job_interest" class="w-full text-sm border-gray-300 rounded"></td>
                                                <td class="p-2 text-center"><button type="button" @click="careerB.splice(index, 1)" class="text-red-500"><ion-icon name="trash-outline"></ion-icon></button></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sticky bottom-0 bg-white/90 backdrop-blur-sm border-t border-gray-200 p-4 mt-8 flex justify-end gap-3 shadow-lg z-20">
                    <button type="submit" name="action" value="draft" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-50">Simpan Draft</button>
                    <button type="submit" name="action" value="submit" onclick="return confirm('Kirim IDP?')" class="px-5 py-2.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700">Submit IDP</button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>