<?php

use App\Livewire\Client\Dashboard;
use App\Models\User;
use App\Models\Customer;
use App\Models\License;
use App\Enums\LicenseStatus;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->customer = Customer::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);
});

describe('Client Dashboard Component', function () {
    test('renders dashboard for authenticated user', function () {
        Livewire::test(Dashboard::class)
            ->assertStatus(200)
            ->assertSee('Tableau de bord')
            ->assertSee($this->user->name);
    });

    test('displays license statistics', function () {
        License::factory()->count(3)->create([
            'customer_id' => $this->customer->id,
            'status' => LicenseStatus::ACTIVE
        ]);

        Livewire::test(Dashboard::class)
            ->assertSee('3'); // Nombre de licences actives
    });

    test('shows expiring licenses alert', function () {
        License::factory()->create([
            'customer_id' => $this->customer->id,
            'expires_at' => now()->addDays(15),
            'status' => LicenseStatus::ACTIVE
        ]);

        Livewire::test(Dashboard::class)
            ->assertSee('Licences expirant bientôt');
    });
});
