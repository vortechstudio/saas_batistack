<?php

use App\Livewire\Auth\Register;
use App\Models\User;
use App\Models\Customer;
use App\Enums\CustomerStatus;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

describe('Register Component', function () {
    beforeEach(function () {
        Event::fake();
    });

    test('renders registration page successfully', function () {
        Livewire::test(Register::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.auth.register');
    });

    test('has correct title and layout', function () {
        $component = new Register();
        $reflection = new ReflectionClass($component);

        $layoutAttribute = $reflection->getAttributes(\Livewire\Attributes\Layout::class)[0] ?? null;
        expect($layoutAttribute?->getArguments()[0])
            ->toBe('components.layouts.auth');
    });

    test('initializes with correct default values', function () {
        Livewire::test(Register::class)
            ->assertSet('currentStep', 1)
            ->assertSet('totalSteps', 2)
            ->assertSet('country', 'FR')
            ->assertSet('emailVerificationSent', false);
    });

    test('validates step 1 required fields', function () {
        Livewire::test(Register::class)
            ->call('nextStep')
            ->assertHasErrors([
                'name' => 'required',
                'email' => 'required',
                'password' => 'required'
            ]);
    });

    test('validates email uniqueness in step 1', function () {
        User::factory()->create(['email' => 'test@example.com']);

        Livewire::test(Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('nextStep')
            ->assertHasErrors(['email' => 'unique']);
    });

    test('validates password confirmation in step 1', function () {
        Livewire::test(Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'different')
            ->call('nextStep')
            ->assertHasErrors(['password' => 'confirmed']);
    });

    test('advances to step 2 with valid step 1 data', function () {
        Livewire::test(Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('nextStep')
            ->assertSet('currentStep', 2)
            ->assertSet('contact_name', 'Test User'); // Auto-filled
    });

    test('can go back to previous step', function () {
        Livewire::test(Register::class)
            ->set('currentStep', 2)
            ->call('previousStep')
            ->assertSet('currentStep', 1);
    });

    test('validates step 2 required fields', function () {
        // Créer un composant et aller directement à l'étape 2 sans passer par nextStep()
        // pour éviter le pré-remplissage automatique du contact_name
        $component = Livewire::test(Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('currentStep', 2)
            ->set('contact_name', ''); // Vider explicitement le contact_name

        // Maintenant tester la validation de l'étape 2
        $component->call('register')
            ->assertHasErrors([
                'company_name' => 'required',
                'contact_name' => 'required',
                'phone' => 'required',
                'address' => 'required',
                'city' => 'required',
                'postal_code' => 'required',
                'country' => 'required'
            ]);
    });

    test('registers user and customer successfully', function () {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $customerData = [
            'company_name' => 'Test Company',
            'contact_name' => 'Test Contact',
            'phone' => '0123456789',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'postal_code' => '12345',
            'country' => 'FR',
            'siret' => '12345678901234',
            'vat_number' => 'FR12345678901'
        ];

        Livewire::test(Register::class)
            ->set($userData)
            ->set($customerData)
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('verification.notice'));

        // Verify user was created
        $user = User::where('email', 'test@example.com')->first();
        expect($user)->not->toBeNull();
        expect(Hash::check('password123', $user->password))->toBeTrue();

        // Verify customer was created
        $customer = Customer::where('user_id', $user->id)->first();
        expect($customer)->not->toBeNull();
        expect($customer->company_name)->toBe('Test Company');
        expect($customer->status)->toBe(CustomerStatus::ACTIVE);

        // Verify events were fired
        Event::assertDispatched(Registered::class);
    });

    test('calculates progress percentage correctly', function () {
        $component = Livewire::test(Register::class);

        expect($component->instance()->getProgressPercentage())->toBe(50); // Step 1 of 2

        $component->set('currentStep', 2);
        expect($component->instance()->getProgressPercentage())->toBe(100); // Step 2 of 2
    });

    test('can resend verification email when authenticated', function () {
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        // Simuler l'authentification et tester la méthode
        $component = Livewire::actingAs($user)->test(Register::class);

        // Appeler la méthode de renvoi d'email
        $component->call('resendVerificationEmail');

        // Dans les tests Livewire, nous devons vérifier différemment
        // car session()->flash() ne persiste pas de la même manière
        // Nous pouvons vérifier que la méthode s'exécute sans erreur
        $component->assertHasNoErrors();

        // Alternative : vérifier que l'utilisateur existe et n'est pas vérifié
        expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
    });

    test('resend verification email sets session status', function () {
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        // Authentifier l'utilisateur
        $this->actingAs($user);

        // Créer une instance du composant et appeler la méthode directement
        $component = new Register();
        $component->resendVerificationEmail();

        // Vérifier que le statut de session a été défini
        expect(session('status'))->toBe('verification-link-sent');
    });
});
