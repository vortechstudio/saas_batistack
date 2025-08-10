<?php

use App\Models\Notification;
use App\Models\User;
use App\Enums\NotificationType;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->notification = Notification::factory()->create([
        'notifiable_type' => User::class,
        'notifiable_id' => $this->user->id,
        'type' => NotificationType::LICENSE_EXPIRING,
        'data' => [
            'title' => 'License Expiring',
            'message' => 'Your license will expire soon',
            'license_id' => 1
        ],
        'channels' => ['email'],
        'priority' => 3,
        'read_at' => null,
        'sent_at' => now(),
    ]);
});

describe('Notification Model', function () {
    test('can create a notification', function () {
        expect($this->notification)->toBeInstanceOf(Notification::class)
            ->and($this->notification->data['title'])->toBe('License Expiring')
            ->and($this->notification->data['message'])->toBe('Your license will expire soon')
            ->and($this->notification->isUnread())->toBeTrue();
    });

    test('has correct fillable attributes', function () {
        $fillable = [
            'type', 'notifiable_type', 'notifiable_id', 'data',
            'read_at', 'priority', 'channels', 'scheduled_at', 'sent_at', 'level'
        ];
        
        expect($this->notification->getFillable())->toBe($fillable);
    });

    test('casts attributes correctly', function () {
        expect($this->notification->type)->toBeInstanceOf(NotificationType::class)
            ->and($this->notification->data)->toBeArray()
            ->and($this->notification->channels)->toBeArray()
            ->and($this->notification->sent_at)->toBeInstanceOf(\Carbon\Carbon::class);
    });

    test('has polymorphic relationship to notifiable', function () {
        expect($this->notification->notifiable)->toBeInstanceOf(User::class)
            ->and($this->notification->notifiable->id)->toBe($this->user->id);
    });

    test('unread scope filters unread notifications', function () {
        Notification::factory()->create(['read_at' => now()]);
        
        $unreadNotifications = Notification::unread()->get();
        
        expect($unreadNotifications)->toHaveCount(1)
            ->and($unreadNotifications->first()->isUnread())->toBeTrue();
    });

    test('read scope filters read notifications', function () {
        Notification::factory()->create(['read_at' => now()]);
        
        $readNotifications = Notification::read()->get();
        
        expect($readNotifications)->toHaveCount(1)
            ->and($readNotifications->first()->isRead())->toBeTrue();
    });

    test('byType scope filters by notification type', function () {
        Notification::factory()->create(['type' => NotificationType::PAYMENT_OVERDUE]);
        
        $licenseNotifications = Notification::byType(NotificationType::LICENSE_EXPIRING)->get();
        
        expect($licenseNotifications)->toHaveCount(1)
            ->and($licenseNotifications->first()->type)->toBe(NotificationType::LICENSE_EXPIRING);
    });

    test('highPriority scope filters high priority notifications', function () {
        Notification::factory()->create(['type' => NotificationType::SECURITY_ALERT]);
        Notification::factory()->create(['type' => NotificationType::NEW_CUSTOMER]);
        
        $highPriorityNotifications = Notification::highPriority()->get();
        
        expect($highPriorityNotifications)->toHaveCount(1)
            ->and($highPriorityNotifications->first()->type)->toBe(NotificationType::SECURITY_ALERT);
    });

    test('isRead returns correct boolean', function () {
        expect($this->notification->isRead())->toBeFalse();
        
        $readNotification = Notification::factory()->create(['read_at' => now()]);
        expect($readNotification->isRead())->toBeTrue();
    });

    test('isUnread returns correct boolean', function () {
        expect($this->notification->isUnread())->toBeTrue();
        
        $readNotification = Notification::factory()->create(['read_at' => now()]);
        expect($readNotification->isUnread())->toBeFalse();
    });

    test('markAsRead updates read status and timestamp', function () {
        $this->notification->markAsRead();
        
        expect($this->notification->fresh()->isRead())->toBeTrue()
            ->and($this->notification->fresh()->read_at)->not->toBeNull();
    });

    test('markAsUnread updates read status', function () {
        $readNotification = Notification::factory()->create([
            'read_at' => now()
        ]);
        
        $readNotification->markAsUnread();
        
        expect($readNotification->fresh()->isUnread())->toBeTrue()
            ->and($readNotification->fresh()->read_at)->toBeNull();
    });

    test('getTypeLabel returns type label', function () {
        expect($this->notification->getTypeLabel())->toBe($this->notification->type->label());
    });

    test('getTypeIcon returns type icon', function () {
        expect($this->notification->getTypeIcon())->toBe($this->notification->type->icon());
    });

    test('getTypeColor returns type color', function () {
        expect($this->notification->getTypeColor())->toBe($this->notification->type->color());
    });

    test('getPriority returns type priority', function () {
        expect($this->notification->getPriority())->toBe($this->notification->type->priority());
    });
});