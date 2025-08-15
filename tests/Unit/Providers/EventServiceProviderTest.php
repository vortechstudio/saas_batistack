<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use App\Providers\EventServiceProvider;
use Illuminate\Auth\Events\Verified;
use App\Listeners\SendWelcomeEmail;

class EventServiceProviderTest extends TestCase
{
    public function test_verified_event_has_send_welcome_email_listener(): void
    {
        $provider = new EventServiceProvider(app());
        $listeners = $provider->listens();

        $this->assertArrayHasKey(Verified::class, $listeners);
        $this->assertContains(SendWelcomeEmail::class, $listeners[Verified::class]);
    }
}