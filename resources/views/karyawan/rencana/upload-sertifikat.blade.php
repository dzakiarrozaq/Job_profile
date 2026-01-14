<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Upload Sertifikat Pelatihan
        </h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8 border border-gray-200 dark:border-gray-700">
            
            <div class="mb-6 border-b border-gray-100 dark:border-gray-700 pb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ $item->title }}</h3>
                <p class="text-sm text-gray-500 mb-2">Provider: {{ $item->provider }}</p>
                <span class="px-2 py-1 text-xs font-bold rounded bg-indigo-100 text-indigo-700">
                    {{ $item->method }}
                </span>
            </div>

            <form action="{{ route('rencana.sertifikat.store', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        File Sertifikat
                    </label>
                    
                    <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="space-y-1 text-center">
                            <ion-icon name="cloud-upload-outline" class="text-4xl text-gray-400"></ion-icon>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                    <span>Upload file</span>
                                    <input id="file-upload" name="file" type="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png" onchange="previewFile()">
                                </label>
                                <p class="pl-1">atau drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PDF, PNG, JPG up to 2MB</p>
                            <p id="filename" class="text-sm font-bold text-indigo-600 mt-2"></p>
                        </div>
                    </div>
                    @error('file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if($item->certificate_path)
                    <div class="mb-6 p-4 rounded-lg flex items-start gap-3 
                        {{ $item->certificate_status == 'verified' ? 'bg-green-50 border border-green-200' : 
                        ($item->certificate_status == 'rejected' ? 'bg-red-50 border border-red-200' : 'bg-yellow-50 border border-yellow-200') }}">
                        
                        {{-- Ikon Status --}}
                        @if($item->certificate_status == 'verified')
                            <ion-icon name="checkmark-circle" class="text-green-600 text-2xl mt-0.5"></ion-icon>
                        @elseif($item->certificate_status == 'rejected')
                            <ion-icon name="alert-circle" class="text-red-600 text-2xl mt-0.5"></ion-icon>
                        @else
                            <ion-icon name="hourglass" class="text-yellow-600 text-2xl mt-0.5"></ion-icon>
                        @endif

                        <div>
                            <p class="text-sm font-bold 
                                {{ $item->certificate_status == 'verified' ? 'text-green-800' : 
                                ($item->certificate_status == 'rejected' ? 'text-red-800' : 'text-yellow-800') }}">
                                
                                @if($item->certificate_status == 'verified')
                                    Sertifikat Terverifikasi
                                @elseif($item->certificate_status == 'rejected')
                                    Sertifikat Ditolak - Silakan Upload Ulang
                                @else
                                    Sedang Menunggu Verifikasi Supervisor
                                @endif
                            </p>
                            
                            <p class="text-xs mt-1 mb-2 {{ $item->certificate_status == 'verified' ? 'text-green-700' : ($item->certificate_status == 'rejected' ? 'text-red-700' : 'text-yellow-700') }}">
                                File saat ini: <a href="{{ asset('storage/' . $item->certificate_path) }}" target="_blank" class="underline font-semibold">Lihat File</a>
                            </p>

                            {{-- Pesan Tambahan --}}
                            @if($item->certificate_status == 'pending_approval')
                                <p class="text-xs text-yellow-600 italic">
                                    *Mengupload file baru akan menimpa file lama dan mereset status verifikasi.
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="flex justify-end gap-3">
                    <a href="{{ route('rencana.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-bold shadow-lg">
                        Simpan Sertifikat
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewFile() {
            const input = document.getElementById('file-upload');
            const fileName = document.getElementById('filename');
            if (input.files.length > 0) {
                fileName.textContent = 'File terpilih: ' + input.files[0].name;
            }
        }
    </script>
</x-app-layout>