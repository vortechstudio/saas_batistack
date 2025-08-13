<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users are redirected to client dashboard', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/dashboard')->assertRedirect(route('client.dashboard'));
});

test('admin users are redirected to admin dashboard', function () {
    $admin = User::factory()->create([
        'email' => 'admin@batistack.com'
    ]);

    $this->actingAs($admin);

    $this->get('/dashboard')->assertRedirect(route('filament.admin.pages.dashboard'));
});
