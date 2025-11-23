<?php

namespace App\Livewire\Frontend;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.frontend')]
class Tarifs extends Component
{
    public $billingCycle = 'yearly'; // 'monthly' or 'yearly'
    protected $plans = [
        'starter' => [
            'name' => 'Starter',
            'description' => 'Pour les artisans et indépendants qui lancent leur activité.',
            'price_monthly' => 29,
            'price_yearly' => 24,
            'features' => [
                '1 Utilisateur',
                'Devis & Factures illimités',
                'Fichier client simple',
                'Support par email (48h)'
            ],
            'cta' => 'Démarrer gratuitement',
            'highlight' => false
        ],
        'pro' => [
            'name' => 'Business',
            'description' => 'La solution complète pour les PME en croissance.',
            'price_monthly' => 79,
            'price_yearly' => 65,
            'features' => [
                'Jusqu\'à 5 Utilisateurs',
                'Tout le module Starter',
                'Suivi de Chantier & Planning',
                'Gestion des Stocks',
                'Connexion Bancaire',
                'Support prioritaire'
            ],
            'cta' => 'Essayer Business',
            'highlight' => true // Offre recommandée
        ],
        'ultimate' => [
            'name' => 'Enterprise',
            'description' => 'Performance et intégrations pour les grandes structures.',
            'price_monthly' => 149,
            'price_yearly' => 129,
            'features' => [
                'Utilisateurs illimités',
                'Tout le module Business',
                'Module RH & Paie',
                'API & Webhooks',
                'Marque blanche',
                'Account Manager dédié'
            ],
            'cta' => 'Contacter les ventes',
            'highlight' => false
        ]
    ];

    public function setBillingCycle($cycle)
    {
        $this->billingCycle = $cycle;
    }

    public function render()
    {
        return view('livewire.frontend.tarifs', [
            'plans' => $this->plans
        ]);
    }
}
