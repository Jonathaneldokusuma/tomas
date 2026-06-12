<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Send push notifications via Firebase Cloud Messaging HTTP v1 API.
 *
 * Setup steps (Railway / production):
 *   1. Go to Firebase Console → Project Settings → Service Accounts
 *   2. Click "Generate new private key"
 *   3. Upload it in Admin Panel → Firebase Push, or set FIREBASE_SERVICE_ACCOUNT_JSON in Railway
 *   4. Set FCM_PROJECT_ID=tomas-app-2e185 only if the JSON project_id should be overridden
 *
 * For now, FCM will silently skip if not configured (no crash).
 */
class FcmService
{
    private static ?string $accessToken = null;
    private static ?string $projectId = null;

    /**
     * Send a notification to a single FCM token.
     */
    public static function send(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        $projectId = self::resolveProjectId();
        if (!$projectId || !$fcmToken) return false;

        $token = self::getAccessToken();
        if (!$token) return false;

        try {
            $channelId = $data['channel_id'] ?? env('FCM_ANDROID_CHANNEL_ID', 'tomas_notifications');
            unset($data['channel_id']);

            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'channel_id'  => $channelId,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ],
                    'data' => array_map('strval', $data),
                ],
            ];

            $response = Http::withToken($token)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

            if (!$response->successful()) {
                Log::warning('FCM send failed', ['status' => $response->status(), 'body' => $response->body()]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('FCM exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send to multiple FCM tokens (batch).
     */
    public static function sendToMany(array $tokens, string $title, string $body, array $data = []): void
    {
        foreach (array_unique(array_filter($tokens)) as $token) {
            self::send($token, $title, $body, $data);
        }
    }

    /**
     * Get OAuth2 access token using service account JSON.
     */
    private static function getAccessToken(): ?string
    {
        if (self::$accessToken) return self::$accessToken;

        $creds = self::loadCredentials();
        if (!$creds) {
            Log::info('FCM: No service account file found, notifications skipped.');
            return null;
        }

        try {
            $jwt   = self::buildJwt($creds);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if ($response->successful()) {
                self::$accessToken = $response->json('access_token');
                return self::$accessToken;
            }

            Log::warning('FCM: Failed to get access token', ['body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('FCM: Access token exception: ' . $e->getMessage());
            return null;
        }
    }

    private static function resolveProjectId(): ?string
    {
        if (self::$projectId !== null) {
            return self::$projectId;
        }

        $projectId = env('FCM_PROJECT_ID');
        if ($projectId) {
            self::$projectId = $projectId;
            return self::$projectId;
        }

        $creds = self::loadCredentials();
        self::$projectId = $creds['project_id'] ?? null;

        return self::$projectId;
    }

    private static function loadCredentials(): ?array
    {
        $envJson = env('FIREBASE_SERVICE_ACCOUNT_JSON') ?: env('GOOGLE_APPLICATION_CREDENTIALS_JSON');
        $decodedEnvJson = self::decodeServiceAccountJson($envJson);
        if ($decodedEnvJson) {
            return $decodedEnvJson;
        }

        try {
            $stored = SystemSetting::where('setting_key', 'firebase_service_account_json')->value('setting_value');
            if (is_string($stored) && $stored !== '') {
                $json = self::decodeServiceAccountJson(Crypt::decryptString($stored));
                if ($json) {
                    return $json;
                }
            }
        } catch (\Throwable $e) {
            Log::debug('FCM: failed to load service account from DB', ['error' => $e->getMessage()]);
        }

        $paths = array_filter([
            env('GOOGLE_APPLICATION_CREDENTIALS'),
            storage_path('firebase-service-account.json'),
            storage_path('app/firebase-service-account.json'),
        ]);

        foreach ($paths as $path) {
            if (!is_string($path) || !file_exists($path)) {
                continue;
            }

            $json = self::decodeServiceAccountJson(file_get_contents($path));
            if ($json) {
                return $json;
            }
        }

        return null;
    }

    private static function decodeServiceAccountJson(?string $value): ?array
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $candidate = trim($value);
        $json = json_decode($candidate, true);

        if (!is_array($json)) {
            $decoded = base64_decode($candidate, true);
            $json = $decoded ? json_decode($decoded, true) : null;
        }

        if (is_array($json) && !empty($json['client_email']) && !empty($json['private_key'])) {
            return $json;
        }

        return null;
    }

    /**
     * Build a signed JWT for Google OAuth2.
     */
    private static function buildJwt(array $creds): string
    {
        $now    = time();
        $scope  = 'https://www.googleapis.com/auth/firebase.messaging';

        $header  = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64url_encode(json_encode([
            'iss'   => $creds['client_email'],
            'sub'   => $creds['client_email'],
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
            'scope' => $scope,
        ]));

        $data = "{$header}.{$payload}";
        openssl_sign($data, $signature, $creds['private_key'], 'SHA256');

        return "{$data}." . base64url_encode($signature);
    }
}

if (!function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
