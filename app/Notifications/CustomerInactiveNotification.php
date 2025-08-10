<?php

namespace App\Notifications;

use App\Models\Customer;
use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerInactiveNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Customer $customer) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $daysSinceLastActivity = $this->customer->last_activity_at
            ? $this->customer->last_activity_at->diffInDays()
            : 'plus de 90';

        return (new MailMessage)
            ->subject("⚠️ Client inactif - {$this->customer->company_name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Le client {$this->customer->company_name} est inactif depuis {$daysSinceLastActivity} jour(s).")
            ->line("Email: {$this->customer->email}")
            ->line("Téléphone: {$this->customer->phone}")
            ->line("Dernière activité: " . ($this->customer->last_activity_at ? $this->customer->last_activity_at->format('d/m/Y') : 'Inconnue'))
            ->action('Voir le client', url("/admin/customers/{$this->customer->id}"))
            ->line('Il pourrait être nécessaire de contacter ce client pour maintenir la relation commerciale.');
    }

    public function toDatabase($notifiable): array
    {
        $daysSinceLastActivity = $this->customer->last_activity_at
            ? $this->customer->last_activity_at->diffInDays()
            : null;

        return [
            'type' => NotificationType::CUSTOMER_INACTIVE,
            'title' => 'Client inactif',
            'message' => "Le client {$this->customer->company_name} est inactif depuis longtemps",
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->company_name,
            'customer_email' => $this->customer->email,
            'last_activity_at' => $this->customer->last_activity_at,
            'days_since_last_activity' => $daysSinceLastActivity,
            'action_url' => "/admin/customers/{$this->customer->id}",
            'priority' => 'medium',
        ];
    }
}
