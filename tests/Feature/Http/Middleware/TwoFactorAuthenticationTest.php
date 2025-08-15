<?php

use App\Http\Middleware\TwoFactorAuthentication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->middleware = new TwoFactorAuthentication();
    $this->user = User::factory()->create();
});

test('allows access when user has no 2FA enabled', function () {
    $this->actingAs($this->user);
    
    $request = Request::create('/test', 'GET');

    $response = $this->middleware->handle($request, function ($req) {
        return new Response('Success');
    });

    expect($response->getContent())->toBe('Success');
});

test('allows access when 2FA is verified in session', function () {
    // Mock user with 2FA enabled
    $this->user->two_factor_enabled = true;
    $this->user->two_factor_secret = 'test-secret';
    $this->user->save();
    $this->actingAs($this->user);

    Session::put('2fa_verified', true);

    $request = Request::create('/test', 'GET');

    $response = $this->middleware->handle($request, function ($req) {
        return new Response('Success');
    });

    expect($response->getContent())->toBe('Success');
});

test('redirects to 2FA verification when required', function () {
    // Mock user with 2FA enabled
    $this->user->two_factor_enabled = true;
    $this->user->two_factor_secret = 'test-secret';
    $this->user->save();
    $this->actingAs($this->user);

    Session::forget('2fa_verified');

    $request = Request::create('/test', 'GET');

    $response = $this->middleware->handle($request, function ($req) {
        return new Response('Success');
    });

    expect($response->getStatusCode())->toBe(302);
    expect($response->headers->get('Location'))->toContain('two-factor-verify');
});

test('allows access to 2FA routes to avoid infinite loop', function () {
    // Mock user with 2FA enabled
    $this->user->two_factor_enabled = true;
    $this->user->two_factor_secret = 'test-secret';
    $this->user->save();
    $this->actingAs($this->user);

    Session::forget('2fa_verified');

    // Test with a 2FA route that should be allowed
    $request = Request::create('/two-factor/verify', 'GET');
    
    // Mock the route to simulate a 2FA route
    $route = new \Illuminate\Routing\Route(['GET'], '/two-factor/verify', []);
    $route->name('two-factor.verify');
    $request->setRouteResolver(fn() => $route);

    $response = $this->middleware->handle($request, function ($req) {
        return new Response('Success');
    });

    expect($response->getContent())->toBe('Success');
});

test('redirects to login when user not authenticated', function () {
    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn() => null);

    $response = $this->middleware->handle($request, function ($req) {
        return new Response('Success');
    });

    expect($response->getStatusCode())->toBe(302);
    expect($response->headers->get('Location'))->toContain('login');
});
