<x-lp-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Edit Katalog</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm">
            <form action="{{ route('lp.katalog.update', $training->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul Pelatihan</label>
                    <input type="text" name="title" value="{{ $training->title }}" required class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white mt-1">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Provider</label>
                        <input type="text" name="provider" value="{{ $training->provider }}" required class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white mt-1">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Pelatihan (Method)</label>
                        <select name="type" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white mt-1">
                            <option value="Online" {{ $training->type == 'Online' ? 'selected' : '' }}>Online</option>
                            <option value="Offline" {{ $training->type == 'Offline' ? 'selected' : '' }}>Offline</option>
                            <option value="Hybrid" {{ $training->type == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Biaya (Rp)</label>
                    <input type="number" name="cost" value="{{ $training->cost }}" required class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white mt-1">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
                    <textarea name="description" rows="3" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white mt-1">{{ $training->description }}</textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('lp.katalog.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>
</x-lp-layout>