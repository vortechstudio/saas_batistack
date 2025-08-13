<?php

namespace App\Livewire\Client;

use App\Models\Customer;
use App\Models\License;
use App\Models\Module;
use App\Enums\LicenseStatus;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class Dashboard extends Component
{
    public $customer;
    public $licenses;
    public $activeModules;
    public $expiringLicenses;
    public $unpaidInvoices;
    public $stats = [];

    // Nouvelles propriétés pour l'accès au service
    public $showServiceAccess = false;
    public $selectedServiceLicense = null;

    public function mount()
    {
        $this->loadCustomerData();
    }

    public function loadCustomerData()
    {
        $this->customer = Auth::user()->customer;

        if (!$this->customer) {
            return;
        }

        // Charger les licences avec leurs relations
        $this->licenses = $this->customer->licenses()
            ->with(['product', 'modules', 'options'])
            ->get();

        // Modules actifs
        $this->activeModules = Module::whereHas('licenses', function($query) {
            $query->where('customer_id', $this->customer->id)
                  ->where('status', LicenseStatus::ACTIVE);
        })->get();

        // Licences expirant dans les 30 jours
        $this->expiringLicenses = $this->licenses->filter(function($license) {
            return $license->expires_at &&
                   $license->expires_at->diffInDays(now()) <= 30 &&
                   $license->expires_at->isFuture();
        });

        // Factures impayées
        $this->unpaidInvoices = $this->customer->invoices()
            ->where('status', '!=', 'paid')
            ->get();

        // Statistiques
        $this->stats = [
            'active_licenses' => $this->licenses->where('status', LicenseStatus::ACTIVE)->count(),
            'total_licenses' => $this->licenses->count(),
            'active_modules' => $this->activeModules->count(),
            'expiring_soon' => $this->expiringLicenses->count(),
            'unpaid_amount' => $this->unpaidInvoices->sum('total')
        ];
    }

    public function activateLicense($licenseId)
    {
        $license = License::find($licenseId);
        if ($license && $license->customer_id === $this->customer->id) {
            $license->update(['status' => LicenseStatus::ACTIVE]);
            $this->loadCustomerData();
            session()->flash('success', 'Licence activée avec succès.');
        }
    }

    public function downloadLicense($licenseId)
    {
        $license = License::find($licenseId);
        if ($license && $license->customer_id === $this->customer->id) {
            // Logique de téléchargement
            return response()->download(storage_path('licenses/' . $license->license_key . '.pdf'));
        }
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

    #[Title("Tableau de Bord")]
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.client.dashboard');
    }
}
