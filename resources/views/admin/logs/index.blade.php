<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                Log Aktivitas Sistem
            </h2>
            <a href="{{ route('admin.logs.export', request()->query()) }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium flex items-center shadow-sm transition">
                <ion-icon name="document-text-outline" class="mr-2 text-lg"></ion-icon>
                Export Excel
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('admin.logs.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                
                <div class="w-full md:w-1/3">
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Pengguna</label>
                    <select name="user_id" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                        <option value="all">Semua Pengguna</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-1/3">
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Tipe Aksi</label>
                    <select name="action" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                        <option value="all">Semua Aksi</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ ($filters['action'] ?? '') == $action ? 'selected' : '' }}>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="px-6 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-sm font-medium shadow-sm transition flex items-center h-[38px]">
                    <ion-icon name="filter-outline" class="mr-2"></ion-icon>
                    Filter Log
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600 dark:text-gray-300 uppercase">Waktu</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 dark:text-gray-300 uppercase">Pengguna</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 dark:text-gray-300 uppercase">Aksi</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 dark:text-gray-300 uppercase">Deskripsi</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 dark:text-gray-300 uppercase">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                {{ $log->timestamp->format('d M Y, H:i:s') }}
                                <span class="block text-xs text-gray-400">{{ $log->timestamp->diffForHumans() }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600 mr-3">
                                        {{ substr($log->user->name ?? '?', 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $log->user->name ?? 'Unknown User' }}</p>
                                        <p class="text-xs text-gray-500">{{ $log->user->email ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $color = 'gray';
                                    if(str_contains(strtolower($log->action), 'login')) $color = 'green';
                                    if(str_contains(strtolower($log->action), 'delete')) $color = 'red';
                                    if(str_contains(strtolower($log->action), 'create')) $color = 'blue';
                                    if(str_contains(strtolower($log->action), 'update')) $color = 'yellow';
                                @endphp
                                <span class="px-2 py-1 rounded-md text-xs font-bold uppercase bg-{{ $color }}-100 text-{{ $color }}-800 border border-{{ $color }}-200">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-700 dark:text-gray-300 max-w-md truncate" title="{{ $log->description }}">
                                {{ $log->description }}
                            </td>
                            <td class="px-6 py-3 text-gray-500 dark:text-gray-400 text-xs font-mono">
                                {{ $log->ip_address ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <ion-icon name="time-outline" class="text-4xl mb-2 text-gray-300"></ion-icon>
                                <p>Belum ada aktivitas yang tercatat.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                {{ $logs->withQueryString()->links() }}
            </div>
        </div>

    </div>
</x-admin-layout>