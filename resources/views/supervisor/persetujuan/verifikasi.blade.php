<x-supervisor-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('supervisor.persetujuan') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline mr-2">
                Pusat Persetujuan
            </a>
            <span class="text-gray-400 dark:text-gray-500 mr-2">/</span>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                Verifikasi: {{ $employee->name }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6">
        <form action="{{ route('supervisor.penilaian.store', $employee->id) }}" method="POST">
            @csrf
            
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Formulir Verifikasi Kompetensi</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Silakan tinjau penilaian mandiri karyawan dan berikan nilai akhir.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Kompetensi</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Target (Ideal)</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Penilaian Karyawan</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider bg-indigo-50 dark:bg-indigo-900/20">Nilai Verifikasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Catatan Supervisor</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($competencies as $comp)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $comp->competency_name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $comp->competency_code }} • {{ ucfirst($comp->type) }}</div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ $comp->ideal_level }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $comp->submitted_level }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center bg-indigo-50/50 dark:bg-indigo-900/10">
                                    <select name="verified_level[{{ $comp->competency_code }}]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white text-center font-bold">
                                        @foreach([1,2,3,4,5] as $val)
                                            <option value="{{ $val }}" {{ $val == ($comp->current_level ?: $comp->submitted_level) ? 'selected' : '' }}>
                                                {{ $val }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-6 py-4">
                                    <textarea name="notes[{{ $comp->competency_code }}]" rows="1" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Opsional...">{{ $comp->reviewer_notes }}</textarea>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex justify-end gap-3">
                    <a href="{{ route('supervisor.persetujuan') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition ease-in-out duration-150">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Simpan & Verifikasi
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-supervisor-layout>