<?php

namespace App\Notifications\Service;

use App\Models\Customer\CustomerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Slack\SlackMessage;
use Illuminate\Notifications\Notification;

class ServiceError extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public CustomerService $service)
    {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'slack'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Accès au service {$this->service->service->name} en erreur")
            ->greeting("Bonjour {$this->service->customer->user->name},")
            ->line("Le service {$this->service->service->name} a rencontré une erreur.")
            ->line("Veuillez contacter le support technique pour plus d'informations.")
            ->action('Contacter le support technique', url('/contact'))
            ->line("Merci d'avoir utilisé notre application.");
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->text("Le service {$this->service->service->name} a rencontré une erreur et actuellement Hors Ligne");
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "Service {$this->service->service->name} en erreur",
            'body' => "Le service {$this->service->service->name} a rencontré une erreur.",
            'icon' => 'heroicon-o-exclamation-circle',
            'iconColor' => 'danger',
            'service_id' => $this->service->id,
            'domain' => $this->service->domain
        ];
    }

    

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'service_id' => $this->service->id,
            'domain' => $this->service->domain,
            'product_name' => $this->service->product->name
        ];
    }
}
