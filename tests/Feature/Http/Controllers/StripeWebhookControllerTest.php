<?php

use App\Http\Controllers\StripeWebhookController;
use App\Models\Customer;
use App\Models\Product;
use App\Models\License;
use App\Models\Invoice;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Services\LicenseCreationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    // Mock Stripe configuration
    config(['services.stripe.webhook_secret' => 'test_webhook_secret']);
});

test('handles checkout session completed for subscription', function () {
    // Mock the LicenseCreationService
    $mockLicenseService = Mockery::mock(LicenseCreationService::class);
    $mockLicense = License::factory()->make(['id' => 1, 'license_key' => 'test-key']);
    
    $mockLicenseService->shouldReceive('createLicenseFromSubscription')
        ->once()
        ->andReturn($mockLicense);
    
    // Bind the mock to the container
    app()->instance(LicenseCreationService::class, $mockLicenseService);
    
    // Mock all log calls to avoid errors
    Log::shouldReceive('info')->withAnyArgs()->zeroOrMoreTimes();
    Log::shouldReceive('error')->withAnyArgs()->never();

    // Create test data
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();

    // Create a proper mock object for Stripe session with metadata as object
    $metadata = (object) [
        'customer_id' => $customer->id,
        'product_id' => $product->id,
        'domain' => 'test.example.com',
        'billing_cycle' => 'monthly',
        'selected_modules' => json_encode(['module1', 'module2']),
        'selected_options' => json_encode(['option1' => 'value1'])
    ];

    $session = (object) [
        'id' => 'cs_test_123',
        'mode' => 'subscription',
        'subscription' => 'sub_test_123',
        'metadata' => $metadata
    ];

    // Create the webhook payload
    $payload = json_encode([
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => $session
        ]
    ]);

    // Create request with proper headers
    $request = Request::create('/stripe/webhook', 'POST', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => 'test_signature',
        'CONTENT_TYPE' => 'application/json'
    ], $payload);

    // Mock Stripe webhook verification
    $this->mock('alias:Stripe\\Webhook', function ($mock) use ($session) {
        $event = (object) [
            'type' => 'checkout.session.completed',
            'data' => (object) [
                'object' => $session
            ]
        ];
        $mock->shouldReceive('constructEvent')->andReturn($event);
    });

    // Execute the webhook
    $controller = new StripeWebhookController();
    $response = $controller->handleWebhook($request);

    // Assertions
    expect($response->getStatusCode())->toBe(200);

    // Verify the service was called correctly
    $mockLicenseService->shouldHaveReceived('createLicenseFromSubscription');
    
    // Verify response is successful
    expect($response->getContent())->toBe('Webhook handled successfully');
});

test('handles payment checkout completed', function () {
    // Mock Log facade
    Log::shouldReceive('info')
        ->with('Stripe webhook received', ['type' => 'checkout.session.completed'])
        ->once();

    // Mock potential error logs
    Log::shouldReceive('error')
        ->with('Invoice not found', Mockery::type('array'))
        ->never();

    // Mock success logs
    Log::shouldReceive('info')
        ->with('Invoice status updated to paid', Mockery::type('array'))
        ->once();
    
    Log::shouldReceive('info')
        ->with('License created successfully', Mockery::type('array'))
        ->once();

    // Create test data
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();
    $invoice = Invoice::factory()->create([
        'customer_id' => $customer->id,
        'status' => InvoiceStatus::PENDING,
        'metadata' => [
            'product_id' => $product->id,
            'domain' => 'example.com',
            'billing_cycle' => 'monthly',
            'max_users' => 10
        ]
    ]);

    $session = (object) [
        'id' => 'cs_test_456',
        'mode' => 'payment',
        'payment_intent' => 'pi_test_456',
        'customer' => 'cus_test_456',
        'metadata' => (object) [
            'invoice_id' => (string) $invoice->id
        ]
    ];

    $payload = json_encode([
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => $session
        ]
    ]);

    $request = Request::create('/stripe/webhook', 'POST', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => 'test_signature',
        'CONTENT_TYPE' => 'application/json'
    ], $payload);

    // Mock Stripe webhook verification
    $this->mock('alias:Stripe\\Webhook', function ($mock) use ($session) {
        $event = (object) [
            'type' => 'checkout.session.completed',
            'data' => (object) [
                'object' => $session
            ]
        ];
        $mock->shouldReceive('constructEvent')->andReturn($event);
    });

    $controller = new StripeWebhookController();
    $response = $controller->handleWebhook($request);

    expect($response->getStatusCode())->toBe(200);

    // Verify invoice was updated
    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::PAID);
});

test('logs webhook events', function () {
    // Mock all possible log calls
    Log::shouldReceive('info')
        ->with('Stripe webhook received', ['type' => 'unknown.event'])
        ->once();

    Log::shouldReceive('info')
        ->with('Unhandled webhook type: unknown.event')
        ->once();

    $session = (object) ['id' => 'evt_test'];
    $payload = json_encode([
        'type' => 'unknown.event',
        'data' => ['object' => $session]
    ]);

    $request = Request::create('/stripe/webhook', 'POST', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => 'test_signature',
        'CONTENT_TYPE' => 'application/json'
    ], $payload);

    // Mock Stripe webhook verification
    $this->mock('alias:Stripe\\Webhook', function ($mock) use ($session) {
        $event = (object) [
            'type' => 'unknown.event',
            'data' => (object) ['object' => $session]
        ];
        $mock->shouldReceive('constructEvent')->andReturn($event);
    });

    $controller = new StripeWebhookController();
    $response = $controller->handleWebhook($request);

    expect($response->getStatusCode())->toBe(200);
});

test('handles missing metadata gracefully', function () {
    Log::shouldReceive('info')
        ->with('Stripe webhook received', ['type' => 'checkout.session.completed'])
        ->once();

    Log::shouldReceive('warning')
        ->with('Missing metadata in subscription checkout', Mockery::type('array'))
        ->once();

    $session = (object) [
        'id' => 'cs_test_789',
        'mode' => 'subscription',
        'subscription' => 'sub_test_789',
        'metadata' => (object) [] // Empty metadata
    ];

    $payload = json_encode([
        'type' => 'checkout.session.completed',
        'data' => ['object' => $session]
    ]);

    $request = Request::create('/stripe/webhook', 'POST', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => 'test_signature',
        'CONTENT_TYPE' => 'application/json'
    ], $payload);

    $this->mock('alias:Stripe\\Webhook', function ($mock) use ($session) {
        $event = (object) [
            'type' => 'checkout.session.completed',
            'data' => (object) ['object' => $session]
        ];
        $mock->shouldReceive('constructEvent')->andReturn($event);
    });

    $controller = new StripeWebhookController();
    $response = $controller->handleWebhook($request);

    expect($response->getStatusCode())->toBe(200);
});

test('handles invalid stripe signature', function () {
    // Mock the exact error log call that will be made
    Log::shouldReceive('error')
        ->with('Webhook signature verification failed: Invalid signature')
        ->once();

    $payload = json_encode(['type' => 'test.event']);
    $request = Request::create('/stripe/webhook', 'POST', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => 'invalid_signature',
        'CONTENT_TYPE' => 'application/json'
    ], $payload);

    $this->mock('alias:Stripe\\Webhook', function ($mock) {
        $mock->shouldReceive('constructEvent')
             ->andThrow(new \Stripe\Exception\SignatureVerificationException('Invalid signature'));
    });

    $controller = new StripeWebhookController();
    $response = $controller->handleWebhook($request);

    expect($response->getStatusCode())->toBe(400);
    expect($response->getContent())->toBe('Invalid signature');
});
