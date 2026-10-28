<?php 
namespace App\Services\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ProfileService {
    public function update(User $user, array $data): User {
        if (isset($data['avatar'])) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $data['avatar']->store('avatars', 'public');
            unset($data['avatar']);
        }

        $user->update($data);
        return $user->fresh();
    }

    public function updatePassword(User $user, string $newPassword): void {
        $user->update(['password' => $newPassword]);
        // Revoke all other tokens except current
        $currentToken = $user->currentAccessToken();
        $user->tokens()->when($currentToken, fn($q) => $q->where('id', '!=', $currentToken->id))->delete();
    }
}