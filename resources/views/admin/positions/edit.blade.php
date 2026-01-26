<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Posisi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <form action="{{ route('admin.positions.update', $position->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Nama Posisi --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Posisi</label>
                            <input type="text" name="title" value="{{ old('title', $position->title) }}" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Tipe --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Pegawai</label>
                            <select name="tipe" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                <option value="organik" {{ $position->tipe == 'organik' ? 'selected' : '' }}>Karyawan Organik</option>
                                <option value="outsourcing" {{ $position->tipe == 'outsourcing' ? 'selected' : '' }}>Karyawan Outsourcing</option>
                            </select>
                        </div>

                        {{-- Organisasi / Unit --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit / Organisasi</label>
                            <select name="organization_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                <option value="">-- Pilih Unit --</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}" {{ $position->organization_id == $org->id ? 'selected' : '' }}>
                                        {{ $org->name }} ({{ ucfirst($org->type) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('organization_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Atasan --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Atasan Langsung (Opsional)</label>
                            <select name="atasan_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                <option value="">-- Tidak Ada (Top Level) --</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}" {{ $position->atasan_id == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.positions.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition">Batal</a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">Simpan Perubahan</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-admin-layout>