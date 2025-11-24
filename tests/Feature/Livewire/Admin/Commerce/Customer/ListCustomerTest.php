<?php

use App\Livewire\Admin\Commerce\Customer\ListCustomer;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(ListCustomer::class)
        ->assertStatus(200);
});
