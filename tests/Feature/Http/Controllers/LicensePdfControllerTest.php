<?php

use App\Models\Customer;
use App\Models\License;
use App\Models\Product;
use App\Models\Module;
use App\Models\Option;
use App\Models\User;
use App\Enums\LicenseStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->customer = Customer::factory()->create([
        'user_id' => $this->user->id, // Correct : customer appartient à user
    ]);
    $this->product = Product::factory()->create();
    $this->license = License::factory()->create([
        'customer_id' => $this->customer->id,
        'product_id' => $this->product->id,
        'status' => LicenseStatus::ACTIVE,
    ]);
});

test('can download license pdf when authenticated', function () {
    $response = $this->actingAs($this->user)
        ->get(route('client.license.certificate', $this->license->id));

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');
});

test('cannot download license pdf when not authenticated', function () {
    $response = $this->get(route('client.license.certificate', $this->license->id));

    $response->assertRedirect(route('login'));
});

test('returns 404 for non-existent license', function () {
    $response = $this->actingAs($this->user)
        ->get(route('client.license.certificate', 999999));

    $response->assertStatus(404);
});

test('returns 403 when user tries to access license from different customer', function () {
    $otherUser = User::factory()->create();
    $otherCustomer = Customer::factory()->create([
        'user_id' => $otherUser->id,
    ]);
    $otherLicense = License::factory()->create([
        'customer_id' => $otherCustomer->id,
        'product_id' => $this->product->id,
        'status' => LicenseStatus::ACTIVE,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('client.license.certificate', $otherLicense->id));

    $response->assertStatus(403);
});

test('includes license modules in pdf', function () {
    $module = Module::factory()->create();
    $this->license->modules()->attach($module, [
        'enabled' => true,
        'expires_at' => null,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('client.license.certificate', $this->license->id));

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');
});

test('includes license options in pdf', function () {
    $option = Option::factory()->create();
    $this->license->options()->attach($option, [
        'enabled' => true,
        'expires_at' => null,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('client.license.certificate', $this->license->id));

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');
});

test('generates pdf with correct filename', function () {
    $response = $this->actingAs($this->user)
        ->get(route('client.license.certificate', $this->license->id));

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');

    $contentDisposition = $response->headers->get('Content-Disposition');
    expect($contentDisposition)->toContain('Certificat_Licence_' . $this->license->license_key);
});

test('includes license information in pdf content', function () {
    $response = $this->actingAs($this->user)
        ->get(route('client.license.certificate', $this->license->id));

    $response->assertStatus(200);

    // Vérifier que le contenu est bien un PDF
    $content = $response->getContent();
    expect($content)->toStartWith('%PDF-');
});

test('handles license without modules and options', function () {
    // Créer une licence sans modules ni options
    $simpleLicense = License::factory()->create([
        'customer_id' => $this->customer->id,
        'product_id' => $this->product->id,
        'status' => LicenseStatus::ACTIVE,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('client.license.certificate', $simpleLicense->id));

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');
});

