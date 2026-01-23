<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
                    Manajemen Pengguna
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Kelola data akses, peran, dan status karyawan.
                </p>
            </div>
            <a href="{{ route('admin.users.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-indigo-500/30">
                <ion-icon name="person-add-outline" class="mr-2 text-lg"></ion-icon>
                Tambah User
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center">
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-full text-blue-600 dark:text-blue-400 mr-4">
                    <ion-icon name="people-outline" class="text-2xl"></ion-icon>
                </div>
                <div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Users</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white">{{ $users->total() }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('admin.users.index') }}" class="w-full">
                <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    
                    <div class="relative flex-1 w-full group">
                        <ion-icon name="search-outline" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-indigo-500 transition-colors"></ion-icon>
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Cari berdasarkan nama, email, atau NIK..." 
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm">
                    </div>

                    <div x-data="{ open: false }" class="relative w-full md:w-auto">
                        <button @click="open = !open" type="button" 
                                class="w-full md:w-auto flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg border border-gray-300 dark:border-gray-600 transition-all shadow-sm">
                            <ion-icon name="filter-outline"></ion-icon>
                            <span class="font-medium text-sm">Filter</span>
                            @if(request('role') || request('status'))
                                <span class="flex h-2.5 w-2.5 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-indigo-500"></span>
                                </span>
                            @endif
                            <ion-icon :name="open ? 'chevron-up-outline' : 'chevron-down-outline'" class="text-xs ml-1 opacity-70"></ion-icon>
                        </button>

                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-2"
                             class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 z-50 p-5"
                             style="display: none;">
                            
                             <div class="space-y-5">
                                <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-3">
                                    <h3 class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                        <ion-icon name="options-outline"></ion-icon> Filter Data
                                    </h3>
                                    @if(request('role') || request('status') || request('search'))
                                        <a href="{{ route('admin.users.index') }}" class="text-xs text-red-500 hover:text-red-700 font-medium hover:underline">Reset All</a>
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider">Status Akun</label>
                                    <select name="status" class="w-full text-sm rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 py-2">
                                        <option value="">Semua Status</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider">Role / Jabatan</label>
                                    <select name="role" class="w-full text-sm rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 py-2">
                                        <option value="">Semua Role</option>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ $user->role->name ?? '-' }}
                                        </span>
                                    </select>
                                </div>

                                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2.5 rounded-lg transition-colors shadow-md shadow-indigo-200 dark:shadow-none">
                                    Terapkan Filter
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="hidden md:flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all font-medium shadow-md shadow-indigo-200 dark:shadow-none">
                        <ion-icon name="search"></ion-icon> Cari
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User Info</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jabatan</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition-colors duration-200 group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full object-cover border-2 border-white dark:border-gray-600 shadow-sm" 
                                             src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=6366f1&background=e0e7ff' }}" 
                                             alt="{{ $user->name }}">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 transition-colors">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500 font-mono mt-0.5">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @if(!empty($user->roles) && $user->roles->count() > 0)
                                        @foreach($user->roles as $role)
                                            @php
                                                $colorClass = match($role->name) {
                                                    'Admin' => 'bg-red-100 text-red-700 border-red-200',
                                                    'Supervisor' => 'bg-purple-100 text-purple-700 border-purple-200',
                                                    default => 'bg-blue-100 text-blue-700 border-blue-200'
                                                };
                                            @endphp
                                            <span class="px-2.5 py-0.5 inline-flex text-xs font-medium rounded-full border {{ $colorClass }}">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $user->position->title ?? '-' }}</span>
                                    <span class="text-xs text-gray-400">{{ $user->department->name ?? 'Unit belum diset' }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($user->status == 'active')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-3 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Edit User">
                                        <ion-icon name="create-outline" class="text-xl"></ion-icon>
                                    </a>
                                    
                                    @if(Auth::id() !== $user->id)
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus user ini? Data yang dihapus tidak bisa dikembalikan.');" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Hapus User">
                                                <ion-icon name="trash-outline" class="text-xl"></ion-icon>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center bg-gray-50/50 dark:bg-gray-800">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-full mb-3">
                                        <ion-icon name="search-outline" class="text-4xl text-gray-400"></ion-icon>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tidak ada pengguna ditemukan</h3>
                                    <p class="text-gray-500 text-sm mt-1 max-w-xs mx-auto">Coba sesuaikan kata kunci pencarian atau filter status dan role Anda.</p>
                                    <a href="{{ route('admin.users.index') }}" class="mt-4 text-indigo-600 hover:text-indigo-800 text-sm font-medium">Reset Filter</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
                <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>