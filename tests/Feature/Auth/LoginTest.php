<?php

use App\Models\User;

it('logs in with valid credentials', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
        'device_name' => 'iPhone 14',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['user', 'token'])
        ->assertJsonPath('user.email', 'user@example.com');

    $this->assertNotEmpty($response->json('token'));
});

it('updates last_login_at on successful login', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
        'last_login_at' => null,
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
        'device_name' => 'iPhone',
    ])->assertOk();

    expect($user->fresh()->last_login_at)->not->toBeNull();
});

it('rejects wrong password', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'user@example.com',
        'password' => 'wrong-password',
        'device_name' => 'iPhone',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('rejects non-existent email', function () {
    $this->postJson('/api/v1/auth/login', [
        'email' => 'nobody@example.com',
        'password' => 'password123',
        'device_name' => 'iPhone',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('requires device_name on login', function () {
    User::factory()->create(['email' => 'user@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['device_name']);
});

it('creates a separate token per device', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
        'device_name' => 'iPhone',
    ])->assertOk();

    $this->postJson('/api/v1/auth/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
        'device_name' => 'Android',
    ])->assertOk();

    expect($user->tokens()->count())->toBe(2);
});
