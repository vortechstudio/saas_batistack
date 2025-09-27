<div>
    @if(!$hasServices)
        <x-mary-alert
            title="Achat de modules non autorisé"
            description='Aucun service disponible. Veuillez souscrire à une offre "Batistack" avant de continuer.'
            icon="o-x-mark"
            class="alert-error"
        />
    @else
        <x-mary-header
            title="Modules disponibles"
            subtitle="Ajoutez des fonctionnalités à votre service"
        />

        <div class="flex gap-6 mt-6">
            <!-- Section gauche - Sélection du service et modules disponibles -->
            <div class="flex-1">
                <!-- Sélection du service -->
                <x-mary-card class="mb-6">
                    <x-slot:title class="text-lg font-semibold text-blue-900">
                        Sélectionnez votre service
                    </x-slot:title>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach(Auth::user()->customer->services as $service)
                            <div
                                class="p-4 border-2 rounded-lg cursor-pointer transition-all {{ $selectedService == $service->id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}"
                                wire:click="selectService({{ $service->id }})"
                            >
                                <div class="font-semibold text-gray-900">{{ $service->service_code }}</div>
                                <div class="text-sm text-gray-600">{{ $service->product->name }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {!! $service->status->badge() !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-mary-card>

                <!-- Modules disponibles -->
                @if($selectedService && !empty($availableFeatures))
                    <x-mary-card>
                        <x-slot:title class="text-lg font-semibold text-blue-900">
                            Modules disponibles
                        </x-slot:title>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($availableFeatures as $feature)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                    <!-- Image du module -->
                                    <div class="h-32 bg-gradient-to-br from-blue-100 to-blue-200 rounded-t-lg flex items-center justify-center">
                                        @if($feature['media'])
                                            <img src="{{ $feature['media'] }}" alt="{{ $feature['name'] }}" class="h-16 w-16 object-contain">
                                        @else
                                            <div class="h-16 w-16 bg-blue-500 rounded-lg flex items-center justify-center">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Contenu du module -->
                                    <div class="p-4">
                                        <h3 class="font-semibold text-gray-900 mb-2">{{ $feature['name'] }}</h3>
                                        <p class="text-sm text-gray-600 mb-4">{{ $feature['description'] }}</p>

                                        <!-- Prix et bouton -->
                                        <div class="flex items-center justify-between">
                                            <div class="text-lg font-bold text-blue-600">
                                                {{ $feature['price_formatted'] }}
                                                <span class="text-xs text-gray-500 font-normal">/mois</span>
                                            </div>

                                            @if(isset($cart[$feature['id']]))
                                                <button
                                                    wire:click="removeFromCart({{ $feature['id'] }})"
                                                    class="px-4 py-2 bg-red-500 text-white text-sm rounded-lg hover:bg-red-600 transition-colors"
                                                >
                                                    Retirer
                                                </button>
                                            @else
                                                <button
                                                    wire:click="addToCart({{ $feature['id'] }})"
                                                    class="px-4 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 transition-colors"
                                                >
                                                    Ajouter
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-mary-card>
                @elseif($selectedService)
                    <x-mary-card>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m8 0V4.5"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun module disponible</h3>
                            <p class="mt-1 text-sm text-gray-500">Tous les modules sont déjà installés sur ce service.</p>
                        </div>
                    </x-mary-card>
                @endif
            </div>

            <!-- Section droite - Panier -->
            <div class="w-80">
                <x-mary-card class="sticky top-6">
                    <x-slot:title class="text-lg font-semibold text-white bg-blue-600 -m-6 mb-4 p-4 rounded-t-lg">
                        Votre sélection
                    </x-slot:title>

                    @if(!empty($cart))
                        <!-- Items du panier -->
                        <div class="space-y-3 mb-4">
                            @foreach($cart as $item)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <div class="font-medium text-sm text-gray-900">{{ $item['name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $item['price_formatted'] }}/mois</div>
                                    </div>
                                    <button
                                        wire:click="removeFromCart({{ $item['id'] }})"
                                        class="text-red-500 hover:text-red-700 ml-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <!-- Récapitulatif des prix -->
                        <div class="border-t pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Sous-total HT</span>
                                <span class="font-medium">{{ number_format($cartTotal, 2) }} €</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">TVA (20%)</span>
                                <span class="font-medium">{{ number_format($cartTotal * 0.2, 2) }} €</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t pt-2">
                                <span>Total TTC</span>
                                <span class="text-blue-600">{{ number_format($cartTotal * 1.2, 2) }} €</span>
                            </div>
                            <div class="text-xs text-gray-500 text-center">
                                {{ count($cart) }} module{{ count($cart) > 1 ? 's' : '' }}
                            </div>
                        </div>

                        <!-- Bouton de commande -->
                        <button
                            wire:click="checkout"
                            class="w-full mt-4 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50"
                        >
                            <span wire:loading.remove>
                                Poursuivre la commande →
                            </span>
                            <span wire:loading class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Traitement...
                            </span>
                        </button>
                    @else
                        <!-- Panier vide -->
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 3H3m4 10v6a1 1 0 001 1h8a1 1 0 001-1v-6M9 17h6"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Panier vide</h3>
                            <p class="mt-1 text-sm text-gray-500">Ajoutez des modules à votre panier pour continuer.</p>
                        </div>
                    @endif
                </x-mary-card>
            </div>
        </div>
    @endif
</div>
