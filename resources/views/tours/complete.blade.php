<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Terminer la tournée') }}: {{ $tour->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('tours.submit-completion', $tour) }}" id="completionForm">
                        @csrf

                        <!-- Informations générales -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Informations générales</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="calendars_sold" :value="__('Nombre de calendriers vendus')" />
                                    <x-text-input id="calendars_sold" class="block mt-1 w-full" type="number" min="0" name="calendars_sold" :value="old('calendars_sold', 0)" required autofocus />
                                    <x-input-error :messages="$errors->get('calendars_sold')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Billets -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Billets</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="tickets_5" :value="__('Billets de 5€')" />
                                    <x-text-input id="tickets_5" class="block mt-1 w-full" type="number" min="0" name="tickets_5" :value="old('tickets_5', 0)" required />
                                    <x-input-error :messages="$errors->get('tickets_5')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="tickets_10" :value="__('Billets de 10€')" />
                                    <x-text-input id="tickets_10" class="block mt-1 w-full" type="number" min="0" name="tickets_10" :value="old('tickets_10', 0)" required />
                                    <x-input-error :messages="$errors->get('tickets_10')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="tickets_20" :value="__('Billets de 20€')" />
                                    <x-text-input id="tickets_20" class="block mt-1 w-full" type="number" min="0" name="tickets_20" :value="old('tickets_20', 0)" required />
                                    <x-input-error :messages="$errors->get('tickets_20')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="tickets_50" :value="__('Billets de 50€')" />
                                    <x-text-input id="tickets_50" class="block mt-1 w-full" type="number" min="0" name="tickets_50" :value="old('tickets_50', 0)" required />
                                    <x-input-error :messages="$errors->get('tickets_50')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="tickets_100" :value="__('Billets de 100€')" />
                                    <x-text-input id="tickets_100" class="block mt-1 w-full" type="number" min="0" name="tickets_100" :value="old('tickets_100', 0)" required />
                                    <x-input-error :messages="$errors->get('tickets_100')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="tickets_200" :value="__('Billets de 200€')" />
                                    <x-text-input id="tickets_200" class="block mt-1 w-full" type="number" min="0" name="tickets_200" :value="old('tickets_200', 0)" required />
                                    <x-input-error :messages="$errors->get('tickets_200')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="tickets_500" :value="__('Billets de 500€')" />
                                    <x-text-input id="tickets_500" class="block mt-1 w-full" type="number" min="0" name="tickets_500" :value="old('tickets_500', 0)" required />
                                    <x-input-error :messages="$errors->get('tickets_500')" class="mt-2" />
                                </div>
                                <div class="md:col-span-3">
                                    <p class="text-sm text-gray-700 font-semibold">
                                        Montant total des billets: <span id="ticketsTotal">0.00</span> €
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Pièces -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Pièces</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <x-input-label for="coins_1c" :value="__('Pièces de 1 centime')" />
                                    <x-text-input id="coins_1c" class="block mt-1 w-full" type="number" min="0" name="coins_1c" :value="old('coins_1c', 0)" required />
                                    <x-input-error :messages="$errors->get('coins_1c')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="coins_2c" :value="__('Pièces de 2 centimes')" />
                                    <x-text-input id="coins_2c" class="block mt-1 w-full" type="number" min="0" name="coins_2c" :value="old('coins_2c', 0)" required />
                                    <x-input-error :messages="$errors->get('coins_2c')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="coins_5c" :value="__('Pièces de 5 centimes')" />
                                    <x-text-input id="coins_5c" class="block mt-1 w-full" type="number" min="0" name="coins_5c" :value="old('coins_5c', 0)" required />
                                    <x-input-error :messages="$errors->get('coins_5c')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="coins_10c" :value="__('Pièces de 10 centimes')" />
                                    <x-text-input id="coins_10c" class="block mt-1 w-full" type="number" min="0" name="coins_10c" :value="old('coins_10c', 0)" required />
                                    <x-input-error :messages="$errors->get('coins_10c')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="coins_20c" :value="__('Pièces de 20 centimes')" />
                                    <x-text-input id="coins_20c" class="block mt-1 w-full" type="number" min="0" name="coins_20c" :value="old('coins_20c', 0)" required />
                                    <x-input-error :messages="$errors->get('coins_20c')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="coins_50c" :value="__('Pièces de 50 centimes')" />
                                    <x-text-input id="coins_50c" class="block mt-1 w-full" type="number" min="0" name="coins_50c" :value="old('coins_50c', 0)" required />
                                    <x-input-error :messages="$errors->get('coins_50c')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="coins_1e" :value="__('Pièces de 1€')" />
                                    <x-text-input id="coins_1e" class="block mt-1 w-full" type="number" min="0" name="coins_1e" :value="old('coins_1e', 0)" required />
                                    <x-input-error :messages="$errors->get('coins_1e')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="coins_2e" :value="__('Pièces de 2€')" />
                                    <x-text-input id="coins_2e" class="block mt-1 w-full" type="number" min="0" name="coins_2e" :value="old('coins_2e', 0)" required />
                                    <x-input-error :messages="$errors->get('coins_2e')" class="mt-2" />
                                </div>
                                <div class="md:col-span-4">
                                    <p class="text-sm text-gray-700 font-semibold">
                                        Montant total des pièces: <span id="coinsTotal">0.00</span> €
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Chèques -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Chèques</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="check_count" :value="__('Nombre de chèques')" />
                                    <x-text-input id="check_count" class="block mt-1 w-full" type="number" min="0" name="check_count" :value="old('check_count', 0)" required />
                                    <x-input-error :messages="$errors->get('check_count')" class="mt-2" />
                                </div>
                                <div class="col-span-2">
                                    <div id="checkAmountsContainer" class="mt-4"></div>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-700 font-semibold mt-2">
                                        Montant total des chèques: <span id="checksTotal">0.00</span> €
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" rows="3" class="block w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <!-- Récapitulatif -->
                        <div class="mb-8 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Récapitulatif</h3>
                            <p class="text-gray-700 font-semibold">Calendriers vendus: <span id="calendarSummary">0</span></p>
                            <p class="text-gray-700 font-semibold">Montant total encaissé: <span id="grandTotal">0.00</span> €</p>
                            <ul class="mt-2 text-sm text-gray-600">
                                <li>Billets: <span id="ticketsSummary">0.00</span> €</li>
                                <li>Pièces: <span id="coinsSummary">0.00</span> €</li>
                                <li>Chèques: <span id="checksSummary">0.00</span> €</li>
                            </ul>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('tours.show', $tour) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition duration-150 ease-in-out mr-3">
                                {{ __('Annuler') }}
                            </a>
                            <x-primary-button id="submitButton">
                                {{ __('Terminer la tournée') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fonction pour formater les nombres en euros
            function formatEuro(amount) {
                return parseFloat(amount).toFixed(2);
            }

            // Éléments pour calculer le total des billets
            const ticketsInputs = {
                tickets_5: document.getElementById('tickets_5'),
                tickets_10: document.getElementById('tickets_10'),
                tickets_20: document.getElementById('tickets_20'),
                tickets_50: document.getElementById('tickets_50'),
                tickets_100: document.getElementById('tickets_100'),
                tickets_200: document.getElementById('tickets_200'),
                tickets_500: document.getElementById('tickets_500')
            };
            const ticketsValues = {
                tickets_5: 5,
                tickets_10: 10,
                tickets_20: 20,
                tickets_50: 50,
                tickets_100: 100,
                tickets_200: 200,
                tickets_500: 500
            };
            
            // Éléments pour calculer le total des pièces
            const coinsInputs = {
                coins_1c: document.getElementById('coins_1c'),
                coins_2c: document.getElementById('coins_2c'),
                coins_5c: document.getElementById('coins_5c'),
                coins_10c: document.getElementById('coins_10c'),
                coins_20c: document.getElementById('coins_20c'),
                coins_50c: document.getElementById('coins_50c'),
                coins_1e: document.getElementById('coins_1e'),
                coins_2e: document.getElementById('coins_2e')
            };
            const coinsValues = {
                coins_1c: 0.01,
                coins_2c: 0.02,
                coins_5c: 0.05,
                coins_10c: 0.10,
                coins_20c: 0.20,
                coins_50c: 0.50,
                coins_1e: 1,
                coins_2e: 2
            };
            
            // Éléments pour les chèques
            const checkCount = document.getElementById('check_count');
            const checkAmountsContainer = document.getElementById('checkAmountsContainer');
            
            // Éléments pour le récapitulatif
            const ticketsTotal = document.getElementById('ticketsTotal');
            const coinsTotal = document.getElementById('coinsTotal');
            const checksTotal = document.getElementById('checksTotal');
            const calendarSummary = document.getElementById('calendarSummary');
            const grandTotal = document.getElementById('grandTotal');
            const ticketsSummary = document.getElementById('ticketsSummary');
            const coinsSummary = document.getElementById('coinsSummary');
            const checksSummary = document.getElementById('checksSummary');
            
            // Éléments pour le calendrier
            const calendars = document.getElementById('calendars_sold');
            
            // Fonctions de calcul
            function calculateTicketsTotal() {
                let total = 0;
                Object.keys(ticketsInputs).forEach(function(key) {
                    total += ticketsInputs[key].value * ticketsValues[key];
                });
                ticketsTotal.textContent = formatEuro(total);
                ticketsSummary.textContent = formatEuro(total);
                return total;
            }
            
            function calculateCoinsTotal() {
                let total = 0;
                Object.keys(coinsInputs).forEach(function(key) {
                    total += coinsInputs[key].value * coinsValues[key];
                });
                coinsTotal.textContent = formatEuro(total);
                coinsSummary.textContent = formatEuro(total);
                return total;
            }
            
            function calculateChecksTotal() {
                let total = 0;
                const checkInputs = document.querySelectorAll('.check-amount');
                checkInputs.forEach(function(input) {
                    if (!isNaN(parseFloat(input.value))) {
                        total += parseFloat(input.value);
                    }
                });
                checksTotal.textContent = formatEuro(total);
                checksSummary.textContent = formatEuro(total);
                return total;
            }
            
            function updateGrandTotal() {
                const billsTotal = calculateTicketsTotal();
                const coinsTotal = calculateCoinsTotal();
                const checksTotal = calculateChecksTotal();
                const total = billsTotal + coinsTotal + checksTotal;
                grandTotal.textContent = formatEuro(total);
                calendarSummary.textContent = calendars.value;
            }
            
            // Mises à jour des champs de montants de chèques
            function updateCheckFields() {
                const count = parseInt(checkCount.value) || 0;
                
                // Vider le conteneur
                checkAmountsContainer.innerHTML = '';
                
                // Créer les champs pour chaque chèque
                for (let i = 1; i <= count; i++) {
                    const div = document.createElement('div');
                    div.className = 'mt-2';
                    
                    div.innerHTML = `
                        <label for="check_amounts_${i}" class="block text-sm font-medium text-gray-700">Montant du chèque ${i}</label>
                        <input id="check_amounts_${i}" type="number" step="0.01" min="0" name="check_amounts[]" class="check-amount mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    `;
                    
                    checkAmountsContainer.appendChild(div);
                }
                
                // Ajouter les écouteurs d'événements pour les nouveaux champs
                const checkInputs = document.querySelectorAll('.check-amount');
                checkInputs.forEach(function(input) {
                    input.addEventListener('input', updateGrandTotal);
                });
                
                updateGrandTotal();
            }
            
            // Ajouter les écouteurs d'événements pour les billets
            Object.keys(ticketsInputs).forEach(function(key) {
                ticketsInputs[key].addEventListener('input', updateGrandTotal);
            });
            
            // Ajouter les écouteurs d'événements pour les pièces
            Object.keys(coinsInputs).forEach(function(key) {
                coinsInputs[key].addEventListener('input', updateGrandTotal);
            });
            
            // Ajouter l'écouteur d'événements pour le nombre de chèques
            checkCount.addEventListener('input', updateCheckFields);
            
            // Ajouter l'écouteur d'événements pour le nombre de calendriers
            calendars.addEventListener('input', updateGrandTotal);
            
            // Initialisation
            updateCheckFields();
            updateGrandTotal();
        });
    </script>
</x-app-layout>