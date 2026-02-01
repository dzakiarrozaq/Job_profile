<li class="relative pl-8 border-l-2 border-indigo-200 dark:border-indigo-900/50 last:border-l-0 transition-all duration-300">
    
    <div class="absolute -left-[2px] top-5 w-6 h-0.5 bg-indigo-200 dark:bg-indigo-900/50"></div>
    <div class="absolute -left-[2px] top-0 w-0.5 h-full bg-indigo-200 dark:bg-indigo-900/50"></div>

    <div class="relative flex items-center justify-between p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-md hover:border-indigo-300 transition-all duration-200 group mb-3">
        
        <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg text-indigo-600 dark:text-indigo-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>

            <div class="flex flex-col">
                <span class="font-bold text-gray-800 dark:text-white text-base">
                    {{ $position->title }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                    Unit: {{ $position->organization->name ?? '-' }} 
                    <span class="mx-1">•</span> 
                    Kode: {{ $position->code ?? '-' }}
                </span>
            </div>
        </div>

        <button onclick="openModal('{{ $position->id }}', '{{ $position->title }}')" 
                class="opacity-0 group-hover:opacity-100 transition-opacity px-3 py-1.5 text-xs font-semibold bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 border border-indigo-200">
            Pindah Atasan
        </button>
    </div>

    @if($position->bawahanRecursive->isNotEmpty())
        <ul class="ml-2 mt-2 space-y-2">
            @foreach($position->bawahanRecursive as $child)
                @include('admin.positions.hierarchy-item', ['position' => $child])
            @endforeach
        </ul>
    @endif
</li>