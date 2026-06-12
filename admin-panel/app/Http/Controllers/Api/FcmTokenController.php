<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    /**
     * POST /api/fcm-token  (user app – Sanctum protected)
     * POST /api/tukang/fcm-token  (tukang app – token header)
     */
    public function storeUser(Request $request)
    {
        $request->validate(['token' => 'required|string', 'device_id' => 'nullable|string|max:200']);

        FcmToken::upsertToken('user', $request->user()->id_user, $request->token, $request->device_id);

        return response()->json(['message' => 'FCM token saved']);
    }

    public function storeTukang(Request $request)
    {
        $request->validate(['token' => 'required|string', 'device_id' => 'nullable|string|max:200']);

        $token   = $request->header('X-Tukang-Token');
        if (!$token) return response()->json(['message' => 'Unauthorized'], 401);

        $decoded = base64_decode($token);
        $parts   = explode('|', $decoded);
        if (count($parts) < 2 || $parts[1] !== 'tukang') return response()->json(['message' => 'Unauthorized'], 401);

        $tukang = \App\Models\Tukang::where('username', $parts[0])->first();
        if (!$tukang) return response()->json(['message' => 'Unauthorized'], 401);

        FcmToken::upsertToken('tukang', $tukang->id_tukang, $request->token, $request->device_id);

        return response()->json(['message' => 'FCM token saved']);
    }
}
