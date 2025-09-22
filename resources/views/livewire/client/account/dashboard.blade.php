<div class="container mx-auto px-4 py-6">
    <!-- Titre de la page -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Mon Compte</h1>
        <p class="text-gray-600 mt-2">Gérez vos informations personnelles et vos préférences</p>
    </div>

    <!-- Système de tabs -->
    <div class="bg-white rounded-lg shadow-sm border">
        <!-- Navigation des tabs -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button
                    wire:click="setActiveTab('general')"
                    class="{{ $activeTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                    Informations générales
                </button>
                <button
                    wire:click="setActiveTab('security')"
                    class="{{ $activeTab === 'security' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                    Sécurité
                </button>
                <button
                    wire:click="setActiveTab('emails')"
                    class="{{ $activeTab === 'emails' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                    Emails reçus
                </button>
                <button
                    wire:click="setActiveTab('support')"
                    class="{{ $activeTab === 'support' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                    Mon niveau de support
                </button>
                <button
                    wire:click="setActiveTab('personal')"
                    class="{{ $activeTab === 'personal' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                    Données personnelles & gestion du compte
                </button>
                <button
                    wire:click="setActiveTab('advanced')"
                    class="{{ $activeTab === 'advanced' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                    Paramètres avancés
                </button>
            </nav>
        </div>

        <!-- Contenu des tabs -->
        <div class="p-6">
            @if($activeTab === 'general')
                <!-- Onglet Informations générales - Design selon l'image -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Section Mon profil -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Mon profil</h2>

                        <!-- Avatar et nom -->
                        <div class="flex items-center mb-6">
                            <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                                {{ strtoupper(substr(auth()->user()->fullname, 0, 1)) }}
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold text-gray-900">{{ auth()->user()->fullname }}</h3>
                                <p class="text-gray-600 text-sm">{{ auth()->user()->email }}</p>
                            </div>
                        </div>

                        <!-- Informations du profil -->
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-gray-700">Nichandle</span>
                                <p class="text-gray-900">{{ auth()->user()->customer->code_client }}</p>
                            </div>

                            <div>
                                <span class="text-sm font-medium text-gray-700">Code client</span>
                                <p class="text-gray-900">{{ auth()->user()->customer->code_client }}</p>
                            </div>

                            @if(auth()->user()->customer->address)
                            <div>
                                <span class="text-sm font-medium text-gray-700">Adresse</span>
                                <p class="text-gray-900 text-sm">{{ auth()->user()->customer->address }}</p>
                            </div>
                            @endif

                            @if(auth()->user()->customer->phone)
                            <div>
                                <span class="text-sm font-medium text-gray-700">Téléphone</span>
                                <p class="text-gray-900">{{ auth()->user()->customer->phone }}</p>
                            </div>
                            @endif

                            <div>
                                <span class="text-sm font-medium text-gray-700">Mon niveau de support</span>
                                <p class="badge badge-outline badge-{{ auth()->user()->customer->support_type->color() }}"> {{ auth()->user()->customer->support_type->label() }}</p>
                            </div>
                        </div>

                        <!-- Bouton Éditer mon profil -->
                        <div class="mt-6">
                            <button type="button" wire:click="editProfilAction" class="w-full bg-white border border-blue-600 text-blue-600 px-4 py-2 rounded-md hover:bg-blue-50 transition-colors font-medium">
                                Éditer mon profil
                            </button>
                            <x-filament::modal id="edit-profil" width="5xl" sticky-header>
                                <x-slot name="heading">
                                    Editer mon profil
                                </x-slot>

                               <form wire:submit="editProfil">
                                    {{ $this->editProfilForm }}

                                    <x-slot name="footer">
                                        <button type="submit" class="btn">Enregistrer</button>
                                    </x-slot>
                               </form>
                            </x-filament::modal>
                        </div>
                    </div>

                    <!-- Section Raccourcis -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Raccourcis</h2>

                        <div class="space-y-3">
                            <a href="#" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                                <span class="text-blue-600 font-medium">Voir mes factures</span>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>

                            <a href="#" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                                <span class="text-blue-600 font-medium">Suivre mes paiements</span>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>

                            <a href="#" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                                <span class="text-blue-600 font-medium">Ajouter un moyen de paiement</span>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>

                            <a href="#" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                                <span class="text-blue-600 font-medium">Voir mes contrats</span>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>

                            <a href="#" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                                <span class="text-blue-600 font-medium">Gérer mes services</span>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>

                            <a href="#" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                                <span class="text-blue-600 font-medium">Gérer mes utilisateurs</span>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>

                            <a href="#" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                                <span class="text-blue-600 font-medium">Ajouter un contact</span>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Section Ma dernière facture -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Ma dernière facture</h2>

                        @if($latestInvoice)
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Référence</span>
                                    <p class="text-gray-900 font-mono">{{ $latestInvoice->reference ?? 'FRT2044717' }}</p>
                                </div>

                                <div>
                                    <span class="text-sm font-medium text-gray-700">Date</span>
                                    <p class="text-gray-900">{{ $latestInvoice->delivered_at ? $latestInvoice->delivered_at->format('d F Y') : '25 août 2025' }}</p>
                                </div>

                                <div>
                                    <span class="text-sm font-medium text-gray-700">Montant</span>
                                    <p class="text-gray-900 font-semibold">{{ $latestInvoice->total_amount ? number_format($latestInvoice->total_amount, 2) . ' €' : '0.00 €' }}</p>
                                </div>
                            </div>

                            <div class="mt-6">
                                <button class="w-full bg-white border border-blue-600 text-blue-600 px-4 py-2 rounded-md hover:bg-blue-50 transition-colors font-medium">
                                    Voir ma facture
                                </button>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-500">Aucune facture disponible</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if($activeTab === 'security')
                <!-- Onglet Sécurité - Design selon l'image -->
                <div class="space-y-6">
                    <!-- Alerte d'attention -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Attention !</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>Les actions effectuées sur cette page modifient les paramètres de sécurité de votre compte.</p>
                                    <p class="mt-1">En cas de perte ou de vol de votre smartphone ou de votre ordinateur, stockez vos mots de passe et la liste de vos codes de secours à usage unique dans un endroit sécurisé. Ne divulguez aucune information confidentielle.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Mot de passe -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-semibold text-gray-900">Mot de passe</h3>
                                </div>
                            </div>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors font-medium">
                                Modifier
                            </button>
                        </div>
                    </div>

                    <!-- Section Double authentification -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-green-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-semibold text-gray-900">Double authentification</h3>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 mt-1">
                                        ACTIVÉ
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- SMS -->
                        <div class="mt-6 space-y-4">
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-gray-900">SMS</h4>
                                        <p class="text-sm text-gray-600">S'authentifier à l'aide d'un code de sécurité reçu par SMS.</p>
                                    </div>
                                </div>
                                <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors">
                                    Ajouter un numéro
                                </button>
                            </div>

                            <!-- Application Mobile -->
                            <div class="p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-gray-900">Application Mobile</h4>
                                            <p class="text-sm text-gray-600">S'authentifier à l'aide d'une application mobile gratuite (compatible Android / iPhone / Windows Phone).</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Liste des applications -->
                                <div class="bg-blue-50 rounded-lg p-3 mt-3">
                                    <h5 class="text-sm font-medium text-blue-900 mb-2">Description</h5>
                                    <div class="space-y-2 text-sm text-gray-700">
                                        <div class="flex justify-between">
                                            <span>Aucune description</span>
                                            <span class="text-xs text-gray-500">(Dernière utilisation : 30 mai 2023)</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Aucune description</span>
                                            <span class="text-xs text-gray-500">(Dernière utilisation : 13 avril 2024)</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Aucune description</span>
                                            <span class="text-xs text-gray-500">(Dernière utilisation : 31 juillet 2023)</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>2FAS</span>
                                            <span class="text-xs text-gray-500">(Dernière utilisation : 26 août 2025)</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors">
                                        Ajouter une application
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Clé de sécurité -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-semibold text-gray-900">Clé de sécurité</h3>
                                    <p class="text-sm text-gray-600">S'authentifier à l'aide d'une clé de sécurité compatible U2F.</p>
                                </div>
                            </div>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors font-medium">
                                Ajouter une clé
                            </button>
                        </div>
                    </div>

                    <!-- Section Codes de secours -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-green-600 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-semibold text-gray-900">Codes de secours</h3>
                                    <p class="text-sm text-gray-600 mb-2">Utilisez ces codes si vous perdez ou n'avez pas accès à votre téléphone.</p>
                                    <p class="text-sm font-medium text-green-600">Vous disposez de 9 codes de secours valides.</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex space-x-3">
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors font-medium">
                                Regénérer les codes
                            </button>
                            <button class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors font-medium">
                                Désactiver les codes 2FA
                            </button>
                        </div>
                    </div>

                    <!-- Section Restriction d'accès par IP -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-semibold text-gray-900">Restriction d'accès par IP</h3>
                                </div>
                            </div>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors font-medium">
                                Activer
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if($activeTab === 'emails')
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold text-gray-900">Emails reçus</h2>
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-md p-4">
                            <p class="text-gray-600">Gérez vos préférences de notification par email.</p>
                        </div>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                                <span class="ml-2 text-sm text-gray-700">Notifications de commandes</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                                <span class="ml-2 text-sm text-gray-700">Newsletters et promotions</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Mises à jour produits</span>
                            </label>
                        </div>
                    </div>
                </div>
            @endif

            @if($activeTab === 'support')
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold text-gray-900">Mon niveau de support</h2>
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Niveau actuel</h3>
                                <p class="text-gray-600">Votre niveau de support détermine les services disponibles</p>
                            </div>
                            <span class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium bg-{{ auth()->user()->customer->support_type->color() }}-100 text-{{ auth()->user()->customer->support_type->color() }}-800">
                                {{ auth()->user()->customer->support_type->label() }}
                            </span>
                        </div>
                        <div class="mt-4">
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                Améliorer mon support
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if($activeTab === 'personal')
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold text-gray-900">Données personnelles & gestion du compte</h2>
                    <div class="space-y-4">
                        <div class="bg-red-50 border border-red-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    @svg('heroicon-o-exclamation-triangle', 'h-5 w-5 text-red-400')
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Zone dangereuse</h3>
                                    <p class="mt-1 text-sm text-red-700">Les actions suivantes sont irréversibles.</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <button class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                                Supprimer mon compte
                            </button>
                            <button class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                                Exporter mes données
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if($activeTab === 'advanced')
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold text-gray-900">Paramètres avancés</h2>
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-md p-4">
                            <p class="text-gray-600">Configurez les paramètres avancés de votre compte.</p>
                        </div>
                        <div class="space-y-3">
                            <label class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Authentification à deux facteurs</span>
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </label>
                            <label class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Connexions automatiques</span>
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                            </label>
                            <label class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Notifications push</span>
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </label>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
