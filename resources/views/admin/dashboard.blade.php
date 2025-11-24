{{-- Ini adalah file: resources/views/admin/dashboard.blade.php --}}

{{-- 1. Gunakan layout ADMIN yang baru saja kita buat --}}
<x-admin-layout>
    
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Dashboard Admin
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm flex items-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full mr-4">
                    <ion-icon name="people-outline" class="text-2xl text-blue-600 dark:text-blue-400"></ion-icon>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Total Pengguna Aktif</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $totalPengguna }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm flex items-center">
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full mr-4">
                    <ion-icon name="briefcase-outline" class="text-2xl text-green-600 dark:text-green-400"></ion-icon>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Total Job Profile</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $totalJobProfile }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm flex items-center">
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full mr-4">
                    <ion-icon name="library-outline" class="text-2xl text-yellow-600 dark:text-yellow-400"></ion-icon>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Total Pelatihan</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $totalPelatihan }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm flex items-center">
                <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full mr-4">
                    <ion-icon name="hourglass-outline" class="text-2xl text-red-600 dark:text-red-400"></ion-icon>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Persetujuan Tertunda</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $persetujuanTertunda }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Log Aktivitas Terbaru</h3>
                <div class="space-y-4">
                    @forelse ($recentLogs as $log)
                    <div class="flex">
                        <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-full h-fit mr-3">
                            <ion-icon name="person-outline" class="text-lg text-gray-500 dark:text-gray-300"></ion-icon>
                        </div>
                        <div>
                            <p class="text-sm text-gray-800 dark:text-gray-100">
                                <span class="font-bold">{{ $log->user->name ?? 'Sistem' }}</span> {{ $log->description }}
                            </p>
                            <p class="text-xs text-gray-400">{{ $log->timestamp->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada aktivitas.</p>
                    @endforelse
                </div>
                <a href="#" class="mt-6 inline-block w-full text-center px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 dark:bg-indigo-900 dark:text-indigo-300 rounded-lg hover:bg-indigo-100">
                    Lihat Semua Log Aktivitas
                </a>
            </div>

            <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                 <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Status Usulan Katalog Anda</h3>
                 <div class="space-y-3">
                    @forelse ($pendingKatalog as $training)
                    <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm">{{ $training->title }}</p>
                        @if ($training->status == 'pending_supervisor')
                            <span class="text-xs font-medium text-yellow-600 dark:text-yellow-400">MENUNGGU VERIFIKASI SPV</span>
                        @elseif ($training->status == 'pending_lp')
                            <span class="text-xs font-medium text-blue-600 dark:text-blue-400">MENUNGGU PERSETUJUAN LP</span>
                        @endif
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada usulan yang tertunda.</p>
                    @endforelse
                 </div>
                 <button class="w-full mt-6 px-4 py-3 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    Kelola Katalog Pelatihan
                 </button>
            </div>

        </div>
    </div>
</x-admin-layout>