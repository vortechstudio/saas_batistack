<?php

use App\Models\Customer;
use App\Models\User;
use App\Enums\CustomerStatus;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'admin@batistack.com',
        'email_verified_at' => now(),
    ]);
    $this->actingAs($this->user);
});

describe('Customer Resource', function () {
    test('can render customer list page', function () {
        $this->get(CustomerResource::getUrl('index'))
            ->assertSuccessful();
    });

    test('can list customers', function () {
        $customers = Customer::factory()->count(10)->create();

        livewire(ListCustomers::class)
            ->assertCanSeeTableRecords($customers);
    });

    test('can render customer create page', function () {
        $this->get(CustomerResource::getUrl('create'))
            ->assertSuccessful();
    });

    test('can create customer', function () {
        $newData = Customer::factory()->make();

        livewire(CreateCustomer::class)
            ->fillForm([
                'company_name' => $newData->company_name,
                'contact_name' => $newData->contact_name,
                'email' => $newData->email,
                'phone' => $newData->phone,
                'address' => $newData->address,
                'city' => $newData->city,
                'postal_code' => $newData->postal_code,
                'country' => $newData->country,
                'status' => CustomerStatus::ACTIVE,
                'user_id' => $this->user->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Customer::class, [
            'company_name' => $newData->company_name,
            'email' => $newData->email,
        ]);
    });

    test('can validate customer creation', function () {
        livewire(CreateCustomer::class)
            ->fillForm([
                'company_name' => null,
                'email' => 'invalid-email',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'company_name' => 'required',
                'email' => 'email',
            ]);
    });

    test('can render customer edit page', function () {
        $customer = Customer::factory()->create();

        $this->get(CustomerResource::getUrl('edit', [
            'record' => $customer,
        ]))->assertSuccessful();
    });

    test('can retrieve customer data for editing', function () {
        $customer = Customer::factory()->create();

        livewire(EditCustomer::class, [
            'record' => $customer->getRouteKey(),
        ])
            ->assertFormSet([
                'company_name' => $customer->company_name,
                'contact_name' => $customer->contact_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'status' => $customer->status->value,
            ]);
    });

    test('can save customer', function () {
        $customer = Customer::factory()->create();
        $newData = Customer::factory()->make();

        livewire(EditCustomer::class, [
            'record' => $customer->getRouteKey(),
        ])
            ->fillForm([
                'company_name' => $newData->company_name,
                'contact_name' => $newData->contact_name,
                'email' => $newData->email,
                'phone' => $newData->phone,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($customer->refresh())
            ->company_name->toBe($newData->company_name)
            ->contact_name->toBe($newData->contact_name)
            ->email->toBe($newData->email);
    });

    test('can delete customer', function () {
        $customer = Customer::factory()->create();

        livewire(EditCustomer::class, [
            'record' => $customer->getRouteKey(),
        ])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($customer);
    });

    test('can search customers', function () {
        $customers = Customer::factory()->count(10)->create();
        $searchCustomer = $customers->first();

        livewire(ListCustomers::class)
            ->searchTable($searchCustomer->company_name)
            ->assertCanSeeTableRecords([$searchCustomer])
            ->assertCanNotSeeTableRecords($customers->skip(1));
    });

    test('can sort customers', function () {
        $customers = Customer::factory()->count(10)->create();

        livewire(ListCustomers::class)
            ->sortTable('company_name')
            ->assertCanSeeTableRecords($customers->sortBy('company_name'), inOrder: true)
            ->sortTable('company_name', 'desc')
            ->assertCanSeeTableRecords($customers->sortByDesc('company_name'), inOrder: true);
    });

    test('can filter customers by status', function () {
        $activeCustomers = Customer::factory()->count(5)->create(['status' => CustomerStatus::ACTIVE]);
        $inactiveCustomers = Customer::factory()->count(3)->create(['status' => CustomerStatus::INACTIVE]);

        livewire(ListCustomers::class)
            ->filterTable('status', CustomerStatus::ACTIVE->value)
            ->assertCanSeeTableRecords($activeCustomers)
            ->assertCanNotSeeTableRecords($inactiveCustomers);
    });

    test('can bulk delete customers', function () {
        $customers = Customer::factory()->count(10)->create();

        livewire(ListCustomers::class)
            ->callTableBulkAction('delete', $customers);

        foreach ($customers as $customer) {
            $this->assertModelMissing($customer);
        }
    });

    test('displays navigation badge with customer count', function () {
        Customer::factory()->count(5)->create();

        expect(CustomerResource::getNavigationBadge())->toBe('5');
    });

    test('can globally search customers', function () {
        $customer = Customer::factory()->create([
            'company_name' => 'Unique Company Name',
        ]);

        $searchableAttributes = CustomerResource::getGloballySearchableAttributes();

        expect($searchableAttributes)->toContain('company_name')
            ->and($searchableAttributes)->toContain('contact_name')
            ->and($searchableAttributes)->toContain('email');
    });
});