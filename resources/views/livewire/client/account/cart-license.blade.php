<div>
    <x-mary-header
        title="Souscrire à une licence"
        subtitle="Choisissez la licence qui correspond à vos besoins"
    />

    <div class="flex gap-6 mt-6">
        <!-- Section gauche - Sélection des licences -->
        <div class="flex-1">
            <!-- Toggle fréquence de paiement -->
            <x-mary-card class="mb-6">
                <x-slot:title class="text-lg font-semibold text-blue-900">
                    Fréquence de paiement
                </x-slot:title>
                <div class="flex items-center justify-center">
                    <div class="flex items-center bg-gray-100 rounded-lg p-1">
                        <button
                            wire:click="toggleFrequency"
                            class="px-6 py-2 rounded-md text-sm font-medium transition-all {{ !$isAnnual ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
                        >
                            Mensuel
                        </button>
                        <button
                            wire:click="toggleFrequency"
                            class="px-6 py-2 rounded-md text-sm font-medium transition-all {{ $isAnnual ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
                        >
                            Annuel
                            @if($isAnnual)
                                <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Économies</span>
                            @endif
                        </button>
                    </div>
                </div>
            </x-mary-card>

            <!-- Grille des licences -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($licenses as $license)
                    <div class="bg-white border-2 rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 {{ $selectedLicense == $license['id'] ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200 hover:border-gray-300' }}">
                        <!-- Header de la carte -->
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-bold text-gray-900">{{ $license['name'] }}</h3>
                                @if($selectedLicense == $license['id'])
                                    <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Prix -->
                            <div class="mb-4">
                                <div class="flex items-baseline">
                                    <span class="text-3xl font-bold text-blue-600">{{ $license['price_formatted'] }}</span>
                                    <span class="ml-2 text-gray-500">{{ $isAnnual ? '/an' : '/mois' }}</span>
                                </div>

                                @if($isAnnual && $license['monthly_equivalent'])
                                    <div class="text-sm text-gray-600 mt-1">
                                        Soit {{ $license['monthly_equivalent'] }}
                                    </div>
                                @endif

                                @if($isAnnual && $license['savings'])
                                    <div class="inline-flex items-center mt-2 px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                        Économisez {{ $license['savings']['amount'] }} ({{ $license['savings']['percent'] }})
                                    </div>
                                @endif
                            </div>

                            <!-- Description -->
                            @if($license['description'])
                                <p class="text-gray-600 text-sm mb-4">{{ $license['description'] }}</p>
                            @endif
                        </div>

                        <!-- Fonctionnalités -->
                        <div class="p-6">
                            @if(!empty($license['features']))
                                <h4 class="font-semibold text-gray-900 mb-3">Fonctionnalités incluses :</h4>
                                <ul class="space-y-2 mb-6">
                                    @foreach($license['features'] as $feature)
                                        <li class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            {{ $feature }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            <!-- Bouton de sélection -->
                            <button
                                wire:click="selectLicense({{ $license['id'] }})"
                                class="w-full py-3 px-4 rounded-lg font-medium transition-all {{ $selectedLicense == $license['id'] ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                            >
                                {{ $selectedLicense == $license['id'] ? 'Sélectionnée' : 'Sélectionner' }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(empty($licenses))
                <x-mary-card>
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune licence disponible</h3>
                        <p class="mt-1 text-sm text-gray-500">Les licences seront bientôt disponibles.</p>
                    </div>
                </x-mary-card>
            @endif
        </div>

        <!-- Section droite - Récapitulatif -->
        <div class="w-80">
            <x-mary-card class="sticky top-6">
                <x-slot:title class="text-lg font-semibold text-white bg-blue-600 -m-6 mb-4 p-4 rounded-t-lg">
                    Récapitulatif
                </x-slot:title>

                @if($selectedLicenseData)
                    <!-- Licence sélectionnée -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-gray-900">{{ $selectedLicenseData['name'] }}</span>
                        </div>
                        <div class="text-sm text-gray-600 mb-3">
                            Facturation {{ $isAnnual ? 'annuelle' : 'mensuelle' }}
                        </div>

                        @if($isAnnual && $selectedLicenseData['savings'])
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <div class="text-sm font-medium text-green-800">Économies annuelles</div>
                                        <div class="text-sm text-green-600">{{ $selectedLicenseData['savings']['amount'] }} ({{ $selectedLicenseData['savings']['percent'] }})</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Calcul des prix -->
                    <div class="border-t pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Sous-total HT</span>
                            <span class="font-medium">{{ number_format($selectedLicenseData['price'] / 1.2, 2) }} €</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">TVA (20%)</span>
                            <span class="font-medium">{{ number_format($selectedLicenseData['price'] - ($selectedLicenseData['price'] / 1.2), 2) }} €</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t pt-2">
                            <span>Total TTC</span>
                            <span class="text-blue-600">{{ $selectedLicenseData['price_formatted'] }}</span>
                        </div>
                        <div class="text-xs text-gray-500 text-center">
                            Paiement {{ $isAnnual ? 'annuel' : 'mensuel' }}
                        </div>
                    </div>

                    <!-- Fonctionnalités incluses -->
                    @if(!empty($selectedLicenseData['features']))
                        <div class="border-t pt-4 mt-4">
                            <h4 class="font-medium text-gray-900 mb-2">Inclus dans cette licence :</h4>
                            <ul class="space-y-1">
                                @foreach(array_slice($selectedLicenseData['features'], 0, 4) as $feature)
                                    <li class="flex items-center text-xs text-gray-600">
                                        <svg class="w-3 h-3 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                                @if(count($selectedLicenseData['features']) > 4)
                                    <li class="text-xs text-gray-500 ml-5">
                                        +{{ count($selectedLicenseData['features']) - 4 }} autres fonctionnalités
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    <!-- Bouton de souscription -->
                    <button
                        wire:click="subscribe"
                        class="w-full mt-6 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                    >
                        <span wire:loading.remove>
                            Souscrire maintenant →
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
                    <!-- Aucune licence sélectionnée -->
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune licence sélectionnée</h3>
                        <p class="mt-1 text-sm text-gray-500">Choisissez une licence pour voir le récapitulatif.</p>
                    </div>
                @endif
            </x-mary-card>
        </div>
    </div>
</div>

<x-filament-actions::modals />
</div>
