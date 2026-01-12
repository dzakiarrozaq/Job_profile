<x-lp-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Katalog Pelatihan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-xl">
                
                {{-- Header Form --}}
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Formulir Pelatihan Baru</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Isi detail pelatihan yang akan ditampilkan di katalog karyawan.</p>
                    </div>
                    <a href="{{ route('lp.katalog.index') }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white underline">
                        Batal & Kembali
                    </a>
                </div>

                <div class="p-6">
                    <form action="{{ route('lp.katalog.store') }}" method="POST" class="space-y-6">
                        @csrf

                        {{-- Judul Pelatihan --}}
                        <div>
                            <x-input-label for="title" :value="__('Judul Pelatihan')" />
                            <x-text-input id="title" class="block mt-1 w-full placeholder-gray-400" type="text" name="title" :value="old('title')" required autofocus placeholder="Contoh: Mastering Laravel 11 for Beginners" />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Provider --}}
                            <div>
                                <x-input-label for="provider" :value="__('Penyedia (Provider)')" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <ion-icon name="business-outline" class="text-gray-400"></ion-icon>
                                    </div>
                                    <x-text-input id="provider" class="block w-full pl-10" type="text" name="provider" :value="old('provider')" required placeholder="Misal: Udemy, Internal HR, Coursera" />
                                </div>
                                <x-input-error :messages="$errors->get('provider')" class="mt-2" />
                            </div>

                            {{-- Durasi (Opsional) - Menyesuaikan dengan Admin --}}
                            <div>
                                <x-input-label for="duration" :value="__('Estimasi Durasi (Opsional)')" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <ion-icon name="time-outline" class="text-gray-400"></ion-icon>
                                    </div>
                                    <x-text-input id="duration" class="block w-full pl-10" type="text" name="duration" :value="old('duration')" placeholder="Contoh: 4 Jam, 2 Hari" />
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Tipe Pelatihan (Method) --}}
                            <div>
                                <x-input-label for="type" :value="__('Tipe Pelatihan (Metode)')" />
                                <select id="type" name="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="" disabled selected>Pilih Metode...</option>
                                    <option value="Online" {{ old('type') == 'Online' ? 'selected' : '' }}>Online</option>
                                    <option value="Offline" {{ old('type') == 'Offline' ? 'selected' : '' }}>Offline</option>
                                    <option value="Hybrid" {{ old('type') == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            {{-- Tingkat Kesulitan (Menyesuaikan Admin) --}}
                            <div>
                                <x-input-label for="difficulty" :value="__('Tingkat Kesulitan')" />
                                <select id="difficulty" name="difficulty" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="Beginner" {{ old('difficulty') == 'Beginner' ? 'selected' : '' }}>Beginner (Pemula)</option>
                                    <option value="Intermediate" {{ old('difficulty') == 'Intermediate' ? 'selected' : '' }}>Intermediate (Menengah)</option>
                                    <option value="Advanced" {{ old('difficulty') == 'Advanced' ? 'selected' : '' }}>Advanced (Lanjut)</option>
                                </select>
                                <x-input-error :messages="$errors->get('difficulty')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Biaya (Khusus LP biasanya butuh input cost) --}}
                        <div>
                            <x-input-label for="cost" :value="__('Biaya (Rp)')" />
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                </div>
                                <x-text-input id="cost" class="block w-full pl-10" type="number" name="cost" :value="old('cost')" required placeholder="0" />
                            </div>
                            <x-input-error :messages="$errors->get('cost')" class="mt-2" />
                        </div>

                        {{-- Link URL (Opsional) --}}
                        <div>
                            <x-input-label for="link_url" :value="__('Link Materi / Pendaftaran (Opsional)')" />
                            <x-text-input id="link_url" class="block mt-1 w-full" type="url" name="link_url" :value="old('link_url')" placeholder="https://..." />
                            <p class="text-xs text-gray-500 mt-1">Kosongkan jika pelatihan offline atau belum ada link.</p>
                        </div>

                        {{-- Deskripsi --}}
                        <div>
                            <x-input-label for="description" :value="__('Deskripsi Lengkap')" />
                            <textarea id="description" name="description" rows="5" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Jelaskan tujuan, materi yang dipelajari, dan target peserta..." required>{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('lp.katalog.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <x-primary-button class="bg-indigo-600 hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900">
                                {{ __('Simpan & Terbitkan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-lp-layout>