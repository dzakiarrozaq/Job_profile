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
                    @elseif($globalStatus === 'pending_verification') {{-- Sesuaikan dengan ENUM database --}}
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
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Level Aktual</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Input Level Saya</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Gap</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                
                                @forelse ($assessments as $item)
                                <tr class="@if($globalStatus == 'verified')  @elseif($globalStatus == 'pending_verification') bg-yellow-50 @endif">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <p class="font-medium text-gray-900">{{ $item->competency_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->competency_code }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-center font-medium text-gray-700">{{ $item->ideal_level }}</td>
                                    
                                    {{-- Level Aktual (Read Only) --}}
                                    <td class="px-4 py-4 text-center font-bold text-lg {{ $item->current_level > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                        {{ $item->current_level > 0 ? $item->current_level : '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        
                                        {{-- PERBAIKAN 2: Cek Editability pakai $globalStatus --}}
                                        @if ($globalStatus == 'draft' || $globalStatus == 'not_started')
                                            {{-- PERBAIKAN 3: Value ID ambil dari $item->id --}}
                                            <select name="competencies[{{ $item->id }}]" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 w-20 text-center">
                                                <option value="">-</option>
                                                {{-- PERBAIKAN 4: Cek selected pakai $item->current_level --}}
                                                <option value="1" @selected($item->current_level == 1)>1</option>
                                                <option value="2" @selected($item->current_level == 2)>2</option>
                                                <option value="3" @selected($item->current_level == 3)>3</option>
                                                <option value="4" @selected($item->current_level == 4)>4</option>
                                                <option value="5" @selected($item->current_level == 5)>5</option>
                                            </select>
                                        @else
                                            <select class="rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed w-20 text-center" disabled>
                                                <option>{{ $item->current_level }}</option>
                                            </select>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        
                                        @if($globalStatus === 'verified')
                                            @php
                                                $gap = $item->current_level - $item->ideal_level;
                                            @endphp

                                            @if($gap < 0)
                                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">{{ $gap }}</span>
                                            @elseif($gap > 0)
                                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">+{{ $gap }}</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">OK</span>
                                            @endif

                                        @elseif($item->current_level > 0)
                                            <span class="px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg">
                                                Menunggu Verifikasi
                                            </span>
                                            
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                        Tidak ada data kompetensi yang ditemukan untuk posisi ini.
                                    </td>
                                </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end gap-4">
                        @if($globalStatus === 'verified' || $globalStatus === 'pending_verification')
                            <button type="button" disabled class="px-6 py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                                {{ $globalStatus === 'verified' ? 'Penilaian Selesai' : 'Sedang Diverifikasi' }}
                            </button>
                        @else
                            {{-- Tombol Simpan --}}
                            <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm">
                                Simpan & Ajukan Verifikasi
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>