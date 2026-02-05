<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Manajemen Posisi') }}
            </h2>
            <div class="flex flex-wrap gap-2">
                {{-- Tombol ke Hierarki Tree View --}}
                <a href="{{ route('admin.positions.hierarchy') }}" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 font-bold transition flex items-center">
                    <i class="fas fa-sitemap mr-2"></i> Struktur Hierarki
                </a>
                {{-- Tombol Tambah --}}
                <a href="{{ route('admin.positions.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 font-bold transition flex items-center">
                    <span class="mr-1">+</span> Tambah Posisi
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    {{-- Search Bar --}}
                    <form method="GET" action="{{ route('admin.positions.index') }}" class="mb-6 flex gap-2">
                        <div class="flex-1">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama posisi..." 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition">
                            Cari
                        </button>
                    </form>

                    {{-- Tabel --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    {{-- Kolom Utama diberi lebar lebih besar --}}
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4 min-w-[200px]">Nama Posisi</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4 min-w-[150px]">Unit / Organisasi</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4 min-w-[150px]">Atasan Langsung</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[100px]">Tipe</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-[150px]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($positions as $pos)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        {{-- Hapus whitespace-nowrap agar teks turun ke bawah --}}
                                        <td class="px-4 py-3 align-top">
                                            <div class="font-bold text-gray-800 dark:text-gray-200 text-sm">
                                                {{ $pos->title }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-top text-sm text-gray-500 dark:text-gray-400">
                                            {{ $pos->organization->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 align-top text-sm text-gray-500 dark:text-gray-400">
                                            {{ $pos->atasan->title ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 align-top text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $pos->tipe == 'organik' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                                {{ ucfirst($pos->tipe) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 align-top text-right text-sm font-medium whitespace-nowrap">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('admin.positions.edit', $pos->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                
                                                <form action="{{ route('admin.positions.destroy', $pos->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin hapus posisi ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada data posisi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $positions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>