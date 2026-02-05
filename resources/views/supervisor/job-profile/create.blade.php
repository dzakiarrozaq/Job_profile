<x-supervisor-layout>
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #999; }
    </style>

    <x-slot name="header">
        <div class="flex items-center">
            {{-- ROUTE SUPERVISOR --}}
            <a href="{{ route('supervisor.job-profile.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Manajemen Job Profile</a>
            <ion-icon name="chevron-forward-outline" class="mx-2 text-gray-400"></ion-icon>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                Buat Job Profile Baru (Supervisor)
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if($positions->isEmpty())
                <div class="mb-6 p-4 rounded-lg bg-yellow-50 border border-yellow-200 flex gap-3 text-yellow-800">
                    <ion-icon name="warning" class="text-xl mt-0.5 flex-shrink-0"></ion-icon>
                    <div>
                        <h3 class="font-semibold">Data Jabatan Kosong</h3>
                        <p class="text-sm mt-1">
                            Tidak ada jabatan bawahan yang tersedia untuk dibuatkan profil, atau semua sudah memiliki profil.
                        </p>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-lg sm:rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="p-8">
                    <div class="mb-6 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 mb-4">
                            <ion-icon name="briefcase" class="text-2xl"></ion-icon>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pilih Jabatan Target</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Silakan cari dan pilih jabatan bawahan yang ingin dibuatkan profil kompetensinya.
                        </p>
                    </div>

                    
                    @if (session('error'))
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm animate-pulse">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <ion-icon name="alert-circle" class="h-5 w-5 text-red-500"></ion-icon>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-bold text-red-800">Gagal Menyimpan</h3>
                                    <div class="mt-1 text-sm text-red-700">
                                        <p>{{ session('error') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <ion-icon name="close-circle" class="h-5 w-5 text-red-500"></ion-icon>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-bold text-red-800">Terdapat Kesalahan Input</h3>
                                    <ul class="mt-1 list-disc list-inside text-sm text-red-700">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <form action="{{ route('supervisor.job-profile.store') }}" method="POST" id="jobProfileForm">
                        @csrf

                        <div class="mb-6 dropdown-container relative" 
                             x-data="{
                                items: {{ Js::from($positions) }},
                                query: '',
                                selectedId: '',
                                isOpen: false,
                                showError: false,
                                
                                getHierarchy(item) {
                                    let parts = [];
                                    let current = item.organization;
                                    if (current) {
                                        parts.push(current.name);
                                        if (current.parent) {
                                            parts.push(current.parent.name);
                                            if (current.parent.parent) {
                                                parts.push(current.parent.parent.name);
                                            }
                                        }
                                    } else {
                                        return 'Tanpa Organisasi';
                                    }
                                    return parts.join(' ➜ ');
                                },

                                get groupedItems() {
                                    const filtered = this.items.filter(item => {
                                        if (this.query === '') return true;
                                        const searchStr = (item.title + ' ' + this.getHierarchy(item)).toLowerCase();
                                        return searchStr.includes(this.query.toLowerCase());
                                    });

                                    const groups = {};
                                    filtered.forEach(item => {
                                        let rootOrg = 'Lainnya';
                                        if (item.organization) {
                                            let curr = item.organization;
                                            while (curr.parent) { curr = curr.parent; }
                                            rootOrg = curr.name;
                                        }
                                        if (!groups[rootOrg]) groups[rootOrg] = [];
                                        groups[rootOrg].push(item);
                                    });

                                    return Object.keys(groups).sort().reduce((acc, key) => {
                                        acc[key] = groups[key];
                                        return acc;
                                    }, {});
                                },

                                get hasResults() { return Object.keys(this.groupedItems).length > 0; },
                                
                                openDropdown() { this.isOpen = true; },
                                closeDropdown() { this.isOpen = false; },
                                
                                selectItem(item) {
                                    this.selectedId = item.id;
                                    this.query = item.title;
                                    this.isOpen = false;
                                    this.showError = false;
                                },
                                
                                reset() {
                                    this.query = '';
                                    this.selectedId = '';
                                    this.showError = false;
                                    this.openDropdown();
                                },
                                
                                submitForm() {
                                    if (!this.selectedId) { 
                                        this.showError = true;
                                        this.$refs.containerInput.scrollIntoView({behavior: 'smooth', block: 'center'});
                                        return; 
                                    }
                                    
                                    const formTarget = document.getElementById('jobProfileForm');
                                    if(formTarget) {
                                        formTarget.submit();
                                    } else {
                                        alert('Error: Form ID tidak ditemukan!');
                                    }
                                }
                             }"
                             @click.away="closeDropdown()">

                            <div class="relative" x-ref="containerInput">
                                
                                <label for="searchPosisi" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                    Posisi / Jabatan <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <ion-icon name="search-outline" class="text-gray-400 text-lg"></ion-icon>
                                    </div>

                                    <input type="text" id="searchPosisi" name="search_query"
                                           x-model="query" @click="openDropdown()" @focus="openDropdown()" @input="openDropdown()" 
                                           placeholder="Ketik nama posisi..." 
                                           class="w-full pl-10 pr-10 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all cursor-text text-sm"
                                           autocomplete="off">
                                    
                                    <button type="button" x-show="query.length > 0" @click="reset()" x-cloak
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-500 cursor-pointer transition-colors">
                                        <ion-icon name="close-circle" class="text-xl"></ion-icon>
                                    </button>
                                </div>
                                
                                <input type="hidden" name="position_id" x-model="selectedId">

                                <div x-show="isOpen" x-cloak 
                                     class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-xl max-h-60 overflow-y-auto custom-scrollbar">
                                    
                                    <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                                        <template x-for="(deptItems, groupName) in groupedItems" :key="groupName">
                                            <div>
                                                <li class="sticky top-0 z-10 px-4 py-2 bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-600">
                                                    <span x-text="groupName"></span>
                                                </li>

                                                <template x-for="item in deptItems" :key="item.id">
                                                    <li @click="selectItem(item)" 
                                                        class="cursor-pointer px-4 py-3 pl-6 hover:bg-indigo-50 dark:hover:bg-gray-600 transition-colors flex items-center justify-between group border-b border-gray-50 dark:border-gray-600 last:border-0">
                                                        
                                                        <div class="flex flex-col">
                                                            <span x-text="item.title" class="font-bold text-gray-800 dark:text-white text-sm"></span>
                                                            
                                                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                                                                <ion-icon name="git-network-outline" class="text-xs text-indigo-400"></ion-icon>
                                                                <span x-text="getHierarchy(item)" class="font-mono"></span>
                                                            </div>
                                                        </div>

                                                        <span x-show="selectedId == item.id" class="text-indigo-600 font-bold text-xl ml-2 flex-shrink-0">
                                                            <ion-icon name="checkmark"></ion-icon>
                                                        </span>
                                                    </li>
                                                </template>
                                            </div>
                                        </template>

                                        <li x-show="!hasResults" class="px-4 py-4 text-center text-gray-500 italic">
                                            Tidak ada jabatan yang cocok.
                                        </li>
                                    </ul>
                                </div>

                                @error('position_id')
                                    <p class="text-sm text-red-600 mt-2 flex items-center gap-1"><ion-icon name="alert-circle"></ion-icon> {{ $message }}</p>
                                @enderror
                                <p x-show="showError" x-cloak class="text-sm text-red-600 mt-2 flex items-center gap-1 animate-pulse"><ion-icon name="alert-circle"></ion-icon> Mohon pilih jabatan dari list.</p>
                            </div>

                            <div class="flex items-center justify-between pt-6 border-t border-gray-100 dark:border-gray-700 mt-6">
                                {{-- ROUTE SUPERVISOR --}}
                                <a href="{{ route('supervisor.job-profile.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                                    Batal
                                </a>
                                
                                <button type="button" 
                                        @click="submitForm()"
                                        class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                                    Lanjut Langkah 2
                                    <ion-icon name="arrow-forward" class="ml-2 text-base"></ion-icon>
                                </button>
                            </div>

                        </div> 
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-supervisor-layout>