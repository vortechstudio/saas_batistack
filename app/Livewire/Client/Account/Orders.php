<?php

namespace App\Livewire\Client\Account;

use App\Enum\Commerce\OrderStatusEnum;
use App\Models\Commerce\Order;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Mes Commandes')]
class Orders extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    protected $listeners = [
        'refreshTable' => '$refresh',
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::with('items', 'items.product')->where('customer_id', Auth::user()->customer->id))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->sortable()
                    ->date('d/m/Y'),

                TextColumn::make('order_number')
                    ->label('Numéro de commande')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->color(fn (?Model $record) => $record->status->color())
                    ->badge()
                    ->formatStateUsing(fn (?Model $record) => $record->status->label()),

                TextColumn::make('total_amount')
                    ->label('Montant total')
                    ->sortable()
                    ->money('EUR'),
            ])
            ->emptyStateHeading('Aucune commande')
            ->emptyStateDescription('Vous n\'avez pas encore commandé.')
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(OrderStatusEnum::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('suivi')
                        ->label('Suivi de commande')
                        ->url(fn (Order $record) => route('client.account.order.show', $record->id))
                        ->openUrlInNewTab(),
                ]),
            ]);
    }

    public function render()
    {
        return view('livewire.client.account.orders');
    }
}
