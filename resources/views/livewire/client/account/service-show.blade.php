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
    <div class="flex justify-between gap-5 mt-10">
        <div class="w-1/3">
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
        <div class="w-1/3">
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
        <div class="w-1/3">
            <div class="card bg-gray-100 p-5 shadow-md mb-2 rounded-lg">
                <div class="card-body">
                    <h2 class="card-title text-blue-900 text-2xl font-black">Modules Installées</h2>
                    <ul class="list align-center">
                        @foreach ($service->modules as $feature)
                            <li class="list-row">
                                <div><img class="size-5 rounded-box" src="{{ $feature->feature->media }}" /></div>
                                <div>
                                    <div>{{ $feature->feature->name }}</div>
                                </div>
                                <x-mary-icon :name="$feature->is_active ? 'o-check-circle' : 'o-x-circle'" class="w-5 h-5 text-green-500" />
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @if($service->options->count() > 0)
            <div class="card bg-gray-100 p-5 shadow-md rounded-lg">
                <div class="card-body">
                    <h2 class="card-title text-blue-900 text-2xl font-black">Options</h2>
                    <ul class="list">
                        @foreach ($service->options as $option)
                            <li class="list-row">
                                <div>{{ $option->product->name }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
