<?php

use App\Livewire\Frontend\Tarifs;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Tarifs::class)
        ->assertStatus(200);
});
