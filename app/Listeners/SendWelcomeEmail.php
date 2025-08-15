<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        $user = $event->user;

        // Vérifier que l'utilisateur existe et a un email vérifié
        if ($user && $user->hasVerifiedEmail()) {
            // Envoyer l'email de bienvenue
            $user->notify(new WelcomeNotification($user));

            // Log pour le suivi
            Log::info('Email de bienvenue envoyé', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name
            ]);
        }
    }
}
