<x-guest-layout>

    <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-gray-100 mt-4">
        Buat Akun Baru
    </h2>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4" 
          x-data="{ 
              selectedOrganization: '{{ old('organization_id', '') }}', 
              positions: [],
              {{-- Data JSON sudah membawa urutan dan posisi dari Controller --}}
              allOrganizations: {{ $organizations->keyBy('id')->toJson() }},
              
              init() {
                  if (this.selectedOrganization) {
                      this.updatePositionList();
                  }
              },
              
              updatePositionList() {
                  if (this.selectedOrganization && this.allOrganizations[this.selectedOrganization]) {
                      this.positions = this.allOrganizations[this.selectedOrganization].positions;
                  } else {
                      this.positions = [];
                  }
              }
          }"
          x-init="init()">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Masukkan nama lengkap..." />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="Masukkan email..." />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="company_name" :value="__('Nama Perusahaan (Asal)')" />
            <x-text-input id="company_name" class="block mt-1 w-full" type="text" name="company_name" :value="old('company_name')" required placeholder="Contoh: PT. Sumber Daya Utama" />
            <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
        </div>
        
        <div>
            <x-input-label for="nik" :value="__('NIK')" />
            <x-text-input id="nik" class="block mt-1 w-full" type="text" name="nik" :value="old('nik')" required placeholder="Contoh: Bagde-2025-II"/>
            <x-input-error :messages="$errors->get('nik')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="organization_id" :value="__('Unit / Organisasi (Penempatan)')" />
            <select id="organization_id" name="organization_id" 
                    x-model="selectedOrganization"
                    @change="updatePositionList()"
                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                
                <option value="">Pilih Unit / Organisasi</option>
                
                @foreach ($organizations as $org)
                    <option value="{{ $org->id }}">
                        {{ $org->display_name }}
                    </option>
                @endforeach

            </select>
            <x-input-error :messages="$errors->get('organization_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="position_id" :value="__('Posisi / Jabatan')" />
            <select id="position_id" name="position_id" 
                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    :disabled="!selectedOrganization"> 
                
                <option value="">Pilih Posisi</option>
                
                <template x-for="position in positions" :key="position.id">
                    <option :value="position.id" 
                            :selected="position.id == '{{ old('position_id', '') }}'">
                        <span x-text="position.title"></span>
                    </option>
                </template>

            </select>
            <x-input-error :messages="$errors->get('position_id')" class="mt-2" />
        </div>
        
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" 
                            placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" 
                            placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-3">
                {{ __('Buat Akun') }}
            </x-primary-button>
        </div>
        
        <div class="flex items-center justify-center mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Sudah punya akun? Login di sini') }}
            </a>
        </div>
    </form>
</x-guest-layout>