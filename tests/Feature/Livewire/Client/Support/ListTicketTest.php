<?php

use App\Livewire\Client\Support\ListTicket;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(ListTicket::class)
        ->assertStatus(200);
});
