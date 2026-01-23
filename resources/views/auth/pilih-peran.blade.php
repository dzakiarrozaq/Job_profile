{{-- File: resources/views/auth/pilih-peran.blade.php --}}
<x-guest-layout>
    <x-slot name="logo">
        <a href="/" class="flex items-center justify-center">
            <ion-icon name="analytics-outline" class="text-4xl text-blue-600"></ion-icon>
            <span class="text-4xl font-bold text-gray-800 ml-2">DevHub</span>
        </a>
    </x-slot>

    <h2 class="text-2xl font-bold text-center text-gray-900 mt-4">
        Pilih Peran Anda
    </h2>
    <p class="text-center text-gray-600">Anda memiliki lebih dari satu peran. Silakan pilih peran yang ingin Anda gunakan untuk sesi ini.</p>

    <div class="mt-6 space-y-4">
        {{-- Form ini akan men-submit ke controller baru kita --}}
        <form method="POST" action="{{ route('role.select') }}">
            @csrf
            
            <div class="space-y-3">
                {{-- Loop semua peran yang dimiliki user --}}
                @foreach (Auth::user()->roles as $role)
                    <button type="submit" name="role_name" value="{{ $role->name }}" 
                            class="w-full flex items-center justify-center px-4 py-3 bg-white border border-gray-300 rounded-lg text-lg font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Masuk sebagai {{ $role->name }}
                    </button>
                @endforeach
            </div>
        </form>
    </div>
</x-guest-layout>