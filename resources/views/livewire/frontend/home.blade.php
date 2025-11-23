<div class="bg-white">
    {{-- HERO SECTION --}}
    <div class="relative bg-ovh-deep overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white" />
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32 relative z-10">
            <div class="lg:grid lg:grid-cols-12 lg:gap-16 items-center">
                <div class="lg:col-span-7 text-center lg:text-left">
                    <h1 class="text-4xl md:text-6xl font-extrabold text-white tracking-tight leading-tight mb-6">
                        La plateforme tout-en-un pour <span class="text-ovh-accent">bâtir votre succès</span>
                    </h1>
                    <p class="text-lg md:text-xl text-blue-100 mb-8 max-w-2xl mx-auto lg:mx-0">
                        Gérez vos chantiers, votre facturation et vos équipes avec la puissance du Cloud Batistack. Sécurité, performance et simplicité pour les professionnels du BTP.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('register') }}" class="bg-ovh-primary text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-blue-600 transition shadow-lg hover:shadow-blue-500/30">
                            Commencer gratuitement
                        </a>
                        <a href="#features" class="bg-white/10 backdrop-blur-sm text-white border border-white/20 px-8 py-4 rounded-full font-bold text-lg hover:bg-white/20 transition">
                            Voir les modules
                        </a>
                    </div>
                    <div class="mt-8 text-sm text-blue-200 flex items-center justify-center lg:justify-start gap-4">
                        <span class="flex items-center"><svg class="w-4 h-4 mr-1 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Essai 14 jours offert</span>
                        <span class="flex items-center"><svg class="w-4 h-4 mr-1 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Pas de carte requise</span>
                    </div>
                </div>
                <div class="lg:col-span-5 mt-12 lg:mt-0 relative">
                    <!-- Illustration Abstract representing SaaS dashboard -->
                    <div class="bg-white rounded-xl shadow-2xl p-2 border border-slate-200 transform rotate-2 hover:rotate-0 transition duration-500">
                        <div class="bg-slate-50 rounded-lg overflow-hidden">
                            <img src="https://placehold.co/600x400/f1f5f9/0050d8?text=Interface+Batistack" alt="Interface Batistack" class="w-full h-auto opacity-90">
                        </div>
                    </div>
                    <!-- Floating Badge -->
                    <div class="absolute -bottom-6 -left-6 bg-white p-4 rounded-lg shadow-xl border border-slate-100 flex items-center gap-3 animate-bounce" style="animation-duration: 3s;">
                        <div class="bg-green-100 p-2 rounded-full text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase font-bold">Système</p>
                            <p class="font-bold text-slate-800">100% Opérationnel</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODULES SECTION (Grid Style) --}}
    <div id="features" class="py-24 bg-ovh-light">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-ovh-primary font-semibold tracking-wide uppercase text-sm">Écosystème Batistack</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                    Tout ce dont vous avez besoin,<br> au même endroit.
                </p>
                <p class="mt-4 max-w-2xl text-xl text-slate-500 mx-auto">
                    Composez votre offre sur mesure en activant uniquement les modules nécessaires à votre activité.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach(\App\Models\Product\Feature::all() as $feature)
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition duration-300 group">
                        <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center mb-6 group-hover:bg-ovh-primary transition">
                            <img src="{{ Storage::disk('public')->url('modules/'.$feature->slug.'.png') }}" alt="" class="w-6 h-6">
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">{{ Str::replace('Module', '', $feature->name) }}</h3>
                        <p class="text-slate-600 mb-6">{{ $feature->description }}</p>
                        <a href="{{ route('feature.show', $feature->slug) }}" class="text-ovh-primary font-semibold flex items-center group-hover:underline">
                            Découvrir <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- STATS / TRUST --}}
    <div class="bg-ovh-deep py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-blue-900">
                <div class="p-4">
                    <div class="text-4xl font-bold text-white mb-2">15 000+</div>
                    <div class="text-blue-200 uppercase tracking-wide text-sm">Chantiers gérés</div>
                </div>
                <div class="p-4">
                    <div class="text-4xl font-bold text-white mb-2">99.9%</div>
                    <div class="text-blue-200 uppercase tracking-wide text-sm">Disponibilité</div>
                </div>
                <div class="p-4">
                    <div class="text-4xl font-bold text-white mb-2">24/7</div>
                    <div class="text-blue-200 uppercase tracking-wide text-sm">Support Expert</div>
                </div>
            </div>
        </div>
    </div>

    {{-- CTA FINAL --}}
    <div class="bg-white py-24 relative overflow-hidden">
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
            <h2 class="text-3xl font-bold text-slate-900 mb-6">Prêt à digitaliser votre activité ?</h2>
            <p class="text-lg text-slate-600 mb-10">Rejoignez les entreprises qui font confiance à Batistack pour structurer leur croissance. Sans engagement.</p>
            <a href="{{ route('register') }}" class="inline-block bg-ovh-primary text-white px-10 py-4 rounded-full font-bold text-lg hover:bg-ovh-dark transition transform hover:-translate-y-1 shadow-xl">
                Créer un compte gratuitement
            </a>
        </div>
        <!-- Decorative circle -->
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] border border-slate-100 rounded-full -z-0"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] border border-slate-100 rounded-full -z-0"></div>
    </div>
</div>
