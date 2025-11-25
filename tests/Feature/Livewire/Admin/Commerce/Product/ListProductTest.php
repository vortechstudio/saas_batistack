<?php

use App\Livewire\Admin\Commerce\Product\ListProduct;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(ListProduct::class)
        ->assertStatus(200);
});
