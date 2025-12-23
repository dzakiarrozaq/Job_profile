<x-supervisor-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                    Review Pengajuan IDP
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Tinjau rencana pengembangan yang diajukan anggota tim Anda.
                </p>
            </div>
            <a href="{{ route('supervisor.persetujuan') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b pb-3 mb-4">Informasi Karyawan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Nama Karyawan</p>
                    <p class="font-medium text-gray-900 dark:text-gray-200 mt-1 text-lg">{{ $idp->user->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Jabatan / Posisi</p>
                    <p class="font-medium text-gray-900 dark:text-gray-200 mt-1 text-lg">{{ $idp->user->position->title ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Periode IDP</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 mt-1">
                        Tahun {{ $idp->year }}
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-100 dark:border-gray-700">
            <div class="p-6 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-sm">1. Development Plan (Rencana Pengembangan)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3 font-bold">Development Goals</th>
                            <th class="px-6 py-3 font-bold">Category</th>
                            <th class="px-6 py-3 font-bold">Activity</th>
                            <th class="px-6 py-3 text-center font-bold">Timeline</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($idp->details as $detail)
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white align-top">
                                {{ $detail->development_goal }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300 align-top">
                                <span class="px-2 py-1 rounded text-xs bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                                    {{ $detail->dev_category }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300 align-top">
                                {{ $detail->activity }}
                            </td>
                            <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-300 align-top">
                                {{ $detail->expected_date }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">
                                Tidak ada detail rencana pengembangan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="p-6 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide text-sm">2. Career Aspiration</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                    <label class="text-xs text-gray-500 font-bold uppercase mb-2 block">Career Preference</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $idp->career_preference ?? '-' }}</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                    <label class="text-xs text-gray-500 font-bold uppercase mb-2 block">Career Interest</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $idp->career_interest ?? '-' }}</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                    <label class="text-xs text-gray-500 font-bold uppercase mb-2 block">Future Job Interest</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $idp->future_job_interest ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-t-4 border-indigo-500 overflow-hidden">
            <div class="p-6">
                <h3 class="font-bold text-xl text-gray-900 dark:text-white mb-2">Keputusan Supervisor</h3>
                <p class="text-sm text-gray-500 mb-6">Silakan berikan keputusan Anda terkait pengajuan IDP ini.</p>
                
                <form action="{{ route('supervisor.idp.update', $idp->id) }}" method="POST">
                    @csrf
                    
                    {{-- Field Catatan (Penting untuk revisi) --}}
                    <div class="mb-6">
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-2">
                            Catatan / Feedback (Opsional untuk Approve, Wajib untuk Tolak)
                        </label>
                        <textarea name="rejection_note" 
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                            rows="3" 
                            placeholder="Tuliskan alasan penolakan atau masukan untuk karyawan..."></textarea>
                    </div>

                    <div class="flex items-center gap-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        {{-- Tombol Approve --}}
                        <button type="submit" name="action" value="approve" 
                            class="flex-1 justify-center inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-lg font-bold text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-green-200"
                            onclick="return confirm('Apakah Anda yakin menyetujui IDP ini? Data akan tersimpan sebagai Final.')">
                            <ion-icon name="checkmark-circle" class="text-xl mr-2"></ion-icon>
                            Setujui IDP
                        </button>

                        {{-- Tombol Reject --}}
                        <button type="submit" name="action" value="reject" 
                            class="flex-1 justify-center inline-flex items-center px-6 py-3 bg-white border-2 border-red-500 rounded-lg font-bold text-red-600 uppercase tracking-widest hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            onclick="return confirm('Apakah Anda yakin ingin menolak/meminta revisi IDP ini?')">
                            <ion-icon name="close-circle-outline" class="text-xl mr-2"></ion-icon>
                            Tolak / Minta Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-supervisor-layout>