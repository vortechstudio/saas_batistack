<div class="bg-slate-50">
    {{-- HERO SEARCH SECTION --}}
    <div class="bg-ovh-deep text-white relative overflow-hidden py-24">
        <!-- Abstract Background -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 opacity-20">
            <svg class="absolute right-0 top-0 h-full w-1/2 translate-x-1/3 transform" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0 0 L50 100 L100 0 Z" fill="url(#grad1)" />
                <defs>
                    <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" style="stop-color:rgb(0,80,216);stop-opacity:0" />
                        <stop offset="100%" style="stop-color:rgb(0,208,212);stop-opacity:1" />
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <h1 class="text-4xl font-bold mb-6">Centre de Ressources Batistack</h1>
            <p class="text-blue-100 text-lg mb-10">Documentation, Guides, API et Statut des services. Trouvez toutes les réponses à vos questions.</p>

            <!-- Search Bar -->
            <div class="relative max-w-2xl mx-auto">
                <input type="text"
                       class="w-full pl-12 pr-4 py-4 rounded-lg bg-white border-0 shadow-lg text-slate-900 focus:ring-2 focus:ring-ovh-accent placeholder-slate-400"
                       placeholder="Rechercher dans la documentation (ex: configurer facture, API key...)">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 text-sm text-blue-300">
                Recherches populaires : <a href="#" class="underline hover:text-white">Facturation</a>, <a href="#" class="underline hover:text-white">API</a>, <a href="#" class="underline hover:text-white">Export comptable</a>
            </div>
        </div>
    </div>

    {{-- HUB NAVIGATION --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 -mt-12 relative z-20">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Card Documentation -->
            <a href="#" class="bg-white p-6 rounded-xl shadow-lg border border-slate-100 hover:-translate-y-1 transition duration-300 group">
                <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center mb-4 text-ovh-primary group-hover:bg-ovh-primary group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Documentation</h3>
                <p class="text-slate-500 text-sm">Guides pas à pas pour prendre en main l'outil et configurer vos premiers chantiers.</p>
            </a>

            <!-- Card API -->
            <a href="#" class="bg-white p-6 rounded-xl shadow-lg border border-slate-100 hover:-translate-y-1 transition duration-300 group">
                <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center mb-4 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Espace Développeurs</h3>
                <p class="text-slate-500 text-sm">Documentation API REST, Webhooks et SDK pour intégrer Batistack à votre SI.</p>
            </a>

            <!-- Card Status -->
            <a href="#" class="bg-white p-6 rounded-xl shadow-lg border border-slate-100 hover:-translate-y-1 transition duration-300 group">
                <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center mb-4 text-green-600 group-hover:bg-green-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">État du service</h3>
                <p class="text-slate-500 text-sm">Vérifiez la disponibilité de nos services et abonnez-vous aux notifications d'incident.</p>
                <div class="mt-3 flex items-center text-xs font-semibold text-green-600">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                    Tous les systèmes opérationnels
                </div>
            </a>

            <!-- Card Community -->
            <a href="#" class="bg-white p-6 rounded-xl shadow-lg border border-slate-100 hover:-translate-y-1 transition duration-300 group">
                <div class="w-12 h-12 bg-orange-50 rounded-lg flex items-center justify-center mb-4 text-orange-500 group-hover:bg-orange-500 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Communauté</h3>
                <p class="text-slate-500 text-sm">Échangez avec d'autres utilisateurs Batistack, partagez vos astuces et votez pour la roadmap.</p>
            </a>
        </div>
    </div>

    {{-- LATEST NEWS / BLOG --}}
    <div class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Dernières actualités</h2>
                    <p class="text-slate-500">Mises à jour produits, conseils métiers et vie de l'entreprise.</p>
                </div>
                <a href="#" class="hidden md:inline-flex items-center text-ovh-primary font-semibold hover:underline">
                    Voir tous les articles <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Article 1 -->
                <article class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <img src="https://placehold.co/600x300/f1f5f9/94a3b8?text=Loi+Finance+2025" alt="Blog Image" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <span class="text-xs font-bold text-ovh-primary uppercase tracking-wide">Légal</span>
                        <h3 class="mt-2 text-xl font-bold text-slate-900 hover:text-ovh-primary transition">
                            <a href="#">Facturation électronique : ce qui change en 2025</a>
                        </h3>
                        <p class="mt-3 text-slate-600 text-sm">
                            Décryptage des nouvelles obligations légales pour les artisans et PME du bâtiment. Êtes-vous prêts ?
                        </p>
                        <div class="mt-4 text-slate-400 text-xs">Publié le 12 Octobre 2024</div>
                    </div>
                </article>

                <!-- Article 2 -->
                <article class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <img src="https://placehold.co/600x300/f1f5f9/94a3b8?text=Update+Chantier" alt="Blog Image" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <span class="text-xs font-bold text-green-600 uppercase tracking-wide">Mise à jour produit</span>
                        <h3 class="mt-2 text-xl font-bold text-slate-900 hover:text-ovh-primary transition">
                            <a href="#">Nouveau module de suivi de chantier mobile</a>
                        </h3>
                        <p class="mt-3 text-slate-600 text-sm">
                            Prenez des photos, annotez les plans et gérez les présences directement depuis votre smartphone.
                        </p>
                        <div class="mt-4 text-slate-400 text-xs">Publié le 28 Septembre 2024</div>
                    </div>
                </article>

                <!-- Article 3 -->
                <article class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <img src="https://placehold.co/600x300/f1f5f9/94a3b8?text=Astuce+Rentabilit%C3%A9" alt="Blog Image" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <span class="text-xs font-bold text-orange-500 uppercase tracking-wide">Conseil métier</span>
                        <h3 class="mt-2 text-xl font-bold text-slate-900 hover:text-ovh-primary transition">
                            <a href="#">5 astuces pour améliorer la marge de vos chantiers</a>
                        </h3>
                        <p class="mt-3 text-slate-600 text-sm">
                            Comment mieux chiffrer vos devis et éviter les dérives de coûts matériaux grâce à Batistack.
                        </p>
                        <div class="mt-4 text-slate-400 text-xs">Publié le 15 Septembre 2024</div>
                    </div>
                </article>
            </div>
        </div>
    </div>

    {{-- FAQ / CONTACT SUPPORT --}}
    <div class="bg-white border-t border-slate-200 py-16">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold text-slate-900 mb-4">Vous ne trouvez pas la réponse ?</h2>
            <p class="text-slate-600 mb-8">Notre équipe support est disponible pour vous aider du lundi au vendredi, de 9h à 18h.</p>
            <div class="flex justify-center gap-4">
                <a href="#" class="bg-ovh-deep text-white px-6 py-3 rounded-md font-semibold hover:bg-blue-900 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Ouvrir un ticket
                </a>
                <a href="#" class="bg-white text-slate-700 border border-slate-300 px-6 py-3 rounded-md font-semibold hover:bg-slate-50 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    Nous appeler
                </a>
            </div>
        </div>
    </div>
</div>
