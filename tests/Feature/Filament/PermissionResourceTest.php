<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use App\Filament\Resources\Permissions\PermissionResource;
use App\Filament\Resources\Permissions\Pages\ListPermissions;
use App\Filament\Resources\Permissions\Pages\CreatePermission;
use App\Filament\Resources\Permissions\Pages\EditPermission;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'admin@batistack.com',
        'email_verified_at' => now(),
    ]);
    $this->actingAs($this->user);
});

describe('Permission Resource', function () {

    test('can list permissions', function () {
        $permissions = collect();
        for ($i = 0; $i < 10; $i++) {
            $permissions->push(Permission::create([
                'name' => 'permission_' . $i,
                'guard_name' => 'web',
            ]));
        }

        livewire(ListPermissions::class)
            ->assertCanSeeTableRecords($permissions);
    });

    test('can create permission', function () {
        $newData = [
            'name' => 'test-permission',
            'guard_name' => 'web',
        ];

        livewire(CreatePermission::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Permission::class, $newData);
    });

    test('can validate permission creation', function () {
        livewire(CreatePermission::class)
            ->fillForm([
                'name' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
            ]);
    });

    test('can retrieve permission data for editing', function () {
        $permission = Permission::create([
            'name' => 'test-permission',
            'guard_name' => 'web',
        ]);

        livewire(EditPermission::class, [
            'record' => $permission->getRouteKey(),
        ])
            ->assertFormSet([
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
            ]);
    });

    test('can save permission', function () {
        $permission = Permission::create([
            'name' => 'test-permission',
            'guard_name' => 'web',
        ]);

        $newData = [
            'name' => 'updated-permission',
            'guard_name' => 'web',
        ];

        livewire(EditPermission::class, [
            'record' => $permission->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($permission->refresh())
            ->name->toBe($newData['name'])
            ->guard_name->toBe($newData['guard_name']);
    });

    test('can delete permission', function () {
        $permission = Permission::create([
            'name' => 'test-permission',
            'guard_name' => 'web',
        ]);

        livewire(EditPermission::class, [
            'record' => $permission->getRouteKey(),
        ])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($permission);
    });

    test('can search permissions', function () {
        $searchPermission = Permission::create([
            'name' => 'unique-search-permission',
            'guard_name' => 'web',
        ]);

        $otherPermissions = collect();
        for ($i = 1; $i <= 5; $i++) {
            $otherPermissions->push(Permission::create([
                'name' => "other-permission-{$i}",
                'guard_name' => 'web',
            ]));
        }

        livewire(ListPermissions::class)
            ->searchTable('unique-search')
            ->assertCanSeeTableRecords([$searchPermission])
            ->assertCanNotSeeTableRecords($otherPermissions);
    });

    test('can sort permissions', function () {
        for ($i = 1; $i <= 5; $i++) {
            Permission::create([
                'name' => "permission-{$i}",
                'guard_name' => 'web',
            ]);
        }

        $permissions = Permission::all();

        livewire(ListPermissions::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords($permissions->sortBy('name'), inOrder: true)
            ->sortTable('name', 'desc')
            ->assertCanSeeTableRecords($permissions->sortByDesc('name'), inOrder: true);
    });

    test('can bulk delete permissions', function () {
        for ($i = 1; $i <= 5; $i++) {
            Permission::create([
                'name' => "permission-{$i}",
                'guard_name' => 'web',
            ]);
        }

        $permissions = Permission::all();

        livewire(ListPermissions::class)
            ->callTableBulkAction('all_delete', $permissions);

        foreach ($permissions as $permission) {
            $this->assertModelMissing($permission);
        }
    });

    test('displays navigation badge with permission count', function () {
        for ($i = 0; $i < 12; $i++) {
            Permission::create([
                'name' => 'permission_' . $i,
                'guard_name' => 'web',
            ]);
        }

        expect(PermissionResource::getNavigationBadge())->toBe('12');
    });

    test('can globally search permissions', function () {
        $permission = Permission::create([
            'name' => 'unique-permission-name',
            'guard_name' => 'web',
        ]);

        $searchableAttributes = PermissionResource::getGloballySearchableAttributes();

        expect($searchableAttributes)->toContain('name');
    });
});
