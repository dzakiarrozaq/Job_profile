<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:underline mr-2">Manajemen Pengguna</a>
                <ion-icon name="chevron-forward" class="text-gray-400 mr-2"></ion-icon>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Edit Profil: {{ $user->name }}</h2>
            </div>
            
            {{-- Badge Status --}}
            <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $user->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ ucfirst($user->status) }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto pb-10">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PATCH') 
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- KOLOM KIRI: INFO UTAMA & LOGIN --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- CARD 1: Identitas Karyawan --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <ion-icon name="id-card-outline" class="mr-2 text-indigo-500"></ion-icon> Identitas Karyawan
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            {{-- NIK --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIK (Nomor Induk)</label>
                                <input type="text" name="nik" value="{{ old('nik', $user->nik) }}" placeholder="Contoh: 2023001"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                            </div>

                            {{-- Nama Lengkap --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            {{-- Jenis Kelamin --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Kelamin</label>
                                <select name="gender" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="" disabled>Pilih Gender</option>
                                    <option value="L" {{ old('gender', $user->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('gender', $user->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>

                            {{-- No HP --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Telepon/WA</label>
                                <input type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Tanggal Bergabung (HIRING DATE) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Bergabung</label>
                                <input type="date" name="hiring_date" value="{{ old('hiring_date', $user->hiring_date) }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    {{-- CARD 2: Informasi Login --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <ion-icon name="lock-closed-outline" class="mr-2 text-indigo-500"></ion-icon> Akun & Keamanan
                        </h3>

                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email (Username)</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Ubah Password</h4>
                                <p class="text-xs text-gray-500 mb-3">Kosongkan jika tidak ingin mengubah password.</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <input type="password" name="password" placeholder="Password Baru" class="block w-full rounded-md border-gray-300 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 text-sm">
                                    </div>
                                    <div>
                                        <input type="password" name="password_confirmation" placeholder="Konfirmasi Password" class="block w-full rounded-md border-gray-300 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 text-sm">
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: STRUKTUR & SETTINGS --}}
                <div class="space-y-6">
                    
                    {{-- CARD 3: Posisi & Organisasi --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <ion-icon name="business-outline" class="mr-2 text-indigo-500"></ion-icon> Organisasi
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status Akun</label>
                                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>🟢 Active</option>
                                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>🔴 Inactive</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Organisasi / Departemen</label>
                                <select name="organization_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500">
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
                                <select name="position_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500">
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

                    {{-- CARD 4: Role / Hak Akses --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <ion-icon name="shield-checkmark-outline" class="mr-2 text-indigo-500"></ion-icon> Role Aplikasi
                        </h3>
                        <div class="space-y-2">
                            @foreach($roles as $role)
                                <label class="flex items-center p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition">
                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" 
                                           {{ $user->roles->contains($role->id) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('role_ids')" class="mt-2" />
                    </div>

                </div> {{-- End Kolom Kanan --}}
            </div>

            {{-- Footer Action --}}
            <div class="mt-8 flex justify-between items-center bg-gray-50 dark:bg-gray-800 p-4 rounded-xl shadow-sm border dark:border-gray-700">
                @if(Auth::id() !== $user->id)
                    <button type="button" onclick="if(confirm('Yakin ingin menghapus user ini secara permanen? Data yang dihapus tidak bisa dikembalikan.')) document.getElementById('delete-form-{{ $user->id }}').submit();" 
                            class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center px-4 py-2 hover:bg-red-50 rounded-lg transition">
                        <ion-icon name="trash-outline" class="mr-2 text-lg"></ion-icon> Hapus Permanen
                    </button>
                @else
                    <div></div>
                @endif

                <div class="flex gap-3">
                    <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 shadow-sm transition">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition flex items-center">
                        <ion-icon name="save-outline" class="mr-2 text-lg"></ion-icon> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

        {{-- Hidden Delete Form --}}
        <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: none;">
            @csrf @method('DELETE')
        </form>
    </div>
</x-admin-layout>