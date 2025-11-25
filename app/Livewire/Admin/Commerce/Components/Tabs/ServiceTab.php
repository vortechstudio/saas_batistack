<?php

namespace App\Livewire\Admin\Commerce\Components\Tabs;

use App\Enum\Customer\CustomerServiceStatusEnum;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerService;
use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
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
            ->recordActions([]);
    }

    public function render()
    {
        return view('livewire.admin.commerce.components.tabs.service-tab');
    }
}
