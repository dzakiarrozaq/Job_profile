<x-admin-layout> 
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('admin.job-profile.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Manajemen Job Profile</a>
            <ion-icon name="chevron-forward-outline" class="mx-2 text-gray-400"></ion-icon>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                Buat Job Profile Baru
            </h2>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start gap-3">
            <ion-icon name="information-circle" class="text-blue-500 text-xl mt-0.5"></ion-icon>
            <div>
                <h3 class="text-sm font-semibold text-blue-800">Langkah 1 dari 2</h3>
                <p class="text-sm text-blue-600 mt-1">
                    Pilih posisi jabatan yang ingin Anda buatkan profilnya. Setelah ini, Anda akan diarahkan ke halaman detail untuk mengisi Kompetensi, Tanggung Jawab, dan lainnya.
                </p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.job-profile.store') }}" method="POST">
            @csrf
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 lg:p-8 space-y-6">
                
                <div>
                    <label for="position_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Pilih Posisi / Jabatan
                    </label>
                    <div class="relative">
                        <select id="position_id" name="position_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white py-3 pl-4 pr-10">
                            <option value="">-- Pilih Posisi yang Belum Punya Profil --</option>
                            @forelse ($positions as $pos)
                                <option value="{{ $pos->id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>
                                    {{ $pos->title }} &mdash; ({{ $pos->department->name ?? 'No Dept' }})
                                </option>
                            @empty
                                <option value="" disabled>Semua posisi sudah memiliki Job Profile</option>
                            @endforelse
                        </select>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Hanya posisi yang <strong>belum memiliki</strong> Job Profile yang muncul di sini.
                    </p>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 dark:border-gray-700 pt-6 mt-6">
                    <a href="{{ route('admin.job-profile.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 flex items-center shadow-sm">
                        <span>Buat & Lanjut Mengisi</span>
                        <ion-icon name="arrow-forward-outline" class="ml-2"></ion-icon>
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>