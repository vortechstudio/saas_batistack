<?php

namespace App\Livewire\Client\Account;

use App\Models\Customer\CustomerService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Mes Service - Détail')]
class ServiceShow extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public CustomerService $service;
    public string $stateInstallLabel = '';
    public int $stateInstallCurrent = 0;
    public int $stateInstallTotal = 0;
    public ?string $comment = null;
    public ?array $infoStorage = null;

    // Gestion des onglets
    public string $activeTab = 'modules';

    public function mount(string $service_code)
    {
        $this->service = CustomerService::with('product', 'steps', 'modules.feature', 'options.product')->where('service_code', $service_code)->first();
        $this->stateInstallTotal = $this->service->steps->count();
        $this->stateInstallCurrent = $this->service->steps->where('done', true)->count()+1;
        $this->stateInstallLabel = $this->service->steps()->where('done', false)->latest()->first()->step ?? '';
        $this->getStorageInfo();
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

    /**
     * Récupère les informations de stockage du service
     */
    public function getStorageInfo()
    {
        $this->infoStorage = Http::withoutVerifying()
            ->get('https://'.$this->service->domain.'/api/core/storage/info')
            ->object();
    }

    public function table(Table $table): Table
    {
        $users = Http::withoutVerifying()
            ->get('https://'.$this->service->domain.'/api/users')
            ->json();

        return $table->records(fn () => json_decode($users, true))
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('email')->label('Email'),
            ]);
    }

    public function render()
    {        
        //dd($this->service->product->info_stripe);
        return view('livewire.client.account.service-show');
    }
}
