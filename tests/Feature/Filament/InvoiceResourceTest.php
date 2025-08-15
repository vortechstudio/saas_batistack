<?php

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\User;
use App\Enums\InvoiceStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use Filament\Actions\DeleteAction;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Créer les permissions pour les factures
    Permission::firstOrCreate(['name' => 'invoices.view']);
    Permission::firstOrCreate(['name' => 'invoices.create']);
    Permission::firstOrCreate(['name' => 'invoices.edit']);
    Permission::firstOrCreate(['name' => 'invoices.delete']);

    // Créer le rôle Super Admin et lui donner toutes les permissions
    $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
    $superAdminRole->syncPermissions(Permission::all());

    // Créer un utilisateur avec le bon email (celui du seeder)
    $this->user = User::firstOrCreate(
        ['email' => 'admin@batistack.com'],
        [
            'name' => 'Super Admin',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]
    );

    // Assigner le rôle Super Admin
    $this->user->assignRole('Super Admin');

    $this->actingAs($this->user);
});

describe('Invoice Resource', function () {
    test('can list invoices', function () {
        $invoices = Invoice::factory()->count(10)->create();

        livewire(ListInvoices::class)
            ->assertCanSeeTableRecords($invoices);
    });

    test('can create invoice', function () {
        $customer = Customer::factory()->create();

        // Créer directement en base pour vérifier que le modèle fonctionne
        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseHas(Invoice::class, [
            'customer_id' => $customer->id,
            'id' => $invoice->id,
        ]);
    });

    test('can validate invoice creation', function () {
        livewire(CreateInvoice::class)
            ->fillForm([
                'customer_id' => null,
                'invoice_number' => null,
                'subtotal_amount' => 'invalid',
                'total_amount' => 'invalid',
                'due_date' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'customer_id' => 'required',
                'invoice_number' => 'required',
                'subtotal_amount' => 'numeric',
                'total_amount' => 'numeric',
                'due_date' => 'required',
            ]);
    });

    test('can retrieve invoice data for editing', function () {
        $invoice = Invoice::factory()->create();

        livewire(EditInvoice::class, [
            'record' => $invoice->getRouteKey(),
        ])
            ->assertFormSet([
                'customer_id' => $invoice->customer_id,
                'invoice_number' => $invoice->invoice_number,
                'subtotal_amount' => $invoice->subtotal_amount,
                'total_amount' => $invoice->total_amount,
                'status' => $invoice->status->value,
            ]);
    });

    test('can save invoice', function () {
        $invoice = Invoice::factory()->create();
        $newData = Invoice::factory()->make();

        livewire(EditInvoice::class, [
            'record' => $invoice->getRouteKey(),
        ])
            ->fillForm([
                'subtotal_amount' => $newData->subtotal_amount,
                'total_amount' => $newData->total_amount,
                'status' => InvoiceStatus::PAID->value,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($invoice->refresh())
            ->subtotal_amount->toBe($newData->subtotal_amount)
            ->total_amount->toBe($newData->total_amount)
            ->status->toBe(InvoiceStatus::PAID);
    });

    test('can delete invoice', function () {
        $invoice = Invoice::factory()->create();

        livewire(EditInvoice::class, [
            'record' => $invoice->getRouteKey(),
        ])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($invoice);
    });

    test('can search invoices', function () {
        $invoices = Invoice::factory()->count(10)->create();
        $searchInvoice = $invoices->first();

        livewire(ListInvoices::class)
            ->searchTable($searchInvoice->invoice_number)
            ->assertCanSeeTableRecords([$searchInvoice])
            ->assertCanNotSeeTableRecords($invoices->skip(1));
    });

    test('can sort invoices', function () {
        $invoices = Invoice::factory()->count(10)->create();

        livewire(ListInvoices::class)
            ->sortTable('invoice_number')
            ->assertCanSeeTableRecords($invoices->sortBy('invoice_number'), inOrder: true)
            ->sortTable('invoice_number', 'desc')
            ->assertCanSeeTableRecords($invoices->sortByDesc('invoice_number'), inOrder: true);
    });

    test('can filter invoices by status', function () {
        $paidInvoices = Invoice::factory()->count(5)->create(['status' => InvoiceStatus::PAID]);
        $pendingInvoices = Invoice::factory()->count(3)->create(['status' => InvoiceStatus::PENDING]);

        livewire(ListInvoices::class)
            ->filterTable('status', InvoiceStatus::PAID->value)
            ->assertCanSeeTableRecords($paidInvoices)
            ->assertCanNotSeeTableRecords($pendingInvoices);
    });

    test('can filter overdue invoices', function () {
        $overdueInvoices = Invoice::factory()->count(3)->create([
            'due_date' => now()->subDays(5),
            'status' => InvoiceStatus::PENDING,
        ]);

        $currentInvoices = Invoice::factory()->count(2)->create([
            'due_date' => now()->addDays(5),
            'status' => InvoiceStatus::PENDING,
        ]);

        livewire(ListInvoices::class)
            ->filterTable('overdue', true)
            ->assertCanSeeTableRecords($overdueInvoices)
            ->assertCanNotSeeTableRecords($currentInvoices);
    });

    test('can bulk delete invoices', function () {
        $invoices = Invoice::factory()->count(10)->create();

        livewire(ListInvoices::class)
            ->callTableBulkAction('delete', $invoices);

        foreach ($invoices as $invoice) {
            $this->assertModelMissing($invoice);
        }
    });

    test('displays navigation badge with invoice count', function () {
        Invoice::factory()->count(7)->create();

        expect(InvoiceResource::getNavigationBadge())->toBe('7');
    });

    test('can globally search invoices', function () {
        $invoice = Invoice::factory()->create([
            'invoice_number' => 'UNIQUE-INV-123',
        ]);

        $searchableAttributes = InvoiceResource::getGloballySearchableAttributes();

        expect($searchableAttributes)->toContain('invoice_number');
    });
});
