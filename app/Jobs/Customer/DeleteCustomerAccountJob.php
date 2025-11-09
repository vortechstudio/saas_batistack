<?php

namespace App\Jobs\Customer;

use App\Models\Customer\Customer;
use App\Services\Stripe\CustomerService as StripeCustomerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteCustomerAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Customer $customer,
        private object $deletionRequest
    ) {}

    public function handle(): void
    {
        try {
            if($this->customer->status !== 'pending_deletion') {
                Log::info('Compte client actif, suppression annulée', [
                    'customer_id' => $this->customer->id,
                    'customer_code' => $this->customer->code_client
                ]);
                return;
            }

            Log::info('Début de la suppression du compte client', [
                'customer_id' => $this->customer->id,
                'customer_code' => $this->customer->code_client
            ]);
            // 1) Archiver (transaction courte)
            DB::transaction(function () {
                $this->archiveEssentialData();
            });
            // 2) Opérations externes (pas de transaction DB)
            $this->deleteStripeData();
            $this->deleteStorageData();
            $this->deleteExternalServices();
            // 3) Nettoyage/anonymisation (transaction courte)
            DB::transaction(function () {
                $this->anonymizeRetainedData();
                $this->deleteDatabaseRecords();
            });

            Log::info('Suppression du compte client terminée avec succès', [
                'customer_id' => $this->customer->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la suppression du compte client', [
                'customer_id' => $this->customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Programmer une nouvelle tentative
            $this->release(3600); // Réessayer dans 1 heure
        }
    }

    private function archiveEssentialData(): void
    {
        // Archiver les données nécessaires pour la conformité légale
        $archiveData = [
            'customer_id' => $this->customer->id,
            'customer_code' => $this->customer->code_client,
            'deletion_date' => now(),
            'deletion_reason' => $this->deletionRequest->reason,
            'financial_summary' => $this->getFinancialSummary(),
            'legal_retention_period' => now()->addYears(10), // Durée de conservation légale
        ];

        // Stocker dans une table d'archive ou un système externe
        DB::table('customer_deletion_archives')->insert($archiveData);
    }

    private function deleteStripeData(): void
    {
        if ($this->customer->stripe_customer_id) {
            try {
                $stripeService = app(StripeCustomerService::class);

                // Annuler tous les abonnements actifs
                $subscriptions = $stripeService->client->subscriptions->all([
                    'customer' => $this->customer->stripe_customer_id,
                    'status' => 'active'
                ]);

                foreach ($subscriptions->data as $subscription) {
                    $stripeService->client->subscriptions->cancel($subscription->id);
                }

                // Supprimer le client Stripe
                $stripeService->client->customers->delete($this->customer->stripe_customer_id);

            } catch (\Exception $e) {
                Log::warning('Erreur lors de la suppression des données Stripe', [
                    'customer_id' => $this->customer->id,
                    'stripe_customer_id' => $this->customer->stripe_customer_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function deleteStorageData(): void
    {
        // Supprimer les fichiers de stockage du client
        $storage = $this->customer->storage;
        if ($storage && $storage->bucket) {
            try {
                Storage::disk('s3')->deleteDirectory($storage->bucket);
            } catch (\Exception $e) {
                Log::warning('Erreur lors de la suppression du stockage', [
                    'customer_id' => $this->customer->id,
                    'bucket' => $storage->bucket,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function deleteExternalServices(): void
    {
        // Supprimer les services externes (domaines, bases de données, etc.)
        foreach ($this->customer->services as $service) {
            try {
                // Ici vous pouvez appeler les services AaPanel, etc.
                // $domainService = app(DomainService::class);
                // $domainService->delete($service->domain);

                Log::info('Service externe supprimé', [
                    'service_id' => $service->id,
                    'service_code' => $service->service_code
                ]);
            } catch (\Exception $e) {
                Log::warning('Erreur lors de la suppression du service externe', [
                    'service_id' => $service->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function anonymizeRetainedData(): void
    {
        // Anonymiser les données qui doivent être conservées
        $this->customer->user->update([
            'name' => 'Utilisateur supprimé',
            'email' => 'deleted_' . $this->customer->id . '@deleted.local',
            'email_verified_at' => null,
            'password' => null,
            'remember_token' => null,
        ]);

        $this->customer->update([
            'entreprise' => 'Entreprise supprimée',
            'telephone' => null,
            'adresse' => 'Adresse supprimée',
            'ville' => 'Ville supprimée',
            'code_postal' => null,
            'stripe_customer_id' => null,
        ]);
    }

    private function deleteDatabaseRecords(): void
    {
        // Supprimer dans l'ordre pour respecter les contraintes de clés étrangères

        // Supprimer les restrictions IP
        $this->customer->restrictedIps()->delete();

        // Supprimer les méthodes de paiement
        $this->customer->paymentMethods()->delete();

        // Supprimer les étapes de service
        foreach ($this->customer->services as $service) {
            $service->steps()->delete();
        }

        // Supprimer les services
        $this->customer->services()->delete();

        // Supprimer le stockage
        if ($this->customer->storage) {
            $this->customer->storage->delete();
        }

        // Supprimer les éléments de commande
        foreach ($this->customer->orders as $order) {
            $order->items()->delete();
            $order->payments()->delete();
            $order->logs()->delete();
        }

        // Supprimer les commandes
        $this->customer->orders()->delete();

        // Supprimer le client
        $this->customer->delete();

        // Supprimer l'utilisateur
        $this->customer->user->delete();
    }

    private function getFinancialSummary(): array
    {
        return [
            'total_orders' => $this->customer->orders()->count(),
            'total_amount' => $this->customer->orders()->sum('total_amount'),
            'last_payment_date' => $this->customer->orders()
                ->whereNotNull('paid_at')
                ->latest('paid_at')
                ->value('paid_at'),
        ];
    }
}
