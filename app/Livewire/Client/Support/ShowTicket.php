<?php

namespace App\Livewire\Client\Support;

use App\Models\Helpdesk\Ticket;
use App\Models\Helpdesk\TicketMessage;
use Auth;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.client')]
class ShowTicket extends Component implements HasSchemas, HasActions
{
    use InteractsWithSchemas, InteractsWithActions;
    public Ticket $ticket;
    public ?array $data = [];

    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket;
        $this->form->fill();
    }

    public function entryTicketList(Schema $schema): Schema
    {
        return $schema
            ->record($this->ticket)
            ->components([
                Section::make('Détails')
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (?Model $record) => $record->status->getColor())
                            ->label('Statut')
                            ->formatStateUsing(fn ($record) => $record->status->getLabel()),

                        TextEntry::make('priority')
                            ->badge()
                            ->color(fn (?Model $record) => $record->priority->getColor())
                            ->label("Priorité")
                            ->formatStateUsing(fn ($record) => $record->priority->getLabel()),

                        TextEntry::make('created_at')
                            ->label("Ouvert le")
                            ->dateTime('d/m/Y à H:i'),

                        TextEntry::make('last_reply_at')
                            ->label("Dernière réponse le")
                            ->dateTime('d/m/Y à H:i'),

                    ])
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->record($this->ticket)
            ->components([
                MarkdownEditor::make('content')
                    ->toolbarButtons([
                        ['bold', 'italic', 'strike', 'link', 'heading','bulletList', 'orderedList', 'attachFiles', 'undo', 'redo']
                    ])
                    ->hint('Editeur Markdown')
                    ->label("Votre réponse"),
            ])
            ->statePath('data');
    }

    public function reply()
    {
        TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'content' => $this->form->getState()['content'],
        ]);

        $this->form->fill();
        // Le TicketMessageObserver (créé précédemment) va mettre à jour le statut et last_reply_at
        $this->ticket->refresh();
    }


    public function render()
    {
        return view('livewire.client.support.show-ticket');
    }
}
