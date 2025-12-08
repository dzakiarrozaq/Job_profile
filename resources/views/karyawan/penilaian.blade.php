<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold text-gray-900">
            Penilaian Kompetensi Saya
        </h1>
        <p class="text-gray-600 mt-1">Perbarui level kompetensi Anda di bawah ini. Penilaian akan ditinjau dan diverifikasi oleh atasan.</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-6 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-lg shadow p-6 lg:p-8">
                
                <h2 class="text-xl font-bold text-gray-900">Formulir Self-Assessment</h2>

                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Status saat ini: 
                    @if($globalStatus === 'verified')
                        <span class="font-bold text-green-600">Semua Sudah Terverifikasi</span>
                    @elseif($globalStatus === 'pending')
                        <span class="font-bold text-yellow-600">Menunggu Verifikasi Supervisor</span>
                    @elseif($globalStatus === 'not_started') 
                        <span class="font-bold text-red-600">Belum Mengisi (Wajib Diisi)</span>
                    @else
                        <span class="font-bold text-gray-600">Draf / Belum Diajukan</span>
                    @endif
                </p>

                <form action="{{ route('penilaian.store') }}" method="POST">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Kompetensi</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Level Ideal</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Level Terverifikasi</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Level Saya (Self-Assessment)</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                
                                @forelse ($assessments as $item)
                                <tr class_="@if($item->status == 'verified') bg-green-50 @elseif($item->status == 'pending_verification') bg-yellow-50 @endif">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <p class="font-medium text-gray-900">{{ $item->competency_name }}</p>
                                        @if($item->reviewer_notes)
                                        <p class="text-xs text-gray-500 font-normal mt-1">Catatan Supervisor: {{ $item->reviewer_notes }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center font-medium text-gray-700">{{ $item->ideal_level }}</td>
                                    <td class="px-4 py-4 text-center font-bold text-lg {{ $item->current_level != '-' ? 'text-green-600' : 'text-gray-500' }}">
                                        {{ $item->current_level }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        
                                        @if ($item->status == 'draft')
                                            <select name="competency[{{ $item->competency_code }}]" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Pilih Level</option>
                                                <option value="1" @if($item->submitted_level == 1) selected @endif>1</option>
                                                <option value="2" @if($item->submitted_level == 2) selected @endif>2</option>
                                                <option value="3" @if($item->submitted_level == 3) selected @endif>3</option>
                                                <option value="4" @if($item->submitted_level == 4) selected @endif>4</option>
                                                <option value="5" @if($item->submitted_level == 5) selected @endif>5</option>
                                            </select>
                                        @else
                                            <select class="rounded-md border-gray-300 shadow-sm bg-gray-100" disabled>
                                                <option selected>{{ $item->submitted_level ?? $item->current_level }}</option>
                                            </select>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($item->status === 'verified')
                                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">VERIFIED</span>
                                        @elseif($item->status === 'pending_verification')
                                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800">PENDING</span>
                                        @elseif($globalStatus === 'not_started') {{-- KONDISI BARU --}}
                                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">EMPTY</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-600">DRAFT</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                        @if($globalStatus === 'no_profile')
                                            Jabatan Anda saat ini belum memiliki Job Profile. Silakan hubungi Admin/HRD.
                                        @else
                                            Tidak ada data kompetensi yang ditemukan.
                                        @endif
                                    </td>
                                </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end gap-4">
                        @if($globalStatus === 'verified' || $globalStatus === 'pending')
                            <button type="button" disabled class="px-6 py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                                {{ $globalStatus === 'verified' ? 'Penilaian Selesai' : 'Menunggu Verifikasi' }}
                            </button>
                        @else
                            <button type="submit" name="action" value="save_draft" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Simpan sebagai Draf
                            </button>
                            <button type="submit" name="action" value="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm">
                                {{ $globalStatus === 'not_started' ? 'Mulai & Ajukan Verifikasi' : 'Ajukan Verifikasi ke Supervisor' }}
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>