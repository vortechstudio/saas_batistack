<?php

use App\Enum\Customer\CustomerServiceStatusEnum;
use App\Enum\Helpdesk\TicketStatusEnum;
use App\Models\Customer\CustomerService;
use App\Models\Helpdesk\Ticket;
use App\Notifications\Service\ServiceError;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    foreach (CustomerService::all() as $service) {
        $request = Http::withoutVerifying()->get("{$service->domain}/api/status");

        $response = $request->json();

        if ($response['status'] !== 'ok') {
            $service->update(['status' => CustomerServiceStatusEnum::ERROR]);
        }

        if ($response['status'] !== 'ok') {
            $service->customer->user->notify(new ServiceError($service));
        }
    }
})
    ->everyFiveMinutes()
    ->description("Vérifie l'état de chaque service Batistack et notifie l'utilisateur si nécessaire.");

Schedule::call(function () {
    $daysBeforeClose = 7;
    $dateLimit = now()->subDays($daysBeforeClose);

    $tickets = Ticket::where('status', TicketStatusEnum::RESOLVED)
        ->where('updated_at', '<', $dateLimit) // Si aucune activité depuis X jours
        ->get();

    $count = 0;

    foreach ($tickets as $ticket) {
        $ticket->update([
            'status' => TicketStatusEnum::CLOSED,
            'closed_at' => now(),
        ]);

        // Optionnel : Envoyer un mail final "Votre ticket a été fermé automatiquement"
        $count++;
    }

    $this->info("{$count} tickets ont été fermés automatiquement.");
})
    ->daily()
    ->description("Fermeture des tickets automatiquement après 10 jours d'inactivité");




