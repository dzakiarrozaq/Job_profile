{{-- File: resources/views/components/supervisor-layout.blade.php --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100 dark:bg-gray-900">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'DevHub') }} - Supervisor</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

        <style>
            /* Style kustom untuk sidebar Supervisor (Sesuai Mockup) */
            body { 
                font-family: 'Instrument Sans', sans-serif;
            }
            .sidebar-link { 
                display: flex; 
                align-items: center; 
                padding: 0.75rem 1rem; 
                border-radius: 0.5rem; 
                color: #d1d5db; /* text-gray-300 */
                transition: all 0.2s; 
                font-size: 0.875rem; /* text-sm */
                font-weight: 500;
            }
            .sidebar-link:hover { 
                background-color: #374151; /* hover:bg-gray-700 */
                color: white; 
            }
            .sidebar-link.active { 
                background-color: #4338CA; /* bg-indigo-700 (SesuAI Mockup) */
                color: white; 
                font-weight: 600; 
            }
            .sidebar-link ion-icon { 
                font-size: 1.25rem; /* text-xl */
                margin-right: 0.75rem; /* mr-3 */
            }
            .sidebar-heading { 
                font-size: 0.75rem; /* text-xs */
                color: #9CA3AF; /* text-gray-400 */
                font-weight: 600; 
                text-transform: uppercase; 
                padding: 0.5rem 1rem; 
                margin-top: 0.75rem; /* 12px - Lebih rapat */
            }
        </style>
    </head>
    <body class="h-full font-sans antialiased">
        
        <div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
            
            <aside class="w-64 h-screen sticky top-0 flex-shrink-0 bg-[#1F2937] text-white flex flex-col overflow-y-auto">
                <div class="p-4 flex-1 flex flex-col">
                    
                    <div class="flex items-center mb-6 px-2 pt-2">
                        <ion-icon name="analytics-outline" class="text-3xl text-indigo-400 mr-2"></ion-icon>
                        <h1 class="text-xl font-bold tracking-tight text-white">DevHub</h1>
                    </div>
                    
                    <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 p-2.5 bg-gray-900 bg-opacity-50 rounded-lg mb-2 hover:bg-gray-700 transition-colors">
                        <img class="h-10 w-10 rounded-full object-cover" src="https://i.pravatar.cc/150?u={{ Auth::id() }}" alt="Foto Profil">
                        <div>
                            <p class="font-semibold text-sm text-white">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-400">{{ Auth::user()->role->name ?? 'Supervisor' }}</p>
                        </div>
                    </a>
                    
                    <nav class="space-y-1">
                        <h2 class="sidebar-heading">Manajemen Tim</h2>
                        <a href="{{ route('supervisor.dashboard') }}" class="sidebar-link {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
                            <ion-icon name="home-outline"></ion-icon>
                            Dashboard Tim
                        </a>
                        <a href="{{ route('supervisor.persetujuan') }}" class="sidebar-link {{ request()->routeIs('supervisor.persetujuan') ? 'active' : '' }} relative">
                            <ion-icon name="checkbox-outline"></ion-icon>
                            Persetujuan
                        </a>
                        <a href="{{ route('supervisor.tim.index') }}" class="sidebar-link {{ request()->routeIs('supervisor.tim*') ? 'active' : '' }}">
                            <ion-icon name="people-outline"></ion-icon>
                            Anggota Tim
                        </a>
                        <a href="{{ route('supervisor.laporan.index') }}" class="sidebar-link {{ request()->routeIs('supervisor.laporan*') ? 'active' : '' }}">
                            <ion-icon name="bar-chart-outline"></ion-icon>
                            Laporan Kompetensi
                        </a>

                        <a href="{{ route('supervisor.job-profile.index') }}" 
                            class="sidebar-link {{ request()->routeIs('supervisor.job-profile.*') ? 'active' : '' }}">
                            <ion-icon name="briefcase-outline"></ion-icon>
                            Manajemen Job Profile
                        </a>

                        <div class="my-6 border-t border-gray-700"></div>

                        <h2 class="sidebar-heading">Area Pribadi</h2>
                        
                        <a href="{{ route('dashboard') }}" 
                           class="sidebar-link hover:bg-indigo-900 text-indigo-200 group transition-colors">
                            <div class="flex items-center w-full">
                                <div class="bg-indigo-500/20 p-1.5 rounded-md mr-3 group-hover:bg-indigo-500 transition-colors">
                                    <ion-icon name="person-outline" class="text-indigo-300 group-hover:text-white"></ion-icon>
                                </div>
                                <div class="flex-1">
                                    <span class="block font-semibold text-gray-200">Pengembangan Diri</span>
                                    <span class="block text-[10px] text-gray-400 group-hover:text-indigo-200">Masuk ke Mode Karyawan</span>
                                </div>
                                <ion-icon name="arrow-forward-outline" class="text-gray-500 group-hover:text-white"></ion-icon>
                            </div>
                        </a>
                    </nav>

                    <div class="mt-auto pt-4 border-t border-gray-700">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}" 
                               class="sidebar-link" 
                               onclick="event.preventDefault(); this.closest('form').submit();">
                                <ion-icon name="log-out-outline"></ion-icon>
                                Logout
                            </a>
                        </form>
                    </div>
                </div>
            </aside>

            <div class="flex-1 flex flex-col min-h-screen">
                @if (isset($header))
                    <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-10">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main class="flex-1 bg-gray-100 dark:bg-gray-900 p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>