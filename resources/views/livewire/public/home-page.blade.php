<div class="space-y-16">
    <!-- Section Hero -->
    <section class="bg-gradient-to-r from-orange-500 to-red-600 text-white py-20">
        <div class="container mx-auto text-center px-4">
            <h1 class="text-5xl font-bold mb-6">L'ERP Bâtiment qui révolutionne votre gestion</h1>
            <p class="text-xl mb-8 max-w-3xl mx-auto">Batistack simplifie la gestion de vos chantiers, devis, factures et équipes. Une solution complète pensée pour les professionnels du BTP.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('solutions') }}" class="ebp-btn ebp-btn-primary text-lg px-8 py-4">Découvrir nos solutions</a>
                <a href="#" class="ebp-btn ebp-btn-outline text-lg px-8 py-4">Démo gratuite</a>
            </div>
        </div>
    </section>

    <!-- Section Nos Offres -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-4">Nos Offres Batistack</h2>
            <p class="text-xl text-gray-600 text-center mb-12">Choisissez la solution qui correspond à la taille de votre entreprise</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Starter -->
                <div class="bg-white rounded-lg shadow-lg p-8 border-2 border-gray-200 hover:border-orange-500 transition-colors">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Batistack Starter</h3>
                        <p class="text-gray-600 mb-4">Idéal pour débuter</p>
                        <div class="text-4xl font-bold text-orange-600 mb-2">49,99€</div>
                        <p class="text-gray-500 mb-6">/mois</p>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Jusqu'à 3 utilisateurs</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> 10 projets maximum</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> 5 GB de stockage</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Modules essentiels inclus</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Support standard</li>
                    </ul>
                    <button class="w-full ebp-btn ebp-btn-outline">Commencer</button>
                </div>

                <!-- Professional (Featured) -->
                <div class="bg-white rounded-lg shadow-xl p-8 border-2 border-orange-500 relative transform scale-105">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-orange-500 text-white px-4 py-2 rounded-full text-sm font-semibold">Le plus populaire</span>
                    </div>
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Batistack Professional</h3>
                        <p class="text-gray-600 mb-4">Pour les entreprises en croissance</p>
                        <div class="text-4xl font-bold text-orange-600 mb-2">99,99€</div>
                        <p class="text-gray-500 mb-6">/mois</p>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Jusqu'à 10 utilisateurs</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> 50 projets maximum</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> 25 GB de stockage</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Modules avancés inclus</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Support prioritaire</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Toutes les options</li>
                    </ul>
                    <button class="w-full ebp-btn ebp-btn-primary">Choisir Professional</button>
                </div>

                <!-- Enterprise -->
                <div class="bg-white rounded-lg shadow-lg p-8 border-2 border-gray-200 hover:border-orange-500 transition-colors">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Batistack Enterprise</h3>
                        <p class="text-gray-600 mb-4">Pour les grandes entreprises</p>
                        <div class="text-4xl font-bold text-orange-600 mb-2">199,99€</div>
                        <p class="text-gray-500 mb-6">/mois</p>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Utilisateurs illimités</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Projets illimités</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> 100 GB de stockage</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Tous les modules premium</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Support dédié 24/7</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Fonctionnalités avancées</li>
                    </ul>
                    <button class="w-full ebp-btn ebp-btn-outline">Contactez-nous</button>
                </div>
            </div>

            <div class="text-center mt-8">
                <p class="text-gray-600">💰 <strong>Économisez 2 mois</strong> avec la facturation annuelle</p>
            </div>
        </div>
    </section>

    <!-- Section Modules par Catégorie -->
    <section class="bg-gray-50 py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-4">Modules disponibles</h2>
            <p class="text-xl text-gray-600 text-center mb-12">Des fonctionnalités adaptées à chaque niveau</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Modules Core -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">🏗️</span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">Modules Essentiels</h3>
                        <p class="text-gray-600">Inclus dans toutes les offres</p>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Gestion des chantiers</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Devis et factures</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Suivi des équipes</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Planning de base</li>
                    </ul>
                </div>

                <!-- Modules Advanced -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">⚡</span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">Modules Avancés</h3>
                        <p class="text-gray-600">À partir de Professional</p>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center"><span class="text-blue-500 mr-2">✓</span> Comptabilité analytique</li>
                        <li class="flex items-center"><span class="text-blue-500 mr-2">✓</span> Gestion des stocks</li>
                        <li class="flex items-center"><span class="text-blue-500 mr-2">✓</span> Rapports avancés</li>
                        <li class="flex items-center"><span class="text-blue-500 mr-2">✓</span> API et intégrations</li>
                    </ul>
                </div>

                <!-- Modules Premium -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">👑</span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">Modules Premium</h3>
                        <p class="text-gray-600">Exclusifs à Enterprise</p>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center"><span class="text-purple-500 mr-2">✓</span> IA prédictive</li>
                        <li class="flex items-center"><span class="text-purple-500 mr-2">✓</span> Analyses avancées</li>
                        <li class="flex items-center"><span class="text-purple-500 mr-2">✓</span> Automatisation</li>
                        <li class="flex items-center"><span class="text-purple-500 mr-2">✓</span> Support dédié</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Options Complémentaires -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-4">Options complémentaires</h2>
            <p class="text-xl text-gray-600 text-center mb-12">Personnalisez votre solution selon vos besoins</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                <!-- Stockage -->
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-xl">💾</span>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Stockage supplémentaire</h3>
                    <p class="text-gray-600 mb-4">Augmentez votre espace de stockage</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• +50 GB</li>
                        <li>• +100 GB</li>
                        <li>• Stockage illimité</li>
                    </ul>
                </div>

                <!-- Support -->
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-xl">🎧</span>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Support premium</h3>
                    <p class="text-gray-600 mb-4">Assistance prioritaire et formation</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Support 24/7</li>
                        <li>• Formation personnalisée</li>
                        <li>• Consultant dédié</li>
                    </ul>
                </div>

                <!-- Fonctionnalités -->
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-xl">🚀</span>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Fonctionnalités avancées</h3>
                    <p class="text-gray-600 mb-4">Modules spécialisés sur mesure</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Modules métier</li>
                        <li>• Intégrations custom</li>
                        <li>• Développements spécifiques</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Témoignages -->
    <section class="bg-gray-100 py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-4">Ils nous font confiance</h2>
            <p class="text-xl text-gray-600 text-center mb-12">Plus de 1000 entreprises du BTP utilisent Batistack</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 text-xl">★★★★★</div>
                    </div>
                    <p class="text-gray-700 mb-6">"Batistack a révolutionné notre gestion de chantiers. Nous avons gagné 30% de temps sur notre administration."</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-300 rounded-full mr-4"></div>
                        <div>
                            <p class="font-semibold">Pierre Dubois</p>
                            <p class="text-gray-600 text-sm">Directeur, Dubois Construction</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 text-xl">★★★★★</div>
                    </div>
                    <p class="text-gray-700 mb-6">"L'interface est intuitive et le support client exceptionnel. Nos équipes ont adopté l'outil en quelques jours."</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-300 rounded-full mr-4"></div>
                        <div>
                            <p class="font-semibold">Marie Leroy</p>
                            <p class="text-gray-600 text-sm">Gérante, Leroy Rénovation</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="flex items-center mb-4">
                        <div class="text-yellow-400 text-xl">★★★★★</div>
                    </div>
                    <p class="text-gray-700 mb-6">"Grâce aux modules avancés, nous avons une visibilité complète sur nos projets et notre rentabilité."</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-300 rounded-full mr-4"></div>
                        <div>
                            <p class="font-semibold">Jean Martin</p>
                            <p class="text-gray-600 text-sm">PDG, Martin & Associés</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section CTA Final -->
    <section class="bg-gradient-to-r from-orange-500 to-red-600 text-white py-16">
        <div class="container mx-auto text-center px-4">
            <h2 class="text-4xl font-bold mb-4">Prêt à transformer votre gestion ?</h2>
            <p class="text-xl mb-8">Rejoignez les milliers d'entreprises qui font confiance à Batistack</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#" class="ebp-btn bg-white text-orange-600 hover:bg-gray-100 text-lg px-8 py-4">Essai gratuit 30 jours</a>
                <a href="#" class="ebp-btn ebp-btn-outline border-white text-white hover:bg-white hover:text-orange-600 text-lg px-8 py-4">Demander une démo</a>
            </div>
        </div>
    </section>
</div>
