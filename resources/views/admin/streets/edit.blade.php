<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Modifier la rue') }}: {{ $street->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.streets.update', $street) }}">
                        @csrf
                        @method('PUT')

                        <!-- Secteur -->
                        <div class="mb-4">
                            <x-input-label for="sector_id" :value="__('Secteur')" />
                            <select id="sector_id" name="sector_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600">
                                <option value="">-- Sélectionnez un secteur --</option>
                                @foreach($sectors as $sector)
                                    <option value="{{ $sector->id }}" {{ old('sector_id', $street->sector_id) == $sector->id ? 'selected' : '' }}>
                                        {{ $sector->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('sector_id')" class="mt-2" />
                        </div>

                        <!-- Nom -->
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Nom de la rue')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $street->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Code postal -->
                        <div class="mb-4">
                            <x-input-label for="postal_code" :value="__('Code postal')" />
                            <x-text-input id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" :value="old('postal_code', $street->postal_code)" />
                            <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
                        </div>

                        <!-- Ville -->
                        <div class="mb-4">
                            <x-input-label for="city" :value="__('Ville')" />
                            <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city', $street->city)" />
                            <x-input-error :messages="$errors->get('city')" class="mt-2" />
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600">{{ old('notes', $street->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.streets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 active:bg-gray-400 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 mr-2">
                                {{ __('Annuler') }}
                            </a>
                            
                            <x-primary-button>
                                {{ __('Mettre à jour') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>