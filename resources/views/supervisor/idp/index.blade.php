<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Persetujuan IDP (Supervisor)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 text-green-700 p-3 rounded">{{ session('success') }}</div>
                    @endif

                    <h3 class="text-lg font-bold mb-4">Menunggu Persetujuan</h3>
                    
                    <table class="min-w-full border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 text-left border">Nama Karyawan</th>
                                <th class="p-3 text-left border">Jabatan</th>
                                <th class="p-3 text-center border">Tahun</th>
                                <th class="p-3 text-center border">Tanggal Submit</th>
                                <th class="p-3 text-center border">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingIdps as $idp)
                            <tr>
                                <td class="p-3 border font-semibold">{{ $idp->user->name }}</td>
                                <td class="p-3 border">{{ $idp->user->position->title ?? '-' }}</td>
                                <td class="p-3 border text-center">{{ $idp->year }}</td>
                                <td class="p-3 border text-center text-sm text-gray-600">{{ $idp->updated_at->format('d M Y') }}</td>
                                <td class="p-3 border text-center">
                                    <a href="{{ route('supervisor.idp.show', $idp->id) }}" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                        Review
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-gray-500">Tidak ada IDP yang perlu disetujui saat ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>