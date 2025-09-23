<?php

namespace App\Livewire\Client\Account\Components\Table;

use App\Enum\Customer\CustomerRestrictedIpTypeEnum;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RestrictedIpTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(User::find(Auth::user()->id)->customer->restrictedIps()->getQuery())
            ->heading('Liste des restrictions IP')
            ->headerActions([
                Action::make('add')
                    ->label('Ajouter une restriction IP')
                    ->icon(Heroicon::PlusCircle)
                    ->schema([
                        TextInput::make('ip_address')
                            ->label('Adresse IP')
                            ->required()
                            ->ip(),

                        Select::make('authorize')
                            ->label('Type d\'autorisation')
                            ->options(CustomerRestrictedIpTypeEnum::options())
                            ->required()
                            ->default(CustomerRestrictedIpTypeEnum::ALLOW->value),

                        Toggle::make('alert')
                            ->label('Activer les alertes')
                            ->default(false),
                    ])
                    ->action(function (array $data) {
                        User::find(Auth::user()->id)->customer->restrictedIps()->create($data);
                    }),
            ])
            ->recordActions([
                DeleteAction::make('delete')
                    ->iconButton()
                    ->tooltip('Supprimer')
                    ->icon(Heroicon::XCircle)
                    ->using(function (?Model $record) {
                        $record->delete();
                    })
                    ->requiresConfirmation(),
            ])
            ->columns([
                TextColumn::make('ip_address')
                    ->label('Adresse IP'),

                TextColumn::make('authorize')
                    ->label('Autorisation'),

                TextColumn::make('alert')
                    ->label('Activer les alertes'),
            ]);
    }

    public function render()
    {
        return view('livewire.client.account.components.table.restricted-ip-table');
    }
}
