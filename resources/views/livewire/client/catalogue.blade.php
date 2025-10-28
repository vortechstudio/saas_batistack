<div>
    <div class="w-[75%] mx-auto mb-10">
        <span class="text-blue-800 text-4xl font-black mb-4">Notre catalogue de service</span>        
    </div>
    <div class="hero bg-blue-700 text-white min-h-[40vh] rounded-lg mb-10">
        <div class="hero-content flex-col lg:flex-row-reverse">
            <img
            src="https://img.daisyui.com/images/stock/photo-1635805737707-575885ab0820.webp"
            class="max-w-lg rounded-lg shadow-2xl"
            />
            <div>
                <h1 class="text-6xl font-bold">Nos logiciels dédiés aux professionnels du bâtiment</h1>
                <p class="py-6 text-2xl">
                    Artisans du bâtiment, créez et chiffrez vos devis bâtiment en quelques clics. Facturez plus vite et automatiquement. Gagnez du temps et de la rentabilité. Gérez enfin votre entreprise comme un pro !
                </p>
            </div>
        </div>
    </div>
    <div class="flex flex-col items-center justify-center">
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
        <div class="grid grid-cols-3 gap-6">
            @foreach ($licences as $k => $license)
            <div class="card @if($k == 1) bg-blue-300 shadow-lg w-98 @else  bg-base-200 shadow-sm w-96 @endif ">
                <div class="card-body">
                    <div class="flex justify-between">
                    <h2 class="text-2xl font-bold">{{ $license["name"] }}</h2>
                    <span class="text-lg">
                        {{ $license['price_formatted'] }}{{ $isAnnual ? '/an' : '/mois' }}
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
                    </span>
                    </div>
                    @if($license['description'])
                        <p class="text-gray-600 text-sm mb-4">{{ $license['description'] }}</p>
                    @endif
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
                    <div class="mt-6">
                        <a href="{{ route('client.account.cart.license') }}" class="btn btn-primary btn-block">Souscrire</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
