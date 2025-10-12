<?php

namespace App\Livewire\Client\Account;

use App\Models\Customer\Customer;
use App\Models\Customer\CustomerServiceBackup;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.client')]
#[Title('Mes Sauvegardes')]
class Sauvegardes extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public Customer $customer;

    public function mount()
    {
        $this->customer = Auth::user()->customer;
    }

    public function table(Table $table): Table
    {
        return $table
                ->query(CustomerServiceBackup::where('customer_id', $this->customer->id))
                ->columns([
                    TextColumn::make('created_at')
                        ->label('Date de sauvegarde')
                        ->dateTime('d/m/Y H:i'),

                    TextColumn::make('customer_service_id')
                        ->label('Service Associé')
                        ->formatStateUsing(function (?Model $record) {
                            return $record->customerService->service_code;
                        }),
                ])
                ->recordActions([
                    Action::make('restore')
                        ->label('Restaurer')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (CustomerServiceBackup $record) {
                            $request = Http::withoutVerifying()
                                ->get($record->customerService->domain.'/api/core/backup-restore', ['backup' => $record->created_at->format('Y-m-d-H-i-s')]);

                            if ($request->successful()) {
                                Notification::make()
                                    ->success()
                                    ->title('Sauvegarde téléchargée avec succès')
                                    ->body($request->body());
                            } else {
                                Notification::make()
                                    ->danger()
                                    ->title('Erreur lors du téléchargement de la sauvegarde')
                                    ->body($request->body());
                            }
                        }),
                ]);
    }

    public function render()
    {
        return view('livewire.client.account.sauvegardes');
    }
}
