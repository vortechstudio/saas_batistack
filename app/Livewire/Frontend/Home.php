<?php

namespace App\Livewire\Frontend;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.frontend')]
#[Title('Accueil')]
class Home extends Component
{
    /**
     * Rend la vue du composant frontend pour la page d'accueil.
     *
     * @return \Illuminate\View\View L'instance de la vue correspondant à 'livewire.frontend.home'.
     */
    public function render()
    {
        return view('livewire.frontend.home');
    }
}