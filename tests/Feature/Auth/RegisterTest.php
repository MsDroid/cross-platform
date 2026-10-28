<?php

use App\Models\User;

it('registers a new user successfully', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Rahul Sharma',
        'email' => 'rahul@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'timezone' => 'Asia/Kolkata',
        'locale' => 'en',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'avatar_url', 'timezone', 'locale'],
            'token',
        ])
        ->assertJsonPath('user.email', 'rahul@example.com');

    $this->assertDatabaseHas('users', [
        'email' => 'rahul@example.com',
        'timezone' => 'Asia/Kolkata',
    ]);
});

it('requires name, email and password', function () {
    $this->postJson('/api/v1/auth/register', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('rejects duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Someone',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('requires password confirmation to match', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

it('requires password to be at least 8 characters', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

it('rejects invalid timezone', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'timezone' => 'Mars/Olympus',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['timezone']);
});

it('throttles registration after 10 attempts per minute', function () {
    foreach (range(1, 11) as $i) {
        $this->postJson('/api/v1/auth/register', [
            'name' => "User $i",
            'email' => "u{$i}@example.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
    }

    // 11th call should hit the throttle (10/min)
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Final',
        'email' => 'final@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertStatus(429);
});
