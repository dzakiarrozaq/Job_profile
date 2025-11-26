{{-- File: resources/views/supervisor/tim/create.blade.php --}}
<x-supervisor-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('supervisor.tim.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Anggota Tim Saya</a>
            <ion-icon name="chevron-forward-outline" class="mx-2 text-gray-400"></ion-icon>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                Tambah Anggota Baru
            </h2>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        
        <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 flex items-start gap-3">
            <ion-icon name="information-circle" class="text-blue-500 text-xl mt-0.5"></ion-icon>
            <div>
                <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300">Informasi Akun</h3>
                <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                    Password default untuk akun baru adalah <strong>password</strong>. Karyawan dapat mengubahnya setelah login pertama kali.
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 lg:p-8">
            <form action="{{ route('supervisor.tim.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Nama lengkap karyawan">
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="email@perusahaan.com">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <label for="batch_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nomor Batch / NIK</label>
                    <input type="text" name="batch_number" id="batch_number" value="{{ old('batch_number') }}"
                           class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Contoh: EMP-2024-001">
                    <x-input-error :messages="$errors->get('batch_number')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status Karyawan</label>
                        <select name="role_id" id="role_id" required
                                class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Pilih Status</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
                    </div>

                    <div>
                        <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Departemen</label>
                        <select name="department_id" id="department_id" required
                                class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Pilih Departemen</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <label for="position_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Posisi / Jabatan</label>
                    <select name="position_id" id="position_id" required
                            class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Pilih Posisi</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>
                                {{ $pos->title }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        *Pastikan Job Profile untuk posisi ini sudah tersedia agar karyawan bisa melakukan penilaian.
                    </p>
                    <x-input-error :messages="$errors->get('position_id')" class="mt-2" />
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('supervisor.tim.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm">
                        Simpan & Tambahkan
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-supervisor-layout>