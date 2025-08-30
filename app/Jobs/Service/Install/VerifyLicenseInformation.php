<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VerifyLicenseInformation implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected CustomerService $service)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // 1. Vérification des informations de base du service
            $serviceValidation = $this->validateServiceInformation();

            if (!$serviceValidation['valid']) {
                throw new \Exception("Informations de service invalides: " . $serviceValidation['error']);
            }

            // 2. Vérification de la cohérence avec Stripe
            $stripeValidation = $this->validateStripeSubscription();

            if (!$stripeValidation['valid']) {
                throw new \Exception("Incohérence avec l'abonnement Stripe: " . $stripeValidation['error']);
            }

            // 3. Vérification des features du produit
            $featuresValidation = $this->validateProductFeatures();

            if (!$featuresValidation['valid']) {
                throw new \Exception("Problème avec les fonctionnalités du produit: " . $featuresValidation['error']);
            }

            // 4. Vérification des dates de licence
            $datesValidation = $this->validateLicenseDates();

            if (!$datesValidation['valid']) {
                throw new \Exception("Dates de licence invalides: " . $datesValidation['error']);
            }

            // 5. Vérification du statut de paiement
            $paymentValidation = $this->validatePaymentStatus();

            if (!$paymentValidation['valid']) {
                Log::warning("Problème de paiement détecté", $paymentValidation);
                // Ne pas faire échouer pour les problèmes de paiement, juste logger
            }

            // Compilation des résultats de validation
            $validationResults = [
                'service' => $serviceValidation,
                'stripe' => $stripeValidation,
                'features' => $featuresValidation,
                'dates' => $datesValidation,
                'payment' => $paymentValidation
            ];

            // Vérification des informations de licence réussie
            $this->service->steps()->where('step', 'Vérification des informations relatives à la license')->first()->update([
                'done' => true,
                'comment' => 'Licence valide. Produit: ' . $this->service->product->name . ', Expiration: ' . $this->service->expirationDate->format('d/m/Y')
            ]);

            dispatch(new ActivateLicenseModule($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));

            Log::info("Informations de licence vérifiées avec succès", [
                'service_id' => $this->service->id,
                'service_code' => $this->service->service_code,
                'product_name' => $this->service->product->name,
                'validation_results' => $validationResults
            ]);

        } catch (\Exception $e) {
            // Gestion des erreurs
            $this->service->steps()->where('step', 'Vérification des informations relatives à la license')->first()->update([
                'done' => false,
                'comment' => $e->getMessage()
            ]);

            Log::error('Erreur vérification informations licence', [
                'service_id' => $this->service->id,
                'service_code' => $this->service->service_code,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Validation des informations de base du service
     */
    private function validateServiceInformation(): array
    {
        try {
            // Vérifier que le service a toutes les informations requises
            $requiredFields = ['service_code', 'creationDate', 'expirationDate', 'customer_id', 'product_id'];

            foreach ($requiredFields as $field) {
                if (empty($this->service->$field)) {
                    return [
                        'valid' => false,
                        'error' => "Champ requis manquant: $field"
                    ];
                }
            }

            // Vérifier que le service_code est unique
            $duplicateService = \App\Models\Customer\CustomerService::where('service_code', $this->service->service_code)
                ->where('id', '!=', $this->service->id)
                ->first();

            if ($duplicateService) {
                return [
                    'valid' => false,
                    'error' => "Code de service en doublon: {$this->service->service_code}"
                ];
            }

            // Vérifier que le produit existe et est actif
            if (!$this->service->product || !$this->service->product->active) {
                return [
                    'valid' => false,
                    'error' => "Produit inexistant ou inactif"
                ];
            }

            // Vérifier que le client existe
            if (!$this->service->customer) {
                return [
                    'valid' => false,
                    'error' => "Client inexistant"
                ];
            }

            return [
                'valid' => true,
                'service_code' => $this->service->service_code,
                'product_name' => $this->service->product->name,
                'customer_code' => $this->service->customer->code_client
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validation de la cohérence avec l'abonnement Stripe
     */
    private function validateStripeSubscription(): array
    {
        try {
            if (empty($this->service->stripe_subscription_id)) {
                return [
                    'valid' => false,
                    'error' => "ID d'abonnement Stripe manquant"
                ];
            }

            // Récupérer l'abonnement Stripe
            $stripeService = app(\App\Services\Stripe\StripeService::class);
            $subscription = $stripeService->client->subscriptions->retrieve($this->service->stripe_subscription_id);

            // Vérifier le statut de l'abonnement
            $validStatuses = ['active', 'trialing', 'past_due'];
            if (!in_array($subscription->status, $validStatuses)) {
                return [
                    'valid' => false,
                    'error' => "Statut d'abonnement Stripe invalide: {$subscription->status}"
                ];
            }

            // Vérifier que le client Stripe correspond
            if ($subscription->customer !== $this->service->customer->stripe_customer_id) {
                return [
                    'valid' => false,
                    'error' => "Incohérence client entre service et abonnement Stripe"
                ];
            }

            // Vérifier que le produit correspond
            $stripeProductId = null;
            foreach ($subscription->items->data as $item) {
                $productPrice = \App\Models\Product\ProductPrice::where('stripe_price_id', $item->price->id)->first();
                if ($productPrice && $productPrice->product_id === $this->service->product_id) {
                    $stripeProductId = $item->price->product;
                    break;
                }
            }

            if (!$stripeProductId || $stripeProductId !== $this->service->product->stripe_product_id) {
                return [
                    'valid' => false,
                    'error' => "Produit Stripe ne correspond pas au service"
                ];
            }

            return [
                'valid' => true,
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
                'current_period_end' => $subscription->current_period_end
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => "Erreur lors de la vérification Stripe: " . $e->getMessage()
            ];
        }
    }

    /**
     * Validation des fonctionnalités du produit
     */
    private function validateProductFeatures(): array
    {
        try {
            // Récupérer les informations du produit depuis Stripe
            $stripeProduct = $this->service->product->getInfoProductStripe();

            // Vérifier les métadonnées importantes
            $requiredMetadata = ['storage_limit'];
            foreach ($requiredMetadata as $key) {
                if (!isset($stripeProduct->metadata->$key)) {
                    return [
                        'valid' => false,
                        'error' => "Métadonnée manquante dans le produit Stripe: $key"
                    ];
                }
            }

            // Vérifier que les features du produit sont cohérentes
            $productFeatures = $this->service->product->features;

            if ($productFeatures->isEmpty()) {
                Log::warning("Aucune fonctionnalité définie pour le produit", [
                    'product_id' => $this->service->product->id,
                    'product_name' => $this->service->product->name
                ]);
            }

            return [
                'valid' => true,
                'features_count' => $productFeatures->count(),
                'storage_limit' => $stripeProduct->metadata->storage_limit ?? 'non défini',
                'stripe_product_active' => $stripeProduct->active
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => "Erreur lors de la validation des fonctionnalités: " . $e->getMessage()
            ];
        }
    }

    /**
     * Validation des dates de licence
     */
    private function validateLicenseDates(): array
    {
        try {
            $now = now();
            $creationDate = $this->service->creationDate;
            $expirationDate = $this->service->expirationDate;
            $nextBillingDate = $this->service->nextBillingDate;

            // Vérifier que la date de création n'est pas dans le futur
            if ($creationDate->isFuture()) {
                return [
                    'valid' => false,
                    'error' => "Date de création dans le futur: {$creationDate->format('d/m/Y')}"
                ];
            }

            // Vérifier que la date d'expiration est après la date de création
            if ($expirationDate->isBefore($creationDate)) {
                return [
                    'valid' => false,
                    'error' => "Date d'expiration antérieure à la date de création"
                ];
            }

            // Vérifier que la prochaine date de facturation est cohérente
            if ($nextBillingDate->isBefore($creationDate)) {
                return [
                    'valid' => false,
                    'error' => "Date de facturation incohérente"
                ];
            }

            // Calculer le statut basé sur les dates
            $isExpired = $expirationDate->isPast();
            $daysUntilExpiration = $now->diffInDays($expirationDate, false);

            return [
                'valid' => true,
                'is_expired' => $isExpired,
                'days_until_expiration' => $daysUntilExpiration,
                'creation_date' => $creationDate->format('d/m/Y'),
                'expiration_date' => $expirationDate->format('d/m/Y'),
                'next_billing_date' => $nextBillingDate->format('d/m/Y')
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => "Erreur lors de la validation des dates: " . $e->getMessage()
            ];
        }
    }

    /**
     * Validation du statut de paiement
     */
    private function validatePaymentStatus(): array
    {
        try {
            // Vérifier que le client a des moyens de paiement
            $hasPaymentMethods = $this->service->customer->hasPaymentMethods();

            if (!$hasPaymentMethods) {
                return [
                    'valid' => false,
                    'error' => "Aucun moyen de paiement configuré pour le client",
                    'warning' => true // Marquer comme warning plutôt qu'erreur critique
                ];
            }

            // Vérifier le statut du service
            $serviceStatus = $this->service->status;
            $validStatuses = [\App\Enum\Customer\CustomerServiceStatusEnum::OK, \App\Enum\Customer\CustomerServiceStatusEnum::PENDING];

            if (!in_array($serviceStatus, $validStatuses)) {
                return [
                    'valid' => false,
                    'error' => "Statut de service problématique: {$serviceStatus->label()}",
                    'warning' => $serviceStatus === \App\Enum\Customer\CustomerServiceStatusEnum::UNPAID
                ];
            }

            return [
                'valid' => true,
                'service_status' => $serviceStatus->label(),
                'has_payment_methods' => $hasPaymentMethods
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => "Erreur lors de la validation du paiement: " . $e->getMessage()
            ];
        }
    }
}
