<?php

use App\Models\Option;
use App\Models\User;
use App\Filament\Resources\Options\OptionResource;
use App\Filament\Resources\Options\Pages\ListOptions;
use App\Filament\Resources\Options\Pages\CreateOption;
use App\Filament\Resources\Options\Pages\EditOption;
use Filament\Actions\DeleteAction;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'admin@batistack.com',
        'email_verified_at' => now(),
    ]);
    $this->actingAs($this->user);
});

it('can render options list page', function () {
    $options = Option::factory()->count(3)->create();

    livewire(ListOptions::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($options);
});

it('can create option', function () {
    $newData = [
        'key' => 'new_option_key',
        'name' => 'New Option Name',
        'description' => 'New option description',
        'type' => 'feature',
        'price' => 15.99,
        'billing_cycle' => 'monthly',
        'is_active' => true,
    ];

    livewire(CreateOption::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Option::class, [
        'key' => $newData['key'],
        'name' => $newData['name'],
    ]);
});

it('can validate option creation', function () {
    livewire(CreateOption::class)
        ->fillForm([
            'key' => null,
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['key' => 'required', 'name' => 'required']);
});

it('can retrieve option data for editing', function () {
    $option = Option::factory()->create();

    livewire(EditOption::class, [
        'record' => $option->getRouteKey(),
    ])
        ->assertFormSet([
            'key' => $option->key,
            'name' => $option->name,
            'description' => $option->description,
            'type' => $option->type->value,
            'price' => $option->price,
            'billing_cycle' => $option->billing_cycle->value,
            'is_active' => $option->is_active,
        ]);
});

it('can save option', function () {
    $option = Option::factory()->create();

    $newData = [
        'key' => 'updated_option_key',
        'name' => 'Updated Option Name',
        'description' => 'Updated description',
        'type' => 'feature',
        'price' => 25.99,
        'billing_cycle' => 'monthly',
        'is_active' => true,
    ];

    livewire(EditOption::class, [
        'record' => $option->getRouteKey(),
    ])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($option->refresh())
        ->key->toBe($newData['key'])
        ->name->toBe($newData['name']);
});

it('can delete option', function () {
    $option = Option::factory()->create();

    livewire(EditOption::class, [
        'record' => $option->getRouteKey(),
    ])
        ->callAction('delete');

    $this->assertModelMissing($option);
});

it('can search options globally', function () {
    $options = Option::factory()->count(10)->create();
    $option = $options->first();

    livewire(ListOptions::class)
        ->searchTable($option->key)
        ->assertCanSeeTableRecords([$option])
        ->assertCanNotSeeTableRecords($options->skip(1));
});

it('can sort options', function () {
    $options = Option::factory()->count(10)->create();

    livewire(ListOptions::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($options->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($options->sortByDesc('name'), inOrder: true);
});

it('can filter options by status', function () {
    $activeOptions = Option::factory()->count(5)->create(['is_active' => true]);
    $inactiveOptions = Option::factory()->count(3)->create(['is_active' => false]);

    livewire(ListOptions::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords($activeOptions)
        ->assertCanNotSeeTableRecords($inactiveOptions);
});

it('can filter options by type', function () {
    $featureOptions = Option::factory()->count(3)->create(['type' => 'feature']);
    $supportOptions = Option::factory()->count(5)->create(['type' => 'support']);

    livewire(ListOptions::class)
        ->filterTable('type', 'feature')
        ->assertCanSeeTableRecords($featureOptions)
        ->assertCanNotSeeTableRecords($supportOptions);
});

it('can bulk delete options', function () {
    $options = Option::factory()->count(10)->create();

    livewire(ListOptions::class)
        ->callTableBulkAction('delete', $options);

    foreach ($options as $option) {
        $this->assertModelMissing($option);
    }
});

it('displays navigation badge with option count', function () {
    Option::factory()->count(11)->create();

    expect(OptionResource::getNavigationBadge())->toBe('11');
});

it('can globally search options', function () {
    $option = Option::factory()->create([
        'key' => 'unique_option_key',
        'name' => 'Unique Option Name',
    ]);

    $searchableAttributes = OptionResource::getGloballySearchableAttributes();

    expect($searchableAttributes)->toContain('name')
        ->and($searchableAttributes)->toContain('key')
        ->and($searchableAttributes)->toContain('description');
});
