<div class="bg-white">
    {{-- HERO SECTION --}}
    <div class="bg-ovh-deep text-white relative overflow-hidden">
        <!-- Background Patterns -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-ovh-primary rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-96 h-96 bg-ovh-accent rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 relative z-10">
            <!-- Breadcrumb -->
            <nav class="flex text-sm text-blue-200 mb-8" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('home') }}" class="hover:text-white transition">Accueil</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>
                            <a href="{{ route('home') }}#features" class="hover:text-white transition">Solutions</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>
                            <span class="text-white font-medium">{{ $feature['title'] }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="lg:grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-block px-3 py-1 mb-4 text-xs font-semibold tracking-wider text-ovh-accent uppercase bg-ovh-primary/20 rounded-full border border-ovh-primary/30">
                        Module Batistack
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-6">
                        {{ $feature['title'] }}
                    </h1>
                    <p class="text-xl text-blue-100 mb-8 font-light leading-relaxed">
                        {{ $feature['description'] }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('register') }}" class="bg-ovh-primary hover:bg-blue-600 text-white px-8 py-3.5 rounded-md font-semibold text-center transition shadow-lg shadow-blue-900/50 flex items-center justify-center">
                            Démarrer l'essai gratuit
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                        <a href="#details" class="bg-transparent border border-white/30 hover:bg-white/10 text-white px-8 py-3.5 rounded-md font-semibold text-center transition flex items-center justify-center">
                            Voir les détails techniques
                        </a>
                    </div>
                </div>
                <div class="mt-12 lg:mt-0 relative">
                    <!-- Image Frame style OVH/Browser -->
                    <div class="rounded-lg bg-white/5 backdrop-blur-sm border border-white/10 p-2 shadow-2xl">
                        <div class="bg-slate-900 rounded overflow-hidden relative aspect-video flex items-center justify-center">
                            @if(file_exists(public_path($feature['image'])))
                                <img src="{{ asset($feature['image']) }}" alt="{{ $feature['title'] }}" class="w-full h-full object-cover object-top">
                            @else
                                <div class="text-slate-500 flex flex-col items-center">
                                    <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span>Aperçu {{ $feature['title'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FEATURES LIST --}}
    <div id="details" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-12 gap-16">
                <!-- Left: Benefits List -->
                <div class="lg:col-span-7">
                    <h2 class="text-3xl font-bold text-slate-900 mb-8">Fonctionnalités clés</h2>
                    <div class="grid md:grid-cols-2 gap-y-6 gap-x-8">
                        @foreach($feature['benefits'] as $benefit)
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-600 mt-0.5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-lg font-medium text-slate-900">{{ $benefit }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-12 p-6 bg-slate-50 rounded-xl border border-slate-100">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-ovh-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Le saviez-vous ?
                        </h3>
                        <p class="text-slate-600">
                            Ce module est entièrement interconnecté avec le reste de la suite Batistack. Une donnée saisie ici est immédiatement disponible pour les autres services, sans double saisie.
                        </p>
                    </div>
                </div>

                <!-- Right: Technical Specs Box (Style "Serveur Dédié" OVH) -->
                <div class="lg:col-span-5 mt-12 lg:mt-0">
                    <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                            <h3 class="font-bold text-slate-800">Spécifications Techniques</h3>
                        </div>
                        <div class="p-0">
                            <table class="w-full text-sm text-left">
                                <tbody>
                                @foreach($feature['specs'] as $key => $value)
                                    <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50 transition">
                                        <td class="px-6 py-4 font-medium text-slate-600 bg-slate-50/50 w-1/3">{{ $key }}</td>
                                        <td class="px-6 py-4 text-slate-900 font-semibold">{{ $value }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-6 bg-slate-50 border-t border-slate-200 text-center">
                            <p class="text-xs text-slate-500 mb-4">Inclus dans toutes les offres Batistack Pro et Ultimate.</p>
                            <a href="{{ route('register') }}" class="block w-full py-3 px-4 bg-white border border-ovh-primary text-ovh-primary font-bold rounded hover:bg-ovh-primary hover:text-white transition">
                                Configurer ce module
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CTA FINAL --}}
    <div class="bg-ovh-light py-16 border-t border-slate-200">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold text-slate-900 mb-4">Pas sûr que ce module soit fait pour vous ?</h2>
            <p class="text-slate-600 mb-8">Nos experts sont disponibles pour une démonstration personnalisée de 15 minutes.</p>
            <div class="flex justify-center gap-4">
                <button class="text-ovh-primary font-semibold hover:underline">Contacter l'équipe commerciale</button>
                <span class="text-slate-300">|</span>
                <button class="text-ovh-primary font-semibold hover:underline">Voir la documentation</button>
            </div>
        </div>
    </div>
</div>
