{{-- File: resources/views/components/admin-layout.blade.php --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100 dark:bg-gray-900">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'DevHub') }} - Admin Panel</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

        <style>
            body { font-family: 'Instrument Sans', sans-serif; }
            .sidebar-link { display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: 0.5rem; color: #d1d5db; transition: all 0.2s; font-size: 0.875rem; font-weight: 500; }
            .sidebar-link:hover { background-color: #374151; color: white; }
            .sidebar-link.active { background-color: #4338CA; color: white; font-weight: 600; }
            .sidebar-link ion-icon { font-size: 1.25rem; margin-right: 0.75rem; }
            .sidebar-heading { font-size: 0.75rem; color: #9CA3AF; font-weight: 600; text-transform: uppercase; padding: 0.5rem 1rem; margin-top: 1.5rem; }
        </style>
    </head>
    <body class="h-full font-sans antialiased">
        
        <div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
            
            <aside class="w-64 h-screen sticky top-0 flex-shrink-0 bg-[#1F2937] text-white flex flex-col overflow-y-auto">
                <div class="p-4 flex-1 flex flex-col">
                    
                    {{-- BAGIAN LOGO (DIPERBARUI) --}}
                    <div class="flex items-center mb-6 px-2 pt-2 gap-3">
                        <img src="{{ asset('img/semen_gresik.svg') }}" alt="Logo" class="h-10 w-auto bg-white rounded p-1">
                        <div>
                            <h1 class="text-lg font-bold tracking-tight text-white leading-tight">DevHub</h1>
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Admin Panel</span>
                        </div>
                    </div>
                    {{-- END BAGIAN LOGO --}}
                    
                    <a href="{{ route('supervisor.profile') }}" class="flex items-center space-x-3 p-2.5 bg-gray-900 bg-opacity-50 rounded-lg mb-2 hover:bg-gray-700 transition-colors">
                        <img class="h-10 w-10 rounded-full object-cover" src="https://i.pravatar.cc/150?u={{ Auth::id() }}" alt="Foto Profil">
                        <div>
                            <p class="font-semibold text-sm text-white">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-400">{{ Auth::user()->role->name ?? 'Admin' }}</p>
                        </div>
                    </a>
                    
                    <nav class="space-y-1">
                        <h2 class="sidebar-heading">Manajemen</h2>
                        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <ion-icon name="home-outline"></ion-icon>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }} relative">
                            <ion-icon name="people-outline"></ion-icon>
                            Manajemen Pengguna
                        </a>
                        <a href="{{ route('admin.job-profile.index') }}" class="sidebar-link {{ request()->routeIs('admin.job-profile.*') ? 'active' : '' }}">
                            <ion-icon name="briefcase-outline"></ion-icon>
                            Manajemen Job Profile
                        </a>
                        <a href="{{ route('admin.trainings.index') }}" class="sidebar-link {{ request()->routeIs('admin.trainings.*') ? 'active' : '' }}">
                            <ion-icon name="library-outline"></ion-icon>
                            Katalog Pelatihan
                        </a>
                        <a href="{{ route('admin.positions.index') }}" class="sidebar-link {{ request()->routeIs('admin.positions.*') ? 'active' : '' }}">
                            <ion-icon name="person-outline"></ion-icon>
                            Manajemen Posisi
                        </a>
                        <a href="{{ route('admin.laporan.index') }}" class="sidebar-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                            <ion-icon name="pie-chart-outline"></ion-icon>
                            Laporan Sistem
                        </a>
                        <a href="{{ route('admin.logs.index') }}" class="sidebar-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                            <ion-icon name="receipt-outline"></ion-icon>
                            Log Aktivitas
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