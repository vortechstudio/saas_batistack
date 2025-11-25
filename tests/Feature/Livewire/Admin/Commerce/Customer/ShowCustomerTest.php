<?php

use App\Livewire\Admin\Commerce\Customer\ShowCustomer;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(ShowCustomer::class)
        ->assertStatus(200);
});
