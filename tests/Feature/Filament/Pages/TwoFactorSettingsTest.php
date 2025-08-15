<?php

use App\Filament\Pages\TwoFactorSettings;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

describe('TwoFactorSettings Page', function () {
    beforeEach(function () {
        // Créer les permissions et rôles nécessaires
        Permission::create(['name' => 'view_admin_panel']);
        Permission::create(['name' => 'access_two_factor_settings']);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(['view_admin_panel', 'access_two_factor_settings']);

        // Créer un utilisateur admin avec email @batistack.com
        $this->user = User::factory()->create([
            'email' => 'admin@batistack.com',
            'email_verified_at' => now()
        ]);

        $this->user->assignRole('admin');
        $this->actingAs($this->user);
    });

    test('can render page', function () {
        Livewire::test(TwoFactorSettings::class)
            ->assertSuccessful();
    });

    test('has correct navigation properties', function () {
        expect(TwoFactorSettings::getNavigationIcon())->toBe('heroicon-o-shield-check');
        expect(TwoFactorSettings::getNavigationLabel())->toBe('2FA');
        expect(TwoFactorSettings::getNavigationSort())->toBe(10);
        expect(TwoFactorSettings::getNavigationGroup())->toBe('Securité');
    });

    test('has correct title', function () {
        $page = new TwoFactorSettings();
        $reflection = new \ReflectionClass($page);
        $titleProperty = $reflection->getProperty('title');
        $titleProperty->setAccessible(true);

        expect($titleProperty->getValue($page))->toBe('Authentification à deux facteurs');
    });

    test('can access when authenticated', function () {
        expect(TwoFactorSettings::canAccess())->toBeTrue();
    });

    test('cannot access when not authenticated', function () {
        auth()->logout();
        expect(TwoFactorSettings::canAccess())->toBeFalse();
    });

    test('uses correct view', function () {
        $page = new TwoFactorSettings();
        $reflection = new \ReflectionClass($page);
        $viewProperty = $reflection->getProperty('view');
        $viewProperty->setAccessible(true);

        expect($viewProperty->getValue($page))->toBe('filament.pages.two-factor-settings');
    });

    test('page loads without errors', function () {
        Livewire::test(TwoFactorSettings::class)
            ->assertSuccessful();
    });
});
