<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Riwayat Pelatihan Saya') }}
            </h2>

            <div class="flex gap-2">
                {{-- 1. TOMBOL "AJUKAN SEMUA KE SPV" (Hanya jika ada draft) --}}
                @if(isset($hasDrafts) && $hasDrafts)
                    <form action="{{ route('rencana.submitAll') }}" method="POST" onsubmit="return confirm('Ajukan semua rencana Draft ke Supervisor?');">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-bold shadow-lg transition transform hover:-translate-y-0.5">
                            <ion-icon name="paper-plane-outline" class="text-xl"></ion-icon>
                            <span>Ajukan ke SPV</span>
                        </button>
                    </form>
                @endif

                {{-- 2. TOMBOL "TAMBAH BARU" (Ke Katalog) --}}
                <a href="{{ route('katalog') }}" 
                   class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold shadow-lg transition transform hover:-translate-y-0.5">
                    <ion-icon name="add-circle-outline" class="text-xl"></ion-icon>
                    <span>Tambah Baru</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Notifikasi Sukses --}}
            @if(session('success'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <ion-icon name="checkmark-circle" class="text-xl"></ion-icon>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900"><ion-icon name="close"></ion-icon></button>
                </div>
            @endif

            {{-- Filter Section --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm mb-8 border border-gray-200 dark:border-gray-700">
                <form method="GET" action="{{ route('riwayat') }}" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/3">
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="all">Semua Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="pending_supervisor" {{ request('status') == 'pending_supervisor' ? 'selected' : '' }}>Menunggu SPV</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="w-full md:w-1/3">
                        <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                        <select name="year" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="all">Semua Tahun</option>
                            @foreach(range(date('Y'), 2020) as $year)
                                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-auto">
                        <button type="submit" class="w-full px-6 py-2 bg-gray-800 text-white font-medium rounded-lg hover:bg-gray-700 transition">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            {{-- LIST RIWAYAT --}}
            <div class="space-y-4">
                @forelse($trainingHistory as $plan)
                    @php
                        $item = $plan->items->first();
                        $trainingTitle = $item ? ($item->training->title ?? $item->title ?? 'Pelatihan Kustom') : 'Rencana Pelatihan';
                    @endphp

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col md:flex-row justify-between items-center gap-4 hover:shadow-md transition-shadow">
                        
                        {{-- KIRI: INFO UTAMA --}}
                        <div class="flex-1 w-full">
                            <div class="flex items-center gap-3 mb-1">
                                {{-- Badge Status --}}
                                @if($plan->status == 'draft')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-200 text-gray-700 border border-gray-300">DRAFT</span>
                                @elseif($plan->status == 'completed')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-700 text-white border border-gray-600">SELESAI</span>
                                @elseif($plan->status == 'approved')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 border border-green-200">DISETUJUI</span>
                                @elseif($plan->status == 'pending_lp')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800 border border-orange-200">VERIFIKASI LP</span>
                                @elseif($plan->status == 'pending_supervisor')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">MENUNGGU SPV</span>
                                @elseif($plan->status == 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">DITOLAK</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">{{ strtoupper($plan->status) }}</span>
                                @endif

                                <span class="text-xs text-gray-400 flex items-center">
                                    <ion-icon name="calendar-outline" class="mr-1"></ion-icon>
                                    {{ $plan->created_at->format('d M Y') }}
                                </span>
                            </div>

                            <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">
                                @if($item && $item->training_id)
                                    <a href="{{ route('training.show', $item->training_id) }}" class="hover:text-indigo-600 transition">
                                        {{ $trainingTitle }}
                                    </a>
                                @else
                                    {{ $trainingTitle }}
                                @endif
                            </h3>
                            
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex gap-3">
                                <span><ion-icon name="business-outline" class="align-middle mr-1"></ion-icon>{{ $item->provider ?? '-' }}</span>
                                <span><ion-icon name="laptop-outline" class="align-middle mr-1"></ion-icon>{{ $item->method ?? '-' }}</span>
                            </div>
                        </div>

                        {{-- KANAN: TOMBOL AKSI --}}
                        <div class="flex-shrink-0 w-full md:w-auto flex items-center justify-end gap-2">
                            
                            {{-- 1. HAPUS (Draft/Pending/Rejected) --}}
                            @if(in_array($plan->status, ['draft', 'pending_supervisor', 'rejected']))
                                <form action="{{ route('rencana.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('Hapus rencana ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition border border-red-100 flex items-center gap-1" title="Hapus">
                                        <ion-icon name="trash-outline" class="text-lg"></ion-icon>
                                        <span class="text-xs font-bold md:hidden">Hapus</span>
                                    </button>
                                </form>

                            {{-- 2. DISETUJUI (Upload/Lihat) --}}
                            @elseif($plan->status == 'approved' && $item)
                                @if($item->certificate_path)
                                    <a href="{{ asset('storage/' . $item->certificate_path) }}" target="_blank" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-green-100 text-green-700 hover:bg-green-200 transition border border-green-200">
                                        <ion-icon name="document-text-outline" class="text-lg"></ion-icon>
                                        <span class="text-xs font-bold">Lihat</span>
                                    </a>
                                    <a href="{{ route('rencana.sertifikat', $item->id) }}" class="text-gray-400 hover:text-indigo-600 p-1" title="Ganti File">
                                        <ion-icon name="create-outline" class="text-xl"></ion-icon>
                                    </a>
                                @else
                                    <a href="{{ route('rencana.sertifikat', $item->id) }}" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition border border-indigo-100 animate-pulse">
                                        <ion-icon name="cloud-upload-outline" class="text-lg"></ion-icon>
                                        <span class="text-xs font-bold">Upload</span>
                                    </a>
                                @endif

                            {{-- 3. SELESAI --}}
                            @elseif($plan->status == 'completed' && $item && $item->certificate_path)
                                <a href="{{ asset('storage/' . $item->certificate_path) }}" target="_blank" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 transition border border-gray-200">
                                    <ion-icon name="ribbon-outline" class="text-lg"></ion-icon>
                                    <span class="text-xs font-bold">Sertifikat</span>
                                </a>

                            {{-- 4. DITOLAK --}}
                            @elseif($plan->status == 'rejected')
                                <button type="button" onclick="alert('Alasan Penolakan: {{ $plan->rejection_reason ?? 'Tidak ada catatan.' }}')" 
                                        class="text-gray-400 hover:text-gray-600 p-1" title="Lihat Alasan">
                                    <ion-icon name="information-circle-outline" class="text-xl"></ion-icon>
                                </button>
                            @endif

                        </div>
                    </div>
                @empty
                    {{-- EMPTY STATE --}}
                    <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-700">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 dark:bg-gray-700 mb-4">
                            <ion-icon name="document-text-outline" class="text-3xl text-indigo-500"></ion-icon>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Belum ada riwayat</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-1 mb-6">Mulai ajukan pelatihan baru Anda.</p>
                        
                        <a href="{{ route('katalog') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 transition shadow-sm">
                            <ion-icon name="add-outline" class="mr-2 text-lg"></ion-icon>
                            Ajukan Sekarang
                        </a>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $trainingHistory->withQueryString()->links() }}
            </div>

        </div>
    </div>
</x-app-layout>