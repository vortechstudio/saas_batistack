<?php

namespace App\Notifications\Commerce;

use App\Models\Commerce\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Slack\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\ContextBlock;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Support\Number;
use NotificationChannels\RocketChat\RocketChatMessage;
use NotificationChannels\RocketChat\RocketChatWebhookChannel;
use Illuminate\Support\Str;

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
        return ['mail', 'slack'];
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

    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->text('Nouvelle Commande client N°'.$this->order->order_number.' a été créée.')
            ->headerBlock("Commande N° {$this->order->order_number}")
            ->contextBlock(function (ContextBlock $block) {
                $block->text('Client '.$this->order->customer->code_client);
            })
            ->sectionBlock(function (SectionBlock $block) {
                $block->text('Souscription N°'.$this->subscription->id);                
                $block->text('Statut : '.$this->subscription->status);                
            })
            ->dividerBlock()
            ->sectionBlock(function (SectionBlock $block) {
                $block->text('Produits :');
                foreach ($this->order->items as $item) {
                    $block->text("{$item->quantity} X {$item->product->name} - ".Number::currency($item->price, in: 'EUR', precision: 2));
                }
            })
            ->dividerBlock()
            ->sectionBlock(function (SectionBlock $block) {
                $block->text('Total : '.Number::currency($this->order->total, in: 'EUR', precision: 2));
            });
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
