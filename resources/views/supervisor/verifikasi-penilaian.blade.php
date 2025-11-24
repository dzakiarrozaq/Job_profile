<x-supervisor-layout>
    
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('supervisor.persetujuan') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Persetujuan</a>
            <ion-icon name="chevron-forward-outline" class="mx-2 text-gray-400"></ion-icon>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                Verifikasi Penilaian Kompetensi
            </h2>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 lg:p-8">
            
            <form action="{{ route('supervisor.penilaian.store', $karyawan->id) }}" method="POST">
                @csrf
                
                <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Penilaian dari Karyawan</h3>
                    <div class="flex items-center space-x-4 mt-4">
                        <img class="h-16 w-16 rounded-full object-cover" src="https://i.pravatar.cc/150?u={{ $karyawan->email }}" alt="Foto Profil">
                        <div>
                            <p class="font-bold text-lg text-gray-900 dark:text-white">{{ $karyawan->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Jabatan: {{ $karyawan->position->title ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Diajukan: {{ $karyawan->employeeProfiles->first()->submitted_at->format('d F Y') ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Kompetensi</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Ideal</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Diajukan</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase w-28">Level Verifikasi Anda</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase w-1/3">Catatan Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            
                            @forelse ($assessments as $item)
                            <tr class="@if($item->status == 'pending_verification') bg-yellow-50 dark:bg-yellow-900/10 @endif">
                                <td class="px-4 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">{{ $item->competency_name }}</td>
                                <td class="px-4 py-4 text-center font-medium">{{ $item->ideal_level }}</td>
                                <td class="px-4 py-4 text-center font-bold text-lg {{ $item->submitted_level < $item->ideal_level ? 'text-red-600' : 'text-yellow-600' }}">
                                    {{ $item->submitted_level ?? '-' }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <select name="level[{{ $item->competency_code }}]" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="1" @if(old('level.'.$item->competency_code, $item->submitted_level) == 1) selected @endif>1</option>
                                        <option value="2" @if(old('level.'.$item->competency_code, $item->submitted_level) == 2) selected @endif>2</option>
                                        <option value="3" @if(old('level.'.$item->competency_code, $item->submitted_level) == 3) selected @endif>3</option>
                                        <option value="4" @if(old('level.'.$item->competency_code, $item->submitted_level) == 4) selected @endif>4</option>
                                        <option value="5" @if(old('level.'.$item->competency_code, $item->submitted_level) == 5) selected @endif>5</option>
                                    </select>
                                </td>
                                <td class="px-4 py-4">
                                    <input type="text" name="notes[{{ $item->competency_code }}]" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Catatan (opsional)..." value="{{ old('notes.'.$item->competency_code) }}">
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    Karyawan ini tidak memiliki Job Profile atau data penilaian.
                                </td>
                            </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
                
                <div class="flex justify-end gap-4">
                    <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Tolak & Kembalikan
                    </button>
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Simpan & Verifikasi Penilaian
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-supervisor-layout>