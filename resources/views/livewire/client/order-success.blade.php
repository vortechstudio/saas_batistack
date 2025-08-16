<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête de succès -->
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Paiement réussi !</h1>
            <p class="text-lg text-gray-600">Merci pour votre commande. Voici les détails de votre achat.</p>
        </div>

        <!-- Message de statut -->
        @if($message)
            <div class="mb-6 p-4 rounded-md {{ $messageType === 'success' ? 'bg-green-50 text-green-800' : ($messageType === 'warning' ? 'bg-yellow-50 text-yellow-800' : ($messageType === 'error' ? 'bg-red-50 text-red-800' : 'bg-blue-50 text-blue-800')) }}">
                {{ $message }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Détails de la commande -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Détails de la commande</h2>

                @if($invoice)
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Numéro de facture :</span>
                            <span class="font-medium">{{ $invoice->invoice_number }}</span>
                        </div>

                        @if($orderDetails)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Produit :</span>
                                <span class="font-medium">{{ $orderDetails['product'] }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-600">Domaine :</span>
                                <span class="font-medium">{{ $orderDetails['domain'] }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-600">Cycle de facturation :</span>
                                <span class="font-medium">{{ $orderDetails['billing_cycle'] === 'yearly' ? 'Annuel' : 'Mensuel' }}</span>
                            </div>
                        @endif

                        <div class="border-t pt-3">
                            <div class="flex justify-between text-lg font-semibold">
                                <span>Total payé :</span>
                                <span>{{ number_format($invoice->total_amount, 2) }} {{ strtoupper($invoice->currency) }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Date de paiement :</span>
                            <span class="font-medium">{{ $invoice->paid_at?->format('d/m/Y H:i') ?? 'En cours' }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Informations de licence -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Votre licence</h2>

                @if($licenseCreated && $license)
                    <div class="space-y-3">
                        <div class="p-4 bg-green-50 rounded-md">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-green-800 font-medium">Licence activée</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Clé de licence :</span>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm font-mono">{{ $license->license_key }}</code>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-600">Statut :</span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ $license->status->label() }}
                                </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-600">Expire le :</span>
                                <span class="font-medium">{{ $license->expires_at->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button wire:click="downloadLicense" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                Télécharger la licence
                            </button>
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-yellow-50 rounded-md">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-yellow-800 font-medium">Licence en cours de création</span>
                        </div>
                        <p class="text-yellow-700 mt-2 text-sm">Votre licence sera disponible sous peu. Vous recevrez un email de confirmation.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <button wire:click="goToDashboard" class="bg-gray-600 text-white px-6 py-3 rounded-md hover:bg-gray-700 transition-colors">
                Aller au tableau de bord
            </button>

            @if($licenseCreated)
                <button wire:click="goToLicenses" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors">
                    Gérer mes licences
                </button>
            @endif
        </div>
    </div>
</div>
