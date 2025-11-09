<div>
    @if(!$hasServices)
        <x-mary-alert
            title="Achat d'options non autorisé"
            description='Aucun service disponible. Veuillez souscrire à une offre "Batistack" avant de continuer.'
            icon="o-x-mark"
            class="alert-error"
        />
    @else
        <x-mary-header
            title="Options disponibles"
            subtitle="Ajoutez des options à votre service"
        />

        <div class="flex gap-6 mt-6">
            <!-- Section gauche - Sélection du service et options disponibles -->
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
                                <div class="text-sm text-gray-600 mt-1">{{ $service->product->name }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {!! $service->status->badge() !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-mary-card>

                <!-- Options disponibles -->
                @if($selectedService && !empty($availableOptions))
                    <x-mary-card>
                        <x-slot:title class="text-lg font-semibold text-blue-900">
                            Options disponibles
                        </x-slot:title>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($availableOptions as $option)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                    <!-- Image de l'option -->
                                    <div class="h-32 bg-gradient-to-br from-green-100 to-green-200 rounded-t-lg flex items-center justify-center">
                                        @if($option['media'])
                                            <img src="{{ $option['media'] }}" alt="{{ $option['name'] }}" class="h-16 w-16 object-contain">
                                        @else
                                            <div class="h-16 w-16 bg-green-300 rounded-lg flex items-center justify-center">
                                                <x-mary-icon name="o-cog-6-tooth" class="w-8 h-8 text-green-700" />
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Contenu de l'option -->
                                    <div class="p-4">
                                        <h3 class="font-semibold text-gray-900 mb-2">{{ $option['name'] }}</h3>
                                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $option['description'] }}</p>

                                        <!-- Prix -->
                                        <div class="flex items-center justify-between mb-4">
                                            <span class="text-lg font-bold text-green-600">{{ $option['price_formatted'] }}</span>
                                            <span class="text-xs text-gray-500">HT/mois</span>
                                        </div>

                                        <!-- Bouton d'action -->
                                        @if(isset($cart[$option['id']]))
                                            <button
                                                wire:click="removeFromCart({{ $option['id'] }})"
                                                class="w-full bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center justify-center gap-2"
                                            >
                                                <x-mary-icon name="o-minus" class="w-4 h-4" />
                                                Retirer
                                            </button>
                                        @else
                                            <button
                                                wire:click="addToCart({{ $option['id'] }})"
                                                class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center justify-center gap-2"
                                            >
                                                <x-mary-icon name="o-plus" class="w-4 h-4" />
                                                Ajouter
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-mary-card>
                @elseif($selectedService && empty($availableOptions))
                    <x-mary-card>
                        <div class="text-center py-8">
                            <x-mary-icon name="o-check-circle" class="w-16 h-16 text-green-500 mx-auto mb-4" />
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Toutes les options sont déjà installées</h3>
                            <p class="text-gray-600">Votre service dispose déjà de toutes les options disponibles.</p>
                        </div>
                    </x-mary-card>
                @endif
            </div>

            <!-- Section droite - Panier -->
            <div class="w-80">
                <x-mary-card class="sticky top-6">
                    <x-slot:title class="text-lg font-semibold text-blue-900 flex items-center gap-2">
                        <x-mary-icon name="o-shopping-cart" class="w-5 h-5" />
                        Panier d'options
                    </x-slot:title>

                    @if(!empty($cart))
                        <!-- Éléments du panier -->
                        <div class="space-y-3 mb-6">
                            @foreach($cart as $item)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <div class="font-medium text-sm text-gray-900">{{ $item['name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $item['price_formatted'] }}</div>
                                    </div>
                                    <button
                                        wire:click="removeFromCart({{ $item['id'] }})"
                                        class="text-red-500 hover:text-red-700 p-1"
                                        title="Retirer"
                                    >
                                        <x-mary-icon name="o-x-mark" class="w-4 h-4" />
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
                                <span class="font-medium">{{ number_format($this->getTaxAmount(), 2) }} €</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t pt-2">
                                <span>Total TTC</span>
                                <span class="text-blue-600">{{ number_format($this->getTotalWithTax(), 2) }} €</span>
                            </div>
                        </div>

                        <!-- Bouton de commande -->
                        <button
                            wire:click="subscribeOptions"
                            wire:loading.attr="disabled"
                            class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-medium py-3 px-4 rounded-lg transition-colors mt-6 flex items-center justify-center gap-2"
                        >
                            <span wire:loading.remove wire:target="subscribeOptions">
                                <x-mary-icon name="o-credit-card" class="w-5 h-5" />
                                Commander les options
                            </span>
                            <span wire:loading wire:target="subscribeOptions" class="flex items-center gap-2">
                                <x-mary-loading class="w-4 h-4" />
                                Traitement...
                            </span>
                        </button>
                    @else
                        <!-- Panier vide -->
                        <div class="text-center py-8">
                            <x-mary-icon name="o-shopping-cart" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                            <p class="text-gray-500 text-sm">Votre panier est vide</p>
                            <p class="text-gray-400 text-xs mt-1">Ajoutez des options pour commencer</p>
                        </div>
                    @endif
                </x-mary-card>
            </div>
        </div>
    @endif
</div>
