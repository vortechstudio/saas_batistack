<?php

use App\Http\Middleware\CheckPermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->middleware = new CheckPermission();
    $this->user = User::factory()->create();

    // Create test permission
    $this->permission = Permission::create(['name' => 'test-permission']);
});

test('allows access when user has permission', function () {
    $this->user->givePermissionTo($this->permission);
    $this->actingAs($this->user);

    $request = Request::create('/test', 'GET');

    $response = $this->middleware->handle($request, function ($req) {
        return new Response('Success');
    }, 'test-permission');

    expect($response->getContent())->toBe('Success');
});

test('denies access when user lacks permission', function () {
    $this->actingAs($this->user);
    
    $request = Request::create('/test', 'GET');

    $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

    $this->middleware->handle($request, function ($req) {
        return new Response('Success');
    }, 'test-permission');
});

test('redirects to login when user not authenticated', function () {
    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn() => null);

    $response = $this->middleware->handle($request, function ($req) {
        return new Response('Success');
    }, 'test-permission');

    expect($response->getStatusCode())->toBe(302);
    expect($response->headers->get('Location'))->toContain('login');
});

test('allows access when user has permission via role', function () {
    $role = Role::create(['name' => 'test-role']);
    $role->givePermissionTo($this->permission);
    $this->user->assignRole($role);
    $this->actingAs($this->user);

    $request = Request::create('/test', 'GET');

    $response = $this->middleware->handle($request, function ($req) {
        return new Response('Success');
    }, 'test-permission');

    expect($response->getContent())->toBe('Success');
});
