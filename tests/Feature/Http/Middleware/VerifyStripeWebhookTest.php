<?php

use App\Http\Middleware\VerifyStripeWebhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->middleware = new VerifyStripeWebhook();
    Config::set('services.stripe.webhook_secret', 'whsec_test_secret');
});

test('allows request with valid stripe signature', function () {
    $payload = json_encode(['type' => 'test.event']);
    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp . '.' . $payload, 'whsec_test_secret');
    $stripeSignature = "t={$timestamp},v1={$signature}";

    // Mock Stripe Event
    $mockEvent = new \stdClass();
    $mockEvent->type = 'test.event';
    
    // Mock static Webhook::constructEvent
     \Mockery::mock('alias:' . Webhook::class)
         ->shouldReceive('constructEvent')
         ->once()
         ->andReturn($mockEvent);

    $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
    $request->headers->set('Stripe-Signature', $stripeSignature);

    $response = $this->middleware->handle($request, function ($req) {
        return new Response('Success');
    });

    expect($response->getContent())->toBe('Success');
    expect($request->attributes->get('stripe_event'))->not->toBeNull();
});

test('rejects request with invalid signature', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('Invalid Stripe webhook signature', ['error' => 'Invalid signature.']);

    // Mock static Webhook::constructEvent to throw exception
     \Mockery::mock('alias:' . Webhook::class)
         ->shouldReceive('constructEvent')
         ->once()
         ->andThrow(new SignatureVerificationException('Invalid signature.'));

    $payload = json_encode(['type' => 'test.event']);

    $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
    $request->headers->set('Stripe-Signature', 'invalid_signature');

    $response = $this->middleware->handle($request, function ($req) {
        return new Response('Success');
    });

    expect($response->getStatusCode())->toBe(400);
    expect($response->getContent())->toBe('Invalid signature');
});

test('returns error when webhook secret not configured', function () {
    Config::set('services.stripe.webhook_secret', '');

    $request = Request::create('/webhook', 'POST');

    $response = $this->middleware->handle($request, function ($req) {
        return new Response('Success');
    });

    expect($response->getStatusCode())->toBe(500);
    expect($response->getContent())->toBe('Webhook secret not configured');
});

test('rejects request with missing signature header', function () {
    // Mock static Webhook::constructEvent to throw exception for missing signature
    \Mockery::mock('alias:' . Webhook::class)
        ->shouldReceive('constructEvent')
        ->once()
        ->andThrow(new SignatureVerificationException('Invalid signature.'));

    $payload = json_encode(['type' => 'test.event']);

    $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
    // No Stripe-Signature header

    $response = $this->middleware->handle($request, function ($req) {
        return new Response('Success');
    });

    expect($response->getStatusCode())->toBe(400);
});

test('sets stripe event in request attributes', function () {
    $payload = json_encode(['type' => 'test.event', 'data' => ['object' => []]]);
    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp . '.' . $payload, 'whsec_test_secret');
    $stripeSignature = "t={$timestamp},v1={$signature}";

    // Mock Stripe Event
    $mockEvent = new \stdClass();
    $mockEvent->type = 'test.event';
    
    // Mock static Webhook::constructEvent
     \Mockery::mock('alias:' . Webhook::class)
         ->shouldReceive('constructEvent')
         ->once()
         ->andReturn($mockEvent);

    $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
    $request->headers->set('Stripe-Signature', $stripeSignature);

    $this->middleware->handle($request, function ($req) {
        $event = $req->attributes->get('stripe_event');
        expect($event->type)->toBe('test.event');
        return new Response('Success');
    });
});
