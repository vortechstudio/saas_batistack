<div>
    <x-mary-header
        title="Service - {{ $service->service_code }} - {{ $service->product->name }}"
        subtitle="Renouvellement prévue le {{ $service->nextBillingDate->format('d/m/Y') }}"
    />
    @if($service->status->value === 'pending' || $service->status->value === 'error')
        @if($service->status->value === 'pending')
            <div role="alert" class="alert alert-vertical sm:alert-horizontal alert-warning">
                @svg('heroicon-o-exclamation-circle', 'w-6 h-6')
                <span>Service en cours d'installation ou de maintenance</span>
                <div class="flex justify-between items-center" wire:poll.visible.2s="refreshStateInstall">
                    <span class="me-5">{{ $stateInstallLabel }}</span>
                    <div><span class="loading loading-spinner loading-xs"></span> Etape <span>{{ $stateInstallCurrent }}</span> / {{ $stateInstallTotal }}</div>
                </div>
            </div>
            <progress class="progress w-[100%]" value="{{ $stateInstallCurrent }}" max="{{ $stateInstallTotal }}"></progress>
        @endif
        @if(isset($comment))
            <div role="alert" class="alert alert-vertical sm:alert-horizontal alert-error mt-3">
                @svg('heroicon-o-x-mark', 'w-6 h-6')
                <span>Une erreur à eu lieu lors de l'installation de votre espace, le support technique à pris la relève et un mail vous informera de la disponibilité de votre service.</span>
            </div>
        @endif
    @endif

    <div class="flex justify-between gap-5 mt-10 mb-10">
        <div class="w-1/2">
            <x-mary-card class="bg-gray-100 p-5 shadow-md">
                <x-slot:title class="text-blue-900 text-xl font-black">
                    Détails du service
                </x-slot:title>
                <x-mary-menu-separator class="my-2" />
                <div class="flex flex-col">
                    <span class="text-blue-900 font-black text-lg">Status</span>
                    {!! $service->status->badge() !!}
                </div>
                <x-mary-menu-separator class="my-2" />
                <div class="flex flex-col">
                    <span class="text-blue-900 font-black text-lg">Date de création</span>
                    <span class="text-gray-400 italic">{{ $service->creationDate->format('d/m/Y') }}</span>
                </div>
                <x-mary-menu-separator class="my-2" />
                <div class="flex flex-col">
                    <span class="text-blue-900 font-black text-lg">Date d'expiration</span>
                    <div class="flex gap-2">
                        <span class="text-gray-400 italic">{{ $service->expirationDate->format('d/m/Y') }}</span>
                        <div class="badge badge-outline badge-error">{{ $service->expirationDate->diffForHumans() }}</div>
                    </div>
                </div>
                <x-mary-menu-separator class="my-2" />
                <div class="flex flex-col">
                    <span class="text-blue-900 font-black text-lg">Numéro de License</span>
                    <div class="flex gap-2">
                        <span class="text-gray-400 italic">{{ $service->service_code }}</span>
                    </div>
                </div>
                <x-mary-menu-separator class="my-2" />
                <div class="flex flex-col">
                    <span class="text-blue-900 font-black text-lg">Accès</span>
                    @if($service->status->value === 'ok')
                    <div class="flex gap-2">
                        <a href="https://{{ $service->domain }}" target="_blank" class="text-gray-400 italic">https://{{ $service->domain }}</a>
                    </div>
                    @endif
                </div>
            </x-mary-card>
        </div>

        <div class="w-1/2">
            <x-mary-card class="bg-gray-100 p-5 shadow-md">
                <x-slot:title class="text-blue-900 text-2xl font-black">
                    Détails du produits
                </x-slot:title>
                <x-mary-menu-separator class="my-2" />
                <div class="flex flex-col">
                    <span class="text-blue-900 font-black text-lg">Produit</span>
                    <span class="text-gray-400 italic">{{ $service->product->name }}</span>
                </div>
                <x-mary-menu-separator class="my-2" />
                <div class="flex flex-col">
                    <span class="text-blue-900 font-black text-lg">Type de produit</span>
                    <span class="text-gray-400 italic">{{ $service->product->category->label() }}</span>
                </div>
                <x-mary-menu-separator class="my-2" />
                <div class="flex flex-col">
                    <span class="text-blue-900 font-black text-lg">Informations</span>
                    <div class="flex justify-center items-center gap-5">
                        <div class="flex tooltip bg-white p-2 rounded-md" data-tip="Nombre maximum d'utilisateurs">
                            @svg('heroicon-o-user-circle', 'w-6 h-6 text-gray-500')
                            <span class="text-gray-400 italic">{{ $service->product->info_stripe->metadata->max_users }}</span>
                        </div>
                        <div class="flex tooltip bg-white p-2 rounded-md" data-tip="Limite de stockage">
                            @svg('heroicon-o-circle-stack', 'w-6 h-6 text-gray-500')
                            <span class="text-gray-400 italic">{{ $service->product->info_stripe->metadata->storage_limit }} Gb</span>
                        </div>
                    </div>
                </div>
            </x-mary-card>
        </div>
    </div>

    <div class="bg-gray-100 p-5 shadow-md rounded-lg">
        <!-- Navigation des onglets -->
        <div class="flex border-b border-gray-200 mb-4">
            <button
                wire:click="setActiveTab('modules')"
                class="px-4 py-2 text-sm font-medium border-b-2 transition-colors duration-200 {{ $activeTab === 'modules' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                <div class="flex items-center gap-2">
                    @svg('heroicon-o-puzzle-piece', 'w-4 h-4')
                    Modules
                </div>
            </button>

            <button
                wire:click="setActiveTab('options')"
                class="px-4 py-2 text-sm font-medium border-b-2 transition-colors duration-200 {{ $activeTab === 'options' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                <div class="flex items-center gap-2">
                    @svg('heroicon-o-cog-6-tooth', 'w-4 h-4')
                    Options
                </div>
            </button>

            <button
                wire:click="setActiveTab('sauvegardes')"
                class="px-4 py-2 text-sm font-medium border-b-2 transition-colors duration-200 {{ $activeTab === 'sauvegardes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                <div class="flex items-center gap-2">
                    @svg('heroicon-o-archive-box', 'w-4 h-4')
                    Sauvegardes
                </div>
            </button>

            <button
                wire:click="setActiveTab('stockages')"
                class="px-4 py-2 text-sm font-medium border-b-2 transition-colors duration-200 {{ $activeTab === 'stockages' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                <div class="flex items-center gap-2">
                    @svg('heroicon-o-circle-stack', 'w-4 h-4')
                    Stockages
                </div>
            </button>
        </div>

        <!-- Contenu des onglets -->
        <div class="min-h-[300px]">
            @if($activeTab === 'modules')
                <div class="space-y-3">
                    <h3 class="text-blue-900 text-xl font-black mb-4">Modules Installés</h3>
                    @if($service->modules->count() > 0)
                        <ul class="list space-y-2">
                            @foreach ($service->modules as $module)
                                <li class="list-row bg-white rounded-lg p-3 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <img class="size-8 rounded-lg" src="{{ $module->feature->media }}" alt="{{ $module->feature->name }}" />
                                        <div class="flex-1">
                                            <div class="font-semibold text-gray-900">{{ $module->feature->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $module->feature->description ?? 'Module installé' }}</div>
                                        </div>
                                        <x-mary-icon
                                            :name="$module->is_active ? 'o-check-circle' : 'o-x-circle'"
                                            class="w-6 h-6 {{ $module->is_active ? 'text-green-500' : 'text-red-500' }}"
                                        />
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-8">
                            <x-mary-icon name="o-puzzle-piece" class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                            <p class="text-gray-500">Aucun module installé</p>
                        </div>
                    @endif
                </div>
            @endif

            @if($activeTab === 'options')
                <div class="space-y-3">
                    <h3 class="text-blue-900 text-xl font-black mb-4">Options Activées</h3>
                    @if($service->options->count() > 0)
                        <ul class="list space-y-2">
                            @foreach ($service->options as $option)
                                <li class="list-row bg-white rounded-lg p-3 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <x-mary-icon name="o-cog-6-tooth" class="w-6 h-6 text-blue-500" />
                                        <div class="flex-1">
                                            <div class="font-semibold text-gray-900">{{ $option->product->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $option->product->description ?? 'Option activée' }}</div>
                                        </div>
                                        <x-mary-badge value="Actif" class="badge-success" />
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-8">
                            <x-mary-icon name="o-cog-6-tooth" class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                            <p class="text-gray-500">Aucune option activée</p>
                        </div>
                    @endif
                </div>
            @endif

            @if($activeTab === 'sauvegardes')
                <div class="space-y-3">
                    <h3 class="text-blue-900 text-xl font-black mb-4">Sauvegardes</h3>
                    <div class="text-center py-8">
                        <x-mary-icon name="o-archive-box" class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                        <p class="text-gray-500 mb-4">Gestion des sauvegardes automatiques</p>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Sauvegarde automatique</span>
                                @if($this->hasBackupOption())
                                    <x-mary-badge value="Activée" class="badge-success" />
                                @else
                                    <x-mary-badge value="Non disponible" class="badge-warning" />
                                @endif
                            </div>
                            @if($this->hasBackupOption())
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-500">Fréquence</span>
                                    <span class="text-sm text-gray-700">Quotidienne</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Dernière sauvegarde</span>
                                    <span class="text-sm text-gray-700">{{ $service->backups()->latest()->first()->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            @else
                                <div class="text-center mt-3">
                                    <p class="text-sm text-gray-500">L'option "Sauvegarde et rétention" n'est pas associée à ce service.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if($activeTab === 'stockages')
                <div class="space-y-3">
                    <h3 class="text-blue-900 text-xl font-black mb-4">Stockages</h3>
                    <div class="space-y-4">
                        <!-- Stockage principal -->
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="flex justify-between items-center mb-3">
                                <span class="font-medium text-gray-900">Stockage Principal</span>
                                <x-mary-badge value="Actif" class="badge-success" />
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Utilisé</span>
                                    <span class="text-gray-700">2.4 GB / {{ $service->product->info_stripe->metadata->storage_limit ?? 10 }} GB</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ (2.4 / ($service->product->info_stripe->metadata->storage_limit ?? 10)) * 100 }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Stockage de sauvegarde -->
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="flex justify-between items-center mb-3">
                                <span class="font-medium text-gray-900">Stockage Sauvegarde</span>
                                <x-mary-badge value="Actif" class="badge-info" />
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Utilisé</span>
                                    <span class="text-gray-700">850 MB / 5 GB</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: 17%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
