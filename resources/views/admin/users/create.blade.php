<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:underline mr-2">Manajemen Pengguna</a>
            <ion-icon name="chevron-forward" class="text-gray-400 mr-2"></ion-icon>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Tambah User Baru</h2>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 space-y-6">
                
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Akun</h3>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                                <input type="password" name="password" required class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" required class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                </div>

                <hr class="dark:border-gray-700">

                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Organisasi</h3>
                    <div class="grid grid-cols-1 gap-6">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Peran (Role)</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($roles as $role)
                                    <label class="inline-flex items-center p-3 rounded border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <input type="checkbox" name="role_id[]" value="{{ $role->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">{{ $role->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Departemen</label>
                                <select name="department_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan (Posisi)</label>
                                <select name="position_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos->id }}">{{ $pos->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Atasan Langsung (Supervisor)</label>
                            <select name="manager_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Tidak Ada / Saya Sendiri --</option>
                                @foreach($supervisors as $spv)
                                    <option value="{{ $spv->id }}">{{ $spv->name }} - {{ $spv->position->title ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Batal</a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-sm">Simpan User</button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>