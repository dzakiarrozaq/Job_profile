{{-- File: resources/views/profile/minat.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('profile.edit') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Profil Saya</a>
            <ion-icon name="chevron-forward-outline" class="mx-2 text-gray-400"></ion-icon>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Posisi yang Diminati') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                 x-data="{
                    interests: {{ Js::from($interests->isEmpty() ? [['position_name' => '', 'interest_level' => 'Sedang']] : $interests) }},
                    
                    addInterest() {
                        this.interests.push({
                            position_name: '',
                            interest_level: 'Sedang'
                        });
                    },
                    
                    removeInterest(index) {
                        this.interests.splice(index, 1);
                    }
                 }">
                 
                <form action="{{ route('profile.interests.update') }}" method="POST" class="p-6 text-gray-900 dark:text-gray-100">
                    @csrf
                    @method('PATCH')

                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Minat Karir</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Masukkan posisi atau jabatan yang Anda minati untuk pengembangan karir masa depan.
                            </p>
                        </div>
                        <button type="button" @click="addInterest()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <ion-icon name="add-outline" class="mr-2 text-lg"></ion-icon>
                            Tambah Posisi
                        </button>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <template x-for="(item, index) in interests" :key="index">
                            <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/50 relative flex items-start gap-4">
                                
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Nama Posisi / Jabatan
                                    </label>
                                    <input type="text" 
                                           x-model="item.position_name" 
                                           :name="'interests['+index+'][position_name]'" 
                                           class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm sm:text-sm" 
                                           placeholder="Contoh: Senior Software Engineer">
                                </div>

                                <div class="w-1/3">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tingkat Minat
                                    </label>
                                    <select x-model="item.interest_level" 
                                            :name="'interests['+index+'][interest_level]'" 
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm sm:text-sm">
                                        <option value="Tinggi">Tinggi</option>
                                        <option value="Sedang">Sedang</option>
                                        <option value="Rendah">Rendah</option>
                                    </select>
                                </div>

                                <div class="pt-6">
                                    <button type="button" @click="removeInterest(index)" class="text-red-500 hover:text-red-700 transition">
                                        <ion-icon name="trash-outline" class="text-xl"></ion-icon>
                                    </button>
                                </div>

                            </div>
                        </template>
                        
                        <div x-show="interests.length === 0" class="text-center py-8 text-gray-500 italic border-2 border-dashed border-gray-200 rounded-lg">
                            Belum ada posisi yang diminati.
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline">
                            {{ __('Batal') }}
                        </a>

                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>