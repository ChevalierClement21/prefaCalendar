@php
    use Silber\Bouncer\BouncerFacade as Bouncer;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestion des utilisateurs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Utilisateurs en attente d'approbation -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Utilisateurs en attente d\'approbation') }}</h3>
                    
                    @if($pendingUsers->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            {{ __('Nom') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            {{ __('Email') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            {{ __('Date d\'inscription') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            {{ __('Actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingUsers as $user)
                                        <tr>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                {{ $user->firstname }} {{ $user->lastname }}
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                {{ $user->email }}
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                {{ $user->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                <div class="flex space-x-2">
                                                    <form action="{{ route('users.approve', $user) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded text-xs">
                                                            {{ __('Approuver') }}
                                                        </button>
                                                    </form>
                                                    
                                                    <form action="{{ route('users.reject', $user) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir rejeter cet utilisateur ? Cette action est irréversible.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded text-xs">
                                                            {{ __('Rejeter') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">{{ __('Aucun utilisateur en attente d\'approbation.') }}</p>
                    @endif
                </div>
            </div>

            <!-- Utilisateurs approuvés -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Utilisateurs approuvés') }}</h3>
                    
                    @if($approvedUsers->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            {{ __('Nom') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            {{ __('Email') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            {{ __('Rôle') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            {{ __('Date d\'inscription') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            {{ __('Actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($approvedUsers as $user)
                                        <tr>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                {{ $user->firstname }} {{ $user->lastname }}
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                {{ $user->email }}
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                @if(Bouncer::is($user)->an('admin'))
                                                    <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2 py-1 rounded">Admin</span>
                                                @else
                                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">Utilisateur</span>
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                {{ $user->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                <div class="flex space-x-2">
                                                    @if(!Bouncer::is($user)->an('admin'))
                                                        <form action="{{ route('users.assign-admin', $user) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white py-1 px-2 rounded text-xs">
                                                                {{ __('Promouvoir admin') }}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('users.remove-admin', $user) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white py-1 px-2 rounded text-xs">
                                                                {{ __('Rétrograder') }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">{{ __('Aucun utilisateur approuvé.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
