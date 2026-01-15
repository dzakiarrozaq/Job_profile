<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Individual Development Plan (IDP)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="bg-white p-6 rounded-lg shadow-sm mb-6 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold">Periode: {{ date('Y') }}</h3>
                    <p class="text-gray-600">Nama: <span class="font-semibold">{{ Auth::user()->name }}</span></p>
                    <p class="text-gray-600">Jabatan: <span class="font-semibold">{{ Auth::user()->position->title ?? '-' }}</span></p>
                </div>
                
                <span class="px-4 py-2 rounded-full font-bold
                    {{ ($idp->status ?? 'draft') == 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                    {{ ($idp->status ?? '') == 'submitted' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ ($idp->status ?? '') == 'approved' ? 'bg-green-100 text-green-800' : '' }}">
                    Status: {{ ucfirst($idp->status ?? 'Draft') }}
                </span>
            </div>

            <form action="{{ route('idp.store') }}" method="POST">
                @csrf
                
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6" 
                     x-data="{ 
                        rows: {{ $idp && $idp->details->count() > 0 ? $idp->details->map(function($d){ return ['goal' => $d->development_goal, 'category' => $d->dev_category, 'activity' => $d->activity, 'date' => $d->expected_date, 'progress' => $d->progress]; }) : "[{ goal: '', category: 'Improve Current Capabilities', activity: '', date: '', progress: '' }]" }}
                     }">
                    
                    <div class="p-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-bold text-gray-800 uppercase">1. Development Plan</h3>
                    </div>
                    <div class="p-6 bg-white">
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-300">
                                <thead class="bg-gray-800 text-white">
                                    <tr>
                                        <th class="p-3 text-left border border-gray-600 w-10">No</th>
                                        <th class="p-3 text-left border border-gray-600">Development Goals</th>
                                        <th class="p-3 text-left border border-gray-600 w-1/4">Dev. Category</th>
                                        <th class="p-3 text-left border border-gray-600">Development Activities</th>
                                        <th class="p-3 text-center border border-gray-600 w-24">Expected Date</th>
                                        <th class="p-3 text-left border border-gray-600">Progress</th>
                                        <th class="p-3 text-center border border-gray-600 w-10">#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(row, index) in rows" :key="index">
                                        <tr>
                                            <td class="p-2 border border-gray-300 text-center" x-text="index + 1"></td>
                                            
                                            <td class="p-2 border border-gray-300 align-top">
                                                <textarea :name="`goals[${index}][goal]`" x-model="row.goal" rows="3" class="w-full border-gray-300 rounded focus:ring-blue-500" placeholder="Target pengembangan..."></textarea>
                                            </td>
                                            
                                            <td class="p-2 border border-gray-300 align-top">
                                                <select :name="`goals[${index}][category]`" x-model="row.category" class="w-full border-gray-300 rounded focus:ring-blue-500">
                                                    <option value="Improve Current Capabilities">Improve Current Capabilities</option>
                                                    <option value="Corporate/Functional Objective">Corporate/Functional Objective</option>
                                                </select>
                                            </td>
                                            
                                            <td class="p-2 border border-gray-300 align-top">
                                                <textarea :name="`goals[${index}][activity]`" x-model="row.activity" rows="3" class="w-full border-gray-300 rounded focus:ring-blue-500" placeholder="Aktivitas..."></textarea>
                                            </td>
                                            
                                            <td class="p-2 border border-gray-300 align-top">
                                                <input type="text" :name="`goals[${index}][date]`" x-model="row.date" class="w-full border-gray-300 rounded text-center" placeholder="2025">
                                            </td>
                                            
                                            <td class="p-2 border border-gray-300 align-top">
                                                <textarea :name="`goals[${index}][progress]`" x-model="row.progress" rows="3" class="w-full border-gray-300 rounded bg-gray-50" placeholder="Progress..."></textarea>
                                            </td>

                                            <td class="p-2 border border-gray-300 text-center align-middle">
                                                <button type="button" @click="rows.splice(index, 1)" class="text-red-500 hover:text-red-700" title="Hapus Baris">
                                                    <ion-icon name="trash-outline" class="text-xl"></ion-icon>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            
                            <button type="button" 
                                @click="rows.push({ goal: '', category: 'Improve Current Capabilities', activity: '', date: '', progress: '' })"
                                class="mt-3 flex items-center text-sm font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                <ion-icon name="add-circle-outline" class="text-xl mr-1"></ion-icon>
                                Tambah Rencana Baru
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-bold text-gray-800 uppercase">2. Career Aspiration</h3>
                    </div>
                    <div class="p-6 bg-white">
                        <table class="min-w-full border border-gray-300">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="p-3 text-left border border-gray-600 w-1/3">Career Preference</th>
                                    <th class="p-3 text-left border border-gray-600 w-1/3">Career Interest</th>
                                    <th class="p-3 text-left border border-gray-600 w-1/3">Future Job Interest</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="p-2 border border-gray-300">
                                        <input type="text" name="career_preference" value="{{ old('career_preference', $idp->career_preference ?? '') }}" class="w-full border-gray-300 rounded" placeholder="Preferensi Karir...">
                                    </td>
                                    <td class="p-2 border border-gray-300">
                                        <input type="text" name="career_interest" value="{{ old('career_interest', $idp->career_interest ?? '') }}" class="w-full border-gray-300 rounded" placeholder="Minat Karir...">
                                    </td>
                                    <td class="p-2 border border-gray-300">
                                        <input type="text" name="future_job_interest" value="{{ old('future_job_interest', $idp->future_job_interest ?? '') }}" class="w-full border-gray-300 rounded" placeholder="Posisi masa depan...">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-blue-50 border border-blue-100 rounded-lg flex justify-between items-center">
                        <div>
                            <h4 class="font-bold text-gray-800">Verifikasi & Persetujuan</h4>
                            <p class="text-sm text-gray-600">Formulir ini akan dikirim ke atasan Anda untuk disetujui.</p>
                        </div>
                        <div class="flex gap-4">
                            <button type="submit" name="action" value="draft" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 font-medium">
                                <ion-icon name="save-outline" class="mr-1"></ion-icon>
                                Simpan Draft
                            </button>
                            <button type="submit" name="action" value="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 shadow-lg font-medium flex items-center">
                                <ion-icon name="paper-plane-outline" class="mr-2"></ion-icon>
                                Submit IDP
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>