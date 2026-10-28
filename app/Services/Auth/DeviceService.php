<?php

namespace App\Services\Auth;

use App\Models\Device;
use App\Models\User;

class DeviceService
{
    public function register(User $user, array $data): Device
    {
        return Device::updateOrCreate(
            ['fcm_token' => $data['fcm_token']],
            [
                'user_id' => $user->id,
                'platform' => $data['platform'],
                'device_name' => $data['device_name'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'last_seen_at' => now(),
            ]
        );
    }

    public function unregister(User $user, string $fcmToken): void
    {
        $user->devices()->where('fcm_token', $fcmToken)->delete();
    }
}
