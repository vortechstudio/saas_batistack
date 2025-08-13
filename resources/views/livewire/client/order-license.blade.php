<div class="max-w-4xl mx-auto p-6">
    <!-- Indicateur de progression -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @for ($i = 1; $i <= $totalSteps; $i++)
                <div class="flex items-center {{ $i < $totalSteps ? 'flex-1' : '' }}">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full
                        {{ $currentStep >= $i ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600' }}">
                        {{ $i }}
                    </div>
                    @if ($i < $totalSteps)
                        <div class="flex-1 h-1 mx-2
                            {{ $currentStep > $i ? 'bg-blue-600' : 'bg-gray-300' }}"></div>
                    @endif
                </div>
            @endfor
        </div>
        <div class="flex justify-between mt-2 text-sm text-gray-600">
            <span>Produit</span>
            <span>Facturation</span>
            <span>Domaine</span>
            <span>Modules</span>
            <span>Options</span>
            <span>Facture</span>
            <span>Paiement</span>
        </div>
    </div>

    <!-- Étape 1: Choix du produit -->
    @if ($currentStep === 1)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-6">Choisissez votre type de licence</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($products as $product)
                    <div class="border rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer
                        {{ $selectedProduct && $selectedProduct->id === $product->id ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}"
                        wire:click="selectProduct({{ $product->id }})">

                        @if ($product->is_featured)
                            <div class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm mb-4 inline-block">
                                Recommandé
                            </div>
                        @endif

                        <h3 class="text-xl font-semibold mb-2">{{ $product->name }}</h3>
                        <p class="text-gray-600 mb-4">{{ $product->description }}</p>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Utilisateurs max:</span>
                                <span>{{ $product->max_users ?? 'Illimité' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Projets max:</span>
                                <span>{{ $product->max_projects ?? 'Illimité' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Stockage:</span>
                                <span>{{ $product->storage_limit ? $product->storage_limit . ' GB' : 'Illimité' }}</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t">
                            <div class="text-2xl font-bold text-blue-600">
                                {{ number_format($product->base_price, 2) }}€
                                <span class="text-sm text-gray-500">/mois</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Étape 2: Cycle de facturation -->
    @if ($currentStep === 2)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-6">Choisissez votre cycle de facturation</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="border rounded-lg p-6 cursor-pointer hover:shadow-lg transition-shadow
                    {{ $billingCycle === 'monthly' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}"
                    wire:click="setBillingCycle('monthly')">
                    <h3 class="text-xl font-semibold mb-2">Facturation Mensuelle</h3>
                    <p class="text-gray-600 mb-4">Payez chaque mois, résiliez à tout moment</p>
                    <div class="text-2xl font-bold text-blue-600">
                        {{ number_format($selectedProduct->base_price, 2) }}€/mois
                    </div>
                </div>

                <div class="border rounded-lg p-6 cursor-pointer hover:shadow-lg transition-shadow relative
                    {{ $billingCycle === 'yearly' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}"
                    wire:click="setBillingCycle('yearly')">
                    <div class="bg-green-500 text-white px-3 py-1 rounded-full text-sm mb-4 inline-block">
                        2 mois gratuits
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Facturation Annuelle</h3>
                    <p class="text-gray-600 mb-4">Économisez avec le paiement annuel</p>
                    <div class="text-2xl font-bold text-blue-600">
                        {{ number_format($selectedProduct->base_price * 10, 2) }}€/an
                        <div class="text-sm text-gray-500">
                            ({{ number_format($selectedProduct->base_price * 10 / 12, 2) }}€/mois)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Étape 3: Domaine -->
    @if ($currentStep === 3)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-6">Domaine de votre licence</h2>
            <div class="max-w-md">
                <label for="domain" class="block text-sm font-medium text-gray-700 mb-2">
                    Nom de domaine ou sous-domaine
                </label>
                <input type="text"
                    id="domain"
                    wire:model="domain"
                    placeholder="exemple.com ou app.exemple.com"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('domain')
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                @enderror
                <p class="text-sm text-gray-500 mt-2">
                    Ce domaine sera lié à votre licence et ne pourra pas être modifié ultérieurement.
                </p>
            </div>
            <div class="mt-6">
                <button wire:click="setDomain"
                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700"
                    {{ empty($domain) ? 'disabled' : '' }}>
                    Continuer
                </button>
            </div>
        </div>
    @endif

    <!-- Étape 4: Modules -->
    @if ($currentStep === 4)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-6">Modules supplémentaires</h2>

            <!-- Modules inclus -->
            @if ($selectedProduct->includedModules->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-3 text-green-600">✓ Modules inclus</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($selectedProduct->includedModules as $module)
                            <div class="border border-green-200 bg-green-50 rounded-lg p-4">
                                <h4 class="font-semibold">{{ $module->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $module->description }}</p>
                                <span class="text-green-600 font-medium">Inclus</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Modules optionnels -->
            @if ($availableModules->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-3">Modules optionnels</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($availableModules as $module)
                            <div class="border rounded-lg p-4 cursor-pointer hover:shadow-lg transition-shadow
                                {{ in_array($module->id, $selectedModules) ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}"
                                wire:click="toggleModule({{ $module->id }})">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold">{{ $module->name }}</h4>
                                    <input type="checkbox"
                                        {{ in_array($module->id, $selectedModules) ? 'checked' : '' }}
                                        class="rounded">
                                </div>
                                <p class="text-sm text-gray-600 mb-2">{{ $module->description }}</p>
                                <div class="text-blue-600 font-medium">
                                    +{{ number_format($module->pivot->price_override ?? $module->base_price, 2) }}€/mois
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <button wire:click="confirmModulesAndOptions"
                class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                Continuer
            </button>
        </div>
    @endif

    <!-- Étape 5: Options -->
    @if ($currentStep === 5)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-6">Options supplémentaires</h2>

            @if ($availableOptions->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    @foreach ($availableOptions as $option)
                        <div class="border rounded-lg p-4 cursor-pointer hover:shadow-lg transition-shadow
                            {{ in_array($option->id, $selectedOptions) ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}"
                            wire:click="toggleOption({{ $option->id }})">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold">{{ $option->name }}</h4>
                                <input type="checkbox"
                                    {{ in_array($option->id, $selectedOptions) ? 'checked' : '' }}
                                    class="rounded">
                            </div>
                            <p class="text-sm text-gray-600 mb-2">{{ $option->description }}</p>
                            <div class="text-blue-600 font-medium">
                                +{{ number_format($option->price, 2) }}€
                                <span class="text-sm">/{{ $option->billing_cycle->value }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 mb-6">Aucune option supplémentaire disponible pour ce produit.</p>
            @endif

            <button wire:click="confirmModulesAndOptions"
                class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                Continuer
            </button>
        </div>
    @endif

    <!-- Étape 6: Récapitulatif et génération de facture -->
    @if ($currentStep === 6)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-6">Récapitulatif de votre commande</h2>

            <!-- Récapitulatif -->
            <div class="space-y-4 mb-6">
                <div class="flex justify-between items-center py-2 border-b">
                    <span class="font-medium">{{ $selectedProduct->name }} ({{ ucfirst($billingCycle) }})</span>
                    <span>{{ number_format($selectedProduct->base_price, 2) }}€</span>
                </div>

                @foreach ($selectedModules as $moduleId)
                    @php $module = $availableModules->find($moduleId) @endphp
                    <div class="flex justify-between items-center py-2 border-b">
                        <span>Module: {{ $module->name }}</span>
                        <span>+{{ number_format($module->pivot->price_override ?? $module->base_price, 2) }}€</span>
                    </div>
                @endforeach

                @foreach ($selectedOptions as $optionId)
                    @php $option = $availableOptions->find($optionId) @endphp
                    <div class="flex justify-between items-center py-2 border-b">
                        <span>Option: {{ $option->name }}</span>
                        <span>+{{ number_format($option->price, 2) }}€</span>
                    </div>
                @endforeach

                <div class="flex justify-between items-center py-2 font-bold text-lg">
                    <span>Total</span>
                    <span>{{ number_format($total, 2) }}€</span>
                </div>
            </div>

            @if (!$invoice)
                <button wire:click="generateInvoice"
                    class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700">
                    Générer la facture
                </button>
            @else
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <p class="text-green-800">
                        ✓ Facture générée: {{ $invoice->invoice_number }}
                    </p>
                </div>

                <button wire:click="payInvoice"
                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                    Procéder au paiement
                </button>
            @endif
        </div>
    @endif

    <!-- Navigation -->
    @if ($currentStep > 1 && $currentStep < 6)
        <div class="mt-6 flex justify-between">
            <button wire:click="previousStep"
                class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                Précédent
            </button>
        </div>
    @endif
</div>
