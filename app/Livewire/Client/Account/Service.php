<?php

namespace App\Livewire\Client\Account;

use App\Enum\Customer\CustomerServiceStatusEnum;
use App\Enum\Product\ProductCategoryEnum;
use App\Models\Customer\CustomerService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.client')]
#[Title('Mes Service')]
class Service extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(CustomerService::with('product')->where('customer_id', Auth::user()->customer->id))
            ->emptyStateHeading("Vous n'avez pas encore de service")
            ->emptyStateDescription('Achetez un service pour commencer à l\'utiliser.')
            ->emptyStateIcon(Heroicon::PlusCircle)
            ->emptyStateActions([
                Action::make('create')
                    ->label('Acheter un service')
                    ->icon(Heroicon::PlusCircle)
                    ->url(route('client.account.cart.index'))
            ])
            ->columns([
                TextColumn::make('service_code')
                    ->label('Code du service')
                    ->searchable(),

                TextColumn::make('product.name')
                    ->label('Produit / Service'),

                TextColumn::make('product.category')
                    ->label('Type')
                    ->formatStateUsing(fn (?Model $record) => $record->product->category->label()),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?Model $record) => $record->status->color())
                    ->formatStateUsing(fn (?Model $record) => $record->status->label()),

                TextColumn::make('created_at')
                    ->label("Date d'effet")
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                SelectFilter::make('product.category')
                    ->label('Type')
                    ->options(ProductCategoryEnum::class),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(CustomerServiceStatusEnum::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('Voir le service')
                        ->icon(Heroicon::Eye)
                        ->url(fn (CustomerService $record) => route('client.service.show', $record->service_code))
                        ->openUrlInNewTab(),

                    Action::make('resilier')
                        ->label('Resilier le service')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->visible(fn (CustomerService $record) => $record->status->value === CustomerServiceStatusEnum::OK),

                    Action::make('renew')
                        ->label('Renouveler le service')
                        ->icon(Heroicon::ArrowUpCircle)
                        ->color('warning')
                        ->visible(fn (CustomerService $record) => $record->status->value === CustomerServiceStatusEnum::UNPAID || $record->status->value === CustomerServiceStatusEnum::EXPIRED),
                ]),
            ]);
    }

    public function render()
    {
        return view('livewire.client.account.service');
    }
}
