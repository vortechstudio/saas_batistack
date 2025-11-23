<?php

use App\Livewire\Client\Support\ShowTicket;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(ShowTicket::class)
        ->assertStatus(200);
});
