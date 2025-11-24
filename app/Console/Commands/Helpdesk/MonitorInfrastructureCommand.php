<?php

namespace App\Console\Commands\Helpdesk;

use App\Enum\Helpdesk\ComponentStatusEnum;
use App\Models\Helpdesk\SystemComponents;
use Http;
use Illuminate\Console\Command;

class MonitorInfrastructureCommand extends Command
{
    protected $signature = 'status:monitor';

    protected $description = 'Vérifie la disponibilité des services critiques';

    public function handle(): void
    {
        // Exemple : Vérifier si la page d'accueil répond (Code 200)
        $this->checkUrl(config('app.url'), 'Site Web Public');

        // Exemple : Vérifier si l'API répond
        // $this->checkUrl(config('app.url').'/api/health', 'API Gateway');
    }

    protected function checkUrl(string $url, string $componentName)
    {
        $component = SystemComponents::where('name', $componentName)->first();
        if (!$component) return;

        try {
            // Timeout court (5s) pour détecter les lenteurs
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                // Auto-healing : Si c'était en panne et que ça revient, on pourrait remettre en vert
                // Mais généralement on laisse un humain confirmer pour éviter l'effet "clignotant"
                $this->info("{$componentName} is UP.");
            } else {
                $this->triggerOutage($component, "Code HTTP " . $response->status());
            }
        } catch (\Exception $e) {
            $this->triggerOutage($component, "Timeout / Connection Refused");
        }
    }

    protected function triggerOutage(SystemComponents $component, string $reason)
    {
        // On ne crée pas de doublon si c'est déjà en panne
        if ($component->status === ComponentStatusEnum::MAJOR_OUTAGE) return;

        $component->update(['status' => ComponentStatusEnum::MAJOR_OUTAGE]);

        $this->error("{$component->name} is DOWN: {$reason}");

        // Ici, on pourrait créer un Incident automatiquement dans la BDD
        // \App\Models\Status\Incident::create([...]);
    }
}
