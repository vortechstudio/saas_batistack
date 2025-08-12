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

#[Layout('components.layouts.app')]
#[Title('Mes Licences')]
class Licenses extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $selectedLicense = null;
    public $showLicenseDetails = false;

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
        $license = License::find($licenseId);
        if ($license && $license->customer_id === $this->customer->id) {
            $license->update(['status' => LicenseStatus::ACTIVE]);
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