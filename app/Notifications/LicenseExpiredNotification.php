<?php

namespace App\Notifications;

use App\Models\License;
use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LicenseExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public License $license) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("🚨 Licence expirée - {$this->license->product->name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("⚠️ La licence pour le produit {$this->license->product->name} a expiré.")
            ->line("Client: {$this->license->customer->company_name}")
            ->line("Date d'expiration: {$this->license->expires_at->format('d/m/Y')}")
            ->line("Jours écoulés: {$this->license->expires_at->diffInDays()} jour(s)")
            ->action('Renouveler maintenant', url("/admin/licenses/{$this->license->id}"))
            ->line('Action immédiate requise pour éviter l\'interruption de service.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => NotificationType::LICENSE_EXPIRED,
            'title' => 'Licence expirée',
            'message' => "La licence {$this->license->product->name} a expiré",
            'license_id' => $this->license->id,
            'customer_name' => $this->license->customer->company_name,
            'product_name' => $this->license->product->name,
            'expires_at' => $this->license->expires_at,
            'days_expired' => $this->license->expires_at->diffInDays(),
            'action_url' => "/admin/licenses/{$this->license->id}",
            'priority' => 'high',
        ];
    }
}
