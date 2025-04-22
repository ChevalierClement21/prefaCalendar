<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mes Tournées') }}
            </h2>
            <a href="{{ route('tours.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                {{ __('Créer une tournée') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($tours->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Nom') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Secteur') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Statut') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Date de début') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($tours as $tour)
                                        <tr>
                                            <td class="py-2 px-4 text-sm font-medium text-gray-900">
                                                {{ $tour->name }}
                                            </td>
                                            <td class="py-2 px-4 text-sm text-gray-500">
                                                <span class="inline-block px-2 py-1 rounded-full" style="background-color: {{ $tour->sector->color }}; color: white;">
                                                    {{ $tour->sector->name }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-4 text-sm text-gray-500">
                                                @if ($tour->status === 'in_progress')
                                                    <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                                        {{ __('En cours') }}
                                                    </span>
                                                @else
                                                    <span class="inline-block px-2 py-1 bg-green-100 text-green-800 rounded-full">
                                                        {{ __('Terminée') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 text-sm text-gray-500">
                                                {{ $tour->start_date->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="py-2 px-4 text-sm text-gray-500">
                                                <a href="{{ route('tours.show', $tour) }}" class="text-blue-600 hover:text-blue-900">
                                                    {{ __('Voir détails') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $tours->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">{{ __('Aucune tournée trouvée.') }}</p>
                            <a href="{{ route('tours.create') }}" class="mt-2 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                {{ __('Créer une tournée') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>