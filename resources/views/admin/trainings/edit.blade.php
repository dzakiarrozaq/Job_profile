<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Katalog Pelatihan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-xl">
                
                {{-- Header Form --}}
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Edit Data Pelatihan</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Perbarui informasi pelatihan yang sudah ada.</p>
                    </div>
                    <a href="{{ route('admin.trainings.index') }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white underline">
                        Batal & Kembali
                    </a>
                </div>

                <div class="p-6">
                    {{-- Form UPDATE --}}
                    <form action="{{ route('admin.trainings.update', $training->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT') {{-- PENTING: Method PUT untuk Update --}}

                        {{-- Judul Pelatihan --}}
                        <div>
                            <x-input-label for="title" :value="__('Judul Pelatihan')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $training->title)" required autofocus />
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
                                    <x-text-input id="provider" class="block w-full pl-10" type="text" name="provider" :value="old('provider', $training->provider)" required />
                                </div>
                                <x-input-error :messages="$errors->get('provider')" class="mt-2" />
                            </div>

                            {{-- Durasi --}}
                            <div>
                                <x-input-label for="duration" :value="__('Estimasi Durasi (Opsional)')" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <ion-icon name="time-outline" class="text-gray-400"></ion-icon>
                                    </div>
                                    <x-text-input id="duration" class="block w-full pl-10" type="text" name="duration" :value="old('duration', $training->duration)" />
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Tipe Pelatihan --}}
                            <div>
                                <x-input-label for="type" :value="__('Tipe Pelatihan')" />
                                <select id="type" name="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="internal" {{ old('type', $training->type) == 'internal' ? 'selected' : '' }}>Internal (Dalam Perusahaan)</option>
                                    <option value="external" {{ old('type', $training->type) == 'external' ? 'selected' : '' }}>External (Pihak Ketiga)</option>
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            {{-- Tingkat Kesulitan --}}
                            <div>
                                <x-input-label for="difficulty" :value="__('Tingkat Kesulitan')" />
                                <select id="difficulty" name="difficulty" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="Beginner" {{ old('difficulty', $training->difficulty) == 'Beginner' ? 'selected' : '' }}>Beginner (Pemula)</option>
                                    <option value="Intermediate" {{ old('difficulty', $training->difficulty) == 'Intermediate' ? 'selected' : '' }}>Intermediate (Menengah)</option>
                                    <option value="Advanced" {{ old('difficulty', $training->difficulty) == 'Advanced' ? 'selected' : '' }}>Advanced (Lanjut)</option>
                                </select>
                                <x-input-error :messages="$errors->get('difficulty')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="link_url" :value="__('Link Materi / Pendaftaran (Opsional)')" />
                            <x-text-input id="link_url" class="block mt-1 w-full" type="url" name="link_url" :value="old('link_url', $training->link_url)" />
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Deskripsi Lengkap')" />
                            <textarea id="description" name="description" rows="5" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>{{ old('description', $training->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('admin.trainings.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <x-primary-button class="bg-indigo-600 hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900">
                                {{ __('Perbarui Data') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-admin-layout>