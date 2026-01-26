<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Struktur Hierarki Jabatan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Alert Messages --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg border-l-4 border-green-500">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg border-l-4 border-red-500">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300">Bagan Struktur</h3>
                    <a href="{{ route('admin.positions.index') }}" class="text-sm text-indigo-600 hover:underline">
                        &larr; Kembali ke List Table
                    </a>
                </div>
                
                {{-- Container Tree View --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-6 bg-gray-50 dark:bg-gray-900 min-h-[500px] overflow-auto">
                    <ul class="space-y-4">
                        @foreach($rootPositions as $position)
                            @include('admin.positions.hierarchy-item', ['position' => $position])
                        @endforeach
                    </ul>

                    @if($rootPositions->isEmpty())
                        <div class="text-center py-10 text-gray-500">
                            Belum ada data posisi. Silakan buat posisi baru terlebih dahulu.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PINDAH ATASAN --}}
    <div id="moveModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative bg-white dark:bg-gray-800 w-full max-w-md m-4 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700">
            
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Pindahkan Posisi</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Ubah atasan untuk posisi <span id="modalPositionName" class="font-bold text-indigo-600"></span>
                </p>
                
                <form id="moveForm" action="{{ route('admin.positions.updateHierarchy') }}" method="POST">
                    @csrf
                    <input type="hidden" name="position_id" id="modalPositionId">
                    
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih Atasan Baru</label>
                        <select name="new_parent_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Jadikan Paling Atas (Tanpa Atasan) --</option>
                            @foreach($allPositions as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->title }} 
                                    {{-- Tampilkan kode/organisasi untuk memperjelas --}}
                                    ({{ $p->code ?? 'N/A' }} - {{ $p->organization->name ?? 'No Org' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeModal()" 
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium shadow-lg shadow-indigo-500/30">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(id, name) {
            document.getElementById('moveModal').classList.remove('hidden');
            document.getElementById('modalPositionId').value = id;
            document.getElementById('modalPositionName').innerText = name;
        }

        function closeModal() {
            document.getElementById('moveModal').classList.add('hidden');
        }
    </script>
</x-admin-layout>