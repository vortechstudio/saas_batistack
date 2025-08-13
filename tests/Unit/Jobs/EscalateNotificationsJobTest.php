<?php

use App\Jobs\EscalateNotificationsJob;
use App\Models\User;
use App\Models\Notification;
use App\Enums\NotificationType;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

beforeEach(function () {
    NotificationFacade::fake();
    $this->adminUser = User::factory()->create(['email' => 'admin@batistack.com']);
});

test('escalates critical unread notifications', function () {
    // Create a critical notification older than 1 hour
    Notification::factory()->create([
        'type' => NotificationType::SYSTEM_ALERT,
        'notifiable_type' => User::class,
        'notifiable_id' => $this->adminUser->id,
        'data' => [
            'priority' => 'critical',
            'title' => 'Critical Alert',
            'message' => 'System error detected'
        ],
        'read_at' => null,
        'created_at' => now()->subHours(2),
    ]);

    $job = new EscalateNotificationsJob();
    $job->handle();

    NotificationFacade::assertSentTo(
        $this->adminUser,
        SystemAlertNotification::class
    );
});

test('escalates high priority notifications after 4 hours', function () {
    // Create a high priority notification older than 4 hours
    Notification::factory()->create([
        'type' => NotificationType::SYSTEM_ALERT,
        'notifiable_type' => User::class,
        'notifiable_id' => $this->adminUser->id,
        'data' => [
            'priority' => 'high',
            'title' => 'High Priority Alert',
            'message' => 'Important system event'
        ],
        'read_at' => null,
        'created_at' => now()->subHours(5),
    ]);

    $job = new EscalateNotificationsJob();
    $job->handle();

    NotificationFacade::assertSentTo(
        $this->adminUser,
        SystemAlertNotification::class
    );
});

test('sends daily report when many unread notifications', function () {
    // Create more than 10 unread notifications
    Notification::factory()->count(15)->create([
        'type' => NotificationType::SYSTEM_ALERT,
        'notifiable_type' => User::class,
        'notifiable_id' => $this->adminUser->id,
        'data' => ['title' => 'Test notification'],
        'read_at' => null,
    ]);

    $job = new EscalateNotificationsJob();
    $job->handle();

    NotificationFacade::assertSentTo(
        $this->adminUser,
        SystemAlertNotification::class
    );
});

test('does not escalate recent notifications', function () {
    // Create a critical notification that's recent (less than 1 hour)
    Notification::factory()->create([
        'type' => NotificationType::SYSTEM_ALERT,
        'notifiable_type' => User::class,
        'notifiable_id' => $this->adminUser->id,
        'data' => [
            'priority' => 'critical',
            'title' => 'Recent Critical Alert'
        ],
        'read_at' => null,
        'created_at' => now()->subMinutes(30),
    ]);

    $job = new EscalateNotificationsJob();
    $job->handle();

    NotificationFacade::assertNothingSent();
});

test('does not escalate read notifications', function () {
    // Create a critical notification that's been read
    Notification::factory()->create([
        'type' => NotificationType::SYSTEM_ALERT,
        'notifiable_type' => User::class,
        'notifiable_id' => $this->adminUser->id,
        'data' => [
            'priority' => 'critical',
            'title' => 'Read Critical Alert'
        ],
        'read_at' => now(),
        'created_at' => now()->subHours(2),
    ]);

    $job = new EscalateNotificationsJob();
    $job->handle();

    NotificationFacade::assertNothingSent();
});
