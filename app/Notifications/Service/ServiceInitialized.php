<?php

namespace App\Notifications\Service;

use App\Models\Customer\CustomerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceInitialized extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public CustomerService $service,
        public array $installationDetails = []
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre service Batistack est prêt !')
            ->markdown('mail.service.initialized', [
                'service' => $this->service,
                'customer' => $this->service->customer,
                'product' => $this->service->product,
                'installationDetails' => $this->installationDetails
            ]);
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Service Batistack initialisé',
            'body' => 'Votre service Batistack est maintenant prêt à être utilisé.',
            'icon' => 'heroicon-o-check-circle',
            'iconColor' => 'success',
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
