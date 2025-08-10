<?php

namespace App\Notifications;

use App\Models\Customer;
use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCustomerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Customer $customer) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("🎉 Nouveau client - {$this->customer->company_name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Un nouveau client vient de s'inscrire !")
            ->line("Entreprise: {$this->customer->company_name}")
            ->line("Contact: {$this->customer->contact_name}")
            ->line("Email: {$this->customer->email}")
            ->line("Téléphone: {$this->customer->phone}")
            ->action('Voir le client', url("/admin/customers/{$this->customer->id}"))
            ->line('N\'hésitez pas à prendre contact pour l\'accueillir.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => NotificationType::NEW_CUSTOMER,
            'title' => 'Nouveau client',
            'message' => "Nouveau client inscrit : {$this->customer->company_name}",
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->company_name,
            'customer_email' => $this->customer->email,
            'contact_name' => $this->customer->contact_name,
            'action_url' => "/admin/customers/{$this->customer->id}",
            'priority' => 'low',
        ];
    }
}
