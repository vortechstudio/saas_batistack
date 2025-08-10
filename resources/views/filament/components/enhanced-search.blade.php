<script>
document.addEventListener('DOMContentLoaded', function() {
    // Améliorer la recherche globale existante
    enhanceGlobalSearch();
    
    function enhanceGlobalSearch() {
        const searchInput = document.querySelector('[data-filament-global-search-input]');
        if (!searchInput) return;
        
        // Ajouter des fonctionnalités avancées
        let searchTimeout;
        let searchHistory = JSON.parse(localStorage.getItem('filament_search_history') || '[]');
        
        // Conteneur pour les suggestions
        const suggestionsContainer = createSuggestionsContainer(searchInput);
        
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                hideSuggestions();
                return;
            }
            
            searchTimeout = setTimeout(() => {
                showSearchSuggestions(query, suggestionsContainer);
            }, 300);
        });
        
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const query = e.target.value.trim();
                if (query) {
                    addToSearchHistory(query);
                }
            }
            
            if (e.key === 'Escape') {
                hideSuggestions();
                e.target.blur();
            }
        });
        
        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) {
                showSearchSuggestions(this.value.trim(), suggestionsContainer);
            } else {
                showRecentSearches(suggestionsContainer);
            }
        });
        
        // Fermer les suggestions en cliquant ailleurs
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                hideSuggestions();
            }
        });
        
        function createSuggestionsContainer(input) {
            const container = document.createElement('div');
            container.className = 'absolute top-full left-0 right-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto hidden';
            container.style.marginTop = '4px';
            
            // Positionner relativement à l'input
            const inputContainer = input.closest('.relative') || input.parentElement;
            if (inputContainer) {
                inputContainer.style.position = 'relative';
                inputContainer.appendChild(container);
            }
            
            return container;
        }
        
        function showSearchSuggestions(query, container) {
            // Suggestions basées sur les types de contenu
            const suggestions = [
                {
                    type: 'customers',
                    icon: '👥',
                    label: 'Rechercher dans les clients',
                    url: `{{ route('filament.admin.resources.customers.index') }}?search=${encodeURIComponent(query)}`
                },
                {
                    type: 'licenses',
                    icon: '📄',
                    label: 'Rechercher dans les licences',
                    url: `{{ route('filament.admin.resources.licenses.index') }}?search=${encodeURIComponent(query)}`
                },
                {
                    type: 'products',
                    icon: '📦',
                    label: 'Rechercher dans les produits',
                    url: `{{ route('filament.admin.resources.products.index') }}?search=${encodeURIComponent(query)}`
                },
                {
                    type: 'activity',
                    icon: '📊',
                    label: 'Rechercher dans l\'activité',
                    url: `{{ route('filament.admin.resources.activity-logs.index') }}?search=${encodeURIComponent(query)}`
                }
            ];
            
            // Suggestions rapides basées sur la requête
            const quickSuggestions = getQuickSuggestions(query);
            
            let html = '';
            
            if (quickSuggestions.length > 0) {
                html += '<div class="p-2 border-b border-gray-200 dark:border-gray-700">';
                html += '<div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Suggestions rapides</div>';
                quickSuggestions.forEach(suggestion => {
                    html += `
                        <a href="${suggestion.url}" class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md text-sm">
                            <span class="mr-2">${suggestion.icon}</span>
                            <span>${suggestion.label}</span>
                        </a>
                    `;
                });
                html += '</div>';
            }
            
            html += '<div class="p-2">';
            html += '<div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Rechercher dans</div>';
            suggestions.forEach(suggestion => {
                html += `
                    <a href="${suggestion.url}" class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md text-sm">
                        <span class="mr-2">${suggestion.icon}</span>
                        <span>${suggestion.label}</span>
                        <span class="ml-auto text-xs text-gray-400">"${query}"</span>
                    </a>
                `;
            });
            html += '</div>';
            
            container.innerHTML = html;
            container.classList.remove('hidden');
        }
        
        function showRecentSearches(container) {
            if (searchHistory.length === 0) return;
            
            let html = '<div class="p-2">';
            html += '<div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Recherches récentes</div>';
            
            searchHistory.slice(0, 5).forEach(search => {
                html += `
                    <div class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md text-sm cursor-pointer" onclick="document.querySelector('[data-filament-global-search-input]').value='${search}'; document.querySelector('[data-filament-global-search-input]').dispatchEvent(new Event('input'));">
                        <span class="mr-2">🕒</span>
                        <span>${search}</span>
                        <button onclick="event.stopPropagation(); removeFromSearchHistory('${search}')" class="ml-auto text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
            container.classList.remove('hidden');
        }
        
        function getQuickSuggestions(query) {
            const suggestions = [];
            
            // Suggestions basées sur des patterns courants
            if (query.match(/^\d+$/)) {
                suggestions.push({
                    icon: '🔍',
                    label: `Rechercher l'ID ${query}`,
                    url: `{{ route('filament.admin.pages.dashboard') }}?global_search=${query}`
                });
            }
            
            if (query.includes('@')) {
                suggestions.push({
                    icon: '📧',
                    label: `Rechercher l'email ${query}`,
                    url: `{{ route('filament.admin.resources.customers.index') }}?search=${encodeURIComponent(query)}`
                });
            }
            
            if (query.toLowerCase().includes('license') || query.toLowerCase().includes('licence')) {
                suggestions.push({
                    icon: '📄',
                    label: 'Voir toutes les licences',
                    url: `{{ route('filament.admin.resources.licenses.index') }}`
                });
            }
            
            if (query.toLowerCase().includes('expired') || query.toLowerCase().includes('expiré')) {
                suggestions.push({
                    icon: '⚠️',
                    label: 'Licences expirées',
                    url: `{{ route('filament.admin.resources.licenses.index') }}?filter[status]=expired`
                });
            }
            
            return suggestions;
        }
        
        function hideSuggestions() {
            const container = document.querySelector('.absolute.top-full');
            if (container) {
                container.classList.add('hidden');
            }
        }
        
        function addToSearchHistory(query) {
            searchHistory = searchHistory.filter(item => item !== query);
            searchHistory.unshift(query);
            searchHistory = searchHistory.slice(0, 10); // Garder seulement les 10 dernières
            localStorage.setItem('filament_search_history', JSON.stringify(searchHistory));
        }
        
        window.removeFromSearchHistory = function(query) {
            searchHistory = searchHistory.filter(item => item !== query);
            localStorage.setItem('filament_search_history', JSON.stringify(searchHistory));
            
            // Rafraîchir l'affichage
            const searchInput = document.querySelector('[data-filament-global-search-input]');
            if (searchInput && document.activeElement === searchInput) {
                showRecentSearches(document.querySelector('.absolute.top-full'));
            }
        };
    }
    
    // Améliorer l'apparence de la barre de recherche
    function enhanceSearchAppearance() {
        const searchInput = document.querySelector('[data-filament-global-search-input]');
        if (!searchInput) return;
        
        // Ajouter un placeholder plus informatif
        searchInput.placeholder = 'Rechercher... (appuyez sur "/" pour focus)';
        
        // Ajouter une icône de recherche si elle n'existe pas
        const searchContainer = searchInput.closest('.relative');
        if (searchContainer && !searchContainer.querySelector('.search-icon')) {
            const icon = document.createElement('div');
            icon.className = 'search-icon absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400';
            icon.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            `;
            searchContainer.appendChild(icon);
            
            // Ajuster le padding de l'input
            searchInput.style.paddingLeft = '2.5rem';
        }
    }
    
    setTimeout(enhanceSearchAppearance, 500);
});
</script>

<style>
/* Styles pour la recherche améliorée */
[data-filament-global-search-input] {
    transition: all 0.2s ease;
}

[data-filament-global-search-input]:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    border-color: rgb(59, 130, 246);
}

.search-suggestions {
    backdrop-filter: blur(8px);
}

.search-suggestions a:hover {
    transform: translateX(2px);
    transition: transform 0.1s ease;
}

/* Animation pour les suggestions */
.absolute.top-full {
    animation: slideDown 0.2s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .absolute.top-full {
        left: -1rem;
        right: -1rem;
    }
}
</style>