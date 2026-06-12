<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use App\Models\SystemSetting;
use App\Services\FcmService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class FcmController extends Controller
{
    public function index()
    {
        $serviceAccountPath = storage_path('firebase-service-account.json');
        $altServiceAccountPath = storage_path('app/firebase-service-account.json');
        $storedJson = $this->readStoredServiceAccount();

        $activePath = file_exists($serviceAccountPath)
            ? $serviceAccountPath
            : (file_exists($altServiceAccountPath) ? $altServiceAccountPath : null);

        $serviceAccount = $activePath
            ? $this->readServiceAccount($activePath)
            : $storedJson;

        $status = [
            'project_id' => env('FCM_PROJECT_ID') ?: ($serviceAccount['project_id'] ?? null),
            'file_exists' => $activePath !== null,
            'stored_in_db' => !empty($storedJson),
            'service_account_active' => $activePath !== null || !empty($storedJson),
            'service_account_source' => $activePath ? 'file' : (!empty($storedJson) ? 'database' : null),
            'file_path'   => $activePath,
            'token_users' => FcmToken::where('user_type', 'user')->count(),
            'token_tukang' => FcmToken::where('user_type', 'tukang')->count(),
        ];

        return view('admin.fcm.index', compact('status'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_account' => 'required|file|mimetypes:application/json,text/plain,text/json,application/octet-stream|max:1024',
        ]);

        $file = $request->file('service_account');
        $contents = file_get_contents($file->getRealPath());
        $decoded = json_decode($contents, true);

        if (!is_array($decoded) || empty($decoded['client_email']) || empty($decoded['private_key'])) {
            return back()->with('error', 'File service account Firebase tidak valid.');
        }

        try {
            SystemSetting::updateOrCreate(
                ['setting_key' => 'firebase_service_account_json'],
                ['setting_value' => Crypt::encryptString($contents)]
            );
        } catch (\Throwable $e) {
            Log::warning('FCM: failed to persist service account to DB', [
                'error' => $e->getMessage(),
            ]);
        }

        File::ensureDirectoryExists(storage_path());
        File::put(storage_path('firebase-service-account.json'), $contents);

        return back()->with('success', 'Service account Firebase berhasil disimpan. Push notification siap dipakai.');
    }

    public function destroy()
    {
        $paths = [
            storage_path('firebase-service-account.json'),
            storage_path('app/firebase-service-account.json'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        try {
            SystemSetting::where('setting_key', 'firebase_service_account_json')->delete();
        } catch (\Throwable $e) {
            Log::warning('FCM: failed to delete service account from DB', [
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Service account Firebase dihapus.');
    }

    public function test(Request $request)
    {
        $request->validate([
            'target' => 'required|in:user,tukang,all',
            'title' => 'nullable|string|max:120',
            'body' => 'nullable|string|max:240',
        ]);

        $target = $request->input('target');
        $title = trim((string) $request->input('title', 'Tes Push Tomas'));
        $body = trim((string) $request->input('body', 'Notifikasi percobaan dari admin panel.'));

        $query = FcmToken::query();
        if ($target !== 'all') {
            $query->where('user_type', $target);
        }

        $tokens = $query->pluck('fcm_token')->filter()->unique()->values()->all();

        if (empty($tokens)) {
            return back()->with('error', 'Belum ada token push untuk target ' . $target . '. Minta user/tukang login sekali lagi supaya token tersimpan.');
        }

        FcmService::sendToMany($tokens, $title, $body, [
            'type' => 'admin_test',
            'target' => $target,
        ]);

        return back()->with('success', 'Tes push dikirim ke ' . count($tokens) . ' token (' . $target . ').');
    }

    private function readServiceAccount(?string $path): array
    {
        if (!$path || !file_exists($path)) {
            return [];
        }

        $json = json_decode(file_get_contents($path), true);

        return is_array($json) ? $json : [];
    }

    private function readStoredServiceAccount(): array
    {
        try {
            $stored = SystemSetting::where('setting_key', 'firebase_service_account_json')->value('setting_value');

            if (is_string($stored) && $stored !== '') {
                $json = json_decode(Crypt::decryptString($stored), true);
                return is_array($json) ? $json : [];
            }
        } catch (\Throwable $e) {
            // Ignore DB or decrypt errors and fall back to file storage.
        }

        return [];
    }
}
