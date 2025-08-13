<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mes Factures</h1>
            <p class="text-gray-600 dark:text-gray-400">Gérez et consultez toutes vos factures</p>
        </div>
    </div>

    @if($customer)
        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total factures -->
            <x-mary-card class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $stats['total_invoices'] }}</div>
                        <div class="text-blue-100">Total factures</div>
                    </div>
                    <x-mary-icon name="o-document-text" class="h-8 w-8 text-blue-200" />
                </div>
            </x-mary-card>

            <!-- Factures payées -->
            <x-mary-card class="bg-gradient-to-r from-green-500 to-green-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $stats['paid_invoices'] }}</div>
                        <div class="text-green-100">Payées</div>
                    </div>
                    <x-mary-icon name="o-check-circle" class="h-8 w-8 text-green-200" />
                </div>
            </x-mary-card>

            <!-- Factures en attente -->
            <x-mary-card class="bg-gradient-to-r from-orange-500 to-orange-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $stats['pending_invoices'] }}</div>
                        <div class="text-orange-100">En attente</div>
                    </div>
                    <x-mary-icon name="o-clock" class="h-8 w-8 text-orange-200" />
                </div>
            </x-mary-card>

            <!-- Montant à payer -->
            <x-mary-card class="bg-gradient-to-r from-red-500 to-red-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-white">{{ number_format($stats['pending_amount'], 2) }}€</div>
                        <div class="text-red-100">À payer</div>
                    </div>
                    <x-mary-icon name="o-exclamation-triangle" class="h-8 w-8 text-red-200" />
                </div>
            </x-mary-card>
        </div>

        <!-- Filtres et recherche -->
        <x-mary-card>
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex flex-col md:flex-row gap-4 flex-1">
                    <!-- Recherche -->
                    <x-mary-input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher par numéro ou description..."
                        icon="o-magnifying-glass"
                        class="md:w-80"
                    />

                    <!-- Filtre par statut -->
                    <x-mary-select
                        wire:model.live="statusFilter"
                        :options="[
                            ['id' => 'all', 'name' => 'Tous les statuts'],
                            ['id' => 'pending', 'name' => 'En attente'],
                            ['id' => 'paid', 'name' => 'Payées'],
                            ['id' => 'overdue', 'name' => 'En retard'],
                            ['id' => 'cancelled', 'name' => 'Annulées']
                        ]"
                        option-value="id"
                        option-label="name"
                        class="md:w-48"
                    />
                </div>
            </div>
        </x-mary-card>

        <!-- Liste des factures -->
        <x-mary-card>
            @if($invoices->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th wire:click="sortBy('invoice_number')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <div class="flex items-center space-x-1">
                                        <span>N° Facture</span>
                                        @if($sortBy === 'invoice_number')
                                            <x-mary-icon name="{{ $sortDirection === 'asc' ? 'o-chevron-up' : 'o-chevron-down' }}" class="h-4 w-4" />
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <div class="flex items-center space-x-1">
                                        <span>Date</span>
                                        @if($sortBy === 'created_at')
                                            <x-mary-icon name="{{ $sortDirection === 'asc' ? 'o-chevron-up' : 'o-chevron-down' }}" class="h-4 w-4" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                                <th wire:click="sortBy('total_amount')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <div class="flex items-center space-x-1">
                                        <span>Montant</span>
                                        @if($sortBy === 'total_amount')
                                            <x-mary-icon name="{{ $sortDirection === 'asc' ? 'o-chevron-up' : 'o-chevron-down' }}" class="h-4 w-4" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Échéance</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($invoices as $invoice)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $invoice->invoice_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $invoice->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        <div class="max-w-xs truncate" title="{{ $invoice->description }}">
                                            {{ $invoice->description ?: 'Facture de service' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($invoice->total_amount, 2) }}€
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-mary-badge
                                            :value="$invoice->status->label()"
                                            class="{{ $invoice->status === App\Enums\InvoiceStatus::PAID ? 'badge-success' :
                                                     ($invoice->status === App\Enums\InvoiceStatus::OVERDUE ? 'badge-error' : 'badge-warning') }}"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if($invoice->due_date)
                                            {{ $invoice->due_date->format('d/m/Y') }}
                                            @if($invoice->due_date->isPast() && $invoice->status !== App\Enums\InvoiceStatus::PAID)
                                                <span class="text-red-500 text-xs block">En retard</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <!-- Bouton Voir -->
                                            <x-mary-button
                                                wire:click="showInvoiceDetails({{ $invoice->id }})"
                                                class="btn-sm btn-outline"
                                                icon="o-eye"
                                            >
                                                Voir
                                            </x-mary-button>

                                            <!-- Bouton Télécharger -->
                                            <x-mary-button
                                                wire:click="downloadInvoice({{ $invoice->id }})"
                                                class="btn-sm btn-outline"
                                                icon="o-arrow-down-tray"
                                            >
                                                PDF
                                            </x-mary-button>

                                            <!-- Bouton Payer (si non payée) -->
                                            @if($invoice->status !== App\Enums\InvoiceStatus::PAID)
                                                <x-mary-button
                                                    wire:click="payInvoice({{ $invoice->id }})"
                                                    class="btn-sm btn-primary"
                                                    icon="o-credit-card"
                                                >
                                                    Payer
                                                </x-mary-button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $invoices->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <x-mary-icon name="o-document-text" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-4 text-lg font-medium text-gray-600 dark:text-gray-400">Aucune facture</h3>
                    <p class="mt-2 text-gray-500">
                        @if($search || $statusFilter !== 'all')
                            Aucune facture ne correspond à vos critères de recherche.
                        @else
                            Vous n'avez pas encore de factures.
                        @endif
                    </p>
                </div>
            @endif
        </x-mary-card>
    @else
        <x-mary-alert icon="o-exclamation-triangle" class="alert-warning">
            <x-slot:title>Profil client requis</x-slot:title>
            Vous devez avoir un profil client pour accéder à vos factures.
        </x-mary-alert>
    @endif

    <!-- Modal de détail de facture -->
    <x-mary-modal wire:model="showInvoiceModal" title="Détail de la facture" class="backdrop-blur">
        @if($selectedInvoice)
            <div class="space-y-6">
                <!-- En-tête de la facture -->
                <div class="border-b pb-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold">{{ $selectedInvoice->invoice_number }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ $selectedInvoice->created_at->format('d/m/Y') }}</p>
                        </div>
                        <x-mary-badge
                            :value="$selectedInvoice->status->label()"
                            class="{{ $selectedInvoice->status === App\Enums\InvoiceStatus::PAID ? 'badge-success' :
                                     ($selectedInvoice->status === App\Enums\InvoiceStatus::OVERDUE ? 'badge-error' : 'badge-warning') }}"
                        />
                    </div>
                </div>

                <!-- Informations générales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white mb-2">Informations</h4>
                        <div class="space-y-1 text-sm">
                            @if($selectedInvoice->description)
                                <div><span class="font-medium">Description :</span> {{ $selectedInvoice->description }}</div>
                            @endif
                            @if($selectedInvoice->due_date)
                                <div><span class="font-medium">Échéance :</span> {{ $selectedInvoice->due_date->format('d/m/Y') }}</div>
                            @endif
                            <div><span class="font-medium">Devise :</span> {{ strtoupper($selectedInvoice->currency) }}</div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white mb-2">Montants</h4>
                        <div class="space-y-1 text-sm">
                            <div><span class="font-medium">Sous-total :</span> {{ number_format($selectedInvoice->subtotal_amount, 2) }}€</div>
                            <div><span class="font-medium">TVA :</span> {{ number_format($selectedInvoice->tax_amount, 2) }}€</div>
                            <div class="font-semibold text-lg"><span class="font-medium">Total :</span> {{ number_format($selectedInvoice->total_amount, 2) }}€</div>
                        </div>
                    </div>
                </div>

                <!-- Lignes de facture -->
                @if($selectedInvoice->invoiceItems->count() > 0)
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Détail des services</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Description</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Qté</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Prix unit.</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($selectedInvoice->invoiceItems as $item)
                                        <tr>
                                            <td class="px-4 py-2 text-sm">
                                                {{ $item->description }}
                                                @if($item->product)
                                                    <div class="text-xs text-gray-500">{{ $item->product->name }}</div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm text-center">{{ $item->quantity }}</td>
                                            <td class="px-4 py-2 text-sm text-right">{{ number_format($item->unit_price, 2) }}€</td>
                                            <td class="px-4 py-2 text-sm text-right font-medium">{{ number_format($item->total_price, 2) }}€</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Historique des paiements -->
                @if($selectedInvoice->payments->count() > 0)
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Historique des paiements</h4>
                        <div class="space-y-2">
                            @foreach($selectedInvoice->payments as $payment)
                                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800 rounded">
                                    <div>
                                        <div class="font-medium">{{ number_format($payment->amount, 2) }}€</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ $payment->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <x-mary-badge
                                        :value="$payment->status->label()"
                                        class="{{ $payment->status === App\Enums\PaymentStatus::SUCCEEDED ? 'badge-success' : 'badge-warning' }}"
                                    />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <x-slot:actions>
                <x-mary-button label="Fermer" wire:click="closeInvoiceModal" />
                <x-mary-button label="Télécharger PDF" wire:click="downloadInvoice({{ $selectedInvoice->id }})" class="btn-outline" icon="o-arrow-down-tray" />
                @if($selectedInvoice->status !== App\Enums\InvoiceStatus::PAID)
                    <x-mary-button label="Payer" wire:click="payInvoice({{ $selectedInvoice->id }})" class="btn-primary" icon="o-credit-card" />
                @endif
            </x-slot:actions>
        @endif
    </x-mary-modal>
</div>
