<?php

namespace App\Observers\Helpdesk;

use App\Enum\Helpdesk\ComponentStatusEnum;
use App\Enum\Helpdesk\IncidentStatusEnum;
use App\Models\Helpdesk\Incident;

class IncidentObserver
{
    public function created(Incident $incident): void
    {
        // 1. Créer automatiquement la première entrée de la timeline
        $incident->updates()->create([
            'status' => $incident->status,
            'message' => "L'incident a été déclaré. Nos équipes investiguent actuellement sur le problème.",
        ]);

        // 2. Mettre à jour le statut des composants liés (Sync)
        $this->syncComponentStatus($incident);
    }

    public function updated(Incident $incident): void
    {
        // Si le statut de l'incident change, on ajoute une entrée dans la timeline
        if ($incident->isDirty('status')) {
            $message = match ($incident->status) {
                IncidentStatusEnum::IDENTIFIED => "La cause du problème a été identifiée. Un correctif est en cours de préparation.",
                IncidentStatusEnum::MONITORING => "Le correctif a été appliqué. Nous surveillons les résultats.",
                IncidentStatusEnum::RESOLVED => "L'incident est résolu. Tous les systèmes sont à nouveau opérationnels.",
                default => "Mise à jour du statut de l'incident.",
            };

            $incident->updates()->create([
                'status' => $incident->status,
                'message' => $message,
            ]);
        }

        // Synchro des composants
        $this->syncComponentStatus($incident);
    }

    private function syncComponentStatus(Incident $incident): void
    {
        // Si l'incident est résolu, on remet les composants en "Opérationnel"
        if ($incident->status === IncidentStatusEnum::RESOLVED) {
            $incident->components()->update(['status' => ComponentStatusEnum::OPERATIONAL]);
            return;
        }

        // Sinon, on applique le statut correspondant à l'impact de l'incident
        $targetStatus = match ($incident->impact) {
            'minor' => ComponentStatusEnum::PERFORMANCE_ISSUES,
            'major' => ComponentStatusEnum::PARTIAL_OUTAGE,
            'critical' => ComponentStatusEnum::MAJOR_OUTAGE,
            'maintenance' => ComponentStatusEnum::MAINTENANCE,
            default => ComponentStatusEnum::OPERATIONAL, // Cas de sécurité
        };

        if ($targetStatus !== ComponentStatusEnum::OPERATIONAL) {
            $incident->components()->update(['status' => $targetStatus]);
        }
    }
}
