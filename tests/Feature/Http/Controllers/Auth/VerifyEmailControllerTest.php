<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => null,
    ]);
});

test('can verify email with valid verification link', function () {
    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $this->user->id, 'hash' => sha1($this->user->email)]
    );

    $response = $this->actingAs($this->user)
        ->get($verificationUrl);

    $response->assertRedirect(route('dashboard') . '?verified=1');

    $this->user->refresh();
    expect($this->user->hasVerifiedEmail())->toBeTrue();

    Event::assertDispatched(Verified::class);
});

test('redirects if email already verified', function () {
    $this->user->markEmailAsVerified();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $this->user->id, 'hash' => sha1($this->user->email)]
    );

    $response = $this->actingAs($this->user)
        ->get($verificationUrl);

    $response->assertRedirect(route('dashboard') . '?verified=1');
});

test('requires authentication', function () {
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $this->user->id, 'hash' => sha1($this->user->email)]
    );

    $response = $this->get($verificationUrl);

    $response->assertRedirect(route('login'));
});

test('rejects invalid verification link', function () {
    $invalidUrl = route('verification.verify', [
        'id' => $this->user->id,
        'hash' => 'invalid-hash'
    ]);

    $response = $this->actingAs($this->user)
        ->get($invalidUrl);

    $response->assertStatus(403);
});

test('rejects expired verification link', function () {
    $expiredUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->subMinutes(60), // Expired
        ['id' => $this->user->id, 'hash' => sha1($this->user->email)]
    );

    $response = $this->actingAs($this->user)
        ->get($expiredUrl);

    $response->assertStatus(403);
});
