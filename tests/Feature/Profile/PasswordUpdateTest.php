<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

it('updates password with correct current password', function () {
    $user = User::factory()->create(['password' => bcrypt('oldpass123')]);
    Sanctum::actingAs($user, ['*']);

    $this->putJson('/api/v1/profile/password', [
        'current_password' => 'oldpass123',
        'password' => 'newpass123',
        'password_confirmation' => 'newpass123',
    ])->assertOk();

    expect(Hash::check('newpass123', $user->fresh()->password))->toBeTrue();
});

it('rejects update when current password is wrong', function () {
    $user = User::factory()->create(['password' => bcrypt('oldpass123')]);
    Sanctum::actingAs($user, ['*']);

    $this->putJson('/api/v1/profile/password', [
        'current_password' => 'wrong',
        'password' => 'newpass123',
        'password_confirmation' => 'newpass123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['current_password']);
});

it('revokes other device tokens on password change', function () {
    $user = User::factory()->create(['password' => bcrypt('oldpass123')]);
    $user->createToken('other-device');
    $user->createToken('current');
    Sanctum::actingAs($user, ['*'], 'current');

    $this->putJson('/api/v1/profile/password', [
        'current_password' => 'oldpass123',
        'password' => 'newpass123',
        'password_confirmation' => 'newpass123',
    ])->assertOk();

    expect($user->tokens()->where('name', 'other-device')->exists())->toBeFalse();
    expect($user->tokens()->where('name', 'current')->exists())->toBeTrue();
});
