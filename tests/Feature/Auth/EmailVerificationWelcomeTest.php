<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Enums\CustomerStatus;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;

class EmailVerificationWelcomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_email_sent_after_email_verification(): void
    {
        Notification::fake();
        // Retirer Event::fake() pour permettre aux listeners de s'exécuter

        // Créer un utilisateur non vérifié
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        Customer::factory()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => CustomerStatus::ACTIVE
        ]);

        // Simuler la vérification d'email
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        // Vérifier que l'utilisateur est maintenant vérifié
        $this->assertNotNull($user->fresh()->email_verified_at);

        // Déclencher manuellement l'événement Verified pour tester le listener
        event(new Verified($user->fresh()));

        // Vérifier que l'email de bienvenue a été envoyé
        Notification::assertSentTo(
            $user,
            WelcomeNotification::class
        );
    }

    public function test_registration_flow_with_welcome_email(): void
    {
        Notification::fake();

        // Données d'inscription
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $customerData = [
            'company_name' => 'Test Company',
            'contact_name' => 'John Doe',
            'phone' => '0123456789',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'postal_code' => '12345',
            'country' => 'FR',
        ];

        // Simuler l'inscription via Livewire
        $component = \Livewire\Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', $userData['name'])
            ->set('email', $userData['email'])
            ->set('password', $userData['password'])
            ->set('password_confirmation', $userData['password_confirmation'])
            ->call('nextStep')
            ->set('company_name', $customerData['company_name'])
            ->set('contact_name', $customerData['contact_name'])
            ->set('phone', $customerData['phone'])
            ->set('address', $customerData['address'])
            ->set('city', $customerData['city'])
            ->set('postal_code', $customerData['postal_code'])
            ->set('country', $customerData['country'])
            ->call('register');

        // Vérifier que l'utilisateur a été créé
        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user);

        // Vérifier que le client a été créé
        $customer = Customer::where('user_id', $user->id)->first();
        $this->assertNotNull($customer);

        // Simuler la vérification d'email
        $user->markEmailAsVerified();
        event(new Verified($user));

        // Vérifier que l'email de bienvenue a été envoyé
        Notification::assertSentTo(
            $user,
            WelcomeNotification::class,
            function ($notification) use ($user) {
                $mailMessage = $notification->toMail($user);
                return $mailMessage->subject === 'Bienvenue chez BatiStack !' &&
                       str_contains($mailMessage->greeting, $user->name);
            }
        );
    }
}
