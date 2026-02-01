<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                Manajemen Job Profile (Admin)
            </h2>
            <a href="{{ route('admin.job-profile.create') }}" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                <ion-icon name="add-outline" class="mr-1"></ion-icon>
                Tambah Job Profile Baru
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b dark:border-gray-700">
                <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Daftar Job Profile</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Jabatan (Posisi)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Dibuat Oleh</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Versi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Terakhir Diperbarui</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($jobProfiles as $profile)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $profile->position->title ?? 'Posisi Dihapus' }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    @php
                                        // Ambil organisasi dari posisi
                                        $org = $profile->position->organization ?? null;
                                        $path = [];

                                        if ($org) {
                                            // 1. Masukkan Unit (Level saat ini)
                                            $path[] = $org->name; 

                                            // 2. Cek apakah punya Parent (Section)
                                            if ($org->parent) {
                                                $path[] = $org->parent->name;

                                                // 3. Cek apakah punya Parent lagi (Departemen)
                                                if ($org->parent->parent) {
                                                    $path[] = $org->parent->parent->name;
                                                }
                                            }
                                        }
                                    @endphp

                                    {{-- Tampilkan digabung dengan tanda panah --}}
                                    {{ !empty($path) ? implode(' -> ', $path) : '-' }}
                                </p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $profile->creator->name ?? 'Sistem' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">v{{ $profile->version }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $profile->updated_at->format('d F Y') }}
                            </td>
                            <td class="px-6 py-4 text-right flex justify-end space-x-2">
                                <a href="{{ route('admin.job-profile.edit', $profile->id) }}" class="px-3 py-1 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                                    Edit
                                </a>
                                <form action="{{ route('admin.job-profile.destroy', $profile->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada Job Profile.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($jobProfiles->hasPages())
                <div class="p-6 border-t dark:border-gray-700">{{ $jobProfiles->links() }}</div>
            @endif
        </div>
    </div>
</x-admin-layout>