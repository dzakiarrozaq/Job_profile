<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <ion-icon name="analytics-outline" class="text-3xl text-blue-600"></ion-icon>
                        <span class="text-3xl font-bold  text-gray-900 dark:text-white ml-2">DevHub</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('katalog')" :active="request()->routeIs('katalog*')">
                        {{ __('Katalog Pelatihan') }}
                    </x-nav-link>
                    <x-nav-link :href="route('riwayat')" :active="request()->routeIs('riwayat*')">
                        {{ __('Riwayat Saya') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Right Side -->
            <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-4">
                <!-- Cart Link -->
                <a href="{{ route('rencana') }}" class="flex items-center text-sm font-medium {{ request()->routeIs('rencana') ? 'text-blue-600' : 'text-gray-700 hover:text-blue-600' }} transition">
                    <ion-icon name="{{ request()->routeIs('rencana') ? 'cart' : 'cart-outline' }}" class="text-2xl mr-1"></ion-icon>
                    Rencana Saya (3)
                </a>

                <!-- Notifications -->
                <a href="#" class="flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                    <ion-icon name="notifications-outline" class="text-2xl mr-1"></ion-icon>
                    <span class="relative">
                        <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                    </span>

                </a>

                @if(Auth::user()->roles->contains('name', 'Supervisor'))
                    <div class="hidden sm:flex sm:items-center ml-4">
                        <a href="{{ route('supervisor.dashboard') }}" 
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-gray-800 hover:bg-gray-700 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                            <ion-icon name="arrow-back-outline" class="mr-1.5 text-lg"></ion-icon>
                            {{ __('Kembali ke Supervisor') }}
                        </a>
                    </div>
                @endif

                <!-- Profile Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center space-x-2 rounded-full p-1 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition ease-in-out duration-150">
                            <img class="h-9 w-9 rounded-full object-cover border-2 border-gray-200" src="https://i.pravatar.cc/150?u={{ Auth::user()->name }}" alt="Foto Profil">
                            <svg class="fill-current h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ Auth::user()->email }}</p>
                        </div>
                        
                        <x-dropdown-link :href="route('profile.edit')">
                            <ion-icon name="person-outline" class="text-base mr-2"></ion-icon>
                            {{ __('Profil Saya') }}
                        </x-dropdown-link>

                        <div class="border-t border-gray-100"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();"
                                    class="text-red-600 hover:bg-red-50">
                                <ion-icon name="log-out-outline" class="text-base mr-2"></ion-icon>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <ion-icon name="home-outline" class="text-lg mr-2"></ion-icon>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('katalog')" :active="request()->routeIs('katalog*')">
                <ion-icon name="book-outline" class="text-lg mr-2"></ion-icon>
                {{ __('Katalog Pelatihan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('riwayat')" :active="request()->routeIs('riwayat*')">
                <ion-icon name="time-outline" class="text-lg mr-2"></ion-icon>
                {{ __('Riwayat Saya') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('rencana')" :active="request()->routeIs('rencana*')">
                <ion-icon name="cart-outline" class="text-lg mr-2"></ion-icon>
                {{ __('Rencana Saya (3)') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive User Info -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="flex items-center">
                    <img class="h-10 w-10 rounded-full object-cover" src="https://i.pravatar.cc/150?u={{ Auth::user()->name }}" alt="Foto Profil">
                    <div class="ml-3">
                        <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                        <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profil Saya') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>