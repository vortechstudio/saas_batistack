<?php

namespace App\Livewire\Client\Support;

use App\Enum\Helpdesk\TicketCategoryEnum;
use App\Enum\Helpdesk\TicketStatusEnum;
use App\Models\Helpdesk\Ticket;
use App\Models\Helpdesk\TicketMessage;
use App\Models\User;
use App\Trait\Helpdesk\TicketSchema;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
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
use Livewire\Component;

#[Layout('components.layouts.client')]
class ListTicket extends Component implements HasSchemas, HasActions, HasTable
{
    use InteractsWithSchemas, InteractsWithActions, InteractsWithTable, TicketSchema;

    public function table(Table $table): Table
    {
        return $table
            ->query(Ticket::where('user_id', auth()->id())->newQuery())
            ->heading("Mes Tickets de support")
            ->headerActions([
                CreateAction::make('create')
                    ->label("Nouveau ticket")
                    ->icon(Heroicon::Ticket)
                    ->schema($this->getNewTicketSchema())
                    ->modalHeading("Nouvelle demande")
                    ->modalDescription("Décrivez votre problème pour que nous puissions vous aider efficacement.")
                    ->modalIcon(Heroicon::Ticket)
                    ->using(function (array $data) {

                        $ticket = Ticket::create([
                            'subject' => $data['subject'],
                            'category' => $data['category'],
                            'user_id' => auth()->id(),
                        ]);

                        TicketMessage::create([
                            'ticket_id' => $ticket->id,
                            'content' => $data['content'],
                            'attachments' => $data['attachments'],
                            'user_id' => auth()->id(),
                        ]);

                        Notification::make()
                            ->success()
                            ->title("Votre ticket à bien été créer")
                            ->send();
                    }),

                Action::make('refresh')
                    ->label("Rafraichir")
                    ->color('gray')
                    ->icon(Heroicon::ArrowPath)
                    ->action(fn () => $this->resetTable())
            ])
            ->columns([
                TextColumn::make('uuid')
                    ->label("Identifiant"),

                TextColumn::make('category')
                    ->label("Catégorie")
                    ->formatStateUsing(fn (?Model $record) => $record->category->getLabel()),

                TextColumn::make('subject')
                    ->label("Sujet"),

                TextColumn::make('status')
                    ->label("Statut")
                    ->badge()
                    ->color(fn (?Model $record) => $record->status->getColor())
                    ->formatStateUsing(fn (?Model $record) => $record->status->getLabel()),

                TextColumn::make('last_reply_at')
                    ->label("Dernière réponse")
                    ->dateTime('d/m/Y H:i'),
            ])
            ->recordActions([
                Action::make('view')
                    ->iconButton()
                    ->tooltip("Voir le ticket")
                    ->icon(Heroicon::Eye)
                    ->url(fn (?Model $record) => route('client.support.ticket.show', $record)),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label("Statut")
                    ->options(TicketStatusEnum::options()),

                SelectFilter::make('category')
                    ->label("Categorie")
                    ->options(TicketCategoryEnum::options()),
            ]);
    }

    public function render()
    {
        return view('livewire.client.support.list-ticket');
    }
}
