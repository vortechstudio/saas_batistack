<?php

use App\Livewire\Auth\Register;
use Livewire\Livewire;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = Livewire::test(Register::class)
        // Étape 1 : Informations utilisateur
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('nextStep')
        ->assertHasNoErrors()
        // Étape 2 : Informations client/entreprise
        ->set('company_name', 'Test Company')
        ->set('contact_name', 'Test Contact')
        ->set('phone', '+33123456789')
        ->set('address', '123 Test Street')
        ->set('city', 'Test City')
        ->set('postal_code', '12345')
        ->set('country', 'FR')
        ->call('register');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('verification.notice', absolute: false));

    $this->assertAuthenticated();

    // Vérifier que l'utilisateur a été créé
    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    // Vérifier que le client a été créé
    $this->assertDatabaseHas('customers', [
        'company_name' => 'Test Company',
        'contact_name' => 'Test Contact',
        'email' => 'test@example.com',
    ]);
});

test('registration validates step 1 fields', function () {
    Livewire::test(Register::class)
        ->set('name', '')
        ->set('email', 'invalid-email')
        ->set('password', 'short')
        ->set('password_confirmation', 'different')
        ->call('nextStep')
        ->assertHasErrors([
            'name' => 'required',
            'email' => 'email',
            'password' => 'confirmed',
        ]);
});

test('registration validates step 2 fields', function () {
    Livewire::test(Register::class)
        // Passer l'étape 1 avec des données valides
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('nextStep')
        ->assertHasNoErrors()
        // Tester la validation de l'étape 2
        ->set('company_name', '')
        ->set('contact_name', '')
        ->set('phone', '')
        ->set('address', '')
        ->set('city', '')
        ->set('postal_code', '')
        ->call('register')
        ->assertHasErrors([
            'company_name' => 'required',
            'contact_name' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'city' => 'required',
            'postal_code' => 'required',
        ]);
});

test('can navigate between registration steps', function () {
    $component = Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->call('previousStep')
        ->assertSet('currentStep', 1);
});
