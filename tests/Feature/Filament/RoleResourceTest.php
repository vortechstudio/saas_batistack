<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
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

describe('Role Resource', function () {
    test('can list roles', function () {
        $roles = collect();
        for ($i = 0; $i < 10; $i++) {
            $roles->push(Role::create([
                'name' => 'role_' . $i,
                'guard_name' => 'web',
            ]));
        }

        livewire(ListRoles::class)
            ->assertCanSeeTableRecords($roles);
    });

    test('can create role', function () {
        $newData = [
            'name' => 'test_role',
            'guard_name' => 'web',
        ];

        livewire(CreateRole::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Role::class, $newData);
    });

    test('can validate role creation', function () {
        livewire(CreateRole::class)
            ->fillForm([
                'name' => null,
                'guard_name' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'guard_name' => 'required',
            ]);
    });

    test('can retrieve role data for editing', function () {
        $role = Role::create([
            'name' => 'test_role',
            'guard_name' => 'web',
        ]);

        $permissions = collect();
        for ($i = 0; $i < 3; $i++) {
            $permissions->push(Permission::create([
                'name' => 'permission_' . $i,
                'guard_name' => 'web',
            ]));
        }
        $role->givePermissionTo($permissions);

        livewire(EditRole::class, [
            'record' => $role->getRouteKey(),
        ])
            ->assertFormSet([
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => $permissions->pluck('id')->toArray(),
            ]);
    });

    test('can edit role', function () {
        $role = Role::create([
            'name' => 'original_role',
            'guard_name' => 'web',
        ]);

        $newData = [
            'name' => 'updated_role',
            'guard_name' => 'web',
        ];

        livewire(EditRole::class, [
            'record' => $role->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($role->refresh())
            ->name->toBe($newData['name'])
            ->guard_name->toBe($newData['guard_name']);
    });

    test('can delete role', function () {
        $role = Role::create([
            'name' => 'role_to_delete',
            'guard_name' => 'web',
        ]);

        livewire(EditRole::class, [
            'record' => $role->getRouteKey(),
        ])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($role);
    });

    test('can search roles', function () {
        $roles = collect();
        for ($i = 0; $i < 10; $i++) {
            $roles->push(Role::create([
                'name' => 'role_' . $i,
                'guard_name' => 'web',
            ]));
        }
        $roleToFind = $roles->first();

        livewire(ListRoles::class)
            ->searchTable($roleToFind->name)
            ->assertCanSeeTableRecords([$roleToFind])
            ->assertCanNotSeeTableRecords($roles->skip(1));
    });

    test('can sort roles', function () {
        $roles = collect();
        for ($i = 0; $i < 10; $i++) {
            $roles->push(Role::create([
                'name' => 'role_' . $i,
                'guard_name' => 'web',
            ]));
        }

        livewire(ListRoles::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords($roles->sortBy('name'), inOrder: true)
            ->sortTable('name', 'desc')
            ->assertCanSeeTableRecords($roles->sortByDesc('name'), inOrder: true);
    });

    test('can bulk delete roles', function () {
        $roles = collect();
        for ($i = 0; $i < 10; $i++) {
            $roles->push(Role::create([
                'name' => 'role_' . $i,
                'guard_name' => 'web',
            ]));
        }

        livewire(ListRoles::class)
            ->callTableBulkAction('delete', $roles);

        foreach ($roles as $role) {
            $this->assertModelMissing($role);
        }
    });

    test('displays navigation badge with role count', function () {
        for ($i = 0; $i < 8; $i++) {
            Role::create([
                'name' => 'role_' . $i,
                'guard_name' => 'web',
            ]);
        }

        expect(RoleResource::getNavigationBadge())->toBe('8');
    });

    test('can globally search roles', function () {
        $role = Role::create([
            'name' => 'Unique Role Name',
            'guard_name' => 'web',
        ]);

        $searchableAttributes = RoleResource::getGloballySearchableAttributes();

        expect($searchableAttributes)->toContain('name');
    });
});
