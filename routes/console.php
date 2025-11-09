<?php

use App\Enum\Customer\CustomerServiceStatusEnum;
use App\Models\Customer\CustomerService;
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

        if($response['status'] !== 'ok'){
            $service->update(['status' => CustomerServiceStatusEnum::ERROR]);
        }

        if ($response['status'] !== 'ok') {
            $service->customer->user->notify(new ServiceError($service));
        }
    }
})
->everyFiveMinutes()
->description("Vérifie l'état de chaque service Batistack et notifie l'utilisateur si nécessaire.");




