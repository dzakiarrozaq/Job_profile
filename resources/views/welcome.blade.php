<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'DevHub') }} - Semen Gresik</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

        <style>
            body { font-family: 'Plus Jakarta Sans', sans-serif; }
            
            /* Animasi Halus */
            .fade-in-up { animation: fadeInUp 0.8s ease-out forwards; opacity: 0; transform: translateY(20px); }
            @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
            .delay-200 { animation-delay: 0.2s; }
            .delay-400 { animation-delay: 0.4s; }
        </style>
    </head>
    <body class="antialiased bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 overflow-x-hidden">
        
        <nav x-data="{ scrolled: false }" 
             @scroll.window="scrolled = (window.pageYOffset > 20)"
             :class="{ 'bg-white/90 dark:bg-gray-900/90 shadow-md backdrop-blur-md': scrolled, 'bg-transparent': !scrolled }"
             class="fixed w-full z-50 transition-all duration-300 border-b border-transparent"
             :class="{ 'border-gray-200 dark:border-gray-800': scrolled }">
             
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-24 items-center">
                    <div class="flex items-center gap-4">
                        <div class="bg-white p-1.5 rounded-lg shadow-sm">
                            <img src="{{ asset('img/semen_gresik.svg') }}" alt="Semen Gresik" class="h-12 w-auto">
                        </div>
                        <div class="hidden md:block">
                            <span class="block text-2xl font-bold tracking-tight leading-none" 
                                  :class="{ 'text-gray-900 dark:text-white': scrolled, 'text-white': !scrolled }">
                                DevHub
                            </span>
                            <span class="block text-xs font-medium tracking-wider uppercase opacity-80"
                                  :class="{ 'text-gray-600 dark:text-gray-400': scrolled, 'text-blue-100': !scrolled }">
                                Semen Gresik HC System
                            </span>
                        </div>
                    </div>

                    <div class="hidden md:flex items-center space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-6 py-2.5 text-sm font-bold bg-white text-blue-800 rounded-full hover:bg-gray-100 transition shadow-lg">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm font-bold transition hover:opacity-80"
                                   :class="{ 'text-gray-700 dark:text-white': scrolled, 'text-white': !scrolled }">
                                   Masuk
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="px-6 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-full hover:bg-red-700 shadow-lg shadow-red-600/30 transition transform hover:-translate-y-0.5 border-2 border-transparent">
                                        Daftar Akun
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <section class="relative min-h-[100vh] flex items-center justify-center overflow-hidden bg-slate-950 font-sans">
    
            <div class="absolute inset-0 z-0 pointer-events-none">
                
                <div class="absolute inset-0 bg-slate-950"></div>

                <div class="absolute inset-0 bg-[linear-gradient(to_right,#8080800a_1px,transparent_1px),linear-gradient(to_bottom,#8080800a_1px,transparent_1px)] bg-[size:40px_40px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>

                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[80vw] h-[50vh] bg-blue-600/30 rounded-full blur-[120px] opacity-70"></div>

                <div class="absolute top-1/2 right-0 w-[40vw] h-[60vh] bg-cyan-500/10 rounded-full blur-[100px] opacity-50"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20 w-full mt-10">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    
                    <div class="text-center lg:text-left">
                        
                        <div class="mb-8 fade-in-up">
                            <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full border border-slate-700 bg-slate-900/50 backdrop-blur-md">
                                <span class="flex h-2.5 w-2.5 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-500"></span>
                                </span>
                                <span class="text-xs font-bold tracking-widest text-slate-300 uppercase">Internal Development Platform</span>
                            </div>
                        </div>
                        
                        <h1 class="text-5xl lg:text-7xl font-bold tracking-tight text-white mb-6 leading-[1.1] drop-shadow-2xl">
                            Membangun <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">
                                Masa Depan Kokoh.
                            </span>
                        </h1>
                        
                        <p class="text-lg text-slate-400 mb-10 leading-relaxed max-w-lg mx-auto lg:mx-0 font-normal">
                            Platform pengembangan karir digital PT Semen Gresik. Tingkatkan kompetensi, kelola performa, dan raih sertifikasi dalam satu ekosistem modern.
                        </p>
                        
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <a href="{{ route('login') }}" class="group relative px-8 py-4 bg-blue-600 text-white font-semibold rounded-full shadow-[0_0_40px_-10px_rgba(37,99,235,0.5)] hover:shadow-[0_0_60px_-15px_rgba(37,99,235,0.7)] transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                                <span class="flex items-center gap-2">
                                    Masuk Sekarang <ion-icon name="arrow-forward"></ion-icon>
                                </span>
                            </a>

                            <a href="#features" class="px-8 py-4 text-slate-300 font-medium rounded-full border border-slate-700 bg-slate-800/30 hover:bg-slate-700/50 hover:text-white transition-all duration-300 flex items-center gap-2 backdrop-blur-sm">
                                <ion-icon name="play-circle-outline" class="text-xl"></ion-icon>
                                Pelajari Fitur
                            </a>
                        </div>
                    </div>

                    <div class="hidden lg:block relative">
                        <div class="relative bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl">
                            
                            <div class="flex items-center justify-between mb-10">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white">
                                        <ion-icon name="person" class="text-xl"></ion-icon>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="h-2.5 w-24 bg-slate-700 rounded-full"></div>
                                        <div class="h-2 w-16 bg-slate-800 rounded-full"></div>
                                    </div>
                                </div>
                                <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-400 text-xs font-bold border border-green-500/20">Active</span>
                            </div>

                            <div class="mb-10">
                                <div class="flex justify-between text-sm text-slate-400 mb-3">
                                    <span>Kompetensi Teknik</span>
                                    <span class="text-blue-400 font-bold">85%</span>
                                </div>
                                <div class="h-3 w-full bg-slate-800 rounded-full overflow-hidden">
                                    <div class="h-full w-[85%] bg-gradient-to-r from-blue-600 to-cyan-400 rounded-full shadow-[0_0_20px_rgba(59,130,246,0.5)]"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-800/50 p-5 rounded-2xl border border-slate-700/50">
                                    <ion-icon name="trophy" class="text-yellow-500 text-2xl mb-2"></ion-icon>
                                    <div class="text-2xl font-bold text-white">12</div>
                                    <div class="text-xs text-slate-500 mt-1">Sertifikat</div>
                                </div>
                                <div class="bg-slate-800/50 p-5 rounded-2xl border border-slate-700/50">
                                    <ion-icon name="trending-up" class="text-cyan-400 text-2xl mb-2"></ion-icon>
                                    <div class="text-2xl font-bold text-white">Top 5%</div>
                                    <div class="text-xs text-slate-500 mt-1">Ranking</div>
                                </div>
                            </div>

                            <div class="absolute -bottom-6 -left-6 bg-slate-800 p-4 rounded-2xl shadow-xl border border-slate-700 flex items-center gap-4 animate-bounce">
                                <div class="bg-blue-600 p-2 rounded-lg text-white shadow-lg">
                                    <ion-icon name="checkmark-done" class="text-xl"></ion-icon>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">APPROVAL</p>
                                    <p class="font-bold text-white text-sm">Rencana Disetujui</p>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        <section id="features" class="py-24 bg-white dark:bg-gray-900 relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-base text-red-600 font-bold tracking-widest uppercase">Keunggulan Sistem</h2>
                    <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                        Pondasi Karir yang Kuat
                    </p>
                    <p class="mt-4 text-xl text-gray-500 dark:text-gray-400">
                        Teknologi presisi untuk memastikan standar kualitas SDM PT Semen Gresik tetap kokoh tak tertandingi.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="group p-8 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:border-red-500 hover:shadow-xl transition duration-300 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-red-100 dark:bg-red-900/20 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
                        <div class="w-14 h-14 bg-white dark:bg-gray-700 rounded-xl shadow-sm flex items-center justify-center text-red-600 text-3xl mb-6 relative z-10">
                            <ion-icon name="construct"></ion-icon>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Konstruksi Kompetensi</h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            Analisis Gap yang mendalam untuk membangun struktur keahlian karyawan yang sesuai dengan standar industri semen.
                        </p>
                    </div>

                    <div class="group p-8 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:border-blue-500 hover:shadow-xl transition duration-300 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-blue-100 dark:bg-blue-900/20 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
                        <div class="w-14 h-14 bg-white dark:bg-gray-700 rounded-xl shadow-sm flex items-center justify-center text-blue-600 text-3xl mb-6 relative z-10">
                            <ion-icon name="trending-up"></ion-icon>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Akselerasi Kinerja</h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            Rekomendasi pelatihan cerdas untuk meningkatkan produktivitas dan efisiensi operasional perusahaan.
                        </p>
                    </div>

                    <div class="group p-8 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:border-green-500 hover:shadow-xl transition duration-300 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-green-100 dark:bg-green-900/20 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
                        <div class="w-14 h-14 bg-white dark:bg-gray-700 rounded-xl shadow-sm flex items-center justify-center text-green-600 text-3xl mb-6 relative z-10">
                            <ion-icon name="shield-checkmark"></ion-icon>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Validasi Terpercaya</h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            Proses persetujuan berjenjang yang transparan, menjamin akurasi data pengembangan karir setiap individu.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20 bg-gray-900 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
                    <div>
                        <h2 class="text-3xl font-bold mb-2">Lingkungan Kerja Kami</h2>
                        <p class="text-gray-400">Sinergi teknologi dan sumber daya manusia unggul.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="group relative h-64 rounded-2xl overflow-hidden cursor-pointer">
                        <img src="{{ asset('img/Gudang.jpeg') }}" 
                             class="w-full h-full object-cover transition duration-500 group-hover:scale-110" alt="Pabrik">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent flex items-end p-6">
                            <span class="font-bold text-lg translate-y-4 group-hover:translate-y-0 transition duration-300">Area Produksi</span>
                        </div>
                    </div>
                    <div class="group relative h-64 rounded-2xl overflow-hidden cursor-pointer md:col-span-2">
                        <img src="{{ asset('img/ccr.jpeg') }}" 
                             class="w-full h-full object-cover transition duration-500 group-hover:scale-110" alt="Konstruksi">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent flex items-end p-6">
                            <span class="font-bold text-lg translate-y-4 group-hover:translate-y-0 transition duration-300">Inovasi Berkelanjutan</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="bg-white dark:bg-gray-950 border-t border-gray-200 dark:border-gray-800 pt-16 pb-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                    <div class="col-span-1 md:col-span-1">
                        <div class="flex items-center gap-3 mb-6">
                            <img src="{{ asset('img/semen_gresik.svg') }}" alt="Logo SG" class="h-12 w-auto">
                        </div>
                        <p class="text-gray-500 text-sm leading-relaxed mb-4">
                            Sistem Informasi Pengembangan Kompetensi & Karir Terintegrasi PT Semen Gresik.
                        </p>
                        <div class="text-sm font-semibold text-gray-800 dark:text-white">
                            Kokoh Tak Tertandingi.
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Akses Cepat</h4>
                        <ul class="space-y-2 text-gray-500 text-sm">
                            <li><a href="#" class="hover:text-red-600 transition">Dashboard</a></li>
                            <li><a href="#" class="hover:text-red-600 transition">Katalog Training</a></li>
                            <li><a href="#" class="hover:text-red-600 transition">Assessment</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Internal Link</h4>
                        <ul class="space-y-2 text-gray-500 text-sm">
                            <li><a href="#" class="hover:text-red-600 transition">Portal SIG</a></li>
                            <li><a href="#" class="hover:text-red-600 transition">HCIS Knowledge</a></li>
                            <li><a href="#" class="hover:text-red-600 transition">Safety Induction</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Kontak HC</h4>
                        <ul class="space-y-3 text-gray-500 text-sm">
                            <li class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                                    <ion-icon name="location"></ion-icon>
                                </div>
                                <span>Gedung Utama, Jl. Veteran, Gresik</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                                    <ion-icon name="call"></ion-icon>
                                </div>
                                <span>(031) 3981732</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm text-gray-500">
                    <p>&copy; {{ date('Y') }} PT Semen Gresik (Persero) Tbk.</p>
                    <div class="flex space-x-4 mt-4 md:mt-0">
                        <span class="flex items-center gap-1 text-red-600 font-semibold"><ion-icon name="heart"></ion-icon> dari Semen Gresik untuk Indonesia</span>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>