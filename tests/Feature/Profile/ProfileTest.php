<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('returns authenticated user profile', function () {
    $user = actingAsUser();

    $this->getJson('/api/v1/profile')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

it('requires authentication to view profile', function () {
    $this->getJson('/api/v1/profile')->assertUnauthorized();
});

it('updates user profile fields', function () {
    $user = actingAsUser();

    $this->putJson('/api/v1/profile', [
        'name' => 'Updated Name',
        'timezone' => 'Asia/Kolkata',
        'locale' => 'hi',
    ])->assertOk()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.locale', 'hi');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
    ]);
});

it('uploads avatar and returns url', function () {
    Storage::fake('public');
    $user = actingAsUser();

    $this->putJson('/api/v1/profile', [
        'name' => 'With Avatar',
        'avatar' => UploadedFile::fake()->image('me.jpg', 300, 300),
    ])->assertOk();

    $user->refresh();
    expect($user->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
});

it('replaces old avatar when uploading new one', function () {
    Storage::fake('public');
    $user = actingAsUser();

    $this->putJson('/api/v1/profile', [
        'avatar' => UploadedFile::fake()->image('old.jpg'),
    ])->assertOk();

    $oldPath = $user->fresh()->avatar_path;

    $this->putJson('/api/v1/profile', [
        'avatar' => UploadedFile::fake()->image('new.jpg'),
    ])->assertOk();

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($user->fresh()->avatar_path);
});

it('rejects avatar larger than 2MB', function () {
    Storage::fake('public');
    actingAsUser();

    $this->putJson('/api/v1/profile', [
        'avatar' => UploadedFile::fake()->image('big.jpg')->size(3000), // 3MB
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['avatar']);
});

it('soft deletes user account and revokes tokens on delete', function () {
    $user = actingAsUser();
    $user->createToken('extra');

    $this->deleteJson('/api/v1/profile')
        ->assertOk()
        ->assertJsonPath('message', 'Account scheduled for deletion.');

    $this->assertSoftDeleted('users', ['id' => $user->id]);
    expect($user->tokens()->count())->toBe(0);
});
