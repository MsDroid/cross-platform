<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'timezone' => $data['timezone'] ?? 'UTC',
                'locale' => $data['locale'] ?? 'en',
            ]);

            $token = $user->createToken($data['device_name'] ?? 'default')->plainTextToken;

            return ['user' => $user, 'token' => $token];
        });
    }

    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);
        $token = $user->createToken($data['device_name'])->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function logout(User $user, ?string $currentTokenId = null): void
    {
        if ($currentTokenId) {
            $user->tokens()->where('id', $currentTokenId)->delete();
        } else {
            $user->tokens()->delete();
        }
    }
}
