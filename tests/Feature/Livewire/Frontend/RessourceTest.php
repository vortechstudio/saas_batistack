<?php

use App\Livewire\Frontend\Ressource;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Ressource::class)
        ->assertStatus(200);
});
