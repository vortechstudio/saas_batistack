<?php

namespace App\Jobs\Service\Install;

use App\Models\Customer\CustomerService;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotifyClientByMail implements ShouldQueue
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
            // Mise à jour du statut de l'étape

            Log::info('Début de l\'envoi de notification email au client', [
                'service_id' => $this->service->id,
                'customer_id' => $this->service->customer->id,
                'customer_email' => $this->service->customer->user->email
            ]);

            // Préparer les détails d'installation pour l'email
            $installationDetails = $this->prepareInstallationDetails();

            // Envoyer la notification Filament (base de données)
            $this->sendDatabaseNotification();

            // Envoyer la notification email
            $this->sendEmailNotification($installationDetails);

            dispatch(new PassServiceToOk($this->service))->onQueue('installApp')->delay(now()->addSeconds(10));

            Log::info('Notification email envoyée avec succès', [
                'service_id' => $this->service->id,
                'customer_email' => $this->service->customer->user->email
            ]);

        } catch (\Exception $e) {
            $this->service->update([
                'status' => 'error',
            ]);
            Notification::make()
                ->danger()
                ->title("Installation d'un service en erreur !")
                ->body($e->getMessage())
                ->sendToDatabase(User::where('email', 'admin@'.config('batistack.domain'))->first());

            Log::error('Erreur lors de l\'envoi de la notification email', [
                'service_id' => $this->service->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Préparer les détails d'installation pour l'email
     */
    private function prepareInstallationDetails(): array
    {
        $details = [
            'installation_completed' => true,
            'modules_activated' => [],
            'features' => [],
            'installation_date' => now(),
            'domain_configured' => !empty($this->service->domain)
        ];

        // Récupérer les fonctionnalités du produit
        if ($this->service->product->features->isNotEmpty()) {
            $details['features'] = $this->service->product->features->map(function ($feature) {
                return [
                    'name' => $feature->name,
                    'description' => $feature->description
                ];
            })->toArray();

            $details['modules_activated'] = $this->service->product->features->pluck('name')->toArray();
        }

        // Ajouter des informations depuis les étapes précédentes
        $completedSteps = $this->service->steps()->where('done', true)->get();
        $details['completed_steps'] = $completedSteps->pluck('step')->toArray();

        // Informations sur les limites du produit
        try {
            $stripeProduct = $this->service->product->getInfoProductStripe();
            if (isset($stripeProduct->metadata)) {
                $details['limits'] = [];
                foreach ($stripeProduct->metadata as $key => $value) {
                    if (str_ends_with($key, '_limit')) {
                        $details['limits'][$key] = $value;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Impossible de récupérer les métadonnées Stripe pour l\'email', [
                'error' => $e->getMessage()
            ]);
        }

        return $details;
    }

    /**
     * Envoyer la notification en base de données (Filament)
     */
    private function sendDatabaseNotification(): void
    {
        \Filament\Notifications\Notification::make()
            ->title('Service Batistack initialisé')
            ->body('Votre service Batistack est maintenant prêt à être utilisé sur le domaine: ' . $this->service->domain)
            ->success()
            ->icon('heroicon-o-check-circle')
            ->actions([
                Action::make('view_service')
                    ->label('Voir le service')
                    ->url(route('client.dashboard'))
                    ->button()
            ])
            ->sendToDatabase($this->service->customer->user);
    }

    /**
     * Envoyer la notification email
     */
    private function sendEmailNotification(array $installationDetails): void
    {
        // Utiliser la classe de notification Laravel
        $this->service->customer->user->notify(
            new \App\Notifications\Service\ServiceInitialized(
                service: $this->service,
                installationDetails: $installationDetails
            )
        );
    }
}
