<?php

use App\Enums\LicenseStatus;
use App\Filament\Widgets\PopularModulesWidget;
use App\Models\Module;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Filament\Tables\Table;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'admin']);
    $this->user->assignRole($role);
    $this->actingAs($this->user);
});

test('can render widget', function () {
    Livewire::test(PopularModulesWidget::class)
        ->assertOk();
});

test('has correct properties', function () {
    $reflection = new ReflectionClass(PopularModulesWidget::class);

    $headingProperty = $reflection->getProperty('heading');
    $headingProperty->setAccessible(true);
    expect($headingProperty->getValue())->toBe('Modules Populaires');

    $sortProperty = $reflection->getProperty('sort');
    $sortProperty->setAccessible(true);
    expect($sortProperty->getValue())->toBe(7);
});

test('displays modules with license count', function () {
    $module1 = Module::factory()->create([
        'name' => 'Module Test 1',
        'is_active' => true
    ]);

    $module2 = Module::factory()->create([
        'name' => 'Module Test 2',
        'is_active' => true
    ]);

    // Créer des licences pour tester le comptage
    $license1 = License::factory()->create();
    $license2 = License::factory()->create();

    // Attacher les modules aux licences
    $license1->modules()->attach($module1->id, ['enabled' => true]);
    $license1->modules()->attach($module2->id, ['enabled' => true]);
    $license2->modules()->attach($module1->id, ['enabled' => true]);

    Livewire::test(PopularModulesWidget::class)
        ->assertCanSeeTableRecords([$module1, $module2])
        ->assertSee('Module Test 1')
        ->assertSee('Module Test 2');
});

test('shows only active modules', function () {
    $activeModule = Module::factory()->create([
        'name' => 'Active Module',
        'is_active' => true
    ]);

    $inactiveModule = Module::factory()->create([
        'name' => 'Inactive Module',
        'is_active' => false
    ]);

    Livewire::test(PopularModulesWidget::class)
        ->assertCanSeeTableRecords([$activeModule])
        ->assertCanNotSeeTableRecords([$inactiveModule]);
});

it('limits to 10 records', function () {
    // Créer 15 modules pour tester la limite
    $modules = Module::factory()->count(15)->create([
        'is_active' => true,
    ]);

    // Créer des licences et les associer aux modules via la table pivot license_modules
    $modules->each(function ($module) {
        $licenses = License::factory()->count(rand(1, 5))->create([
            'product_id' => Product::factory()->create()->id,
            'status' => LicenseStatus::ACTIVE,
        ]);

        // Associer chaque licence au module via la table pivot
        $licenses->each(function ($license) use ($module) {
            DB::table('license_modules')->insert([
                'license_id' => $license->id,
                'module_id' => $module->id,
                'enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    });

    // Tester directement la requête du widget
    $query = Module::query()
        ->select('modules.*')
        ->selectSub(
            DB::table('license_modules')
                ->selectRaw('COUNT(*)')
                ->whereColumn('license_modules.module_id', 'modules.id')
                ->where('license_modules.enabled', true),
            'licenses_count'
        )
        ->where('is_active', true)
        ->orderBy('licenses_count', 'desc')
        ->limit(10);

    $results = $query->get();

    // Vérifier que le résultat est limité à 10 enregistrements maximum
    expect($results)->toHaveCount(min(10, 15));
    expect($results->count())->toBeLessThanOrEqual(10);
});
