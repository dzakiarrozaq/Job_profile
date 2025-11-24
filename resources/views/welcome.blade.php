<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>DevHub - Job Profile & Training Recommendation</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
        
        <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-50">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="/" class="flex items-center">
                            <ion-icon name="analytics-outline" class="text-3xl text-blue-600"></ion-icon>
                        <span class="text-3xl font-bold  text-gray-900 dark:text-white ml-2">DevHub</span>
                        </a>
                    </div>

                    <div class="flex items-center space-x-3">
                        @if (Route::has('login'))
                            <a
                                href="{{ route('login') }}"
                                class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 transition hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none"
                            >
                                Log in
                            </a>

                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                                >
                                    Register
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            </nav>
        </header>

        <main>
            <div class="relative bg-white dark:bg-gray-800 overflow-hidden">
                <div class="max-w-7xl mx-auto">
                    <div class="relative z-10 pb-16 sm:pb-20 md:pb-24 lg:pb-32 xl:pb-40">
                        <div class="pt-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 lg:pt-20">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                                <div class="text-center lg:text-left">
                                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white sm:text-5xl md:text-6xl">
                                        <span class="block xl:inline">Kembangkan Potensi</span>
                                        <span class="block text-blue-600 xl:inline">Karir Anda.</span>
                                    </h1>
                                    <p class="mt-3 text-base text-gray-500 dark:text-gray-300 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto lg:mx-0">
                                        Selamat datang di DevHub. Identifikasi kesenjangan kompetensi Anda dengan Job Profile yang terstandar dan dapatkan rekomendasi pelatihan berbasis AI untuk percepatan karir Anda.
                                    </p>
                                    <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                                        <div class="rounded-md shadow">
                                            <a href="{{ route('register') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10">
                                                Mulai Sekarang
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="hidden lg:block">
                                    <div class="flex items-center justify-center p-8 bg-gray-100 dark:bg-gray-700 rounded-lg shadow-inner aspect-square">
                                        <ion-icon name="stats-chart-outline" class="text-9xl text-blue-300 dark:text-blue-500"></ion-icon>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <section class="py-20 bg-gray-50 dark:bg-gray-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <h2 class="text-base font-semibold text-blue-600 tracking-wide uppercase">Fitur Utama</h2>
                        <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                            Semua yang Anda Butuhkan untuk Berkembang
                        </p>
                    </div>

                    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="p-6 bg-white dark:bg-gray-700 rounded-lg shadow-lg">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                <ion-icon name="search-outline" class="text-2xl"></ion-icon>
                            </div>
                            <h3 class="mt-5 text-lg font-bold text-gray-900 dark:text-white">Analisis Gap Kompetensi</h3>
                            <p class="mt-2 text-base text-gray-500 dark:text-gray-300">
                                Lakukan self-assessment dan lihat perbandingan langsung antara level Anda dengan standar yang dibutuhkan perusahaan.
                            </p>
                        </div>
                        <div class="p-6 bg-white dark:bg-gray-700 rounded-lg shadow-lg">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                <ion-icon name="sparkles-outline" class="text-2xl"></ion-icon>
                            </div>
                            <h3 class="mt-5 text-lg font-bold text-gray-900 dark:text-white">Rekomendasi AI</h3>
                            <p class="mt-2 text-base text-gray-500 dark:text-gray-300">
                                Dapatkan rekomendasi pelatihan yang personal dan cerdas, langsung ditargetkan untuk menutup kesenjangan kompetensi Anda.
                            </p>
                        </div>
                        <div class="p-6 bg-white dark:bg-gray-700 rounded-lg shadow-lg">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                <ion-icon name="checkbox-outline" class="text-2xl"></ion-icon>
                            </div>
                            <h3 class="mt-5 text-lg font-bold text-gray-900 dark:text-white">Alur Persetujuan Terpusat</h3>
                            <p class="mt-2 text-base text-gray-500 dark:text-gray-300">
                                Ajukan rencana pelatihan Anda dan dapatkan persetujuan dari atasan dan Learning Partner dalam satu alur yang transparan.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-gray-100 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            </div>
        </footer>
    </body>
</html>