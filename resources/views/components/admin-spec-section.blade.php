<div class="border rounded-lg overflow-hidden border-gray-200 dark:border-gray-700">
    <div class="bg-gray-100 dark:bg-gray-800 px-4 py-3 flex justify-between items-center border-b border-gray-200 dark:border-gray-700">
        <h4 class="font-bold text-gray-800 dark:text-gray-200">{{ $title }}</h4>
        <button type="button" @click.prevent="addRow('{{ $var }}')" class="text-xs flex items-center px-3 py-1.5 bg-white border rounded shadow-sm hover:bg-gray-50 text-gray-700">
            <ion-icon name="add" class="mr-1"></ion-icon> Tambah
        </button>
    </div>
    <div class="p-4 bg-white dark:bg-gray-800">
        <template x-for="(row, index) in {{ $var }}" :key="row.key">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3 items-start border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                <input type="hidden" :name="'{{ $var }}['+index+'][id]'" x-model="row.id">
                <input type="hidden" :name="'{{ $var }}['+index+'][type]'" value="{{ $type }}">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Persyaratan</label>
                    <input type="text" x-model="row.requirement" :name="'{{ $var }}['+index+'][requirement]'" class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="{{ $ph1 }}">
                </div>
                <div class="relative flex gap-2">
                    <div class="w-full">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Keterangan</label>
                        <input type="text" x-model="row.level_or_notes" :name="'{{ $var }}['+index+'][level_or_notes]'" class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="{{ $ph2 }}">
                    </div>
                    <button type="button" @click.prevent="removeRow('{{ $var }}', row.key)" class="text-gray-400 hover:text-red-500 self-end mb-2"><ion-icon name="trash-outline" class="text-lg"></ion-icon></button>
                </div>
            </div>
        </template>
    </div>
</div>