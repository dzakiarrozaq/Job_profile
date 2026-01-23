<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:underline mr-2">Manajemen Pengguna</a>
            <ion-icon name="chevron-forward" class="text-gray-400 mr-2"></ion-icon>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Edit User: {{ $user->name }}</h2>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PATCH') 
            
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 space-y-6">
                
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Akun</h3>
                    <div class="grid grid-cols-1 gap-6">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status Akun</label>
                            <select name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active (Aktif)</option>
                                <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive (Non-Aktif)</option>
                            </select>
                        </div>

                        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-100 dark:border-yellow-800">
                            <h4 class="text-sm font-bold text-yellow-800 dark:text-yellow-400 mb-2 flex items-center">
                                <ion-icon name="key-outline" class="mr-2"></ion-icon> Ganti Password
                            </h4>
                            <p class="text-xs text-yellow-700 dark:text-yellow-500 mb-4">
                                Biarkan kosong jika Anda tidak ingin mengubah password pengguna ini.
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password Baru</label>
                                    <input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password</label>
                                    <input type="password" name="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
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
                                    <label class="inline-flex items-center p-3 rounded border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $user->roles->contains($role->id) ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-200' : '' }}">
                                        <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" 
                                               {{ $user->roles->contains($role->id) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">{{ $role->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('role_ids')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Organisasi</label>
                                <select name="organization_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach($organizations as $org)
                                        <option value="{{ $org->id }}" {{ (optional($user->position)->organization_id == $org->id) ? 'selected' : '' }}>
                                            {{ $org->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan (Posisi)</label>
                                <select name="position_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos->id }}" {{ old('position_id', $user->position_id) == $pos->id ? 'selected' : '' }}>
                                            {{ $pos->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        </div>
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-gray-700">
                    
                    @if(Auth::id() !== $user->id)
                        <button type="button" onclick="if(confirm('Yakin ingin menghapus user ini secara permanen?')) document.getElementById('delete-form-{{ $user->id }}').submit();" 
                                class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center">
                            <ion-icon name="trash-outline" class="mr-1"></ion-icon> Hapus User
                        </button>
                    @else
                        <div></div> 
                    @endif

                    <div class="flex gap-3">
                        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Batal</a>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-sm">Simpan Perubahan</button>
                    </div>
                </div>
            </div>
        </form>

        <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</x-admin-layout>