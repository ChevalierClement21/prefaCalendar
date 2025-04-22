<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Détails de la rue') }}: {{ $street->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.streets.edit', $street) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Modifier') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <h3 class="text-lg font-medium">{{ __('Nom') }}</h3>
                            <p class="mt-1">{{ $street->name }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium">{{ __('Code postal') }}</h3>
                            <p class="mt-1">{{ $street->postal_code ?: '-' }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium">{{ __('Ville') }}</h3>
                            <p class="mt-1">{{ $street->city ?: '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h3 class="text-lg font-medium">{{ __('Secteur') }}</h3>
                        <div class="mt-1">
                            @if ($street->sector)
                                <div class="flex items-center">
                                    @if ($street->sector->color)
                                        <div class="w-4 h-4 rounded mr-2" style="background-color: {{ $street->sector->color }}"></div>
                                    @endif
                                    <a href="{{ route('admin.sectors.show', $street->sector) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $street->sector->name }}
                                    </a>
                                </div>
                            @else
                                <p>Aucun secteur associé</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h3 class="text-lg font-medium">{{ __('Notes') }}</h3>
                        <p class="mt-1">{{ $street->notes ?: 'Aucune note' }}</p>
                    </div>

                    <div class="mt-8 flex justify-between">
                        <div>
                            <a href="{{ route('admin.streets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 active:bg-gray-400 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Retour à la liste des rues') }}
                            </a>
                            
                            @if ($street->sector)
                                <a href="{{ route('admin.sectors.show', $street->sector) }}" class="ml-2 inline-flex items-center px-4 py-2 bg-blue-200 dark:bg-blue-800 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-blue-300 dark:hover:bg-blue-700 active:bg-blue-400 dark:active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    {{ __('Voir le secteur') }}
                                </a>
                            @endif
                        </div>
                        
                        <form action="{{ route('admin.streets.destroy', $street) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette rue?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Supprimer') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>