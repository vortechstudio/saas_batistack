<?php

namespace App\Notifications\Commerce;

use App\Models\Commerce\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\RocketChat\RocketChatMessage;
use NotificationChannels\RocketChat\RocketChatWebhookChannel;

class CreateSubscription extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Order $order, public $subscription)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', RocketChatWebhookChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[".config('app.name')."] - Commande N°".$this->order->order_number)
            ->markdown('mail.commerce.create_subscription', [
                'order' => $this->order,
                'subscription' => $this->subscription
            ]);
    }

    public function toRocketChat(object $notifiable): RocketChatMessage
    {
        $object = "[".config('app.env')."] [".config('app.name')."] Commande N°".$this->order->order_number;
        $content = "Nouvelle commande N°".$this->order->order_number." a été créée.";
        
        return RocketChatMessage::create($object)
            ->to(config('services.rocketchat.channel'))
            ->content($content);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
