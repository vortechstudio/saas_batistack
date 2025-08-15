<div class="min-h-screen bg-gray-50">
    <!-- Section Hero -->
    <section class="bg-gradient-to-r from-orange-500 to-red-600 text-white py-16">
        <div class="container mx-auto text-center px-4">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">Demandez votre démonstration gratuite</h1>
            <p class="text-xl mb-8 max-w-3xl mx-auto">Découvrez comment Batistack peut transformer la gestion de votre entreprise BTP en seulement 30 minutes.</p>
        </div>
    </section>

    <!-- Section Formulaire -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    <!-- Formulaire FilamentPHP -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-lg p-8">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Planifiez votre démonstration</h2>

                            <form wire:submit="submitDemo" class="space-y-6">
                                {{ $this->form }}

                                <div class="pt-4">
                                    <button type="submit"
                                            class="w-full ebp-btn ebp-btn-primary py-3 text-lg font-semibold"
                                            wire:loading.attr="disabled">
                                        <span wire:loading.remove>Demander ma démonstration</span>
                                        <span wire:loading class="flex items-center justify-center">
                                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Envoi en cours...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Informations complémentaires -->
                    <div class="space-y-8">
                        <!-- Ce que vous découvrirez -->
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Ce que vous découvrirez</h3>
                            <ul class="space-y-3">
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-3 mt-1">✓</span>
                                    <span class="text-sm">Interface intuitive adaptée aux professionnels du BTP</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-3 mt-1">✓</span>
                                    <span class="text-sm">Gestion complète des chantiers et projets</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-3 mt-1">✓</span>
                                    <span class="text-sm">Création de devis et factures professionnels</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-3 mt-1">✓</span>
                                    <span class="text-sm">Suivi en temps réel des équipes et ressources</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-3 mt-1">✓</span>
                                    <span class="text-sm">Rapports et analyses pour optimiser votre activité</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Processus de démonstration -->
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Comment ça se passe ?</h3>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center text-sm font-bold mr-3 mt-1 flex-shrink-0">1</div>
                                    <div>
                                        <h4 class="font-semibold text-sm">Prise de contact</h4>
                                        <p class="text-gray-600 text-xs">Nous vous contactons sous 24h pour planifier votre démonstration</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center text-sm font-bold mr-3 mt-1 flex-shrink-0">2</div>
                                    <div>
                                        <h4 class="font-semibold text-sm">Démonstration personnalisée</h4>
                                        <p class="text-gray-600 text-xs">30 minutes de présentation adaptée à vos besoins spécifiques</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center text-sm font-bold mr-3 mt-1 flex-shrink-0">3</div>
                                    <div>
                                        <h4 class="font-semibold text-sm">Questions & réponses</h4>
                                        <p class="text-gray-600 text-xs">Échanges libres pour répondre à toutes vos questions</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact direct -->
                        <div class="bg-orange-50 rounded-lg p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-2">Besoin d'une réponse immédiate ?</h3>
                            <p class="text-gray-600 mb-4 text-sm">Contactez directement notre équipe commerciale</p>
                            <div class="space-y-2">
                                <p class="flex items-center text-sm">
                                    <span class="mr-2">📞</span>
                                    <strong>01 23 45 67 89</strong>
                                </p>
                                <p class="flex items-center text-sm">
                                    <span class="mr-2">✉️</span>
                                    <strong>demo@batistack.com</strong>
                                </p>
                            </div>
                        </div>

                        <!-- Témoignage rapide -->
                        <div class="bg-blue-50 rounded-lg p-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">JD</div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-gray-700 italic">"Batistack a révolutionné notre gestion de chantiers. Un gain de temps énorme !"</p>
                                    <p class="text-xs text-gray-500 mt-2">Jean Dupont, Directeur - Entreprise Dupont BTP</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Avantages de la démo -->
    <section class="bg-white py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Pourquoi demander une démonstration ?</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">🎯</span>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Personnalisée</h3>
                        <p class="text-gray-600 text-sm">Adaptée à votre secteur d'activité et vos besoins spécifiques</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">⚡</span>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Rapide</h3>
                        <p class="text-gray-600 text-sm">30 minutes suffisent pour découvrir toutes les fonctionnalités</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">💡</span>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Éclairante</h3>
                        <p class="text-gray-600 text-sm">Découvrez comment optimiser votre gestion dès maintenant</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    // Animation d'apparition des éléments au scroll
    document.addEventListener('DOMContentLoaded', function() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.bg-white').forEach(el => {
            observer.observe(el);
        });
    });
</script>
@endpush

@push('styles')
<style>
    .animate-fade-in {
        animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush
