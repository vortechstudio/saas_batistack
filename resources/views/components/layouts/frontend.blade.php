<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Batistack') }} - Solution SaaS BTP</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>
<body class="font-sans text-slate-800 antialiased bg-white flex flex-col min-h-screen">

{{-- TOP BAR (Style OVH : Barre très fine sombre au dessus) --}}
<div class="bg-ovh-deep text-white text-xs py-2 px-4 hidden md:block">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="flex space-x-6">
            <a href="#" class="font-bold text-white border-b border-ovh-accent pb-0.5">Professionnels & Entreprises</a>
            <a href="#" class="hover:text-ovh-accent transition">Partenaires</a>
        </div>
        <div class="flex space-x-6">
            <a href="#" class="hover:text-ovh-accent transition">Assistance</a>
            <a href="#" class="hover:text-ovh-accent transition">Contact</a>
        </div>
    </div>
</div>

{{-- NAVBAR --}}
<header class="sticky top-0 z-50 bg-white shadow-md border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <x-app-logo class="block h-10 w-auto fill-current text-ovh-primary" />
                </a>
            </div>

            <!-- Navigation Links (Desktop) -->
            <div class="hidden space-x-8 md:-my-px md:ml-10 md:flex items-center font-medium text-slate-600">
                <!-- Menu simplifié et plus direct -->
                <a href="#features" class="hover:text-ovh-primary transition">Fonctionnalités</a>
                <a href="{{ route('tarifs') }}" class="hover:text-ovh-primary transition">Tarifs</a>
                <a href="{{ route('company') }}" class="hover:text-ovh-primary transition">Entreprise</a>
                <a href="{{ route('ressources') }}" class="hover:text-ovh-primary transition">Ressources</a>
            </div>

            <!-- Actions -->
            <div class="hidden md:flex items-center space-x-4">
                @auth
                    <a href="{{ url('/client/dashboard') }}" class="text-sm font-semibold text-slate-700 hover:text-ovh-primary">
                        Mon Espace
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-ovh-primary px-3 py-2">
                        Connexion
                    </a>
                    <a href="{{ route('register') }}" class="rounded-full bg-ovh-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-ovh-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ovh-primary transition-all">
                        Créer un compte
                    </a>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center md:hidden">
                <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>

{{-- MAIN CONTENT --}}
<main class="flex-grow">
    {{ $slot }}
</main>

{{-- FOOTER (Style OVH : Fond sombre, beaucoup de liens, très structuré) --}}
<footer class="bg-ovh-deep text-slate-300 py-12 border-t border-ovh-primary/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8 text-sm">
        <div class="col-span-2 lg:col-span-2">
            <div class="flex items-center gap-2 mb-6">
                <x-app-logo class="block h-8 w-auto fill-current text-white" />
                <span class="font-bold text-xl text-white tracking-tight">Batistack</span>
            </div>
            <p class="mb-6 text-slate-400 max-w-sm">
                La solution complète pour la gestion de vos chantiers, de votre facturation et de vos équipes. Simplifiez votre BTP avec Batistack.
            </p>
            <div class="flex space-x-4">
                <!-- Social Placeholder -->
                <a href="#" class="bg-slate-800 p-2 rounded-full hover:bg-ovh-primary transition"><span class="sr-only">LinkedIn</span><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg></a>
            </div>
        </div>

        <div>
            <h3 class="text-white font-semibold mb-4">Solutions</h3>
            <ul class="space-y-3">
                <li><a href="#" class="hover:text-white hover:underline">Gestion de Chantier</a></li>
                <li><a href="#" class="hover:text-white hover:underline">Facturation & Devis</a></li>
                <li><a href="#" class="hover:text-white hover:underline">Planification RH</a></li>
                <li><a href="#" class="hover:text-white hover:underline">Stock & Matériel</a></li>
            </ul>
        </div>

        <div>
            <h3 class="text-white font-semibold mb-4">Entreprise</h3>
            <ul class="space-y-3">
                <li><a href="#" class="hover:text-white hover:underline">À propos</a></li>
                <li><a href="#" class="hover:text-white hover:underline">Carrières</a></li>
                <li><a href="#" class="hover:text-white hover:underline">Blog</a></li>
                <li><a href="#" class="hover:text-white hover:underline">Presse</a></li>
            </ul>
        </div>

        <div>
            <h3 class="text-white font-semibold mb-4">Légal & Support</h3>
            <ul class="space-y-3">
                <li><a href="#" class="hover:text-white hover:underline">Centre d'aide</a></li>
                <li><a href="#" class="hover:text-white hover:underline">État du service</a></li>
                <li><a href="#" class="hover:text-white hover:underline">Mentions légales</a></li>
                <li><a href="#" class="hover:text-white hover:underline">Confidentialité</a></li>
            </ul>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-8 border-t border-slate-800 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} Vortech Studio - Batistack. Tous droits réservés. Hébergé en France.
    </div>
</footer>

@livewireScripts
</body>
</html>
