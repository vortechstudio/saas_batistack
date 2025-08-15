<?php

use App\Filament\Widgets\NavigationStatsWidget;
use App\Models\License;
use App\Models\Customer;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'admin']);
    $this->user->assignRole($role);
    $this->actingAs($this->user);
});

test('can render widget', function () {
    Livewire::test(NavigationStatsWidget::class)
        ->assertOk();
});

test('has correct properties', function () {
    $widget = new NavigationStatsWidget();

    expect($widget->getPollingInterval())->toBe('30s');

    $reflection = new ReflectionClass($widget);
    $sortProperty = $reflection->getProperty('sort');
    $sortProperty->setAccessible(true);
    expect($sortProperty->getValue())->toBe(1);
});

test('calculates stats correctly', function () {
    // Créer des données de test
    $customer = Customer::factory()->create();

    // Licences qui expirent dans 15 jours
    License::factory()->create([
        'customer_id' => $customer->id,
        'expires_at' => Carbon::now()->addDays(15)
    ]);

    // Licence expirée
    License::factory()->create([
        'customer_id' => $customer->id,
        'expires_at' => Carbon::now()->subDays(5)
    ]);

    // Nouveau client cette semaine
    Customer::factory()->create([
        'created_at' => Carbon::now()->startOfWeek()->addDay()
    ]);

    // Activité aujourd'hui
    Activity::create([
        'log_name' => 'test',
        'description' => 'test activity',
        'subject_type' => User::class,
        'subject_id' => $this->user->id,
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
        'created_at' => Carbon::today()
    ]);

    $widget = new NavigationStatsWidget();
    $reflection = new ReflectionMethod($widget, 'getStats');
    $reflection->setAccessible(true);
    $stats = $reflection->invoke($widget);

    expect($stats)->toHaveCount(5);
    expect($stats[0]->getLabel())->toBe('Notifications totales');
    expect($stats[1]->getLabel())->toBe('Licences à expirer');
    expect($stats[2]->getLabel())->toBe('Licences expirées');
    expect($stats[3]->getLabel())->toBe('Nouveaux clients');
    expect($stats[4]->getLabel())->toBe('Activités');
});

test('has correct columns count', function () {
    $widget = new NavigationStatsWidget();
    $reflection = new ReflectionMethod($widget, 'getColumns');
    $reflection->setAccessible(true);
    $columns = $reflection->invoke($widget);

    expect($columns)->toBe(5);
});

test('can view returns true when authenticated', function () {
    expect(NavigationStatsWidget::canView())->toBeTrue();
});

test('can view returns false when not authenticated', function () {
    Auth::logout();
    expect(NavigationStatsWidget::canView())->toBeFalse();
});
