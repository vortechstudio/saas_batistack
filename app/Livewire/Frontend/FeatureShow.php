<?php

namespace App\Livewire\Frontend;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.frontend')]
class FeatureShow extends Component
{
    public $slug;
    public $feature;

    // Base de données simulée des contenus marketing
    protected $featuresList = [
        'facturation' => [
            'title' => 'Facturation & Devis',
            'subtitle' => 'Simplifiez votre gestion commerciale',
            'description' => 'Créez des devis professionnels, transformez-les en factures en un clic et suivez vos règlements. Une solution conforme aux normes fiscales 2025.',
            'image' => 'storage/modules/module-facturation.png',
            'icon' => 'document-currency-euro',
            'benefits' => [
                'Devis & Factures illimités',
                'Signature électronique intégrée',
                'Relances automatiques',
                'Bibliothèque de prix ouvrages',
                'Export comptable FEC',
                'Tableau de bord financier'
            ],
            'specs' => [
                'Conformité' => 'Loi Finance 2024 (Factur-X)',
                'Export' => 'PDF, Excel, CSV',
                'Intégration' => 'Chorus Pro Natif',
                'Devises' => 'Multi-devises'
            ]
        ],
        'chantier' => [
            'title' => 'Suivi de Chantier',
            'subtitle' => 'Maîtrisez vos opérations sur le terrain',
            'description' => 'Pilotez l’avancement de vos travaux en temps réel. Gérez les équipes, les matériaux et les imprévus depuis votre mobile ou tablette.',
            'image' => 'storage/modules/module-chantier.png',
            'icon' => 'building-office-2',
            'benefits' => [
                'Planning interactif (Gantt)',
                'Suivi de la rentabilité en temps réel',
                'Compte-rendu de chantier mobile',
                'Gestion des sous-traitants',
                'Suivi des consommations',
                'Photos et documents illimités'
            ],
            'specs' => [
                'Accessibilité' => 'Mode Hors-ligne (Mobile)',
                'Planification' => 'Vue Gantt & Calendrier',
                'Alertes' => 'Notifications Push & Email',
                'Géolocalisation' => 'Pointage GPS'
            ]
        ],
        'rh' => [
            'title' => 'Ressources Humaines',
            'subtitle' => 'Centralisez la gestion de vos équipes',
            'description' => 'Optimisez la planification de vos compagnons, gérez les absences et préparez les éléments variables de paie sans erreur.',
            'image' => 'storage/modules/module-rh.png',
            'icon' => 'users',
            'benefits' => [
                'Gestion des congés & absences',
                'Relevés d’heures numériques',
                'Coffre-fort numérique salarié',
                'Gestion des habilitations & visites médicales',
                'Notes de frais simplifiées',
                'Onboarding nouveaux collaborateurs'
            ],
            'specs' => [
                'Sécurité' => 'Données chiffrées RGPD',
                'Paie' => 'Export vers Silae, Sage, etc.',
                'Portail' => 'Espace personnel salarié',
                'Planning' => 'Vue équipe & individuelle'
            ]
        ],
        // Fallback générique pour les autres modules si besoin
    ];

    public function mount($slug)
    {
        $this->slug = $slug;

        if (!array_key_exists($slug, $this->featuresList)) {
            // Si le module n'existe pas, on redirige ou on affiche une 404
            // Pour l'exemple, on met des données par défaut si l'image existe, sinon 404
            if(file_exists(public_path('storage/modules/'.$slug.'.png'))) {
                $this->feature = [
                    'title' => ucfirst(str_replace('-', ' ', $slug)),
                    'subtitle' => 'Module spécialisé Batistack',
                    'description' => 'Ce module permet d\'étendre les fonctionnalités de votre ERP pour répondre aux besoins spécifiques de votre activité.',
                    'image' => 'storage/modules/'.$slug.'.png',
                    'icon' => 'cube',
                    'benefits' => ['Intégration native', 'Mises à jour incluses', 'Support dédié'],
                    'specs' => ['Type' => 'Module Extension', 'Version' => 'Latest']
                ];
            } else {
                abort(404);
            }
        } else {
            $this->feature = $this->featuresList[$slug];
        }
    }

    public function render()
    {
        return view('livewire.frontend.feature-show');
    }
}
