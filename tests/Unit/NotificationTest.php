<?php

use App\Models\Notification;
use App\Models\User;
use App\Enums\NotificationType;
use App\Enums\NotificationChannel;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->notification = Notification::factory()->create([
        'user_id' => $this->user->id,
        'type' => NotificationType::LICENSE_EXPIRING,
        'channel' => NotificationChannel::EMAIL,
        'title' => 'License Expiring',
        'message' => 'Your license will expire soon',
        'data' => ['license_id' => 1],
        'is_read' => false,
        'sent_at' => now(),
    ]);
});

describe('Notification Model', function () {
    test('can create a notification', function () {
        expect($this->notification)->toBeInstanceOf(Notification::class)
            ->and($this->notification->title)->toBe('License Expiring')
            ->and($this->notification->message)->toBe('Your license will expire soon')
            ->and($this->notification->is_read)->toBeFalse();
    });

    test('has correct fillable attributes', function () {
        $fillable = [
            'user_id', 'type', 'channel', 'title', 'message', 'data',
            'is_read', 'read_at', 'sent_at', 'scheduled_for'
        ];
        
        expect($this->notification->getFillable())->toBe($fillable);
    });

    test('casts attributes correctly', function () {
        expect($this->notification->type)->toBeInstanceOf(NotificationType::class)
            ->and($this->notification->channel)->toBeInstanceOf(NotificationChannel::class)
            ->and($this->notification->data)->toBeArray()
            ->and($this->notification->is_read)->toBeFalse()
            ->and($this->notification->sent_at)->toBeInstanceOf(\Carbon\Carbon::class);
    });

    test('belongs to a user', function () {
        expect($this->notification->user)->toBeInstanceOf(User::class)
            ->and($this->notification->user->id)->toBe($this->user->id);
    });

    test('unread scope filters unread notifications', function () {
        Notification::factory()->create(['is_read' => true]);
        
        $unreadNotifications = Notification::unread()->get();
        
        expect($unreadNotifications)->toHaveCount(1)
            ->and($unreadNotifications->first()->is_read)->toBeFalse();
    });

    test('read scope filters read notifications', function () {
        Notification::factory()->create(['is_read' => true]);
        
        $readNotifications = Notification::read()->get();
        
        expect($readNotifications)->toHaveCount(1)
            ->and($readNotifications->first()->is_read)->toBeTrue();
    });

    test('sent scope filters sent notifications', function () {
        Notification::factory()->create(['sent_at' => null]);
        
        $sentNotifications = Notification::sent()->get();
        
        expect($sentNotifications)->toHaveCount(1)
            ->and($sentNotifications->first()->sent_at)->not->toBeNull();
    });

    test('pending scope filters pending notifications', function () {
        Notification::factory()->create(['sent_at' => null]);
        
        $pendingNotifications = Notification::pending()->get();
        
        expect($pendingNotifications)->toHaveCount(1)
            ->and($pendingNotifications->first()->sent_at)->toBeNull();
    });

    test('byType scope filters by notification type', function () {
        Notification::factory()->create(['type' => NotificationType::PAYMENT_OVERDUE]);
        
        $licenseNotifications = Notification::byType(NotificationType::LICENSE_EXPIRING)->get();
        
        expect($licenseNotifications)->toHaveCount(1)
            ->and($licenseNotifications->first()->type)->toBe(NotificationType::LICENSE_EXPIRING);
    });

    test('byChannel scope filters by notification channel', function () {
        Notification::factory()->create(['channel' => NotificationChannel::SMS]);
        
        $emailNotifications = Notification::byChannel(NotificationChannel::EMAIL)->get();
        
        expect($emailNotifications)->toHaveCount(1)
            ->and($emailNotifications->first()->channel)->toBe(NotificationChannel::EMAIL);
    });

    test('isRead returns correct boolean', function () {
        expect($this->notification->isRead())->toBeFalse();
        
        $readNotification = Notification::factory()->create(['is_read' => true]);
        expect($readNotification->isRead())->toBeTrue();
    });

    test('isSent returns correct boolean', function () {
        expect($this->notification->isSent())->toBeTrue();
        
        $pendingNotification = Notification::factory()->create(['sent_at' => null]);
        expect($pendingNotification->isSent())->toBeFalse();
    });

    test('markAsRead updates read status and timestamp', function () {
        $this->notification->markAsRead();
        
        expect($this->notification->fresh()->is_read)->toBeTrue()
            ->and($this->notification->fresh()->read_at)->not->toBeNull();
    });

    test('markAsUnread updates read status', function () {
        $readNotification = Notification::factory()->create([
            'is_read' => true,
            'read_at' => now()
        ]);
        
        $readNotification->markAsUnread();
        
        expect($readNotification->fresh()->is_read)->toBeFalse()
            ->and($readNotification->fresh()->read_at)->toBeNull();
    });

    test('markAsSent updates sent timestamp', function () {
        $pendingNotification = Notification::factory()->create(['sent_at' => null]);
        
        $pendingNotification->markAsSent();
        
        expect($pendingNotification->fresh()->sent_at)->not->toBeNull();
    });

    test('getTypeLabelAttribute returns type label', function () {
        expect($this->notification->type_label)->toBe($this->notification->type->label());
    });

    test('getChannelLabelAttribute returns channel label', function () {
        expect($this->notification->channel_label)->toBe($this->notification->channel->label());
    });
});