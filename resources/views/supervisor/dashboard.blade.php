<x-supervisor-layout>

    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Dashboard Manajemen Tim
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm flex items-center">
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full mr-4">
                    <ion-icon name="person-add-outline"
                        class="text-2xl text-yellow-600 dark:text-yellow-400"></ion-icon>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Penilaian Kompetensi</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $penilaianCount }} Menunggu</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm flex items-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full mr-4">
                    <ion-icon name="document-attach-outline"
                        class="text-2xl text-blue-600 dark:text-blue-400"></ion-icon>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Rencana Pelatihan</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $rencanaCount }} Menunggu</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm flex items-center">
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full mr-4">
                    <ion-icon name="ribbon-outline" class="text-2xl text-green-600 dark:text-green-400"></ion-icon>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Verifikasi Sertifikat</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $sertifikatCount }} Menunggu</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm flex items-center">
                <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full mr-4">
                    <ion-icon name="trending-up-outline" class="text-2xl text-purple-600 dark:text-purple-400"></ion-icon>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Persetujuan IDP</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $idpCount ?? 0 }} Menunggu</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4 p-6 border-b dark:border-gray-700">Tugas
                Persetujuan Mendesak</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Karyawan</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Tipe Pengajuan</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Tanggal Diajukan</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">

                        @forelse ($tugasMendesak as $tugas)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                    {{ $tugas->karyawan }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($tugas->status_sort == '0_jobprofile')
                                        <span
                                            class="px-2 py-1 text-xs font-bold rounded-full bg-purple-100 text-purple-800 border border-purple-200">
                                            {{ $tugas->tipe }}
                                        </span>
                                    @elseif ($tugas->status_sort == '1_penilaian')
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ $tugas->tipe }}
                                        </span>
                                    @elseif ($tugas->status_sort == '2_rencana')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $tugas->tipe }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ $tugas->tipe }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $tugas->tanggal ? \Carbon\Carbon::parse($tugas->tanggal)->format('d F Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ $tugas->url }}"
                                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Tinjau</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada tugas persetujuan yang mendesak. Kerja bagus!
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Anggota Tim Saya
                ({{ $teamMembers->count() }} Orang)</h3>
            <div class="space-y-4">

                @forelse ($teamMembers as $member)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="relative inline-block">
                                <img class="h-16 w-16 rounded-full object-cover border-4 border-white dark:border-gray-700 shadow-md mr-4" 
                                    src="{{ $member->profile_photo_path ? asset('storage/' . $member->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($member->name) }}" 
                                    alt="Foto">
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $member->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $member->position->title ?? 'Belum ada posisi' }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('supervisor.tim.show', $member->id) }}"
                            class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold text-sm">Lihat
                            Profil</a>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Anda belum memiliki anggota tim.</p>
                @endforelse

            </div>
            <a href="{{ route('supervisor.tim.index') }}"
                class="block w-full mt-6 px-4 py-3 text-sm font-medium text-center text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">
                Kelola Semua Anggota Tim
            </a>
        </div>

    </div>
</x-supervisor-layout>