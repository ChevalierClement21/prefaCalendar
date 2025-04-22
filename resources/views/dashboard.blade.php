<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tableau de bord') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-4">{{ __('Actions rapides') }}</h3>
                <div class="flex gap-4">
                    <a href="{{ route('tours.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition ease-in-out duration-150">
                        {{ __('Mes tournées') }}
                    </a>
                    <a href="{{ route('tours.create') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition ease-in-out duration-150">
                        {{ __('Nouvelle tournée') }}
                    </a>
                    <a href="{{ route('profile.edit') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition ease-in-out duration-150">
                        {{ __('Mon profil') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-center">
                        <p class="text-4xl font-bold text-gray-800 dark:text-gray-200 mb-2">{{ auth()->user()->createdTours()->where('status', 'in_progress')->count() }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Tournées en cours') }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-center">
                        <p class="text-4xl font-bold text-gray-800 dark:text-gray-200 mb-2">{{ auth()->user()->createdTours()->where('status', 'completed')->count() }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Tournées terminées') }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-center">
                        <p class="text-4xl font-bold text-gray-800 dark:text-gray-200 mb-2">{{ auth()->user()->createdTours()->count() }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Tournées totales') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>