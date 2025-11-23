<?php

use App\Livewire\Frontend\Company;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Company::class)
        ->assertStatus(200);
});
