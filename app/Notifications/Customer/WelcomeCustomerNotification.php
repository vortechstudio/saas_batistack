<?php

namespace App\Notifications\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeCustomerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Bienvenue sur Batistack ! 🚀")
            ->view('mail.customer.welcome-customer', [
                'user' => $notifiable,
                'dashboardUrl' => route('client.dashboard')
            ]);
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
