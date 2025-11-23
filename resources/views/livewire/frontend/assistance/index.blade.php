<div class="bg-slate-50 min-h-screen">
    {{-- HERO SEARCH --}}
    <div class="bg-ovh-deep text-white py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
            <h1 class="text-3xl md:text-4xl font-bold mb-6">Comment pouvons-nous vous aider ?</h1>

            <div class="relative max-w-2xl mx-auto">
                <div class="relative">
                    <input wire:model.live.debounce.300ms="search" type="text"
                           class="w-full pl-12 pr-4 py-4 rounded-lg bg-white border-0 shadow-xl text-slate-900 focus:ring-2 focus:ring-ovh-accent"
                           placeholder="Rechercher un article (ex: configuration domaine, facture...)">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                {{-- SEARCH DROPDOWN --}}
                @if(!empty($search) && count($results) > 0)
                    <div class="absolute w-full bg-white mt-2 rounded-lg shadow-xl z-50 text-left overflow-hidden">
                        @foreach($results as $article)
                            <a href="{{ route('support.kb.show', $article->slug) }}" class="block px-6 py-4 hover:bg-slate-50 border-b border-slate-100 last:border-0 transition">
                                <h4 class="text-slate-900 font-bold">{{ $article->title }}</h4>
                                <p class="text-sm text-slate-500 truncate">{{ Str::limit(strip_tags($article->content), 80) }}</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- CATEGORIES GRID --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h2 class="text-xl font-bold text-slate-900 mb-8">Parcourir par thématiques</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($categories as $category)
                <a href="#" class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-md hover:border-ovh-primary transition group h-full flex flex-col">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-ovh-primary flex items-center justify-center mr-4 group-hover:bg-ovh-primary group-hover:text-white transition">
                            {{-- Icône dynamique ou par défaut --}}
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-ovh-primary transition">{{ $category->name }}</h3>
                    </div>
                    <p class="text-slate-500 text-sm flex-grow mb-4">{{ $category->description }}</p>
                    <div class="text-xs font-medium text-slate-400 bg-slate-50 py-2 px-3 rounded self-start">
                        {{ $category->articles_count }} articles
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- FOOTER HELP --}}
    <div class="bg-white border-t border-slate-200 py-12">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h3 class="text-lg font-bold text-slate-900 mb-2">Besoin d'une assistance technique ?</h3>
            <p class="text-slate-500 mb-6">Nos experts sont disponibles pour résoudre vos problématiques complexes.</p>
            @auth
                <a href="{{ route('client.support.tickets') }}" class="inline-flex items-center bg-ovh-primary text-white px-6 py-3 rounded-md font-semibold hover:bg-ovh-dark transition">
                    Créer un ticket de support
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center bg-ovh-primary text-white px-6 py-3 rounded-md font-semibold hover:bg-ovh-dark transition">
                    Connectez-vous pour contacter le support
                </a>
            @endauth
        </div>
    </div>
</div>
