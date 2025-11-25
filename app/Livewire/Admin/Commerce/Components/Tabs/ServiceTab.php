<?php

namespace App\Livewire\Admin\Commerce\Components\Tabs;

use App\Actions\Services\ServiceStatusUpdate;
use App\Enum\Customer\CustomerServiceStatusEnum;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class ServiceTab extends Component implements HasSchemas, HasActions, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public Customer $customer;

    public function mount(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Construit et retourne la configuration de la table affichant les services associés au client.
     *
     * Configure la requête filtrée par client, les colonnes (code de service, produit, statut avec badge et
     * prochaine date de facturation), les filtres par statut, les actions de la barre d'outils (suspendre / activer)
     * et les actions par enregistrement (voir le service).
     *
     * @param Table $table Instance de table Filament à configurer.
     * @return Table La table Filament configurée pour l'affichage et la gestion des services du client.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(CustomerService::where('customer_id', $this->customer->id)->newQuery())
            ->heading("Liste des services du client")
            ->columns([
                TextColumn::make('service_code')
                    ->label("Code de service")
                    ->searchable(),

                TextColumn::make('product.name')
                    ->label("Produit"),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?Model $record) => $record->status->color())
                    ->formatStateUsing(fn (?Model $record) => $record->status->label()),

                TextColumn::make('nextBillingDate')
                    ->label("Prochain paiement")
                    ->date('d/m/Y'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label("Status")
                    ->options(CustomerServiceStatusEnum::options())
            ])
            ->toolbarActions([
                BulkAction::make('suspendre')
                    ->label("Suspendre le service")
                    ->action(fn (Collection $records) => $records->each->update(['status' => CustomerServiceStatusEnum::SUSPENDED])),

                BulkAction::make('activate')
                    ->label("Activer le service")
                    ->action(fn (Collection $records) => $records->each->update(['status' => CustomerServiceStatusEnum::OK])),
            ])
            ->headerActions([])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->label("Voir le service")
                        ->icon(Heroicon::Eye)
                        ->url("#"),
                ])
            ]);
    }

    /**
     * Rend la vue du volet "Services" utilisée dans l'onglet d'administration du commerce.
     *
     * @return \Illuminate\View\View La vue Blade pour le composant ServiceTab.
     */
    public function render()
    {
        return view('livewire.admin.commerce.components.tabs.service-tab');
    }
}