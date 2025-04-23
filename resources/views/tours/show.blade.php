<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $tour->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('tours.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    {{ __('Retour') }}
                </a>
                @if ($tour->status === 'in_progress')
                    <a href="{{ route('tours.complete-form', $tour) }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 inline-block">
                        {{ __('Terminer la tournée') }}
                    </a>
                @endif
            </div>
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
                    
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Information sur la tournée -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium mb-4">{{ __('Informations') }}</h3>
                            <div class="space-y-2">
                                <p><span class="font-medium">{{ __('Secteur') }}:</span> 
                                    <span class="inline-block px-2 py-1 rounded-full" style="background-color: {{ $tour->sector->color }}; color: white;">
                                        {{ $tour->sector->name }}
                                    </span>
                                </p>
                                <p><span class="font-medium">{{ __('Statut') }}:</span> 
                                    @if ($tour->status === 'in_progress')
                                        <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                            {{ __('En cours') }}
                                        </span>
                                    @else
                                        <span class="inline-block px-2 py-1 bg-green-100 text-green-800 rounded-full">
                                            {{ __('Terminée') }}
                                        </span>
                                    @endif
                                </p>
                                <p><span class="font-medium">{{ __('Date de début') }}:</span> {{ $tour->start_date->format('d/m/Y H:i') }}</p>
                                @if ($tour->end_date)
                                    <p><span class="font-medium">{{ __('Date de fin') }}:</span> {{ $tour->end_date->format('d/m/Y H:i') }}</p>
                                @endif
                                <p><span class="font-medium">{{ __('Créée par') }}:</span> {{ $tour->creator->firstname }} {{ $tour->creator->lastname }}</p>
                                @if ($tour->session)
                                    <p><span class="font-medium">{{ __('Session') }}:</span> {{ $tour->session->name }} ({{ $tour->session->year }})</p>
                                @endif
                                @if ($tour->notes)
                                    <p><span class="font-medium">{{ __('Notes') }}:</span> {{ $tour->notes }}</p>
                                @endif
                            </div>
                            
                            <h4 class="text-md font-medium mt-4 mb-2">{{ __('Participants') }}</h4>
                            <div class="space-y-1">
                                @if ($tour->users->count() > 0)
                                    @foreach ($tour->users as $user)
                                        <p>{{ $user->firstname }} {{ $user->lastname }}</p>
                                    @endforeach
                                @else
                                    <p class="text-gray-500">{{ __('Aucun participant assigné.') }}</p>
                                @endif
                            </div>
                        </div>
                    
                        <!-- Maisons à revisiter -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium mb-4">{{ __('Maisons à revisiter') }}</h3>
                            <div class="max-h-60 overflow-y-auto space-y-2">
                                @php
                                    $revisitCount = 0;
                                @endphp
                                @foreach ($houseNumbers as $streetId => $numbers)
                                    @foreach ($numbers as $houseNumber)
                                        @if ($houseNumber->status === 'to_revisit')
                                            @php $revisitCount++; @endphp
                                            <div class="flex justify-between items-center p-2 bg-yellow-50 border border-yellow-200 rounded">
                                                <div>
                                                    <span class="font-medium">{{ $houseNumber->street->name }} {{ $houseNumber->number }}</span>
                                                    @if ($houseNumber->notes)
                                                        <p class="text-sm text-gray-600">{{ $houseNumber->notes }}</p>
                                                    @endif
                                                </div>
                                                @if ($tour->status === 'in_progress')
                                                    <div class="flex space-x-1">
                                                        <form action="{{ route('tours.house-numbers.status', [$tour, $houseNumber]) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="visited">
                                                            <button type="submit" class="px-2 py-1 bg-green-500 text-white rounded text-xs">
                                                                {{ __('Visité') }}
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('tours.house-numbers.status', [$tour, $houseNumber]) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="skipped">
                                                            <button type="submit" class="px-2 py-1 bg-gray-500 text-white rounded text-xs">
                                                                {{ __('Ignorer') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                @endforeach
                                
                                @if ($revisitCount === 0)
                                    <p class="text-gray-500">{{ __('Aucune maison à revisiter.') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($tour->status === 'in_progress')
                        <div class="mt-8">
                            <h3 class="text-lg font-medium mb-4">{{ __('Rues du secteur') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($streets as $street)
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow">
                                        <h4 class="font-medium text-gray-900">{{ $street->name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $street->postal_code }} {{ $street->city }}</p>
                                        
                                        @if (isset($houseNumbers[$street->id]))
                                            <div class="mt-2 mb-3">
                                                <h5 class="text-sm font-medium text-gray-600">{{ __('Numéros enregistrés:') }}</h5>
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @foreach ($houseNumbers[$street->id] as $houseNumber)
                                                        <span class="inline-block px-2 py-1 text-xs rounded-full
                                                            @if ($houseNumber->status === 'to_revisit') bg-yellow-100 text-yellow-800 @endif
                                                            @if ($houseNumber->status === 'visited') bg-green-100 text-green-800 @endif
                                                            @if ($houseNumber->status === 'skipped') bg-gray-100 text-gray-800 @endif
                                                        ">
                                                            {{ $houseNumber->number }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        
                                        @if ($tour->session_id && isset($availableHouseNumbers[$street->id]) && count($availableHouseNumbers[$street->id]) > 0)
                                            <div class="mt-2 mb-3">
                                                <h5 class="text-sm font-medium text-gray-600">{{ __('Numéros existants dans cette session:') }}</h5>
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @foreach ($availableHouseNumbers[$street->id] as $availableNumber)
                                                        <form action="{{ route('tours.house-numbers.add', $tour) }}" method="POST" class="inline">
                                                            @csrf
                                                            <input type="hidden" name="street_id" value="{{ $street->id }}">
                                                            <input type="hidden" name="number" value="{{ $availableNumber->number }}">
                                                            <input type="hidden" name="notes" value="{{ $availableNumber->notes }}">
                                                            <button type="submit" class="inline-block px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800 hover:bg-orange-200">
                                                                {{ $availableNumber->number }}
                                                            </button>
                                                        </form>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <form action="{{ route('tours.house-numbers.add', $tour) }}" method="POST" class="mt-2 flex items-end space-x-2">
                                            @csrf
                                            <input type="hidden" name="street_id" value="{{ $street->id }}">
                                            <div>
                                                <label for="number_{{ $street->id }}" class="block text-xs font-medium text-gray-700">{{ __('Ajouter n°') }}</label>
                                                <input type="text" name="number" id="number_{{ $street->id }}" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="42">
                                            </div>
                                            <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                                {{ __('Ajouter') }}
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>