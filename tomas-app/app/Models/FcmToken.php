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
        // A Firebase token belongs to one current app identity. Remove stale owner rows
        // so logout/login with a different account on the same phone still receives push.
        static::where('fcm_token', $token)
            ->where(function ($query) use ($userType, $userId) {
                $query->where('user_type', '!=', $userType)
                    ->orWhere('user_id', '!=', $userId);
            })
            ->delete();

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
