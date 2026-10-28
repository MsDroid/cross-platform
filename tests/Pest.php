<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

// Helper: authenticated user
function actingAsUser(?User $user = null): User
{
    $user = $user ?? User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    return $user;
}
