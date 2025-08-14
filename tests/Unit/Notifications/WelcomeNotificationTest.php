<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_notification_can_be_created(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $notification = new WelcomeNotification($user);

        $this->assertInstanceOf(WelcomeNotification::class, $notification);
    }

    public function test_welcome_notification_uses_mail_channel(): void
    {
        $user = User::factory()->create();
        $notification = new WelcomeNotification($user);

        $channels = $notification->via($user);

        $this->assertContains('mail', $channels);
    }

    public function test_welcome_notification_mail_content(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $notification = new WelcomeNotification($user);
        $mailMessage = $notification->toMail($user);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
        $this->assertEquals('Bienvenue chez BatiStack !', $mailMessage->subject);
        $this->assertStringContainsString('John Doe', $mailMessage->greeting);
        $this->assertStringContainsString('BatiStack', $mailMessage->introLines[0]);
    }

    public function test_welcome_notification_includes_dashboard_action(): void
    {
        $user = User::factory()->create();
        $notification = new WelcomeNotification($user);
        $mailMessage = $notification->toMail($user);

        $this->assertEquals('Accéder à mon tableau de bord', $mailMessage->actionText);
        $this->assertEquals(route('dashboard'), $mailMessage->actionUrl);
    }

    public function test_welcome_notification_to_array(): void
    {
        $user = User::factory()->create([
            'id' => 123,
            'email' => 'john@example.com'
        ]);

        $notification = new WelcomeNotification($user);
        $array = $notification->toArray($user);

        $this->assertEquals(123, $array['user_id']);
        $this->assertStringContainsString('john@example.com', $array['message']);
    }
}
