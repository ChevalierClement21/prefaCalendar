<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Créer une nouvelle tournée') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Erreurs!</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('tours.store') }}" method="POST" id="create-tour-form">
                        @csrf
                        
                        @if(isset($activeSession))
                            <div class="mb-4 p-4 bg-blue-50 rounded-md border border-blue-200">
                                <p class="text-blue-700 font-medium">{{ __('Session active:') }} {{ $activeSession->name }} ({{ $activeSession->year }})</p>
                                <p class="text-blue-600 text-sm mt-1">{{ __('Cette tournée sera associée à la session active ci-dessus.') }}</p>
                                <input type="hidden" name="session_id" value="{{ $activeSession->id }}">
                            </div>
                        @else
                            <div class="mb-4 p-4 bg-yellow-50 rounded-md border border-yellow-200">
                                <p class="text-yellow-700">{{ __('Aucune session active. Cette tournée ne sera associée à aucune session.') }}</p>
                            </div>
                        @endif

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Nom de la tournée')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="sector_id" :value="__('Secteur')" />
                            <select id="sector_id" name="sector_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                                <option value="">{{ __('Sélectionner un secteur') }}</option>
                                @foreach ($sectors as $sector)
                                    <option value="{{ $sector->id }}" @selected(old('sector_id') == $sector->id) style="color: {{ $sector->color }};">
                                        {{ $sector->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('sector_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="user_ids" :value="__('Assigner des utilisateurs (optionnel)')" />
                            <div class="mt-2 border border-gray-300 rounded-md p-2 max-h-60 overflow-y-auto">
                                @foreach ($users as $user)
                                    <div class="flex items-center mb-2">
                                        <input type="checkbox" name="user_ids[]" id="user_{{ $user->id }}" value="{{ $user->id }}" 
                                            @checked(is_array(old('user_ids')) && in_array($user->id, old('user_ids')))
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <label for="user_{{ $user->id }}" class="ml-2 text-sm text-gray-700">
                                            {{ $user->firstname }} {{ $user->lastname }} ({{ $user->email }})
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('user_ids')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="notes" :value="__('Notes (optionnel)')" />
                            <textarea id="notes" name="notes" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('tours.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Annuler') }}
                            </a>
                            <x-primary-button type="submit" id="submit-btn">
                                {{ __('Créer la tournée') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('create-tour-form');
            const submitBtn = document.getElementById('submit-btn');
            
            form.addEventListener('submit', function(e) {
                console.log('Formulaire soumis');
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Création en cours...';
            });
        });
    </script>
</x-app-layout>