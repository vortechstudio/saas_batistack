<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Invoice $invoice;
    public ?License $license;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invoice $invoice, ?License $license = null)
    {
        $this->invoice = $invoice;
        $this->license = $license;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $customerName = $notifiable->name ?? 'Client';

        $message = (new MailMessage)
            ->success()
            ->subject('Confirmation de paiement - Facture #' . $this->invoice->invoice_number)
            ->greeting('Bonjour ' . $customerName . ',')
            ->line('Nous vous confirmons que votre paiement de ' . $this->invoice->getFormattedTotalAttribute() . ' pour la facture #' . $this->invoice->invoice_number . ' a été traité avec succès.')
            ->line('Votre commande a été validée et votre licence est maintenant active.');

        if ($this->license) {
            $message->line('**Détails de votre licence :**')
                    ->line('• Clé de licence : ' . $this->license->license_key)
                    ->line('• Domaine : ' . $this->license->domain)
                    ->line('• Expire le : ' . $this->license->expires_at->format('d/m/Y'))
                    ->action('Télécharger votre licence', route('client.licenses.download', $this->license->id));
        }

        return $message->action('Voir ma facture', route('client.invoices.show', $this->invoice->id))
                      ->line('Merci de votre confiance !')
                      ->salutation('L\'équipe BatiStack');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'total_amount' => $this->invoice->total_amount,
            'currency' => $this->invoice->currency,
            'license_id' => $this->license?->id,
            'message' => 'Payment successful for invoice #' . $this->invoice->invoice_number,
        ];
    }
}
