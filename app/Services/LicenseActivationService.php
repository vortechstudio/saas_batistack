<?php

namespace App\Services;

use App\Models\License;
use App\Models\Customer;
use App\Models\User;
use App\Enums\LicenseStatus;
use App\Enums\CustomerStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LicenseActivationService
{
    /**
     * Pipeline d'activation sécurisé
     */
    public function activate(int $licenseId, User $user): array
    {
        return DB::transaction(function () use ($licenseId, $user) {
            // Étape 1: Validation des entrées
            $this->validateInput($licenseId, $user);

            // Étape 2: Récupération sécurisée
            $license = $this->findLicenseSecurely($licenseId, $user);

            // Étape 3: Vérifications métier
            $this->validateBusinessRules($license);

            // Étape 4: Vérifications de sécurité
            $this->validateSecurityConstraints($license, $user);

            // Étape 5: Activation
            $this->performActivation($license, $user);

            // Étape 6: Post-activation
            $this->handlePostActivation($license, $user);

            return [
                'success' => true,
                'message' => 'Licence activée avec succès',
                'license' => $license->fresh()
            ];
        });
    }

    /**
     * Étape 1: Validation des entrées
     */
    private function validateInput(int $licenseId, User $user): void
    {
        if ($licenseId <= 0) {
            throw ValidationException::withMessages([
                'license_id' => 'ID de licence invalide'
            ]);
        }

        if (!$user->exists) {
            throw ValidationException::withMessages([
                'user' => 'Utilisateur invalide'
            ]);
        }
    }

    /**
     * Étape 2: Récupération sécurisée
     */
    private function findLicenseSecurely(int $licenseId, User $user): License
    {
        $license = License::with(['customer', 'product', 'modules', 'options'])
            ->find($licenseId);

        if (!$license) {
            throw ValidationException::withMessages([
                'license' => 'Licence introuvable ou accès non autorisé'
            ]);
        }

        return $license;
    }

    /**
     * Étape 3: Vérifications métier
     */
    private function validateBusinessRules(License $license): void
    {
        // Vérifier si déjà active
        if ($license->status === LicenseStatus::ACTIVE) {
            throw ValidationException::withMessages([
                'status' => 'Cette licence est déjà active'
            ]);
        }

        // Vérifier l'expiration
        if ($license->isExpired()) {
            throw ValidationException::withMessages([
                'expiration' => 'Impossible d\'activer une licence expirée'
            ]);
        }

        // Vérifier les statuts bloquants
        $blockedStatuses = [LicenseStatus::CANCELLED];
        if (in_array($license->status, $blockedStatuses)) {
            throw ValidationException::withMessages([
                'status' => 'Impossible d\'activer une licence ' . $license->status->label()
            ]);
        }

        // Vérifier la date de début
        if ($license->starts_at && $license->starts_at->isFuture()) {
            throw ValidationException::withMessages([
                'start_date' => 'Cette licence n\'est pas encore disponible'
            ]);
        }
    }

    /**
     * Étape 4: Vérifications de sécurité
     */
    private function validateSecurityConstraints(License $license, User $user): void
    {
        // Vérifier le statut du client
        if (!$license->customer->isActive()) {
            throw ValidationException::withMessages([
                'customer' => 'Compte client inactif'
            ]);
        }

        // Vérifier les limites d'utilisation
        if ($license->max_users && $license->current_users >= $license->max_users) {
            throw ValidationException::withMessages([
                'capacity' => 'Limite d\'utilisateurs atteinte'
            ]);
        }

        // Rate limiting (optionnel)
        $this->checkRateLimit($user);
    }

    /**
     * Étape 5: Activation
     */
    private function performActivation(License $license, User $user): void
    {
        $license->update([
            'status' => LicenseStatus::ACTIVE,
            'last_used_at' => now(),
            'current_users' => $license->current_users + 1,
        ]);

        Log::info('Licence activée', [
            'license_id' => $license->id,
            'user_id' => $user->id,
            'customer_id' => $license->customer_id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Étape 6: Post-activation
     */
    private function handlePostActivation(License $license, User $user): void
    {
        // Audit trail
        activity()
            ->performedOn($license)
            ->causedBy($user)
            ->withProperties([
                'old_status' => $license->getOriginal('status'),
                'new_status' => $license->status,
                'ip' => request()->ip(),
            ])
            ->log('Licence activée par le client');

        // Notifications (optionnel)
        // $this->sendActivationNotification($license, $user);

        // Synchronisation externe (optionnel)
        // dispatch(new SyncEntityJob('crm', 'update', $license));
    }

    /**
     * Rate limiting
     */
    private function checkRateLimit(User $user): void
    {
        $key = 'license_activation:' . $user->id;
        $attempts = cache()->get($key, 0);

        if ($attempts >= 5) { // Max 5 tentatives par heure
            throw ValidationException::withMessages([
                'rate_limit' => 'Trop de tentatives d\'activation. Réessayez plus tard.'
            ]);
        }

        cache()->put($key, $attempts + 1, now()->addHour());
    }
}
