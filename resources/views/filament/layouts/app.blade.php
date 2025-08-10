@extends('filament-panels::layout')

@section('head')
    @parent
    
    <!-- Styles personnalisés pour la navigation améliorée -->
    <style>
        /* Amélioration des badges de navigation */
        .fi-sidebar-nav-item-badge {
            animation: pulse 2s infinite;
        }
        
        .fi-sidebar-nav-item-badge.critical {
            background-color: rgb(239, 68, 68);
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.5; }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Amélioration de la recherche globale */
        .fi-global-search-input {
            transition: all 0.3s ease;
        }
        
        .fi-global-search-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            border-color: rgb(59, 130, 246);
        }
        
        /* Indicateurs de raccourcis clavier */
        .keyboard-shortcut-indicator {
            opacity: 0;
            transition: opacity 0.2s ease;
            font-size: 0.75rem;
            color: rgb(156, 163, 175);
        }
        
        .fi-sidebar-nav-item:hover .keyboard-shortcut-indicator {
            opacity: 1;
        }
        
        /* Amélioration des notifications */
        .notification-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background-color: rgb(239, 68, 68);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .keyboard-shortcut-indicator {
                display: none;
            }
        }
    </style>
@endsection

@section('scripts')
    @parent
    
    <!-- Inclure les composants de navigation améliorée -->
    @include('filament.components.keyboard-shortcuts')
    @include('filament.components.enhanced-search')
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Améliorer l'affichage des badges de navigation
            enhanceNavigationBadges();
            
            // Ajouter des indicateurs de raccourcis clavier
            addKeyboardShortcutIndicators();
            
            // Améliorer les notifications en temps réel
            setupRealTimeNotifications();
        });
        
        function enhanceNavigationBadges() {
            const badges = document.querySelectorAll('.fi-sidebar-nav-item-badge');
            badges.forEach(badge => {
                const count = parseInt(badge.textContent);
                if (count > 10) {
                    badge.classList.add('critical');
                }
                
                // Ajouter un tooltip
                badge.title = `${count} notification(s)`;
            });
        }
        
        function addKeyboardShortcutIndicators() {
            const navigationItems = document.querySelectorAll('.fi-sidebar-nav-item');
            const shortcuts = {
                'dashboard': 'g d',
                'customers': 'g c',
                'licenses': 'g l',
                'products': 'g p',
                'notification': 'g n'
            };
            
            navigationItems.forEach(item => {
                const link = item.querySelector('a');
                if (!link) return;
                
                const href = link.getAttribute('href');
                let shortcut = null;
                
                Object.keys(shortcuts).forEach(key => {
                    if (href && href.includes(key)) {
                        shortcut = shortcuts[key];
                    }
                });
                
                if (shortcut) {
                    const indicator = document.createElement('span');
                    indicator.className = 'keyboard-shortcut-indicator ml-auto';
                    indicator.textContent = shortcut;
                    
                    const label = item.querySelector('.fi-sidebar-nav-item-label');
                    if (label) {
                        label.appendChild(indicator);
                    }
                }
            });
        }
        
        function setupRealTimeNotifications() {
            // Vérifier les nouvelles notifications toutes les 30 secondes
            setInterval(async () => {
                try {
                    const response = await fetch('/admin/api/notifications/count');
                    const data = await response.json();
                    
                    // Mettre à jour les badges de navigation
                    updateNavigationBadges(data);
                    
                    // Afficher une notification si de nouvelles notifications arrivent
                    if (data.new_count > 0) {
                        showNewNotificationAlert(data.new_count);
                    }
                } catch (error) {
                    console.log('Erreur lors de la vérification des notifications:', error);
                }
            }, 30000);
        }
        
        function updateNavigationBadges(data) {
            const notificationBadge = document.querySelector('[data-navigation-item="notifications"] .fi-sidebar-nav-item-badge');
            if (notificationBadge && data.total_count > 0) {
                notificationBadge.textContent = data.total_count;
                notificationBadge.style.display = 'inline-block';
            } else if (notificationBadge) {
                notificationBadge.style.display = 'none';
            }
        }
        
        function showNewNotificationAlert(count) {
            // Créer une notification toast
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
            toast.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                    </svg>
                    <span>${count} nouvelle(s) notification(s)</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Animer l'entrée
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
            }, 100);
            
            // Auto-suppression après 5 secondes
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 300);
            }, 5000);
        }
        
        // Améliorer l'accessibilité
        document.addEventListener('keydown', function(e) {
            // Alt + N pour aller aux notifications
            if (e.altKey && e.key === 'n') {
                e.preventDefault();
                const notificationLink = document.querySelector('[data-navigation-item="notifications"] a');
                if (notificationLink) {
                    notificationLink.click();
                }
            }
            
            // Alt + S pour focus sur la recherche
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                const searchInput = document.querySelector('[data-filament-global-search-input]');
                if (searchInput) {
                    searchInput.focus();
                }
            }
        });
    </script>
@endsection