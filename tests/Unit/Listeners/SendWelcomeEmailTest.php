<?php

namespace Tests\Unit\Listeners;

use Tests\TestCase;
use App\Models\User;
use App\Listeners\SendWelcomeEmail;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_listener_can_be_instantiated(): void
    {
        $listener = new SendWelcomeEmail();

        $this->assertInstanceOf(SendWelcomeEmail::class, $listener);
    }

    public function test_sends_welcome_email_when_user_email_is_verified(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $event = new Verified($user);
        $listener = new SendWelcomeEmail();

        $listener->handle($event);

        Notification::assertSentTo(
            $user,
            WelcomeNotification::class,
            function ($notification, $channels) use ($user) {
                return $notification instanceof WelcomeNotification;
            }
        );
    }

    public function test_does_not_send_welcome_email_when_user_email_not_verified(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        $event = new Verified($user);
        $listener = new SendWelcomeEmail();

        $listener->handle($event);

        Notification::assertNotSentTo($user, WelcomeNotification::class);
    }

    public function test_logs_welcome_email_sent(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Email de bienvenue envoyé', [
                'user_id' => 1,
                'email' => 'john@example.com',
                'name' => 'John Doe'
            ]);

        Notification::fake();

        $user = User::factory()->create([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'email_verified_at' => now()
        ]);

        $event = new Verified($user);
        $listener = new SendWelcomeEmail();

        $listener->handle($event);
    }

    public function test_handles_null_user_gracefully(): void
    {
        Notification::fake();

        $event = new Verified(null);
        $listener = new SendWelcomeEmail();

        // Ne devrait pas lever d'exception
        $listener->handle($event);

        Notification::assertNothingSent();
    }
}
