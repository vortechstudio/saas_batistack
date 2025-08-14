<div>
    <!-- Section Hero -->
    <section class="ebp-hero bg-gradient-to-br from-blue-600 to-blue-800 text-white py-20">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-5xl font-bold mb-6">Devis & Métrés</h1>
            <p class="text-xl mb-8 max-w-3xl mx-auto">
                Créez des devis précis et des métrés détaillés avec notre solution intégrée.
                Optimisez vos estimations et gagnez du temps sur vos projets BTP.
            </p>
            <div class="flex justify-center space-x-4">
                <a href="#fonctionnalites" class="ebp-btn ebp-btn-primary">
                    Découvrir les fonctionnalités
                </a>
                <a href="{{ route('pricing') }}" class="ebp-btn ebp-btn-outline">
                    Voir les tarifs
                </a>
            </div>
        </div>
    </section>

    <!-- Section Fonctionnalités -->
    <section id="fonctionnalites" class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Fonctionnalités Principales</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Des outils puissants pour créer des devis professionnels et des métrés précis
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="ebp-card p-8 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Devis Automatisés</h3>
                    <p class="text-gray-600">Génération automatique de devis basés sur vos métrés et barèmes personnalisés.</p>
                </div>

                <div class="ebp-card p-8 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Métrés Précis</h3>
                    <p class="text-gray-600">Calculs automatiques des quantités avec gestion des ratios et coefficients.</p>
                </div>

                <div class="ebp-card p-8 text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Gestion des Prix</h3>
                    <p class="text-gray-600">Base de données de prix actualisée et personnalisable selon vos fournisseurs.</p>
                </div>

                <div class="ebp-card p-8 text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Analyses & Rapports</h3>
                    <p class="text-gray-600">Tableaux de bord et analyses détaillées de vos estimations et marges.</p>
                </div>

                <div class="ebp-card p-8 text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Bibliothèque d'Ouvrages</h3>
                    <p class="text-gray-600">Catalogue complet d'ouvrages BTP avec métrés types et prix de référence.</p>
                </div>

                <div class="ebp-card p-8 text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2v0a2 2 0 01-2-2v-1"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Export & Impression</h3>
                    <p class="text-gray-600">Génération de documents PDF professionnels et export vers Excel.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section CTA -->
    <section class="py-20 bg-blue-600 text-white">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-4xl font-bold mb-6">Prêt à optimiser vos devis ?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">
                Rejoignez les entreprises BTP qui font confiance à Batistack pour leurs estimations.
            </p>
            <div class="flex justify-center space-x-4">
                <a href="{{ route('pricing') }}" class="ebp-btn ebp-btn-primary bg-white text-blue-600 hover:bg-gray-100">
                    Commencer maintenant
                </a>
                <a href="{{ route('contact') }}" class="ebp-btn ebp-btn-outline border-white text-white hover:bg-white hover:text-blue-600">
                    Demander une démo
                </a>
            </div>
        </div>
    </section>
</div>
