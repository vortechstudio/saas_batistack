<?php

namespace App\Trait\Helpdesk;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

trait TicketSchema
{
    public function getNewTicketSchema(): array
    {
        return [
            Select::make('category')
                ->label("Motif")
                ->required()
                ->options([
                    'technical' => "Mon problème est d'ordre technique",
                    'commercial' => "Mon problème est commercial"
                ]),

            TextInput::make('subject')
                ->label("Sujet")
                ->required(),

            MarkdownEditor::make('content')
                ->label("Décrivez le problème que vous rencontrer")
                ->toolbarButtons([
                    ['bold', 'italic', 'strike', 'link', 'heading','bulletList', 'orderedList', 'attachFiles', 'undo', 'redo']
                ])
                ->hint('Editeur Markdown')
                ->required(),

            FileUpload::make('attachments')
                ->label("Pièces Jointes"),
        ];
    }
}
