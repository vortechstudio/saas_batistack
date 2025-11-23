<?php

use App\Livewire\Frontend\FeatureShow;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(FeatureShow::class)
        ->assertStatus(200);
});
