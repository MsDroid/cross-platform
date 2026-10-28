<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('logs out authenticated user and revokes current token', function () {
    $user = actingAsUser();
    $tokenId = $user->currentAccessToken()->id;

    $this->postJson('/api/v1/auth/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logged out.');

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
});

it('rejects logout for guests', function () {
    $this->postJson('/api/v1/auth/logout')
        ->assertUnauthorized();
});

it('does not revoke other device tokens on logout', function () {
    $user = User::factory()->create();
    $user->createToken('other-device');   // another token
    $user->createToken('current');
    Sanctum::actingAs($user, ['*'], 'current');

    $this->postJson('/api/v1/auth/logout')->assertOk();

    // The 'other-device' token must still exist
    expect($user->tokens()->where('name', 'other-device')->exists())->toBeTrue();
});
