<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

it('sends password reset link for valid email', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'reset@example.com']);

    $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'reset@example.com',
    ])->assertOk();

    Notification::assertSentTo($user, ResetPassword::class);
});

it('does not reveal whether email exists', function () {
    Notification::fake();

    $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'missing@example.com',
    ])->assertOk();   // same response regardless
});
