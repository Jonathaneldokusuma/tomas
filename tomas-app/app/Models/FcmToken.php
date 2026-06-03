<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FcmToken extends Model
{
    protected $table = 'fcm_tokens';
    protected $fillable = ['user_type', 'user_id', 'fcm_token', 'device_id'];

    /**
     * Save or update FCM token for a user/tukang on a specific device.
     */
    public static function upsertToken(string $userType, int $userId, string $token, ?string $deviceId = null): void
    {
        static::updateOrCreate(
            ['user_type' => $userType, 'user_id' => $userId, 'device_id' => $deviceId],
            ['fcm_token' => $token]
        );
    }

    /**
     * Get all FCM tokens for a user/tukang.
     *
     * @return string[]
     */
    public static function getTokens(string $userType, int $userId): array
    {
        return static::where('user_type', $userType)
            ->where('user_id', $userId)
            ->pluck('fcm_token')
            ->toArray();
    }
}
