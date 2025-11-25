<?php

namespace App\Livewire\Admin\Commerce\Customer;

use App\Enum\Customer\CustomerTypeEnum;
use App\Models\Customer\Customer;
use App\Models\User;
use App\Notifications\Customer\WelcomeCustomerNotification;
use App\Services\Stripe\CustomerService;
use App\Trait\Commerce\TiersSchema;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ListCustomer extends Component implements HasTable, HasActions, HasSchemas
{
    use InteractsWithTable, InteractsWithActions, InteractsWithSchemas, TiersSchema;

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
            ->filters([
                SelectFilter::make('type_compte')
                    ->label("Type de client")
                    ->options(CustomerTypeEnum::options()),

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        "active" => "Active",
                        "inactive" => "Inactive"
                    ])
            ])
            ->headerActions([
                CreateAction::make('create')
                    ->icon(Heroicon::PlusCircle)
                    ->label("Créer un client")
                    ->modalHeading("Nouveau client")
                    ->modalWidth(Width::FourExtraLarge)
                    ->schema($this->getSchemaTiers())
                    ->using(function (array $data) {
                        $password = \Str::random(10);

                        $user = User::create([
                            'nom' => $data['nom'],
                            'prenom' => $data['prenom'],
                            'email' => $data['email'],
                            'password' => \Hash::make($password),
                        ]);

                        $customer = Customer::create([
                            'type_compte' => $data['type_compte'],
                            'entreprise' => $data['entreprise'] ?? null,
                            'adresse' => $data['adresse'],
                            'code_postal' => $data['code_postal'],
                            'ville' => $data['ville'],
                            'pays' => $data['pays'],
                            'tel' => $data['tel'],
                            'portable' => $data['portable'],
                            'support_type' => $data['support_type'],
                            'user_id' => $user->id
                        ]);

                        app(CustomerService::class)->create($customer);
                        $user->notify((new WelcomeCustomerNotification())->delay(now()->addSeconds(30)));

                    }),
            ])
            ->recordActions([]);
    }

    public function render()
    {
        return view('livewire.admin.commerce.customer.list-customer');
    }
}
