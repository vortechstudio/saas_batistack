<?php

namespace App\Livewire\Client\Account;

use App\Models\Commerce\Order;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Mon Compte')]
class Dashboard extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions, InteractsWithSchemas;
    public $activeTab = 'general';
    public $latestInvoice;
    public ?array $profilData = [];

    public function mount()
    {
        // Récupérer la dernière facture
        $this->latestInvoice = Order::where('customer_id', Auth::user()->customer->id)
            ->whereNotNull('delivered_at')
            ->latest('delivered_at')
            ->first();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function editProfilAction()
    {
        $this->dispatch('open-modal', id: 'edit-profil');
    }

    public function editProfilForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->email(),
                TextInput::make('phone')
                    ->label('Téléphone')
                    ->required(),
            ])
            ->statePath('profilData')
            ->model(Auth::user());
    }



    public function render()
    {
        return view('livewire.client.account.dashboard');
    }
}
