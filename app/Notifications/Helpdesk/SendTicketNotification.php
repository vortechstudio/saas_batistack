<?php

namespace App\Notifications\Helpdesk;

use App\Models\Helpdesk\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TicketMessage $ticketMessage,
    )
    {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        if ($this->ticketMessage->user_id !== $this->ticketMessage->ticket->user_id) {
            $url = "#";
            return (new MailMessage)
                ->subject("Nouvelle réponse au ticket #{$this->ticketMessage->ticket->id}")
                ->greeting("Bonjour ".$this->ticketMessage->user->fullname)
                ->line("Une réponse à été apporter à votre ticket #{$this->ticketMessage->ticket->id}, veuillez suivre le lien suivant pour en prendre connaissance.")
                ->action('Voir la réponse', $url)
                ->salutation("Cordialement,");
        }
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
