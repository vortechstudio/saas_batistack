<?php

namespace App\Livewire\Client\Account\Components\Table;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Notifications & Mail Reçus')
            ->headerActions([
                Action::make('refresh')
                    ->tooltip('Rafraichir')
                    ->iconButton()
                    ->size(Size::Large)
                    ->icon(Heroicon::ArrowTurnUpRight)
                    ->action(function () {
                        $this->dispatch('refresh');
                    }),
            ])
            ->query(User::find(Auth::user()->id)->notifications()->getQuery())
            ->columns([
                TextColumn::make('data.title')
                    ->label('Sujet'),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i'),

                Panel::make([
                    Split::make([
                        TextColumn::make('data.body')
                            ->html(),
                    ])
                ])->collapsed(true),
            ]);
    }

    public function render()
    {
        return view('livewire.client.account.components.table.notification-table');
    }
}
