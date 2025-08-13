<div class="space-y-6">
    <!-- En-tête avec titre et actions -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tableau de bord</h1>
            <p class="text-gray-600 dark:text-gray-400">Bienvenue {{ auth()->user()->name }}, voici un aperçu de votre compte.</p>
        </div>
    </div>

    @if($customer)
        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Licences actives -->
            <x-mary-card class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $stats['active_licenses'] }}</div>
                        <div class="text-blue-100">Licences actives</div>
                    </div>
                    <x-mary-icon name="o-shield-check" class="h-8 w-8 text-blue-200" />
                </div>
            </x-mary-card>

            <!-- Modules actifs -->
            <x-mary-card class="bg-gradient-to-r from-green-500 to-green-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $stats['active_modules'] }}</div>
                        <div class="text-green-100">Modules actifs</div>
                    </div>
                    <x-mary-icon name="o-puzzle-piece" class="h-8 w-8 text-green-200" />
                </div>
            </x-mary-card>

            <!-- Licences expirant -->
            <x-mary-card class="bg-gradient-to-r from-orange-500 to-orange-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $stats['expiring_soon'] }}</div>
                        <div class="text-orange-100">Expirent bientôt</div>
                    </div>
                    <x-mary-icon name="o-clock" class="h-8 w-8 text-orange-200" />
                </div>
            </x-mary-card>

            <!-- Montant impayé -->
            <x-mary-card class="bg-gradient-to-r from-red-500 to-red-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-white">{{ number_format($stats['unpaid_amount'], 2) }}€</div>
                        <div class="text-red-100">Montant impayé</div>
                    </div>
                    <x-mary-icon name="o-exclamation-triangle" class="h-8 w-8 text-red-200" />
                </div>
            </x-mary-card>
        </div>

        <!-- Alertes -->
        @if($expiringLicenses->count() > 0 || $unpaidInvoices->count() > 0)
            <div class="space-y-4">
                @if($expiringLicenses->count() > 0)
                    <x-mary-alert icon="o-exclamation-triangle" class="alert-warning">
                        <x-slot:title>Licences expirant bientôt</x-slot:title>
                        {{ $expiringLicenses->count() }} licence(s) expirent dans les 30 prochains jours.
                        <x-slot:actions>
                            <x-mary-button link="{{ route('client.licenses') }}" class="btn-sm btn-outline">
                                Voir les licences
                            </x-mary-button>
                        </x-slot:actions>
                    </x-mary-alert>
                @endif

                @if($unpaidInvoices->count() > 0)
                    <x-mary-alert icon="o-exclamation-circle" class="alert-error">
                        <x-slot:title>Factures impayées</x-slot:title>
                        Vous avez {{ $unpaidInvoices->count() }} facture(s) impayée(s) pour un montant de {{ number_format($stats['unpaid_amount'], 2) }}€.
                        <x-slot:actions>
                            <x-mary-button link="{{ route('client.invoices') }}" class="btn-sm btn-outline">
                                Voir les factures
                            </x-mary-button>
                        </x-slot:actions>
                    </x-mary-alert>
                @endif
            </div>
        @endif

        <!-- Liste des licences -->
        <x-mary-card>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold">Mes licences</h2>
                <x-mary-button link="{{ route('client.licenses') }}" class="btn-sm btn-outline">
                    Voir toutes
                </x-mary-button>
            </div>

            @if($licenses->count() > 0)
                <div class="space-y-4">
                    @foreach($licenses->take(5) as $license)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <h3 class="font-medium">{{ $license->product->name }}</h3>
                                        <x-mary-badge
                                            :value="$license->status->label()"
                                            class="{{ $license->status === App\Enums\LicenseStatus::ACTIVE ? 'badge-success' :
                                                     ($license->status === App\Enums\LicenseStatus::EXPIRED ? 'badge-error' : 'badge-warning') }}"
                                        />
                                    </div>

                                    <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600 dark:text-gray-400">
                                        <div>
                                            <span class="font-medium">Clé :</span> {{ $license->license_key }}
                                        </div>
                                        @if($license->expires_at)
                                            <div>
                                                <span class="font-medium">Expire le :</span> {{ $license->expires_at->format('d/m/Y') }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="font-medium">Utilisateurs :</span> {{ $license->current_users }}/{{ $license->max_users }}
                                        </div>
                                    </div>

                                    @if($license->modules->count() > 0)
                                        <div class="mt-3">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Modules actifs :</span>
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach($license->modules->take(3) as $module)
                                                    <x-mary-badge :value="$module->name" class="badge-outline badge-sm" />
                                                @endforeach
                                                @if($license->modules->count() > 3)
                                                    <x-mary-badge :value="'+' . ($license->modules->count() - 3) . ' autres'" class="badge-outline badge-sm" />
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center space-x-2 ml-4">
                                    @if($license->status === App\Enums\LicenseStatus::PENDING)
                                        <x-mary-button wire:click="activateLicense({{ $license->id }})" class="btn-sm btn-primary">
                                            Activer
                                        </x-mary-button>
                                    @endif

                                    <!-- Bouton d'accès direct si le domaine est configuré -->
                                    @if($license->status === App\Enums\LicenseStatus::ACTIVE && $license->hasDomain())
                                        <x-mary-button
                                            wire:click="accessServiceDirect({{ $license->id }})"
                                            class="btn-sm btn-success"
                                            icon="o-arrow-top-right-on-square"
                                        >
                                            Accéder
                                        </x-mary-button>
                                    @endif

                                    <x-mary-dropdown>
                                        <x-slot:trigger>
                                            <x-mary-button icon="o-ellipsis-horizontal" class="btn-sm btn-outline" />
                                        </x-slot:trigger>

                                        <x-mary-menu-item title="Télécharger" icon="o-arrow-down-tray" wire:click="downloadLicense({{ $license->id }})" />

                                        @if($license->status === App\Enums\LicenseStatus::ACTIVE)
                                            @if($license->hasDomain())
                                                <x-mary-menu-item title="Accéder au service" icon="o-link" wire:click="showServiceAccess({{ $license->id }})" />
                                            @else
                                                <x-mary-menu-item title="Domaine en cours de configuration" icon="o-clock" disabled />
                                            @endif
                                        @else
                                            <x-mary-menu-item title="Licence inactive" icon="o-x-circle" disabled />
                                        @endif

                                        <x-mary-menu-item title="Configurer" icon="o-cog" link="#" />
                                    </x-mary-dropdown>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-mary-icon name="o-shield-exclamation" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-4 text-lg font-medium text-gray-600 dark:text-gray-400">Aucune licence</h3>
                    <p class="mt-2 text-gray-500">Vous n'avez pas encore de licence active.</p>
                    <div class="mt-4">
                        <x-mary-button link="{{ route('client.licenses') }}" class="btn-primary">
                            Découvrir nos produits
                        </x-mary-button>
                    </div>
                </div>
            @endif
        </x-mary-card>

        <!-- Actions rapides -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-mary-card class="hover:shadow-lg transition-shadow cursor-pointer" onclick="window.location.href='{{ route('client.order') }}'" wire:navigate>
                <div class="text-center py-6">
                    <x-mary-icon name="o-shopping-cart" class="mx-auto h-8 w-8 text-amber-500 mb-3" />
                    <h3 class="font-medium">Commander une nouvelle licence</h3>
                    <p class="mt-1 text-sm text-gray-600">Commandez une licence pour accéder à nos services</p>
                </div>
            </x-mary-card>
            <x-mary-card class="hover:shadow-lg transition-shadow cursor-pointer" onclick="window.location.href='{{ route('client.licenses') }}'">
                <div class="text-center py-6">
                    <x-mary-icon name="o-shield-check" class="mx-auto h-8 w-8 text-blue-500 mb-3" />
                    <h3 class="font-medium">Gérer les licences</h3>
                    <p class="mt-1 text-sm text-gray-600">Activez, configurez et gérez vos licences</p>
                </div>
            </x-mary-card>

            <x-mary-card class="hover:shadow-lg transition-shadow cursor-pointer" onclick="window.location.href='{{ route('client.invoices') }}'">
                <div class="text-center py-6">
                    <x-mary-icon name="o-document-text" class="mx-auto h-8 w-8 text-green-500 mb-3" />
                    <h3 class="font-medium">Facturation</h3>
                    <p class="mt-1 text-sm text-gray-600">Consultez vos factures et paiements</p>
                </div>
            </x-mary-card>

            <x-mary-card class="hover:shadow-lg transition-shadow cursor-pointer" onclick="window.location.href='{{ route('client.support') }}'">
                <div class="text-center py-6">
                    <x-mary-icon name="o-chat-bubble-left-right" class="mx-auto h-8 w-8 text-purple-500 mb-3" />
                    <h3 class="font-medium">Support</h3>
                    <p class="mt-1 text-sm text-gray-600">Obtenez de l'aide et du support</p>
                </div>
            </x-mary-card>
        </div>
    @endif

    <!-- Modal d'accès au service -->
    @if($showServiceAccess && $selectedServiceLicense)
        <x-mary-modal
            wire:model="showServiceAccess"
            title="Accéder au service - {{ $selectedServiceLicense->product->name }}"
            class="backdrop-blur"
            box-class="!max-w-4xl !w-full"
        >
            <div class="space-y-6">
                <!-- Informations de connexion -->
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <h3 class="font-medium text-blue-900 dark:text-blue-100 mb-3">
                        <x-mary-icon name="o-globe-alt" class="w-5 h-5 inline mr-2" />
                        Informations de connexion
                    </h3>

                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">URL du service :</label>
                            <div class="mt-1 p-3 bg-white dark:bg-gray-800 border rounded-md flex items-center justify-between">
                                <a href="{{ $selectedServiceLicense->getServiceUrl() }}"
                                   target="_blank"
                                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400 font-mono text-sm">
                                    {{ $selectedServiceLicense->getServiceUrl() }}
                                </a>
                                <x-mary-button
                                    icon="o-clipboard"
                                    class="btn-xs btn-ghost"
                                    onclick="navigator.clipboard.writeText('{{ $selectedServiceLicense->getServiceUrl() }}');
                                             $dispatch('mary-toast', {message: 'URL copiée!', type: 'success'})"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Clé de licence :</label>
                            <div class="mt-1 p-3 bg-white dark:bg-gray-800 border rounded-md flex items-center justify-between">
                                <span class="font-mono text-sm">{{ $selectedServiceLicense->license_key }}</span>
                                <x-mary-button
                                    icon="o-clipboard"
                                    class="btn-xs btn-ghost"
                                    onclick="navigator.clipboard.writeText('{{ $selectedServiceLicense->license_key }}');
                                             $dispatch('mary-toast', {message: 'Clé copiée!', type: 'success'})"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Domaine :</label>
                                <div class="mt-1 p-3 bg-white dark:bg-gray-800 border rounded-md">
                                    <span class="font-mono text-sm">{{ $selectedServiceLicense->domain }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Utilisateurs :</label>
                                <div class="mt-1 p-3 bg-white dark:bg-gray-800 border rounded-md">
                                    {{ $selectedServiceLicense->current_users }}/{{ $selectedServiceLicense->max_users }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modules actifs -->
                @if($selectedServiceLicense->modules->count() > 0)
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-3">
                            <x-mary-icon name="o-puzzle-piece" class="w-5 h-5 inline mr-2" />
                            Modules disponibles
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach($selectedServiceLicense->modules as $module)
                                <div class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-md">
                                    <x-mary-icon name="o-check-circle" class="w-4 h-4 text-green-600 mr-2 flex-shrink-0" />
                                    <span class="text-sm">{{ $module->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Informations de la licence -->
                <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-3">
                        <x-mary-icon name="o-information-circle" class="w-5 h-5 inline mr-2" />
                        Informations de la licence
                    </h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">Statut :</span>
                            <x-mary-badge
                                :value="$selectedServiceLicense->status->label()"
                                class="badge-success ml-2"
                            />
                        </div>
                        @if($selectedServiceLicense->expires_at)
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Expire le :</span>
                                <span class="ml-2">{{ $selectedServiceLicense->expires_at->format('d/m/Y') }}</span>
                            </div>
                        @endif
                        @if($selectedServiceLicense->last_used_at)
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Dernière utilisation :</span>
                                <span class="ml-2">{{ $selectedServiceLicense->last_used_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg">
                    <h3 class="font-medium text-amber-900 dark:text-amber-100 mb-2">
                        <x-mary-icon name="o-light-bulb" class="w-5 h-5 inline mr-2" />
                        Instructions d'accès
                    </h3>
                    <ol class="text-sm text-amber-800 dark:text-amber-200 space-y-1 list-decimal list-inside">
                        <li>Cliquez sur "Ouvrir le service" pour accéder à votre application</li>
                        <li>Votre session sera automatiquement configurée</li>
                        <li>Si demandé, utilisez votre clé de licence ci-dessus</li>
                        <li>Tous vos modules sont déjà activés et prêts à l'emploi</li>
                    </ol>
                </div>
            </div>

            <x-slot:actions>
                <x-mary-button
                    onclick="window.open('{{ $selectedServiceLicense->getServiceUrl() }}', '_blank')"
                    class="bg-blue-500 hover:bg-blue-600 text-white border-0 shadow-md mr-2"
                    icon="o-arrow-top-right-on-square"
                >
                    Ouvrir le service
                </x-mary-button>
                <x-mary-button
                    wire:click="accessServiceDirect({{ $selectedServiceLicense->id }})"
                    class="bg-green-500 hover:bg-green-600 text-white border-0 shadow-md mr-2"
                    icon="o-link"
                >
                    Accès direct
                </x-mary-button>
                <x-mary-button wire:click="closeServiceAccess" class="bg-gray-500 hover:bg-gray-600 text-white border-0 shadow-md">
                    Fermer
                </x-mary-button>
            </x-slot:actions>
        </x-mary-modal>
    @endif
</div>
