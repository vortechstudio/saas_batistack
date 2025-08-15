<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body>
    <div class="min-h-screen flex flex-col">
        <!-- Header ERP Bâtiment -->
        <header class="ebp-header">
            <nav class="ebp-nav-container">
                <a href="{{ route('home') }}" class="ebp-logo">
                    {{ config('app.name') }}
                </a>

                <ul class="ebp-nav-menu">
                    <li><a href="{{ route('home') }}" class="ebp-nav-link">Accueil</a></li>
                    <li class="ebp-dropdown">
                        <a href="{{ route('solutions') }}" class="ebp-nav-link ebp-dropdown-toggle">
                            Solutions BTP
                            <svg class="w-4 h-4 ml-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <ul class="ebp-dropdown-menu">
                            <li><a href="{{ route('solutions.gestion-chantier') }}" class="ebp-dropdown-link">🏗️ Gestion de chantier</a></li>
                            <li><a href="{{ route('solutions.devis-metres') }}" class="ebp-dropdown-link">📋 Devis & Métrés</a></li>
                            <li><a href="{{ route('solutions.facturation-btp') }}" class="ebp-dropdown-link">💰 Facturation BTP</a></li>
                            <li><a href="{{ route('solutions.planning-resources') }}" class="ebp-dropdown-link">📅 Planning & Resources</a></li>
                            <li><a href="{{ route('solutions.comptabilite-btp') }}" class="ebp-dropdown-link">📊 Comptabilité BTP</a></li>
                            <li><a href="{{ route('solutions.gestion-stock') }}" class="ebp-dropdown-link">📦 Gestion des Stock</a></li>
                        </ul>
                    </li>
                    <li><a href="{{ route('resources') }}" class="ebp-nav-link">Ressources</a></li>
                    <li><a href="{{ route('support') }}" class="ebp-nav-link">Support Technique</a></li>
                    <li><a href="{{ route('pricing') }}" class="ebp-nav-link">Tarifs</a></li>
                </ul>

                <div class="ebp-auth-buttons">
                    <a href="{{ route('login') }}" class="ebp-btn ebp-btn-outline">Connexion</a>
                    <a href="{{ route('register') }}" class="ebp-btn ebp-btn-primary">Démo gratuite</a>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="ebp-main flex-1">
            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>

        <!-- Footer ERP Bâtiment -->
        <footer class="ebp-footer">
            <div class="ebp-footer-container">
                <div class="ebp-footer-content">
                    <div class="ebp-footer-section">
                        <h3>BatiStack ERP</h3>
                        <p>La solution ERP complète dédiée aux entreprises du bâtiment et des travaux publics. Gestion de chantiers, devis, facturation, comptabilité et suivi de projets.</p>
                        <p><strong>📞 Support :</strong> 01 23 45 67 89</p>
                        <p><strong>✉️ Contact :</strong> contact@batistack.fr</p>
                        <p><strong>🏢 Siège :</strong> Paris, France</p>
                    </div>

                    <div class="ebp-footer-section">
                        <h3>Modules BTP</h3>
                        <ul class="ebp-footer-links">
                            <li><a href="{{ route('solutions.gestion-chantier') }}">Gestion de chantiers</a></li>
                            <li><a href="{{ route('solutions.devis-metres') }}">Devis & Métrés</a></li>
                            <li><a href="{{ route('solutions.facturation-btp') }}">Facturation BTP</a></li>
                            <li><a href="{{ route('solutions.planning-resources') }}">Planning & Ressources</a></li>
                            <li><a href="{{ route('solutions.comptabilite-btp') }}">Comptabilité BTP</a></li>
                            <li><a href="{{ route('solutions.gestion-stock') }}">Gestion des stocks</a></li>
                        </ul>
                    </div>

                    <div class="ebp-footer-section">
                        <h3>Support & Formation</h3>
                        <ul class="ebp-footer-links">
                            <li><a href="#">Centre d'aide BTP</a></li>
                            <li><a href="#">Documentation technique</a></li>
                            <li><a href="#">Formation utilisateurs</a></li>
                            <li><a href="#">Webinaires métier</a></li>
                            <li><a href="#">Support technique 24/7</a></li>
                            <li><a href="#">Communauté BTP</a></li>
                        </ul>
                    </div>

                    <div class="ebp-footer-section">
                        <h3>Entreprise</h3>
                        <ul class="ebp-footer-links">
                            <li><a href="#">À propos de nous</a></li>
                            <li><a href="#">Nos références BTP</a></li>
                            <li><a href="#">Partenaires métier</a></li>
                            <li><a href="#">Actualités secteur</a></li>
                            <li><a href="#">Carrières</a></li>
                            <li><a href="#">Presse & Médias</a></li>
                        </ul>
                    </div>
                </div>

                <div class="ebp-footer-bottom">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }} - ERP spécialisé Bâtiment & TP. Tous droits réservés. |
                        <a href="#">Mentions légales</a> |
                        <a href="#">Politique de confidentialité</a> |
                        <a href="#">CGU BTP</a> |
                        <a href="#">Certifications</a>
                    </p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
