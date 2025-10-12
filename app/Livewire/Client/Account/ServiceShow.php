<?php

namespace App\Livewire\Client\Account;

use App\Models\Customer\CustomerService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Mes Service - Détail')]
class ServiceShow extends Component
{
    public CustomerService $service;
    public string $stateInstallLabel = '';
    public int $stateInstallCurrent = 0;
    public int $stateInstallTotal = 0;
    public ?string $comment = null;

    // Gestion des onglets
    public string $activeTab = 'modules';

    public function mount(string $service_code)
    {
        $this->service = CustomerService::with('product', 'steps', 'modules.feature', 'options.product')->where('service_code', $service_code)->first();
        $this->stateInstallTotal = $this->service->steps->count();
        $this->stateInstallCurrent = $this->service->steps->where('done', true)->count()+1;
        $this->stateInstallLabel = $this->service->steps()->where('done', false)->latest()->first()->step ?? '';
    }

    public function refreshStateInstall()
    {
        $this->stateInstallTotal = $this->service->steps->count();
        $this->stateInstallCurrent = $this->service->steps->where('done', true)->count()+1;
        $this->stateInstallLabel = $this->service->steps()->where('done', false)->latest()->first()->step ?? 'Fin';

        if($this->stateInstallCurrent == $this->stateInstallTotal) {
            $this->stateInstallLabel = 'Fin';
        }

        $this->comment = $this->service->steps()->where('done', false)->latest()->first()->comment ?? null;
    }

    public function setActiveTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    /**
     * Vérifie si l'option "Sauvegarde et rétention" est associée au service
     */
    public function hasBackupOption(): bool
    {
        return $this->service->options()
            ->whereHas('product', function ($query) {
                $query->where('slug', 'sauvegarde-et-retentions');
            })
            ->exists();
    }

    public function render()
    {
        return view('livewire.client.account.service-show');
    }
}
