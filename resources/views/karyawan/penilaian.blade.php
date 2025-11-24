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

                <p class="text-sm text-gray-600 mb-6">
                    @if ($globalStatus === 'pending')
                        Status saat ini: <span class="font-semibold text-yellow-800">Menunggu Verifikasi Supervisor</span>
                        <span class="text-xs block text-gray-500">(Anda masih bisa menyimpan draf untuk item lain)</span>
                    @elseif ($globalStatus === 'draft')
                        Status saat ini: <span class="font-semibold text-blue-800">Draft (Belum Diajukan)</span>
                    @elseif ($globalStatus === 'no_profile')
                        Status saat ini: <span class="font-semibold text-red-800">Job Profile tidak ditemukan. Hubungi Admin.</span>
                    @else
                        Status saat ini: <span class="font-semibold text-green-800">Semua Sudah Terverifikasi</span>
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
                                    <td class="px-4 py-4">
                                        @if ($item->status == 'verified')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                VERIFIED
                                            </span>
                                        @elseif ($item->status == 'pending_verification')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                PENDING
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                DRAFT
                                            </span>
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
                        <x-secondary-button type="submit" name="action" value="draft" :disabled="!$hasDrafts">
                            Simpan sebagai Draf
                        </x-secondary-button>
                        <x-primary-button type="submit" name="action" value="submit" :disabled="!$hasDrafts">
                            Ajukan Verifikasi ke Supervisor
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>