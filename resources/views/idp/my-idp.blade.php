<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
            <ion-icon name="document-text-outline" class="text-2xl text-indigo-600"></ion-icon>
            {{ __('Individual Development Plan (IDP)') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded-r shadow-sm flex items-center gap-3 animate-fade-in-down">
                    <ion-icon name="checkmark-circle" class="text-green-500 text-2xl"></ion-icon>
                    <p class="text-green-700 dark:text-green-400 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            {{-- 1. HEADER CARD --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden relative">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <ion-icon name="ribbon-outline" class="text-9xl text-indigo-600"></ion-icon>
                </div>
                
                <div class="bg-gradient-to-r from-gray-900 to-gray-800 px-8 py-5 flex justify-between items-center relative z-10">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/10 p-2 rounded-lg">
                            <ion-icon name="calendar-outline" class="text-white text-xl"></ion-icon>
                        </div>
                        <h3 class="text-lg font-bold text-white tracking-wide">PERIODE IDP: {{ date('Y') }}</h3>
                    </div>
                    <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider 
                        {{ ($idp->status ?? 'Draft') == 'approved' ? 'bg-green-500 text-white' : 'bg-gray-600 text-gray-200' }}">
                        {{ ucfirst($idp->status ?? 'Draft') }}
                    </span>
                </div>
                
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10">
                    <div class="flex items-center gap-5 group">
                        <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform shadow-sm">
                            <ion-icon name="person" class="text-2xl"></ion-icon>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Nama Karyawan</label>
                            <div class="text-xl font-bold text-gray-900 dark:text-white">{{ Auth::user()->name }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-5 md:border-l md:border-gray-100 dark:border-gray-700 md:pl-8 group">
                        <div class="w-14 h-14 bg-indigo-50 dark:bg-indigo-900/20 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform shadow-sm">
                            <ion-icon name="briefcase" class="text-2xl"></ion-icon>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Posisi Saat Ini</label>
                            <div class="text-xl font-bold text-gray-900 dark:text-white">{{ Auth::user()->position->title ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('idp.store') }}" method="POST" id="idpForm" class="space-y-8">
                @csrf
                
                {{-- 2. DEVELOPMENT PLAN SECTION --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden"
                     x-data="{ 
                        rows: {{ 
                            $idp && $idp->details->count() > 0 
                            ? Js::from($idp->details->map(fn($d) => [
                                'goal' => $d->development_goal, 
                                'category' => $d->dev_category, 
                                'activities' => is_array($d->activities) ? $d->activities : [['desc' => '', 'date' => '', 'progress' => '']]
                              ]))
                            : Js::from([['goal' => '', 'category' => '', 'activities' => [['desc' => '', 'date' => '', 'progress' => '']]]]) 
                        }}
                     }">
                    
                    <div class="px-8 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div class="bg-indigo-600 text-white w-10 h-10 flex items-center justify-center rounded-xl shadow-indigo-200 shadow-md font-bold text-lg">1</div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white text-lg">Development Plan</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Rencana pengembangan kompetensi Anda tahun ini.</p>
                            </div>
                        </div>
                        <button type="button" @click="rows.push({ goal: '', category: '', activities: [{ desc: '', date: '', progress: '' }] })"
                                class="text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2.5 rounded-lg flex items-center gap-2 shadow-sm transition-all transform hover:-translate-y-0.5">
                            <ion-icon name="add-circle-outline" class="text-lg"></ion-icon> Tambah Sasaran
                        </button>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="(row, index) in rows" :key="index">
                            <div class="p-6 sm:p-8 hover:bg-gray-50/30 dark:hover:bg-gray-700/20 transition-colors duration-200">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                                    
                                    {{-- KOLOM KIRI (GOALS) --}}
                                    <div class="lg:col-span-4 space-y-5">
                                        <div class="flex items-center justify-between lg:hidden border-b pb-2 mb-2">
                                            <span class="font-bold text-gray-800 dark:text-white">Sasaran Ke-<span x-text="index+1"></span></span>
                                            <button type="button" @click="rows.splice(index, 1)" class="text-red-500"><ion-icon name="trash-outline"></ion-icon></button>
                                        </div>

                                        <div class="bg-white dark:bg-gray-700 p-5 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm relative group">
                                            <div class="absolute -left-1 top-5 w-1 h-8 bg-indigo-500 rounded-r"></div>
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Sasaran Pengembangan</label>
                                                    <textarea :name="`goals[${index}][goal]`" x-model="row.goal" rows="4" 
                                                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow resize-none placeholder-gray-400"
                                                        placeholder="Contoh: Meningkatkan kemampuan Leadership..."></textarea>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Kategori</label>
                                                    <input type="text" :name="`goals[${index}][category]`" x-model="row.category" 
                                                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow"
                                                        placeholder="Contoh: Soft Skill / Technical">
                                                </div>
                                            </div>
                                            
                                            <div class="hidden lg:block mt-4 pt-4 border-t border-gray-100 dark:border-gray-600 text-right">
                                                 <button type="button" @click="rows.splice(index, 1)" class="text-xs font-bold text-red-500 hover:text-red-700 flex items-center gap-1 ml-auto transition-colors">
                                                    <ion-icon name="trash-outline"></ion-icon> Hapus Sasaran Ini
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- KOLOM KANAN (ACTIVITIES) --}}
                                    <div class="lg:col-span-8 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden flex flex-col">
                                        <div class="bg-gray-100 dark:bg-gray-700/50 px-5 py-3 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
                                            <h5 class="font-bold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wider">Aktivitas & Progres</h5>
                                            <span class="bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300 text-[10px] font-bold px-2 py-0.5 rounded" x-text="row.activities.length + ' Item'"></span>
                                        </div>

                                        <div class="p-4 space-y-4 flex-1">
                                            <template x-for="(act, actIndex) in row.activities" :key="actIndex">
                                                <div class="bg-white dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm relative pl-10 group hover:border-indigo-300 dark:hover:border-indigo-500 transition-colors">
                                                    <span class="absolute left-3 top-4 w-5 h-5 bg-gray-100 dark:bg-gray-600 rounded-full flex items-center justify-center text-xs font-bold text-gray-500 dark:text-gray-300" x-text="actIndex + 1"></span>
                                                    
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Deskripsi Aktivitas</label>
                                                            <textarea :name="`goals[${index}][activities][${actIndex}][desc]`" x-model="act.desc" rows="2" 
                                                                class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                                                                placeholder="Apa yang akan dilakukan?"></textarea>
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-green-600/70 dark:text-green-400 uppercase mb-1">Progres Aktual</label>
                                                            <textarea :name="`goals[${index}][activities][${actIndex}][progress]`" x-model="act.progress" rows="2" 
                                                                class="w-full text-xs border-green-200 dark:border-green-800 bg-green-50/50 dark:bg-green-900/10 dark:text-green-100 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500 resize-none placeholder-green-700/30"
                                                                placeholder="Update pencapaian..."></textarea>
                                                        </div>
                                                    </div>

                                                    <button type="button" @click="row.activities.splice(actIndex, 1)" x-show="row.activities.length > 1" 
                                                        class="absolute top-2 right-2 text-gray-300 hover:text-red-500 transition-colors opacity-0 group-hover:opacity-100">
                                                        <ion-icon name="close-circle" class="text-xl"></ion-icon>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="p-3 bg-gray-100 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-600 text-center">
                                            <button type="button" @click="row.activities.push({ desc: '', date: '', progress: '' })" 
                                                class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 flex items-center justify-center gap-1 mx-auto transition-colors">
                                                <ion-icon name="add-circle" class="text-base"></ion-icon> Tambah Aktivitas Lain
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    {{-- Empty State jika tidak ada row --}}
                    <div x-show="rows.length === 0" class="p-12 text-center text-gray-400">
                        <ion-icon name="clipboard-outline" class="text-6xl mb-3 text-gray-300"></ion-icon>
                        <p class="text-lg">Belum ada rencana pengembangan.</p>
                        <button type="button" @click="rows.push({ goal: '', category: '', activities: [{ desc: '', date: '', progress: '' }] })" class="mt-4 text-indigo-600 font-bold hover:underline">Mulai Buat Rencana</button>
                    </div>
                </div>

                {{-- 3. CAREER ASPIRATION SECTION --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden"
                     x-data="{
                        careerA: {{ isset($idp->career_aspirations['a']) ? Js::from($idp->career_aspirations['a']) : Js::from([['career_interest' => '', 'future_job_interest' => '']]) }},
                        careerB: {{ isset($idp->career_aspirations['b']) ? Js::from($idp->career_aspirations['b']) : Js::from([['career_interest' => '', 'future_job_interest' => '']]) }}
                     }">
                    
                    <div class="px-8 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 flex items-center gap-4">
                        <div class="bg-indigo-600 text-white w-10 h-10 flex items-center justify-center rounded-xl shadow-indigo-200 shadow-md font-bold text-lg">2</div>
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white text-lg">Career Aspiration</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Rencana karir masa depan Anda.</p>
                        </div>
                    </div>

                    <div class="p-8 space-y-10">
                        {{-- SECTION A --}}
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <div class="lg:col-span-4">
                                <h5 class="font-bold text-gray-800 dark:text-white mb-2 flex items-center gap-2">
                                    <span class="w-1 h-6 bg-indigo-500 rounded-full"></span>
                                    A. Job Family yang Sama
                                </h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 leading-relaxed">
                                    Aspirasi karir yang masih dalam satu rumpun pekerjaan dengan posisi saat ini.
                                </p>
                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-lg p-4 text-xs text-blue-800 dark:text-blue-200">
                                    <strong>Tips:</strong> Isi jabatan yang setingkat lebih tinggi atau spesialisasi di bidang yang sama.
                                </div>
                            </div>
                            <div class="lg:col-span-8">
                                <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden shadow-sm">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600 text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-bold">
                                                <th class="p-4 w-12 text-center">#</th>
                                                <th class="p-4">Career Interest</th>
                                                <th class="p-4">Future Job Interest</th>
                                                <th class="p-4 w-12"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-600">
                                            <template x-for="(row, index) in careerA" :key="index">
                                                <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                                    <td class="p-4 text-center text-gray-400 font-bold" x-text="index + 1"></td>
                                                    <td class="p-4"><input type="text" :name="`career_interest_a[${index}]`" x-model="row.career_interest" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="Minat..."></td>
                                                    <td class="p-4"><input type="text" :name="`future_job_interest_a[${index}]`" x-model="row.future_job_interest" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="Target Jabatan..."></td>
                                                    <td class="p-4 text-center">
                                                        <button type="button" @click="careerA.splice(index, 1)" class="text-gray-300 hover:text-red-500 transition-colors"><ion-icon name="trash-outline" class="text-lg"></ion-icon></button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                    <div class="p-3 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-600">
                                        <button type="button" @click="careerA.push({career_interest: '', future_job_interest: ''})" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 flex items-center gap-1 transition-colors">
                                            <ion-icon name="add-circle"></ion-icon> Tambah Baris
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100 dark:border-gray-700">

                        {{-- SECTION B --}}
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <div class="lg:col-span-4">
                                <h5 class="font-bold text-gray-800 dark:text-white mb-2 flex items-center gap-2">
                                    <span class="w-1 h-6 bg-pink-500 rounded-full"></span>
                                    B. Job Family yang Berbeda
                                </h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 leading-relaxed">
                                    Aspirasi karir di lintas departemen atau fungsi yang berbeda total.
                                </p>
                            </div>
                            <div class="lg:col-span-8">
                                <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden shadow-sm">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600 text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-bold">
                                                <th class="p-4 w-12 text-center">#</th>
                                                <th class="p-4">Career Interest</th>
                                                <th class="p-4">Future Job Interest</th>
                                                <th class="p-4 w-12"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-600">
                                            <template x-for="(row, index) in careerB" :key="index">
                                                <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                                    <td class="p-4 text-center text-gray-400 font-bold" x-text="index + 1"></td>
                                                    <td class="p-4"><input type="text" :name="`career_interest_b[${index}]`" x-model="row.career_interest" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="Minat..."></td>
                                                    <td class="p-4"><input type="text" :name="`future_job_interest_b[${index}]`" x-model="row.future_job_interest" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="Target Jabatan..."></td>
                                                    <td class="p-4 text-center">
                                                        <button type="button" @click="careerB.splice(index, 1)" class="text-gray-300 hover:text-red-500 transition-colors"><ion-icon name="trash-outline" class="text-lg"></ion-icon></button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                    <div class="p-3 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-600">
                                        <button type="button" @click="careerB.push({career_interest: '', future_job_interest: ''})" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 flex items-center gap-1 transition-colors">
                                            <ion-icon name="add-circle"></ion-icon> Tambah Baris
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER ACTION BAR --}}
                <div class="sticky bottom-4 bg-white/80 dark:bg-gray-800/90 backdrop-blur-md border border-gray-200 dark:border-gray-700 p-4 rounded-xl flex justify-between items-center shadow-2xl z-50 max-w-7xl mx-auto">
                    <div class="text-xs text-gray-500 italic hidden sm:block">
                        * Pastikan semua data terisi sebelum melakukan submit.
                    </div>
                    <div class="flex gap-3 ml-auto">
                        <button type="submit" name="action" value="draft" 
                            class="px-6 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-white font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors shadow-sm">
                            Simpan Draft
                        </button>
                        <button type="submit" name="action" value="submit" onclick="return confirm('Apakah Anda yakin ingin mengirim IDP ini? Data tidak dapat diubah setelah dikirim.')" 
                            class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-bold rounded-lg hover:from-indigo-700 hover:to-blue-700 transition-all shadow-lg hover:shadow-indigo-500/30 flex items-center gap-2">
                            <ion-icon name="paper-plane-outline"></ion-icon> Submit IDP
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>