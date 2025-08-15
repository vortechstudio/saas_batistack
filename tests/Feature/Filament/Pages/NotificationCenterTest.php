<?php

use App\Filament\Pages\NotificationCenter;
use App\Models\User;
use App\Models\Notification;
use App\Enums\NotificationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Créer les permissions nécessaires pour les notifications
    Permission::firstOrCreate(['name' => 'notification.view']);
    Permission::firstOrCreate(['name' => 'notification.create']);
    Permission::firstOrCreate(['name' => 'notification.edit']);
    Permission::firstOrCreate(['name' => 'notification.delete']);

    // Créer un rôle admin avec toutes les permissions
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $adminRole->givePermissionTo([
        'notification.view',
        'notification.create',
        'notification.edit',
        'notification.delete'
    ]);

    // Créer un utilisateur admin
    $this->user = User::factory()->create([
        'email' => 'admin@batistack.com',
        'email_verified_at' => now(),
    ]);

    // Assigner le rôle admin à l'utilisateur
    $this->user->assignRole('admin');

    $this->actingAs($this->user);
});

describe('NotificationCenter Page', function () {
    test('can render page', function () {
        livewire(NotificationCenter::class)
            ->assertSuccessful();
    });

    test('has correct navigation properties', function () {
        expect(NotificationCenter::getNavigationIcon())->toBe('heroicon-o-bell');
        expect(NotificationCenter::getNavigationLabel())->toBe('Centre de Notifications');
    });

    test('displays user notifications in table', function () {
        // Créer des notifications avec des types enum valides
        $notification = Notification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => NotificationType::LICENSE_EXPIRING,
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'Licence bientôt expirée',
                'message' => 'Votre licence expire dans 7 jours',
            ],
            'priority' => 'high',
            'created_at' => now(),
        ]);

        livewire(NotificationCenter::class)
            ->assertCanSeeTableRecords([$notification]);
    });

    test('can mark notification as read', function () {
        $notification = Notification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => NotificationType::LICENSE_EXPIRING,
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'Test notification',
                'message' => 'Test message',
            ],
            'priority' => 'medium',
            'created_at' => now(),
        ]);

        livewire(NotificationCenter::class)
            ->callTableAction('markAsRead', $notification->id);

        expect($notification->fresh()->read_at)->not->toBeNull();
    });

    test('can mark notification as unread', function () {
        $notification = Notification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => NotificationType::LICENSE_EXPIRING,
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'Test notification',
                'message' => 'Test message',
            ],
            'priority' => 'medium',
            'read_at' => now(),
            'created_at' => now(),
        ]);

        livewire(NotificationCenter::class)
            ->callTableAction('markAsUnread', $notification->id);

        expect($notification->fresh()->read_at)->toBeNull();
    });

    test('can delete notification', function () {
        $notification = Notification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => NotificationType::LICENSE_EXPIRING,
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'Test notification',
                'message' => 'Test message',
            ],
            'priority' => 'medium',
            'created_at' => now(),
        ]);

        livewire(NotificationCenter::class)
            ->callTableAction('delete', $notification->id);

        expect(Notification::find($notification->id))->toBeNull();
    });

    test('can mark all notifications as read', function () {
        // Créer plusieurs notifications non lues
        $notifications = [];
        for ($i = 0; $i < 3; $i++) {
            $notifications[] = Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => NotificationType::LICENSE_EXPIRING,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $this->user->id,
                'data' => [
                    'title' => "Test notification {$i}",
                    'message' => 'Test message',
                ],
                'priority' => 'medium',
                'created_at' => now(),
            ]);
        }

        livewire(NotificationCenter::class)
            ->callAction('markAllAsRead');

        $unreadCount = Notification::where('notifiable_id', $this->user->id)
            ->whereNull('read_at')
            ->count();

        expect($unreadCount)->toBe(0);
    });

    test('can delete all read notifications', function () {
        // Créer des notifications lues
        $notifications = [];
        for ($i = 0; $i < 3; $i++) {
            $notifications[] = Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => NotificationType::LICENSE_EXPIRING,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $this->user->id,
                'data' => [
                    'title' => "Test notification {$i}",
                    'message' => 'Test message',
                ],
                'priority' => 'medium',
                'read_at' => now(),
                'created_at' => now(),
            ]);
        }

        livewire(NotificationCenter::class)
            ->callAction('deleteAllRead');

        $readCount = Notification::where('notifiable_id', $this->user->id)
            ->whereNotNull('read_at')
            ->count();

        expect($readCount)->toBe(0);
    });

    test('can search notifications', function () {
        $notifications = Notification::factory()->count(10)->create([
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->user->id,
        ]);
        $searchNotification = $notifications->first();

        livewire(NotificationCenter::class)
            ->searchTable($searchNotification->data['title'])
            ->assertCanSeeTableRecords([$searchNotification]);
    });

    test('can sort notifications', function () {
        $notifications = Notification::factory()->count(5)->create([
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->user->id,
        ]);

        livewire(NotificationCenter::class)
            ->sortTable('created_at')
            ->assertCanSeeTableRecords($notifications->sortBy('created_at'), inOrder: true)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords($notifications->sortByDesc('created_at'), inOrder: true);
    });

    test('filters notifications by type', function () {
        // Créer des notifications de différents types
        $licenseNotification = Notification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => NotificationType::LICENSE_EXPIRING,
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'License expiring',
                'message' => 'Test message',
            ],
            'priority' => 'high',
            'created_at' => now(),
        ]);

        $paymentNotification = Notification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => NotificationType::PAYMENT_OVERDUE,
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'Payment overdue',
                'message' => 'Test message',
            ],
            'priority' => 'high',
            'created_at' => now(),
        ]);

        livewire(NotificationCenter::class)
            ->filterTable('type', NotificationType::LICENSE_EXPIRING->value)
            ->assertCanSeeTableRecords([$licenseNotification])
            ->assertCanNotSeeTableRecords([$paymentNotification]);
    });

    test('filters notifications by read status', function () {
        // Créer une notification lue
        $readNotification = Notification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => NotificationType::LICENSE_EXPIRING,
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'Read notification',
                'message' => 'Test message',
            ],
            'priority' => 'medium',
            'read_at' => now(),
            'created_at' => now(),
        ]);

        // Créer une notification non lue
        $unreadNotification = Notification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => NotificationType::PAYMENT_OVERDUE,
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'Unread notification',
                'message' => 'Test message',
            ],
            'priority' => 'medium',
            'created_at' => now(),
        ]);

        // Tester le filtre "non lues"
        livewire(NotificationCenter::class)
            ->filterTable('read_status', 'unread')
            ->assertCanSeeTableRecords([$unreadNotification])
            ->assertCanNotSeeTableRecords([$readNotification]);
    });

    test('displays navigation badge with unread count', function () {
        // Créer des notifications non lues
        for ($i = 0; $i < 5; $i++) {
            Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => NotificationType::LICENSE_EXPIRING,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $this->user->id,
                'data' => [
                    'title' => "Unread notification {$i}",
                    'message' => 'Test message',
                ],
                'priority' => 'medium',
                'created_at' => now(),
            ]);
        }

        // Simuler l'authentification pour la méthode statique
        $this->actingAs($this->user);

        expect(NotificationCenter::getNavigationBadge())->toBe('5');
    });
});
