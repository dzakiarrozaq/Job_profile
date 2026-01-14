<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'DevHub') }} - Job Profile & Training</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

        <style>
            body { font-family: 'Plus Jakarta Sans', sans-serif; }
            .blob { position: absolute; filter: blur(40px); z-index: -1; opacity: 0.4; animation: float 10s infinite ease-in-out; }
            @keyframes float { 0%, 100% { transform: translateY(0) scale(1); } 50% { transform: translateY(-20px) scale(1.05); } }
            .animation-delay-2000 { animation-delay: 2s; }
            .animation-delay-4000 { animation-delay: 4s; }
        </style>
    </head>
    <body class="antialiased bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 overflow-x-hidden">
        
        <nav class="fixed w-full z-50 transition-all duration-300 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-100 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20 items-center">
                    <div class="flex items-center gap-2">
                        <div class="bg-blue-600 p-1.5 rounded-lg text-white">
                            <ion-icon name="analytics" class="text-2xl"></ion-icon>
                        </div>
                        <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">DevHub</span>
                    </div>

                    <div class="hidden md:flex items-center space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition">Sign in</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-full hover:bg-blue-700 shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-0.5">
                                        Sign Up
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </div>

                    <div class="md:hidden flex items-center">
                        <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-300 text-2xl">
                            <ion-icon name="menu-outline"></ion-icon>
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <section class="relative pt-32 pb-20 lg:pt-40 lg:pb-28 overflow-hidden">
            <div class="blob bg-blue-400 w-96 h-96 rounded-full top-0 left-0 -translate-x-1/2 -translate-y-1/2"></div>
            <div class="blob bg-purple-400 w-96 h-96 rounded-full bottom-0 right-0 translate-x-1/2 translate-y-1/2 animation-delay-2000"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-8 items-center">
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center px-3 py-1 rounded-full border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-semibold uppercase tracking-wide mb-6">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 animate-pulse"></span>
                            Platform Pengembangan Karir Semen Gresik
                        </div>
                        <h1 class="text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight mb-6 text-gray-900 dark:text-white">
                            Kembangkan Potensi <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400">Karir Profesional Anda</span>
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-300 mb-8 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                            Identifikasi kesenjangan kompetensi dengan Job Profile standar industri. Dapatkan rekomendasi pelatihan berbasis AI untuk akselerasi karir yang terukur.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <a href="{{ route('register') }}" class="px-8 py-4 text-base font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-xl shadow-blue-600/20 transition transform hover:-translate-y-1">
                                Daftar Sekarang
                            </a>
                            <a href="#features" class="px-8 py-4 text-base font-bold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center justify-center gap-2">
                                <ion-icon name="play-circle-outline" class="text-xl"></ion-icon>
                                Pelajari Cara Kerja
                            </a>
                        </div>
                        
                        <div class="mt-10 pt-8 border-t border-gray-100 dark:border-gray-800 flex justify-center lg:justify-start gap-8">
                            <div>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white">500+</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Modul Pelatihan</p>
                            </div>
                            <div>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white">98%</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tingkat Kepuasan</p>
                            </div>
                            <div>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white">24/7</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Akses Sistem</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative lg:block">
                        <div class="relative rounded-2xl bg-gradient-to-br from-gray-900 to-gray-800 p-4 shadow-2xl rotate-2 hover:rotate-0 transition duration-500 border border-gray-700">
                            <div class="flex items-center gap-2 mb-4 border-b border-gray-700 pb-3">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div class="col-span-2 h-24 rounded-lg bg-gray-700/50 animate-pulse"></div>
                                <div class="col-span-1 h-24 rounded-lg bg-blue-600/20 border border-blue-500/30"></div>
                            </div>
                            <div class="h-8 w-1/2 rounded bg-gray-700/50 mb-4"></div>
                            <div class="space-y-2">
                                <div class="h-4 w-full rounded bg-gray-700/30"></div>
                                <div class="h-4 w-5/6 rounded bg-gray-700/30"></div>
                                <div class="h-4 w-4/6 rounded bg-gray-700/30"></div>
                            </div>
                            <div class="absolute -bottom-6 -left-6 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 flex items-center gap-3 animate-bounce" style="animation-duration: 3s;">
                                <div class="bg-green-100 p-2 rounded-full text-green-600">
                                    <ion-icon name="checkmark-circle" class="text-2xl"></ion-icon>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Status</p>
                                    <p class="font-bold text-sm">Kompetensi Tercapai</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="py-24 bg-gray-50 dark:bg-gray-800/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Fitur Unggulan</h2>
                    <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                        Teknologi untuk Pertumbuhan Anda
                    </p>
                    <p class="mt-4 text-xl text-gray-500 dark:text-gray-400">
                        Platform terintegrasi yang menghubungkan profil pekerjaan, penilaian kompetensi, dan pembelajaran dalam satu ekosistem.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 relative overflow-hidden">
                        <div class="absolute top-0 right-0 bg-blue-50 dark:bg-gray-700 w-24 h-24 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                        <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600 mb-6 relative z-10">
                            <ion-icon name="scan-outline" class="text-3xl"></ion-icon>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Analisis Gap Kompetensi</h3>
                        <p class="text-gray-500 dark:text-gray-400 leading-relaxed">
                            Bandingkan skill Anda saat ini dengan standar industri. Visualisasikan area kekuatan dan area yang perlu ditingkatkan secara real-time.
                        </p>
                    </div>

                    <div class="group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 relative overflow-hidden">
                        <div class="absolute top-0 right-0 bg-purple-50 dark:bg-gray-700 w-24 h-24 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                        <div class="w-14 h-14 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center text-purple-600 mb-6 relative z-10">
                            <ion-icon name="bulb-outline" class="text-3xl"></ion-icon>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Rekomendasi Cerdas</h3>
                        <p class="text-gray-500 dark:text-gray-400 leading-relaxed">
                            Algoritma kami menyarankan modul pelatihan spesifik yang paling berdampak untuk menutup gap kompetensi Anda secara efisien.
                        </p>
                    </div>

                    <div class="group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 relative overflow-hidden">
                        <div class="absolute top-0 right-0 bg-green-50 dark:bg-gray-700 w-24 h-24 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                        <div class="w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center text-green-600 mb-6 relative z-10">
                            <ion-icon name="git-network-outline" class="text-3xl"></ion-icon>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Alur Approval Digital</h3>
                        <p class="text-gray-500 dark:text-gray-400 leading-relaxed">
                            Hilangkan birokrasi kertas. Ajukan rencana pelatihan dan dapatkan persetujuan Supervisor & Learning Partner dalam hitungan klik.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative rounded-3xl overflow-hidden bg-blue-600 shadow-2xl">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-700"></div>
                    <div class="absolute top-0 left-0 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-white opacity-10 rounded-full"></div>
                    <div class="absolute bottom-0 right-0 translate-x-1/2 translate-y-1/2 w-64 h-64 bg-white opacity-10 rounded-full"></div>
                    
                    <div class="relative z-10 px-8 py-16 text-center">
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Siap Mengakselerasi Karir Anda?</h2>
                        <p class="text-blue-100 text-lg mb-8 max-w-2xl mx-auto">
                            Bergabunglah dengan ribuan profesional yang telah meningkatkan kompetensi mereka melalui DevHub.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ route('register') }}" class="px-8 py-4 bg-white text-blue-600 font-bold rounded-xl hover:bg-gray-50 transition shadow-lg">
                                Buat Akun 
                            </a>
                            <a href="{{ route('login') }}" class="px-8 py-4 bg-blue-700 text-white font-bold rounded-xl hover:bg-blue-800 transition shadow-lg border border-blue-500">
                                Masuk Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="bg-gray-900 text-white border-t border-gray-800 pt-16 pb-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                    <div class="col-span-1 md:col-span-1">
                        <div class="flex items-center gap-2 mb-4">
                            <ion-icon name="analytics" class="text-2xl text-blue-500"></ion-icon>
                            <span class="text-2xl font-bold">DevHub</span>
                        </div>
                        <p class="text-gray-400 text-sm leading-relaxed">
                            Platform manajemen kompetensi dan pelatihan terpadu untuk profesional modern.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-4">Produk</h4>
                        <ul class="space-y-2 text-gray-400 text-sm">
                            <li><a href="#" class="hover:text-blue-400 transition">Job Profile</a></li>
                            <li><a href="#" class="hover:text-blue-400 transition">Assessment</a></li>
                            <li><a href="#" class="hover:text-blue-400 transition">Training Catalog</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-4">Perusahaan</h4>
                        <ul class="space-y-2 text-gray-400 text-sm">
                            <li><a href="#" class="hover:text-blue-400 transition">Tentang Kami</a></li>
                            <li><a href="#" class="hover:text-blue-400 transition">Karir</a></li>
                            <li><a href="#" class="hover:text-blue-400 transition">Kontak</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-4">Hubungi Kami</h4>
                        <ul class="space-y-2 text-gray-400 text-sm">
                            <li class="flex items-center gap-2"><ion-icon name="mail-outline"></ion-icon> support@devhub.com</li>
                            <li class="flex items-center gap-2"><ion-icon name="call-outline"></ion-icon> +62 21 555 0123</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm text-gray-500">
                    <p>&copy; {{ date('Y') }} DevHub. All rights reserved.</p>
                    <div class="flex space-x-4 mt-4 md:mt-0">
                        <a href="#" class="hover:text-white transition"><ion-icon name="logo-linkedin" class="text-xl"></ion-icon></a>
                        <a href="#" class="hover:text-white transition"><ion-icon name="logo-instagram" class="text-xl"></ion-icon></a>
                        <a href="#" class="hover:text-white transition"><ion-icon name="logo-twitter" class="text-xl"></ion-icon></a>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>