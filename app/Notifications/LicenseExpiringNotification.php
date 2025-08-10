<?php

namespace App\Notifications;

use App\Models\License;
use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LicenseExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public License $license,
        public int $daysUntilExpiry
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Licence bientôt expirée - {$this->license->product->name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("La licence pour le produit {$this->license->product->name} expire dans {$this->daysUntilExpiry} jour(s).")
            ->line("Client: {$this->license->customer->company_name}")
            ->line("Date d'expiration: {$this->license->expires_at->format('d/m/Y')}")
            ->action('Voir la licence', url("/admin/licenses/{$this->license->id}"))
            ->line('Merci de prendre les mesures nécessaires pour le renouvellement.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => NotificationType::LICENSE_EXPIRING,
            'title' => 'Licence bientôt expirée',
            'message' => "La licence {$this->license->product->name} expire dans {$this->daysUntilExpiry} jour(s)",
            'license_id' => $this->license->id,
            'customer_name' => $this->license->customer->company_name,
            'product_name' => $this->license->product->name,
            'expires_at' => $this->license->expires_at,
            'days_until_expiry' => $this->daysUntilExpiry,
            'action_url' => "/admin/licenses/{$this->license->id}",
        ];
    }
}
