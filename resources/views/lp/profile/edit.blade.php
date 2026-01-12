<x-lp-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Profil Saya
        </h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                {{ session('success') }}
            </div>
        @endif
        
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Akun</h3>
            
            <form method="post" action="{{ route('lp.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('patch')

                <div class="flex items-center space-x-6">
                    <div class="shrink-0">
                        @if($user->profile_photo_path)
                            <img class="h-20 w-20 object-cover rounded-full border-2 border-gray-200" 
                                 src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                 alt="Foto Profil">
                        @else
                            <img class="h-20 w-20 object-cover rounded-full border-2 border-gray-200" 
                                 src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&color=fff" 
                                 alt="Default Avatar">
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Foto Profil</label>
                        <div class="mt-1 flex items-center">
                            <input type="file" name="photo" 
                                   class="block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-full file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300
                                          cursor-pointer">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG atau JPEG. Maks 1MB.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                        <input type="text" value="Learning Partner" disabled
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed dark:bg-gray-600 dark:text-gray-400">
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/30 font-medium">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Ganti Password</h3>
            
            <form method="post" action="{{ route('lp.password.update') }}" class="space-y-4">
                @csrf
                @method('put')

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password Saat Ini</label>
                    <input type="password" name="current_password" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password Baru</label>
                    <input type="password" name="password" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition dark:bg-gray-600 dark:hover:bg-gray-500">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-lp-layout>