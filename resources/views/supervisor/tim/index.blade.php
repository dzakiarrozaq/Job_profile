{{-- File: resources/views/supervisor/tim/index.blade.php --}}
<x-supervisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                Anggota Tim Saya
            </h2>
            <a href="{{ route('supervisor.tim.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 shadow-sm flex items-center">
                <ion-icon name="person-add-outline" class="mr-2"></ion-icon>
                Tambah Anggota Tim
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm flex items-center border border-gray-100 dark:border-gray-700">
                <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-full mr-4 text-indigo-600 dark:text-indigo-400">
                    <ion-icon name="people" class="text-2xl"></ion-icon>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">Total Anggota Tim</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $teamMembers->total() }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm flex items-center border border-gray-100 dark:border-gray-700">
                <div class="p-3 bg-green-50 dark:bg-green-900/30 rounded-full mr-4 text-green-600 dark:text-green-400">
                    <ion-icon name="person" class="text-2xl"></ion-icon>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">Karyawan Organik</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $organicCount}}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm flex items-center border border-gray-100 dark:border-gray-700">
                <div class="p-3 bg-purple-50 dark:bg-purple-900/30 rounded-full mr-4 text-purple-600 dark:text-purple-400">
                    <ion-icon name="briefcase" class="text-2xl"></ion-icon>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">Karyawan Outsourcing</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $outsourcingCount}}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Daftar Anggota Tim</h3>
                
                <form method="GET" action="{{ route('supervisor.tim.index') }}" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1 relative">
                        <ion-icon name="search-outline" class="absolute left-3 top-3 text-gray-400"></ion-icon>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama karyawan..." class="w-full pl-10 rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="w-full md:w-48">
                        <select name="role" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all">Semua Role</option>
                            <option value="Karyawan Organik" {{ request('role') == 'Karyawan Organik' ? 'selected' : '' }}>Karyawan Organik</option>
                            <option value="Karyawan Outsourcing" {{ request('role') == 'Karyawan Outsourcing' ? 'selected' : '' }}>Karyawan Outsourcing</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 font-medium text-sm">
                        Filter
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jabatan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status Penilaian</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @forelse($teamMembers as $member)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                            src="{{ $member->profile_photo_path ? asset('storage/' . $member->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($member->name) }}" 
                                            alt="{{ $member->name }}">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $member->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $member->nik ?? 'NIK' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $member->position->title ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-500 dark:text-gray-400">

                                    {{-- ATAU jika relasi many-to-many manual --}}
                                    {{ $member->roles->pluck('name')->implode(', ') ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($member->assessment_status == 'verified')
                                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800 uppercase">SUDAH TERVERIFIKASI</span>
                                @elseif($member->assessment_status == 'pending_verification')
                                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800 uppercase">MENUNGGU VERIFIKASI</span>
                                @elseif($member->assessment_status == 'in_review')
                                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-600 uppercase">BELUM DINILAI</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800 border border-red-200 uppercase"> BELUM MENGISI </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('supervisor.tim.show', $member->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md transition">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                Tidak ada anggota tim yang ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($teamMembers->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $teamMembers->withQueryString()->links() }}
                </div>
            @endif

        </div>
    </div>
</x-supervisor-layout>