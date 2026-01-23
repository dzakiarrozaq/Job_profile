<x-admin-layout>
    {{-- Style agar elemen Alpine tidak kedip saat loading --}}
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #999; }

        .dropdown-container { position: relative; z-index: 50; }
        .dropdown-menu { position: absolute; z-index: 9999 !important; }
    </style>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Job Profile Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            {{-- JIKA DATA KOSONG --}}
            @if($positions->isEmpty())
                <div class="mb-6 p-4 rounded-lg bg-yellow-50 border border-yellow-200 flex gap-3 text-yellow-800">
                    <ion-icon name="warning" class="text-xl mt-0.5 flex-shrink-0"></ion-icon>
                    <div>
                        <h3 class="font-semibold">Data Jabatan Kosong</h3>
                        <p class="text-sm mt-1">Pastikan Anda memiliki data jabatan (Positions) di database.</p>
                    </div>
                </div>
            @endif

            {{-- CARD FORM --}}
            <div class="bg-white dark:bg-gray-800 shadow-lg sm:rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="p-8">
                    <div class="mb-6 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 mb-4">
                            <ion-icon name="briefcase" class="text-2xl"></ion-icon>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pilih Jabatan Target</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Silakan cari dan pilih jabatan yang ingin dibuatkan profil kompetensinya.
                        </p>
                    </div>

                    <form action="{{ route('admin.job-profile.store') }}" method="POST" id="jobProfileForm">
                        @csrf

                        {{-- CUSTOM SEARCHABLE DROPDOWN --}}
                        <div class="mb-6 dropdown-container" 
                             x-data="{
                                items: {{ Js::from($positions) }},
                                query: '',
                                selectedId: '',
                                isOpen: false,
                                showError: false,
                                
                                // 1. LOGIKA UTAMA: Filter dulu, baru Grouping
                                get groupedItems() {
                                    // Step A: Filter berdasarkan ketikan user
                                    const filtered = this.items.filter(item => {
                                        return item.title.toLowerCase().includes(this.query.toLowerCase());
                                    });

                                    // Step B: Grouping by Department Name
                                    const groups = {};
                                    filtered.forEach(item => {
                                        // Cek nama departemen, jika null masukkan ke 'Lainnya'
                                        const deptName = item.department ? item.department.name : 'Unit Umum / Lainnya';
                                        
                                        if (!groups[deptName]) {
                                            groups[deptName] = [];
                                        }
                                        groups[deptName].push(item);
                                    });

                                    // Return object yang sudah dikelompokkan
                                    // Urutkan key (nama departemen) secara alfabetis
                                    return Object.keys(groups).sort().reduce((acc, key) => {
                                        acc[key] = groups[key];
                                        return acc;
                                    }, {});
                                },

                                // Helper untuk cek apakah hasil pencarian kosong
                                get hasResults() {
                                    return Object.keys(this.groupedItems).length > 0;
                                },
                                
                                openDropdown() { this.isOpen = true; },
                                closeDropdown() { this.isOpen = false; },
                                
                                selectItem(item) {
                                    this.selectedId = item.id;
                                    this.query = item.title; // Tampilkan nama jabatan di input
                                    this.isOpen = false;
                                    this.showError = false;
                                },
                                
                                reset() {
                                    this.query = '';
                                    this.selectedId = '';
                                    this.showError = false;
                                    this.openDropdown();
                                },
                                
                                validateAndSubmit() {
                                    if (!this.selectedId) {
                                        this.showError = true;
                                        return;
                                    }
                                    document.getElementById('jobProfileForm').submit();
                                }
                             }"
                             @click.away="closeDropdown()">
                            
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Posisi / Jabatan <span class="text-red-500">*</span>
                            </label>

                            {{-- WRAPPER INPUT --}}
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <ion-icon name="search-outline" class="text-gray-400 text-lg"></ion-icon>
                                </div>

                                <input 
                                    type="text" 
                                    x-model="query"
                                    @click="openDropdown()"
                                    @focus="openDropdown()"
                                    @input="openDropdown()" 
                                    placeholder="Ketik atau klik untuk memilih jabatan..." 
                                    class="w-full pl-10 pr-10 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all cursor-text"
                                    autocomplete="off"
                                >

                                {{-- Tombol Clear --}}
                                <button type="button" 
                                        x-show="query.length > 0" 
                                        @click="reset()"
                                        x-cloak
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-500 cursor-pointer transition-colors">
                                    <ion-icon name="close-circle" class="text-xl"></ion-icon>
                                </button>
                            </div>

                            <input type="hidden" name="position_id" x-model="selectedId">

                            {{-- DROPDOWN LIST (GROUPED) --}}
                            <div x-show="isOpen"
                                x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="dropdown-menu w-full mt-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-xl max-h-60 overflow-y-auto custom-scrollbar">
                                
                                <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                                    
                                    {{-- LOOP 1: Nama Departemen (Key) --}}
                                    <template x-for="(deptItems, deptName) in groupedItems" :key="deptName">
                                        <div>
                                            {{-- Header Departemen (Sticky) --}}
                                            <li class="sticky top-0 z-10 px-4 py-2 bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-600">
                                                <span x-text="deptName"></span>
                                            </li>

                                            {{-- LOOP 2: Item Jabatan dalam Departemen tersebut --}}
                                            <template x-for="item in deptItems" :key="item.id">
                                                <li @click="selectItem(item)" 
                                                    class="cursor-pointer px-4 py-3 pl-6 hover:bg-indigo-50 dark:hover:bg-gray-600 transition-colors flex items-center justify-between group border-b border-gray-50 dark:border-gray-600 last:border-0">
                                                    
                                                    <div>
                                                        <span x-text="item.title" class="font-semibold block text-gray-800 dark:text-white group-hover:text-indigo-700 dark:group-hover:text-indigo-300"></span>
                                                    </div>

                                                    {{-- Centang --}}
                                                    <span x-show="selectedId == item.id" class="text-indigo-600 font-bold text-xl">
                                                        <ion-icon name="checkmark"></ion-icon>
                                                    </span>
                                                </li>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Jika Tidak Ada Hasil --}}
                                    <li x-show="!hasResults" class="px-4 py-4 text-center text-gray-500 italic">
                                        Tidak ada jabatan yang cocok dengan "<span x-text="query" class="font-bold"></span>"
                                    </li>
                                </ul>
                            </div>

                            @error('position_id')
                                <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                                    <ion-icon name="alert-circle"></ion-icon> {{ $message }}
                                </p>
                            @enderror
                            
                            <p x-show="showError" x-cloak class="text-sm text-red-600 mt-2 flex items-center gap-1 animate-pulse">
                                <ion-icon name="alert-circle"></ion-icon> Mohon pilih jabatan dari daftar yang tersedia.
                            </p>
                        </div>

                        {{-- FOOTER BUTTONS --}}
                        <div class="flex items-center justify-between pt-6 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('admin.job-profile.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                                Batal
                            </a>
                            
                            <button type="button" 
                                    @click="validateAndSubmit()"
                                    class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                                Lanjut Langkah 2
                                <ion-icon name="arrow-forward" class="ml-2 text-base"></ion-icon>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>