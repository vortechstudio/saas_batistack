<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mes Licences</h1>
            <p class="text-gray-600 dark:text-gray-400">Gérez vos licences, modules et options.</p>
        </div>
    </div>

    @if(session('success'))
        <x-mary-alert icon="o-check-circle" class="alert-success">
            {{ session('success') }}
        </x-mary-alert>
    @endif

    @if($this->customer)
        <!-- Filtres et recherche -->
        <x-mary-card class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 border-l-4 border-blue-500">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Recherche -->
                <div>
                    <x-mary-input 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Rechercher par clé ou produit..." 
                        icon="o-magnifying-glass" 
                        clearable 
                        class="border-blue-200 focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
                
                <!-- Filtre par statut -->
                <div>
                    <x-mary-select 
                        wire:model.live="statusFilter" 
                        :options="[
                            ['id' => 'all', 'name' => 'Tous les statuts'],
                            ['id' => 'active', 'name' => 'Actives'],
                            ['id' => 'expired', 'name' => 'Expirées'],
                            ['id' => 'expiring', 'name' => 'Expirant bientôt']
                        ]" 
                        option-value="id" 
                        option-label="name" 
                        class="border-blue-200 focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
                
                <!-- Statistiques rapides -->
                <div class="flex items-center justify-end space-x-4 text-sm">
                    <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded-lg border border-blue-200 shadow-sm">
                        <span class="text-gray-600 dark:text-gray-400">Total: </span>
                        <span class="font-bold text-blue-600 dark:text-blue-400">{{ $this->licenses->total() }}</span>
                    </div>
                </div>
            </div>
        </x-mary-card>

        <!-- Liste des licences -->
        @if($this->licenses->count() > 0)
            <div class="space-y-6">
                @foreach($this->licenses as $license)
                    <div class="group relative">
                        <x-mary-card class="
                            border-2 border-gray-200 dark:border-gray-700 
                            hover:border-blue-300 dark:hover:border-blue-600
                            hover:shadow-xl 
                            transition-all duration-300 ease-in-out
                            transform hover:-translate-y-1
                            bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900
                            @if($license->status === App\Enums\LicenseStatus::ACTIVE) 
                                border-l-4 border-l-green-500 bg-gradient-to-br from-green-50 to-white dark:from-green-900/20 dark:to-gray-800
                            @elseif($license->isExpired()) 
                                border-l-4 border-l-red-500 bg-gradient-to-br from-red-50 to-white dark:from-red-900/20 dark:to-gray-800
                            @else 
                                border-l-4 border-l-gray-400 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/20 dark:to-gray-800
                            @endif
                        ">
                            <!-- Badge de statut en coin -->
                            <div class="absolute top-4 right-4">
                                @if($license->status === App\Enums\LicenseStatus::ACTIVE)
                                    <div class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                        <x-mary-icon name="o-check-circle" class="h-3 w-3 inline mr-1" />
                                        ACTIVE
                                    </div>
                                @elseif($license->isExpired())
                                    <div class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                        <x-mary-icon name="o-x-circle" class="h-3 w-3 inline mr-1" />
                                        EXPIRÉ
                                    </div>
                                @else
                                    <div class="bg-gray-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                        <x-mary-icon name="o-pause-circle" class="h-3 w-3 inline mr-1" />
                                        INACTIF
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center justify-between pr-20">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-4 mb-4">
                                        <!-- Icône de produit avec arrière-plan -->
                                        <div class="
                                            p-3 rounded-xl shadow-md
                                            @if($license->status === App\Enums\LicenseStatus::ACTIVE) 
                                                bg-green-100 dark:bg-green-900/30
                                            @elseif($license->isExpired()) 
                                                bg-red-100 dark:bg-red-900/30
                                            @else 
                                                bg-gray-100 dark:bg-gray-700
                                            @endif
                                        ">
                                            @if($license->status === App\Enums\LicenseStatus::ACTIVE)
                                                <x-mary-icon name="o-shield-check" class="h-8 w-8 text-green-600 dark:text-green-400" />
                                            @elseif($license->isExpired())
                                                <x-mary-icon name="o-shield-exclamation" class="h-8 w-8 text-red-600 dark:text-red-400" />
                                            @else
                                                <x-mary-icon name="o-shield" class="h-8 w-8 text-gray-600 dark:text-gray-400" />
                                            @endif
                                        </div>
                                        
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                                                {{ $license->product->name }}
                                            </h3>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm text-gray-500 dark:text-gray-400">Clé:</span>
                                                <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-sm font-mono text-gray-800 dark:text-gray-200">
                                                    {{ $license->license_key }}
                                                </code>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Informations détaillées avec cartes -->
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Statut</div>
                                            <span class="px-3 py-1 rounded-full text-xs font-bold
                                                @if($license->status === App\Enums\LicenseStatus::ACTIVE) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @elseif($license->isExpired()) bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                {{ $license->status->value }}
                                            </span>
                                        </div>
                                        
                                        @if($license->expires_at)
                                            <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">
                                                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Expire le</div>
                                                <div class="font-semibold text-gray-900 dark:text-white">
                                                    {{ $license->expires_at->format('d/m/Y') }}
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Utilisateurs</div>
                                            <div class="font-semibold text-gray-900 dark:text-white">
                                                <span class="text-blue-600 dark:text-blue-400">{{ $license->current_users }}</span>
                                                <span class="text-gray-400">/</span>
                                                <span>{{ $license->max_users }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Modules</div>
                                            <div class="font-semibold text-gray-900 dark:text-white">
                                                <span class="text-green-600 dark:text-green-400">{{ $license->activeModules->count() }}</span>
                                                <span class="text-gray-400 text-sm"> actifs</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Actions en bas -->
                            <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <x-mary-button 
                                    wire:click="showDetails({{ $license->id }})" 
                                    class="btn-sm bg-blue-500 hover:bg-blue-600 text-white border-0 shadow-md hover:shadow-lg transition-all"
                                    icon="o-eye"
                                >
                                    Détails
                                </x-mary-button>
                                
                                <x-mary-dropdown>
                                    <x-slot:trigger>
                                        <x-mary-button icon="o-ellipsis-vertical" class="btn-sm bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border-0 shadow-md" />
                                    </x-slot:trigger>
                                    
                                    @if($license->status !== App\Enums\LicenseStatus::ACTIVE)
                                        <x-mary-menu-item 
                                            title="Activer" 
                                            icon="o-play" 
                                            wire:click="activateLicense({{ $license->id }})" 
                                            class="text-green-600 hover:bg-green-50"
                                        />
                                    @endif
                                    
                                    <x-mary-menu-item 
                                        title="Télécharger" 
                                        icon="o-arrow-down-tray" 
                                        wire:click="downloadLicense({{ $license->id }})" 
                                        class="text-blue-600 hover:bg-blue-50"
                                    />
                                    
                                    <x-mary-menu-item 
                                        title="Accéder au service" 
                                        icon="o-link" 
                                        link="#" 
                                        class="text-purple-600 hover:bg-purple-50"
                                    />
                                </x-mary-dropdown>
                            </div>
                        </x-mary-card>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-8">
                {{ $this->licenses->links() }}
            </div>
        @else
            <x-mary-card class="border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-900">
                <div class="text-center py-16">
                    <div class="bg-gray-100 dark:bg-gray-700 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-mary-icon name="o-shield-exclamation" class="h-12 w-12 text-gray-400" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Aucune licence trouvée</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                        @if($search || $statusFilter !== 'all')
                            Aucune licence ne correspond à vos critères de recherche.
                        @else
                            Vous n'avez pas encore de licence active.
                        @endif
                    </p>
                    @if($search || $statusFilter !== 'all')
                        <x-mary-button 
                            wire:click="$set('search', ''); $set('statusFilter', 'all')" 
                            class="bg-blue-500 hover:bg-blue-600 text-white border-0 shadow-md hover:shadow-lg transition-all"
                            icon="o-arrow-path"
                        >
                            Réinitialiser les filtres
                        </x-mary-button>
                    @endif
                </div>
            </x-mary-card>
        @endif
    @else
        <x-mary-card class="border-2 border-yellow-200 dark:border-yellow-600 bg-gradient-to-br from-yellow-50 to-white dark:from-yellow-900/20 dark:to-gray-800">
            <div class="text-center py-16">
                <div class="bg-yellow-100 dark:bg-yellow-900/30 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-mary-icon name="o-exclamation-triangle" class="h-12 w-12 text-yellow-500" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Profil client requis</h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                    Vous devez avoir un profil client pour accéder à vos licences.
                </p>
            </div>
        </x-mary-card>
    @endif

    <!-- Modal des détails de licence -->
    @if($showLicenseDetails && $selectedLicense)
        <x-mary-modal wire:model="showLicenseDetails" title="Détails de la licence" class="backdrop-blur">
            <div class="space-y-6">
                <!-- Informations générales -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 p-6 rounded-xl border border-blue-200 dark:border-gray-600">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-mary-icon name="o-information-circle" class="h-5 w-5 mr-2 text-blue-500" />
                        Informations générales
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide block mb-1">Produit</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $selectedLicense->product->name }}</span>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide block mb-1">Clé de licence</span>
                            <code class="font-mono text-sm bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $selectedLicense->license_key }}</code>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide block mb-1">Statut</span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold
                                @if($selectedLicense->status === App\Enums\LicenseStatus::ACTIVE) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                {{ $selectedLicense->status->value }}
                            </span>
                        </div>
                        @if($selectedLicense->expires_at)
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                                <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide block mb-1">Date d'expiration</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $selectedLicense->expires_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Modules -->
                @if($selectedLicense->modules->count() > 0)
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-gray-800 dark:to-gray-700 p-6 rounded-xl border border-purple-200 dark:border-gray-600">
                        <h4 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <x-mary-icon name="o-puzzle-piece" class="h-5 w-5 mr-2 text-purple-500" />
                            Modules
                        </h4>
                        <div class="space-y-3">
                            @foreach($selectedLicense->modules as $module)
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                                <x-mary-icon name="o-puzzle-piece" class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                            </div>
                                            <div>
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $module->name }}</span>
                                                @if($module->description)
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $module->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold
                                                @if($module->pivot->enabled) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                {{ $module->pivot->enabled ? 'Activé' : 'Désactivé' }}
                                            </span>
                                            <x-mary-toggle 
                                                wire:click="toggleModule({{ $selectedLicense->id }}, {{ $module->id }})" 
                                                :checked="$module->pivot->enabled"
                                                class="scale-75"
                                            />
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Options -->
                @if($selectedLicense->options->count() > 0)
                    <div class="bg-gradient-to-r from-green-50 to-teal-50 dark:from-gray-800 dark:to-gray-700 p-6 rounded-xl border border-green-200 dark:border-gray-600">
                        <h4 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <x-mary-icon name="o-cog" class="h-5 w-5 mr-2 text-green-500" />
                            Options
                        </h4>
                        <div class="space-y-3">
                            @foreach($selectedLicense->options as $option)
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                                <x-mary-icon name="o-cog" class="h-4 w-4 text-green-600 dark:text-green-400" />
                                            </div>
                                            <div>
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $option->name }}</span>
                                                @if($option->description)
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $option->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-xs font-bold
                                            @if($option->pivot->enabled) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                            {{ $option->pivot->enabled ? 'Activé' : 'Désactivé' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <x-slot:actions>
                <x-mary-button wire:click="closeDetails" class="bg-gray-500 hover:bg-gray-600 text-white border-0 shadow-md">
                    Fermer
                </x-mary-button>
            </x-slot:actions>
        </x-mary-modal>
    @endif
</div>