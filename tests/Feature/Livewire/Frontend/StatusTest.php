<?php

use App\Livewire\Frontend\Status;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Status::class)
        ->assertStatus(200);
});
