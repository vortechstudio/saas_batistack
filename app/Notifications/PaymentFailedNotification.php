<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Invoice $invoice;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $customerName = $notifiable->name ?? 'Client';

        return (new MailMessage)
            ->error()
            ->subject('Échec de paiement de votre facture #' . $this->invoice->invoice_number)
            ->greeting('Bonjour ' . $customerName . ',')
            ->line('Nous vous informons que le paiement de votre facture #' . $this->invoice->invoice_number . ' d\'un montant de ' . $this->invoice->getFormattedTotalAttribute() . ' a échoué.')
            ->line('Veuillez mettre à jour vos informations de paiement ou contacter notre support pour résoudre ce problème.')
            ->action('Voir la facture', url('/client/invoices/' . $this->invoice->id))
            ->line('Merci de votre compréhension.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'total_amount' => $this->invoice->total_amount,
            'currency' => $this->invoice->currency,
            'message' => 'Payment failed for invoice #' . $this->invoice->invoice_number,
        ];
    }
}
