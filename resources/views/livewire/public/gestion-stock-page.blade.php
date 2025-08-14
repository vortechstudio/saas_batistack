<div>
    <!-- Section Hero -->
    <section class="ebp-hero bg-gradient-to-br from-green-50 to-emerald-100 dark:from-gray-900 dark:to-gray-800">
        <div class="ebp-container">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                    Gestion des Stocks
                </h1>
                <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-3xl mx-auto">
                    Optimisez la gestion de vos stocks et matériaux avec des outils adaptés au secteur BTP
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('pricing') }}" class="ebp-btn ebp-btn-primary">
                        Commencer maintenant
                    </a>
                    <a href="#fonctionnalites" class="ebp-btn ebp-btn-outline">
                        Découvrir les fonctionnalités
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Fonctionnalités -->
    <section id="fonctionnalites" class="py-20 bg-white dark:bg-gray-900">
        <div class="ebp-container">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    Fonctionnalités Gestion de Stock
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                    Une solution complète pour optimiser la gestion de vos stocks et matériaux
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Inventaire en Temps Réel -->
                <div class="ebp-card">
                    <div class="p-6">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            Inventaire en Temps Réel
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Suivi instantané des stocks avec mise à jour automatique des mouvements
                        </p>
                    </div>
                </div>

                <!-- Gestion Multi-Dépôts -->
                <div class="ebp-card">
                    <div class="p-6">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            Gestion Multi-Dépôts
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Gérez plusieurs entrepôts et chantiers avec transferts automatisés
                        </p>
                    </div>
                </div>

                <!-- Codes-Barres -->
                <div class="ebp-card">
                    <div class="p-6">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            Codes-Barres & QR Codes
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Traçabilité complète avec lecture de codes-barres et QR codes
                        </p>
                    </div>
                </div>

                <!-- Alertes Stock -->
                <div class="ebp-card">
                    <div class="p-6">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            Alertes de Stock
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Notifications automatiques pour les seuils minimum et ruptures de stock
                        </p>
                    </div>
                </div>

                <!-- Valorisation -->
                <div class="ebp-card">
                    <div class="p-6">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            Valorisation des Stocks
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Calcul automatique de la valeur des stocks selon différentes méthodes
                        </p>
                    </div>
                </div>

                <!-- Commandes Fournisseurs -->
                <div class="ebp-card">
                    <div class="p-6">
                        <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            Commandes Fournisseurs
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Gestion complète des commandes avec suivi des livraisons et réceptions
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Avantages -->
    <section class="py-20 bg-gray-50 dark:bg-gray-800">
        <div class="ebp-container">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    Pourquoi choisir notre solution ?
                </h2>
            </div>

            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        Optimisez vos coûts et votre productivité
                    </h3>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-600 dark:text-gray-300">
                                Réduction des ruptures de stock de 80%
                            </span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-600 dark:text-gray-300">
                                Optimisation des niveaux de stock
                            </span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-600 dark:text-gray-300">
                                Gain de temps sur la gestion quotidienne
                            </span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-600 dark:text-gray-300">
                                Traçabilité complète des matériaux
                            </span>
                        </li>
                    </ul>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-8">
                    <h4 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        Tableau de bord en temps réel
                    </h4>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-300">Stock total</span>
                            <span class="font-semibold text-gray-900 dark:text-white">€ 245,680</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-300">Articles en stock</span>
                            <span class="font-semibold text-gray-900 dark:text-white">1,247</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-300">Alertes actives</span>
                            <span class="font-semibold text-red-600">3</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-300">Commandes en cours</span>
                            <span class="font-semibold text-gray-900 dark:text-white">12</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section CTA -->
    <section class="py-20 bg-green-600 dark:bg-green-800">
        <div class="ebp-container text-center">
            <h2 class="text-3xl font-bold text-white mb-4">
                Prêt à optimiser votre gestion de stock ?
            </h2>
            <p class="text-xl text-green-100 mb-8 max-w-2xl mx-auto">
                Découvrez comment notre solution peut transformer la gestion de vos stocks et matériaux
            </p>
            <a href="{{ route('pricing') }}" class="ebp-btn bg-white text-green-600 hover:bg-gray-100">
                Commencer maintenant
            </a>
        </div>
    </section>
</div>
