<?php

namespace App\Livewire\Frontend;

use App\Enum\Helpdesk\ComponentStatusEnum;
use App\Models\Helpdesk\Incident;
use App\Models\Helpdesk\StatusSubscriber;
use App\Models\Helpdesk\SystemComponents;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.frontend')]
class Status extends Component
{
    public $refreshInterval = 60; // Rafraichissement auto toutes les 60s
    // Subscription Form
    public $showSubscribeModal = false;

    #[Validate('required|email|unique:status_subscribers,email')]
    public $subscriberEmail = '';

    public function subscribe()
    {
        $this->validate();

        StatusSubscriber::create([
            'email' => $this->subscriberEmail,
            'verification_token' => \Illuminate\Support\Str::random(32),
        ]);

        $this->reset('subscriberEmail');
        $this->showSubscribeModal = false;

        // Flash message (à gérer dans la vue)
        session()->flash('status_success', 'Vous êtes bien abonné aux alertes.');
    }

    /**
     * Génère l'historique des 60 derniers jours (Barres vertes/rouges)
     * Optimisation: On le fait une fois pour tous les incidents, pas par composant pour éviter les N+1
     */
    public function getUptimeHistoryProperty()
    {
        $days = 60;
        $history = [];
        $startDate = now()->subDays($days - 1)->startOfDay();

        // Récupérer tous les incidents des 60 derniers jours qui ne sont PAS des maintenances
        $incidents = Incident::where('occurred_at', '>=', $startDate)
            ->where('impact', '!=', 'maintenance') // On exclut les maintenances des "pannes"
            ->get()
            ->groupBy(function($item) {
                return $item->occurred_at->format('Y-m-d');
            });

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateString = $date->format('Y-m-d');

            $dayIncidents = $incidents->get($dateString);

            if ($dayIncidents) {
                // S'il y a eu un incident ce jour-là
                $worstImpact = 'minor';
                foreach($dayIncidents as $inc) {
                    if ($inc->impact === 'critical') $worstImpact = 'critical';
                    elseif ($inc->impact === 'major' && $worstImpact !== 'critical') $worstImpact = 'major';
                }

                $history[] = [
                    'date' => $date,
                    'status' => 'incident',
                    'impact' => $worstImpact,
                    'tooltip' => $date->translatedFormat('d M') . ' - Incident signalée'
                ];
            } else {
                // Tout va bien
                $history[] = [
                    'date' => $date,
                    'status' => 'operational',
                    'impact' => 'none',
                    'tooltip' => $date->translatedFormat('d M') . ' - Aucun incident'
                ];
            }
        }

        return $history;
    }

    public function getGlobalStatusProperty()
    {
        // Si un composant est en panne majeure -> Panne Majeure
        if (SystemComponents::where('status', ComponentStatusEnum::MAJOR_OUTAGE)->exists()) {
            return [
                'label' => 'Panne Majeure',
                'color' => 'bg-red-600',
                'icon' => 'x-circle',
                'message' => 'Certains systèmes critiques rencontrent des problèmes.'
            ];
        }

        // Si panne partielle -> Panne Partielle
        if (SystemComponents::where('status', ComponentStatusEnum::PARTIAL_OUTAGE)->exists()) {
            return [
                'label' => 'Panne Partielle',
                'color' => 'bg-orange-500',
                'icon' => 'exclamation-triangle',
                'message' => 'Certains services fonctionnent de manière dégradée.'
            ];
        }

        // Si maintenance -> Maintenance
        if (SystemComponents::where('status', ComponentStatusEnum::MAINTENANCE)->exists()) {
            return [
                'label' => 'Maintenance en cours',
                'color' => 'bg-blue-500',
                'icon' => 'wrench-screwdriver',
                'message' => 'Une opération de maintenance planifiée est en cours.'
            ];
        }

        // Sinon -> Tout va bien
        return [
            'label' => 'Tous les systèmes sont opérationnels',
            'color' => 'bg-green-500',
            'icon' => 'check-circle',
            'message' => 'Aucun incident n\'est à signaler pour le moment.'
        ];
    }

    public function render()
    {
        return view('livewire.frontend.status', [
            'groups' => SystemComponents::orderBy('order')->get()->groupBy('group'),
            'activeIncidents' => Incident::whereNull('resolved_at')
                ->with(['updates', 'components'])
                ->orderByDesc('occurred_at')
                ->get(),
            'pastIncidents' => Incident::whereNotNull('resolved_at')
                ->orderByDesc('resolved_at')
                ->take(5)
                ->get(),
            'uptimeHistory' => $this->uptimeHistory, // On passe l'historique à la vue
        ]);
    }
}
