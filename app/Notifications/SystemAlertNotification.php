<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $level = 'warning',
        public ?string $actionUrl = null
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $emoji = match($this->level) {
            'critical' => '🚨',
            'error' => '❌',
            'warning' => '⚠️',
            'info' => 'ℹ️',
            default => '🔔',
        };

        $mail = (new MailMessage)
            ->subject("{$emoji} Alerte système - {$this->title}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line($this->message);

        if ($this->actionUrl) {
            $mail->action('Voir les détails', $this->actionUrl);
        }

        return $mail->line('Merci de vérifier le système.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => NotificationType::SYSTEM_ALERT,
            'title' => $this->title,
            'message' => $this->message,
            'level' => $this->level,
            'action_url' => $this->actionUrl,
            'priority' => match($this->level) {
                'critical' => 'critical',
                'error' => 'high',
                'warning' => 'medium',
                default => 'low',
            },
        ];
    }
}
