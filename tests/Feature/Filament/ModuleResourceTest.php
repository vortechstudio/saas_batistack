<?php

use App\Models\Module;
use App\Models\User;
use App\Filament\Resources\Modules\ModuleResource;
use App\Filament\Resources\Modules\Pages\ListModules;
use App\Filament\Resources\Modules\Pages\CreateModule;
use App\Filament\Resources\Modules\Pages\EditModule;
use Filament\Actions\DeleteAction;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'admin@batistack.com',
        'email_verified_at' => now(),
    ]);
    $this->actingAs($this->user);
});

describe('Module Resource', function () {

    test('can list modules', function () {
        $modules = Module::factory()->count(10)->create();

        livewire(ListModules::class)
            ->assertCanSeeTableRecords($modules);
    });

    test('can create module', function () {
        $newData = [
            'key' => 'new_module_key',
            'name' => 'New Module Name',
            'description' => 'New module description',
            'category' => 'core',
            'base_price' => 25.99,
            'is_active' => true,
            'sort_order' => 10,
        ];

        livewire(CreateModule::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Module::class, [
            'key' => $newData['key'],
            'name' => $newData['name'],
        ]);
    });

    test('can validate module creation', function () {
        livewire(CreateModule::class)
            ->fillForm([
                'key' => null,
                'name' => null,
                'base_price' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'key' => 'required',
                'name' => 'required',
            ]);
    });

    test('can retrieve module data for editing', function () {
        $module = Module::factory()->create();

        livewire(EditModule::class, [
            'record' => $module->getRouteKey(),
        ])
            ->assertFormSet([
                'key' => $module->key,
                'name' => $module->name,
                'description' => $module->description,
                'category' => $module->category->value,
                'base_price' => $module->base_price,
                'is_active' => $module->is_active,
                'sort_order' => $module->sort_order,
            ]);
    });

    test('can save module', function () {
        $module = Module::factory()->create();

        $newData = [
            'key' => 'updated_module_key',
            'name' => 'Updated Module Name',
            'description' => 'Updated description',
            'category' => 'advanced',
            'base_price' => 45.99,
            'is_active' => false,
            'sort_order' => 20,
        ];

        livewire(EditModule::class, [
            'record' => $module->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($module->refresh())
            ->key->toBe($newData['key'])
            ->name->toBe($newData['name'])
            ->description->toBe($newData['description'])
            ->base_price->toBe('45.99')
            ->is_active->toBe(false);
    });

    test('can delete module', function () {
        $module = Module::factory()->create();

        livewire(EditModule::class, [
            'record' => $module->getRouteKey(),
        ])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($module);
    });

    test('can search modules', function () {
        $modules = Module::factory()->count(10)->create();
        $searchModule = $modules->first();

        livewire(ListModules::class)
            ->searchTable($searchModule->name)
            ->assertCanSeeTableRecords([$searchModule]);
    });

    test('can sort modules', function () {
        $modules = Module::factory()->count(10)->create();

        livewire(ListModules::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords($modules->sortBy('name'))
            ->sortTable('name', 'desc')
            ->assertCanSeeTableRecords($modules->sortByDesc('name'));
    });

    test('can filter modules by status', function () {
        $activeModules = Module::factory()->count(5)->create(['is_active' => true]);
        $inactiveModules = Module::factory()->count(3)->create(['is_active' => false]);

        livewire(ListModules::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords($activeModules)
            ->assertCanNotSeeTableRecords($inactiveModules);
    });

    test('can filter modules by category', function () {
        $coreModules = Module::factory()->count(3)->create(['category' => 'core']);
        $advancedModules = Module::factory()->count(5)->create(['category' => 'advanced']);

        livewire(ListModules::class)
            ->filterTable('category', 'core')
            ->assertCanSeeTableRecords($coreModules)
            ->assertCanNotSeeTableRecords($advancedModules);
    });

    test('can bulk delete modules', function () {
        $modules = Module::factory()->count(10)->create();

        livewire(ListModules::class)
            ->callTableBulkAction('delete', $modules);

        foreach ($modules as $module) {
            $this->assertModelMissing($module);
        }
    });

    test('displays navigation badge with module count', function () {
        Module::factory()->count(9)->create();

        expect(ModuleResource::getNavigationBadge())->toBe('9');
    });

    test('can globally search modules', function () {
        $module = Module::factory()->create([
            'key' => 'unique_module_key',
            'name' => 'Unique Module Name',
        ]);

        $searchableAttributes = ModuleResource::getGloballySearchableAttributes();

        expect($searchableAttributes)->toContain('name')
            ->and($searchableAttributes)->toContain('key')
            ->and($searchableAttributes)->toContain('description');
    });
});
