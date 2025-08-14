<?php

namespace App\Livewire\Public;

use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Schema;
use Filament\Notifications\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class DemoPage extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nom complet')
                        ->required()
                        ->minLength(2)
                        ->maxLength(255)
                        ->placeholder('Votre nom complet')
                        ->columnSpan(1),

                    TextInput::make('email')
                        ->label('Email professionnel')
                        ->email()
                        ->required()
                        ->placeholder('votre.email@entreprise.com')
                        ->columnSpan(1),
                ]),

            Grid::make(2)
                ->schema([
                    TextInput::make('company')
                        ->label('Nom de l\'entreprise')
                        ->required()
                        ->minLength(2)
                        ->maxLength(255)
                        ->placeholder('Nom de votre entreprise')
                        ->columnSpan(1),

                    TextInput::make('phone')
                        ->label('Téléphone')
                        ->tel()
                        ->placeholder('06 12 34 56 78')
                        ->columnSpan(1),
                ]),

            Select::make('selectedPlan')
                ->label('Plan qui vous intéresse')
                ->required()
                ->options([
                    'starter' => 'Batistack Starter (49,99€/mois)',
                    'professional' => 'Batistack Professional (99,99€/mois)',
                    'enterprise' => 'Batistack Enterprise (199,99€/mois)',
                ])
                ->default('professional'),

            Textarea::make('message')
                ->label('Message (optionnel)')
                ->placeholder('Décrivez vos besoins spécifiques ou posez vos questions...')
                ->rows(4)
                ->maxLength(500)
                ->columnSpanFull(),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    #[Layout('livewire.public.main-layout')]
    #[Title('Demander une démo - Batistack')]
    public function render()
    {
        return view('livewire.public.demo-page');
    }

    public function submitDemo(): void
    {
        $data = $this->form->getState();

        // Ici vous pouvez ajouter la logique pour traiter la demande de démo
        // Par exemple : envoyer un email, sauvegarder en base de données, etc.

        // Notification de succès avec FilamentPHP
        Notification::make()
            ->title('Demande envoyée avec succès !')
            ->body('Votre demande de démonstration a été envoyée. Nous vous contacterons dans les plus brefs délais.')
            ->success()
            ->duration(5000)
            ->send();

        // Réinitialiser le formulaire
        $this->form->fill();
    }
}
