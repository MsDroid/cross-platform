<?php

it('registers an FCM device token', function () {
    $user = actingAsUser();

    $this->postJson('/api/v1/devices', [
        'fcm_token' => 'fcm_token_abc_123',
        'platform' => 'android',
        'device_name' => 'Pixel 7',
        'app_version' => '1.0.0',
    ])->assertCreated()
        ->assertJsonStructure(['device_id']);

    $this->assertDatabaseHas('devices', [
        'user_id' => $user->id,
        'fcm_token' => 'fcm_token_abc_123',
        'platform' => 'android',
    ]);
});

it('updates existing device if same fcm_token sent again', function () {
    $user = actingAsUser();

    $this->postJson('/api/v1/devices', [
        'fcm_token' => 'same-token',
        'platform' => 'ios',
    ])->assertCreated();

    $this->postJson('/api/v1/devices', [
        'fcm_token' => 'same-token',
        'platform' => 'ios',
        'app_version' => '2.0.0',
    ])->assertCreated();

    expect($user->devices()->where('fcm_token', 'same-token')->count())->toBe(1);
    expect($user->devices()->first()->app_version)->toBe('2.0.0');
});

it('rejects invalid platform', function () {
    actingAsUser();

    $this->postJson('/api/v1/devices', [
        'fcm_token' => 'abc',
        'platform' => 'windows',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['platform']);
});

it('unregisters a device by token', function () {
    $user = actingAsUser();
    $this->postJson('/api/v1/devices', [
        'fcm_token' => 'to-remove',
        'platform' => 'android',
    ])->assertCreated();

    $this->deleteJson('/api/v1/devices', ['fcm_token' => 'to-remove'])
        ->assertOk();

    expect($user->devices()->where('fcm_token', 'to-remove')->exists())->toBeFalse();
});
