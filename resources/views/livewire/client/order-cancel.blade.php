<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête d'annulation -->
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                {{ $cancelReason === 'failed' ? 'Paiement échoué' : 'Paiement annulé' }}
            </h1>
            <p class="text-lg text-gray-600">{{ $message }}</p>
        </div>

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
                                <span>Montant :</span>
                                <span>{{ number_format($invoice->total_amount, 2) }} {{ strtoupper($invoice->currency) }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Statut :</span>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                {{ $invoice->status->label() }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Options d'action -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Que souhaitez-vous faire ?</h2>

                <div class="space-y-4">
                    @if($canRetry)
                        <div class="p-4 bg-blue-50 rounded-md">
                            <h3 class="font-medium text-blue-900 mb-2">Réessayer le paiement</h3>
                            <p class="text-blue-700 text-sm mb-3">Vous pouvez réessayer le paiement de cette commande.</p>
                            <button wire:click="retryPayment" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                Réessayer le paiement
                            </button>
                        </div>
                    @endif

                    <div class="p-4 bg-green-50 rounded-md">
                        <h3 class="font-medium text-green-900 mb-2">Nouvelle commande</h3>
                        <p class="text-green-700 text-sm mb-3">Créer une nouvelle commande avec les mêmes paramètres.</p>
                        <button wire:click="createNewOrder" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                            Nouvelle commande
                        </button>
                    </div>

                    <div class="p-4 bg-yellow-50 rounded-md">
                        <h3 class="font-medium text-yellow-900 mb-2">Besoin d'aide ?</h3>
                        <p class="text-yellow-700 text-sm mb-3">Contactez notre support pour obtenir de l'aide.</p>
                        <button wire:click="contactSupport" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition-colors">
                            Contacter le support
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions principales -->
        <div class="mt-8 flex justify-center">
            <button wire:click="goToDashboard" class="bg-gray-600 text-white px-6 py-3 rounded-md hover:bg-gray-700 transition-colors">
                Retour au tableau de bord
            </button>
        </div>
    </div>
</div>
