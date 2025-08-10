<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration des raccourcis clavier
    const shortcuts = {
        // Navigation rapide
        'g d': () => window.location.href = '{{ route("filament.admin.pages.dashboard") }}',
        'g c': () => window.location.href = '{{ route("filament.admin.resources.customers.index") }}',
        'g l': () => window.location.href = '{{ route("filament.admin.resources.licenses.index") }}',
        'g p': () => window.location.href = '{{ route("filament.admin.resources.products.index") }}',
        'g n': () => window.location.href = '{{ route("filament.admin.pages.notification-center") }}',
        
        // Actions rapides
        'c c': () => window.location.href = '{{ route("filament.admin.resources.customers.create") }}',
        'c l': () => window.location.href = '{{ route("filament.admin.resources.licenses.create") }}',
        'c p': () => window.location.href = '{{ route("filament.admin.resources.products.create") }}',
        
        // Recherche
        '/': () => {
            const searchInput = document.querySelector('[data-filament-global-search-input]');
            if (searchInput) {
                searchInput.focus();
            }
        },
        
        // Aide
        '?': () => showKeyboardShortcutsModal(),
        
        // Échapper
        'Escape': () => {
            // Fermer les modales ouvertes
            const modals = document.querySelectorAll('[data-modal-open="true"]');
            modals.forEach(modal => {
                const closeButton = modal.querySelector('[data-modal-close]');
                if (closeButton) closeButton.click();
            });
            
            // Désélectionner les éléments actifs
            const activeElement = document.activeElement;
            if (activeElement && activeElement.blur) {
                activeElement.blur();
            }
        }
    };

    let keySequence = '';
    let sequenceTimeout;

    document.addEventListener('keydown', function(e) {
        // Ignorer si on est dans un champ de saisie
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
            if (e.key === 'Escape') {
                e.target.blur();
            }
            return;
        }

        // Gérer les touches spéciales
        if (e.key === 'Escape') {
            shortcuts['Escape']();
            return;
        }

        // Construire la séquence de touches
        clearTimeout(sequenceTimeout);
        
        if (e.key === ' ') {
            keySequence += ' ';
        } else {
            keySequence += e.key.toLowerCase();
        }

        // Vérifier si la séquence correspond à un raccourci
        if (shortcuts[keySequence]) {
            e.preventDefault();
            shortcuts[keySequence]();
            keySequence = '';
            return;
        }

        // Réinitialiser la séquence après 1 seconde
        sequenceTimeout = setTimeout(() => {
            keySequence = '';
        }, 1000);
    });

    // Fonction pour afficher la modale d'aide
    function showKeyboardShortcutsModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
        modal.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Raccourcis Clavier</h2>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-medium text-gray-900 dark:text-white mb-3">Navigation</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Tableau de bord</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">g d</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Clients</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">g c</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Licences</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">g l</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Produits</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">g p</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Notifications</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">g n</kbd>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="font-medium text-gray-900 dark:text-white mb-3">Actions Rapides</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Nouveau client</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">c c</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Nouvelle licence</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">c l</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Nouveau produit</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">c p</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Rechercher</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">/</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Aide</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">?</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Échapper</span>
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">Esc</kbd>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            💡 Astuce : Les raccourcis de navigation utilisent des séquences de deux touches (ex: g puis d pour le tableau de bord)
                        </p>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Fermer avec Escape
        modal.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                modal.remove();
            }
        });
        
        // Fermer en cliquant à l'extérieur
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    // Indicateur visuel des raccourcis dans la navigation
    function addShortcutIndicators() {
        const navigationItems = document.querySelectorAll('[data-navigation-item]');
        const shortcuts = {
            'dashboard': 'g d',
            'customers': 'g c',
            'licenses': 'g l',
            'products': 'g p',
            'notifications': 'g n'
        };

        navigationItems.forEach(item => {
            const href = item.getAttribute('href');
            let shortcut = null;
            
            if (href && href.includes('dashboard')) shortcut = shortcuts.dashboard;
            else if (href && href.includes('customers')) shortcut = shortcuts.customers;
            else if (href && href.includes('licenses')) shortcut = shortcuts.licenses;
            else if (href && href.includes('products')) shortcut = shortcuts.products;
            else if (href && href.includes('notification')) shortcut = shortcuts.notifications;
            
            if (shortcut) {
                const indicator = document.createElement('span');
                indicator.className = 'ml-auto text-xs text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity';
                indicator.textContent = shortcut;
                item.appendChild(indicator);
            }
        });
    }

    // Ajouter les indicateurs après le chargement
    setTimeout(addShortcutIndicators, 1000);
});
</script>

<!-- Indicateur de raccourcis dans le coin -->
<div class="fixed bottom-4 right-4 z-40">
    <button 
        onclick="document.dispatchEvent(new KeyboardEvent('keydown', {key: '?'}))"
        class="bg-gray-800 dark:bg-gray-700 text-white p-2 rounded-full shadow-lg hover:bg-gray-700 dark:hover:bg-gray-600 transition-colors"
        title="Afficher les raccourcis clavier (?)">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
    </button>
</div>