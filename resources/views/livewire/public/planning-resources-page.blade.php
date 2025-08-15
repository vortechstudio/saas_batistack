<div>
    <!-- Section Hero -->
    <section class="ebp-hero bg-gradient-to-br from-purple-600 to-purple-800 text-white py-20">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-5xl font-bold mb-6">Planning & Resources</h1>
            <p class="text-xl mb-8 max-w-3xl mx-auto">
                Optimisez la gestion de vos équipes et ressources avec notre solution de planning intégrée.
                Planification intelligente et suivi en temps réel de vos chantiers.
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
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Gestion Optimisée des Ressources</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Planifiez, organisez et suivez vos équipes et matériels en temps réel
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="ebp-card p-8 text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10a2 2 0 002 2h4a2 2 0 002-2V11m-6 0a2 2 0 002-2h4a2 2 0 002 2v0a2 2 0 01-2 2h-4a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Planning Visuel</h3>
                    <p class="text-gray-600">Interface intuitive de type Gantt pour visualiser et organiser vos projets.</p>
                </div>

                <div class="ebp-card p-8 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Gestion d'Équipes</h3>
                    <p class="text-gray-600">Affectation optimale des équipes selon les compétences et disponibilités.</p>
                </div>

                <div class="ebp-card p-8 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Gestion Matériel</h3>
                    <p class="text-gray-600">Suivi des équipements, maintenance préventive et optimisation des coûts.</p>
                </div>

                <div class="ebp-card p-8 text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Suivi Temps Réel</h3>
                    <p class="text-gray-600">Monitoring en direct de l'avancement des tâches et des ressources.</p>
                </div>

                <div class="ebp-card p-8 text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Tableaux de Bord</h3>
                    <p class="text-gray-600">Indicateurs de performance et analyses de productivité détaillées.</p>
                </div>

                <div class="ebp-card p-8 text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Application Mobile</h3>
                    <p class="text-gray-600">Accès mobile pour vos équipes terrain avec synchronisation temps réel.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section CTA -->
    <section class="py-20 bg-purple-600 text-white">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-4xl font-bold mb-6">Optimisez vos ressources dès aujourd'hui</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">
                Améliorez votre productivité et réduisez vos coûts avec notre solution de planning.
            </p>
            <div class="flex justify-center space-x-4">
                <a href="{{ route('pricing') }}" class="ebp-btn ebp-btn-primary bg-white text-purple-600 hover:bg-gray-100">
                    Commencer maintenant
                </a>
                <a href="{{ route('contact') }}" class="ebp-btn ebp-btn-outline border-white text-white hover:bg-white hover:text-purple-600">
                    Demander une démo
                </a>
            </div>
        </div>
    </section>
</div>
