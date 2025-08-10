<?php

namespace App\Notifications;

use App\Models\Customer;
use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Customer $customer,
        public float $amount,
        public int $daysOverdue
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("💰 Paiement en retard - {$this->customer->company_name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Un paiement est en retard pour le client {$this->customer->company_name}.")
            ->line("Montant dû: {$this->amount} €")
            ->line("Retard: {$this->daysOverdue} jour(s)")
            ->action('Voir le client', url("/admin/customers/{$this->customer->id}"))
            ->line('Merci de contacter le client pour régulariser la situation.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => NotificationType::PAYMENT_OVERDUE,
            'title' => 'Paiement en retard',
            'message' => "Paiement en retard de {$this->amount}€ pour {$this->customer->company_name}",
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->company_name,
            'amount' => $this->amount,
            'days_overdue' => $this->daysOverdue,
            'action_url' => "/admin/customers/{$this->customer->id}",
            'priority' => 'high',
        ];
    }
}
