<?php

namespace App\Livewire\Admin\Commerce\Customer;

use App\Models\Customer\Customer;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ListCustomer extends Component implements HasTable, HasActions, HasSchemas
{
    use InteractsWithTable, InteractsWithActions, InteractsWithSchemas;

    public function table(Table $table): Table
    {
        return $table
            ->query(Customer::query())
            ->heading("Liste des Clients")
            ->columns([
                TextColumn::make('entreprise')
                    ->label('Client')
                    ->description(fn (?Model $record) => $record->code_client)
                    ->searchable(),

                TextColumn::make('type_compte')
                    ->label("Type de client")
                    ->formatStateUsing(fn (?Model $record) => $record->type_compte->label()),

                TextColumn::make('adresse')
                    ->label("Adresse")
                    ->formatStateUsing(function (?Model $record) {
                        return "{$record->adresse}<br>{$record->code_postal} {$record->ville}<br>{$record->pays}";
                    })
                    ->html(),

                TextColumn::make('tel')
                    ->label("Coordonnées")
                    ->formatStateUsing(function (?Model $record) {
                        return "<strong>Téléphone:</strong> {$record->tel}<br><strong>Email:</strong> {$record->user->email}";
                    })
                    ->html(),

                TextColumn::make('status')
                    ->label("Statut")
                    ->badge()
                    ->formatStateUsing(function (?Model $record) {
                        return \Str::ucfirst($record->status);
                    })
                    ->color(fn (?Model $record) => match($record->status) {
                        "active" => "info",
                        "inactive" => "danger"
                    }),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([]);
    }

    public function render()
    {
        return view('livewire.admin.commerce.customer.list-customer');
    }
}
