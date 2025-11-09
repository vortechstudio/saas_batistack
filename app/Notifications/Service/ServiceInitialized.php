<?php

namespace App\Notifications\Service;

use App\Models\Customer\CustomerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Slack\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;

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
        return ['mail', 'database', 'slack'];
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

    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->text("Le service {$this->service->service_code} {$this->service->domain} est maintenant prêt à être utilisé.")
            ->headerBlock("Service {$this->service->service_code} initialisé")
            ->sectionBlock(function (SectionBlock $block) {
                $block->text("Service : {$this->service->service_code}");
                $block->field("Domaine : {$this->service->domain}");
                $block->field("Produit : {$this->service->product->name}");
                $block->field("Etat : {$this->service->status->value}");
            });
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
