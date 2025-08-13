<?php

use App\Http\Controllers\StripeWebhookController;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Payment;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Services\LicenseCreationService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Stripe\Webhook;

beforeEach(function () {
    Event::fake();
    // Configurer un secret webhook de test
    Config::set('services.stripe.webhook_secret', 'whsec_test_secret');

    // Mock Stripe Webhook pour éviter les vraies vérifications
    $this->mock(Webhook::class, function ($mock) {
        $mock->shouldReceive('constructEvent')
            ->andReturnUsing(function ($payload, $signature, $secret) {
                $data = json_decode($payload, true);
                return (object) [
                    'type' => $data['type'],
                    'data' => (object) [
                        'object' => $data['data']['object'],
                        'toArray' => function() use ($data) {
                            return $data;
                        }
                    ]
                ];
            });
    });

    // Mock LicenseCreationService pour éviter les erreurs de création de licence
    $this->mock(LicenseCreationService::class, function ($mock) {
        $mock->shouldReceive('createLicenseFromSubscription')
            ->andReturn((object) ['id' => 'test_license_123']);
        $mock->shouldReceive('createLicenseFromInvoice')
            ->andReturn((object) ['id' => 'test_license_456']);
    });
});

describe('StripeWebhookController', function () {
    test('handles payment intent succeeded', function () {
        $payment = Payment::factory()->create([
            'stripe_payment_intent_id' => 'pi_test_123',
            'status' => PaymentStatus::PENDING
        ]);

        $payload = json_encode([
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123',
                    'charges' => [
                        'data' => [['id' => 'ch_test_123']]
                    ]
                ]
            ]
        ]);

        // Créer une signature de test valide
        $timestamp = time();
        $secret = 'whsec_test_secret';
        $signedPayload = $timestamp . '.' . $payload;
        $signature = hash_hmac('sha256', $signedPayload, $secret);
        $stripeSignature = "t={$timestamp},v1={$signature}";

        $response = $this->call('POST', '/api/stripe/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => $stripeSignature
        ], $payload);

        $response->assertStatus(200);
        expect($payment->fresh()->status)->toBe(PaymentStatus::SUCCEEDED);
    });

    test('handles payment intent failed', function () {
        $payment = Payment::factory()->create([
            'stripe_payment_intent_id' => 'pi_test_789',
            'status' => PaymentStatus::PENDING
        ]);

        $payload = json_encode([
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'pi_test_789',
                    'last_payment_error' => [
                        'message' => 'Your card was declined.'
                    ]
                ]
            ]
        ]);

        // Créer une signature de test valide
        $timestamp = time();
        $secret = 'whsec_test_secret';
        $signedPayload = $timestamp . '.' . $payload;
        $signature = hash_hmac('sha256', $signedPayload, $secret);
        $stripeSignature = "t={$timestamp},v1={$signature}";

        $response = $this->call('POST', '/api/stripe/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => $stripeSignature
        ], $payload);

        $response->assertStatus(200);
        expect($payment->fresh()->status)->toBe(PaymentStatus::FAILED);
    });

    test('returns 400 for invalid signature', function () {
        $payload = json_encode([
            'type' => 'checkout.session.completed',
            'data' => ['object' => []]
        ]);

        $response = $this->call('POST', '/api/stripe/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => 'invalid_signature'
        ], $payload);

        $response->assertStatus(400);
    });

    test('returns 500 when webhook secret is not configured', function () {
        Config::set('services.stripe.webhook_secret', null);

        $payload = json_encode([
            'type' => 'checkout.session.completed',
            'data' => ['object' => []]
        ]);

        $response = $this->call('POST', '/api/stripe/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], $payload);

        $response->assertStatus(500);
    });
});
