<?php

namespace App\Jobs\Commerce;

use App\Enum\Commerce\OrderStatusEnum;
use App\Jobs\Service\CreateService;
use App\Models\Commerce\Order;
use App\Models\User;
use App\Notifications\Commerce\CreateSubscription;
use App\Services\Stripe\StripeService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class CreateInvoiceByOrder implements ShouldQueue
{
    use Queueable;
    private $stripe;

    /**
     * Create a new job instance.
     */
    public function __construct(private Order $order)
    {
        $this->stripe = new StripeService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $subscription = $this->createSubscriptionOnStripe();

            // Vérifier si la souscription est valide avant de notifier le client
            if ($subscription && in_array($subscription->status, ['active', 'trialing'])) {
                $this->notifyCustomer($subscription);
            } else {
                // Log et notifier l'administrateur si la souscription n'est pas active/trialing
                logger()->error("La souscription Stripe pour la commande {$this->order->id} n'est pas active ou en période d'essai. Statut: {$subscription->status}");
                Notification::make()
                    ->title('Erreur de souscription Stripe')
                    ->body("La souscription pour la commande #{$this->order->id} n'a pas pu être activée. Statut: {$subscription->status}")
                    ->danger()
                    ->sendToDatabase(User::find(1)); // Envoyer à l'utilisateur connecté (admin)
            }
        } catch (Throwable $th) {
            logger()->error("Erreur lors du traitement de la commande {$this->order->id}: " . $th->getMessage());
            Notification::make()
                ->title('Erreur critique de commande')
                ->body("Une erreur inattendue est survenue lors du traitement de la commande #{$this->order->id}. " . $th->getMessage())
                ->danger()
                ->sendToDatabase(User::find(1)); // Envoyer à l'utilisateur connecté (admin)
            throw $th;
        }
    }

    public function createSubscriptionOnStripe()
    {
        $items = collect();

        foreach ($this->order->items as $item) {
            // Vérifier si stripe_price_id est non vide dans productPrice
            if (!empty($item->productPrice->stripe_price_id) && !is_null($item->productPrice->stripe_price_id)) {
                $items->push([
                    "price" => trim($item->productPrice->stripe_price_id), // Accès via productPrice
                    "quantity" => $item->quantity,
                ]);
            } else {
                // Journaliser une erreur si un produit n'a pas de stripe_price_id valide
                logger()->warning("Produit avec ID {$item->product->id} (ProductPrice ID: {$item->productPrice->id}) n'a pas de stripe_price_id valide. Ignoré pour la souscription Stripe.");
            }
        }

        // Vérifier si la collection items est vide avant l'appel à Stripe
        if ($items->isEmpty()) {
            $errorMessage = "Aucun produit valide avec stripe_price_id trouvé pour la commande {$this->order->id}";
            logger()->error($errorMessage);

            // Notifier l'administrateur
            Notification::make()
                ->title('Erreur de configuration produit')
                ->body("La commande #{$this->order->id} ne contient aucun produit avec un stripe_price_id valide.")
                ->danger()
                ->sendToDatabase(User::find(1));

            throw new \Exception($errorMessage);
        }

        try {
            $subscription = $this->stripe->client->subscriptions->create([
                "customer" => $this->order->customer->stripe_customer_id,
                "items" => $items->toArray(),
            ]);

            // Simplification de la condition: vérifier si le statut est 'active' ou 'trialing'
            if (in_array($subscription->status, ['active', 'trialing'])) {
                // Met à jour l'état de la commande
                $this->order->logs()->create(['libelle' => "Commande accepté et souscription pris en compte"]);
                $invoiceStripe = app(StripeService::class)->client->invoices->retrieve($subscription->latest_invoice);
                $this->order->update([
                    'status' => OrderStatusEnum::CONFIRMED,
                    'confirmed_at' => now(),
                    'stripe_invoice_id' => $subscription->latest_invoice,
                    'stripe_payment_intent_id' => $invoiceStripe->payment_intent
                ]);

                $this->order->payments()->create([
                    'order_id' => $this->order->id,
                    'payment_method' => 'stripe',
                    'status' => 'completed',
                    'amount' => $this->order->total_amount,
                    'processed_at' => now(),
                ]);
                $this->order->logs()->create(['libelle' => "Paiement Effectuer"]);
                dispatch(new CreateService($this->order, $subscription->id));
                return $subscription;
            } else {
                logger()->error("Erreur lors de la création de la souscription sur stripe pour la commande {$this->order->id}. Statut: {$subscription->status}");
                return $subscription; // Retourne la souscription même si elle n'est pas active/trialing
            }
        } catch (Throwable $th) {
            logger()->error("Erreur Stripe pour la commande {$this->order->id}: " . $th->getMessage());
            throw $th;
        }
    }

    public function notifyCustomer($subscription)
    {
        // Notification Filament pour l'utilisateur (base de données)
        Notification::make()
            ->title('Nouvelle souscription')
            ->body('Votre souscription a été créée avec succès.')
            ->success()
            ->sendToDatabase($this->order->customer->user);

        // Notification Laravel (email) pour l'utilisateur
        $this->order->customer->user->notify(new CreateSubscription(
            order: $this->order,
            subscription: $subscription
        ));
    }
}
