<?php

use App\Models\Product;
use App\Models\User;
use App\Enums\BillingCycle;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use Filament\Actions\DeleteAction;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'admin@batistack.com',
        'email_verified_at' => now(),
    ]);
    $this->actingAs($this->user);
});

describe('Product Resource', function () {
    test('can render product list page', function () {
        $this->get(ProductResource::getUrl('index'))
            ->assertSuccessful();
    });

    test('can see table records', function () {
        $products = Product::factory()->count(10)->create();

        livewire(ListProducts::class)
            ->assertCanSeeTableRecords($products);
    });

    test('can render product create page', function () {
        $this->get(ProductResource::getUrl('create'))
            ->assertSuccessful();
    });

    test('can create product', function () {
        $newData = Product::factory()->make();

        livewire(CreateProduct::class)
            ->fillForm([
                'name' => $newData->name,
                'slug' => $newData->slug,
                'description' => $newData->description,
                'base_price' => $newData->base_price,
                'billing_cycle' => $newData->billing_cycle->value,
                'max_users' => $newData->max_users,
                'is_active' => true,
                'is_featured' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Product::class, [
            'name' => $newData->name,
            'slug' => $newData->slug,
        ]);
    });

    test('can validate product creation', function () {
        livewire(CreateProduct::class)
            ->fillForm([
                'name' => '',
                'slug' => '',
                'base_price' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'slug', 'base_price']);
    });

    test('can render product edit page', function () {
        $product = Product::factory()->create();

        $this->get(ProductResource::getUrl('edit', [
            'record' => $product,
        ]))->assertSuccessful();
    });

    test('can retrieve product data for editing', function () {
        $product = Product::factory()->create();

        livewire(EditProduct::class, [
            'record' => $product->getRouteKey(),
        ])
            ->assertFormSet([
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'base_price' => $product->base_price,
                'billing_cycle' => $product->billing_cycle->value,
            ]);
    });

    test('can save product', function () {
        $product = Product::factory()->create();
        $newData = Product::factory()->make();

        livewire(EditProduct::class, [
            'record' => $product->getRouteKey(),
        ])
            ->fillForm([
                'name' => $newData->name,
                'description' => $newData->description,
                'base_price' => $newData->base_price,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($product->refresh())
            ->name->toBe($newData->name)
            ->description->toBe($newData->description)
            ->base_price->toBe($newData->base_price);
    });

    test('can delete product', function () {
        $product = Product::factory()->create();

        livewire(EditProduct::class, [
            'record' => $product->getRouteKey(),
        ])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($product);
    });

    test('can search products', function () {
        $products = Product::factory()->count(10)->create();
        $searchProduct = $products->first();

        livewire(ListProducts::class)
            ->searchTable($searchProduct->name)
            ->assertCanSeeTableRecords([$searchProduct])
            ->assertCanNotSeeTableRecords($products->skip(1));
    });

    test('can sort products', function () {
        $products = Product::factory()->count(10)->create();

        livewire(ListProducts::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords($products->sortBy('name'), inOrder: true)
            ->sortTable('name', 'desc')
            ->assertCanSeeTableRecords($products->sortByDesc('name'), inOrder: true);
    });

    test('can filter products by status', function () {
        $activeProducts = Product::factory()->count(5)->create(['is_active' => true]);
        $inactiveProducts = Product::factory()->count(3)->create(['is_active' => false]);

        livewire(ListProducts::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords($activeProducts)
            ->assertCanNotSeeTableRecords($inactiveProducts);
    });

    test('can filter products by billing cycle', function () {
        $monthlyProducts = Product::factory()->count(5)->create(['billing_cycle' => BillingCycle::MONTHLY]);
        $yearlyProducts = Product::factory()->count(3)->create(['billing_cycle' => BillingCycle::YEARLY]);

        livewire(ListProducts::class)
            ->filterTable('billing_cycle', BillingCycle::MONTHLY->value)
            ->assertCanSeeTableRecords($monthlyProducts)
            ->assertCanNotSeeTableRecords($yearlyProducts);
    });

    test('can bulk delete products', function () {
        $products = Product::factory()->count(10)->create();

        livewire(ListProducts::class)
            ->callTableBulkAction('delete', $products);

        foreach ($products as $product) {
            $this->assertModelMissing($product);
        }
    });

    test('displays navigation badge with product count', function () {
        Product::factory()->count(7)->create();

        expect(ProductResource::getNavigationBadge())->toBe('7');
    });

    test('can globally search products', function () {
        $product = Product::factory()->create([
            'name' => 'Unique Product Name',
        ]);

        $searchableAttributes = ProductResource::getGloballySearchableAttributes();

        expect($searchableAttributes)->toContain('name')
            ->and($searchableAttributes)->toContain('slug')
            ->and($searchableAttributes)->toContain('description');
    });
});
