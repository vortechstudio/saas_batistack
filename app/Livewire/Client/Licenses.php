<?php

namespace App\Livewire\Client;

use App\Models\Customer;
use App\Models\License;
use App\Models\Module;
use App\Enums\LicenseStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Services\LicenseActivationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

#[Layout('components.layouts.app')]
#[Title('Mes Licences')]
class Licenses extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $selectedLicense = null;
    public $showLicenseDetails = false;

    // Nouvelles propriétés pour l'accès au service
    public $showServiceAccess = false;
    public $selectedServiceLicense = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    #[Computed]
    public function customer()
    {
        return Auth::user()->customer;
    }

    #[Computed]
    public function licenses()
    {
        if (!$this->customer) {
            return collect();
        }

        $query = $this->customer->licenses()
            ->with(['product', 'modules', 'options'])
            ->orderBy('created_at', 'desc');

        // Filtrage par recherche
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('license_key', 'like', '%' . $this->search . '%')
                  ->orWhereHas('product', function ($productQuery) {
                      $productQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Filtrage par statut
        if ($this->statusFilter !== 'all') {
            switch ($this->statusFilter) {
                case 'active':
                    $query->where('status', LicenseStatus::ACTIVE);
                    break;
                case 'expired':
                    $query->where('expires_at', '<', now());
                    break;
                case 'expiring':
                    $query->where('expires_at', '>', now())
                          ->where('expires_at', '<=', now()->addDays(30));
                    break;
            }
        }

        return $query->paginate(10);
    }

    public function activateLicense($licenseId)
    {
        try {
            $activationService = app(LicenseActivationService::class);
            $result = $activationService->activate($licenseId, Auth::user());

            session()->flash('success', $result['message']);
            $this->resetPage(); // Rafraîchir la pagination

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $message = collect($errors)->flatten()->first();
            session()->flash('error', $message);

        } catch (\Exception $e) {
            Log::error('Erreur activation licence', [
                'license_id' => $licenseId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            session()->flash('error', 'Une erreur technique est survenue.');
        }
    }

    public function downloadLicense($licenseId)
    {
        $license = License::with(['customer', 'product', 'activeModules', 'activeOptions'])
            ->find($licenseId);

        if (!$license) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Licence introuvable.'
            ]);
            return;
        }

        if ($license->customer_id !== $this->customer->id) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Accès non autorisé à cette licence.'
            ]);
            return;
        }

        // Redirection vers le contrôleur pour générer et télécharger le PDF
        return redirect()->route('client.license.certificate', $license);
    }

    public function showDetails($licenseId)
    {
        $this->selectedLicense = License::with(['product', 'modules', 'options', 'customer'])
            ->where('id', $licenseId)
            ->where('customer_id', $this->customer->id)
            ->first();

        $this->showLicenseDetails = true;
    }

    public function closeDetails()
    {
        $this->showLicenseDetails = false;
        $this->selectedLicense = null;
    }

    /**
     * Affiche le modal d'accès au service
     */
    public function showServiceAccess($licenseId)
    {
        $license = License::with(['product', 'modules'])->find($licenseId);

        if (!$license || $license->customer_id !== $this->customer->id) {
            session()->flash('error', 'Licence non trouvée.');
            return;
        }

        if ($license->status !== LicenseStatus::ACTIVE) {
            session()->flash('error', 'Cette licence n\'est pas active.');
            return;
        }

        if (!$license->hasDomain()) {
            session()->flash('error', 'Le domaine de cette licence n\'est pas encore configuré.');
            return;
        }

        $this->selectedServiceLicense = $license;
        $this->showServiceAccess = true;
    }

    /**
     * Ferme le modal d'accès au service
     */
    public function closeServiceAccess()
    {
        $this->showServiceAccess = false;
        $this->selectedServiceLicense = null;
    }

    /**
     * Accès direct au service
     */
    public function accessServiceDirect($licenseId)
    {
        $license = License::find($licenseId);

        if (!$license || $license->customer_id !== $this->customer->id) {
            session()->flash('error', 'Licence non trouvée.');
            return;
        }

        if ($license->status !== LicenseStatus::ACTIVE) {
            session()->flash('error', 'Cette licence n\'est pas active.');
            return;
        }

        if (!$license->hasDomain()) {
            session()->flash('error', 'Le domaine de cette licence n\'est pas encore configuré.');
            return;
        }

        // Mettre à jour la dernière utilisation
        $license->updateLastUsed();

        // Rediriger vers le service
        return redirect()->away($license->getServiceUrl());
    }

    public function toggleModule($licenseId, $moduleId)
    {
        $license = License::find($licenseId);
        if ($license && $license->customer_id === $this->customer->id) {
            $module = $license->modules()->where('module_id', $moduleId)->first();

            if ($module) {
                $isEnabled = $module->pivot->enabled;
                $license->modules()->updateExistingPivot($moduleId, [
                    'enabled' => !$isEnabled,
                    'updated_at' => now(),
                ]);

                session()->flash('success', 'Module ' . (!$isEnabled ? 'activé' : 'désactivé') . ' avec succès.');
            }
        }
    }

    public function render()
    {
        return view('livewire.client.licenses');
    }
}
