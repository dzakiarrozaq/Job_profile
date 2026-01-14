<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-50 transition-colors duration-200">
    @php
        $user = Auth::user();
    @endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center group">
                        <ion-icon name="analytics-outline" class="text-3xl text-blue-600 group-hover:text-blue-500 transition"></ion-icon>
                        <span class="text-2xl font-bold text-gray-900 dark:text-white ml-2 tracking-tight">DevHub</span>
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('katalog')" :active="request()->routeIs('katalog*')">
                        {{ __('Katalog Pelatihan') }}
                    </x-nav-link>
                    <x-nav-link :href="route('idp.index')" :active="request()->routeIs('idp*')">
                        {{ __('Individual Development Plan') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-4">
                
                <a href="{{ route('rencana.index') }}" 
                    class="flex items-center text-sm font-medium transition px-3 py-2 rounded-md
                        {{ request()->routeIs('rencana') 
                            ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' 
                            : 'text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    
                    <ion-icon name="{{ request()->routeIs('rencana') ? 'cart' : 'cart-outline' }}" class="text-xl mr-1.5"></ion-icon>
                    
                    Rencana
                    
                    @if(isset($rencanaCount) && $rencanaCount > 0)
                        <span class="ml-1">({{ $rencanaCount }})</span>
                    @endif
                </a>

                <div class="relative ml-3" x-data="{ open: false }">
                    <button @click="open = ! open" class="relative p-1 rounded-full text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="sr-only">View notifications</span>
                        <ion-icon name="notifications-outline" class="text-2xl"></ion-icon>
                        
                        {{-- Badge Merah (Hanya muncul jika ada notif belum dibaca) --}}
                        @if(Auth::user()->unreadNotifications->count() > 0)
                            <span class="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white dark:ring-gray-800 bg-red-500 animate-pulse"></span>
                        @endif
                    </button>

                    {{-- Dropdown Body --}}
                    <div x-show="open" 
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-80 rounded-md shadow-lg py-1 bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50" 
                        style="display: none;">
                        
                        <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Notifikasi</span>
                            @if(Auth::user()->unreadNotifications->count() > 0)
                                <form action="{{ route('notifikasi.bacaSemua') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">Tandai semua dibaca</button>
                                </form>
                            @endif
                        </div>

                        <div class="max-h-64 overflow-y-auto">
                            @forelse(Auth::user()->unreadNotifications as $notification)
                                <a href="{{ route('notifikasi.baca', $notification->id) }}" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition border-b border-gray-100 dark:border-gray-700 last:border-0">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            {{-- Ikon berdasarkan tipe notifikasi (Opsional) --}}
                                            <ion-icon name="information-circle" class="text-indigo-500 text-lg"></ion-icon>
                                        </div>
                                        <div class="ml-3 w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $notification->data['title'] ?? 'Pemberitahuan' }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ $notification->data['message'] ?? '' }}
                                            </p>
                                            <p class="text-[10px] text-gray-400 mt-1">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <ion-icon name="notifications-off-outline" class="text-2xl mb-1"></ion-icon>
                                    <p>Tidak ada notifikasi baru</p>
                                </div>
                            @endforelse
                        </div>
                        
                        {{-- Link Lihat Semua (Opsional) --}}
                        {{-- <a href="#" class="block px-4 py-2 text-xs text-center text-gray-500 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 rounded-b-md">Lihat Semua Riwayat</a> --}}
                    </div>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center space-x-2 rounded-full p-0.5 hover:ring-2 hover:ring-gray-300 dark:hover:ring-gray-600 transition">
                            <img class="h-9 w-9 rounded-full object-cover border border-gray-200 dark:border-gray-600 shadow-sm" 
                                src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                                alt="Foto Profil">
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        
                        <x-dropdown-link :href="route('profile.edit')">
                            <ion-icon name="person-outline" class="text-lg mr-2 align-middle"></ion-icon>
                            {{ __('Profil Saya') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('riwayat')">
                            <ion-icon name="time-outline" class="text-lg mr-2 align-middle"></ion-icon>
                            {{ __('Riwayat Pelatihan') }}
                        </x-dropdown-link>

                        @if(Auth::user()->roles->contains('name', 'Supervisor'))
                            <div class="border-t border-gray-100 dark:border-gray-700"></div>
                            <div class="block px-4 py-2 text-xs text-indigo-500 dark:text-indigo-400 font-bold uppercase">
                                Area Supervisor
                            </div>
                            <x-dropdown-link :href="route('supervisor.dashboard')">
                                <ion-icon name="grid-outline" class="text-lg mr-2 align-middle"></ion-icon>
                                {{ __('Dashboard Tim') }}
                            </x-dropdown-link>
                        @endif

                        <div class="border-t border-gray-100 dark:border-gray-700"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                <ion-icon name="log-out-outline" class="text-lg mr-2 align-middle"></ion-icon>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <ion-icon name="home-outline" class="text-lg mr-2 align-text-bottom"></ion-icon>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('katalog')" :active="request()->routeIs('katalog*')">
                <ion-icon name="book-outline" class="text-lg mr-2 align-text-bottom"></ion-icon>
                {{ __('Katalog Pelatihan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('idp.index')" :active="request()->routeIs('idp*')">
                <ion-icon name="document-text-outline" class="text-lg mr-2 align-text-bottom"></ion-icon>
                {{ __('IDP Saya') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('rencana.index')" :active="request()->routeIs('rencana*')">
                <ion-icon name="cart-outline" class="text-lg mr-2 align-text-bottom"></ion-icon>
                {{ __('Rencana Saya (3)') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
            <div class="px-4 flex items-center">
                <div class="flex-shrink-0">
                    <img class="h-10 w-10 rounded-full object-cover border border-gray-300 dark:border-gray-600" src="https://i.pravatar.cc/150?u={{ Auth::user()->name }}" alt="Foto">
                </div>
                <div class="ml-3">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profil Saya') }}
                </x-responsive-nav-link>
                
                @if(Auth::user()->roles->contains('name', 'Supervisor'))
                    <x-responsive-nav-link :href="route('supervisor.dashboard')" class="text-indigo-600 dark:text-indigo-400">
                        {{ __('Dashboard Supervisor') }}
                    </x-responsive-nav-link>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();"
                            class="text-red-600 dark:text-red-400">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>